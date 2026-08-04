<?php
/**
 * CentroAlertas — fuente única de las alertas accionables del sistema.
 * Reutilizada por el reporte `reportes/alertas` y por la campana del header.
 * Cada alerta: ['clave','titulo','desc','n','icono','url','sev','ids']
 * (sev: info|warning|danger; ids = PKs de los registros que la componen,
 * usados para el fingerprint de "visto" por usuario — ver resumenPersonal()).
 */
class CentroAlertas extends Model {

    /** Segundos que se cachea el resumen en sesión (la campana corre en cada página). */
    const CACHE_TTL = 120;

    /**
     * Igual que resumen(), pero memoiza el resultado en sesión por CACHE_TTL
     * segundos. Lo usa `reportes/alertas` (vista completa, sin filtrar por
     * usuario). Evita repetir las consultas en cada navegación.
     */
    public static function resumenCacheado(int $rol): array {
        $c = $_SESSION['_alertas_cache'] ?? null;
        if (is_array($c) && ($c['rol'] ?? null) === $rol && (time() - (int)($c['t'] ?? 0)) < self::CACHE_TTL) {
            return $c['data'];
        }
        $data = self::resumen($rol);
        $_SESSION['_alertas_cache'] = ['t' => time(), 'rol' => $rol, 'data' => $data];
        return $data;
    }

    /**
     * Como resumen(), pero oculta (n=0) las alertas que el usuario ya vio
     * con el MISMO conjunto de registros (fingerprint sin cambios). La usa
     * la campana del header. Cachea aparte de resumenCacheado() para no
     * mezclar la vista personalizada con la del reporte completo.
     */
    public static function resumenPersonal(int $rol, int $usuarioId): array {
        $c = $_SESSION['_alertas_cache_personal'] ?? null;
        if (is_array($c) && ($c['rol'] ?? null) === $rol && ($c['uid'] ?? null) === $usuarioId
            && (time() - (int)($c['t'] ?? 0)) < self::CACHE_TTL) {
            return $c['data'];
        }
        $alertas = self::resumen($rol);
        $vistas  = self::vistasUsuario($usuarioId);
        foreach ($alertas as &$a) {
            $fp = self::fingerprint($a['ids']);
            if ($fp !== '' && ($vistas[$a['clave']] ?? null) === $fp) {
                $a['n'] = 0; // mismo conjunto ya visto: se oculta para este usuario
            }
        }
        unset($a);
        $_SESSION['_alertas_cache_personal'] = ['t' => time(), 'rol' => $rol, 'uid' => $usuarioId, 'data' => $alertas];
        return $alertas;
    }

    /**
     * Marca como vistas, para este usuario, todas las alertas actualmente
     * visibles (n>0 tras resumenPersonal) con su fingerprint vigente. Se
     * invoca al abrir el dropdown de la campana.
     */
    public static function marcarVisiblesVistas(int $rol, int $usuarioId): void {
        $alertas = self::resumenPersonal($rol, $usuarioId);
        $db = new Database();
        foreach ($alertas as $a) {
            if ((int)$a['n'] <= 0 || empty($a['ids'])) continue;
            $db->query("INSERT INTO alertas_vistas (id_usuario, clave_alerta, fingerprint, visto_at)
                        VALUES (:uid, :clave, :fp, CURRENT_TIMESTAMP)
                        ON CONFLICT (id_usuario, clave_alerta)
                        DO UPDATE SET fingerprint = EXCLUDED.fingerprint, visto_at = CURRENT_TIMESTAMP");
            $db->bind(':uid', $usuarioId);
            $db->bind(':clave', $a['clave']);
            $db->bind(':fp', self::fingerprint($a['ids']));
            $db->execute();
        }
        unset($_SESSION['_alertas_cache_personal']);
    }

    /** Invalida el cache de alertas (p. ej. tras una acción que cambia los conteos). */
    public static function invalidarCache(): void {
        unset($_SESSION['_alertas_cache']);
        unset($_SESSION['_alertas_cache_personal']);
    }

    /** Huella del conjunto de IDs (orden-independiente); vacío si no hay registros. */
    private static function fingerprint(array $ids): string {
        if (empty($ids)) return '';
        $ids = array_map('intval', $ids);
        sort($ids);
        return md5(implode(',', $ids));
    }

    /** [clave_alerta => fingerprint] de lo ya visto por el usuario. */
    private static function vistasUsuario(int $usuarioId): array {
        $db = new Database();
        $db->query("SELECT clave_alerta, fingerprint FROM alertas_vistas WHERE id_usuario = :uid");
        $db->bind(':uid', $usuarioId);
        $out = [];
        foreach ($db->resultSet() as $r) $out[$r->clave_alerta] = $r->fingerprint;
        return $out;
    }

    /** Lista de alertas aplicables al rol (incluye las de conteo 0). */
    public static function resumen(int $rol): array {
        $db = new Database();
        $esRRHH = in_array($rol, [1, 2], true);
        $esForm = in_array($rol, [1, 3], true);
        $esInv  = in_array($rol, [1, 4], true);

        // Umbrales de preaviso (config, con fallback) — compartidos con el Dashboard.
        $diasContrato = 30; $diasPasante = 15;
        try {
            $db->query("SELECT clave, valor FROM configuracion_sistema WHERE clave IN ('dias_preaviso_contrato','dias_preaviso_pasante')");
            foreach ($db->resultSet() as $row) {
                if ($row->clave === 'dias_preaviso_contrato' && (int)$row->valor > 0) $diasContrato = (int)$row->valor;
                if ($row->clave === 'dias_preaviso_pasante'  && (int)$row->valor > 0) $diasPasante  = (int)$row->valor;
            }
        } catch (\Exception $ignored) {}

        $ids = function (string $sql, array $binds = []) use ($db): array {
            $db->query($sql);
            foreach ($binds as $k => $v) $db->bind($k, $v);
            return array_map(fn($r) => (int)$r->id, $db->resultSet());
        };

        $alertas = [];

        if ($esRRHH) {
            $idsContratos = $ids("SELECT id FROM empleados
                        WHERE is_active = TRUE AND tipo_contrato = 'Contratado' AND fecha_egreso IS NULL
                          AND fecha_vencimiento_contrato IS NOT NULL
                          AND fecha_vencimiento_contrato BETWEEN CURRENT_DATE AND (CURRENT_DATE + ($diasContrato || ' days')::INTERVAL)");

            $idsPermPend = $ids("SELECT id FROM permisos_laborales WHERE estado = 'Pendiente' AND is_active = TRUE");

            $limite = Amonestacion::LIMITE_DESPIDO;
            $idsDespido = [];
            foreach (Amonestacion::roster() as $r) if ((int)$r->amonestaciones >= $limite) $idsDespido[] = (int)$r->id;

            $idsExpedInc = [];
            foreach (ExpedienteDocumento::faltantesObligatorios() as $idEmp => $f) if ((int)$f > 0) $idsExpedInc[] = (int)$idEmp;

            $idsEnCurso = $ids("SELECT id FROM permisos_laborales
                        WHERE estado = 'Aprobado' AND is_active = TRUE
                          AND fecha_inicio <= CURRENT_DATE AND fecha_fin >= CURRENT_DATE");

            $alertas[] = ['clave' => 'contratos_por_vencer', 'titulo' => 'Contratos por vencer', 'desc' => "Contratados que vencen en los próximos {$diasContrato} días.", 'n' => count($idsContratos), 'icono' => 'bi-person-badge', 'url' => URL_ROOT . '/empleados/index', 'sev' => 'warning', 'ids' => $idsContratos];
            $alertas[] = ['clave' => 'permisos_pendientes',  'titulo' => 'Permisos / reposos pendientes', 'desc' => 'Solicitudes por aprobar o rechazar.', 'n' => count($idsPermPend), 'icono' => 'bi-calendar2-check', 'url' => URL_ROOT . '/permisos/index', 'sev' => 'warning', 'ids' => $idsPermPend];
            $alertas[] = ['clave' => 'causa_despido',        'titulo' => 'En causa de despido', 'desc' => "Empleados con {$limite}+ amonestaciones activas.", 'n' => count($idsDespido), 'icono' => 'bi-flag-fill', 'url' => URL_ROOT . '/amonestaciones/index', 'sev' => 'danger', 'ids' => $idsDespido];
            $alertas[] = ['clave' => 'expedientes_incompletos', 'titulo' => 'Expedientes incompletos', 'desc' => 'Personal con recaudos obligatorios faltantes.', 'n' => count($idsExpedInc), 'icono' => 'bi-folder-x', 'url' => URL_ROOT . '/reportes/expedientesIncompletos', 'sev' => 'warning', 'ids' => $idsExpedInc];
            $alertas[] = ['clave' => 'permisos_en_curso',    'titulo' => 'Permisos / reposos en curso', 'desc' => 'Ausencias justificadas vigentes hoy.', 'n' => count($idsEnCurso), 'icono' => 'bi-info-circle', 'url' => URL_ROOT . '/permisos/index', 'sev' => 'info', 'ids' => $idsEnCurso];
        }

        if ($esForm) {
            $idsTallVenc = $ids("SELECT id FROM talleres
                        WHERE is_active = TRUE AND (
                            (estado = 'Programado' AND fecha_inicio < CURRENT_DATE)
                            OR (estado = 'En Curso' AND fecha_fin IS NOT NULL AND fecha_fin < CURRENT_DATE)
                        )");
            $idsPasCulm = $ids("SELECT id FROM pasantes
                        WHERE is_active = TRUE AND estado = 'En Curso' AND fecha_fin IS NOT NULL
                          AND fecha_fin BETWEEN CURRENT_DATE AND (CURRENT_DATE + ($diasPasante || ' days')::INTERVAL)");

            $alertas[] = ['clave' => 'talleres_vencidos', 'titulo' => 'Talleres / actividades vencidas', 'desc' => 'Programadas cuya fecha ya pasó (sin ejecutarse) o en curso sin finalizar.', 'n' => count($idsTallVenc), 'icono' => 'bi-calendar-x', 'url' => URL_ROOT . '/talleres/index', 'sev' => 'danger', 'ids' => $idsTallVenc];
            $alertas[] = ['clave' => 'pasantes_por_culminar', 'titulo' => 'Pasantes por culminar', 'desc' => "Pasantías que culminan en los próximos {$diasPasante} días.", 'n' => count($idsPasCulm), 'icono' => 'bi-journal-text', 'url' => URL_ROOT . '/pasantes/index', 'sev' => 'info', 'ids' => $idsPasCulm];
        }

        if ($esInv) {
            $idsBienes = $ids("SELECT id FROM inventario
                              WHERE is_active = TRUE AND estatus <> 'Dado de baja'
                                AND (condicion = 'Dañado' OR estatus = 'En mantenimiento')");
            $alertas[] = ['clave' => 'bienes_en_alerta', 'titulo' => 'Bienes en alerta', 'desc' => 'Patrimonio dañado o en reparación.', 'n' => count($idsBienes), 'icono' => 'bi-box-seam', 'url' => URL_ROOT . '/inventario/index', 'sev' => 'warning', 'ids' => $idsBienes];
        }

        return $alertas;
    }

    /** Conteo total accionable (severidad warning/danger con n>0) para el badge de la campana. */
    public static function totalAccionable(int $rol): int {
        $t = 0;
        foreach (self::resumen($rol) as $a) {
            if ((int)$a['n'] > 0 && in_array($a['sev'], ['warning', 'danger'], true)) $t += (int)$a['n'];
        }
        return $t;
    }
}
