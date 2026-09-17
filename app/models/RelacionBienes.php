<?php
/**
 * RelacionBienes — oficio de relación de bienes nuevos a la Alcaldía.
 *
 * Formato entregado por la Directora de Bienes el 2026-09-15
 * (docs/formatos/oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg,
 * Oficio N° 179/2026): se dirige a la Coordinación de Bienes de la Alcaldía
 * con un lote de bienes y una tabla de tres columnas —cantidad, descripción y
 * monto en Bs—. Es el dolor #1 declarado por el cliente (B-05).
 *
 * Se modela como cabecera + vínculo desde cada bien (`inventario.id_relacion`),
 * el mismo patrón de `inventario_consolidados_bm1`, para poder responder dos
 * preguntas que el papel no responde: qué bienes ya se reportaron —y por tanto
 * no deben volver a ir— y cómo se veía exactamente el oficio que se envió.
 *
 * ⚠️ El formato recibido es el del procedimiento ANTERIOR al cambio que la
 * Alcaldía notificó el 2026-09-02 (ver PLAN_MODULO_BIENES.md §2-ter): pide que
 * la Alcaldía asigne los códigos, y su tabla no tiene columna de código. Se
 * construyó fiel a lo entregado. Si el cliente confirma el formato nuevo, el
 * cambio es agregar la columna en la vista imprimible: el modelo ya guarda el
 * código de cada bien.
 */
class RelacionBienes extends Model {

    /** Bienes que todavía no se han reportado en ningún oficio. */
    public static function candidatos() {
        $db = new Database();
        $db->query("SELECT i.id, i.nombre, i.marca, i.modelo, i.serial, i.descripcion,
                           i.costo_adquisicion, i.origen, i.estatus, i.fecha_adquisicion,
                           i.codigo_grupo, i.codigo_subgrupo, i.codigo_seccion, i.nro_orden,
                           c.nombre AS categoria, u.nombre AS ubicacion
                      FROM inventario i
                      INNER JOIN categorias  c ON i.id_categoria = c.id
                      INNER JOIN ubicaciones u ON i.id_ubicacion = u.id
                     WHERE i.is_active = TRUE
                       AND i.id_relacion IS NULL
                       AND i.estatus <> :baja
                     ORDER BY i.created_at ASC, i.id ASC");
        $db->bind(':baja', Inventario::EST_BAJA);
        return $db->resultSet();
    }

    /** Oficios emitidos, del más reciente al más viejo. */
    public static function all() {
        $db = new Database();
        $db->query("SELECT r.*,
                           (SELECT COUNT(*) FROM inventario i
                             WHERE i.id_relacion = r.id AND i.is_active = TRUE) AS total_bienes,
                           (SELECT COALESCE(SUM(i.costo_adquisicion), 0) FROM inventario i
                             WHERE i.id_relacion = r.id AND i.is_active = TRUE) AS monto_total
                      FROM inventario_relaciones r
                     WHERE r.is_active = TRUE
                     ORDER BY r.fecha DESC, r.id DESC");
        return $db->resultSet();
    }

    public static function find(int $id) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_relaciones WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    /** Renglones del oficio, en el orden en que se imprimen. */
    public static function items(int $id) {
        $db = new Database();
        $db->query("SELECT i.id, i.nombre, i.marca, i.modelo, i.serial, i.descripcion,
                           i.costo_adquisicion, i.origen,
                           i.codigo_grupo, i.codigo_subgrupo, i.codigo_seccion, i.nro_orden,
                           c.nombre AS categoria
                      FROM inventario i
                      INNER JOIN categorias c ON i.id_categoria = c.id
                     WHERE i.id_relacion = :id AND i.is_active = TRUE
                     ORDER BY i.id ASC");
        $db->bind(':id', $id);
        return $db->resultSet();
    }

    /**
     * Emite el oficio: toma el número del correlativo de bienes, crea la
     * cabecera y marca los bienes del lote. Todo en una transacción — un
     * oficio sin sus bienes (o al revés) no sirve para nada.
     *
     * Devuelve ['id' => int, 'numero' => string].
     */
    public static function emitir(array $idsBienes, array $datos, $user_id = null): array {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsBienes))));
        if (empty($ids)) {
            throw new Exception('Debe seleccionar al menos un bien para relacionar.');
        }
        $destinatario = trim($datos['destinatario_nombre'] ?? '');
        if ($destinatario === '') {
            throw new Exception('El nombre del destinatario es obligatorio.');
        }

        // Ningún bien puede ir en dos oficios: se verifica antes de numerar,
        // para no quemar un correlativo en un intento que va a fallar.
        $db = new Database();
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $db->query("SELECT COUNT(*) AS n FROM inventario
                     WHERE id IN ($marcadores) AND (id_relacion IS NOT NULL OR is_active = FALSE)");
        foreach ($ids as $i => $v) $db->bind($i + 1, $v);
        if ((int)($db->single()->n ?? 0) > 0) {
            throw new Exception('Alguno de los bienes seleccionados ya fue relacionado en otro oficio o fue eliminado. Actualiza la página e inténtalo de nuevo.');
        }

        $numero = ConfigSistema::generarNumeroOficio('bienes');

        $db->beginTransaction();
        try {
            $db->query("INSERT INTO inventario_relaciones
                            (numero, fecha, destinatario_nombre, destinatario_cargo,
                             destinatario_ente, observacion, created_by)
                        VALUES (:num, :fecha, :dn, :dc, :de, :obs, :uid)
                        RETURNING id");
            $db->bind(':num',   $numero);
            $db->bind(':fecha', $datos['fecha'] ?: date('Y-m-d'));
            $db->bind(':dn',    $destinatario);
            $db->bind(':dc',    $datos['destinatario_cargo'] ?: null);
            $db->bind(':de',    $datos['destinatario_ente'] ?: null);
            $db->bind(':obs',   $datos['observacion'] ?: null);
            $db->bind(':uid',   $user_id);
            $idRelacion = (int)$db->single()->id;

            $db->query("UPDATE inventario SET id_relacion = ?, updated_at = CURRENT_TIMESTAMP, updated_by = ?
                         WHERE id IN ($marcadores)");
            $db->bind(1, $idRelacion);
            $db->bind(2, $user_id);
            foreach ($ids as $i => $v) $db->bind($i + 3, $v);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('inventario_relaciones', 'INSERT', $idRelacion, null,
            ['numero' => $numero, 'bienes' => count($ids)], $user_id);

        return ['id' => $idRelacion, 'numero' => $numero];
    }

    /**
     * Anula un oficio emitido (se envió con un error, se rehízo, etc.).
     * Los bienes vuelven a quedar disponibles para un oficio nuevo; el
     * correlativo NO se recicla: un número emitido queda consumido, como en
     * cualquier libro de oficios.
     */
    public static function anular(int $id, string $motivo, $user_id = null): bool {
        $motivo = trim($motivo);
        if ($motivo === '') throw new Exception('Debe indicar el motivo de la anulación.');

        $previos = self::find($id);
        if (!$previos) throw new Exception('El oficio no existe.');
        if (!$previos->is_active) throw new Exception('El oficio ya está anulado.');

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("UPDATE inventario SET id_relacion = NULL, updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id_relacion = :id");
            $db->bind(':uid', $user_id);
            $db->bind(':id', $id);
            $db->execute();

            $db->query("UPDATE inventario_relaciones
                           SET is_active = FALSE, anulado_motivo = :m,
                               deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid
                         WHERE id = :id");
            $db->bind(':m', $motivo);
            $db->bind(':uid', $user_id);
            $db->bind(':id', $id);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('inventario_relaciones', 'DELETE', $id, $previos,
            ['anulado_motivo' => $motivo], $user_id);
        return true;
    }
}
