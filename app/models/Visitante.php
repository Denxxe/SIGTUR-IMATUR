<?php
class Visitante extends Model {

    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM visitantes WHERE is_active = TRUE ORDER BY nombre ASC, apellido ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM visitantes WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
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
        self::auditStatic('visitantes', $op, $data['id'] ?? null, $previos, ['cedula' => $data['cedula'], 'nombre' => $data['nombre'], 'apellido' => $data['apellido'], 'procedencia' => $data['procedencia'] ?: null], $userId);
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
