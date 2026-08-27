<?php

/**
 * Bono Vacacional (R-11).
 *
 * **v2 — 2026-08-27, fase N-D (mig. 073): las primas se CALCULAN.** Todo lo que
 * es derivable pasa por `Nomina::calcular()`, el mismo motor de la nómina
 * quincenal: primas de profesionalización y antigüedad, bono de transporte,
 * prima por hijos, sueldo normal diario y alícuota. Ya no se capturan.
 *
 * ⚠️ **El TOTAL sigue siendo de captura.** La fórmula del monto que se paga no
 * está en ninguna fuente: la plantilla de nómina documenta la ALÍCUOTA (el
 * devengo diario), no el total, y el mes ya calculado que el cliente prometió
 * (audio del 23/07) no llegó. En vez de inventarla, el sistema:
 *
 *   · calcula `total_calculado` bajo un supuesto EXPLÍCITO
 *     (`sueldo_normal_diario × días correspondientes`), y
 *   · lo muestra al lado de `total_bono_vacacional`, el que confirma Talento
 *     Humano, con la diferencia entre ambos.
 *
 * En cuanto llegue un mes real, esa diferencia dice si el supuesto acierta. Es
 * la pregunta pendiente convertida en instrumento, no un número afirmado sin
 * respaldo. Ver `docs/PLAN_MODULO_NOMINA.md` §6.4.
 */
class BonoVacacional extends Model
{
    /** Los 5 tipos de personal. Fuente única: Nomina::TIPOS. */
    const TIPOS = Nomina::TIPOS;

    /** Clave de configuración con los días base por tipo de personal (contrato colectivo, no LOTTT). */
    const CONFIG_DIAS = [
        'Alto Nivel'           => 'bono_vac_dias_alto_nivel',
        'Empleados Fijos'      => 'bono_vac_dias_empleados_fijos',
        'Obreros Fijos'        => 'bono_vac_dias_obreros_fijos',
        'Contratados'          => 'bono_vac_dias_contratados',
        'Comisión de Servicio' => 'bono_vac_dias_comision',
    ];

    /**
     * ¿El tipo suma años de servicio a los días base? (Obreros Fijos es plano).
     *
     * ⚠️ Esta distinción y los días base de `CONFIG_DIAS` son la **pregunta
     * N-1, sin responder**: la plantilla de nómina usa 75 en las cinco hojas,
     * incluidas obreros y contratados, mientras la configuración tiene 85 y 45.
     * Se conservan como parámetros hasta que el cliente aclare cuál manda.
     */
    const SUMA_ANIOS = [
        'Alto Nivel' => true, 'Empleados Fijos' => true, 'Obreros Fijos' => false,
        'Contratados' => true, 'Comisión de Servicio' => true,
    ];

    /**
     * Tipo de personal. Delega en `Nomina::tipoPersonal()` para que haya una
     * sola definición: la nómina quincenal y el bono vacacional deben agrupar
     * al mismo trabajador en la misma hoja.
     */
    public static function tipoPersonal($empleado): string
    {
        return Nomina::tipoPersonal($empleado);
    }

    public static function diasBase(string $tipo): int
    {
        $clave = self::CONFIG_DIAS[$tipo] ?? null;
        return $clave ? (int)ConfigSistema::get($clave) : 0;
    }

    /**
     * Días que le corresponden. Los años se cuentan **a la fecha de corte** del
     * período, no a hoy: generar un período pasado tiene que dar el mismo
     * número que dio entonces.
     */
    public static function diasCorrespondientes($empleado, string $tipo, ?string $fechaCorte = null): int
    {
        $base = self::diasBase($tipo);
        if (!(self::SUMA_ANIOS[$tipo] ?? false)) return $base;
        return $base + Nomina::aniosAdministracion($empleado, $fechaCorte);
    }

    /** Listado de períodos generados (más reciente primero). */
    public static function periodos(): array
    {
        $db = new Database();
        $db->query("SELECT p.*, COUNT(d.id) AS total_empleados,
                           COALESCE(SUM(d.total_bono_vacacional), 0) AS total_confirmado,
                           COALESCE(SUM(d.total_calculado), 0)       AS total_calculado,
                           COUNT(d.id) FILTER (WHERE d.total_bono_vacacional IS NULL) AS sin_confirmar,
                           COUNT(d.id) FILTER (WHERE d.advertencias IS NOT NULL)      AS con_advertencias
                    FROM bono_vacacional_periodos p
                    LEFT JOIN bono_vacacional_detalle d ON d.id_periodo = p.id
                    GROUP BY p.id ORDER BY p.periodo DESC");
        return $db->resultSet();
    }

    public static function find(int $id)
    {
        $db = new Database();
        $db->query("SELECT * FROM bono_vacacional_periodos WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function findDetalle(int $id)
    {
        $db = new Database();
        $db->query("SELECT * FROM bono_vacacional_detalle WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Genera un período nuevo: calcula el snapshot de todo el personal activo
     * con `Nomina::calcular()` (v2 — antes copiaba las primas capturadas a mano
     * en `empleado_salarios`).
     *
     * Exige que el mes esté cargado en `nomina_parametros_mes`: la cesta ticket
     * entra en el sueldo normal diario, y sin ella el diario y la alícuota
     * saldrían mal sin que nadie lo note.
     *
     * `total_bono_vacacional` queda NULL: es el total que **confirma** Talento
     * Humano. `total_calculado` lleva la estimación del sistema.
     */
    public static function generarPeriodo(string $periodo, string $fechaCorte, ?int $userId = null): int
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            throw new Exception('Formato de período inválido (use AAAA-MM, ej. 2026-08).');
        }

        $pm = Nomina::parametrosMes($periodo);
        if (!$pm) {
            throw new Exception('Faltan los parámetros de ' . $periodo . ': cargue el monto de cesta ticket y la tasa del dólar del mes antes de generar el bono vacacional.');
        }

        $db = new Database();
        $db->query("SELECT id FROM bono_vacacional_periodos WHERE periodo = :p");
        $db->bind(':p', $periodo);
        if ($db->single()) throw new Exception('Ya existe un período generado para ' . $periodo . '.');

        $cesta = (float)$pm->monto_cesta_ticket;
        $tasa  = (float)$pm->tasa_dolar;

        $db->beginTransaction();
        $idPeriodo = null;
        $n = 0;
        try {
            $db->query("INSERT INTO bono_vacacional_periodos
                        (periodo, fecha_corte, monto_cesta_ticket, tasa_dolar, created_by)
                        VALUES (:p, :f, :c, :t, :u) RETURNING id");
            $db->bind(':p', $periodo);
            $db->bind(':f', $fechaCorte);
            $db->bind(':c', $cesta);
            $db->bind(':t', $tasa);
            $db->bind(':u', $userId);
            $idPeriodo = (int)$db->single()->id;

            foreach (Nomina::empleadosParaNomina() as $emp) {
                self::insertarDetalle($db, $idPeriodo, $emp, $fechaCorte, $cesta, $tasa);
                $n++;
            }

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('bono_vacacional_periodos', 'INSERT', $idPeriodo, null,
            ['periodo' => $periodo, 'fecha_corte' => $fechaCorte, 'empleados' => $n], $userId);
        return $idPeriodo;
    }

    /**
     * Calcula e inserta la fila de un empleado. Comparte el motor con la nómina
     * quincenal, así que las primas de ambos documentos no pueden discrepar.
     */
    private static function insertarDetalle(Database $db, int $idPeriodo, $emp, string $fechaCorte, float $cesta, float $tasa): void
    {
        $c = Nomina::calcularQuincena($emp, $fechaCorte, $cesta, $tasa, (int)Nomina::params()['nomina_semanas_default']);
        $r = $c['resultado'];
        $e = $c['entradas'];
        $tipo = $e['tipo_personal'];

        // Los días del bono vienen del contrato colectivo (bono_vac_dias_*), que
        // NO es lo mismo que los `dias_habiles_bono_vac` de la alícuota (esos
        // usan `nomina_dias_bono_vac_base`). Se contradicen: es la pregunta N-1.
        $dias = self::diasCorrespondientes($emp, $tipo, $fechaCorte);

        // Alícuota recalculada con los días del contrato colectivo, para que sea
        // coherente con los días que este documento paga.
        $diasAnio = (float)Nomina::params()['nomina_dias_base_anio'] ?: 360;
        $alicuota = round($r['sueldo_normal_diario'] * ($dias / $diasAnio), 2);

        // ESTIMACIÓN, no la fórmula del cliente (ver docblock de la clase).
        $totalCalculado = round($r['sueldo_normal_diario'] * $dias, 2);

        $sueldo = Sueldo::actual((int)$emp->id, $fechaCorte);
        $primaDiscapacidad = $sueldo ? (float)($sueldo->prima_discapacidad ?? 0) : 0.0;
        // La caja de ahorro queda en 0 por regla: la gobernación no la paga.
        $cajaAhorro = 0.0;

        $db->query("INSERT INTO bono_vacacional_detalle
            (id_periodo, id_empleado, tipo_personal, dias_vacaciones,
             sueldo_basico, sueldo_base_quincenal, codigo_grado, pct_profesionalizacion,
             anios_administracion, pct_antiguedad,
             prima_profesional, prima_antiguedad, n_hijos, monto_hijo, prima_por_hijo,
             bono_transporte, prima_discapacidad, caja_ahorro, sueldo_integral,
             sueldo_normal_diario, cuenta_bancaria, monto_cesta_ticket, alicuotas,
             total_calculado, advertencias)
            VALUES
            (:idp, :ide, :tipo, :dias,
             :sb, :sbq, :cg, :ppct,
             :aa, :apct,
             :pp, :pa, :nh, :mh, :ph,
             :bt, :pdis, :ca, :si,
             :snd, :cta, :mct, :alic,
             :tcalc, :adv)");
        $db->bind(':idp', $idPeriodo);
        $db->bind(':ide', (int)$emp->id);
        $db->bind(':tipo', $tipo);
        $db->bind(':dias', $dias);
        $db->bind(':sb', $r['sueldo_base_mensual']);
        $db->bind(':sbq', $r['sueldo_base_quincenal']);
        $db->bind(':cg', $e['codigo_grado']);
        $db->bind(':ppct', $r['pct_profesionalizacion']);
        $db->bind(':aa', $e['anios_administracion']);
        $db->bind(':apct', $r['pct_antiguedad']);
        $db->bind(':pp', $r['prima_profesionalizacion']);
        $db->bind(':pa', $r['prima_antiguedad']);
        $db->bind(':nh', $r['n_hijos']);
        $db->bind(':mh', (float)Nomina::params()['nomina_monto_por_hijo']);
        $db->bind(':ph', $r['prima_por_hijos']);
        $db->bind(':bt', $r['bono_transporte']);
        $db->bind(':pdis', $primaDiscapacidad);
        $db->bind(':ca', $cajaAhorro);
        $db->bind(':si', $r['sueldo_integral_diario']);
        $db->bind(':snd', $r['sueldo_normal_diario']);
        $db->bind(':cta', $emp->cuenta_nomina ?? null);
        $db->bind(':mct', $cesta);
        $db->bind(':alic', $alicuota);
        $db->bind(':tcalc', $totalCalculado);
        $db->bind(':adv', $c['advertencias'] ? implode(' · ', $c['advertencias']) : null);
        $db->execute();
    }

    /**
     * Recalcula un período en Borrador con los datos actuales de las fichas.
     * **Preserva los totales ya confirmados** por Talento Humano: solo se
     * recalcula lo derivable, no se pisa el trabajo de captura.
     */
    public static function recalcular(int $idPeriodo, ?int $userId = null): int
    {
        $per = self::find($idPeriodo);
        if (!$per) throw new Exception('Período no encontrado.');
        if ($per->estado !== 'Borrador') throw new Exception('El período está cerrado; no se puede recalcular.');

        $pm    = Nomina::parametrosMes($per->periodo);
        $cesta = $pm ? (float)$pm->monto_cesta_ticket : (float)$per->monto_cesta_ticket;
        $tasa  = $pm ? (float)$pm->tasa_dolar         : (float)$per->tasa_dolar;

        $db = new Database();
        // Totales confirmados, por empleado, para reponerlos tras recalcular.
        $db->query("SELECT id_empleado, total_bono_vacacional, grado_escala
                      FROM bono_vacacional_detalle
                     WHERE id_periodo = :id AND (total_bono_vacacional IS NOT NULL OR grado_escala IS NOT NULL)");
        $db->bind(':id', $idPeriodo);
        $confirmados = [];
        foreach ($db->resultSet() as $row) {
            $confirmados[(int)$row->id_empleado] = [
                'total'  => $row->total_bono_vacacional,
                'escala' => $row->grado_escala,
            ];
        }

        $db->beginTransaction();
        $n = 0;
        try {
            $db->query("DELETE FROM bono_vacacional_detalle WHERE id_periodo = :id");
            $db->bind(':id', $idPeriodo);
            $db->execute();

            $db->query("UPDATE bono_vacacional_periodos SET monto_cesta_ticket = :c, tasa_dolar = :t WHERE id = :id");
            $db->bind(':c', $cesta);
            $db->bind(':t', $tasa);
            $db->bind(':id', $idPeriodo);
            $db->execute();

            foreach (Nomina::empleadosParaNomina() as $emp) {
                self::insertarDetalle($db, $idPeriodo, $emp, $per->fecha_corte, $cesta, $tasa);
                $n++;

                $prev = $confirmados[(int)$emp->id] ?? null;
                if ($prev && ($prev['total'] !== null || $prev['escala'] !== null)) {
                    $db->query("UPDATE bono_vacacional_detalle
                                   SET total_bono_vacacional = :t, grado_escala = :g
                                 WHERE id_periodo = :idp AND id_empleado = :ide");
                    $db->bind(':t', $prev['total']);
                    $db->bind(':g', $prev['escala']);
                    $db->bind(':idp', $idPeriodo);
                    $db->bind(':ide', (int)$emp->id);
                    $db->execute();
                }
            }
            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('bono_vacacional_periodos', 'UPDATE', $idPeriodo, null,
            ['accion' => 'recalcular', 'empleados' => $n, 'totales_preservados' => count($confirmados)], $userId);
        return $n;
    }

    /** Empleados del período con algo sin resolver (revisar antes de cerrar). */
    public static function advertencias(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT bvd.id, bvd.advertencias, p.cedula, p.nombre, p.apellido
                      FROM bono_vacacional_detalle bvd
                      INNER JOIN empleados e ON bvd.id_empleado = e.id
                      INNER JOIN personas p  ON e.id_persona = p.id
                     WHERE bvd.id_periodo = :id AND bvd.advertencias IS NOT NULL
                     ORDER BY p.apellido, p.nombre");
        $db->bind(':id', $idPeriodo);
        return $db->resultSet();
    }

    /** Detalle de un período agrupado por tipo de personal, en el mismo orden que las hojas del .ods. */
    public static function detallePorPeriodo(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT bvd.*, p.cedula, p.nombre, p.apellido, p.genero, c.nombre AS cargo,
                           e.fecha_ingreso, e.fecha_ingreso_administracion
                    FROM bono_vacacional_detalle bvd
                    INNER JOIN empleados e ON bvd.id_empleado = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    WHERE bvd.id_periodo = :id
                    ORDER BY bvd.tipo_personal, p.apellido, p.nombre");
        $db->bind(':id', $idPeriodo);
        $rows = $db->resultSet();

        $grupos = array_fill_keys(self::TIPOS, []);
        foreach ($rows as $r) $grupos[$r->tipo_personal][] = $r;
        return $grupos;
    }

    /**
     * Lo que queda de captura manual, mientras el período esté en Borrador:
     *
     *  · `total_bono_vacacional` — el total que CONFIRMA Talento Humano. Sigue
     *    siendo la cifra oficial porque la fórmula del total no está confirmada
     *    (ver docblock de la clase). Si se deja vacío, hereda `total_calculado`.
     *  · `grado_escala` — el grado/escala del cargo del cliente, que no es el
     *    grado de instrucción y no se deriva de nada que tengamos.
     *
     * Las primas, la alícuota, el diario y la cuenta bancaria **ya no se
     * capturan aquí**: las primas y la alícuota las calcula el motor, y la
     * cuenta viene de la ficha del empleado (mig. 072).
     */
    public static function actualizarDetalle(int $idDetalle, array $campos, ?int $userId = null): bool
    {
        $db = new Database();
        $db->query("SELECT bvd.*, per.estado FROM bono_vacacional_detalle bvd
                    INNER JOIN bono_vacacional_periodos per ON bvd.id_periodo = per.id
                    WHERE bvd.id = :id");
        $db->bind(':id', $idDetalle);
        $row = $db->single();
        if (!$row) throw new Exception('Registro no encontrado.');
        if ($row->estado !== 'Borrador') throw new Exception('El período ya está cerrado; no se puede editar.');

        $grado = trim((string)($campos['grado_escala'] ?? $row->grado_escala ?? ''));
        $total = isset($campos['total_bono_vacacional']) && $campos['total_bono_vacacional'] !== ''
            ? round((float)str_replace(',', '.', $campos['total_bono_vacacional']), 2) : null;
        if ($total !== null && $total < 0) throw new Exception('El total no puede ser negativo.');

        $db->query("UPDATE bono_vacacional_detalle
                    SET grado_escala = :g, total_bono_vacacional = :t
                    WHERE id = :id");
        $db->bind(':g', $grado !== '' ? $grado : null);
        $db->bind(':t', $total);
        $db->bind(':id', $idDetalle);
        $result = $db->execute();

        self::auditStatic('bono_vacacional_detalle', 'UPDATE', $idDetalle,
            ['total_bono_vacacional' => $row->total_bono_vacacional, 'grado_escala' => $row->grado_escala],
            ['total_bono_vacacional' => $total, 'grado_escala' => $grado], $userId);
        return $result;
    }

    /**
     * Toma el total calculado como confirmado para todo el período. Atajo para
     * cuando Talento Humano valida la estimación en bloque; deja rastro en la
     * auditoría igual que si se hubiera capturado fila por fila.
     */
    public static function aceptarCalculados(int $idPeriodo, ?int $userId = null): int
    {
        $per = self::find($idPeriodo);
        if (!$per) throw new Exception('Período no encontrado.');
        if ($per->estado !== 'Borrador') throw new Exception('El período está cerrado; no se puede editar.');

        $db = new Database();
        $db->query("UPDATE bono_vacacional_detalle
                       SET total_bono_vacacional = total_calculado
                     WHERE id_periodo = :id AND total_bono_vacacional IS NULL AND total_calculado IS NOT NULL");
        $db->bind(':id', $idPeriodo);
        $db->execute();
        $n = $db->rowCount();

        self::auditStatic('bono_vacacional_periodos', 'UPDATE', $idPeriodo, null,
            ['accion' => 'aceptar_totales_calculados', 'filas' => $n], $userId);
        return $n;
    }

    /**
     * Cantidad y totales por tipo de personal (hoja CUADRO_RESUMEN_).
     * Devuelve el confirmado y el calculado, para que la UI muestre la
     * diferencia: es lo que calibra el supuesto del total cuando llegue un mes
     * real del cliente.
     */
    public static function resumen(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT tipo_personal, COUNT(*) AS cantidad,
                           COALESCE(SUM(total_bono_vacacional), 0) AS total,
                           COALESCE(SUM(total_calculado), 0)       AS total_calculado,
                           COUNT(*) FILTER (WHERE total_bono_vacacional IS NULL) AS sin_confirmar
                    FROM bono_vacacional_detalle WHERE id_periodo = :id GROUP BY tipo_personal");
        $db->bind(':id', $idPeriodo);
        $rows = $db->resultSet();

        $out = [];
        foreach (self::TIPOS as $tipo) {
            $out[$tipo] = ['cantidad' => 0, 'total' => 0.0, 'total_calculado' => 0.0, 'sin_confirmar' => 0];
        }
        foreach ($rows as $r) {
            $out[$r->tipo_personal] = [
                'cantidad'        => (int)$r->cantidad,
                'total'           => (float)$r->total,
                'total_calculado' => (float)$r->total_calculado,
                'sin_confirmar'   => (int)$r->sin_confirmar,
            ];
        }
        return $out;
    }

    /** Cierra el período: bloquea edición futura del detalle. */
    public static function cerrar(int $idPeriodo, ?int $userId = null): bool
    {
        $db = new Database();
        $db->query("UPDATE bono_vacacional_periodos
                    SET estado = 'Cerrado', cerrado_at = CURRENT_TIMESTAMP, cerrado_by = :u
                    WHERE id = :id AND estado = 'Borrador'");
        $db->bind(':u', $userId);
        $db->bind(':id', $idPeriodo);
        $db->execute();
        if ($db->rowCount() < 1) throw new Exception('El período no existe o ya estaba cerrado.');

        self::auditStatic('bono_vacacional_periodos', 'UPDATE', $idPeriodo, ['estado' => 'Borrador'], ['estado' => 'Cerrado'], $userId);
        return true;
    }
}
