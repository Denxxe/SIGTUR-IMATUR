<?php

/**
 * Nomina — motor de cálculo de la nómina quincenal (fase N-A del plan).
 *
 * Fuente de las fórmulas: `docs/PLAN_MODULO_NOMINA.md` §3, extraído de las
 * FÓRMULAS de la plantilla real del cliente (`INSTITUTO IMATUR JULIO 2026.xlsx`,
 * datos de prueba pero fórmulas reales). **Leer ese documento antes de tocar
 * este archivo.** Ningún número de aquí salió de un audio.
 *
 * Diseño:
 *
 *  · `calcular()` es **pura**: recibe todas las entradas explícitas y devuelve
 *    los resultados, sin tocar la BD. Así se puede probar contra los valores
 *    ya calculados de la plantilla (ver tests/run.php).
 *  · `entradasEmpleado()` resuelve esas entradas desde la ficha del empleado y
 *    devuelve además las **advertencias** de lo que no pudo resolver.
 *  · `calcularQuincena()` une las dos.
 *
 * Los porcentajes NO son constantes PHP: viven en `nomina_grados` y
 * `nomina_antiguedad`, y los escalares en `configuracion_sistema`. Son
 * parámetros de contratación colectiva, no valores de dominio del software.
 *
 * Redondeo: los intermedios se calculan SIN redondear (como hace Excel) y
 * solo se redondea a 2 decimales cada valor de salida. Redondear en cascada
 * desviaría los totales respecto de la plantilla del cliente.
 */
class Nomina extends Model
{
    /** Los 5 tipos de personal = las 5 hojas del formato oficial. */
    const TIPOS = ['Alto Nivel', 'Empleados Fijos', 'Obreros Fijos', 'Contratados', 'Comisión de Servicio'];

    /** Tipos que cobran bono de responsabilidad en divisas. */
    const TIPOS_CON_RESPONSABILIDAD = ['Alto Nivel', 'Comisión de Servicio'];

    /** Factor semanal de la base de SSO/LRPPF y aportes: 12 meses / 52 semanas. */
    const FACTOR_SEMANAL = 12 / 52;

    /** Divisor del sueldo normal diario (convención venezolana: mes de 30 días). */
    const DIAS_MES = 30;

    /**
     * Mapeo `personas.nivel_academico` (varchar libre) → código de grado.
     *
     * Lo que NO está aquí devuelve null a propósito: el empleado se reporta
     * como pendiente en vez de cobrar 0 % en silencio. Ese silencio es el
     * defecto #7 de la plantilla del cliente (compara contra "BACH" pero
     * teclea "BCH", y cualquier variante mal escrita cae al else con 0 %).
     */
    const MAPEO_GRADO = [
        // Por debajo de bachiller y bachillerato: 0 %
        'primaria'      => 'BACH',
        '1er año'       => 'BACH',
        '2do año'       => 'BACH',
        '3er año'       => 'BACH',
        '4to año'       => 'BACH',
        'bachiller'     => 'BACH',
        'técnico medio' => 'BACH',
        'tecnico medio' => 'BACH',
        // Técnico superior
        'tsu'                             => 'TSU',
        'técnico superior universitario'  => 'TSU',
        'tecnico superior universitario'  => 'TSU',
        // Profesional
        'licenciado'   => 'PROF',
        'licenciada'   => 'PROF',
        'ingeniero'    => 'PROF',
        'ingeniera'    => 'PROF',
        'abogado'      => 'PROF',
        'abogada'      => 'PROF',
        'profesional'  => 'PROF',
        // Postgrado
        'especialización' => 'ESP',
        'especializacion' => 'ESP',
        'especialista'    => 'ESP',
        'magíster'        => 'MAEST',
        'magister'        => 'MAEST',
        'maestría'        => 'MAEST',
        'maestria'        => 'MAEST',
        'doctorado'       => 'DR',
        'doctor'          => 'DR',
        'doctora'         => 'DR',
    ];

    // =====================================================================
    // Parámetros
    // =====================================================================

    /** Caché por request de los escalares de configuración. */
    private static ?array $cacheParams = null;

    /**
     * Escalares del cálculo, leídos de `configuracion_sistema`. Los defaults
     * replican la plantilla y solo actúan si falta la clave.
     */
    public static function params(): array
    {
        if (self::$cacheParams !== null) return self::$cacheParams;

        $defaults = [
            'nomina_bono_transporte_mensual' => 12.50,
            'nomina_monto_por_hijo'          => 6.50,
            'nomina_becas_por_hijo'          => 12.50,
            'nomina_semanas_default'         => 4,
            'nomina_pct_sso_trabajador'      => 2,
            'nomina_pct_faov_trabajador'     => 1,
            'nomina_pct_lrppf_trabajador'    => 0.5,
            'nomina_pct_sso_patronal'        => 4,
            'nomina_pct_faov_patronal'       => 2,
            'nomina_pct_rpe_patronal'        => 1.7,
            'nomina_dias_bono_fin_anio'      => 150,
            'nomina_dias_base_anio'          => 360,
            'nomina_dias_bono_vac_base'      => 75,
        ];

        $out = [];
        foreach ($defaults as $clave => $default) {
            $valor = ConfigSistema::get($clave);
            $out[$clave] = ($valor === '' || $valor === null) ? $default : (float)$valor;
        }
        self::$cacheParams = $out;
        return $out;
    }

    /** Invalida la caché de parámetros (usar tras editarlos en /config). */
    public static function invalidarCache(): void { self::$cacheParams = null; }

    /** Catálogo de grados: [codigo => ['nombre'=>…, 'porcentaje'=>float]]. */
    public static function grados(): array
    {
        $db = new Database();
        $db->query("SELECT codigo, nombre, porcentaje FROM nomina_grados
                     WHERE is_active = TRUE ORDER BY orden, codigo");
        $out = [];
        foreach ($db->resultSet() as $r) {
            $out[$r->codigo] = ['nombre' => $r->nombre, 'porcentaje' => (float)$r->porcentaje];
        }
        return $out;
    }

    /** % de prima de profesionalización de un código de grado (0 si no existe). */
    public static function pctGrado(?string $codigo): float
    {
        if (!$codigo) return 0.0;
        $db = new Database();
        $db->query("SELECT porcentaje FROM nomina_grados WHERE codigo = :c AND is_active = TRUE");
        $db->bind(':c', $codigo);
        $row = $db->single();
        return $row ? (float)$row->porcentaje : 0.0;
    }

    /**
     * % de prima de antigüedad por años en la administración pública.
     * A partir del año marcado como tope se congela (30 %).
     */
    public static function pctAntiguedad(int $anios): float
    {
        if ($anios < 1) return 0.0;
        $db = new Database();
        $db->query("SELECT porcentaje FROM nomina_antiguedad
                     WHERE anios <= :a ORDER BY anios DESC LIMIT 1");
        $db->bind(':a', $anios);
        $row = $db->single();
        return $row ? (float)$row->porcentaje : 0.0;
    }

    /** Escala de antigüedad completa (para mostrarla y editarla). */
    public static function escalaAntiguedad(): array
    {
        $db = new Database();
        $db->query("SELECT anios, porcentaje, es_tope FROM nomina_antiguedad ORDER BY anios");
        return $db->resultSet();
    }

    /**
     * Parámetros del mes (cesta ticket y tasa del dólar). Devuelve null si el
     * mes no está cargado — el generador lo trata como insumo faltante en vez
     * de asumir 0, que produciría alícuotas mal calculadas sin avisar.
     */
    public static function parametrosMes(string $periodo)
    {
        $db = new Database();
        $db->query("SELECT * FROM nomina_parametros_mes WHERE periodo = :p");
        $db->bind(':p', $periodo);
        return $db->single();
    }

    public static function parametrosMesTodos(): array
    {
        $db = new Database();
        $db->query("SELECT * FROM nomina_parametros_mes ORDER BY periodo DESC");
        return $db->resultSet();
    }

    /** Alta/edición de los parámetros de un mes (upsert por período). */
    public static function guardarParametrosMes(string $periodo, float $cesta, float $tasa, ?string $obs, ?int $userId = null): bool
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            throw new Exception('Formato de período inválido (use AAAA-MM, ej. 2026-08).');
        }
        if ($cesta < 0 || $tasa < 0) throw new Exception('Los montos no pueden ser negativos.');

        $previo = self::parametrosMes($periodo);
        $db = new Database();
        if ($previo) {
            $db->query("UPDATE nomina_parametros_mes
                           SET monto_cesta_ticket = :c, tasa_dolar = :t, observaciones = :o,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :u
                         WHERE periodo = :p");
        } else {
            $db->query("INSERT INTO nomina_parametros_mes (periodo, monto_cesta_ticket, tasa_dolar, observaciones, created_by)
                        VALUES (:p, :c, :t, :o, :u)");
        }
        $db->bind(':p', $periodo);
        $db->bind(':c', round($cesta, 2));
        $db->bind(':t', round($tasa, 4));
        $db->bind(':o', $obs !== null && trim($obs) !== '' ? trim($obs) : null);
        $db->bind(':u', $userId);
        $result = $db->execute();

        self::auditStatic('nomina_parametros_mes', $previo ? 'UPDATE' : 'INSERT',
            (int)($previo->id ?? 0), $previo ? self::toArrayStatic($previo) : null,
            ['periodo' => $periodo, 'monto_cesta_ticket' => $cesta, 'tasa_dolar' => $tasa], $userId);
        return $result;
    }

    /** Helper: stdClass de PDO → array, para AuditLog (que exige ?array). */
    private static function toArrayStatic($obj): ?array
    {
        return $obj ? json_decode(json_encode($obj), true) : null;
    }

    // =====================================================================
    // Derivaciones desde la ficha
    // =====================================================================

    /**
     * Tipo de personal (= hoja del formato). Son 5, no 4: la mig. 072 sumó
     * Comisión de Servicio, que tiene hoja y cálculo propios.
     *
     * **Comisión de Servicio tiene prioridad** sobre el nivel jerárquico: su
     * hoja calcula la DIFERENCIA contra lo que paga la dependencia de origen,
     * así que un director en comisión va a esa hoja, no a Alto Nivel.
     *
     * Todo se deriva de datos que ya existen (mig. 025), sin captura nueva.
     */
    public static function tipoPersonal($empleado): string
    {
        if (($empleado->institucion_origen ?? 'IMATUR') !== 'IMATUR') return 'Comisión de Servicio';

        // `Empleado::find()` aliasa `cargos.nivel_jerarquico` como `nivel_cargo`,
        // mientras las consultas de nómina lo traen con su nombre. Se aceptan los
        // dos para que el tipo no salga mal según por dónde venga el empleado.
        $nivel = $empleado->nivel_jerarquico ?? ($empleado->nivel_cargo ?? null);
        if (in_array($nivel, ['Presidencia', 'Dirección'], true)) return 'Alto Nivel';
        if (($empleado->tipo_contrato ?? '') === 'Contratado')     return 'Contratados';
        if (($empleado->clasificacion ?? '') === 'Obrero')         return 'Obreros Fijos';
        return 'Empleados Fijos';
    }

    /**
     * Código de grado de instrucción. Prioridad: la corrección manual
     * (`personas.codigo_grado`) y, si no hay, el mapeo de `nivel_academico`.
     * Devuelve null si no reconoce el valor — el llamador debe advertirlo,
     * NO asumir 0 %.
     */
    public static function codigoGrado(?string $nivelAcademico, ?string $override = null): ?string
    {
        if ($override !== null && trim($override) !== '') return strtoupper(trim($override));
        if ($nivelAcademico === null || trim($nivelAcademico) === '') return null;

        $clave = mb_strtolower(trim($nivelAcademico), 'UTF-8');
        return self::MAPEO_GRADO[$clave] ?? null;
    }

    /** Años cumplidos en la administración pública a una fecha de corte. */
    public static function aniosAdministracion($empleado, ?string $fechaCorte = null): int
    {
        $base = !empty($empleado->fecha_ingreso_administracion)
            ? $empleado->fecha_ingreso_administracion
            : ($empleado->fecha_ingreso ?? null);
        if (!$base) return 0;
        try {
            $ini   = new \DateTime($base);
            $corte = new \DateTime($fechaCorte ?: 'today');
        } catch (\Exception $e) { return 0; }
        if ($corte < $ini) return 0;
        return (int)$ini->diff($corte)->y;
    }

    /**
     * Reúne las entradas del cálculo para un empleado y lista lo que no pudo
     * resolver. Devuelve ['entradas' => [...], 'advertencias' => [...]].
     */
    public static function entradasEmpleado($empleado, string $fechaCorte, float $cesta, float $tasa, int $semanas): array
    {
        $adv    = [];
        $params = self::params();

        $sueldo = Sueldo::actual((int)$empleado->id, $fechaCorte);
        $base   = $sueldo ? (float)$sueldo->sueldo_basico : 0.0;
        if (!$sueldo)        $adv[] = 'Sin sueldo registrado en el historial salarial.';
        elseif ($base <= 0)  $adv[] = 'El sueldo básico registrado es 0.';

        $codigo = self::codigoGrado($empleado->nivel_academico ?? null, $empleado->codigo_grado ?? null);
        if ($codigo === null) {
            $adv[] = empty($empleado->nivel_academico)
                ? 'Sin grado de instrucción registrado: la prima de profesionalización queda en 0.'
                : 'Grado de instrucción no reconocido ("' . $empleado->nivel_academico . '"): corregirlo en la ficha para que la prima de profesionalización se calcule.';
        }

        $anios = self::aniosAdministracion($empleado, $fechaCorte);
        if (empty($empleado->fecha_ingreso_administracion)) {
            $adv[] = 'Sin fecha de ingreso a la administración pública: la antigüedad se calcula con la fecha de ingreso a IMATUR.';
        }

        $tipo = self::tipoPersonal($empleado);
        if (empty($empleado->cuenta_nomina)) $adv[] = 'Sin cuenta bancaria de nómina.';

        $divisas = in_array($tipo, self::TIPOS_CON_RESPONSABILIDAD, true)
            ? (float)($empleado->divisas_bono_responsabilidad ?? 0) : 0.0;

        $sueldoOrigen = 0.0;
        if ($tipo === 'Comisión de Servicio') {
            $sueldoOrigen = (float)($empleado->sueldo_dependencia_origen ?? 0);
            if ($sueldoOrigen <= 0) {
                $adv[] = 'Personal en comisión sin el sueldo de su dependencia de origen: la diferencia no se puede calcular.';
            }
        }

        return [
            'entradas' => [
                'tipo_personal'          => $tipo,
                'sueldo_base_mensual'    => $base,
                'codigo_grado'           => $codigo,
                'pct_profesionalizacion' => self::pctGrado($codigo),
                'anios_administracion'   => $anios,
                'pct_antiguedad'         => self::pctAntiguedad($anios),
                'n_hijos'                => Sueldo::contarHijos((int)$empleado->id_persona),
                'semanas'                => $semanas,
                'monto_cesta_ticket'     => $cesta,
                'tasa_dolar'             => $tasa,
                'divisas'                => $divisas,
                'sueldo_dependencia_origen' => $sueldoOrigen,
                'dias_bono_vac_base'     => (float)$params['nomina_dias_bono_vac_base'],
            ],
            'advertencias' => $adv,
        ];
    }

    // =====================================================================
    // El cálculo (puro — sin BD)
    // =====================================================================

    /**
     * Calcula una quincena. FUNCIÓN PURA: todo lo que necesita entra por
     * $in, así que se puede probar contra los valores de la plantilla.
     *
     * $in acepta (todas opcionales salvo sueldo_base_mensual):
     *   sueldo_base_mensual · pct_profesionalizacion · pct_antiguedad ·
     *   n_hijos · semanas · monto_cesta_ticket · tasa_dolar · divisas ·
     *   sueldo_dependencia_origen · dias_bono_vac_base · anios_administracion ·
     *   tipo_personal
     *
     * $params permite inyectar los escalares en las pruebas; si se omite se
     * leen de configuración.
     */
    public static function calcular(array $in, ?array $params = null): array
    {
        $p = $params ?? self::params();
        $n = fn($k, $d = 0.0) => (float)($in[$k] ?? $d);

        // ── Base quincenal ────────────────────────────────────────────────
        $mensual = $n('sueldo_base_mensual');
        $base    = $mensual / 2;

        // ── Asignaciones ──────────────────────────────────────────────────
        $primaProf  = $base * ($n('pct_profesionalizacion') / 100);
        $primaAnt   = $base * ($n('pct_antiguedad') / 100);
        $transporte = (float)$p['nomina_bono_transporte_mensual'] / 2;   // el mensual, a la mitad
        $nHijos     = max(0, (int)($in['n_hijos'] ?? 0));
        $primaHijos = $nHijos * (float)$p['nomina_monto_por_hijo'];

        $totalAsig = $primaProf + $primaAnt + $transporte + $primaHijos;
        $total     = $base + $totalAsig;                                  // sueldo normal quincenal

        // ── Deducciones y aportes ─────────────────────────────────────────
        // SSO, LRPPF y los patronales se calculan sobre la base semanal
        // (total x 12/52 x semanas); FAOV va sobre el total quincenal.
        $semanas    = max(1, (int)($in['semanas'] ?? $p['nomina_semanas_default']));
        $baseSemanal = $total * self::FACTOR_SEMANAL * $semanas;

        $ssoTrab   = $baseSemanal * ((float)$p['nomina_pct_sso_trabajador']   / 100);
        $faovTrab  = $total       * ((float)$p['nomina_pct_faov_trabajador']  / 100);
        $lrppfTrab = $baseSemanal * ((float)$p['nomina_pct_lrppf_trabajador'] / 100);
        $totalDed  = $ssoTrab + $faovTrab + $lrppfTrab;
        $neto      = $total - $totalDed;

        $ssoPat  = $baseSemanal * ((float)$p['nomina_pct_sso_patronal']  / 100);
        $faovPat = $total       * ((float)$p['nomina_pct_faov_patronal'] / 100);
        $rpePat  = $baseSemanal * ((float)$p['nomina_pct_rpe_patronal']  / 100);
        $totalAportes = $ssoPat + $faovPat + $rpePat;

        // ── Alícuotas y conceptos derivados ───────────────────────────────
        $cesta   = $n('monto_cesta_ticket');
        $diario  = (($total * 2) + $cesta) / self::DIAS_MES;

        $diasAnio    = (float)$p['nomina_dias_base_anio'] ?: 360;
        $diasVacBase = (float)($in['dias_bono_vac_base'] ?? $p['nomina_dias_bono_vac_base']);
        $aniosAdmin  = max(0, (int)($in['anios_administracion'] ?? 0));
        $diasHabiles = $diasVacBase + $aniosAdmin;

        $alicVac = $diario * ($diasHabiles / $diasAnio);
        $alicFin = $diario * ((float)$p['nomina_dias_bono_fin_anio'] / $diasAnio);
        $integralDiario = $diario + $alicVac + $alicFin;

        $becas  = $nHijos * (float)$p['nomina_becas_por_hijo'];
        $bono50 = $base * 0.5;

        // El bono de responsabilidad se pacta EN DIVISAS y se paga en
        // bolívares al cambio del mes; la quincena paga la mitad.
        $bonoResp = ($n('divisas') * $n('tasa_dolar')) / 2;

        // ── Comisión de servicio: se paga la diferencia ───────────────────
        $sueldoOrigen = $n('sueldo_dependencia_origen');
        $diferencia   = ($in['tipo_personal'] ?? '') === 'Comisión de Servicio'
            ? max(0, $total - $sueldoOrigen) : 0.0;

        $r = fn($v) => round((float)$v, 2);

        return [
            'sueldo_base_mensual'      => $r($mensual),
            'sueldo_base_quincenal'    => $r($base),
            'pct_profesionalizacion'   => round($n('pct_profesionalizacion'), 3),
            'pct_antiguedad'           => round($n('pct_antiguedad'), 3),
            'n_hijos'                  => $nHijos,
            'semanas'                  => $semanas,

            'prima_profesionalizacion' => $r($primaProf),
            'prima_antiguedad'         => $r($primaAnt),
            'bono_transporte'          => $r($transporte),
            'prima_por_hijos'          => $r($primaHijos),
            'total_asignaciones'       => $r($totalAsig),
            'total_sueldo_normal'      => $r($total),

            'sso_trabajador'           => $r($ssoTrab),
            'faov_trabajador'          => $r($faovTrab),
            'lrppf_trabajador'         => $r($lrppfTrab),
            'total_deducciones'        => $r($totalDed),
            'neto_a_cobrar'            => $r($neto),

            'sso_patronal'             => $r($ssoPat),
            'faov_patronal'            => $r($faovPat),
            'rpe_patronal'             => $r($rpePat),
            'total_aportes'            => $r($totalAportes),

            'sueldo_normal_diario'     => $r($diario),
            'alicuota_bono_vac'        => $r($alicVac),
            'alicuota_bono_fin_anio'   => $r($alicFin),
            'sueldo_integral_diario'   => $r($integralDiario),
            'dias_habiles_bono_vac'    => (int)$diasHabiles,
            'becas'                    => $r($becas),
            'bono_50'                  => $r($bono50),
            'bono_responsabilidad'     => $r($bonoResp),

            'sueldo_dependencia_origen' => $r($sueldoOrigen),
            'diferencia_comision'       => $r($diferencia),
        ];
    }

    /** Entradas + cálculo para un empleado concreto. */
    public static function calcularQuincena($empleado, string $fechaCorte, float $cesta, float $tasa, int $semanas): array
    {
        $e = self::entradasEmpleado($empleado, $fechaCorte, $cesta, $tasa, $semanas);
        return [
            'resultado'    => self::calcular($e['entradas']),
            'entradas'     => $e['entradas'],
            'advertencias' => $e['advertencias'],
        ];
    }

    // =====================================================================
    // Períodos
    // =====================================================================

    /** Empleados activos con todo lo que el cálculo necesita, en una consulta. */
    public static function empleadosParaNomina(): array
    {
        $db = new Database();
        $db->query("SELECT e.*, p.id AS id_persona, p.cedula, p.nombre, p.apellido, p.genero,
                           p.nivel_academico, p.codigo_grado,
                           c.nombre AS cargo, c.nivel_jerarquico,
                           d.nombre AS departamento
                      FROM empleados e
                      INNER JOIN personas p ON e.id_persona = p.id
                      INNER JOIN cargos c   ON e.id_cargo = c.id
                      LEFT  JOIN departamentos d ON e.id_departamento = d.id
                     WHERE e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL
                     ORDER BY p.apellido, p.nombre");
        return $db->resultSet();
    }

    public static function periodos(): array
    {
        $db = new Database();
        $db->query("SELECT np.*, COUNT(nd.id) AS total_empleados,
                           COALESCE(SUM(nd.neto_a_cobrar), 0) AS total_neto,
                           COUNT(nd.id) FILTER (WHERE nd.advertencias IS NOT NULL) AS con_advertencias
                      FROM nomina_periodos np
                      LEFT JOIN nomina_detalle nd ON nd.id_periodo = np.id
                     GROUP BY np.id
                     ORDER BY np.periodo DESC, np.quincena DESC");
        return $db->resultSet();
    }

    public static function find(int $id)
    {
        $db = new Database();
        $db->query("SELECT * FROM nomina_periodos WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Genera una quincena: congela los parámetros del mes y calcula el
     * snapshot de cada empleado activo. Transaccional.
     *
     * Exige que el mes esté cargado en `nomina_parametros_mes`: sin cesta
     * ticket ni tasa del dólar las alícuotas y el bono de responsabilidad
     * saldrían mal, y es mejor bloquear que producir un número plausible.
     */
    public static function generarPeriodo(string $periodo, int $quincena, ?int $semanas = null, ?int $userId = null): int
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            throw new Exception('Formato de período inválido (use AAAA-MM, ej. 2026-08).');
        }
        if (!in_array($quincena, [1, 2], true)) {
            throw new Exception('La quincena debe ser 1 (días 1-15) o 2 (día 16 al fin de mes).');
        }

        $pm = self::parametrosMes($periodo);
        if (!$pm) {
            throw new Exception('Faltan los parámetros de ' . $periodo . ': cargue el monto de cesta ticket y la tasa del dólar del mes antes de generar la nómina.');
        }

        $db = new Database();
        $db->query("SELECT id FROM nomina_periodos WHERE periodo = :p AND quincena = :q");
        $db->bind(':p', $periodo);
        $db->bind(':q', $quincena);
        if ($db->single()) {
            throw new Exception('Ya existe la quincena ' . $quincena . ' de ' . $periodo . '.');
        }

        // Fecha de corte = último día de la quincena (define sueldo vigente y antigüedad)
        [$anio, $mes] = array_map('intval', explode('-', $periodo));
        $fechaCorte = $quincena === 1
            ? sprintf('%04d-%02d-15', $anio, $mes)
            : date('Y-m-t', mktime(0, 0, 0, $mes, 1, $anio));

        $params  = self::params();
        $semanas = $semanas ?: (int)$params['nomina_semanas_default'];
        $cesta   = (float)$pm->monto_cesta_ticket;
        $tasa    = (float)$pm->tasa_dolar;

        $db->beginTransaction();
        $idPeriodo = null;
        $n = 0;
        try {
            $db->query("INSERT INTO nomina_periodos
                        (periodo, quincena, fecha_corte, monto_cesta_ticket, tasa_dolar, semanas, created_by)
                        VALUES (:p, :q, :f, :c, :t, :s, :u) RETURNING id");
            $db->bind(':p', $periodo);
            $db->bind(':q', $quincena);
            $db->bind(':f', $fechaCorte);
            $db->bind(':c', $cesta);
            $db->bind(':t', $tasa);
            $db->bind(':s', $semanas);
            $db->bind(':u', $userId);
            $idPeriodo = (int)$db->single()->id;

            foreach (self::empleadosParaNomina() as $emp) {
                $c = self::calcularQuincena($emp, $fechaCorte, $cesta, $tasa, $semanas);
                self::insertarDetalle($db, $idPeriodo, (int)$emp->id, $emp, $c);
                $n++;
            }

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('nomina_periodos', 'INSERT', $idPeriodo, null,
            ['periodo' => $periodo, 'quincena' => $quincena, 'empleados' => $n, 'semanas' => $semanas], $userId);
        return $idPeriodo;
    }

    private static function insertarDetalle(Database $db, int $idPeriodo, int $idEmpleado, $emp, array $c): void
    {
        $r = $c['resultado'];
        $e = $c['entradas'];

        $db->query("INSERT INTO nomina_detalle
            (id_periodo, id_empleado, tipo_personal, sueldo_base_mensual, sueldo_base_quincenal,
             codigo_grado, pct_profesionalizacion, anios_administracion, pct_antiguedad, n_hijos,
             prima_profesionalizacion, prima_antiguedad, bono_transporte, prima_por_hijos,
             total_asignaciones, total_sueldo_normal,
             sso_trabajador, faov_trabajador, lrppf_trabajador, total_deducciones, neto_a_cobrar,
             sso_patronal, faov_patronal, rpe_patronal, total_aportes,
             sueldo_normal_diario, alicuota_bono_vac, alicuota_bono_fin_anio, sueldo_integral_diario,
             dias_habiles_bono_vac, becas, bono_50, bono_responsabilidad,
             sueldo_dependencia_origen, diferencia_comision, cuenta_nomina, banco_nomina, advertencias)
            VALUES
            (:idp, :ide, :tipo, :sbm, :sbq, :cg, :ppct, :aa, :apct, :nh,
             :pp, :pa, :bt, :ph, :ta, :tsn,
             :ssot, :faovt, :lrppft, :td, :neto,
             :ssop, :faovp, :rpep, :tap,
             :snd, :av, :af, :sid,
             :dh, :becas, :b50, :bresp,
             :sdo, :dif, :cta, :banco, :adv)");

        $db->bind(':idp', $idPeriodo);
        $db->bind(':ide', $idEmpleado);
        $db->bind(':tipo', $e['tipo_personal']);
        $db->bind(':sbm', $r['sueldo_base_mensual']);
        $db->bind(':sbq', $r['sueldo_base_quincenal']);
        $db->bind(':cg', $e['codigo_grado']);
        $db->bind(':ppct', $r['pct_profesionalizacion']);
        $db->bind(':aa', $e['anios_administracion']);
        $db->bind(':apct', $r['pct_antiguedad']);
        $db->bind(':nh', $r['n_hijos']);
        $db->bind(':pp', $r['prima_profesionalizacion']);
        $db->bind(':pa', $r['prima_antiguedad']);
        $db->bind(':bt', $r['bono_transporte']);
        $db->bind(':ph', $r['prima_por_hijos']);
        $db->bind(':ta', $r['total_asignaciones']);
        $db->bind(':tsn', $r['total_sueldo_normal']);
        $db->bind(':ssot', $r['sso_trabajador']);
        $db->bind(':faovt', $r['faov_trabajador']);
        $db->bind(':lrppft', $r['lrppf_trabajador']);
        $db->bind(':td', $r['total_deducciones']);
        $db->bind(':neto', $r['neto_a_cobrar']);
        $db->bind(':ssop', $r['sso_patronal']);
        $db->bind(':faovp', $r['faov_patronal']);
        $db->bind(':rpep', $r['rpe_patronal']);
        $db->bind(':tap', $r['total_aportes']);
        $db->bind(':snd', $r['sueldo_normal_diario']);
        $db->bind(':av', $r['alicuota_bono_vac']);
        $db->bind(':af', $r['alicuota_bono_fin_anio']);
        $db->bind(':sid', $r['sueldo_integral_diario']);
        $db->bind(':dh', $r['dias_habiles_bono_vac']);
        $db->bind(':becas', $r['becas']);
        $db->bind(':b50', $r['bono_50']);
        $db->bind(':bresp', $r['bono_responsabilidad']);
        $db->bind(':sdo', $r['sueldo_dependencia_origen']);
        $db->bind(':dif', $r['diferencia_comision']);
        $db->bind(':cta', $emp->cuenta_nomina ?? null);
        $db->bind(':banco', $emp->banco_nomina ?? null);
        $db->bind(':adv', $c['advertencias'] ? implode(' · ', $c['advertencias']) : null);
        $db->execute();
    }

    /** Detalle agrupado por tipo de personal, en el orden de las hojas del formato. */
    public static function detallePorPeriodo(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT nd.*, p.cedula, p.nombre, p.apellido, p.genero,
                           c.nombre AS cargo, d.nombre AS departamento,
                           e.fecha_ingreso, e.fecha_ingreso_administracion
                      FROM nomina_detalle nd
                      INNER JOIN empleados e ON nd.id_empleado = e.id
                      INNER JOIN personas p  ON e.id_persona = p.id
                      INNER JOIN cargos c    ON e.id_cargo = c.id
                      LEFT  JOIN departamentos d ON e.id_departamento = d.id
                     WHERE nd.id_periodo = :id
                     ORDER BY nd.tipo_personal, p.apellido, p.nombre");
        $db->bind(':id', $idPeriodo);

        $grupos = array_fill_keys(self::TIPOS, []);
        foreach ($db->resultSet() as $r) $grupos[$r->tipo_personal][] = $r;
        return $grupos;
    }

    /** Consolidado por tipo de personal (hoja RESUMEN del formato). */
    public static function resumen(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT tipo_personal,
                           COUNT(*) AS cantidad,
                           COALESCE(SUM(total_sueldo_normal), 0) AS total_sueldo,
                           COALESCE(SUM(total_deducciones),   0) AS total_deducciones,
                           COALESCE(SUM(sso_trabajador),      0) AS sso,
                           COALESCE(SUM(faov_trabajador),     0) AS faov,
                           COALESCE(SUM(lrppf_trabajador),    0) AS lrppf,
                           COALESCE(SUM(neto_a_cobrar),       0) AS total_neto,
                           COALESCE(SUM(total_aportes),       0) AS total_aportes
                      FROM nomina_detalle WHERE id_periodo = :id
                     GROUP BY tipo_personal");
        $db->bind(':id', $idPeriodo);

        $out = [];
        foreach (self::TIPOS as $t) {
            $out[$t] = ['cantidad' => 0, 'total_sueldo' => 0.0, 'total_deducciones' => 0.0,
                        'sso' => 0.0, 'faov' => 0.0, 'lrppf' => 0.0,
                        'total_neto' => 0.0, 'total_aportes' => 0.0];
        }
        foreach ($db->resultSet() as $r) {
            $out[$r->tipo_personal] = [
                'cantidad'          => (int)$r->cantidad,
                'total_sueldo'      => (float)$r->total_sueldo,
                'total_deducciones' => (float)$r->total_deducciones,
                'sso'               => (float)$r->sso,
                'faov'              => (float)$r->faov,
                'lrppf'             => (float)$r->lrppf,
                'total_neto'        => (float)$r->total_neto,
                'total_aportes'     => (float)$r->total_aportes,
            ];
        }
        return $out;
    }

    /** Empleados del período con algo sin resolver (para revisar antes de cerrar). */
    public static function advertencias(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT nd.id, nd.advertencias, p.cedula, p.nombre, p.apellido
                      FROM nomina_detalle nd
                      INNER JOIN empleados e ON nd.id_empleado = e.id
                      INNER JOIN personas p  ON e.id_persona = p.id
                     WHERE nd.id_periodo = :id AND nd.advertencias IS NOT NULL
                     ORDER BY p.apellido, p.nombre");
        $db->bind(':id', $idPeriodo);
        return $db->resultSet();
    }

    /**
     * Recalcula un período en Borrador: borra el snapshot y lo vuelve a
     * generar con los datos actuales. Es la vía para incorporar una
     * corrección de ficha sin perder el número de período.
     */
    public static function recalcular(int $idPeriodo, ?int $userId = null): int
    {
        $per = self::find($idPeriodo);
        if (!$per) throw new Exception('Período no encontrado.');
        if ($per->estado !== 'Borrador') throw new Exception('El período está cerrado; no se puede recalcular.');

        $pm = self::parametrosMes($per->periodo);
        $cesta = $pm ? (float)$pm->monto_cesta_ticket : (float)$per->monto_cesta_ticket;
        $tasa  = $pm ? (float)$pm->tasa_dolar         : (float)$per->tasa_dolar;

        $db = new Database();
        $db->beginTransaction();
        $n = 0;
        try {
            $db->query("DELETE FROM nomina_detalle WHERE id_periodo = :id");
            $db->bind(':id', $idPeriodo);
            $db->execute();

            // Los parámetros del mes pueden haber cambiado: se re-congelan.
            $db->query("UPDATE nomina_periodos SET monto_cesta_ticket = :c, tasa_dolar = :t WHERE id = :id");
            $db->bind(':c', $cesta);
            $db->bind(':t', $tasa);
            $db->bind(':id', $idPeriodo);
            $db->execute();

            foreach (self::empleadosParaNomina() as $emp) {
                $c = self::calcularQuincena($emp, $per->fecha_corte, $cesta, $tasa, (int)$per->semanas);
                self::insertarDetalle($db, $idPeriodo, (int)$emp->id, $emp, $c);
                $n++;
            }
            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('nomina_periodos', 'UPDATE', $idPeriodo, null,
            ['accion' => 'recalcular', 'empleados' => $n], $userId);
        return $n;
    }

    /** Cierra el período: queda inmutable. */
    public static function cerrar(int $idPeriodo, ?int $userId = null): bool
    {
        $db = new Database();
        $db->query("UPDATE nomina_periodos
                       SET estado = 'Cerrado', cerrado_at = CURRENT_TIMESTAMP, cerrado_by = :u
                     WHERE id = :id AND estado = 'Borrador'");
        $db->bind(':u', $userId);
        $db->bind(':id', $idPeriodo);
        $db->execute();
        if ($db->rowCount() < 1) throw new Exception('El período no existe o ya estaba cerrado.');

        self::auditStatic('nomina_periodos', 'UPDATE', $idPeriodo,
            ['estado' => 'Borrador'], ['estado' => 'Cerrado'], $userId);
        return true;
    }
}
