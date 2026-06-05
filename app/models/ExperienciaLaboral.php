<?php

/**
 * Experiencia laboral (trabajos anteriores) de una persona.
 * Bloque "Experiencia Laboral" de la Ficha Técnica (D-RH23).
 */
class ExperienciaLaboral extends Model
{
    public static function porPersona($idPersona)
    {
        $db = new Database();
        $db->query("SELECT * FROM experiencia_laboral
                    WHERE id_persona = :id AND is_active = TRUE
                    ORDER BY fecha_inicio ASC NULLS LAST, id ASC");
        $db->bind(':id', $idPersona);
        return $db->resultSet();
    }

    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT * FROM experiencia_laboral WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function save(array $data, $user_id = null)
    {
        $db = new Database();
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        if (empty($data['organismo'])) {
            throw new Exception("El organismo/empleador es obligatorio.");
        }

        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE experiencia_laboral
                        SET organismo=:org, cargo=:cargo, fecha_inicio=:ini, fecha_culminacion=:fin,
                            updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                        WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO experiencia_laboral (id_persona, organismo, cargo, fecha_inicio, fecha_culminacion, created_by)
                        VALUES (:id_persona, :org, :cargo, :ini, :fin, :user_id) RETURNING id");
            $db->bind(':id_persona', (int)$data['id_persona']);
        }
        $db->bind(':org', trim($data['organismo']));
        $db->bind(':cargo', !empty($data['cargo']) ? trim($data['cargo']) : null);
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

        self::auditStatic('experiencia_laboral', $id ? 'UPDATE' : 'INSERT', $newId, $previos, $data, $user_id);
        return $ok;
    }

    public static function delete($id, $user_id = null)
    {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE experiencia_laboral SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $ok = $db->execute();
        self::auditStatic('experiencia_laboral', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
