<?php

/**
 * Clase Municipio: Modelo para la tabla municipios
 */
class Municipio extends Model
{
    private $id;
    private $nombre;
    private $codigo_postal;

    public function __construct($data = [])
    {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->codigo_postal = $data['codigo_postal'] ?? '';
        }
    }

    // --- Getters y Setters ---
    public function getId()
    {
        return $this->id;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
    }
    public function getCodigoPostal()
    {
        return $this->codigo_postal;
    }
    public function setCodigoPostal($codigo_postal)
    {
        $this->codigo_postal = $codigo_postal;
    }

    /**
     * Obtener todos los municipios activos
     */
    public static function all()
    {
        $db = new Database();
        $db->query("SELECT id, nombre, codigo_postal, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by
            FROM public.municipio WHERE is_active = TRUE ORDER BY nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar un municipio por ID
     */
    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT id, nombre, codigo_postal, is_active, created_at, updated_at, deleted_at, created_by, updated_by, deleted_by
            FROM public.municipio WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guardar o actualizar registro
     */
    public function save($user_id = null)
    {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE municipio SET 
                                nombre = :nombre, 
                                codigo_postal = :codigo_postal, 
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id 
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO municipio (nombre, codigo_postal, created_by, is_active, created_at, updated_at, updated_by) 
                              VALUES (:nombre, :codigo_postal, :user_id, TRUE, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP, :user_id) RETURNING id");
        }

        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':codigo_postal', $this->codigo_postal);
        $this->db->bind(':user_id', $user_id);

        if ($this->id) {
            $result = $this->db->execute();
            $this->audit('municipio', 'ACTUALIZAR', $this->id, $previos, ['nombre' => $this->nombre, 'codigo_postal' => $this->codigo_postal], $user_id);
            return $result;
        } else {
            $row = $this->db->single();
            $newId = $row->id ?? null;
            $this->audit('municipio', 'INSERTAR', $newId, null, ['nombre' => $this->nombre, 'codigo_postal' => $this->codigo_postal], $user_id);
            return true;
        }
    }

    /**
     * Borrado lógico
     */
    public static function delete($id, $user_id = null)
    {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE municipio SET 
                        is_active = FALSE, 
                        deleted_at = CURRENT_TIMESTAMP, 
                        deleted_by = :user_id 
                    WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('municipio', 'ELIMINAR', $id, $previos, null, $user_id);
        return $result;
    }
}
