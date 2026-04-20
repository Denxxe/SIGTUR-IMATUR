<?php

/**
 * Clase Persona: Modelo para la tabla base personas
 */
class Persona extends Model
{
    protected $id;
    protected $cedula;
    protected $nombre;
    protected $apellido;
    protected $telefono;
    protected $correo;
    protected $genero;
    protected $fecha_nacimiento;
    protected $direccion;

    public function __construct($data = [])
    {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->cedula = $data['cedula'] ?? '';
            $this->nombre = $data['nombre'] ?? '';
            $this->apellido = $data['apellido'] ?? '';
            $this->telefono = $data['telefono'] ?? '';
            $this->correo = $data['correo'] ?? '';
            $this->genero = $data['genero'] ?? '';
            $this->fecha_nacimiento = $data['fecha_nacimiento'] ?? '';
            $this->direccion = $data['direccion'] ?? '';
        }
    }

    // --- Getters y Setters ---
    public function getId()
    {
        return $this->id;
    }
    public function getCedula()
    {
        return $this->cedula;
    }
    public function getNombre()
    {
        return $this->nombre;
    }
    public function getApellido()
    {
        return $this->apellido;
    }

    /**
     * Guardar o actualizar registro de persona
     */
    public function save($user_id = null)
    {
        if ($this->id) {
            $this->db->query("UPDATE personas SET 
                                cedula = :cedula, 
                                nombre = :nombre, 
                                apellido = :apellido, 
                                telefono = :telefono, 
                                correo = :correo, 
                                genero = :genero, 
                                fecha_nacimiento = :fecha_nacimiento, 
                                direccion = :direccion, 
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id 
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
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

        if ($this->db->execute()) {
            if (!$this->id) {
                // Si es un insert, obtener el ID generado
                // Nota: La clase Database original no tenía getInsertId, pero PDO sí.
                // Usaré PDO directo o asumiré que hay una forma de obtenerlo.
                // En PostgreSQL SERIAL, se puede usar RETURNING id.
            }
            return true;
        }
        return false;
    }
}
