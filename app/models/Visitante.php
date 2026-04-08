<?php
/**
 * Clase Visitante: Modelo para la tabla visitantes (Persona + Visitante)
 */
class Visitante extends Model {
    // Datos personales
    private $id_persona;
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $genero;
    private $fecha_nacimiento;
    private $direccion;

    // Datos visitantes
    private $id;
    private $procedencia;
    private $motivo_frecuente;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_persona = $data['id_persona'] ?? null;
            $this->cedula = $data['cedula'] ?? '';
            $this->nombre = $data['nombre'] ?? '';
            $this->apellido = $data['apellido'] ?? '';
            $this->telefono = $data['telefono'] ?? '';
            $this->correo = $data['correo'] ?? '';
            $this->genero = $data['genero'] ?? '';
            $this->fecha_nacimiento = $data['fecha_nacimiento'] ?? '';
            $this->direccion = $data['direccion'] ?? '';
            $this->procedencia = $data['procedencia'] ?? '';
            $this->motivo_frecuente = $data['motivo_frecuente'] ?? '';
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT v.*, p.cedula, p.nombre, p.apellido, p.telefono
                    FROM visitantes v
                    INNER JOIN personas p ON v.id_persona = p.id
                    WHERE v.is_active = TRUE AND p.is_active = TRUE
                    ORDER BY p.nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT v.*, p.*, v.id as id
                    FROM visitantes v
                    INNER JOIN personas p ON v.id_persona = p.id
                    WHERE v.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        try {
            $this->db->beginTransaction();

            if ($this->id) {
                $this->db->query("UPDATE personas SET cedula=:cedula, nombre=:nombre, apellido=:apellido, telefono=:telefono, 
                                 correo=:correo, genero=:genero, fecha_nacimiento=:fecha_nacimiento, direccion=:direccion, 
                                 updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id_persona");
                $this->db->bind(':id_persona', $this->id_persona);
            } else {
                $this->db->query("INSERT INTO personas (cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento, direccion, created_by) 
                                 VALUES (:cedula, :nombre, :apellido, :telefono, :correo, :genero, :fecha_nacimiento, :direccion, :user_id)");
            }
            
            $this->db->bind(':cedula', $this->cedula);
            $this->db->bind(':nombre', $this->nombre);
            $this->db->bind(':apellido', $this->apellido);
            $this->db->bind(':telefono', $this->telefono);
            $this->db->bind(':correo', $this->correo);
            $this->db->bind(':genero', $this->genero);
            $this->db->bind(':fecha_nacimiento', $this->fecha_nacimiento);
            $this->db->bind(':direccion', $this->direccion);
            $this->db->bind(':user_id', $user_id);
            $this->db->execute();

            if (!$this->id) {
                $this->id_persona = $this->db->lastInsertId();
            }

            if ($this->id) {
                $this->db->query("UPDATE visitantes SET procedencia=:procedencia, motivo_frecuente=:motivo_frecuente, 
                                 updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
                $this->db->bind(':id', $this->id);
            } else {
                $this->db->query("INSERT INTO visitantes (id_persona, procedencia, motivo_frecuente, created_by) 
                                 VALUES (:id_persona, :procedencia, :motivo_frecuente, :user_id)");
                $this->db->bind(':id_persona', $this->id_persona);
            }

            $this->db->bind(':procedencia', $this->procedencia);
            $this->db->bind(':motivo_frecuente', $this->motivo_frecuente);
            $this->db->bind(':user_id', $user_id);
            $this->db->execute();

            $this->db->endTransaction();
            return true;
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE visitantes SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
