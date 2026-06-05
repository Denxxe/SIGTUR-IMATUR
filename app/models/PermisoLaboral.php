<?php
/**
 * Permiso o reposo laboral de un empleado (R-8).
 * Reposo y Permiso son entidades diferenciadas por `categoria` (D-RH32).
 * Vacaciones queda pendiente (fórmula D-RH04/05/NEW05).
 */
class PermisoLaboral extends Model
{
    const CATEGORIAS = ['Reposo', 'Permiso'];
    // Tipos válidos por categoría (cascada en la UI)
    const TIPOS = [
        'Reposo'  => ['Reposo médico'],
        'Permiso' => ['Médico familiar', 'Diligencia', 'Duelo', 'Maternidad/Paternidad', 'Personal', 'Estudios', 'Otro'],
    ];
    const ESTADOS = ['Pendiente', 'Aprobado', 'Rechazado', 'Anulado'];

    /** Lista con datos del empleado + estatus derivado (En curso/Concluido). */
    public static function all(array $filtros = []) {
        $db = new Database();
        $sql = "SELECT pl.*, p.nombre, p.apellido, e.nro_expediente,
                       CASE WHEN pl.fecha_fin >= CURRENT_DATE THEN 'En curso' ELSE 'Concluido' END AS estatus_periodo
                FROM permisos_laborales pl
                INNER JOIN empleados e ON pl.id_empleado = e.id
                INNER JOIN personas p ON e.id_persona = p.id
                WHERE pl.is_active = TRUE";
        if (!empty($filtros['estado'])) $sql .= " AND pl.estado = :estado";
        if (!empty($filtros['categoria'])) $sql .= " AND pl.categoria = :categoria";
        $sql .= " ORDER BY pl.fecha_inicio DESC, pl.id DESC";
        $db->query($sql);
        if (!empty($filtros['estado'])) $db->bind(':estado', $filtros['estado']);
        if (!empty($filtros['categoria'])) $db->bind(':categoria', $filtros['categoria']);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM permisos_laborales WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public static function porEmpleado($idEmpleado) {
        $db = new Database();
        $db->query("SELECT * FROM permisos_laborales WHERE id_empleado = :id AND is_active = TRUE ORDER BY fecha_inicio DESC");
        $db->bind(':id', $idEmpleado);
        return $db->resultSet();
    }

    public static function save(array $data, $user_id = null) {
        $db = new Database();
        $id = !empty($data['id']) ? (int)$data['id'] : null;

        $categoria = in_array($data['categoria'] ?? '', self::CATEGORIAS, true) ? $data['categoria'] : null;
        $tipo      = $data['tipo_permiso'] ?? '';
        $tiposValidos = $categoria ? self::TIPOS[$categoria] : array_merge(...array_values(self::TIPOS));
        if (!$categoria || !in_array($tipo, $tiposValidos, true)) {
            throw new Exception("Categoría/tipo de permiso inválido.");
        }
        if (empty($data['id_empleado']) || empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
            throw new Exception("Empleado, fecha de inicio y fecha hasta son obligatorios.");
        }
        if ($data['fecha_fin'] < $data['fecha_inicio']) {
            throw new Exception("La fecha 'hasta' no puede ser anterior a la fecha de inicio.");
        }
        $dias = (int)((strtotime($data['fecha_fin']) - strtotime($data['fecha_inicio'])) / 86400) + 1;

        if ($id) {
            $previos = self::find($id);
            $db->query("UPDATE permisos_laborales SET categoria=:cat, tipo_permiso=:tipo,
                            fecha_inicio=:fi, fecha_fin=:ff, dias_solicitados=:dias, duracion=:dur,
                            motivo=:motivo, updated_at=CURRENT_TIMESTAMP, updated_by=:uid
                        WHERE id=:id");
            $db->bind(':id', $id);
        } else {
            $previos = null;
            $db->query("INSERT INTO permisos_laborales
                            (id_empleado, categoria, tipo_permiso, fecha_inicio, fecha_fin, dias_solicitados, duracion, motivo, estado, created_by)
                        VALUES (:emp, :cat, :tipo, :fi, :ff, :dias, :dur, :motivo, 'Pendiente', :uid) RETURNING id");
            $db->bind(':emp', (int)$data['id_empleado']);
        }
        $db->bind(':cat', $categoria);
        $db->bind(':tipo', $tipo);
        $db->bind(':fi', $data['fecha_inicio']);
        $db->bind(':ff', $data['fecha_fin']);
        $db->bind(':dias', $dias);
        $db->bind(':dur', !empty($data['duracion']) ? trim($data['duracion']) : null);
        $db->bind(':motivo', !empty($data['motivo']) ? trim($data['motivo']) : null);
        $db->bind(':uid', $user_id);

        if ($id) { $ok = $db->execute(); $newId = $id; }
        else { $res = $db->single(); $ok = (bool)$res; $newId = $res->id ?? null; }
        self::auditStatic('permisos_laborales', $id ? 'UPDATE' : 'INSERT', $newId, $previos, $data, $user_id);
        return $ok;
    }

    /** Cambia el estado de aprobación (Aprobado/Rechazado/Anulado). */
    public static function cambiarEstado($id, $estado, $user_id = null) {
        if (!in_array($estado, self::ESTADOS, true)) throw new Exception("Estado inválido.");
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE permisos_laborales
                    SET estado=:estado, fecha_aprobacion=CURRENT_TIMESTAMP, updated_at=CURRENT_TIMESTAMP, updated_by=:uid
                    WHERE id=:id");
        $db->bind(':estado', $estado);
        $db->bind(':uid', $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('permisos_laborales', 'UPDATE', $id, $previos, ['estado' => $estado], $user_id);
        return $ok;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE permisos_laborales SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:uid WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('permisos_laborales', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
