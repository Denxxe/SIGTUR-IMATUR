<?php
/**
 * Clase ActividadInventario: Modelo para movimientos/actividad de inventario
 */
class ActividadInventario extends Model {
    private ?int $id;
    private ?int $id_inventario;
    private string $tipo_movimiento;
    private string $descripcion;
    private ?string $fecha;
    private ?int $id_empleado_responsable;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_inventario = $data['id_inventario'] ?? null;
            $this->tipo_movimiento = $data['tipo_movimiento'] ?? 'Asignacion';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->fecha = $data['fecha'] ?? date('Y-m-d');
            $this->id_empleado_responsable = $data['id_empleado_responsable'] ?? null;
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT ai.*, i.nombre as item_nombre, i.codigo_bn, 
                           p.nombre as emp_nombre, p.apellido as emp_apellido
                    FROM actividad_inventario ai
                    INNER JOIN inventario i ON ai.id_inventario = i.id
                    LEFT JOIN empleados e ON ai.id_empleado_responsable = e.id
                    LEFT JOIN personas p ON e.id_persona = p.id
                    WHERE ai.is_active = TRUE
                    ORDER BY ai.fecha DESC");
        return $db->resultSet();
    }

    public static function byItem($id_inventario) {
        $db = new Database();
        $db->query("SELECT ai.*, p.nombre as emp_nombre, p.apellido as emp_apellido
                    FROM actividad_inventario ai
                    LEFT JOIN empleados e ON ai.id_empleado_responsable = e.id
                    LEFT JOIN personas p ON e.id_persona = p.id
                    WHERE ai.id_inventario = :id_inventario AND ai.is_active = TRUE
                    ORDER BY ai.fecha DESC");
        $db->bind(':id_inventario', $id_inventario);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM actividad_inventario WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE actividad_inventario SET tipo_movimiento=:tipo_movimiento, descripcion=:descripcion,
                              fecha=:fecha, id_empleado_responsable=:id_empleado_responsable,
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO actividad_inventario (id_inventario, tipo_movimiento, descripcion, fecha, id_empleado_responsable, created_by)
                              VALUES (:id_inventario, :tipo_movimiento, :descripcion, :fecha, :id_empleado_responsable, :user_id)");
            $this->db->bind(':id_inventario', $this->id_inventario);
        }
        $this->db->bind(':tipo_movimiento', $this->tipo_movimiento);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':fecha', $this->fecha);
        $this->db->bind(':id_empleado_responsable', $this->id_empleado_responsable);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('actividad_inventario', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['id_inventario' => $this->id_inventario, 'tipo_movimiento' => $this->tipo_movimiento, 'descripcion' => $this->descripcion, 'fecha' => $this->fecha], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE actividad_inventario SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('actividad_inventario', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
