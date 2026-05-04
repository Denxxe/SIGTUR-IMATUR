<?php
class Visita extends Model {

    public static function getRecientes() {
        $db = new Database();
        $db->query("
            SELECT vi.*,
                   vt.nombre  AS vis_nombre,  vt.apellido AS vis_apellido, vt.cedula AS vis_cedula,
                   p.nombre   AS emp_nombre,  p.apellido  AS emp_apellido
            FROM visitas vi
            LEFT JOIN visitantes vt ON vi.id_visitante = vt.id
            LEFT JOIN empleados  e  ON vi.id_empleado  = e.id
            LEFT JOIN personas   p  ON e.id_persona    = p.id
            WHERE vi.is_active = TRUE
            ORDER BY vi.hora_entrada DESC
            LIMIT 100
        ");
        return $db->resultSet();
    }

    /**
     * Toggle marcaje: si hay visita abierta del mismo visitante marca salida; si no, registra entrada.
     */
    public static function registrar($data, $userId) {
        $db = new Database();
        $db->query("
            SELECT id FROM visitas
            WHERE id_visitante = :id_visitante AND hora_salida IS NULL AND is_active = TRUE
            ORDER BY hora_entrada DESC LIMIT 1
        ");
        $db->bind(':id_visitante', $data['id_visitante']);
        $abierta = $db->single();

        if ($abierta) {
            $db->query("UPDATE visitas SET hora_salida = CURRENT_TIMESTAMP WHERE id = :id");
            $db->bind(':id', $abierta->id);
        } else {
            $db->query("
                INSERT INTO visitas (id_visitante, id_empleado, motivo, observaciones, created_by)
                VALUES (:id_visitante, :id_empleado, :motivo, :observaciones, :uid)
            ");
            $db->bind(':id_visitante', $data['id_visitante']);
            $db->bind(':id_empleado',  $data['id_empleado'] ?: null);
            $db->bind(':motivo',       $data['motivo'] ?: null);
            $db->bind(':observaciones', $data['observaciones'] ?: null);
            $db->bind(':uid',          $userId);
        }
        return $db->execute();
    }

    public static function delete($id) {
        $db = new Database();
        $db->query("UPDATE visitas SET is_active = FALSE WHERE id = :id");
        $db->bind(':id', $id);
        return $db->execute();
    }
}
