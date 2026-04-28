<?php
/**
 * Clase Ruta: Modelo para la tabla rutas turísticas
 */
class Ruta extends Model {
    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private string $duracion_estimada;
    private string $nivel_dificultad;
    private string $estado;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->duracion_estimada = $data['duracion_estimada'] ?? '';
            $this->nivel_dificultad = $data['nivel_dificultad'] ?? 'Fácil';
            $this->estado = $data['estado'] ?? 'Activa';
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT r.*, 
                           (SELECT COUNT(*) FROM puntos_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) as total_puntos
                    FROM rutas r WHERE r.is_active = TRUE ORDER BY r.nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM rutas WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE rutas SET nombre=:nombre, descripcion=:descripcion, duracion_estimada=:duracion_estimada,
                              nivel_dificultad=:nivel_dificultad, estado=:estado, 
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO rutas (nombre, descripcion, duracion_estimada, nivel_dificultad, estado, created_by) 
                              VALUES (:nombre, :descripcion, :duracion_estimada, :nivel_dificultad, :estado, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':duracion_estimada', $this->duracion_estimada);
        $this->db->bind(':nivel_dificultad', $this->nivel_dificultad);
        $this->db->bind(':estado', $this->estado);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE rutas SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }

    /**
     * Obtener puntos de una ruta (ordenados)
     */
    public static function getPuntos($id_ruta) {
        $db = new Database();
        $db->query("SELECT * FROM puntos_ruta WHERE id_ruta = :id_ruta AND is_active = TRUE ORDER BY orden ASC");
        $db->bind(':id_ruta', $id_ruta);
        return $db->resultSet();
    }
}
