<?php
/**
 * Clase Visita: Registro de ingreso de visitantes a la institución
 */
class Visita extends Model {
    private $id;
    private $id_visitante;
    private $id_empleado_visitado;
    private $motivo;
    private $fecha;
    private $hora_entrada;
    private $hora_salida;
    private $observaciones;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_visitante = $data['id_visitante'] ?? null;
            $this->id_empleado_visitado = $data['id_empleado_visitado'] ?? null;
            $this->motivo = $data['motivo'] ?? '';
            $this->fecha = $data['fecha'] ?? date('Y-m-d');
            $this->hora_entrada = $data['hora_entrada'] ?? date('H:i:s');
            $this->hora_salida = $data['hora_salida'] ?? null;
            $this->observaciones = $data['observaciones'] ?? '';
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT v.*, vis.cedula as vis_cedula, vis_p.nombre as vis_nombre, vis_p.apellido as vis_apellido, 
                           emp_p.nombre as emp_nombre, emp_p.apellido as emp_apellido
                    FROM visitas v
                    INNER JOIN visitantes vis ON v.id_visitante = vis.id
                    INNER JOIN personas vis_p ON vis.id_persona = vis_p.id
                    INNER JOIN empleados emp ON v.id_empleado_visitado = emp.id
                    INNER JOIN personas emp_p ON emp.id_persona = emp_p.id
                    WHERE v.is_active = TRUE
                    ORDER BY v.fecha DESC, v.hora_entrada DESC");
        return $db->resultSet();
    }

    public static function findOpen($id_visitante) {
        $db = new Database();
        $db->query("SELECT * FROM visitas WHERE id_visitante = :id_visitante AND hora_salida IS NULL AND is_active = TRUE");
        $db->bind(':id_visitante', $id_visitante);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE visitas SET hora_salida = :hora_salida, observaciones = :observaciones, 
                              updated_at = CURRENT_TIMESTAMP, updated_by = :user_id WHERE id = :id");
            $this->db->bind(':id', $this->id);
            $this->db->bind(':hora_salida', $this->hora_salida);
        } else {
            $this->db->query("INSERT INTO visitas (id_visitante, id_empleado_visitado, motivo, fecha, hora_entrada, observaciones, created_by) 
                              VALUES (:id_visitante, :id_empleado_visitado, :motivo, :fecha, :hora_entrada, :observaciones, :user_id)");
            $this->db->bind(':id_visitante', $this->id_visitante);
            $this->db->bind(':id_empleado_visitado', $this->id_empleado_visitado);
            $this->db->bind(':motivo', $this->motivo);
            $this->db->bind(':fecha', $this->fecha);
            $this->db->bind(':hora_entrada', $this->hora_entrada);
        }
        $this->db->bind(':observaciones', $this->observaciones);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE visitas SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
