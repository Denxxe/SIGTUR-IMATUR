<?php

/**
 * Bono Vacacional — "registro + reporte" (R-11, 1ra entrega). El sistema NO
 * calcula el monto legal completo: organiza los datos que Talento Humano ya
 * captura (sueldo/primas del empleado, días según contrato colectivo) y deja
 * el total final como campo de captura/verificación manual, hasta que el
 * cliente confirme un mes ya calculado con números reales para calibrar la
 * fórmula exacta ("FÓRMULA NUEVA DE BONO VACACIONAL" de la plantilla).
 */
class BonoVacacional extends Model
{
    const TIPOS = ['Alto Nivel', 'Empleados Fijos', 'Obreros Fijos', 'Contratados'];

    /** Clave de configuración con los días base por tipo de personal (contrato colectivo, no LOTTT). */
    const CONFIG_DIAS = [
        'Alto Nivel'      => 'bono_vac_dias_alto_nivel',
        'Empleados Fijos' => 'bono_vac_dias_empleados_fijos',
        'Obreros Fijos'   => 'bono_vac_dias_obreros_fijos',
        'Contratados'     => 'bono_vac_dias_contratados',
    ];

    /** ¿El tipo suma años de servicio a los días base? (Obreros Fijos es plano). */
    const SUMA_ANIOS = [
        'Alto Nivel' => true, 'Empleados Fijos' => true, 'Obreros Fijos' => false, 'Contratados' => true,
    ];

    /**
     * Deriva el tipo de personal (para las 4 hojas del formato oficial) de
     * datos que YA existen en la ficha del empleado — sin captura adicional.
     */
    public static function tipoPersonal($empleado): string
    {
        $nivel = $empleado->nivel_jerarquico ?? null;
        if (in_array($nivel, ['Presidencia', 'Dirección'], true)) return 'Alto Nivel';
        if (($empleado->tipo_contrato ?? '') === 'Contratado') return 'Contratados';
        if (($empleado->clasificacion ?? '') === 'Obrero') return 'Obreros Fijos';
        return 'Empleados Fijos';
    }

    public static function diasBase(string $tipo): int
    {
        $clave = self::CONFIG_DIAS[$tipo] ?? null;
        return $clave ? (int)ConfigSistema::get($clave) : 0;
    }

    public static function diasCorrespondientes($empleado, string $tipo): int
    {
        $base = self::diasBase($tipo);
        if (!(self::SUMA_ANIOS[$tipo] ?? false)) return $base;
        return $base + Empleado::aniosServicio($empleado);
    }

    /** Listado de períodos generados (más reciente primero). */
    public static function periodos(): array
    {
        $db = new Database();
        $db->query("SELECT p.*, COUNT(d.id) AS total_empleados
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
     * Genera un período nuevo: snapshot de todos los empleados activos con su
     * tipo de personal, días correspondientes y sueldo/primas vigentes a la
     * fecha de corte. El total (columna capturable) queda NULL — lo completa
     * Talento Humano en verPeriodo() mientras el período esté en Borrador.
     */
    public static function generarPeriodo(string $periodo, string $fechaCorte, ?int $userId = null): int
    {
        if (!preg_match('/^\d{4}-\d{2}$/', $periodo)) {
            throw new Exception('Formato de período inválido (use AAAA-MM, ej. 2026-08).');
        }

        $db = new Database();
        $db->query("SELECT id FROM bono_vacacional_periodos WHERE periodo = :p");
        $db->bind(':p', $periodo);
        if ($db->single()) throw new Exception('Ya existe un período generado para ' . $periodo . '.');

        $db->beginTransaction();
        $idPeriodo = null;
        $totalEmpleados = 0;
        try {
            $db->query("INSERT INTO bono_vacacional_periodos (periodo, fecha_corte, created_by) VALUES (:p, :f, :u) RETURNING id");
            $db->bind(':p', $periodo);
            $db->bind(':f', $fechaCorte);
            $db->bind(':u', $userId);
            $idPeriodo = (int)$db->single()->id;

            $montoCesta = (float)ConfigSistema::get('monto_cesta_ticket');

            $db->query("SELECT e.*, p.cedula, p.nombre, p.apellido, p.genero, p.id AS id_persona,
                               c.nombre AS cargo, c.nivel_jerarquico
                        FROM empleados e
                        INNER JOIN personas p ON e.id_persona = p.id
                        INNER JOIN cargos c ON e.id_cargo = c.id
                        WHERE e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL
                        ORDER BY p.apellido, p.nombre");
            $empleados = $db->resultSet();
            $totalEmpleados = count($empleados);

            foreach ($empleados as $emp) {
                $tipo    = self::tipoPersonal($emp);
                $dias    = self::diasCorrespondientes($emp, $tipo);
                $sueldo  = Sueldo::actual((int)$emp->id, $fechaCorte);
                $nHijos  = Sueldo::contarHijos((int)$emp->id_persona);
                $g       = fn($campo) => $sueldo ? (float)($sueldo->$campo ?? 0) : 0.0;
                $sueldoIntegral   = $sueldo ? Sueldo::integralMensual($sueldo, $nHijos) : 0.0;
                $primaPorHijoTotal = round($g('prima_por_hijo') * $nHijos, 2);

                $db->query("INSERT INTO bono_vacacional_detalle
                            (id_periodo, id_empleado, tipo_personal, dias_vacaciones, sueldo_basico, prima_profesional,
                             prima_antiguedad, n_hijos, monto_hijo, prima_por_hijo, bono_transporte, prima_discapacidad,
                             caja_ahorro, sueldo_integral, monto_cesta_ticket)
                            VALUES (:idp, :ide, :tipo, :dias, :sb, :pp, :pa, :nh, :mh, :ph, :bt, :pdis, :ca, :si, :mct)");
                $db->bind(':idp', $idPeriodo);
                $db->bind(':ide', (int)$emp->id);
                $db->bind(':tipo', $tipo);
                $db->bind(':dias', $dias);
                $db->bind(':sb', $g('sueldo_basico'));
                $db->bind(':pp', $g('prima_profesional'));
                $db->bind(':pa', $g('prima_antiguedad'));
                $db->bind(':nh', $nHijos);
                $db->bind(':mh', $g('prima_por_hijo'));
                $db->bind(':ph', $primaPorHijoTotal);
                $db->bind(':bt', $g('bono_transporte'));
                $db->bind(':pdis', $g('prima_discapacidad'));
                $db->bind(':ca', $g('caja_ahorro'));
                $db->bind(':si', $sueldoIntegral);
                $db->bind(':mct', $montoCesta);
                $db->execute();
            }

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('bono_vacacional_periodos', 'INSERT', $idPeriodo, null,
            ['periodo' => $periodo, 'fecha_corte' => $fechaCorte, 'empleados' => $totalEmpleados], $userId);
        return $idPeriodo;
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
     * Corrige/completa celdas de captura manual (grado/escala, cuenta bancaria,
     * alícuotas, total) mientras el período siga en Borrador.
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

        $grado  = trim((string)($campos['grado_escala'] ?? $row->grado_escala ?? ''));
        $cuenta = trim((string)($campos['cuenta_bancaria'] ?? $row->cuenta_bancaria ?? ''));
        $alic   = isset($campos['alicuotas']) && $campos['alicuotas'] !== '' ? round((float)$campos['alicuotas'], 2) : (float)$row->alicuotas;
        $total  = isset($campos['total_bono_vacacional']) && $campos['total_bono_vacacional'] !== ''
            ? round((float)$campos['total_bono_vacacional'], 2) : null;

        $db->query("UPDATE bono_vacacional_detalle
                    SET grado_escala = :g, cuenta_bancaria = :c, alicuotas = :a, total_bono_vacacional = :t
                    WHERE id = :id");
        $db->bind(':g', $grado !== '' ? $grado : null);
        $db->bind(':c', $cuenta !== '' ? $cuenta : null);
        $db->bind(':a', $alic);
        $db->bind(':t', $total);
        $db->bind(':id', $idDetalle);
        return $db->execute();
    }

    /** Cantidad + total por tipo de personal (igual que la hoja CUADRO_RESUMEN_). */
    public static function resumen(int $idPeriodo): array
    {
        $db = new Database();
        $db->query("SELECT tipo_personal, COUNT(*) AS cantidad, COALESCE(SUM(total_bono_vacacional), 0) AS total
                    FROM bono_vacacional_detalle WHERE id_periodo = :id GROUP BY tipo_personal");
        $db->bind(':id', $idPeriodo);
        $rows = $db->resultSet();

        $out = [];
        foreach (self::TIPOS as $tipo) $out[$tipo] = ['cantidad' => 0, 'total' => 0.0];
        foreach ($rows as $r) $out[$r->tipo_personal] = ['cantidad' => (int)$r->cantidad, 'total' => (float)$r->total];
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
