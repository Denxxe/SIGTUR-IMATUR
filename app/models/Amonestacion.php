<?php
/**
 * Amonestación de un empleado, registrada manualmente por RRHH (D-RH28).
 * 3 amonestaciones activas = causa de despido (empleado Contratado).
 */
class Amonestacion extends Model
{
    const LIMITE_DESPIDO = 3;

    public static function porEmpleado($idEmpleado) {
        $db = new Database();
        $db->query("SELECT * FROM amonestaciones WHERE id_empleado = :id AND is_active = TRUE ORDER BY fecha DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM amonestaciones WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function save(array $data, $user_id = null) {
        $db = new Database();
        $id = !empty($data['id']) ? (int)$data['id'] : null;
        if (empty($data['id_empleado'])) throw new Exception("Empleado obligatorio.");
        if (empty($data['motivo'])) throw new Exception("El motivo de la amonestación es obligatorio.");
        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE amonestaciones SET fecha=:fecha, motivo=:motivo, updated_at=CURRENT_TIMESTAMP, updated_by=:uid WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO amonestaciones (id_empleado, fecha, motivo, created_by) VALUES (:emp, :fecha, :motivo, :uid) RETURNING id");
            $db->bind(':emp', (int)$data['id_empleado']);
        }
        $db->bind(':fecha', !empty($data['fecha']) ? $data['fecha'] : date('Y-m-d'));
        $db->bind(':motivo', trim($data['motivo']));
        $db->bind(':uid', $user_id);
        if ($id) { $ok = $db->execute(); $newId = $id; }
        else { $res = $db->single(); $ok = (bool)$res; $newId = $res->id ?? null; }
        self::auditStatic('amonestaciones', $id ? 'UPDATE' : 'INSERT', $newId, $previos, $data, $user_id);
        return $ok;
    }

    public static function delete($id, $user_id = null, $motivoAnulacion = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE amonestaciones SET is_active=FALSE, motivo_anulacion=:ma,
                        deleted_at=CURRENT_TIMESTAMP, deleted_by=:uid WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':ma', !empty($motivoAnulacion) ? trim($motivoAnulacion) : null);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('amonestaciones', 'DELETE', $id, $previos, ['motivo_anulacion' => $motivoAnulacion], $user_id);
        return $ok;
    }

    /**
     * Roster: por cada empleado activo, conteo de faltas y amonestaciones activas,
     * tipo de contrato y estado (OK / En riesgo / Causa de despido).
     */
    public static function roster() {
        $db = new Database();
        $db->query("SELECT e.id, p.nombre, p.apellido, e.tipo_contrato,
                           d.nombre AS departamento,
                           COALESCE(f.total, 0) AS faltas,
                           COALESCE(a.total, 0) AS amonestaciones
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    LEFT JOIN departamentos d ON e.id_departamento = d.id
                    LEFT JOIN (SELECT id_empleado, COUNT(*) total FROM faltas WHERE is_active = TRUE GROUP BY id_empleado) f
                           ON f.id_empleado = e.id
                    LEFT JOIN (SELECT id_empleado, COUNT(*) total FROM amonestaciones WHERE is_active = TRUE GROUP BY id_empleado) a
                           ON a.id_empleado = e.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                    ORDER BY amonestaciones DESC, faltas DESC, p.nombre ASC");
        return $db->resultSet();
    }
}
