<?php
/**
 * Clase ActividadRuta: Eventos programados a lo largo de una ruta turística
 */
class ActividadRuta extends Model {
    private ?int $id;
    private ?int $id_ruta;
    private string $nombre;
    private string $descripcion;
    private ?string $fecha;
    private ?int $id_empleado_responsable;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_ruta = $data['id_ruta'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->fecha = $data['fecha'] ?? null;
            $this->id_empleado_responsable = !empty($data['id_empleado_responsable']) ? $data['id_empleado_responsable'] : null;
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT ar.*, r.nombre as ruta_nombre, p.nombre as emp_nombre, p.apellido as emp_apellido
                    FROM actividades_ruta ar
                    INNER JOIN rutas r ON ar.id_ruta = r.id
                    LEFT JOIN empleados e ON ar.id_empleado_responsable = e.id
                    LEFT JOIN personas p ON e.id_persona = p.id
                    WHERE ar.is_active = TRUE
                    ORDER BY ar.fecha DESC, ar.nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM actividades_ruta WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE actividades_ruta SET id_ruta=:id_ruta, nombre=:nombre, descripcion=:descripcion, 
                              fecha=:fecha, id_empleado_responsable=:id_empleado_responsable, 
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO actividades_ruta (id_ruta, nombre, descripcion, fecha, id_empleado_responsable, created_by) 
                              VALUES (:id_ruta, :nombre, :descripcion, :fecha, :id_empleado_responsable, :user_id)");
        }
        $this->db->bind(':id_ruta', $this->id_ruta);
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':fecha', $this->fecha);
        $this->db->bind(':id_empleado_responsable', $this->id_empleado_responsable);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE actividades_ruta SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
