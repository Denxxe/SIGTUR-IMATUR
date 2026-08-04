<?php
/**
 * ConsolidadoBM1 — recepciones del Formulario BM-1 (mig. 064).
 *
 * El BM-1 es un documento **ENTRANTE**: la Alcaldía elabora el inventario
 * consolidado y se lo devuelve a IMATUR ya con los códigos asignados
 * (grupo-subgrupo-sección + N° de orden). El sistema NO lo genera.
 *
 * Cada recepción se registra aquí, se le adjunta el archivo escaneado, y
 * desde ella se codifican en lote los bienes que traía. Así queda la
 * trazabilidad de en qué BM-1 se codificó cada bien
 * (`inventario.id_consolidado_bm1`), que es lo que hace falta en la
 * auditoría por cambio de gestión (§4.5 del plan).
 */
class ConsolidadoBM1 extends Model {

    public static function all() {
        $db = new Database();
        $db->query("SELECT c.*,
                           (SELECT COUNT(*) FROM inventario i
                             WHERE i.id_consolidado_bm1 = c.id AND i.is_active = TRUE) AS bienes_codificados
                      FROM inventario_consolidados_bm1 c
                     WHERE c.is_active = TRUE
                     ORDER BY c.fecha_recepcion DESC, c.id DESC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_consolidados_bm1 WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$id);
        return $db->single();
    }

    /** Bienes que se codificaron con este BM-1. */
    public static function bienes(int $id) {
        $db = new Database();
        $db->query("SELECT i.id, i.nombre, i.codigo_bn, i.nro_orden, i.fecha_verificacion,
                           u.nombre AS ubicacion
                      FROM inventario i
                      LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                     WHERE i.id_consolidado_bm1 = :id AND i.is_active = TRUE
                     ORDER BY i.nro_orden");
        $db->bind(':id', (int)$id);
        return $db->resultSet();
    }

    public static function crear(array $data, $user_id = null): int {
        $db = new Database();
        $db->query("INSERT INTO inventario_consolidados_bm1
                        (fecha_recepcion, fecha_documento, referencia,
                         archivo_url, nombre_original, observaciones, created_by)
                    VALUES (:frec, :fdoc, :ref, :url, :nombre, :obs, :uid) RETURNING id");
        $db->bind(':frec',   $data['fecha_recepcion'] ?: date('Y-m-d'));
        $db->bind(':fdoc',   ($data['fecha_documento'] ?? '') ?: null);
        $db->bind(':ref',    ($data['referencia'] ?? '') ?: null);
        $db->bind(':url',    ($data['archivo_url'] ?? '') ?: null);
        $db->bind(':nombre', ($data['nombre_original'] ?? '') ?: null);
        $db->bind(':obs',    ($data['observaciones'] ?? '') ?: null);
        $db->bind(':uid',    $user_id);
        $id = (int)($db->single()->id ?? 0);
        self::auditStatic('inventario_consolidados_bm1', 'INSERT', $id, null, $data, $user_id);
        return $id;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE inventario_consolidados_bm1
                       SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid
                     WHERE id = :id");
        $db->bind(':id',  (int)$id);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_consolidados_bm1', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
