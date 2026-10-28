<?php

/**
 * Liquidación de Prestaciones Sociales — fase N-E del plan de nómina (mig. 086).
 *
 * Fuente: las FÓRMULAS del formato del cliente `LIQUIDACION MES JULIO 2026.xls`
 * (datos de prueba, fórmulas reales) + la respuesta N-3 del 2026-10-06. **Leer
 * `docs/REGLAS_NEGOCIO_Nomina.md` antes de tocar este archivo.** Las tres hojas
 * del formato tienen su equivalente aquí:
 *
 *   · INTERESES       → `calcularMes()` / `calcularMeses()`: un renglón por mes
 *     con salario, salario integral, días depositados, anticipos y el interés del
 *     BCV capitalizado mes a mes — exactamente las columnas B…R de la hoja.
 *   · FORMATO         → `calcularResumen()`: último salario (mensual y diario),
 *     salario integral y los conceptos de la liquidación.
 *   · INTERES DE MORA → `calcularMora()`: meses entre el egreso y el pago.
 *
 * Igual que `Nomina::calcular()`, las funciones de cálculo son **puras** (sin BD)
 * para poder probarlas contra los valores ya calculados de la plantilla
 * (tests/run.php). Los intermedios no se redondean; solo la salida.
 *
 * Dos decisiones que no salen de la plantilla y conviene no revertir sin leer el
 * plan:
 *   1. Art. 142 d LOTTT: se paga lo MAYOR entre la garantía depositada (a+b) y
 *      el retroactivo (c). La plantilla solo trae la línea del literal c; el
 *      sistema calcula los dos y dice cuál aplicó.
 *   2. Los días adicionales (art. 142 b) siguen la regla ESCRITA del cliente
 *      (2 días por año desde el segundo, hasta 30), no los 6/8/10 de la hoja de
 *      prueba, que no cuadran con ninguna fecha de ingreso coherente.
 */
class Liquidacion extends Model
{
    const ESTADOS = ['Borrador', 'Cerrada', 'Anulada'];
    const ESTADO_BADGES = [
        'Borrador' => 'sig-badge--warning',
        'Cerrada'  => 'sig-badge--neutral',
        'Anulada'  => 'sig-badge--danger',
    ];

    /** Meses que cierran trimestre en el modo «calendario» (es la definición de trimestre, no un parámetro). */
    const MESES_FIN_TRIMESTRE = [3, 6, 9, 12];

    /**
     * Las cifras de la ley y del contrato NO están en el código: viven en
     * `configuracion_sistema` (mig. 086) y se editan en Nómina → Parámetros.
     * Si cambia la LOTTT o la contratación colectiva, basta con cambiar el
     * valor; las liquidaciones cerradas no se tocan (guardan su resultado).
     * Los defaults replican la regla vigente y solo actúan si falta la clave.
     */
    const PARAMS_DEFAULT = [
        'liq_dias_garantia_trimestre'      => 15,   // art. 142 a
        'liq_dias_adicionales_anio'        => 2,    // art. 142 b
        'liq_dias_adicionales_desde_anio'  => 2,    // «a partir del segundo año»
        'liq_dias_adicionales_tope'        => 30,   // «acumulativos hasta 30»
        'liq_dias_retroactivo_anio'        => 30,   // art. 142 c
        'liq_meses_fraccion_retroactivo'   => 6,    // «fracción superior a seis meses»
        'nomina_dias_mes'                  => 30,   // salario diario = mensual / 30
        'nomina_dias_base_anio'            => 360,  // alícuotas = diario × días / 360
    ];

    /** Clave de texto (no numérica): cómo se cuentan los trimestres. */
    const CLAVE_MODO_TRIMESTRE = 'liq_trimestre_modo';

    private static ?array $cacheParams = null;

    public static function params(): array
    {
        if (self::$cacheParams !== null) return self::$cacheParams;
        $out = [];
        foreach (self::PARAMS_DEFAULT as $clave => $default) {
            $v = ConfigSistema::get($clave);
            $out[$clave] = ($v === '' || $v === null) ? $default : (float)$v;
        }
        $modo = ConfigSistema::get(self::CLAVE_MODO_TRIMESTRE);
        $out[self::CLAVE_MODO_TRIMESTRE] = in_array($modo, self::MODOS_TRIMESTRE, true) ? $modo : 'calendario';
        return self::$cacheParams = $out;
    }

    public static function invalidarCache(): void { self::$cacheParams = null; }

    /** Parámetros efectivos: los inyectados (pruebas) o los de configuración, completados con los defaults. */
    private static function p(?array $p): array
    {
        return ($p ?? self::params()) + self::PARAMS_DEFAULT + [self::CLAVE_MODO_TRIMESTRE => 'calendario'];
    }

    /** Entradas editables de un renglón mensual (hoja INTERESES). */
    const CAMPOS_MES = [
        'salario_base', 'prima_profesional', 'prima_antiguedad', 'bono_transporte',
        'bono_hijos', 'dias_alic_vac', 'dias_alic_fin', 'dias_deposito', 'anticipo',
    ];

    const MODOS_TRIMESTRE = ['calendario', 'ingreso'];

    // =====================================================================
    // Cálculo puro
    // =====================================================================

    /** Años, meses y días entre dos fechas (tiempo laboral del FORMATO). */
    public static function tiempoServicio(string $desde, string $hasta): array
    {
        try {
            $a = new DateTime($desde);
            $b = new DateTime($hasta);
        } catch (Exception $e) {
            return ['anios' => 0, 'meses' => 0, 'dias' => 0];
        }
        if ($b < $a) return ['anios' => 0, 'meses' => 0, 'dias' => 0];
        $d = $a->diff($b);
        return ['anios' => $d->y, 'meses' => $d->m, 'dias' => $d->d];
    }

    /** Meses COMPLETOS entre dos fechas (0 si la segunda es anterior). */
    public static function mesesCompletos(string $desde, string $hasta): int
    {
        try {
            $a = new DateTime($desde);
            $b = (new DateTime($hasta))->modify('+1 day');   // el día de egreso cuenta
        } catch (Exception $e) { return 0; }
        if ($b <= $a) return 0;
        $d = $a->diff($b);
        return $d->y * 12 + $d->m;
    }

    /**
     * Días de garantía que se depositan en un mes (columna «5 DÍAS PREST.
     * SOCIALES» de INTERESES): los 15 del trimestre (art. 142 a) más los
     * adicionales del aniversario (art. 142 b).
     *
     * `$modo`:
     *   · 'calendario' — como la hoja del cliente: se depositan en marzo, junio,
     *     septiembre y diciembre; el primer trimestre, si está incompleto, da 5
     *     días por mes trabajado.
     *   · 'ingreso'    — cada 3 meses contados desde el mes de ingreso.
     * En el mes del egreso se deposita lo que falte del trimestre en curso.
     */
    public static function diasDeposito(string $ingreso, string $mes, string $egreso, ?array $params = null): float
    {
        $p = self::p($params);
        $modo = $p[self::CLAVE_MODO_TRIMESTRE];
        $porMes = (float)$p['liq_dias_garantia_trimestre'] / 3;          // 15 → 5 por mes
        $ing = new DateTime($ingreso);
        $m   = new DateTime(substr($mes, 0, 7) . '-01');
        $egr = new DateTime($egreso);

        $k = ((int)$m->format('Y') - (int)$ing->format('Y')) * 12
           + ((int)$m->format('n') - (int)$ing->format('n'));          // 0 = mes de ingreso
        if ($k < 0 || $m > $egr) return 0.0;

        $esMesEgreso = $m->format('Y-m') === $egr->format('Y-m');
        $mesNum = (int)$m->format('n');
        $dias = 0.0;

        if ($modo === 'ingreso') {
            $enTrimestre = ($k % 3) + 1;                                // 1, 2 o 3
            if ($enTrimestre === 3)    $dias += 3 * $porMes;
            elseif ($esMesEgreso)      $dias += $enTrimestre * $porMes;
        } else {
            $inicioTrim = $mesNum - (($mesNum - 1) % 3);                // 1, 4, 7 o 10
            $mesesTrim  = min($mesNum - $inicioTrim + 1, $k + 1);       // meses trabajados del trimestre hasta este
            if (in_array($mesNum, self::MESES_FIN_TRIMESTRE, true) || $esMesEgreso) {
                $dias += $mesesTrim * $porMes;
            }
        }

        // Aniversario: al cumplir el año n (n >= «desde», 2 por defecto) se depositan
        // «por año» × (n − desde + 1) días, con tope. Con 2/2/30: año 2 → 2, año 3 → 4…
        if ($k >= 12 && $k % 12 === 0) {
            $n = intdiv($k, 12);
            $desde = (int)$p['liq_dias_adicionales_desde_anio'];
            $aniversario = (clone $ing)->modify('+' . $n . ' years');
            if ($n >= $desde && $aniversario <= $egr) {
                $dias += min((float)$p['liq_dias_adicionales_anio'] * ($n - $desde + 1),
                             (float)$p['liq_dias_adicionales_tope']);
            }
        }
        return round($dias, 2);
    }

    /**
     * Un renglón de la hoja INTERESES. FUNCIÓN PURA.
     *
     *   G total mensual   = B+C+D+E+F
     *   H salario diario  = G / 30
     *   I alíc. bono vac. = H × días_bono_vac / 360
     *   J alíc. fin año   = H × días_aguinaldo / 360
     *   K salario integral= H + I + J
     *   M depósito        = K × L (días)
     *   N saldo           = saldo anterior + M − R (anticipo)
     *   P interés         = N × O %   (O = tasa anual del BCV / 12)
     *   Q saldo final     = N + P
     *
     * `$tasaMensual` null = falta la tasa de ese mes: el interés queda en 0 y el
     * llamador lo reporta como advertencia (no se inventa una tasa).
     */
    public static function calcularMes(array $in, float $saldoAnterior, ?float $tasaMensual, ?array $params = null): array
    {
        $p = self::p($params);
        $n = fn($k) => (float)($in[$k] ?? 0);
        $diasMes  = (float)$p['nomina_dias_mes'] ?: 30;
        $diasAnio = (float)$p['nomina_dias_base_anio'] ?: 360;

        $total    = $n('salario_base') + $n('prima_profesional') + $n('prima_antiguedad')
                  + $n('bono_transporte') + $n('bono_hijos');
        $diario   = $total / $diasMes;
        $alicVac  = $diario * $n('dias_alic_vac') / $diasAnio;
        $alicFin  = $diario * $n('dias_alic_fin') / $diasAnio;
        $integral = $diario + $alicVac + $alicFin;
        $deposito = $integral * $n('dias_deposito');
        $antes    = $saldoAnterior + $deposito - $n('anticipo');
        $interes  = $tasaMensual === null ? 0.0 : $antes * $tasaMensual / 100;
        $saldo    = $antes + $interes;

        return [
            'total_mensual'       => $total,
            'salario_diario'      => $diario,
            'alic_vac'            => $alicVac,
            'alic_fin'            => $alicFin,
            'salario_integral'    => $integral,
            'monto_deposito'      => $deposito,
            'saldo_antes_interes' => $antes,
            'tasa_mensual'        => $tasaMensual,
            'interes'             => $interes,
            'saldo'               => $saldo,
        ];
    }

    /**
     * La hoja INTERESES completa. `$meses` = lista ordenada de renglones con
     * 'mes' (AAAA-MM-01) y los CAMPOS_MES; `$tasasAnuales` = [AAAA-MM => % anual].
     * El saldo se encadena SIN redondear, como en Excel.
     */
    public static function calcularMeses(array $meses, array $tasasAnuales, ?array $params = null): array
    {
        $p = self::p($params);
        $saldo = 0.0;
        $out = [];
        $tot = ['dias' => 0.0, 'depositos' => 0.0, 'intereses' => 0.0, 'anticipos' => 0.0, 'sin_tasa' => []];
        foreach ($meses as $m) {
            $per = substr((string)$m['mes'], 0, 7);
            $anual = $tasasAnuales[$per] ?? null;
            $tasaMensual = $anual === null ? null : (float)$anual / 12;
            $c = self::calcularMes($m, $saldo, $tasaMensual, $p);
            $saldo = $c['saldo'];

            $tot['dias']      += (float)($m['dias_deposito'] ?? 0);
            $tot['depositos'] += $c['monto_deposito'];
            $tot['intereses'] += $c['interes'];
            $tot['anticipos'] += (float)($m['anticipo'] ?? 0);
            // Solo importa la tasa cuando hay saldo sobre el cual aplicarla.
            if ($anual === null && abs($c['saldo_antes_interes']) > 0.0001) $tot['sin_tasa'][] = $per;

            // Lo calculado manda sobre lo leído: los renglones que vienen de la BD
            // traen las derivadas del cálculo anterior (o 0), y con `$m + $c` esas
            // claves viejas tapaban las nuevas — la tabla mes a mes se guardaba en 0.
            $out[] = $c + $m;
        }
        $tot['saldo'] = $saldo;
        return ['meses' => $out, 'totales' => $tot];
    }

    /** Días del retroactivo (art. 142 c): 30 por año y un año más si la fracción supera 6 meses. */
    public static function diasRetroactivo(int $anios, int $meses, int $dias, ?array $params = null): float
    {
        $p = self::p($params);
        $umbral = (int)$p['liq_meses_fraccion_retroactivo'];
        $fraccion = ($meses > $umbral || ($meses === $umbral && $dias > 0)) ? 1 : 0;
        return (float)$p['liq_dias_retroactivo_anio'] * ($anios + $fraccion);
    }

    /**
     * La hoja FORMATO. FUNCIÓN PURA. Recibe el último salario (mensual), los días
     * de las alícuotas, el tiempo de servicio, los totales de la hoja INTERESES y
     * los días de cada fracción; devuelve el bloque de salario y los conceptos.
     *
     *   H11 salario normal diario   = subtotal mensual / 30
     *   H12 alíc. bono vacacional   = H11 × días / 360
     *   H13 alíc. bonif. fin de año = H11 × días / 360
     *   H14 salario integral diario = TRUNC(H11 + H12 + H13; 2)
     */
    public static function calcularResumen(array $in, ?array $params = null): array
    {
        $p = self::p($params);
        $n = fn($k) => (float)($in[$k] ?? 0);
        $diasMes  = (float)$p['nomina_dias_mes'] ?: 30;
        $diasAnio = (float)$p['nomina_dias_base_anio'] ?: 360;

        $mensual = $n('sueldo_base') + $n('prima_profesional') + $n('prima_responsabilidad')
                 + $n('prima_antiguedad') + $n('prima_hijos') + $n('bono_transporte')
                 + $n('prima_fond') + $n('prima_discapacidad') + $n('caja_ahorro');
        $normal  = $mensual / $diasMes;
        $alicVac = $normal * $n('dias_alic_vac') / $diasAnio;
        $alicFin = $normal * $n('dias_alic_fin') / $diasAnio;
        $integral = floor(($normal + $alicVac + $alicFin) * 100 + 1e-9) / 100;   // TRUNC(…;2)

        $diasRetro  = self::diasRetroactivo((int)($in['anios'] ?? 0), (int)($in['meses'] ?? 0), (int)($in['dias'] ?? 0), $p);
        $montoRetro = $diasRetro * $integral;
        $garantia   = $n('monto_garantia');
        $aplicada   = $garantia > $montoRetro ? 'garantia' : 'retroactivo';
        $antiguedad = max($garantia, $montoRetro);

        $montoVac  = $n('dias_vacaciones') * $normal;
        $montoBono = $n('dias_bono_vac')   * $normal;
        $montoFin  = $n('dias_fin_anio')   * $normal;

        $subtotal = $antiguedad + $montoVac + $montoBono + $montoFin + $n('intereses') - $n('anticipos');

        $r = fn($v) => round((float)$v, 2);
        return [
            'subtotal_mensual'        => $r($mensual),
            'salario_normal_diario'   => $r($normal),
            'alic_vac_diaria'         => $r($alicVac),
            'alic_fin_diaria'         => $r($alicFin),
            'salario_integral_diario' => $r($integral),
            'dias_retroactivo'        => $diasRetro,
            'monto_retroactivo'       => $r($montoRetro),
            'monto_garantia'          => $r($garantia),
            'antiguedad_aplicada'     => $aplicada,
            'monto_antiguedad'        => $r($antiguedad),
            'monto_vacaciones'        => $r($montoVac),
            'monto_bono_vac'          => $r($montoBono),
            'monto_fin_anio'          => $r($montoFin),
            'intereses'               => $r($n('intereses')),
            'anticipos'               => $r($n('anticipos')),
            'subtotal'                => $r($subtotal),
        ];
    }

    /**
     * Hoja INTERES DE MORA. FUNCIÓN PURA. Interés simple sobre el subtotal por
     * cada mes entre el egreso y el pago (desde el mes siguiente al egreso hasta
     * el mes del pago, inclusive), a la tasa del mes truncada como en la hoja:
     * `TRUNC(tasa anual / 12; 2)`. Sin fecha de pago no hay mora que calcular.
     *
     * `$tasasAnuales` = [AAAA-MM => % anual].
     */
    public static function calcularMora(float $monto, string $egreso, ?string $pago, array $tasasAnuales): array
    {
        $out = ['meses' => [], 'total' => 0.0, 'sin_tasa' => []];
        if (!$pago || $monto <= 0) return $out;
        try {
            $m   = (new DateTime(substr($egreso, 0, 7) . '-01'))->modify('+1 month');
            $fin = new DateTime(substr($pago, 0, 7) . '-01');
        } catch (Exception $e) { return $out; }

        while ($m <= $fin) {
            $per = $m->format('Y-m');
            $anual = $tasasAnuales[$per] ?? null;
            if ($anual === null) {
                $out['sin_tasa'][] = $per;
            } else {
                $mensual = floor((float)$anual / 12 * 100 + 1e-9) / 100;
                $interes = $monto * $mensual / 100;
                $out['meses'][] = ['periodo' => $per, 'tasa_mensual' => $mensual, 'interes' => round($interes, 2)];
                $out['total'] += $interes;
            }
            $m->modify('+1 month');
        }
        $out['total'] = round($out['total'], 2);
        return $out;
    }

    // =====================================================================
    // Tasas del BCV (interés de prestaciones sociales)
    // =====================================================================

    public static function tasasBcv(): array
    {
        $db = new Database();
        $db->query("SELECT * FROM nomina_tasas_bcv ORDER BY periodo DESC");
        return $db->resultSet();
    }

    /** [AAAA-MM => % anual]; `$columna` = 'tasa_prestaciones' o 'tasa_activa' (mora). */
    public static function mapaTasas(string $columna = 'tasa_prestaciones'): array
    {
        $db = new Database();
        $db->query("SELECT periodo, tasa_prestaciones, tasa_activa FROM nomina_tasas_bcv");
        $out = [];
        foreach ($db->resultSet() as $t) {
            $v = $columna === 'tasa_activa' ? ($t->tasa_activa ?? $t->tasa_prestaciones) : $t->tasa_prestaciones;
            $out[$t->periodo] = (float)$v;
        }
        return $out;
    }

    public static function guardarTasaBcv(string $periodo, $prestaciones, $activa, ?string $obs, ?int $userId = null): void
    {
        $periodo = trim($periodo);
        if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
            throw new Exception('Formato de mes inválido (use AAAA-MM, ej. 2026-07).');
        }
        $p = (float)str_replace(',', '.', (string)$prestaciones);
        $a = trim((string)$activa) === '' ? null : (float)str_replace(',', '.', (string)$activa);
        if ($p <= 0 || $p > 1000) throw new Exception('La tasa de prestaciones debe ser un porcentaje anual mayor que 0 (ej. 58,35).');
        if ($a !== null && ($a < 0 || $a > 1000)) throw new Exception('La tasa activa debe ser un porcentaje anual válido.');

        $db = new Database();
        $db->query("SELECT * FROM nomina_tasas_bcv WHERE periodo = :p");
        $db->bind(':p', $periodo);
        $previo = $db->single();

        if ($previo) {
            $db->query("UPDATE nomina_tasas_bcv SET tasa_prestaciones = :tp, tasa_activa = :ta, observaciones = :o,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE periodo = :p");
        } else {
            $db->query("INSERT INTO nomina_tasas_bcv (periodo, tasa_prestaciones, tasa_activa, observaciones, created_by)
                        VALUES (:p, :tp, :ta, :o, :u)");
        }
        $db->bind(':p', $periodo);
        $db->bind(':tp', round($p, 4));
        $db->bind(':ta', $a === null ? null : round($a, 4));
        $db->bind(':o', $obs !== null && trim($obs) !== '' ? trim($obs) : null);
        $db->bind(':u', $userId);
        $db->execute();

        self::auditStatic('nomina_tasas_bcv', $previo ? 'UPDATE' : 'INSERT', (int)($previo->id ?? 0),
            $previo ? json_decode(json_encode($previo), true) : null,
            ['periodo' => $periodo, 'tasa_prestaciones' => $p, 'tasa_activa' => $a], $userId);
    }

    public static function eliminarTasaBcv(string $periodo, ?int $userId = null): void
    {
        $db = new Database();
        $db->query("SELECT * FROM nomina_tasas_bcv WHERE periodo = :p");
        $db->bind(':p', $periodo);
        $previo = $db->single();
        if (!$previo) throw new Exception('Esa tasa no existe.');
        $db->query("DELETE FROM nomina_tasas_bcv WHERE periodo = :p");
        $db->bind(':p', $periodo);
        $db->execute();
        self::auditStatic('nomina_tasas_bcv', 'DELETE', (int)$previo->id,
            json_decode(json_encode($previo), true), null, $userId);
    }

    // =====================================================================
    // Consultas
    // =====================================================================

    public static function todas(): array
    {
        $db = new Database();
        $db->query("SELECT l.*, p.cedula, p.nombre, p.apellido
                      FROM liquidaciones l
                      INNER JOIN empleados e ON l.id_empleado = e.id
                      INNER JOIN personas p  ON e.id_persona = p.id
                     ORDER BY l.created_at DESC, l.id DESC");
        return $db->resultSet();
    }

    public static function find(int $id)
    {
        $db = new Database();
        $db->query("SELECT l.*, p.cedula, p.nombre, p.apellido
                      FROM liquidaciones l
                      INNER JOIN empleados e ON l.id_empleado = e.id
                      INNER JOIN personas p  ON e.id_persona = p.id
                     WHERE l.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function meses(int $idLiquidacion): array
    {
        $db = new Database();
        $db->query("SELECT * FROM liquidacion_meses WHERE id_liquidacion = :id ORDER BY mes");
        $db->bind(':id', $idLiquidacion);
        return $db->resultSet();
    }

    public static function findMes(int $idMes)
    {
        $db = new Database();
        $db->query("SELECT * FROM liquidacion_meses WHERE id = :id");
        $db->bind(':id', $idMes);
        return $db->single();
    }

    /** Liquidación viva (no anulada) de un empleado, si existe. */
    public static function vigenteDeEmpleado(int $idEmpleado)
    {
        $db = new Database();
        $db->query("SELECT * FROM liquidaciones WHERE id_empleado = :id AND estado <> 'Anulada'
                     ORDER BY fecha_egreso DESC LIMIT 1");
        $db->bind(':id', $idEmpleado);
        return $db->single();
    }

    /** Egresados que aún no tienen liquidación viva por su último egreso. */
    public static function pendientes(): array
    {
        $db = new Database();
        $db->query("SELECT e.id, e.fecha_ingreso, e.fecha_egreso, e.motivo_egreso,
                           p.cedula, p.nombre, p.apellido, c.nombre AS cargo
                      FROM empleados e
                      INNER JOIN personas p ON e.id_persona = p.id
                      LEFT  JOIN cargos c   ON e.id_cargo = c.id
                     WHERE e.is_active = TRUE AND e.fecha_egreso IS NOT NULL
                       AND NOT EXISTS (SELECT 1 FROM liquidaciones l
                                        WHERE l.id_empleado = e.id AND l.fecha_egreso = e.fecha_egreso
                                          AND l.estado <> 'Anulada')
                     ORDER BY e.fecha_egreso DESC, p.apellido");
        return $db->resultSet();
    }

    /** El empleado con todo lo que la liquidación necesita (incluido si está egresado). */
    private static function empleado(int $idEmpleado)
    {
        $db = new Database();
        $db->query("SELECT e.*, p.id AS id_persona, p.cedula, p.nombre, p.apellido,
                           p.nivel_academico, p.codigo_grado,
                           c.nombre AS cargo, c.nivel_jerarquico, d.nombre AS departamento
                      FROM empleados e
                      INNER JOIN personas p ON e.id_persona = p.id
                      LEFT  JOIN cargos c   ON e.id_cargo = c.id
                      LEFT  JOIN departamentos d ON e.id_departamento = d.id
                     WHERE e.id = :id");
        $db->bind(':id', $idEmpleado);
        return $db->single();
    }

    /**
     * Inicio del período que se liquida: la última fecha de reingreso anterior
     * al egreso (un reingreso abre una relación nueva) o, si no hubo, el ingreso.
     */
    private static function inicioPeriodo($emp): string
    {
        $db = new Database();
        $db->query("SELECT MAX(fecha_reingreso) AS f FROM empleados_egresos
                     WHERE id_empleado = :id AND fecha_reingreso IS NOT NULL AND fecha_reingreso <= :egr");
        $db->bind(':id', (int)$emp->id);
        $db->bind(':egr', $emp->fecha_egreso);
        $r = $db->single();
        return $r && $r->f ? $r->f : $emp->fecha_ingreso;
    }

    /** Casilla del pie del FORMATO: EMPLEADO FIJO · OBRERO FIJO · CONTRATADO. */
    public static function condicion($emp): string
    {
        if (($emp->tipo_contrato ?? '') === 'Contratado') return 'Contratado';
        return ($emp->clasificacion ?? '') === 'Obrero' ? 'Obrero Fijo' : 'Empleado Fijo';
    }

    // =====================================================================
    // Ciclo de vida
    // =====================================================================

    /**
     * Abre la liquidación de un egresado: congela su ficha, siembra un renglón
     * por mes de servicio y calcula. Queda en Borrador para que Talento Humano
     * complete lo que el sistema no sabe (sueldos anteriores a su registro,
     * anticipos, tasas del BCV).
     */
    public static function crear(int $idEmpleado, ?int $userId = null): int
    {
        $emp = self::empleado($idEmpleado);
        if (!$emp) throw new Exception('El empleado no existe.');
        if (empty($emp->fecha_egreso)) {
            throw new Exception('Solo se liquida a personal egresado. Procese primero el egreso desde el expediente.');
        }
        if (self::vigenteDeEmpleado($idEmpleado) && self::vigenteDeEmpleado($idEmpleado)->fecha_egreso === $emp->fecha_egreso) {
            throw new Exception('Ese egreso ya tiene una liquidación. Ábrala o anúlela antes de crear otra.');
        }

        $inicio = self::inicioPeriodo($emp);
        $t = self::tiempoServicio($inicio, $emp->fecha_egreso);

        $db = new Database();
        $db->query("SELECT id FROM empleados_egresos WHERE id_empleado = :id AND fecha_reingreso IS NULL
                     ORDER BY id DESC LIMIT 1");
        $db->bind(':id', $idEmpleado);
        $egr = $db->single();

        $db->beginTransaction();
        try {
            $db->query("INSERT INTO liquidaciones
                        (id_empleado, id_egreso, fecha_ingreso, fecha_egreso, motivo_egreso,
                         tipo_personal, condicion, cargo, departamento, anios, meses, dias, created_by)
                        VALUES (:e, :eg, :fi, :fe, :mo, :tp, :co, :ca, :de, :a, :m, :d, :u) RETURNING id");
            $db->bind(':e', $idEmpleado);
            $db->bind(':eg', $egr ? (int)$egr->id : null);
            $db->bind(':fi', $inicio);
            $db->bind(':fe', $emp->fecha_egreso);
            $db->bind(':mo', $emp->motivo_egreso);
            $db->bind(':tp', Nomina::tipoPersonal($emp));
            $db->bind(':co', self::condicion($emp));
            $db->bind(':ca', $emp->cargo);
            $db->bind(':de', $emp->departamento);
            $db->bind(':a', $t['anios']);
            $db->bind(':m', $t['meses']);
            $db->bind(':d', $t['dias']);
            $db->bind(':u', $userId);
            $id = (int)$db->single()->id;

            self::sembrarMeses($db, $id, $emp, $inicio);
            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::recalcular($id, $userId, false);
        self::auditStatic('liquidaciones', 'INSERT', $id, null,
            ['id_empleado' => $idEmpleado, 'fecha_ingreso' => $inicio, 'fecha_egreso' => $emp->fecha_egreso], $userId);
        return $id;
    }

    /**
     * Un renglón por mes, del mes de inicio al del egreso, con lo que la ficha
     * permite reconstruir: el sueldo vigente al cierre de cada mes
     * (`empleado_salarios`), las primas derivadas con el motor de nómina y los
     * días de la garantía. Lo anterior al primer sueldo registrado queda en 0 y
     * se advierte: el sistema no inventa sueldos pasados.
     */
    private static function sembrarMeses(Database $db, int $idLiq, $emp, string $inicio): void
    {
        $params  = Nomina::params();
        $codigo = Nomina::codigoGrado($emp->nivel_academico ?? null, $emp->codigo_grado ?? null);
        $pctProf = Nomina::pctGrado($codigo);
        $hijos   = Sueldo::contarHijos((int)$emp->id_persona);
        $diasBase = (float)BonoVacacional::diasBase($emp);

        $m   = new DateTime(substr($inicio, 0, 7) . '-01');
        $fin = new DateTime(substr($emp->fecha_egreso, 0, 7) . '-01');
        while ($m <= $fin) {
            $mes   = $m->format('Y-m-01');
            $corte = min($m->format('Y-m-t'), $emp->fecha_egreso);
            $s     = Sueldo::actual((int)$emp->id, $corte);
            $base  = $s ? (float)$s->sueldo_basico : 0.0;
            $anios = Nomina::aniosAdministracion($emp, $corte);

            $db->query("INSERT INTO liquidacion_meses
                        (id_liquidacion, mes, salario_base, prima_profesional, prima_antiguedad,
                         bono_transporte, bono_hijos, dias_alic_vac, dias_alic_fin, dias_deposito, anticipo)
                        VALUES (:l, :m, :sb, :pp, :pa, :bt, :bh, :dv, :df, :dd, 0)");
            $db->bind(':l', $idLiq);
            $db->bind(':m', $mes);
            $db->bind(':sb', round($base, 2));
            $db->bind(':pp', round($base * $pctProf / 100, 2));
            $db->bind(':pa', round($base * Nomina::pctAntiguedad($anios) / 100, 2));
            $db->bind(':bt', $base > 0 ? (float)$params['nomina_bono_transporte_mensual'] : 0);
            // La prima por hijo de la nómina es quincenal; aquí va el mes completo.
            $db->bind(':bh', $base > 0 ? round($hijos * (float)$params['nomina_monto_por_hijo'] * 2, 2) : 0);
            $db->bind(':dv', $diasBase + $anios);
            // 120 días hasta 2022, 150 desde 2023: los del año de ese mes (mig. 092).
            $db->bind(':df', Nomina::diasAguinaldo((int)$m->format('Y')));
            $db->bind(':dd', self::diasDeposito($inicio, $mes, $emp->fecha_egreso));
            $db->execute();

            $m->modify('+1 month');
        }
    }

    /**
     * Recalcula todo a partir de las entradas guardadas: la hoja INTERESES con
     * las tasas del BCV vigentes, el bloque de salario desde el último renglón
     * (así una corrección de Talento Humano se propaga), las fracciones y la
     * mora. `$reconstruir` = descartar los renglones y volver a sembrarlos desde
     * la ficha (pierde las correcciones manuales).
     */
    public static function recalcular(int $id, ?int $userId = null, bool $reconstruir = false): void
    {
        $liq = self::find($id);
        if (!$liq) throw new Exception('Liquidación no encontrada.');
        if ($liq->estado !== 'Borrador') throw new Exception('La liquidación está ' . mb_strtolower($liq->estado) . ': no se puede recalcular.');

        $emp = self::empleado((int)$liq->id_empleado);
        $db  = new Database();

        if ($reconstruir) {
            $db->beginTransaction();
            try {
                $db->query("DELETE FROM liquidacion_meses WHERE id_liquidacion = :id");
                $db->bind(':id', $id);
                $db->execute();
                $emp->fecha_egreso = $liq->fecha_egreso;   // por si hubo reingreso después
                self::sembrarMeses($db, $id, $emp, $liq->fecha_ingreso);
                $db->endTransaction();
            } catch (Exception $e) {
                $db->cancelTransaction();
                throw $e;
            }
        }

        // ── Hoja INTERESES ────────────────────────────────────────────────
        $filas = array_map(fn($r) => (array)$r, self::meses($id));
        $ledger = self::calcularMeses($filas, self::mapaTasas('tasa_prestaciones'));

        $db->beginTransaction();
        try {
            foreach ($ledger['meses'] as $r) {
                $db->query("UPDATE liquidacion_meses SET
                                total_mensual = :tm, salario_diario = :sd, alic_vac = :av, alic_fin = :af,
                                salario_integral = :si, monto_deposito = :md, saldo_antes_interes = :sa,
                                tasa_mensual = :tasa, interes = :in, saldo = :sal
                             WHERE id = :id");
                $db->bind(':tm', round($r['total_mensual'], 2));
                $db->bind(':sd', round($r['salario_diario'], 4));
                $db->bind(':av', round($r['alic_vac'], 4));
                $db->bind(':af', round($r['alic_fin'], 4));
                $db->bind(':si', round($r['salario_integral'], 4));
                $db->bind(':md', round($r['monto_deposito'], 2));
                $db->bind(':sa', round($r['saldo_antes_interes'], 2));
                $db->bind(':tasa', $r['tasa_mensual'] === null ? null : round($r['tasa_mensual'], 4));
                $db->bind(':in', round($r['interes'], 2));
                $db->bind(':sal', round($r['saldo'], 2));
                $db->bind(':id', (int)$r['id']);
                $db->execute();
            }
            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }
        $tot = $ledger['totales'];

        // ── Último salario (bloque superior del FORMATO) ──────────────────
        $adv = [];
        $ultimo = end($ledger['meses']) ?: [];
        $sueldoEgr = Sueldo::actual((int)$emp->id, $liq->fecha_egreso);
        $tipo = $liq->tipo_personal;

        $primaResp = 0.0;
        if (Nomina::cobraResponsabilidad($emp)) {   // solo alto nivel (2026-10-11)
            $divisas = (float)($emp->divisas_bono_responsabilidad ?? 0);
            $pm = Nomina::parametrosMes(substr($liq->fecha_egreso, 0, 7));
            if ($divisas > 0 && !$pm) {
                $adv[] = 'Falta la tasa del dólar de ' . substr($liq->fecha_egreso, 0, 7) . ' para la prima de responsabilidad (se paga en divisas): cárguela en Nómina → Parámetros.';
            }
            $primaResp = $pm ? $divisas * (float)$pm->tasa_dolar : 0.0;
        }

        $salario = [
            'sueldo_base'           => (float)($ultimo['salario_base'] ?? 0),
            'prima_profesional'     => (float)($ultimo['prima_profesional'] ?? 0),
            'prima_responsabilidad' => round($primaResp, 2),
            'prima_antiguedad'      => (float)($ultimo['prima_antiguedad'] ?? 0),
            'prima_hijos'           => (float)($ultimo['bono_hijos'] ?? 0),
            'bono_transporte'       => (float)($ultimo['bono_transporte'] ?? 0),
            'prima_fond'            => $sueldoEgr ? (float)($sueldoEgr->prima_fond ?? 0) : 0.0,
            'prima_discapacidad'    => $sueldoEgr ? (float)($sueldoEgr->prima_discapacidad ?? 0) : 0.0,
            'caja_ahorro'           => 0.0,   // la gobernación no la paga (audio 3)
        ];

        // ── Fracciones ────────────────────────────────────────────────────
        $fr = self::fracciones($emp, $liq->fecha_ingreso, $liq->fecha_egreso,
                               (float)($ultimo['dias_alic_vac'] ?? 0), (float)($ultimo['dias_alic_fin'] ?? 0));
        $adv = array_merge($adv, $fr['advertencias']);
        $diasVac  = $liq->dias_vacaciones_manual !== null ? (float)$liq->dias_vacaciones_manual : $fr['vacaciones'];
        $diasBono = $liq->dias_bono_vac_manual   !== null ? (float)$liq->dias_bono_vac_manual   : $fr['bono_vac'];
        $diasFin  = $liq->dias_fin_anio_manual   !== null ? (float)$liq->dias_fin_anio_manual   : $fr['fin_anio'];

        $res = self::calcularResumen($salario + [
            'dias_alic_vac'   => (float)($ultimo['dias_alic_vac'] ?? 0),
            'dias_alic_fin'   => (float)($ultimo['dias_alic_fin'] ?? 0),
            'anios'           => (int)$liq->anios,
            'meses'           => (int)$liq->meses,
            'dias'            => (int)$liq->dias,
            'monto_garantia'  => $tot['depositos'],
            'intereses'       => $tot['intereses'],
            'anticipos'       => $tot['anticipos'],
            'dias_vacaciones' => $diasVac,
            'dias_bono_vac'   => $diasBono,
            'dias_fin_anio'   => $diasFin,
        ]);

        $mora = self::calcularMora($res['subtotal'], $liq->fecha_egreso, $liq->fecha_pago, self::mapaTasas('tasa_activa'));
        $total = round($res['subtotal'] + $mora['total'], 2);

        // ── Advertencias ──────────────────────────────────────────────────
        $sinSueldo = array_values(array_filter($ledger['meses'], fn($r) => (float)$r['salario_base'] <= 0));
        if ($sinSueldo) {
            $adv[] = count($sinSueldo) . ' mes(es) sin sueldo registrado (' . self::rangoMeses(array_column($sinSueldo, 'mes'))
                   . '): el sistema solo conoce los sueldos cargados en el historial salarial. Complételos en la tabla mensual.';
        }
        if ($tot['sin_tasa']) {
            $adv[] = 'Falta la tasa del BCV de ' . count($tot['sin_tasa']) . ' mes(es) (' . self::rangoMeses($tot['sin_tasa'])
                   . '): sus intereses quedaron en 0. Cárguelas en Nómina → Parámetros.';
        }
        if ($mora['sin_tasa']) {
            $adv[] = 'Falta la tasa del BCV de ' . self::rangoMeses($mora['sin_tasa']) . ' para el interés de mora.';
        }
        if (min(array_merge([0.0], array_map(fn($r) => (float)$r['saldo'], $ledger['meses']))) < -0.005) {
            $adv[] = 'En algún mes los anticipos superan lo depositado (saldo negativo). Revise la columna de anticipos.';
        }
        if ($tipo === 'Comisión de Servicio') {
            $adv[] = 'Trabajador en comisión de servicio: confirme si sus prestaciones las paga IMATUR o la institución de origen.';
        }
        if (empty($emp->fecha_ingreso_administracion)) {
            $adv[] = 'Sin fecha de ingreso a la administración pública: los días de bono vacacional y la prima de antigüedad se calcularon desde el ingreso a IMATUR.';
        }

        $db->query("UPDATE liquidaciones SET
                        sueldo_base = :sb, prima_profesional = :pp, prima_responsabilidad = :pr,
                        prima_antiguedad = :pa, prima_hijos = :ph, bono_transporte = :bt,
                        prima_fond = :pf, prima_discapacidad = :pd, caja_ahorro = :ca,
                        subtotal_mensual = :stm, salario_normal_diario = :snd,
                        dias_alic_vac = :dav, alic_vac_diaria = :avd, dias_alic_fin = :daf, alic_fin_diaria = :afd,
                        salario_integral_diario = :sid,
                        dias_retroactivo = :dr, monto_retroactivo = :mr, dias_garantia = :dg, monto_garantia = :mg,
                        antiguedad_aplicada = :aa, monto_antiguedad = :ma,
                        dias_vacaciones = :dvac, monto_vacaciones = :mvac,
                        dias_bono_vac = :dbv, monto_bono_vac = :mbv,
                        dias_fin_anio = :dfa, monto_fin_anio = :mfa,
                        intereses = :int, anticipos = :ant, subtotal = :sub,
                        interes_mora = :im, detalle_mora = :dm, total = :tot,
                        advertencias = :adv, updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':sb', $salario['sueldo_base']);
        $db->bind(':pp', $salario['prima_profesional']);
        $db->bind(':pr', $salario['prima_responsabilidad']);
        $db->bind(':pa', $salario['prima_antiguedad']);
        $db->bind(':ph', $salario['prima_hijos']);
        $db->bind(':bt', $salario['bono_transporte']);
        $db->bind(':pf', $salario['prima_fond']);
        $db->bind(':pd', $salario['prima_discapacidad']);
        $db->bind(':ca', $salario['caja_ahorro']);
        $db->bind(':stm', $res['subtotal_mensual']);
        $db->bind(':snd', $res['salario_normal_diario']);
        $db->bind(':dav', (float)($ultimo['dias_alic_vac'] ?? 0));
        $db->bind(':avd', $res['alic_vac_diaria']);
        $db->bind(':daf', (float)($ultimo['dias_alic_fin'] ?? 0));
        $db->bind(':afd', $res['alic_fin_diaria']);
        $db->bind(':sid', $res['salario_integral_diario']);
        $db->bind(':dr', $res['dias_retroactivo']);
        $db->bind(':mr', $res['monto_retroactivo']);
        $db->bind(':dg', round($tot['dias'], 2));
        $db->bind(':mg', $res['monto_garantia']);
        $db->bind(':aa', $res['antiguedad_aplicada']);
        $db->bind(':ma', $res['monto_antiguedad']);
        $db->bind(':dvac', round($diasVac, 2));
        $db->bind(':mvac', $res['monto_vacaciones']);
        $db->bind(':dbv', round($diasBono, 2));
        $db->bind(':mbv', $res['monto_bono_vac']);
        $db->bind(':dfa', round($diasFin, 2));
        $db->bind(':mfa', $res['monto_fin_anio']);
        $db->bind(':int', $res['intereses']);
        $db->bind(':ant', $res['anticipos']);
        $db->bind(':sub', $res['subtotal']);
        $db->bind(':im', $mora['total']);
        $db->bind(':dm', json_encode($mora['meses']));
        $db->bind(':tot', $total);
        $db->bind(':adv', $adv ? implode(' · ', $adv) : null);
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        $db->execute();
    }

    /**
     * Días de las tres fracciones del FORMATO, calculados desde la ficha:
     *   · Vacaciones (art. 190): días ganados y no disfrutados según el módulo
     *     Vacaciones + la fracción del año en curso (15 + años, tope 30, × meses/12).
     *   · Bono vacacional (art. 192): (días base + años) × meses del año en curso / 12.
     *   · Bonificación de fin de año: días del aguinaldo × meses del año civil / 12.
     * Son una propuesta: Talento Humano puede corregir cada uno.
     */
    private static function fracciones($emp, string $inicio, string $egreso, float $diasBonoVac, float $diasFin): array
    {
        $adv = [];
        $base = Vacacion::fechaBaseServicio($emp) ?: $inicio;
        $anios = Vacacion::aniosServicio($emp, $egreso);
        $ultimoAniversario = (new DateTime($base))->modify('+' . $anios . ' years')->format('Y-m-d');
        $mesesAnio = self::mesesCompletos($ultimoAniversario, $egreso);

        // Vacaciones vencidas y no disfrutadas (lo que dice el módulo Vacaciones).
        $pendiente = Vacacion::derechoAcumulado($emp, $egreso)
                   - (int)($emp->vacaciones_ajuste_dias ?? 0)
                   - Vacacion::totalDisfrutado((int)$emp->id);
        $pendiente = max(0, $pendiente);
        if ($pendiente > 0) {
            $adv[] = 'Incluye ' . $pendiente . ' día(s) de vacaciones vencidas no disfrutadas según el módulo Vacaciones: verifíquelos (si se disfrutaron antes del sistema, cárguelos como ajuste inicial).';
        }
        $vac = $pendiente + Vacacion::diasPorAnios($anios) * $mesesAnio / 12;

        // El bono vacacional del año en curso, proporcional.
        $bono = $diasBonoVac * $mesesAnio / 12;

        // Bonificación de fin de año: meses del año civil del egreso.
        $enero = max(substr($egreso, 0, 4) . '-01-01', $inicio);
        $fin = $diasFin * self::mesesCompletos($enero, $egreso) / 12;

        return [
            'vacaciones'   => round($vac, 2),
            'bono_vac'     => round($bono, 2),
            'fin_anio'     => round($fin, 2),
            'advertencias' => $adv,
        ];
    }

    /** "2024-01 a 2024-06" para no listar cien meses en una advertencia. */
    private static function rangoMeses(array $meses): string
    {
        $meses = array_map(fn($m) => substr((string)$m, 0, 7), $meses);
        sort($meses);
        $a = reset($meses); $b = end($meses);
        return $a === $b ? $a : $a . ' a ' . $b;
    }

    /** Corrige la cabecera (fecha de pago, días de las fracciones, observaciones) y recalcula. */
    public static function guardarCabecera(int $id, array $d, ?int $userId = null): void
    {
        $liq = self::find($id);
        if (!$liq) throw new Exception('Liquidación no encontrada.');
        if ($liq->estado !== 'Borrador') throw new Exception('Solo se edita una liquidación en Borrador.');

        $pago = trim($d['fecha_pago'] ?? '');
        if ($pago !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $pago)) throw new Exception('Fecha de pago inválida.');
        if ($pago !== '' && $pago < $liq->fecha_egreso) throw new Exception('La fecha de pago no puede ser anterior al egreso.');

        $dias = function ($v) {
            $v = trim((string)$v);
            if ($v === '') return null;                     // vacío = usar el cálculo del sistema
            $f = (float)str_replace(',', '.', $v);
            if ($f < 0 || $f > 2000) throw new Exception('Los días deben ser un número entre 0 y 2000.');
            return round($f, 2);
        };

        $db = new Database();
        $db->query("UPDATE liquidaciones SET fecha_pago = :p, dias_vacaciones_manual = :dv,
                           dias_bono_vac_manual = :db, dias_fin_anio_manual = :df, observaciones = :o,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u WHERE id = :id");
        $db->bind(':p', $pago ?: null);
        $db->bind(':dv', $dias($d['dias_vacaciones_manual'] ?? ''));
        $db->bind(':db', $dias($d['dias_bono_vac_manual'] ?? ''));
        $db->bind(':df', $dias($d['dias_fin_anio_manual'] ?? ''));
        $db->bind(':o', trim($d['observaciones'] ?? '') ?: null);
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        $db->execute();

        self::auditStatic('liquidaciones', 'UPDATE', $id, null,
            array_intersect_key($d, array_flip(['fecha_pago', 'dias_vacaciones_manual', 'dias_bono_vac_manual', 'dias_fin_anio_manual'])), $userId);
        self::recalcular($id, $userId);
    }

    /** Corrige un renglón mensual (hoja INTERESES) y recalcula la cadena completa. */
    public static function guardarMes(int $idMes, array $d, ?int $userId = null): int
    {
        $mes = self::findMes($idMes);
        if (!$mes) throw new Exception('Renglón no encontrado.');
        $liq = self::find((int)$mes->id_liquidacion);
        if (!$liq || $liq->estado !== 'Borrador') throw new Exception('Solo se edita una liquidación en Borrador.');

        $vals = [];
        foreach (self::CAMPOS_MES as $c) {
            $v = (float)str_replace(',', '.', (string)($d[$c] ?? $mes->{$c}));
            if ($v < 0) throw new Exception('Los montos y días no pueden ser negativos.');
            $vals[$c] = round($v, 2);
        }

        $db = new Database();
        $set = implode(', ', array_map(fn($c) => "$c = :$c", self::CAMPOS_MES));
        $db->query("UPDATE liquidacion_meses SET $set, editado = TRUE WHERE id = :id");
        foreach ($vals as $c => $v) $db->bind(':' . $c, $v);
        $db->bind(':id', $idMes);
        $db->execute();

        self::auditStatic('liquidacion_meses', 'UPDATE', $idMes,
            array_intersect_key((array)$mes, $vals), $vals, $userId);
        self::recalcular((int)$liq->id, $userId);
        return (int)$liq->id;
    }

    /** Cierra la liquidación: le da número y la vuelve inmutable. */
    public static function cerrar(int $id, ?int $userId = null): string
    {
        $liq = self::find($id);
        if (!$liq) throw new Exception('Liquidación no encontrada.');
        if ($liq->estado !== 'Borrador') throw new Exception('La liquidación ya estaba ' . mb_strtolower($liq->estado) . '.');

        self::recalcular($id, $userId);   // cierra con los números del momento
        $numero = 'LIQ-' . ConfigSistema::generarNumeroOficio('liquidacion');

        $db = new Database();
        $db->query("UPDATE liquidaciones SET estado = 'Cerrada', numero = :n,
                           cerrada_at = CURRENT_TIMESTAMP, cerrada_by = :u
                     WHERE id = :id AND estado = 'Borrador'");
        $db->bind(':n', $numero);
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        $db->execute();

        self::auditStatic('liquidaciones', 'UPDATE', $id, ['estado' => 'Borrador'],
            ['estado' => 'Cerrada', 'numero' => $numero], $userId);
        return $numero;
    }

    /** Anula (no borra): conserva el documento y su número, con el motivo. */
    public static function anular(int $id, string $motivo, ?int $userId = null): void
    {
        $motivo = trim($motivo);
        if (mb_strlen($motivo) < 5) throw new Exception('Indique el motivo de la anulación.');
        $liq = self::find($id);
        if (!$liq) throw new Exception('Liquidación no encontrada.');
        if ($liq->estado === 'Anulada') throw new Exception('La liquidación ya estaba anulada.');

        $db = new Database();
        $db->query("UPDATE liquidaciones SET estado = 'Anulada', motivo_anulacion = :m,
                           anulada_at = CURRENT_TIMESTAMP, anulada_by = :u WHERE id = :id");
        $db->bind(':m', $motivo);
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        $db->execute();

        self::auditStatic('liquidaciones', 'UPDATE', $id, ['estado' => $liq->estado],
            ['estado' => 'Anulada', 'motivo_anulacion' => $motivo], $userId);
    }
}
