<?php
/**
 * Clase Empleado: Modelo para la tabla empleados (Hereda atributos de Persona en el flujo)
 */
class Empleado extends Model {
    // Datos de personas
    private $id_persona;
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $correo;
    private $genero;
    private $fecha_nacimiento;
    private $direccion;

    // Datos de empleados
    private $id;
    private $id_cargo;
    private $id_departamento;
    private $nro_expediente;
    private $fecha_ingreso;

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
            $this->id_cargo = $data['id_cargo'] ?? '';
            $this->id_departamento = $data['id_departamento'] ?? '';
            $this->nro_expediente = $data['nro_expediente'] ?? '';
            $this->fecha_ingreso = $data['fecha_ingreso'] ?? date('Y-m-d');
        }
    }

    /**
     * Obtener listado completo de empleados con joins
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT e.*, p.cedula, p.nombre, p.apellido, c.nombre as cargo, d.nombre as departamento 
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN cargos c ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                    ORDER BY p.nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar un empleado por ID
     */
    public static function find($id) {
        $db = new Database();
        $db->query("SELECT e.*, p.*, e.id as id
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE e.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guardar registro (Atómico: Persona + Empleado)
     */
    public function save($user_id = null) {
        try {
            $this->db->beginTransaction();

            if ($this->id) {
                // UPDATE Persona
                $this->db->query("UPDATE personas SET cedula=:cedula, nombre=:nombre, apellido=:apellido, telefono=:telefono, 
                                 correo=:correo, genero=:genero, fecha_nacimiento=:fecha_nacimiento, direccion=:direccion, 
                                 updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id_persona");
                $this->db->bind(':id_persona', $this->id_persona);
            } else {
                // INSERT Persona
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
                $this->id_persona = $this->db->lastInsertId('personas_id_seq');
            }

            // --- EMPLEADO ---
            if ($this->id) {
                $this->db->query("UPDATE empleados SET id_cargo=:id_cargo, id_departamento=:id_departamento, nro_expediente=:nro_expediente, 
                                 updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
                $this->db->bind(':id', $this->id);
            } else {
                $this->db->query("INSERT INTO empleados (id_persona, id_cargo, id_departamento, nro_expediente, fecha_ingreso, created_by) 
                                 VALUES (:id_persona, :id_cargo, :id_departamento, :nro_expediente, :fecha_ingreso, :user_id)");
                $this->db->bind(':id_persona', $this->id_persona);
                $this->db->bind(':fecha_ingreso', $this->fecha_ingreso);
            }

            $this->db->bind(':id_cargo', $this->id_cargo);
            $this->db->bind(':id_departamento', $this->id_departamento);
            $this->db->bind(':nro_expediente', $this->nro_expediente);
            $this->db->bind(':user_id', $user_id);
            $this->db->execute();

            $this->db->endTransaction();
            return true;
        } catch (Exception $e) {
            $this->db->cancelTransaction();
            return false;
        }
    }

    /**
     * Borrado lógico (Solo marca el empleado como inactivo)
     */
    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE empleados SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
