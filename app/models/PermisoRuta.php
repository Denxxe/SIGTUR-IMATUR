<?php
/**
 * Oficio de permiso a una institución custodia — fase T-H (mig. 081).
 *
 * R-20: para visitar un museo, un castillo o una fundación, IMATUR envía un
 * oficio pidiendo el acceso. **Todas las salidas de la semana hacia esa
 * institución van en un solo oficio**, para agilizar el trámite, y se lleva
 * control de si llegó, si dieron el pase o si lo rechazaron. Lo tramita el
 * Director de Relaciones Inter-Institucionales.
 *
 * Por eso no es una columna de `ruta_ejecuciones`: la relación es N:M. Un
 * permiso cubre varias salidas, y una salida puede necesitar varios permisos
 * —una ruta que pasa por el Castillo y por la Basílica tiene dos custodios—.
 *
 * ⚠️ El **imprimible es provisional**: el formato del oficio no ha llegado. El
 * flujo y lo que se registra sí son los que describió el cliente.
 */
class PermisoRuta extends Model {

    const EST_ESPERA    = 'En espera';
    const EST_ACEPTADO  = 'Aceptado';
    const EST_RECHAZADO = 'Rechazado';
    const EST_ANULADO   = 'Anulado';

    const ESTADOS = [self::EST_ESPERA, self::EST_ACEPTADO, self::EST_RECHAZADO];

    /** Un permiso respondido ya no se vuelve a responder. */
    const ESTADOS_RESPONDIDOS = [self::EST_ACEPTADO, self::EST_RECHAZADO];

    const ESTADO_BADGES = [
        self::EST_ESPERA    => 'sig-badge--warning',
        self::EST_ACEPTADO  => 'sig-badge--success',
        self::EST_RECHAZADO => 'sig-badge--danger',
        self::EST_ANULADO   => 'sig-badge--neutral',
    ];

    private const SELECT_BASE = "
        SELECT p.*,
               TRIM(pe.nombre || ' ' || pe.apellido) AS responsable_nombre,
               c.nombre AS responsable_cargo,
               (SELECT COUNT(*) FROM ruta_permiso_salidas ps
                 WHERE ps.id_permiso = p.id AND ps.is_active = TRUE) AS total_salidas
          FROM ruta_permisos p
          LEFT JOIN empleados e  ON p.id_responsable = e.id
          LEFT JOIN personas  pe ON e.id_persona     = pe.id
          LEFT JOIN cargos    c  ON e.id_cargo       = c.id";

    // ── Lectura ──────────────────────────────────────────────────────────────

    public static function find(int $id): ?object {
        $db = new Database();
        $db->query(self::SELECT_BASE . " WHERE p.id = :id AND p.is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single() ?: null;
    }

    public static function all(array $f = []): array {
        $db    = new Database();
        $where = "p.is_active = TRUE";
        $binds = [];
        if (!empty($f['estado']) && in_array($f['estado'], array_merge(self::ESTADOS, [self::EST_ANULADO]), true)) {
            $where .= " AND p.estado = :est"; $binds[':est'] = $f['estado'];
        }
        if (!empty($f['q'])) {
            $where .= " AND (p.institucion ILIKE :q OR p.numero ILIKE :q OR p.destinatario_nombre ILIKE :q)";
            $binds[':q'] = '%' . $f['q'] . '%';
        }
        if (!empty($f['desde'])) { $where .= " AND p.semana_hasta >= :d"; $binds[':d'] = $f['desde']; }
        if (!empty($f['hasta'])) { $where .= " AND p.semana_desde <= :h"; $binds[':h'] = $f['hasta']; }

        $db->query(self::SELECT_BASE . " WHERE {$where} ORDER BY p.semana_desde DESC, p.id DESC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    /** Las salidas que cubre un permiso. */
    public static function salidas(int $idPermiso): array {
        $db = new Database();
        $db->query("SELECT ej.id, ej.fecha, ej.hora, ej.estado, ej.institucion_nombre, ej.origen,
                           r.nombre AS ruta_nombre
                      FROM ruta_permiso_salidas ps
                      INNER JOIN ruta_ejecuciones ej ON ps.id_ejecucion = ej.id
                      INNER JOIN rutas r             ON ej.id_ruta = r.id
                     WHERE ps.id_permiso = :id AND ps.is_active = TRUE
                     ORDER BY ej.fecha ASC, ej.hora ASC NULLS LAST");
        $db->bind(':id', $idPermiso);
        return $db->resultSet();
    }

    /**
     * Los permisos que cubren una salida — para verlo desde la salida misma.
     * Aquí importa saber si **falta** alguno antes de que llegue el día.
     */
    public static function porEjecucion(int $idEjecucion): array {
        $db = new Database();
        $db->query("SELECT p.id, p.numero, p.institucion, p.estado, p.fecha_respuesta
                      FROM ruta_permiso_salidas ps
                      INNER JOIN ruta_permisos p ON ps.id_permiso = p.id
                     WHERE ps.id_ejecucion = :id AND ps.is_active = TRUE AND p.is_active = TRUE
                     ORDER BY p.institucion ASC");
        $db->bind(':id', $idEjecucion);
        return $db->resultSet();
    }

    /**
     * Salidas programadas dentro de un rango — las candidatas a ir en el oficio.
     * Solo `Programado`: pedir permiso para algo que ya ocurrió no tiene sentido.
     */
    public static function salidasDeLaSemana(string $desde, string $hasta): array {
        $db = new Database();
        $db->query("SELECT ej.id, ej.fecha, ej.hora, ej.institucion_nombre, ej.origen,
                           r.nombre AS ruta_nombre, r.id AS id_ruta
                      FROM ruta_ejecuciones ej
                      INNER JOIN rutas r ON ej.id_ruta = r.id
                     WHERE ej.is_active = TRUE AND ej.estado = :est
                       AND ej.fecha BETWEEN :d AND :h
                     ORDER BY ej.fecha ASC, ej.hora ASC NULLS LAST");
        $db->bind(':est', RutaEjecucion::EST_PROGRAMADO);
        $db->bind(':d', $desde);
        $db->bind(':h', $hasta);
        return $db->resultSet();
    }

    /**
     * A qué instituciones hay que pedirles permiso esa semana: los entes
     * custodios de los puntos de las rutas que se van a recorrer (R-06).
     *
     * Es lo que evita que alguien tenga que acordarse de memoria — que era,
     * según R-06, uno de los tres dolores principales del módulo.
     */
    public static function custodiosDeLaSemana(string $desde, string $hasta): array {
        $db = new Database();
        $db->query("SELECT pr.ente_custodio AS institucion,
                           COUNT(DISTINCT ej.id) AS salidas,
                           STRING_AGG(DISTINCT r.nombre, ', ') AS rutas,
                           (SELECT COUNT(*) FROM ruta_permisos p
                             WHERE p.is_active = TRUE AND p.estado <> :anulado
                               AND p.institucion = pr.ente_custodio
                               AND p.semana_desde <= :h2 AND p.semana_hasta >= :d2) AS permisos_emitidos
                      FROM ruta_ejecuciones ej
                      INNER JOIN rutas r      ON ej.id_ruta = r.id
                      INNER JOIN puntos_ruta pr ON pr.id_ruta = r.id AND pr.is_active = TRUE
                     WHERE ej.is_active = TRUE AND ej.estado = :est
                       AND ej.fecha BETWEEN :d AND :h
                       AND pr.ente_custodio IS NOT NULL AND TRIM(pr.ente_custodio) <> ''
                     GROUP BY pr.ente_custodio
                     ORDER BY pr.ente_custodio ASC");
        $db->bind(':est',     RutaEjecucion::EST_PROGRAMADO);
        $db->bind(':anulado', self::EST_ANULADO);
        $db->bind(':d',  $desde);  $db->bind(':h',  $hasta);
        $db->bind(':d2', $desde);  $db->bind(':h2', $hasta);
        return $db->resultSet();
    }

    public static function resumenPorEstado(): array {
        $db = new Database();
        $db->query("SELECT estado, COUNT(*) AS n FROM ruta_permisos
                     WHERE is_active = TRUE GROUP BY estado");
        $out = [self::EST_ESPERA => 0, self::EST_ACEPTADO => 0, self::EST_RECHAZADO => 0];
        foreach ($db->resultSet() as $r) $out[$r->estado] = (int)$r->n;
        return $out;
    }

    // ── Emisión ──────────────────────────────────────────────────────────────

    /**
     * Emite el oficio. Transaccional, y **valida todo antes de pedir el
     * correlativo**: un intento fallido no puede quemar un número (mismo
     * criterio que `RelacionBienes::emitir()`).
     */
    public static function emitir(array $d, array $idsSalidas, $user_id = null): array {
        $institucion = trim($d['institucion'] ?? '');
        if ($institucion === '') throw new Exception('Indique la institución custodia a la que se pide el permiso.');

        $desde = trim($d['semana_desde'] ?? '');
        $hasta = trim($d['semana_hasta'] ?? '');
        if ($desde === '' || $hasta === '') throw new Exception('Indique la semana que cubre el permiso.');
        if ($hasta < $desde) throw new Exception('La fecha final de la semana no puede ser anterior a la inicial.');

        $ids = array_values(array_unique(array_filter(array_map('intval', $idsSalidas))));
        if (empty($ids)) throw new Exception('Seleccione al menos una salida que cubra el permiso.');

        $db = new Database();

        // Todas las salidas deben existir, estar activas y caer dentro de la semana:
        // un permiso que cubre una fecha que no menciona no sirve de respaldo.
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $db->query("SELECT COUNT(*) AS n FROM ruta_ejecuciones
                     WHERE id IN ($marcadores) AND is_active = TRUE
                       AND fecha BETWEEN ? AND ?");
        foreach ($ids as $i => $v) $db->bind($i + 1, $v);
        $db->bind(count($ids) + 1, $desde);
        $db->bind(count($ids) + 2, $hasta);
        if ((int)($db->single()->n ?? 0) !== count($ids)) {
            throw new Exception('Alguna de las salidas seleccionadas ya no existe o quedó fuera de la semana indicada. Actualice la página.');
        }

        // Prefijo propio para que no se confunda con el oficio saliente al punto
        // (`RUTA-`) ni con los de Bienes: son trámites distintos.
        $numero = 'PERM-' . ConfigSistema::generarNumeroOficio('permiso');

        $db->beginTransaction();
        try {
            $db->query("INSERT INTO ruta_permisos
                            (numero, fecha, institucion, destinatario_nombre, destinatario_cargo,
                             semana_desde, semana_hasta, estado, observaciones, id_responsable, created_by)
                        VALUES (:num, :fecha, :inst, :dn, :dc, :sd, :sh, :est, :obs, :resp, :uid)
                        RETURNING id");
            $db->bind(':num',   $numero);
            $db->bind(':fecha', ($d['fecha'] ?? '') ?: date('Y-m-d'));
            $db->bind(':inst',  $institucion);
            $db->bind(':dn',    trim($d['destinatario_nombre'] ?? '') ?: null);
            $db->bind(':dc',    trim($d['destinatario_cargo']  ?? '') ?: null);
            $db->bind(':sd',    $desde);
            $db->bind(':sh',    $hasta);
            $db->bind(':est',   self::EST_ESPERA);
            $db->bind(':obs',   trim($d['observaciones'] ?? '') ?: null);
            $db->bind(':resp',  (int)($d['id_responsable'] ?? 0) ?: null);
            $db->bind(':uid',   $user_id);
            $idPermiso = (int)$db->single()->id;

            foreach ($ids as $idEj) {
                $db->query("INSERT INTO ruta_permiso_salidas (id_permiso, id_ejecucion, created_by)
                            VALUES (:p, :e, :u)");
                $db->bind(':p', $idPermiso);
                $db->bind(':e', $idEj);
                $db->bind(':u', $user_id);
                $db->execute();
            }

            $db->endTransaction();
        } catch (Exception $e) {
            $db->cancelTransaction();
            throw $e;
        }

        self::auditStatic('ruta_permisos', 'INSERT', $idPermiso, null,
            ['numero' => $numero, 'institucion' => $institucion, 'salidas' => count($ids)], $user_id);

        return ['id' => $idPermiso, 'numero' => $numero];
    }

    // ── Respuesta de la institución ──────────────────────────────────────────

    /**
     * Registra lo que contestó la institución (R-20: «si se dio el pase, si se
     * rechazó»). Un rechazo exige decir por qué: es lo que explica que una
     * salida programada no pueda hacer una de sus paradas.
     */
    public static function responder(int $id, string $estado, ?string $fecha, ?string $obs, $user_id = null): bool {
        $p = self::find($id);
        if (!$p) throw new Exception('El permiso no existe.');
        if (!in_array($estado, self::ESTADOS_RESPONDIDOS, true)) {
            throw new Exception('La respuesta debe ser «Aceptado» o «Rechazado».');
        }
        if ($p->estado === self::EST_ANULADO) throw new Exception('Este permiso está anulado.');
        if (in_array($p->estado, self::ESTADOS_RESPONDIDOS, true)) {
            throw new Exception('Este permiso ya fue respondido como «' . $p->estado . '».');
        }
        if ($estado === self::EST_RECHAZADO && trim((string)$obs) === '') {
            throw new Exception('Indique el motivo del rechazo.');
        }
        $fecha = trim((string)$fecha) ?: date('Y-m-d');
        if ($fecha < $p->fecha) throw new Exception('La respuesta no puede ser anterior a la emisión del oficio.');

        $db = new Database();
        $db->query("UPDATE ruta_permisos
                       SET estado = :est, fecha_respuesta = :fr, observaciones = :obs,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':est', $estado);
        $db->bind(':fr',  $fecha);
        $db->bind(':obs', trim((string)$obs) ?: $p->observaciones);
        $db->bind(':u',   $user_id);
        $db->bind(':id',  $id);
        $ok = $db->execute();
        self::auditStatic('ruta_permisos', 'UPDATE', $id, $p,
            ['estado' => $estado, 'fecha_respuesta' => $fecha], $user_id);
        return $ok;
    }

    /** El pase devuelto por la institución, escaneado. */
    public static function guardarRespuestaArchivo(int $id, string $archivo, ?string $original, $user_id = null): bool {
        if (!self::find($id)) throw new Exception('El permiso no existe.');
        $db = new Database();
        $db->query("UPDATE ruta_permisos
                       SET respuesta_archivo = :a, respuesta_original = :o,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':a', $archivo);
        $db->bind(':o', $original);
        $db->bind(':u', $user_id);
        $db->bind(':id', $id);
        $ok = $db->execute();
        self::auditStatic('ruta_permisos', 'UPDATE', $id, null, ['respuesta_original' => $original], $user_id);
        return $ok;
    }

    // ── Anulación ────────────────────────────────────────────────────────────

    /**
     * Anular ≠ borrar. El oficio ya salió de la institución, así que el registro
     * se conserva con su motivo. **El número no se recicla**: mismo criterio que
     * en Bienes — un correlativo repetido sería peor que uno perdido.
     */
    public static function anular(int $id, string $motivo, $user_id = null): bool {
        $p = self::find($id);
        if (!$p) throw new Exception('El permiso no existe.');
        if ($p->estado === self::EST_ANULADO) throw new Exception('Este permiso ya está anulado.');
        if (trim($motivo) === '') throw new Exception('Indique por qué se anula el permiso.');

        $db = new Database();
        $db->query("UPDATE ruta_permisos
                       SET estado = :est, motivo_anulacion = :mot,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':est', self::EST_ANULADO);
        $db->bind(':mot', trim($motivo));
        $db->bind(':u',   $user_id);
        $db->bind(':id',  $id);
        $ok = $db->execute();
        self::auditStatic('ruta_permisos', 'UPDATE', $id, $p,
            ['estado' => self::EST_ANULADO, 'motivo' => $motivo], $user_id);
        return $ok;
    }

    /** El responsable por defecto: quien dirige Relaciones Inter-Institucionales (R-20). */
    public static function responsableSugerido(): ?object {
        $db = new Database();
        $db->query("SELECT e.id, TRIM(p.nombre || ' ' || p.apellido) AS nombre
                      FROM empleados e
                      INNER JOIN personas     p ON e.id_persona     = p.id
                      INNER JOIN departamentos d ON e.id_departamento = d.id
                     WHERE e.is_active = TRUE AND e.fecha_egreso IS NULL
                       AND d.nombre ILIKE '%inter%institucional%'
                     ORDER BY e.id ASC LIMIT 1");
        return $db->single() ?: null;
    }
}
