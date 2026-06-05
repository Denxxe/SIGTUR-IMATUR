<?php
/**
 * Clase Asistencia: Modelo para la tabla asistencias
 */
class Asistencia extends Model {
    private ?int $id;
    private ?int $id_empleado;
    private ?string $fecha;
    private string $hora_entrada;
    private ?string $hora_salida;
    private string $observacion;
    private ?int $minutos_tarde;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_empleado = $data['id_empleado'] ?? null;
            $this->fecha = $data['fecha'] ?? date('Y-m-d');
            $this->hora_entrada = $data['hora_entrada'] ?? '';
            $this->hora_salida = $data['hora_salida'] ?? null;
            $this->observacion = $data['observacion'] ?? '';
            $this->minutos_tarde = isset($data['minutos_tarde']) ? (int)$data['minutos_tarde'] : null;
        }
    }

    /** Tolerancia de puntualidad (minutos) desde configuración; default 15. */
    public static function toleranciaPuntualidad(): int {
        $t = (int) ConfigSistema::get('minutos_tolerancia_puntualidad');
        return $t > 0 ? $t : 15;
    }

    /**
     * Minutos de retraso respecto a la hora de entrada del horario asignado.
     * Devuelve null si el empleado no tiene horario; 0 si llegó a tiempo o antes.
     */
    public static function calcularMinutosTarde($idEmpleado, $horaEntradaReal): ?int {
        $db = new Database();
        $db->query("SELECT h.hora_entrada
                    FROM empleados e INNER JOIN horarios h ON e.id_horario = h.id
                    WHERE e.id = :id AND h.is_active = TRUE");
        $db->bind(':id', $idEmpleado);
        $row = $db->single();
        if (!$row || empty($row->hora_entrada)) return null;
        $prog = strtotime($row->hora_entrada);
        $real = strtotime($horaEntradaReal);
        if ($prog === false || $real === false) return null;
        return max(0, (int) floor(($real - $prog) / 60));
    }

    /**
     * IDs de empleados que ese día están "En actividad" (ruta o formación externa),
     * por lo que su ausencia presencial no se considera falta (RN-RH15).
     */
    public static function empleadosEnActividad($fecha): array {
        $db = new Database();
        $db->query("SELECT DISTINCT e.id
                    FROM empleados e
                    WHERE e.is_active = TRUE AND (
                        EXISTS (SELECT 1 FROM participantes_ruta pr
                                INNER JOIN rutas r ON pr.id_ruta = r.id
                                WHERE pr.id_persona = e.id_persona AND pr.is_active = TRUE
                                  AND r.fecha_visita = :f1)
                        OR EXISTS (SELECT 1 FROM participantes_taller pt
                                INNER JOIN talleres t ON pt.id_taller = t.id
                                WHERE pt.id_persona = e.id_persona AND pt.is_active = TRUE
                                  AND t.es_interna = FALSE
                                  AND :f2 BETWEEN t.fecha_inicio AND COALESCE(t.fecha_fin, t.fecha_inicio))
                    )");
        $db->bind(':f1', $fecha);
        $db->bind(':f2', $fecha);
        $rows = $db->resultSet();
        return array_map(fn($r) => (int)$r->id, $rows);
    }

    /** Asistencias de una fecha con datos del empleado y horas trabajadas. */
    public static function presentesDia($fecha) {
        $db = new Database();
        $db->query("SELECT a.*, e.id AS id_empleado, p.nombre, p.apellido, e.nro_expediente,
                           CASE WHEN a.hora_salida IS NOT NULL
                                THEN ROUND(EXTRACT(EPOCH FROM (a.hora_salida - a.hora_entrada))/3600.0, 2)
                           END AS horas
                    FROM asistencias a
                    INNER JOIN empleados e ON a.id_empleado = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE a.fecha = :f AND a.is_active = TRUE
                    ORDER BY a.hora_entrada ASC");
        $db->bind(':f', $fecha);
        return $db->resultSet();
    }

    /**
     * Obtener historial de asistencias con datos de empleados
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT a.*, p.nombre, p.apellido, e.nro_expediente,
                           CASE WHEN a.hora_salida IS NOT NULL
                                THEN ROUND(EXTRACT(EPOCH FROM (a.hora_salida - a.hora_entrada))/3600.0, 2)
                           END AS horas
                    FROM asistencias a
                    INNER JOIN empleados e ON a.id_empleado = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE a.is_active = TRUE
                    ORDER BY a.fecha DESC, a.hora_entrada DESC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM asistencias WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Buscar asistencia abierta de un empleado (sin hora de salida) para el día actual
     */
    public static function findOpen($id_empleado) {
        $db = new Database();
        $db->query("SELECT * FROM asistencias 
                    WHERE id_empleado = :id_empleado 
                    AND fecha = CURRENT_DATE 
                    AND hora_salida IS NULL 
                    AND is_active = TRUE");
        $db->bind(':id_empleado', $id_empleado);
        return $db->single();
    }

    /**
     * Guardar o actualizar registro
     */
    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE asistencias SET
                                hora_salida = :hora_salida,
                                observacion = :observacion,
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
            $this->db->bind(':hora_salida', $this->hora_salida);
        } else {
            $this->db->query("INSERT INTO asistencias (id_empleado, fecha, hora_entrada, observacion, minutos_tarde, created_by)
                              VALUES (:id_empleado, :fecha, :hora_entrada, :observacion, :minutos_tarde, :user_id)");
            $this->db->bind(':id_empleado', $this->id_empleado);
            $this->db->bind(':fecha', $this->fecha);
            $this->db->bind(':hora_entrada', $this->hora_entrada);
            $this->db->bind(':minutos_tarde', $this->minutos_tarde);
        }
        $this->db->bind(':observacion', $this->observacion);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $nuevos = $this->id
            ? ['hora_salida' => $this->hora_salida, 'observacion' => $this->observacion]
            : ['id_empleado' => $this->id_empleado, 'fecha' => $this->fecha, 'hora_entrada' => $this->hora_entrada, 'minutos_tarde' => $this->minutos_tarde];
        $this->audit('asistencias', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, $nuevos, $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE asistencias SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('asistencias', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
