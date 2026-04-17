<?php
/**
 * Clase Cargo: Modelo para la tabla cargos
 */
class Cargo extends Model {
    private $id;
    private $nombre;
    private $descripcion;
    private $sueldo_base;
    private $is_active;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->sueldo_base = $data['sueldo_base'] ?? 0;
            $this->is_active = $data['is_active'] ?? true;
        }
    }

    // --- Getters y Setters ---
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function getSueldoBase() { return $this->sueldo_base; }
    public function setSueldoBase($sueldo) { $this->sueldo_base = $sueldo; }

    /**
     * Obtener todos los cargos activos
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM cargos WHERE is_active = TRUE ORDER BY nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar un cargo por ID
     */
    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM cargos WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guardar o actualizar registro
     */
    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE cargos SET 
                                nombre = :nombre, 
                                descripcion = :descripcion, 
                                sueldo_base = :sueldo, 
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id 
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO cargos (nombre, descripcion, sueldo_base, created_by) 
                              VALUES (:nombre, :descripcion, :sueldo, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':sueldo', $this->sueldo_base);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('cargos', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, ['nombre' => $this->nombre, 'sueldo_base' => $this->sueldo_base], $user_id);
        return $result;
    }

    /**
     * Borrado lógico (Soft Delete)
     */
    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE cargos SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('cargos', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
