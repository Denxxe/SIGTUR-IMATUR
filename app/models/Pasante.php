<?php
class Pasante extends Model {
    protected $table = 'pasantes';

    public function getPasantesConTutor() {
        $this->db->query("
            SELECT p.*,
                   pp.cedula, pp.nombre, pp.apellido,
                   pp.telefono, pp.correo,
                   pt.nombre AS tutor_nombre,
                   pt.apellido AS tutor_apellido
            FROM pasantes p
            INNER JOIN personas pp ON p.id_persona = pp.id
            LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
            LEFT  JOIN personas pt  ON e.id_persona = pt.id
            WHERE p.is_active = TRUE
            ORDER BY p.fecha_inicio DESC
        ");
        return $this->db->resultSet();
    }

    public function getPasanteUnico($id) {
        $this->db->query("
            SELECT p.*,
                   pp.cedula, pp.nombre, pp.apellido,
                   pp.telefono, pp.correo, pp.genero, pp.fecha_nacimiento, pp.direccion,
                   pt.nombre AS tutor_nombre,
                   pt.apellido AS tutor_apellido
            FROM pasantes p
            INNER JOIN personas pp ON p.id_persona = pp.id
            LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
            LEFT  JOIN personas pt  ON e.id_persona = pt.id
            WHERE p.id = :id AND p.is_active = TRUE
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function getById($id) {
        $this->db->query("
            SELECT p.*,
                   pp.cedula, pp.nombre, pp.apellido,
                   pp.telefono, pp.correo
            FROM pasantes p
            INNER JOIN personas pp ON p.id_persona = pp.id
            WHERE p.id = :id AND p.is_active = TRUE
        ");
        $this->db->bind(':id', $id);
        return $this->db->single();
    }

    public function findPersonaByCedula($cedula) {
        $this->db->query("SELECT id FROM personas WHERE cedula = :cedula LIMIT 1");
        $this->db->bind(':cedula', $cedula);
        return $this->db->single();
    }

    public function createPersona($data, $userId) {
        $this->db->query("
            INSERT INTO personas (cedula, nombre, apellido, created_at, created_by)
            VALUES (:cedula, :nombre, :apellido, CURRENT_TIMESTAMP, :uid)
            RETURNING id
        ");
        $this->db->bind(':cedula',   $data['cedula']);
        $this->db->bind(':nombre',   $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':uid',      $userId);
        $row = $this->db->single();
        return $row ? (int)$row->id : null;
    }

    public function updatePersona($idPersona, $data, $userId) {
        $this->db->query("
            UPDATE personas SET
                cedula     = :cedula,
                nombre     = :nombre,
                apellido   = :apellido,
                updated_at = CURRENT_TIMESTAMP,
                updated_by = :uid
            WHERE id = :id
        ");
        $this->db->bind(':id',       $idPersona);
        $this->db->bind(':cedula',   $data['cedula']);
        $this->db->bind(':nombre',   $data['nombre']);
        $this->db->bind(':apellido', $data['apellido']);
        $this->db->bind(':uid',      $userId);
        return $this->db->execute();
    }

    public function create($idPersona, $data, $userId) {
        $this->db->query("
            INSERT INTO pasantes
                (id_persona, institucion, carrera, id_tutor_institucional,
                 fecha_inicio, fecha_fin, estado, created_by)
            VALUES
                (:id_persona, :institucion, :carrera, :id_tutor,
                 :fecha_inicio, :fecha_fin, :estado, :uid)
        ");
        $this->db->bind(':id_persona',   $idPersona);
        $this->db->bind(':institucion',  $data['institucion']);
        $this->db->bind(':carrera',      $data['carrera']);
        $this->db->bind(':id_tutor',     $data['id_tutor_institucional'] ?: null);
        $this->db->bind(':fecha_inicio', $data['fecha_inicio'] ?: null);
        $this->db->bind(':fecha_fin',    $data['fecha_fin'] ?: null);
        $this->db->bind(':estado',       $data['estado'] ?? 'Postulado');
        $this->db->bind(':uid',          $userId);
        $result = $this->db->execute();
        $this->audit('pasantes', 'INSERT', null, null, ['id_persona' => $idPersona, 'estado' => $data['estado'] ?? 'Postulado', 'institucion' => $data['institucion'], 'carrera' => $data['carrera']], $userId);
        return $result;
    }

    public function update($data, $userId) {
        $previos = $this->getById($data['id']);
        $this->db->query("
            UPDATE pasantes SET
                institucion            = :institucion,
                carrera                = :carrera,
                id_tutor_institucional = :id_tutor,
                fecha_inicio           = :fecha_inicio,
                fecha_fin              = :fecha_fin,
                estado                 = :estado,
                evaluacion             = :evaluacion,
                nota                   = :nota,
                updated_at             = CURRENT_TIMESTAMP,
                updated_by             = :uid
            WHERE id = :id
        ");
        $this->db->bind(':id',           $data['id']);
        $this->db->bind(':institucion',  $data['institucion']);
        $this->db->bind(':carrera',      $data['carrera']);
        $this->db->bind(':id_tutor',     $data['id_tutor_institucional'] ?: null);
        $this->db->bind(':fecha_inicio', $data['fecha_inicio'] ?: null);
        $this->db->bind(':fecha_fin',    $data['fecha_fin'] ?: null);
        $this->db->bind(':estado',       $data['estado']);
        $this->db->bind(':evaluacion',   $data['evaluacion'] ?: null);
        $this->db->bind(':nota',         isset($data['nota']) && $data['nota'] !== '' ? (float)$data['nota'] : null);
        $this->db->bind(':uid',          $userId);
        $result = $this->db->execute();
        $this->audit('pasantes', 'UPDATE', (int)$data['id'], $previos, ['estado' => $data['estado'], 'institucion' => $data['institucion'], 'carrera' => $data['carrera'], 'fecha_inicio' => $data['fecha_inicio'], 'fecha_fin' => $data['fecha_fin'], 'evaluacion' => $data['evaluacion'] ?? null, 'nota' => $data['nota'] ?? null], $userId);
        return $result;
    }

    public function softDelete($id, $userId) {
        $previos = $this->getById($id);
        $this->db->query("
            UPDATE pasantes
            SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid
            WHERE id = :id
        ");
        $this->db->bind(':uid', $userId);
        $this->db->bind(':id',  $id);
        $result = $this->db->execute();
        $this->audit('pasantes', 'DELETE', (int)$id, $previos, null, $userId);
        return $result;
    }

    public function getDocumentos($id_pasante) {
        $this->db->query("SELECT * FROM pasante_documentos WHERE id_pasante = :id_pasante ORDER BY id ASC");
        $this->db->bind(':id_pasante', $id_pasante);
        return $this->db->resultSet();
    }

    public function saveDocumento($data) {
        $this->db->query("
            INSERT INTO pasante_documentos
                (id_pasante, tipo_documento, entregado, archivo_url, observaciones, created_by)
            VALUES
                (:id_pasante, :tipo_documento, :entregado, :archivo_url, :observaciones, :created_by)
        ");
        $this->db->bind(':id_pasante',     $data['id_pasante']);
        $this->db->bind(':tipo_documento', $data['tipo_documento']);
        $this->db->bind(':entregado',      $data['entregado'] ? 'true' : 'false');
        $this->db->bind(':archivo_url',    $data['archivo_url']);
        $this->db->bind(':observaciones',  $data['observaciones']);
        $this->db->bind(':created_by',     $_SESSION['user_id'] ?? null);
        return $this->db->execute();
    }
}
