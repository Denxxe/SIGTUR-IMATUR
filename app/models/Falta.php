<?php
/**
 * Falta injustificada de un empleado. El sistema las cuenta y notifica;
 * RRHH decide registrar amonestaciones a partir de ellas (D-RH28).
 */
class Falta extends Model
{
    public static function porEmpleado($idEmpleado) {
        $db = new Database();
        $db->query("SELECT * FROM faltas WHERE id_empleado = :id AND is_active = TRUE ORDER BY fecha DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM faltas WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function save(array $data, $user_id = null) {
        $db = new Database();
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        if (empty($data['id_empleado'])) throw new Exception("Empleado obligatorio.");
        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE faltas SET fecha=:fecha, motivo=:motivo, updated_at=CURRENT_TIMESTAMP, updated_by=:uid WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO faltas (id_empleado, fecha, motivo, created_by) VALUES (:emp, :fecha, :motivo, :uid) RETURNING id");
            $db->bind(':emp', (int)$data['id_empleado']);
        }
        $db->bind(':fecha', !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d'));
        $db->bind(':motivo', !empty($data['motivo']) ? trim($data['motivo']) : null);
        $db->bind(':uid', $user_id);
        if ($id) { $ok = $db->execute(); $newId = $id; }
        else { $res = $db->single(); $ok = (bool)$res; $newId = $res->id ?? null; }
        self::auditStatic('faltas', $id ? 'UPDATE' : 'INSERT', $newId, $previos, $data, $user_id);
        return $ok;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE faltas SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:uid WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('faltas', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
