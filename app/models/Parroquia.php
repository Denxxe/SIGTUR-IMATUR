<?php

/**
 * Clase Parroquia: Modelo para la tabla parroquias
 */
class Parroquia extends Model
{
    private ?int $id;
    private string $nombre;
    private ?int $id_municipio;

    public function __construct(array $data = [])
    {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->id_municipio = $data['id_municipio'] ?? '';
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
    public function getIdMunicipio()
    {
        return $this->id_municipio;
    }
    public function setIdMunicipio($id_municipio)
    {
        $this->id_municipio = $id_municipio;
    }

    /**
     * Obtener todas las parroquias activas
     */
    public static function all()
    {
        $db = new Database();
        $db->query("SELECT p.id, p.nombre, p.id_municipio, m.nombre AS municipio, p.is_active, p.create_by, p.update_by, p.delete_by, p.create_at, p.update_at, p.delete_at,
                           (SELECT COALESCE(NULLIF(TRIM(COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')), ''), u.username)
                            FROM usuarios u
                            LEFT JOIN empleados e  ON u.id_empleado = e.id
                            LEFT JOIN personas per ON e.id_persona  = per.id
                            WHERE u.id = p.create_by) AS creado_por
        FROM public.parroquia p
        LEFT JOIN public.municipio m ON p.id_municipio = m.id
        WHERE p.is_active = TRUE AND m.is_active = TRUE
        ORDER BY p.nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar una parroquia por ID
     */
    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT p.id, p.nombre, p.id_municipio, m.nombre AS municipio, p.is_active, p.create_by, p.update_by, p.delete_by, p.create_at, p.update_at, p.delete_at
        FROM public.parroquia p
        LEFT JOIN public.municipio m ON p.id_municipio = m.id 
        WHERE p.is_active = TRUE AND p.id = :id");
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
            $this->db->query("UPDATE parroquia SET 
                                nombre = :nombre, 
                                id_municipio = :id_municipio, 
                                update_at = CURRENT_TIMESTAMP,
                                update_by = :user_id 
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO parroquia (nombre, id_municipio, create_by, is_active, update_at, update_by, create_at ) 
                              VALUES (:nombre, :id_municipio, :user_id, TRUE, CURRENT_TIMESTAMP, :user_id, CURRENT_TIMESTAMP) RETURNING id");
        }

        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':id_municipio', $this->id_municipio);
        $this->db->bind(':user_id', $user_id);

        if ($this->id) {
            $result = $this->db->execute();
            $this->audit('parroquia', 'ACTUALIZAR', $this->id, $previos, ['nombre' => $this->nombre, 'id_municipio' => $this->id_municipio], $user_id);
            return $result;
        } else {
            $row = $this->db->single();
            $newId = $row->id ?? null;
            $this->audit('parroquia', 'INSERTAR', $newId, null, ['nombre' => $this->nombre, 'id_municipio' => $this->id_municipio], $user_id);
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
        $db->query("UPDATE parroquia SET 
                        is_active = FALSE, 
                        delete_at = CURRENT_TIMESTAMP, 
                        delete_by = :user_id 
                    WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('parroquia', 'ELIMINAR', $id, $previos, null, $user_id);
        return $result;
    }
}
