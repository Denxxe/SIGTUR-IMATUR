<?php

/**
 * Historial salarial por empleado (sueldo básico + primas). Append-only —
 * cada cambio inserta una fila nueva, nunca se edita una existente (mismo
 * patrón que empleado_traslados). El valor "actual"/"vigente" es siempre la
 * fila con fecha_efectiva más reciente hasta la fecha consultada.
 */
class Sueldo extends Model
{
    /** Columnas monetarias que componen el sueldo integral. */
    const COMPONENTES = [
        'sueldo_basico', 'prima_profesional', 'prima_responsabilidad',
        'prima_antiguedad', 'bono_transporte', 'prima_fond',
        'prima_discapacidad', 'caja_ahorro',
    ];

    /**
     * Sueldo vigente de un empleado a una fecha dada (hoy si se omite):
     * la fila con fecha_efectiva más reciente que no sea posterior a esa fecha.
     */
    public static function actual(int $idEmpleado, ?string $fecha = null)
    {
        $db = new Database();
        $db->query("SELECT * FROM empleado_salarios
                    WHERE id_empleado = :id AND fecha_efectiva <= :f
                    ORDER BY fecha_efectiva DESC, id DESC LIMIT 1");
        $db->bind(':id', $idEmpleado);
        $db->bind(':f', $fecha ?: date('Y-m-d'));
        return $db->single();
    }

    /** Historial completo (más reciente primero). */
    public static function historial(int $idEmpleado): array
    {
        $db = new Database();
        $db->query("SELECT * FROM empleado_salarios WHERE id_empleado = :id ORDER BY fecha_efectiva DESC, id DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    /**
     * Registra un nuevo valor salarial (INSERT, nunca UPDATE — conserva el
     * historial completo para poder reconstruir el sueldo vigente en una
     * fecha pasada, por ejemplo al calcular una liquidación).
     */
    public static function guardar(int $idEmpleado, array $datos, string $fechaEfectiva, ?string $motivo, ?int $userId = null): bool
    {
        $vals = [];
        foreach (array_merge(self::COMPONENTES, ['prima_por_hijo']) as $campo) {
            $vals[$campo] = round((float)($datos[$campo] ?? 0), 2);
        }

        $db = new Database();
        $db->query("INSERT INTO empleado_salarios
                    (id_empleado, fecha_efectiva, sueldo_basico, prima_profesional, prima_responsabilidad,
                     prima_antiguedad, prima_por_hijo, bono_transporte, prima_fond, prima_discapacidad,
                     caja_ahorro, motivo, created_by)
                    VALUES (:id, :fecha, :sb, :pp, :pr, :pa, :ph, :bt, :pf, :pd, :ca, :mo, :uid)");
        $db->bind(':id', $idEmpleado);
        $db->bind(':fecha', $fechaEfectiva ?: date('Y-m-d'));
        $db->bind(':sb', $vals['sueldo_basico']);
        $db->bind(':pp', $vals['prima_profesional']);
        $db->bind(':pr', $vals['prima_responsabilidad']);
        $db->bind(':pa', $vals['prima_antiguedad']);
        $db->bind(':ph', $vals['prima_por_hijo']);
        $db->bind(':bt', $vals['bono_transporte']);
        $db->bind(':pf', $vals['prima_fond']);
        $db->bind(':pd', $vals['prima_discapacidad']);
        $db->bind(':ca', $vals['caja_ahorro']);
        $db->bind(':mo', $motivo ?: null);
        $db->bind(':uid', $userId);
        $result = $db->execute();

        self::auditStatic('empleado_salarios', 'INSERT', $idEmpleado, null, $vals, $userId);
        return $result;
    }

    /** Suma de todos los componentes salariales (sin la prima por hijo, que depende de n_hijos). */
    public static function totalComponentes($sueldo): float
    {
        $total = 0.0;
        foreach (self::COMPONENTES as $campo) $total += (float)($sueldo->{$campo} ?? 0);
        return round($total, 2);
    }

    /** Sueldo integral mensual: componentes fijos + (prima_por_hijo unitaria × cantidad de hijos). */
    public static function integralMensual($sueldo, int $nHijos): float
    {
        if (!$sueldo) return 0.0;
        $total = self::totalComponentes($sueldo) + ((float)($sueldo->prima_por_hijo ?? 0) * max(0, $nHijos));
        return round($total, 2);
    }

    /** Sueldo integral diario (mensual / 30, convención venezolana). */
    public static function integralDiario($sueldo, int $nHijos): float
    {
        return round(self::integralMensual($sueldo, $nHijos) / 30, 2);
    }

    /** Cantidad de hijos vivos y activos en la carga familiar del empleado (para prima por hijo). */
    public static function contarHijos(int $idPersona): int
    {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM carga_familiar
                    WHERE id_persona = :id AND parentesco = 'Hijo' AND vive = TRUE AND is_active = TRUE");
        $db->bind(':id', $idPersona);
        $row = $db->single();
        return (int)($row->total ?? 0);
    }
}
