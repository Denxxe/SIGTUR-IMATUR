<?php
/**
 * Vacacion — gestión de vacaciones (R-8 / 3A).
 *
 * Reglas (confirmadas 2026-06-21):
 *   · Derecho del año = 15 días HÁBILES + 1 por año de servicio, TOPE 30.
 *   · Antigüedad = total: usa empleados.fecha_ingreso_administracion (comisionados)
 *     y, si es NULL, empleados.fecha_ingreso (IMATUR).
 *   · Días contados = hábiles (sin fines de semana NI feriados, ver Feriado).
 *   · Derecho ACUMULADO = suma de los derechos de cada año de servicio cumplido;
 *     el saldo disponible = acumulado − días ya disfrutados/aprobados (no expira).
 *
 * Cada fila de `vacaciones` es un PERÍODO solicitado/disfrutado (varios por año).
 * El cobro/liquidación NO se calcula aquí (pendiente de formatos de nómina, 3B).
 */
class Vacacion extends Model {

    const ESTADOS         = ['Pendiente', 'Aprobado', 'En Curso', 'Completado', 'Rechazado'];
    const ESTADO_DEFAULT  = 'Pendiente';
    const DIAS_BASE       = 15;
    const TOPE_DIAS       = 30;
    const ESTADO_BADGES   = [
        'Pendiente'  => 'sig-badge--warning',
        'Aprobado'   => 'sig-badge--info',
        'En Curso'   => 'sig-badge--success',
        'Completado' => 'sig-badge--neutral',
        'Rechazado'  => 'sig-badge--danger',
    ];
    // Estados que consumen saldo (cuentan como días disfrutados/comprometidos)
    const ESTADOS_CONSUMEN = ['Aprobado', 'En Curso', 'Completado'];

    // ── Antigüedad ───────────────────────────────────────────────────────────
    /** Fecha base de servicio: ingreso a la administración (comisionados) o a IMATUR. */
    public static function fechaBaseServicio($empleado): ?string {
        if (!empty($empleado->fecha_ingreso_administracion)) return $empleado->fecha_ingreso_administracion;
        return $empleado->fecha_ingreso ?? null;
    }

    /** Años de servicio cumplidos hasta hoy (o hasta $hasta). */
    public static function aniosServicio($empleado, ?string $hasta = null): int {
        $base = self::fechaBaseServicio($empleado);
        if (!$base) return 0;
        try {
            $ini = new \DateTime($base);
            $fin = $hasta ? new \DateTime($hasta) : new \DateTime('today');
        } catch (\Exception $e) { return 0; }
        if ($fin < $ini) return 0;
        return (int)$ini->diff($fin)->y;
    }

    /** Derecho de UN año, dado los años de servicio cumplidos: 15 + años, tope 30. */
    public static function diasPorAnios(int $anios): int {
        return min(self::DIAS_BASE + max(0, $anios), self::TOPE_DIAS);
    }

    /** Derecho del año en curso (lo que corresponde según la antigüedad actual). */
    public static function derechoAnioActual($empleado): int {
        return self::diasPorAnios(self::aniosServicio($empleado));
    }

    /**
     * Derecho ACUMULADO histórico: por cada año de servicio cumplido se gana el
     * derecho de ese año (año 1 → 15, año 2 → 16 … tope 30). Con 0 años → 0.
     */
    public static function derechoAcumulado($empleado, ?string $hasta = null): int {
        $anios = self::aniosServicio($empleado, $hasta);
        $total = 0;
        for ($k = 1; $k <= $anios; $k++) $total += self::diasPorAnios($k - 1);
        return $total;
    }

    /** Total de días disfrutados/comprometidos (estados que consumen saldo). */
    public static function totalDisfrutado(int $idEmpleado): int {
        $db = new Database();
        $in = "'" . implode("','", self::ESTADOS_CONSUMEN) . "'";
        $db->query("SELECT COALESCE(SUM(dias_tomados),0) AS t FROM vacaciones
                    WHERE id_empleado = :id AND is_active = TRUE AND estado IN ($in)");
        $db->bind(':id', $idEmpleado);
        return (int)($db->single()->t ?? 0);
    }

    /**
     * Saldo disponible = derecho acumulado − ajuste inicial (días disfrutados antes
     * del sistema) − días ya disfrutados/comprometidos en períodos registrados.
     */
    public static function saldo($empleado): int {
        $ajuste = (int)($empleado->vacaciones_ajuste_dias ?? 0);
        return self::derechoAcumulado($empleado) - $ajuste - self::totalDisfrutado((int)$empleado->id);
    }

    // ── Conteo de días hábiles (sin fines de semana ni feriados) ──────────────
    public static function diasHabiles(string $inicio, string $fin): int {
        try {
            $ini = new \DateTime($inicio);
            $f   = new \DateTime($fin);
        } catch (\Exception $e) { return 0; }
        if ($f < $ini) return 0;
        $fer = Feriado::lookup();
        $count = 0;
        $cur = clone $ini;
        while ($cur <= $f) {
            $dow = (int)$cur->format('N');            // 1=Lun … 7=Dom
            $esFinde   = ($dow >= 6);
            $esFeriado = isset($fer['md'][$cur->format('m-d')]) || isset($fer['ymd'][$cur->format('Y-m-d')]);
            if (!$esFinde && !$esFeriado) $count++;
            $cur->modify('+1 day');
        }
        return $count;
    }

    // ── Períodos ──────────────────────────────────────────────────────────────
    public static function porEmpleado(int $idEmpleado): array {
        $db = new Database();
        $db->query("SELECT * FROM vacaciones WHERE id_empleado = :id AND is_active = TRUE
                    ORDER BY fecha_inicio DESC NULLS LAST, id DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    public static function find(int $id) {
        $db = new Database();
        $db->query("SELECT * FROM vacaciones WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /** Crea un período (estado Pendiente). $dias = días hábiles calculados. */
    public static function crear(int $idEmpleado, int $anio, string $inicio, string $fin, int $dias, ?string $obs, $userId = null): bool {
        $db = new Database();
        $db->query("INSERT INTO vacaciones (id_empleado, anio, dias_correspondientes, dias_tomados, fecha_inicio, fecha_fin, estado, observaciones, created_by)
                    VALUES (:emp, :anio, :corr, :dias, :ini, :fin, :est, :obs, :uid)");
        $db->bind(':emp', $idEmpleado);
        $db->bind(':anio', $anio);
        $db->bind(':corr', $dias);   // referencia: días del período
        $db->bind(':dias', $dias);
        $db->bind(':ini', $inicio);
        $db->bind(':fin', $fin);
        $db->bind(':est', self::ESTADO_DEFAULT);
        $db->bind(':obs', $obs);
        $db->bind(':uid', $userId);
        $ok = $db->execute();
        self::auditStatic('vacaciones', 'INSERT', 0, null, ['id_empleado' => $idEmpleado, 'anio' => $anio, 'dias' => $dias], $userId);
        return $ok;
    }

    public static function cambiarEstado(int $id, string $estado, $userId = null): bool {
        if (!in_array($estado, self::ESTADOS, true)) return false;
        $previo = self::find($id);
        $db = new Database();
        $db->query("UPDATE vacaciones SET estado = :est, updated_at = CURRENT_TIMESTAMP, updated_by = :uid WHERE id = :id");
        $db->bind(':est', $estado);
        $db->bind(':uid', $userId);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('vacaciones', 'UPDATE', $id, $previo, ['estado' => $estado], $userId);
        return $ok;
    }

    public static function eliminar(int $id, $userId = null): bool {
        $previo = self::find($id);
        $db = new Database();
        $db->query("UPDATE vacaciones SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':uid', $userId);
        $ok = $db->execute();
        self::auditStatic('vacaciones', 'DELETE', $id, $previo, null, $userId);
        return $ok;
    }
}
