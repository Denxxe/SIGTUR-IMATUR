<?php
/**
 * CentroAlertas — fuente única de las alertas accionables del sistema.
 * Reutilizada por el reporte `reportes/alertas` y por la campana del header.
 * Cada alerta: ['titulo','desc','n','icono','url','sev']  (sev: info|warning|danger)
 */
class CentroAlertas extends Model {

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

        $alertas = [];

        if ($esRRHH) {
            $db->query("SELECT COUNT(*) AS t FROM empleados
                        WHERE is_active = TRUE AND tipo_contrato = 'Contratado' AND fecha_egreso IS NULL
                          AND fecha_vencimiento_contrato IS NOT NULL
                          AND fecha_vencimiento_contrato BETWEEN CURRENT_DATE AND (CURRENT_DATE + ($diasContrato || ' days')::INTERVAL)");
            $contratosVencen = (int)($db->single()->t ?? 0);

            $db->query("SELECT COUNT(*) AS t FROM permisos_laborales WHERE estado = 'Pendiente' AND is_active = TRUE");
            $permPend = (int)($db->single()->t ?? 0);

            $limite = Amonestacion::LIMITE_DESPIDO; $despido = 0;
            foreach (Amonestacion::roster() as $r) if ((int)$r->amonestaciones >= $limite) $despido++;

            $expedInc = 0;
            foreach (ExpedienteDocumento::faltantesObligatorios() as $f) if ((int)$f > 0) $expedInc++;

            $db->query("SELECT COUNT(*) AS t FROM permisos_laborales
                        WHERE estado = 'Aprobado' AND is_active = TRUE
                          AND fecha_inicio <= CURRENT_DATE AND fecha_fin >= CURRENT_DATE");
            $enCurso = (int)($db->single()->t ?? 0);

            $alertas[] = ['titulo' => 'Contratos por vencer', 'desc' => "Contratados que vencen en los próximos {$diasContrato} días.", 'n' => $contratosVencen, 'icono' => 'bi-person-badge', 'url' => URL_ROOT . '/empleados/index', 'sev' => 'warning'];
            $alertas[] = ['titulo' => 'Permisos / reposos pendientes', 'desc' => 'Solicitudes por aprobar o rechazar.', 'n' => $permPend, 'icono' => 'bi-calendar2-check', 'url' => URL_ROOT . '/permisos/index', 'sev' => 'warning'];
            $alertas[] = ['titulo' => 'En causa de despido', 'desc' => "Empleados con {$limite}+ amonestaciones activas.", 'n' => $despido, 'icono' => 'bi-flag-fill', 'url' => URL_ROOT . '/amonestaciones/index', 'sev' => 'danger'];
            $alertas[] = ['titulo' => 'Expedientes incompletos', 'desc' => 'Personal con recaudos obligatorios faltantes.', 'n' => $expedInc, 'icono' => 'bi-folder-x', 'url' => URL_ROOT . '/reportes/expedientesIncompletos', 'sev' => 'warning'];
            $alertas[] = ['titulo' => 'Permisos / reposos en curso', 'desc' => 'Ausencias justificadas vigentes hoy.', 'n' => $enCurso, 'icono' => 'bi-info-circle', 'url' => URL_ROOT . '/permisos/index', 'sev' => 'info'];
        }

        if ($esForm) {
            $tallVenc = Taller::contarVencidos();
            $db->query("SELECT COUNT(*) AS t FROM pasantes
                        WHERE is_active = TRUE AND estado = 'En Curso' AND fecha_fin IS NOT NULL
                          AND fecha_fin BETWEEN CURRENT_DATE AND (CURRENT_DATE + ($diasPasante || ' days')::INTERVAL)");
            $pasCulm = (int)($db->single()->t ?? 0);

            $alertas[] = ['titulo' => 'Talleres / actividades vencidas', 'desc' => 'Programadas cuya fecha ya pasó (sin ejecutarse) o en curso sin finalizar.', 'n' => $tallVenc, 'icono' => 'bi-calendar-x', 'url' => URL_ROOT . '/talleres/index', 'sev' => 'danger'];
            $alertas[] = ['titulo' => 'Pasantes por culminar', 'desc' => "Pasantías que culminan en los próximos {$diasPasante} días.", 'n' => $pasCulm, 'icono' => 'bi-journal-text', 'url' => URL_ROOT . '/pasantes/index', 'sev' => 'info'];
        }

        if ($esInv) {
            $db->query("SELECT COUNT(*) AS t FROM inventario WHERE is_active = TRUE AND condicion IN ('Dañado','En Reparación')");
            $bienesAlerta = (int)($db->single()->t ?? 0);
            $alertas[] = ['titulo' => 'Bienes en alerta', 'desc' => 'Patrimonio dañado o en reparación.', 'n' => $bienesAlerta, 'icono' => 'bi-box-seam', 'url' => URL_ROOT . '/inventario/index', 'sev' => 'warning'];
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
