<?php

/**
 * Clase AuditLog: Modelo para visualizar y registrar las bitácoras del sistema.
 */
class AuditLog extends Model
{

    public static function all()
    {
        $db = new Database();
        $db->query("SELECT a.*, u.username 
                    FROM audit_logs a
                    LEFT JOIN usuarios u ON a.id_usuario = u.id
                    ORDER BY a.fecha DESC LIMIT 500");
        return $db->resultSet();
    }

    public static function byTabla($tabla)
    {
        $db = new Database();
        $db->query("SELECT a.*, u.username 
                    FROM audit_logs a
                    LEFT JOIN usuarios u ON a.id_usuario = u.id
                    WHERE a.tabla_afectada = :tabla
                    ORDER BY a.fecha DESC LIMIT 500");
        $db->bind(':tabla', $tabla);
        return $db->resultSet();
    }

    public static function getDeleted($tabla)
    {
        $db = new Database();
        $identificador = "id";
        if ($tabla == 'personas') $identificador = "cedula || ' - ' || nombre || ' ' || apellido";
        if ($tabla == 'inventario') $identificador = "codigo_bn || ' - ' || nombre";
        if (in_array($tabla, ['talleres', 'rutas', 'departamentos', 'cargos', 'categorias', 'ubicaciones', 'ubicaciones_formacion'])) $identificador = "nombre";
        // pasantes ya no tiene cedula/nombre propios (migración 003): se consulta a personas por id_persona
        if ($tabla == 'pasantes') $identificador = "(SELECT pp.cedula || ' - ' || pp.nombre || ' ' || pp.apellido FROM personas pp WHERE pp.id = id_persona)";
        if ($tabla == 'municipio') $identificador = "nombre || ' - ' || codigo_postal";
        if ($tabla == 'parroquia') $identificador = "nombre";

        $sql = "SELECT *, ($identificador) as display_name FROM $tabla WHERE is_active = FALSE ORDER BY deleted_at DESC";
        $db->query($sql);
        return $db->resultSet();
    }

    /**
     * Insertar un log de auditoría.
     * @param string $tabla Tabla afectada.
     * @param string $operacion Tipo de operación (INSERT, UPDATE, DELETE).
     * @param int $record_id ID del registro afectado.
     * @param array $datos_previos Datos antes del cambio.
     * @param array $datos_nuevos Datos después del cambio.
     * @param int $id_usuario Usuario que realiza la acción.
     * @param Database|null $dbInstance Instancia de DB opcional para usar dentro de una transacción.
     */
    public static function log(string $tabla, string $operacion, ?int $record_id, ?array $datos_previos, ?array $datos_nuevos, ?int $id_usuario, $dbInstance = null)
    {
        // Si no se provee instancia, creamos una nueva.
        $db = $dbInstance ? $dbInstance : new Database();

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
