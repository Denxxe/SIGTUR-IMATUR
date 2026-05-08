<?php
/**
 * Clase RutaInventario: Modelo pivot para asignar bienes institucionales a rutas turísticas
 */
class RutaInventario extends Model {
    
    // Asignar un bien a una ruta
    public static function asignar(int $id_ruta, int $id_inventario, int $cantidad, string $observaciones = '', ?int $user_id = null) {
        $db = new Database();
        $db->query("INSERT INTO ruta_inventario (id_ruta, id_inventario, cantidad, observaciones, created_by) 
                    VALUES (:id_ruta, :id_inventario, :cantidad, :observaciones, :user_id)");
        $db->bind(':id_ruta', $id_ruta);
        $db->bind(':id_inventario', $id_inventario);
        $db->bind(':cantidad', $cantidad);
        $db->bind(':observaciones', $observaciones);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }

    // Remover un bien de una ruta
    public static function remover($id) {
        $db = new Database();
        $db->query("DELETE FROM ruta_inventario WHERE id = :id");
        $db->bind(':id', $id);
        return $db->execute();
    }

    // Obtener los bienes asignados a una ruta
    public static function getByRuta($id_ruta) {
        $db = new Database();
        $db->query("SELECT ri.*, i.nombre as item_nombre, i.codigo_bn, i.condicion 
                    FROM ruta_inventario ri
                    INNER JOIN inventario i ON ri.id_inventario = i.id
                    WHERE ri.id_ruta = :id_ruta
                    ORDER BY ri.created_at DESC");
        $db->bind(':id_ruta', $id_ruta);
        return $db->resultSet();
    }
}
