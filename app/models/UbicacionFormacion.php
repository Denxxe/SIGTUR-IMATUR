<?php
/**
 * Clase UbicacionFormacion: Modelo para ubicaciones de formación (Liceos, plazas, centros)
 */
class UbicacionFormacion extends Model {
    private $id;
    private $nombre;
    private $tipo;
    private $direccion;
    private $municipio;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->tipo = $data['tipo'] ?? '';
            $this->direccion = $data['direccion'] ?? '';
            $this->municipio = $data['municipio'] ?? '';
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM ubicaciones_formacion WHERE is_active = TRUE ORDER BY nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM ubicaciones_formacion WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE ubicaciones_formacion SET nombre=:nombre, tipo=:tipo, direccion=:direccion, 
                              municipio=:municipio, updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO ubicaciones_formacion (nombre, tipo, direccion, municipio, created_by) 
                              VALUES (:nombre, :tipo, :direccion, :municipio, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':tipo', $this->tipo);
        $this->db->bind(':direccion', $this->direccion);
        $this->db->bind(':municipio', $this->municipio);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE ubicaciones_formacion SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
