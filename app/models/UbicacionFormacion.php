<?php

/**
 * Clase UbicacionFormacion: Modelo para ubicaciones de formación (Liceos, plazas, centros)
 */
class UbicacionFormacion extends Model
{
    private ?int $id;
    private string $nombre;
    private string $tipo;
    private string $direccion;
    private ?int $id_parroquia;

    public function __construct(array $data = [])
    {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->tipo = $data['tipo'] ?? '';
            $this->direccion = $data['direccion'] ?? '';
            $this->id_parroquia = $data['id_parroquia'] ?? '';
        }
    }

    public static function all()
    {
        $db = new Database();
        $db->query("SELECT u.*, p.nombre AS nombre_parroquia FROM ubicaciones_formacion u LEFT JOIN parroquia p ON u.parroquia = p.id WHERE u.is_active = TRUE ORDER BY u.nombre ASC");
        return $db->resultSet();
    }

    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT u.*, p.nombre AS nombre_parroquia FROM ubicaciones_formacion u LEFT JOIN parroquia p ON u.parroquia = p.id WHERE u.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null)
    {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE ubicaciones_formacion SET nombre=:nombre, tipo=:tipo, direccion=:direccion,
                              parroquia=:id_parroquia, updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO ubicaciones_formacion (nombre, tipo, direccion, parroquia, created_by)
                              VALUES (:nombre, :tipo, :direccion, :id_parroquia, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':tipo', $this->tipo);
        $this->db->bind(':direccion', $this->direccion);
        $this->db->bind(':id_parroquia', $this->id_parroquia);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('ubicaciones_formacion', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['nombre' => $this->nombre, 'tipo' => $this->tipo, 'direccion' => $this->direccion], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null)
    {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE ubicaciones_formacion SET  is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('ubicaciones_formacion', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
