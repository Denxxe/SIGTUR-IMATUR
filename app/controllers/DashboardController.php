<?php
/**
 * DashboardController — Panel principal role-aware
 * Carga únicamente los datos relevantes para el rol del usuario autenticado.
 */
class DashboardController extends Controller {

    public function index() {
        // Respaldo de transiciones automáticas (Programado→En Curso) al abrir el
        // sistema, por si la tarea programada no está activa. Idempotente.
        try { Taller::autoTransicionarProgramados(); } catch (\Throwable $ignored) {}

        $db   = new Database();
        $rol  = (int)($_SESSION['user_rol'] ?? 0);
        $anio = (int)date('Y');

        $data = [
            'titulo' => 'Panel Principal',
            'rol'    => $rol,
            'anio'   => $anio,
        ];

        try {
            // Helper: rellena períodos mensuales faltantes con cero (ventana completa)
            $padMonths = function(array $rows, int $n): array {
                $map = [];
                foreach ($rows as $r) $map[$r->mes ?? ''] = (int)($r->total ?? 0);
                $result = [];
                for ($i = $n - 1; $i >= 0; $i--) {
                    $key      = date('Y-m', strtotime("-$i months"));
                    $result[] = (object)['mes' => $key, 'total' => $map[$key] ?? 0];
                }
                return $result;
            };

            // Helper: rellena días faltantes con cero (ventana completa)
            $padDays = function(array $rows, int $n): array {
                $map = [];
                foreach ($rows as $r) $map[$r->dia ?? ''] = (int)($r->total ?? 0);
                $result = [];
                for ($i = $n - 1; $i >= 0; $i--) {
                    $key      = date('Y-m-d', strtotime("-$i days"));
                    $result[] = (object)['dia' => $key, 'total' => $map[$key] ?? 0];
                }
                return $result;
            };

            // Leer umbrales de alerta desde configuracion_sistema
            $diasContrato = 30;
            $diasPasante  = 15;
            try {
                $db->query("SELECT clave, valor FROM configuracion_sistema WHERE clave IN ('dias_preaviso_contrato','dias_preaviso_pasante')");
                foreach ($db->resultSet() as $row) {
                    if ($row->clave === 'dias_preaviso_contrato' && (int)$row->valor > 0) $diasContrato = (int)$row->valor;
                    if ($row->clave === 'dias_preaviso_pasante'  && (int)$row->valor > 0) $diasPasante  = (int)$row->valor;
                }
            } catch (\Exception $ignored) {}

            // Helper: calcula delta % entre período actual y anterior
            $mkDelta = function(int $curr, int $prev, string $label): ?array {
                if ($prev === 0) return null;
                $pct = round((($curr - $prev) / $prev) * 100, 1);
                if (abs($pct) < 0.5) return null;
                return [
                    'pct'   => abs($pct),
                    'arrow' => $pct > 0 ? '↑' : '↓',
                    'color' => $pct > 0 ? '#059669' : '#DC2626',
                    'label' => $label,
                ];
            };

            // ══════════════════════════════════════════════════════════════
            // PERSONAL — roles 1 (Admin) y 2 (RRHH)
            // ══════════════════════════════════════════════════════════════
            if (in_array($rol, [1, 2])) {
                $db->query("SELECT COUNT(*) AS total FROM empleados WHERE is_active = TRUE");
                $data['kpiEmpleados'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT COUNT(*) AS total FROM asistencias
                            WHERE is_active = TRUE
                              AND date_trunc('month', fecha) = date_trunc('month', CURRENT_DATE)");
                $data['kpiAsistenciaMes'] = (int)($db->single()->total ?? 0);

                // Delta asistencias: vs mes anterior
                $db->query("SELECT COUNT(*) AS total FROM asistencias
                            WHERE is_active = TRUE
                              AND date_trunc('month', fecha) = date_trunc('month', CURRENT_DATE - INTERVAL '1 month')");
                $prevAsist = (int)($db->single()->total ?? 0);
                $data['deltaAsistenciaMes'] = $mkDelta($data['kpiAsistenciaMes'], $prevAsist, 'vs mes anterior');

                // Contratos por vencer: usa el vencimiento del contrato (no la fecha de egreso real, que es R-12).
                $db->query("SELECT COUNT(*) AS total FROM empleados
                            WHERE is_active = TRUE AND tipo_contrato = 'Contratado'
                              AND fecha_egreso IS NULL
                              AND fecha_vencimiento_contrato IS NOT NULL
                              AND fecha_vencimiento_contrato BETWEEN CURRENT_DATE AND (CURRENT_DATE + ($diasContrato || ' days')::INTERVAL)");
                $data['kpiContratosVencen'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT TO_CHAR(a.fecha, 'YYYY-MM') AS mes, COUNT(*) AS total
                            FROM asistencias a
                            WHERE a.is_active = TRUE AND a.fecha >= (CURRENT_DATE - INTERVAL '4 months')
                            GROUP BY mes ORDER BY mes ASC");
                $data['asistenciaPorMes'] = $padMonths($db->resultSet(), 4);

                $db->query("SELECT d.nombre AS departamento, COUNT(e.id) AS total
                            FROM departamentos d
                            LEFT JOIN empleados e ON d.id = e.id_departamento AND e.is_active = TRUE
                            WHERE d.is_active = TRUE GROUP BY d.nombre ORDER BY total DESC LIMIT 8");
                $data['empPorDepto'] = $db->resultSet();

                // Permisos/reposos vigentes hoy (aprobados, en curso) + pendientes de aprobar
                $db->query("SELECT COUNT(*) AS total FROM permisos_laborales
                            WHERE is_active = TRUE AND estado = 'Aprobado'
                              AND CURRENT_DATE BETWEEN fecha_inicio AND fecha_fin");
                $data['kpiPermisosVigentes'] = (int)($db->single()->total ?? 0);
                $db->query("SELECT COUNT(*) AS total FROM permisos_laborales WHERE is_active = TRUE AND estado = 'Pendiente'");
                $data['kpiPermisosPendientes'] = (int)($db->single()->total ?? 0);

                // Amonestaciones: empleados en causa de despido (>= límite)
                $db->query("SELECT COUNT(*) AS total FROM (
                                SELECT id_empleado FROM amonestaciones WHERE is_active = TRUE
                                GROUP BY id_empleado HAVING COUNT(*) >= :lim) q");
                $db->bind(':lim', Amonestacion::LIMITE_DESPIDO);
                $data['kpiAmonDespido'] = (int)($db->single()->total ?? 0);

                // Impuntualidad del mes actual
                $db->query("SELECT COUNT(CASE WHEN minutos_tarde IS NOT NULL THEN 1 END) AS con,
                                   COUNT(CASE WHEN minutos_tarde > :tol THEN 1 END) AS imp
                            FROM asistencias
                            WHERE is_active = TRUE AND date_trunc('month', fecha) = date_trunc('month', CURRENT_DATE)");
                $db->bind(':tol', Asistencia::toleranciaPuntualidad());
                $pm = $db->single();
                $data['kpiImpuntualMes'] = (int)($pm->imp ?? 0);
                $data['kpiImpuntualPct'] = ((int)($pm->con ?? 0) > 0) ? (int)round((int)$pm->imp * 100 / (int)$pm->con) : 0;
            }

            // ══════════════════════════════════════════════════════════════
            // VISITAS — roles 1, 2 y 5 (Recepción)
            // ══════════════════════════════════════════════════════════════
            if (in_array($rol, [1, 2, 5])) {
                $db->query("SELECT COUNT(*) AS total FROM visitas
                            WHERE is_active = TRUE AND DATE(hora_entrada) = CURRENT_DATE");
                $data['kpiVisitasHoy'] = (int)($db->single()->total ?? 0);

                // Delta visitas hoy: vs ayer
                $db->query("SELECT COUNT(*) AS total FROM visitas
                            WHERE is_active = TRUE AND DATE(hora_entrada) = CURRENT_DATE - 1");
                $prevVisHoy = (int)($db->single()->total ?? 0);
                $data['deltaVisitasHoy'] = $mkDelta($data['kpiVisitasHoy'], $prevVisHoy, 'vs ayer');

                $db->query("SELECT COUNT(DISTINCT id_visitante) AS total FROM visitas
                            WHERE is_active = TRUE
                              AND DATE(hora_entrada) >= date_trunc('week', CURRENT_DATE)::date");
                $data['kpiVisitasSemana'] = (int)($db->single()->total ?? 0);

                // Delta visitantes semana: vs semana anterior
                $db->query("SELECT COUNT(DISTINCT id_visitante) AS total FROM visitas
                            WHERE is_active = TRUE
                              AND DATE(hora_entrada) >= (date_trunc('week', CURRENT_DATE) - INTERVAL '7 days')::date
                              AND DATE(hora_entrada) <  date_trunc('week', CURRENT_DATE)::date");
                $prevVisSem = (int)($db->single()->total ?? 0);
                $data['deltaVisitasSemana'] = $mkDelta($data['kpiVisitasSemana'], $prevVisSem, 'vs semana anterior');

                $db->query("SELECT COUNT(DISTINCT id_visitante) AS total FROM visitas
                            WHERE is_active = TRUE
                              AND date_trunc('month', hora_entrada) = date_trunc('month', NOW())");
                $data['kpiVisitantesMes'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT DATE(hora_entrada) AS dia, COUNT(*) AS total
                            FROM visitas
                            WHERE is_active = TRUE AND hora_entrada >= (CURRENT_DATE - INTERVAL '14 days')
                            GROUP BY dia ORDER BY dia ASC");
                $data['visitasPorDia'] = $padDays($db->resultSet(), 14);
            }

            // ══════════════════════════════════════════════════════════════
            // FORMACIÓN Y TURISMO — roles 1 y 3 (Turismo)
            // ══════════════════════════════════════════════════════════════
            if (in_array($rol, [1, 3])) {
                $db->query("SELECT COUNT(*) AS total FROM talleres
                            WHERE estado IN ('En Curso','Programado') AND is_active = TRUE");
                $data['kpiActividadesActivas'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT COUNT(*) AS total
                            FROM participantes_taller pt
                            JOIN talleres t ON pt.id_taller = t.id
                            WHERE EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                              AND pt.is_active = TRUE AND t.is_active = TRUE");
                $db->bind(':anio', $anio);
                $data['kpiFormadosAnio'] = (int)($db->single()->total ?? 0);

                // Delta formados: vs año anterior
                $db->query("SELECT COUNT(*) AS total
                            FROM participantes_taller pt
                            JOIN talleres t ON pt.id_taller = t.id
                            WHERE EXTRACT(YEAR FROM t.fecha_inicio) = :anio_prev
                              AND pt.is_active = TRUE AND t.is_active = TRUE");
                $db->bind(':anio_prev', $anio - 1);
                $prevFormados = (int)($db->single()->total ?? 0);
                $data['deltaFormados'] = $mkDelta($data['kpiFormadosAnio'], $prevFormados, 'vs ' . ($anio - 1));

                $db->query("SELECT COUNT(*) AS total FROM rutas WHERE estado = 'Activa' AND is_active = TRUE");
                $data['kpiRutas'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT COUNT(*) AS total FROM pasantes WHERE estado = 'En Curso' AND is_active = TRUE");
                $data['kpiPasantes'] = (int)($db->single()->total ?? 0);

                // Tasa de ocupación de actividades
                $db->query("SELECT
                                COALESCE(SUM(sub.inscritos), 0)           AS total_inscritos,
                                COALESCE(SUM(COALESCE(t.cupo_maximo,0)),0) AS total_cupos
                            FROM talleres t
                            LEFT JOIN (
                                SELECT id_taller, COUNT(*) AS inscritos
                                FROM participantes_taller WHERE is_active = TRUE GROUP BY id_taller
                            ) sub ON sub.id_taller = t.id
                            WHERE t.is_active = TRUE AND t.estado <> 'Cancelado'
                              AND t.cupo_maximo > 0
                              AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio");
                $db->bind(':anio', $anio);
                $oR = $db->single();
                $tc = (int)($oR->total_cupos ?? 0);
                $ti = (int)($oR->total_inscritos ?? 0);
                $data['tasaOcupacion'] = $tc > 0 ? round(($ti / $tc) * 100, 1) : 0;
                $data['ocupInscritos'] = $ti;
                $data['ocupCupos']     = $tc;

                // Tasa de finalización
                $db->query("SELECT COUNT(*) AS total,
                                   COUNT(CASE WHEN estado='Finalizado' THEN 1 END) AS finalizadas,
                                   COUNT(CASE WHEN estado='Cancelado'  THEN 1 END) AS canceladas
                            FROM talleres WHERE is_active = TRUE AND EXTRACT(YEAR FROM fecha_inicio) = :anio");
                $db->bind(':anio', $anio);
                $eR = $db->single();
                $totalActs = (int)($eR->total ?? 0);
                $data['tasaFinaliz']  = $totalActs > 0 ? round(((int)$eR->finalizadas / $totalActs) * 100, 1) : 0;
                $data['tasaCancel']   = $totalActs > 0 ? round(((int)$eR->canceladas  / $totalActs) * 100, 1) : 0;
                $data['totalActsAnio'] = $totalActs;

                // Tendencia actividades 6 meses
                $db->query("SELECT TO_CHAR(fecha_inicio, 'YYYY-MM') AS mes, COUNT(*) AS total
                            FROM talleres WHERE is_active = TRUE
                              AND fecha_inicio >= (CURRENT_DATE - INTERVAL '6 months')
                            GROUP BY mes ORDER BY mes ASC");
                $data['talleresPorMes'] = $padMonths($db->resultSet(), 6);
            }

            // ══════════════════════════════════════════════════════════════
            // INVENTARIO — roles 1 y 4 (Inventario)
            // ══════════════════════════════════════════════════════════════
            if (in_array($rol, [1, 4])) {
                $db->query("SELECT COUNT(*) AS total FROM inventario WHERE is_active = TRUE");
                $data['kpiBienes'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT COUNT(*) AS total FROM inventario
                            WHERE condicion IN ('Dañado','En Reparación') AND is_active = TRUE");
                $data['kpiBienesAlerta'] = (int)($db->single()->total ?? 0);

                $db->query("SELECT COUNT(*) AS total FROM inventario
                            WHERE is_active = FALSE AND deleted_at IS NOT NULL
                              AND EXTRACT(YEAR FROM deleted_at) = :anio");
                $db->bind(':anio', $anio);
                $data['kpiBajasAnio'] = (int)($db->single()->total ?? 0);

                $totalBienes     = $data['kpiBienes'];
                $bienesAlerta    = $data['kpiBienesAlerta'];
                $data['tasaDeprec'] = $totalBienes > 0 ? round(($bienesAlerta / $totalBienes) * 100, 1) : 0;

                $db->query("SELECT condicion, COUNT(*) AS total
                            FROM inventario WHERE is_active = TRUE
                            GROUP BY condicion ORDER BY total DESC");
                $data['invPorCondicion'] = $db->resultSet();
            }

            // ══════════════════════════════════════════════════════════════
            // FEED DE ACTIVIDAD RECIENTE — solo Admin (rol 1)
            // ══════════════════════════════════════════════════════════════
            if ($rol === 1) {
                try {
                    $db->query("SELECT al.tabla_afectada, al.operacion, al.record_id,
                                       al.fecha, al.datos_nuevos,
                                       COALESCE(u.username, 'Sistema') AS username
                                FROM audit_logs al
                                LEFT JOIN usuarios u ON al.id_usuario = u.id
                                ORDER BY al.fecha DESC
                                LIMIT 15");
                    $data['feedActividad'] = $db->resultSet();
                } catch (\Exception $ignored) {
                    $data['feedActividad'] = [];
                }
            }

            // ══════════════════════════════════════════════════════════════
            // ALERTAS — según rol
            // ══════════════════════════════════════════════════════════════
            $alertas = [];

            if (in_array($rol, [1, 2])) {
                if (($data['kpiContratosVencen'] ?? 0) > 0) {
                    $n = $data['kpiContratosVencen'];
                    $alertas[] = ['tipo' => 'warning', 'ico' => 'bi-person-badge',
                        'msg' => "$n contrato(s) contratado(s) vencen en los próximos {$diasContrato} días"];
                }
                if (($data['kpiAmonDespido'] ?? 0) > 0) {
                    $n = $data['kpiAmonDespido'];
                    $alertas[] = ['tipo' => 'danger', 'ico' => 'bi-flag-fill',
                        'msg' => "$n empleado(s) con 3+ amonestaciones (causa de despido)"];
                }
                if (($data['kpiPermisosPendientes'] ?? 0) > 0) {
                    $n = $data['kpiPermisosPendientes'];
                    $alertas[] = ['tipo' => 'info', 'ico' => 'bi-calendar2-week',
                        'msg' => "$n permiso(s)/reposo(s) pendiente(s) de aprobar"];
                }
            }
            if (in_array($rol, [1, 3])) {
                $db->query("SELECT COUNT(*) AS total FROM pasantes WHERE is_active = TRUE
                            AND estado = 'En Curso' AND fecha_fin IS NOT NULL
                            AND fecha_fin BETWEEN CURRENT_DATE AND (CURRENT_DATE + ($diasPasante || ' days')::INTERVAL)");
                $pasantesCulm = (int)($db->single()->total ?? 0);
                if ($pasantesCulm > 0) {
                    $alertas[] = ['tipo' => 'info', 'ico' => 'bi-journal-text',
                        'msg' => "$pasantesCulm pasante(s) culminan en los próximos {$diasPasante} días"];
                }
                if (($data['kpiActividadesActivas'] ?? 0) > 0) {
                    $n = $data['kpiActividadesActivas'];
                    $alertas[] = ['tipo' => 'brand', 'ico' => 'bi-mortarboard',
                        'msg' => "$n actividad(es) de formación actualmente en curso o programadas"];
                }
            }
            if (in_array($rol, [1, 4]) && ($data['kpiBienesAlerta'] ?? 0) > 0) {
                $n = $data['kpiBienesAlerta'];
                $alertas[] = ['tipo' => 'danger', 'ico' => 'bi-exclamation-triangle',
                    'msg' => "$n bien(es) en estado de alerta (dañado o en reparación)"];
            }
            $data['alertas'] = $alertas;

        } catch (Exception $e) {
            $data['dash_error'] = $e->getMessage();
        }

        $this->view('dashboard/index', $data);
    }

    public function accesoDenegado() {
        $data = [
            'titulo'  => 'Acceso Denegado',
            'mensaje' => 'No tienes permisos suficientes para acceder a este módulo. Contacta al administrador del sistema.'
        ];
        $this->view('dashboard/acceso_denegado', $data);
    }
}
