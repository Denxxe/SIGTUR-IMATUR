<?php
/**
 * Clase Rol: Modelo para la tabla roles
 */
class Rol extends Model {
    private $id;
    private $nombre;
    private $descripcion;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
        }
    }

    // --- Getters y Setters ---
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }

    /**
     * Obtener todos los roles activos
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM roles WHERE is_active = TRUE ORDER BY nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar un rol por ID
     */
    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM roles WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guardar o actualizar registro
     */
    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE roles SET 
                                nombre = :nombre, 
                                descripcion = :descripcion, 
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id 
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO roles (nombre, descripcion, created_by) 
                              VALUES (:nombre, :descripcion, :user_id)");
        }

        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':user_id', $user_id);

        return $this->db->execute();
    }

    /**
     * Borrado lógico
     */
    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE roles SET 
                        is_active = FALSE, 
                        deleted_at = CURRENT_TIMESTAMP, 
                        deleted_by = :user_id 
                    WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
