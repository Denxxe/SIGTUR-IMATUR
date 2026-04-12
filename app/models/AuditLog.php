<?php
/**
 * Clase AuditLog: Modelo de solo lectura para visualizar las bitácoras del sistema.
 */
class AuditLog extends Model {
    
    public static function all() {
        $db = new Database();
        $db->query("SELECT a.*, u.username 
                    FROM audit_logs a
                    LEFT JOIN usuarios u ON a.id_usuario = u.id
                    ORDER BY a.fecha DESC LIMIT 500");
        return $db->resultSet();
    }

    public static function byTabla($tabla) {
        $db = new Database();
        $db->query("SELECT a.*, u.username 
                    FROM audit_logs a
                    LEFT JOIN usuarios u ON a.id_usuario = u.id
                    WHERE a.tabla_afectada = :tabla
                    ORDER BY a.fecha DESC LIMIT 500");
        $db->bind(':tabla', $tabla);
        return $db->resultSet();
    }

    // Insertar un log (Debe ser consumido internamente por los modelos en el `save()`)
    public static function log($tabla, $operacion, $record_id, $datos_previos, $datos_nuevos, $id_usuario) {
        $db = new Database();
        $db->query("INSERT INTO audit_logs (tabla_afectada, operacion, record_id, datos_previos, datos_nuevos, id_usuario, ip_direccion) 
                    VALUES (:tabla, :operacion, :record_id, :previos, :nuevos, :id_usuario, :ip)");
        $db->bind(':tabla', $tabla);
        $db->bind(':operacion', $operacion);
        $db->bind(':record_id', $record_id);
        $db->bind(':previos', $datos_previos ? json_encode($datos_previos) : null);
        $db->bind(':nuevos', $datos_nuevos ? json_encode($datos_nuevos) : null);
        $db->bind(':id_usuario', $id_usuario);
        $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');
        return $db->execute();
    }
}
