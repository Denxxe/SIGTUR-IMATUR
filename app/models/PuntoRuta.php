<?php
/**
 * Clase PuntoRuta: Modelo para los puntos/paradas de una ruta turística
 */
class PuntoRuta extends Model {
    private $id;
    private $id_ruta;
    private $nombre;
    private $descripcion;
    private $orden;
    private $latitud;
    private $longitud;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_ruta = $data['id_ruta'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->orden = $data['orden'] ?? 1;
            $this->latitud = $data['latitud'] ?? null;
            $this->longitud = $data['longitud'] ?? null;
        }
    }

    public static function allByRuta($id_ruta) {
        $db = new Database();
        $db->query("SELECT * FROM puntos_ruta WHERE id_ruta = :id_ruta AND is_active = TRUE ORDER BY orden ASC");
        $db->bind(':id_ruta', $id_ruta);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM puntos_ruta WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE puntos_ruta SET nombre=:nombre, descripcion=:descripcion, orden=:orden,
                              latitud=:latitud, longitud=:longitud, updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO puntos_ruta (id_ruta, nombre, descripcion, orden, latitud, longitud, created_by) 
                              VALUES (:id_ruta, :nombre, :descripcion, :orden, :latitud, :longitud, :user_id)");
            $this->db->bind(':id_ruta', $this->id_ruta);
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':orden', $this->orden);
        $this->db->bind(':latitud', $this->latitud);
        $this->db->bind(':longitud', $this->longitud);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE puntos_ruta SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
