<?php
/**
 * Modelo Pasante
 */
class Pasante extends Model {
    protected $table = 'pasantes';

    public function getPasantesConTutor() {
        $this->db->query("
            SELECT p.*, 
                   e.nro_expediente, 
                   per.nombre AS tutor_nombre, 
                   per.apellido AS tutor_apellido
            FROM pasantes p
            LEFT JOIN empleados e ON p.id_tutor_institucional = e.id
            LEFT JOIN personas per ON e.id_persona = per.id
            WHERE p.is_active = TRUE
            ORDER BY p.fecha_inicio DESC
        ");
        return $this->db->resultSet();
    }

    public function getPasanteUnico($id) {
        $this->db->query("
            SELECT p.*, 
                   e.nro_expediente, 
                   per.nombre AS tutor_nombre, 
                   per.apellido AS tutor_apellido
            FROM pasantes p
            LEFT JOIN empleados e ON p.id_tutor_institucional = e.id
            LEFT JOIN personas per ON e.id_persona = per.id
            WHERE p.id = :id AND p.is_active = TRUE
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getDocumentos($id_pasante) {
        $this->db->query("SELECT * FROM pasante_documentos WHERE id_pasante = :id_pasante ORDER BY id ASC");
        $this->db->bind(':id_pasante', $id_pasante);
        return $this->db->resultSet();
    }

    public function saveDocumento($data) {
        $this->db->query("
            INSERT INTO pasante_documentos (id_pasante, tipo_documento, entregado, archivo_url, observaciones, created_by)
            VALUES (:id_pasante, :tipo_documento, :entregado, :archivo_url, :observaciones, :created_by)
        ");
        $this->db->bind(':id_pasante', $data['id_pasante']);
        $this->db->bind(':tipo_documento', $data['tipo_documento']);
        $this->db->bind(':entregado', $data['entregado'] ? 'true' : 'false');
        $this->db->bind(':archivo_url', $data['archivo_url']);
        $this->db->bind(':observaciones', $data['observaciones']);
        $this->db->bind(':created_by', $_SESSION['user_id'] ?? null);
        return $this->db->execute();
    }
}
