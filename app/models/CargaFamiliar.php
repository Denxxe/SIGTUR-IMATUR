<?php

/**
 * Carga familiar de una persona (hijos/padres/cónyuge). Bloque de la Ficha Técnica.
 */
class CargaFamiliar extends Model
{
    const PARENTESCOS = ['Padre', 'Madre', 'Cónyuge', 'Concubino', 'Hijo'];

    /** Familiares activos de una persona */
    public static function porPersona($idPersona)
    {
        $db = new Database();
        $db->query("SELECT * FROM carga_familiar
                    WHERE id_persona = :id AND is_active = TRUE
                    ORDER BY id ASC");
        $db->bind(':id', $idPersona);
        return $db->resultSet();
    }

    public static function find($id)
    {
        $db = new Database();
        $db->query("SELECT * FROM carga_familiar WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /** Inserta o actualiza un familiar. Devuelve true/false. */
    public static function save(array $data, $user_id = null)
    {
        $db = new Database();
        $id          = !empty($data['id']) ? (int)$data['id'] : null;
        $parentesco  = in_array($data['parentesco'] ?? '', self::PARENTESCOS, true) ? $data['parentesco'] : null;
        if ($parentesco === null || empty($data['nombre_apellido'])) {
            throw new Exception("Nombre y parentesco del familiar son obligatorios.");
        }

        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE carga_familiar
                        SET nombre_apellido=:na, cedula=:cedula, fecha_nacimiento=:fnac, parentesco=:par,
                            updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                        WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO carga_familiar (id_persona, nombre_apellido, cedula, fecha_nacimiento, parentesco, created_by)
                        VALUES (:id_persona, :na, :cedula, :fnac, :par, :user_id) RETURNING id");
            $db->bind(':id_persona', (int)$data['id_persona']);
        }
        $db->bind(':na', trim($data['nombre_apellido']));
        $db->bind(':cedula', !empty($data['cedula']) ? trim($data['cedula']) : null);
        $db->bind(':fnac', !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null);
        $db->bind(':par', $parentesco);
        $db->bind(':user_id', $user_id);

        if ($id) {
            $ok = $db->execute();
            $newId = $id;
        } else {
            $res = $db->single();
            $ok = (bool)$res;
            $newId = $res->id ?? null;
        }

        self::auditStatic('carga_familiar', $id ? 'UPDATE' : 'INSERT', $newId, $previos, $data, $user_id);
        return $ok;
    }

    public static function delete($id, $user_id = null)
    {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE carga_familiar SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $ok = $db->execute();
        self::auditStatic('carga_familiar', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
