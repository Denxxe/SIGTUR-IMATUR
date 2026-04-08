<?php
/**
 * Clase Actividad: Modelo para la tabla actividades (Eventos turísticos e institucionales)
 */
class Actividad extends Model {
    private $id;
    private $nombre;
    private $tipo; // 'Turística', 'Institucional', 'Comunitaria'
    private $descripcion;
    private $fecha_inicio;
    private $fecha_fin;
    private $lugar;
    private $presupuesto;
    private $estado; // 'Planificada', 'En Ejecución', 'Culminada', 'Cancelada'

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->tipo = $data['tipo'] ?? 'Institucional';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->fecha_inicio = $data['fecha_inicio'] ?? date('Y-m-d');
            $this->fecha_fin = $data['fecha_fin'] ?? date('Y-m-d');
            $this->lugar = $data['lugar'] ?? '';
            $this->presupuesto = $data['presupuesto'] ?? 0.00;
            $this->estado = $data['estado'] ?? 'Planificada';
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM actividades WHERE is_active = TRUE ORDER BY fecha_inicio DESC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM actividades WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE actividades SET nombre=:nombre, tipo=:tipo, descripcion=:descripcion, 
                              fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin, lugar=:lugar, presupuesto=:presupuesto, 
                              estado=:estado, updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO actividades (nombre, tipo, descripcion, fecha_inicio, fecha_fin, lugar, presupuesto, estado, created_by) 
                              VALUES (:nombre, :tipo, :descripcion, :fecha_inicio, :fecha_fin, :lugar, :presupuesto, :estado, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':tipo', $this->tipo);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':fecha_inicio', $this->fecha_inicio);
        $this->db->bind(':fecha_fin', $this->fecha_fin);
        $this->db->bind(':lugar', $this->lugar);
        $this->db->bind(':presupuesto', $this->presupuesto);
        $this->db->bind(':estado', $this->estado);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE actividades SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
