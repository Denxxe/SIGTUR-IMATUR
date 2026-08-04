<?php
/**
 * InventarioDocumento — respaldos documentales de un bien (mig. 064).
 *
 * B-16 a B-19: cada bien acumula factura, informe de la Alcaldía, oficio
 * de donación y las actas de su ciclo de vida. Mismo patrón ya probado en
 * RRHH con `expediente_documentos`: el binario vive FUERA del web root
 * (`storage/uploads/bienes/`) y se sirve por id de registro con control de
 * rol vía `DescargaController::bien()`.
 */
class InventarioDocumento extends Model {

    /** Catálogo de tipos: clave => [etiqueta, ícono]. */
    const TIPOS = [
        'factura'            => ['Factura o documento de adquisición', 'bi-receipt'],
        'informe_alcaldia'   => ['Informe de la Alcaldía',             'bi-file-earmark-check'],
        'oficio_donacion'    => ['Oficio de donación',                 'bi-gift'],
        'acta_asignacion'    => ['Acta de asignación a responsable',   'bi-person-check'],
        'acta_baja'          => ['Acta de baja / desincorporación',    'bi-archive'],
        'denuncia'           => ['Denuncia (robo o pérdida)',          'bi-shield-exclamation'],
        'garantia'           => ['Garantía',                           'bi-patch-check'],
        'otro'               => ['Otro documento',                     'bi-paperclip'],
    ];

    public static function labelTipo(string $clave): string {
        return self::TIPOS[$clave][0] ?? $clave;
    }

    public static function iconoTipo(string $clave): string {
        return self::TIPOS[$clave][1] ?? 'bi-paperclip';
    }

    public static function porBien(int $idBien) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_documentos
                     WHERE id_inventario = :id AND is_active = TRUE
                     ORDER BY created_at DESC, id DESC");
        $db->bind(':id', $idBien);
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_documentos WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', (int)$id);
        return $db->single();
    }

    public static function save(array $data, $user_id = null) {
        $db = new Database();
        $db->query("INSERT INTO inventario_documentos
                        (id_inventario, tipo_documento, archivo_url, nombre_original, observaciones, created_by)
                    VALUES (:bien, :tipo, :url, :nombre, :obs, :uid) RETURNING id");
        $db->bind(':bien',   (int)$data['id_inventario']);
        $db->bind(':tipo',   $data['tipo_documento']);
        $db->bind(':url',    $data['archivo_url']);
        $db->bind(':nombre', $data['nombre_original'] ?? null);
        $db->bind(':obs',    !empty($data['observaciones']) ? trim($data['observaciones']) : null);
        $db->bind(':uid',    $user_id);
        $res = $db->single();
        self::auditStatic('inventario_documentos', 'INSERT', $res->id ?? null, null, $data, $user_id);
        return (bool)$res;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE inventario_documentos
                       SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid
                     WHERE id = :id");
        $db->bind(':id',  (int)$id);
        $db->bind(':uid', $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_documentos', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
