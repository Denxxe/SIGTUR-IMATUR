<?php
/**
 * Clase PuntoRuta: Modelo para los puntos/paradas de una ruta turística
 */
class PuntoRuta extends Model {
    private ?int $id;
    private ?int $id_ruta;
    private string $nombre;
    private string $descripcion;
    private int $orden;
    private ?float $latitud;
    private ?float $longitud;
    /** R-06/R-20: la institucion a la que hay que pedir permiso de acceso. */
    private ?string $ente_custodio;
    /** R-31: el punto aporta su propio guia, que se suma al de IMATUR. */
    private bool $tiene_guia_externo;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_ruta = $data['id_ruta'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->orden = $data['orden'] ?? 1;
            $this->latitud = $data['latitud'] ?? null;
            $this->longitud = $data['longitud'] ?? null;
            $this->ente_custodio = ($data['ente_custodio'] ?? '') !== '' ? $data['ente_custodio'] : null;
            $this->tiene_guia_externo = !empty($data['tiene_guia_externo']);
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
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE puntos_ruta SET nombre=:nombre, descripcion=:descripcion, orden=:orden,
                              latitud=:latitud, longitud=:longitud, ente_custodio=:custodio,
                              tiene_guia_externo=:guiaext,
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO puntos_ruta (id_ruta, nombre, descripcion, orden, latitud, longitud, ente_custodio, tiene_guia_externo, created_by)
                              VALUES (:id_ruta, :nombre, :descripcion, :orden, :latitud, :longitud, :custodio, :guiaext, :user_id)");
            $this->db->bind(':id_ruta', $this->id_ruta);
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':orden', $this->orden);
        $this->db->bind(':latitud', $this->latitud);
        $this->db->bind(':longitud', $this->longitud);
        $this->db->bind(':custodio', $this->ente_custodio);
        $this->db->bind(':guiaext',  $this->tiene_guia_externo, PDO::PARAM_BOOL);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('puntos_ruta', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['nombre' => $this->nombre, 'id_ruta' => $this->id_ruta, 'orden' => $this->orden], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE puntos_ruta SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('puntos_ruta', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
