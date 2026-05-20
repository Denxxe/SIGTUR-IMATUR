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

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_empleado = $data['id_empleado'] ?? null;
            $this->fecha = $data['fecha'] ?? date('Y-m-d');
            $this->hora_entrada = $data['hora_entrada'] ?? '';
            $this->hora_salida = $data['hora_salida'] ?? null;
            $this->observacion = $data['observacion'] ?? '';
        }
    }

    /**
     * Obtener historial de asistencias con datos de empleados
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT a.*, p.nombre, p.apellido, e.nro_expediente 
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
            $this->db->query("INSERT INTO asistencias (id_empleado, fecha, hora_entrada, observacion, created_by)
                              VALUES (:id_empleado, :fecha, :hora_entrada, :observacion, :user_id)");
            $this->db->bind(':id_empleado', $this->id_empleado);
            $this->db->bind(':fecha', $this->fecha);
            $this->db->bind(':hora_entrada', $this->hora_entrada);
        }
        $this->db->bind(':observacion', $this->observacion);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $nuevos = $this->id
            ? ['hora_salida' => $this->hora_salida, 'observacion' => $this->observacion]
            : ['id_empleado' => $this->id_empleado, 'fecha' => $this->fecha, 'hora_entrada' => $this->hora_entrada];
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
