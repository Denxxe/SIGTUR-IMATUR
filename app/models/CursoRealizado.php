<?php

/**
 * Cursos realizados por una persona. Bloque "Cursos Realizados" de la Ficha Técnica.
 */
class CursoRealizado extends Model
{
    public static function porPersona($idPersona)
    {
        $db = new Database();
        $db->query("SELECT * FROM cursos_realizados
                    WHERE id_persona = :id AND is_active = TRUE
                    ORDER BY fecha_inicio ASC NULLS LAST, id ASC");
        $db->bind(':id', $idPersona);
        return $db->resultSet();
    }

    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT * FROM cursos_realizados WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function save(array $data, $user_id = null)
    {
        $db = new Database();
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        if (empty($data['curso'])) {
            throw new Exception("El nombre del curso es obligatorio.");
        }

        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE cursos_realizados
                        SET institucion=:inst, curso=:curso, fecha_inicio=:ini, fecha_culminacion=:fin,
                            updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                        WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO cursos_realizados (id_persona, institucion, curso, fecha_inicio, fecha_culminacion, created_by)
                        VALUES (:id_persona, :inst, :curso, :ini, :fin, :user_id) RETURNING id");
            $db->bind(':id_persona', (int)$data['id_persona']);
        }
        $db->bind(':inst', !empty($data['institucion']) ? trim($data['institucion']) : null);
        $db->bind(':curso', trim($data['curso']));
        $db->bind(':ini', !empty($data['fecha_inicio']) ? $data['fecha_inicio'] : null);
        $db->bind(':fin', !empty($data['fecha_culminacion']) ? $data['fecha_culminacion'] : null);
        $db->bind(':user_id', $user_id);

        if ($id) {
            $ok = $db->execute();
            $newId = $id;
        } else {
            $res = $db->single();
            $ok = (bool)$res;
            $newId = $res->id ?? null;
        }

        self::auditStatic('cursos_realizados', $id ? 'UPDATE' : 'INSERT', $newId, $previos, $data, $user_id);
        return $ok;
    }

    public static function delete($id, $user_id = null)
    {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE cursos_realizados SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $ok = $db->execute();
        self::auditStatic('cursos_realizados', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
