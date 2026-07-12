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

    /**
     * ¿Ya existe un familiar ACTIVO con esta cédula para ESTE MISMO empleado?
     * A propósito NO es una comprobación global: la misma cédula puede
     * repetirse legítimamente entre empleados distintos (hermanos que
     * declaran al mismo padre, cónyuges que trabajan ambos en la institución
     * y se declaran mutuamente). Solo se evita el doble registro accidental
     * del mismo familiar para la misma persona.
     */
    public static function existeCedulaEnPersona($idPersona, $cedula, $excluirId = null): bool
    {
        $cedula = preg_replace('/\D/', '', (string)$cedula);
        if ($cedula === '') return false;
        $db = new Database();
        $sql = "SELECT id FROM carga_familiar
                WHERE id_persona = :id_persona AND is_active = TRUE
                  AND regexp_replace(COALESCE(cedula, ''), '[^0-9]', '', 'g') = :cedula";
        if ($excluirId) $sql .= " AND id <> :id";
        $sql .= " LIMIT 1";
        $db->query($sql);
        $db->bind(':id_persona', (int)$idPersona);
        $db->bind(':cedula', $cedula);
        if ($excluirId) $db->bind(':id', (int)$excluirId);
        return (bool) $db->single();
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
        // Las cédulas se almacenan solo con dígitos (migración 037).
        $data['cedula'] = !empty($data['cedula']) ? preg_replace('/\D/', '', $data['cedula']) : null;
        if (!empty($data['cedula']) && self::existeCedulaEnPersona($data['id_persona'], $data['cedula'], $id)) {
            throw new Exception("Este empleado ya tiene registrado un familiar con la cédula {$data['cedula']}.");
        }
        $genero = in_array($data['genero'] ?? '', ['M', 'F'], true) ? $data['genero'] : null;
        // vive: TRUE por defecto; FALSE solo si se indica explícitamente fallecido
        $vive   = !(isset($data['vive']) && ($data['vive'] === '0' || $data['vive'] === 0 || $data['vive'] === false));

        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE carga_familiar
                        SET nombre_apellido=:na, cedula=:cedula, fecha_nacimiento=:fnac, parentesco=:par,
                            genero=:gen, vive=:vive, updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                        WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO carga_familiar (id_persona, nombre_apellido, cedula, fecha_nacimiento, parentesco, genero, vive, created_by)
                        VALUES (:id_persona, :na, :cedula, :fnac, :par, :gen, :vive, :user_id) RETURNING id");
            $db->bind(':id_persona', (int)$data['id_persona']);
        }
        $db->bind(':na', trim($data['nombre_apellido']));
        $db->bind(':cedula', !empty($data['cedula']) ? trim($data['cedula']) : null);
        $db->bind(':fnac', !empty($data['fecha_nacimiento']) ? $data['fecha_nacimiento'] : null);
        $db->bind(':par', $parentesco);
        $db->bind(':gen', $genero);
        $db->bind(':vive', $vive, PDO::PARAM_BOOL);
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
