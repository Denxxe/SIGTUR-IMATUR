<?php
/**
 * ActaDesincorporacion — el acta por lote con la que la Alcaldía retira bienes (C-5).
 *
 * Procedimiento nuevo (PLAN_MODULO_BIENES.md §2-ter): el acta de baja pasa a
 * llamarse **Acta de Desincorporación**, se hace **por lote** y el sello de la
 * Alcaldía sobre ella ES el aval del retiro — ya no hay oficio de retiro.
 *
 * El ciclo tiene dos momentos y por eso el acta tiene dos estados:
 *
 *   EMITIDA   se armó con un lote de bienes desincorporados. Se imprime y se
 *             lleva a la Alcaldía. Los bienes siguen «Por retirar»: salieron
 *             del inventario activo pero están físicamente en la sede (B-67).
 *   FIRMADA   volvió sellada. Al registrarla, TODOS sus bienes pasan a
 *             «Retirado» de una sola vez — que es justo lo que antes había que
 *             hacer bien por bien con `Inventario::marcarRetirado()`.
 *
 * Mismo patrón que `ConsolidadoBM1` y `RelacionBienes`: cabecera propia y
 * vínculo desde cada bien (`inventario.id_acta_desincorporacion`), para poder
 * responder qué bienes fueron en la misma acta y reimprimirla tal cual.
 *
 * ⚠️ El FORMATO oficial del acta no ha llegado (§3.4 del BACKLOG). Este modelo
 * no depende de él: cuando llegue, solo cambia la vista imprimible.
 */
class ActaDesincorporacion extends Model {

    /**
     * Bienes que pueden entrar en un acta: desincorporados, que la Alcaldía
     * todavía no retiró y que no están ya en otra acta.
     */
    public static function candidatos() {
        $db = new Database();
        $db->query("SELECT i.id, i.codigo_bn, i.nombre, i.marca, i.modelo, i.serial,
                           i.condicion, i.descripcion,
                           c.nombre AS categoria, u.nombre AS ubicacion,
                           ai.fecha AS fecha_baja, ai.descripcion AS motivo_baja
                      FROM inventario i
                      INNER JOIN categorias  c ON i.id_categoria = c.id
                      INNER JOIN ubicaciones u ON i.id_ubicacion = u.id
                      LEFT JOIN LATERAL (
                          SELECT fecha, descripcion FROM actividad_inventario
                           WHERE id_inventario = i.id AND tipo_movimiento = 'Baja' AND is_active = TRUE
                           ORDER BY fecha DESC, id DESC LIMIT 1
                      ) ai ON TRUE
                     WHERE i.is_active = TRUE
                       AND i.estatus = :baja
                       AND i.retirado_alcaldia = FALSE
                       AND i.id_acta_desincorporacion IS NULL
                     ORDER BY ai.fecha ASC NULLS LAST, i.nombre ASC");
        $db->bind(':baja', Inventario::EST_BAJA);
        return $db->resultSet();
    }

    /** Actas emitidas, de la más reciente a la más antigua. */
    public static function all() {
        $db = new Database();
        $db->query("SELECT a.*,
                           (SELECT COUNT(*) FROM inventario i
                             WHERE i.id_acta_desincorporacion = a.id AND i.is_active = TRUE) AS total_bienes
                      FROM inventario_actas_desincorporacion a
                     WHERE a.is_active = TRUE
                     ORDER BY a.fecha DESC, a.id DESC");
        return $db->resultSet();
    }

    public static function find(int $id) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_actas_desincorporacion WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    /** Renglones del acta, en el orden en que se imprimen. */
    public static function items(int $id) {
        $db = new Database();
        $db->query("SELECT i.id, i.codigo_bn, i.nombre, i.marca, i.modelo, i.serial,
                           i.condicion, i.descripcion, i.costo_adquisicion,
                           i.retirado_alcaldia, i.fecha_retiro,
                           c.nombre AS categoria, u.nombre AS ubicacion,
                           ai.fecha AS fecha_baja, ai.descripcion AS motivo_baja
                      FROM inventario i
                      INNER JOIN categorias  c ON i.id_categoria = c.id
                      INNER JOIN ubicaciones u ON i.id_ubicacion = u.id
                      LEFT JOIN LATERAL (
                          SELECT fecha, descripcion FROM actividad_inventario
                           WHERE id_inventario = i.id AND tipo_movimiento = 'Baja' AND is_active = TRUE
                           ORDER BY fecha DESC, id DESC LIMIT 1
                      ) ai ON TRUE
                     WHERE i.id_acta_desincorporacion = :id AND i.is_active = TRUE
                     ORDER BY i.codigo_bn NULLS LAST, i.nombre ASC");
        $db->bind(':id', $id);
        return $db->resultSet();
    }

    /** ¿El acta ya volvió firmada y sellada por la Alcaldía? */
    public static function estaFirmada($acta): bool {
        return !empty($acta->fecha_firma);
    }

    /**
     * Emite el acta con el lote seleccionado. Transaccional: un acta sin sus
     * bienes (o unos bienes enganchados a un acta que no existe) no sirve.
     * Devuelve ['id' => int, 'numero' => string].
     */
    public static function emitir(array $idsBienes, array $datos, $user_id = null): array {
        $ids = array_values(array_unique(array_filter(array_map('intval', $idsBienes))));
        if (empty($ids)) {
            throw new Exception('Debe seleccionar al menos un bien para el acta.');
        }

        $db = new Database();
        $marcadores = implode(',', array_fill(0, count($ids), '?'));

        // Se valida ANTES de pedir número, para no quemar un correlativo en un
        // intento que va a fallar. Un bien solo entra si sigue desincorporado,
        // sin retirar y sin acta previa.
        $db->query("SELECT COUNT(*) AS n FROM inventario
                     WHERE id IN ($marcadores)
                       AND (is_active = FALSE OR estatus <> ? OR retirado_alcaldia = TRUE
                            OR id_acta_desincorporacion IS NOT NULL)");
        foreach ($ids as $i => $v) $db->bind($i + 1, $v);
        $db->bind(count($ids) + 1, Inventario::EST_BAJA);
        if ((int)($db->single()->n ?? 0) > 0) {
            throw new Exception('Alguno de los bienes seleccionados ya está en otra acta, ya fue retirado o dejó de estar desincorporado. Actualiza la página e inténtalo de nuevo.');
        }

        $numero = ConfigSistema::generarNumeroOficio('acta');

        $db->beginTransaction();
        try {
            $db->query("INSERT INTO inventario_actas_desincorporacion
                            (numero, fecha, motivo, observacion, created_by)
                        VALUES (:num, :fecha, :mot, :obs, :uid) RETURNING id");
            $db->bind(':num',   $numero);
            $db->bind(':fecha', ($datos['fecha'] ?? '') ?: date('Y-m-d'));
            $db->bind(':mot',   ($datos['motivo'] ?? '') ?: null);
            $db->bind(':obs',   ($datos['observacion'] ?? '') ?: null);
            $db->bind(':uid',   $user_id);
            $idActa = (int)$db->single()->id;

            $db->query("UPDATE inventario
                           SET id_acta_desincorporacion = ?, updated_at = CURRENT_TIMESTAMP, updated_by = ?
                         WHERE id IN ($marcadores)");
            $db->bind(1, $idActa);
            $db->bind(2, $user_id);
            foreach ($ids as $i => $v) $db->bind($i + 3, $v);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('inventario_actas_desincorporacion', 'INSERT', $idActa, null,
            ['numero' => $numero, 'bienes' => count($ids)], $user_id);

        return ['id' => $idActa, 'numero' => $numero];
    }

    /**
     * Registra el acta ya firmada y sellada por la Alcaldía: **ese sello es el
     * aval del retiro**, así que en el mismo acto TODOS los bienes del acta
     * pasan a «Retirado». Es la operación en bloque que reemplaza al
     * `marcarRetirado()` bien por bien.
     */
    public static function registrarFirmada(int $id, array $datos, $user_id = null): int {
        $acta = self::find($id);
        if (!$acta)              throw new Exception('El acta no existe.');
        if (!$acta->is_active)   throw new Exception('El acta está anulada.');
        if (self::estaFirmada($acta)) throw new Exception('Esta acta ya fue registrada como firmada.');

        $fecha = ($datos['fecha_firma'] ?? '') ?: date('Y-m-d');
        if ($fecha > date('Y-m-d'))   throw new Exception('La fecha de la firma no puede ser futura.');
        if ($fecha < $acta->fecha)    throw new Exception('La fecha de la firma no puede ser anterior a la del acta.');

        $bienes = self::items($id);
        if (empty($bienes)) throw new Exception('El acta no tiene bienes: no hay nada que retirar.');

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("UPDATE inventario_actas_desincorporacion
                           SET fecha_firma = :f, recibido_por = :r,
                               archivo_url = COALESCE(:arch, archivo_url),
                               nombre_original = COALESCE(:orig, nombre_original),
                               updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id = :id");
            $db->bind(':f',    $fecha);
            $db->bind(':r',    ($datos['recibido_por'] ?? '') ?: null);
            $db->bind(':arch', $datos['archivo_url']     ?? null);
            $db->bind(':orig', $datos['nombre_original'] ?? null);
            $db->bind(':uid',  $user_id);
            $db->bind(':id',   $id);
            $db->execute();

            // El retiro en bloque. Se acota a los que siguen sin retirar por si
            // alguno se confirmó por el camino con la acción individual.
            $db->query("UPDATE inventario
                           SET retirado_alcaldia = TRUE, fecha_retiro = :f,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id_acta_desincorporacion = :id
                           AND is_active = TRUE AND retirado_alcaldia = FALSE");
            $db->bind(':f',   $fecha);
            $db->bind(':uid', $user_id);
            $db->bind(':id',  $id);
            $db->execute();
            $retirados = $db->rowCount();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('inventario_actas_desincorporacion', 'UPDATE', $id, $acta,
            ['fecha_firma' => $fecha, 'bienes_retirados' => $retirados], $user_id);

        return $retirados;
    }

    /**
     * Anula el acta. Sus bienes vuelven a quedar disponibles para otra acta y,
     * si el acta ya estaba firmada, se les revierte el retiro — porque el aval
     * que lo respaldaba dejó de existir. El correlativo NO se recicla.
     */
    public static function anular(int $id, string $motivo, $user_id = null): bool {
        $motivo = trim($motivo);
        if ($motivo === '') throw new Exception('Debe indicar el motivo de la anulación.');

        $acta = self::find($id);
        if (!$acta)            throw new Exception('El acta no existe.');
        if (!$acta->is_active) throw new Exception('El acta ya está anulada.');

        $db = new Database();
        $db->beginTransaction();
        try {
            $db->query("UPDATE inventario
                           SET id_acta_desincorporacion = NULL,
                               retirado_alcaldia = FALSE, fecha_retiro = NULL,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :uid
                         WHERE id_acta_desincorporacion = :id");
            $db->bind(':uid', $user_id);
            $db->bind(':id',  $id);
            $db->execute();

            $db->query("UPDATE inventario_actas_desincorporacion
                           SET is_active = FALSE, anulado_motivo = :m,
                               deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid
                         WHERE id = :id");
            $db->bind(':m',   $motivo);
            $db->bind(':uid', $user_id);
            $db->bind(':id',  $id);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('inventario_actas_desincorporacion', 'DELETE', $id, $acta,
            ['anulado_motivo' => $motivo], $user_id);
        return true;
    }
}
