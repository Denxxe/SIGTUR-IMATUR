<?php
/**
 * ConteoInventario — conteo físico por cambio de gestión (mig. 065).
 *
 * Es el **dolor #2** declarado por el cliente (B-05): la auditoría de todos
 * los bienes cuando cambia el coordinador o la presidencia (B-48). No es
 * periódico y no arranca de cero: al abrirlo se **congela** lo que el
 * sistema cree tener de cada bien, y luego se registra lo hallado
 * físicamente. Así la comparación queda auditable aunque el bien se mueva
 * después del conteo.
 *
 * Lo que se verifica de cada bien es **estatus, lugar y condición** (B-50).
 */
class ConteoInventario extends Model {

    const MOTIVOS = ['Cambio de coordinación', 'Cambio de presidencia', 'Auditoría', 'Otro'];
    const ABIERTO = 'Abierto';
    const CERRADO = 'Cerrado';

    public static function all() {
        $db = new Database();
        $db->query("SELECT c.*,
                           TRIM(COALESCE(p.nombre,'') || ' ' || COALESCE(p.apellido,'')) AS responsable,
                           (SELECT COUNT(*) FROM inventario_conteo_detalle d WHERE d.id_conteo = c.id) AS total,
                           (SELECT COUNT(*) FROM inventario_conteo_detalle d WHERE d.id_conteo = c.id AND d.hallado IS NOT NULL) AS verificados,
                           (SELECT COUNT(*) FROM inventario_conteo_detalle d WHERE d.id_conteo = c.id AND d.hallado = FALSE) AS faltantes
                      FROM inventario_conteos c
                      LEFT JOIN empleados e ON c.id_responsable = e.id
                      LEFT JOIN personas  p ON e.id_persona = p.id
                     WHERE c.is_active = TRUE
                     ORDER BY c.fecha_inicio DESC, c.id DESC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT c.*,
                           TRIM(COALESCE(p.nombre,'') || ' ' || COALESCE(p.apellido,'')) AS responsable
                      FROM inventario_conteos c
                      LEFT JOIN empleados e ON c.id_responsable = e.id
                      LEFT JOIN personas  p ON e.id_persona = p.id
                     WHERE c.id = :id AND c.is_active = TRUE");
        $db->bind(':id', (int)$id);
        return $db->single();
    }

    /** El conteo abierto (solo puede haber uno), o null. */
    public static function abierto() {
        $db = new Database();
        $db->query("SELECT * FROM inventario_conteos
                     WHERE estado = :e AND is_active = TRUE ORDER BY id DESC LIMIT 1");
        $db->bind(':e', self::ABIERTO);
        return $db->single() ?: null;
    }

    /**
     * Abre un conteo y congela el estado actual de todos los bienes del
     * inventario activo. Transaccional: o se crea con su detalle completo
     * o no se crea nada.
     */
    public static function abrir(array $datos, $user_id = null): int {
        if (self::abierto()) {
            throw new Exception('Ya hay un conteo abierto. Ciérralo antes de iniciar otro.');
        }
        $motivo = in_array($datos['motivo'] ?? '', self::MOTIVOS, true) ? $datos['motivo'] : 'Otro';

        $db = new Database();
        try {
            $db->beginTransaction();

            $db->query("INSERT INTO inventario_conteos
                            (motivo, fecha_inicio, estado, id_responsable, observaciones, created_by)
                        VALUES (:m, :f, :e, :r, :o, :u) RETURNING id");
            $db->bind(':m', $motivo);
            $db->bind(':f', ($datos['fecha_inicio'] ?? '') ?: date('Y-m-d'));
            $db->bind(':e', self::ABIERTO);
            $db->bind(':r', (int)($datos['id_responsable'] ?? 0) ?: null);
            $db->bind(':o', trim((string)($datos['observaciones'] ?? '')) ?: null);
            $db->bind(':u', $user_id);
            $idConteo = (int)($db->single()->id ?? 0);

            // Congela el estado que el sistema cree tener de cada bien activo.
            $db->query("INSERT INTO inventario_conteo_detalle
                            (id_conteo, id_inventario, esperado_ubicacion, esperado_estatus, esperado_condicion)
                        SELECT :c, i.id, i.id_ubicacion, i.estatus, i.condicion
                          FROM inventario i
                         WHERE i.is_active = TRUE AND i.estatus <> :baja");
            $db->bind(':c',    $idConteo);
            $db->bind(':baja', Inventario::EST_BAJA);
            $db->execute();

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('inventario_conteos', 'INSERT', $idConteo, null,
            ['motivo' => $motivo, 'estado' => self::ABIERTO], $user_id);
        return $idConteo;
    }

    /** Detalle del conteo con el bien y la comparación esperado/hallado. */
    public static function detalle(int $idConteo, string $filtro = '') {
        $where = '';
        if ($filtro === 'pendientes')  $where = ' AND d.hallado IS NULL';
        if ($filtro === 'faltantes')   $where = ' AND d.hallado = FALSE';
        if ($filtro === 'diferencias') $where = ' AND (d.hallado = FALSE
                                                       OR (d.hallado_ubicacion IS NOT NULL AND d.hallado_ubicacion <> d.esperado_ubicacion)
                                                       OR (d.hallado_condicion IS NOT NULL AND d.hallado_condicion <> d.esperado_condicion))';
        $db = new Database();
        $db->query("SELECT d.*, i.nombre AS bien, i.codigo_bn,
                           ue.nombre AS ubic_esperada, uh.nombre AS ubic_hallada
                      FROM inventario_conteo_detalle d
                      INNER JOIN inventario  i  ON d.id_inventario = i.id
                      LEFT  JOIN ubicaciones ue ON d.esperado_ubicacion = ue.id
                      LEFT  JOIN ubicaciones uh ON d.hallado_ubicacion  = uh.id
                     WHERE d.id_conteo = :c" . $where . "
                     ORDER BY i.nombre");
        $db->bind(':c', $idConteo);
        return $db->resultSet();
    }

    /** Registra lo hallado físicamente de un bien. */
    public static function verificar(int $idConteo, int $idBien, array $datos, $user_id = null): bool {
        $conteo = self::find($idConteo);
        if (!$conteo || $conteo->estado !== self::ABIERTO) {
            throw new Exception('El conteo no está abierto.');
        }
        $hallado = !empty($datos['hallado']);
        $cond = in_array($datos['hallado_condicion'] ?? '', Inventario::CONDICIONES, true)
            ? $datos['hallado_condicion'] : null;

        $db = new Database();
        $db->query("UPDATE inventario_conteo_detalle
                       SET hallado = :h,
                           hallado_ubicacion = :u,
                           hallado_condicion = :c,
                           observaciones = :o,
                           verificado_at = CURRENT_TIMESTAMP,
                           verificado_by = :by
                     WHERE id_conteo = :ct AND id_inventario = :b");
        $db->bind(':h',  $hallado ? 't' : 'f');
        $db->bind(':u',  $hallado ? ((int)($datos['hallado_ubicacion'] ?? 0) ?: null) : null);
        $db->bind(':c',  $hallado ? $cond : null);
        $db->bind(':o',  trim((string)($datos['observaciones'] ?? '')) ?: null);
        $db->bind(':by', $user_id);
        $db->bind(':ct', $idConteo);
        $db->bind(':b',  $idBien);
        return $db->execute();
    }

    /**
     * Cierra el conteo. NO modifica los bienes automáticamente: las
     * diferencias las resuelve una persona con los movimientos normales,
     * que es lo que deja rastro auditable. El conteo es el acta, no una
     * herramienta de corrección masiva.
     */
    public static function cerrar(int $idConteo, $user_id = null): bool {
        $conteo = self::find($idConteo);
        if (!$conteo) throw new Exception('El conteo no existe.');
        if ($conteo->estado === self::CERRADO) throw new Exception('El conteo ya está cerrado.');

        $db = new Database();
        $db->query("SELECT COUNT(*) AS n FROM inventario_conteo_detalle
                     WHERE id_conteo = :c AND hallado IS NULL");
        $db->bind(':c', $idConteo);
        if ((int)($db->single()->n ?? 0) > 0) {
            throw new Exception('Aún quedan bienes sin verificar. Marca todos antes de cerrar el conteo.');
        }

        $db->query("UPDATE inventario_conteos
                       SET estado = :e, fecha_cierre = CURRENT_DATE,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':e',  self::CERRADO);
        $db->bind(':u',  $user_id);
        $db->bind(':id', $idConteo);
        $ok = $db->execute();
        self::auditStatic('inventario_conteos', 'UPDATE', $idConteo, $conteo,
            ['estado' => self::CERRADO], $user_id);
        return $ok;
    }

    /** Resumen para el acta: totales y diferencias. */
    public static function resumen(int $idConteo): array {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total,
                           COUNT(CASE WHEN hallado IS NULL  THEN 1 END) AS pendientes,
                           COUNT(CASE WHEN hallado = TRUE   THEN 1 END) AS hallados,
                           COUNT(CASE WHEN hallado = FALSE  THEN 1 END) AS faltantes,
                           COUNT(CASE WHEN hallado = TRUE AND hallado_ubicacion IS NOT NULL
                                       AND hallado_ubicacion <> esperado_ubicacion THEN 1 END) AS movidos,
                           COUNT(CASE WHEN hallado = TRUE AND hallado_condicion IS NOT NULL
                                       AND hallado_condicion <> esperado_condicion THEN 1 END) AS cambio_condicion
                      FROM inventario_conteo_detalle WHERE id_conteo = :c");
        $db->bind(':c', $idConteo);
        $r = $db->single();
        return [
            'total'            => (int)($r->total ?? 0),
            'pendientes'       => (int)($r->pendientes ?? 0),
            'hallados'         => (int)($r->hallados ?? 0),
            'faltantes'        => (int)($r->faltantes ?? 0),
            'movidos'          => (int)($r->movidos ?? 0),
            'cambio_condicion' => (int)($r->cambio_condicion ?? 0),
        ];
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE inventario_conteos SET is_active = FALSE,
                        deleted_at = CURRENT_TIMESTAMP, deleted_by = :u WHERE id = :id");
        $db->bind(':id', (int)$id);
        $db->bind(':u',  $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_conteos', 'DELETE', $id, $previos, null, $user_id);
        return $ok;
    }
}
