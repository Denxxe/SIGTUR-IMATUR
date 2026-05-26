<?php
class Visitante extends Model {

    public static function all() {
        $db = new Database();
        $db->query("SELECT vt.*,
                           COALESCE(p.cedula,    vt.cedula)    AS cedula,
                           COALESCE(p.nombre,    vt.nombre)    AS nombre,
                           COALESCE(p.apellido,  vt.apellido)  AS apellido,
                           COALESCE(p.correo,    vt.correo)    AS correo,
                           COALESCE(p.telefono,  vt.telefono)  AS telefono,
                           COALESCE(p.genero,    vt.genero)    AS genero
                    FROM   visitantes vt
                    LEFT JOIN personas p ON vt.id_persona = p.id
                    WHERE  vt.is_active = TRUE
                    ORDER  BY COALESCE(p.nombre, vt.nombre) ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM visitantes WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Search by cédula: looks in personas linked to visitantes.
     * Returns object with id_visitante, id_persona, and all personal fields.
     */
    public static function buscarPorCedula(string $cedula) {
        $db = new Database();
        $db->query("
            SELECT vt.id           AS id_visitante,
                   vt.procedencia,
                   vt.motivo_frecuente,
                   p.id            AS id_persona,
                   p.cedula,
                   p.nombre,
                   p.apellido,
                   p.telefono,
                   p.correo,
                   p.genero
            FROM   visitantes vt
            JOIN   personas   p  ON vt.id_persona = p.id
            WHERE  p.cedula = :cedula
              AND  vt.is_active = TRUE
              AND  p.is_active  = TRUE
            LIMIT  1
        ");
        $db->bind(':cedula', $cedula);
        return $db->single();
    }

    /**
     * Find-or-create a visitante (and its backing persona) for reception.
     * Returns the visitante.id.
     */
    public static function crear(array $data, $userId): int {
        $db = new Database();

        // 1. Find or create the persona
        $idPersona = null;
        if (!empty($data['cedula'])) {
            $db->query("SELECT id FROM personas WHERE cedula = :c AND is_active = TRUE LIMIT 1");
            $db->bind(':c', $data['cedula']);
            $row = $db->single();
            if ($row) $idPersona = (int)$row->id;
        }

        if (!$idPersona) {
            $db->query("
                INSERT INTO personas (cedula, nombre, apellido, telefono, genero, correo, created_by)
                VALUES (:cedula, :nombre, :apellido, :telefono, :genero, :correo, :uid)
                RETURNING id
            ");
            $db->bind(':cedula',   $data['cedula'] ?: null);
            $db->bind(':nombre',   $data['nombre']);
            $db->bind(':apellido', $data['apellido']);
            $db->bind(':telefono', $data['telefono'] ?: null);
            $db->bind(':genero',   $data['genero'] ?: null);
            $db->bind(':correo',   $data['correo'] ?: null);
            $db->bind(':uid',      $userId);
            $row = $db->single();
            if (!$row) throw new Exception('Error al registrar datos personales.');
            $idPersona = (int)$row->id;
            self::auditStatic('personas', 'INSERT', $idPersona, null, ['nombre' => $data['nombre']], $userId);
        }

        // 2. Find or create the visitante record for that persona
        $db->query("SELECT id FROM visitantes WHERE id_persona = :pid AND is_active = TRUE LIMIT 1");
        $db->bind(':pid', $idPersona);
        $existing = $db->single();
        if ($existing) return (int)$existing->id;

        $db->query("
            INSERT INTO visitantes (id_persona, procedencia, motivo_frecuente, created_by)
            VALUES (:pid, :proc, :motivo, :uid)
            RETURNING id
        ");
        $db->bind(':pid',    $idPersona);
        $db->bind(':proc',   $data['procedencia'] ?: null);
        $db->bind(':motivo', $data['motivo_frecuente'] ?: null);
        $db->bind(':uid',    $userId);
        $row = $db->single();
        if (!$row) throw new Exception('Error al crear ficha de visitante.');
        $idVisitante = (int)$row->id;
        self::auditStatic('visitantes', 'INSERT', $idVisitante, null, ['id_persona' => $idPersona], $userId);
        return $idVisitante;
    }

    public static function store($data, $userId) {
        $previos = !empty($data['id']) ? self::find($data['id']) : null;
        $op      = !empty($data['id']) ? 'UPDATE' : 'INSERT';
        $db = new Database();
        if (!empty($data['id'])) {
            $db->query("
                UPDATE visitantes SET
                    cedula = :cedula, nombre = :nombre, apellido = :apellido,
                    procedencia = :procedencia, telefono = :telefono,
                    genero = :genero, correo = :correo, motivo_frecuente = :motivo,
                    updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                WHERE id = :id
            ");
            $db->bind(':id', $data['id']);
            $db->bind(':uid', $userId);
        } else {
            $db->query("
                INSERT INTO visitantes (cedula, nombre, apellido, procedencia, telefono, genero, correo, motivo_frecuente, created_by)
                VALUES (:cedula, :nombre, :apellido, :procedencia, :telefono, :genero, :correo, :motivo, :uid)
            ");
            $db->bind(':uid', $userId);
        }
        $db->bind(':cedula',      $data['cedula']);
        $db->bind(':nombre',      $data['nombre']);
        $db->bind(':apellido',    $data['apellido']);
        $db->bind(':procedencia', $data['procedencia'] ?: null);
        $db->bind(':telefono',    $data['telefono'] ?: null);
        $db->bind(':genero',      $data['genero'] ?: null);
        $db->bind(':correo',      $data['correo'] ?: null);
        $db->bind(':motivo',      $data['motivo_frecuente'] ?: null);
        $result = $db->execute();
        self::auditStatic('visitantes', $op, $data['id'] ?? null, $previos, ['nombre' => $data['nombre']], $userId);
        return $result;
    }

    public static function delete($id, $userId) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE visitantes SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid WHERE id = :id");
        $db->bind(':uid', $userId);
        $db->bind(':id', $id);
        $result = $db->execute();
        self::auditStatic('visitantes', 'DELETE', $id, $previos, null, $userId);
        return $result;
    }
}
