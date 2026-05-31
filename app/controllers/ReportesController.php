<?php
/**
 * ReportesController — Módulo de Reportes e Indicadores (RF27-RF31)
 * Incluye exportación a CSV (Excel) y PDF (HTML imprimible)
 */
class ReportesController extends Controller {

    public function index() {
        $data = [
            'titulo' => 'Centro de Reportes e Indicadores'
        ];
        $this->view('reportes/index', $data);
    }

    private function requireRoles(array $roles) {
        $rol = (int)($_SESSION['user_rol'] ?? 0);
        if (!in_array($rol, $roles)) {
            flash('global_msg', 'No tienes permiso para acceder a este reporte.', 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
            exit;
        }
    }

    // =========================================================================
    // RF27: Reporte de Asistencia
    // =========================================================================
    public function asistencia() {
        $this->requireRoles([1, 2]);
        try {
            $db = new Database();
            $db->query("SELECT id, nombre FROM departamentos WHERE is_active = TRUE ORDER BY nombre ASC");
            $departamentos = $db->resultSet();

            $registros = $this->queryAsistencia();
            $stats     = $this->statsAsistencia();

            $data = [
                'titulo'        => 'Reporte de Asistencia',
                'registros'     => $registros,
                'stats'         => $stats,
                'departamentos' => $departamentos,
                'fecha_inicio'  => $_GET['fecha_inicio']  ?? date('Y-m-01'),
                'fecha_fin'     => $_GET['fecha_fin']     ?? date('Y-m-d'),
                'filtro_depto'  => $_GET['departamento']  ?? '',
                'filtro_busca'  => $_GET['buscar']        ?? '',
            ];
            $this->view('reportes/asistencia', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de asistencia: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarAsistenciaCsv() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryAsistencia();
            $headers   = ['Fecha', 'Cédula', 'Nombre', 'Apellido', 'Departamento', 'Tipo Contrato', 'Entrada', 'Salida', 'Observación'];
            $rows      = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->fecha,
                    $r->cedula,
                    $r->nombre,
                    $r->apellido,
                    $r->departamento,
                    $r->tipo_contrato ?? '-',
                    $r->hora_entrada,
                    $r->hora_salida ?? '',
                    $r->observacion ?? '',
                ];
            }
            $this->exportCsv('reporte_asistencia', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarAsistenciaPdf() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryAsistencia();
            $stats     = $this->statsAsistencia();
            $fi        = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $ff        = $_GET['fecha_fin']    ?? date('Y-m-d');

            $headers = ['Fecha', 'Empleado', 'Cédula', 'Departamento', 'Tipo Contrato', 'Entrada', 'Salida'];
            $rows    = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->fecha,
                    $r->nombre . ' ' . $r->apellido,
                    $r->cedula,
                    $r->departamento,
                    $r->tipo_contrato ?? '-',
                    $r->hora_entrada,
                    $r->hora_salida ?? '-',
                ];
            }
            $kpis = [
                'Total Registros'    => $stats->total,
                'Empleados con Reg.' => $stats->empleados_unicos,
                'Días con Registros' => $stats->dias_con_registros,
                'Período'            => "$fi a $ff",
            ];
            $this->exportPdf("Reporte de Asistencia", "Período: $fi — $ff", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryAsistencia() {
        $db    = new Database();
        $fi    = $_GET['fecha_inicio']    ?? date('Y-m-01');
        $ff    = $_GET['fecha_fin']       ?? date('Y-m-d');
        $depto = trim($_GET['departamento'] ?? '');
        $busca = trim($_GET['buscar']       ?? '');

        $where = "a.is_active = TRUE AND a.fecha BETWEEN :fecha_inicio AND :fecha_fin";
        if ($depto) $where .= " AND e.id_departamento = :depto";
        if ($busca) $where .= " AND (p.nombre ILIKE :busca OR p.apellido ILIKE :busca OR p.cedula ILIKE :busca)";

        $db->query("SELECT a.fecha, a.hora_entrada, a.hora_salida, a.observacion,
                           p.nombre, p.apellido, p.cedula,
                           d.nombre as departamento, e.tipo_contrato
                    FROM asistencias a
                    INNER JOIN empleados e     ON a.id_empleado     = e.id
                    INNER JOIN personas p      ON e.id_persona      = p.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY a.fecha DESC, p.apellido ASC");
        $db->bind(':fecha_inicio', $fi);
        $db->bind(':fecha_fin',    $ff);
        if ($depto) $db->bind(':depto', (int)$depto);
        if ($busca) $db->bind(':busca', '%' . $busca . '%');
        return $db->resultSet();
    }

    private function statsAsistencia() {
        $db    = new Database();
        $fi    = $_GET['fecha_inicio']    ?? date('Y-m-01');
        $ff    = $_GET['fecha_fin']       ?? date('Y-m-d');
        $depto = trim($_GET['departamento'] ?? '');
        $busca = trim($_GET['buscar']       ?? '');

        $joins = "INNER JOIN empleados e ON a.id_empleado = e.id INNER JOIN personas p ON e.id_persona = p.id";
        $where = "a.is_active = TRUE AND a.fecha BETWEEN :fi AND :ff";
        if ($depto) $where .= " AND e.id_departamento = :depto";
        if ($busca) $where .= " AND (p.nombre ILIKE :busca OR p.apellido ILIKE :busca OR p.cedula ILIKE :busca)";

        $db->query("SELECT COUNT(*) as total,
                           COUNT(DISTINCT a.id_empleado) as empleados_unicos,
                           COUNT(DISTINCT a.fecha) as dias_con_registros
                    FROM asistencias a {$joins}
                    WHERE {$where}");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        if ($depto) $db->bind(':depto', (int)$depto);
        if ($busca) $db->bind(':busca', '%' . $busca . '%');
        return $db->single();
    }

    // =========================================================================
    // RF28: Reporte de Talleres
    // =========================================================================
    public function talleres() {
        $this->requireRoles([1, 3]);
        try {
            $talleres = $this->queryTalleres();
            $stats    = $this->statsTalleres();

            $data = [
                'titulo'        => 'Reporte de Talleres y Formación',
                'talleres'      => $talleres,
                'stats'         => $stats,
                'estado_filtro' => $_GET['estado']         ?? '',
                'tipo_filtro'   => $_GET['tipo_actividad'] ?? '',
                'nombre_filtro' => $_GET['nombre']         ?? '',
                'fecha_inicio'  => $_GET['fecha_inicio']   ?? '',
                'fecha_fin'     => $_GET['fecha_fin']      ?? '',
            ];
            $this->view('reportes/talleres', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de talleres: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarTalleresCsv() {
        $this->requireRoles([1, 3]);
        try {
            $talleres = $this->queryTalleres();
            $headers  = ['Taller', 'Tipo', 'Facilitador', 'Sede', 'Fecha Inicio', 'Estado', 'Ámbito', 'Inscritos', 'Cupo Máx.', 'Mujeres', 'Hombres', 'Niños/as', 'Total Atend.'];
            $rows     = [];
            foreach ($talleres as $t) {
                $rows[] = [
                    $t->nombre,
                    $t->tipo_actividad ?? '-',
                    $t->facilitador_nombre . ' ' . $t->facilitador_apellido,
                    $t->sede ?? 'Sin sede',
                    $t->fecha_inicio,
                    $t->estado,
                    ($t->es_interna ?? false) ? 'Interna' : 'Externa',
                    (int)($t->total_inscritos  ?? 0),
                    (int)($t->cupo_maximo      ?? 0),
                    (int)($t->mujeres          ?? 0),
                    (int)($t->hombres          ?? 0),
                    (int)($t->ninas ?? 0) + (int)($t->ninos ?? 0),
                    (int)($t->total_atendidas  ?? 0),
                ];
            }
            $this->exportCsv('reporte_talleres', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarTalleresPdf() {
        $this->requireRoles([1, 3]);
        try {
            $talleres = $this->queryTalleres();
            $stats    = $this->statsTalleres();

            $headers = ['Taller', 'Tipo', 'Facilitador', 'Fecha', 'Estado', 'Inscritos/Cupo', 'Total Atend.'];
            $rows    = [];
            foreach ($talleres as $t) {
                $rows[] = [
                    $t->nombre,
                    $t->tipo_actividad ?? '-',
                    $t->facilitador_nombre . ' ' . $t->facilitador_apellido,
                    $t->fecha_inicio,
                    $t->estado,
                    $t->total_inscritos . '/' . $t->cupo_maximo,
                    (int)($t->total_atendidas ?? 0),
                ];
            }
            $kpis = [
                'Total Actividades' => $stats->total_talleres,
                'Finalizadas'       => $stats->finalizados,
                'En Curso'          => $stats->en_curso,
                'Canceladas'        => $stats->cancelados,
                'Total Inscritos'   => $stats->total_participantes,
            ];
            $this->exportPdf("Reporte de Talleres y Formación", "IMATUR — Formación Comunitaria", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryTalleres() {
        $db     = new Database();
        $estado = trim($_GET['estado']         ?? '');
        $tipo   = trim($_GET['tipo_actividad'] ?? '');
        $nombre = trim($_GET['nombre']         ?? '');
        $fi     = trim($_GET['fecha_inicio']   ?? '');
        $ff     = trim($_GET['fecha_fin']      ?? '');

        $sql = "SELECT t.*, uf.nombre as sede,
                       p.nombre as facilitador_nombre, p.apellido as facilitador_apellido,
                       inf.mujeres, inf.hombres, inf.ninas, inf.ninos, inf.total_atendidas,
                       (SELECT COUNT(*) FROM participantes_taller pt
                        WHERE pt.id_taller = t.id AND pt.is_active = TRUE) as total_inscritos
                FROM talleres t
                LEFT  JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                LEFT  JOIN taller_informes inf       ON t.id = inf.id_taller
                INNER JOIN empleados e               ON t.id_facilitador = e.id
                INNER JOIN personas p                ON e.id_persona = p.id
                WHERE t.is_active = TRUE";
        if ($estado) $sql .= " AND t.estado = :estado";
        if ($tipo)   $sql .= " AND t.tipo_actividad = :tipo";
        if ($nombre) $sql .= " AND t.nombre ILIKE :nombre";
        if ($fi)     $sql .= " AND t.fecha_inicio >= :fi";
        if ($ff)     $sql .= " AND t.fecha_inicio <= :ff";
        $sql .= " ORDER BY t.fecha_inicio DESC";
        $db->query($sql);
        if ($estado) $db->bind(':estado', $estado);
        if ($tipo)   $db->bind(':tipo',   $tipo);
        if ($nombre) $db->bind(':nombre', '%' . $nombre . '%');
        if ($fi)     $db->bind(':fi', $fi);
        if ($ff)     $db->bind(':ff', $ff);
        return $db->resultSet();
    }

    private function statsTalleres() {
        $db     = new Database();
        $estado = trim($_GET['estado']         ?? '');
        $tipo   = trim($_GET['tipo_actividad'] ?? '');
        $nombre = trim($_GET['nombre']         ?? '');
        $fi     = trim($_GET['fecha_inicio']   ?? '');
        $ff     = trim($_GET['fecha_fin']      ?? '');

        $where = "t.is_active = TRUE";
        if ($estado) $where .= " AND t.estado = :estado";
        if ($tipo)   $where .= " AND t.tipo_actividad = :tipo";
        if ($nombre) $where .= " AND t.nombre ILIKE :nombre";
        if ($fi)     $where .= " AND t.fecha_inicio >= :fi";
        if ($ff)     $where .= " AND t.fecha_inicio <= :ff";

        $db->query("SELECT COUNT(t.id) as total_talleres,
                        COUNT(CASE WHEN t.estado = 'Finalizado' THEN 1 END) as finalizados,
                        COUNT(CASE WHEN t.estado = 'En Curso'   THEN 1 END) as en_curso,
                        COUNT(CASE WHEN t.estado = 'Programado' THEN 1 END) as programados,
                        COUNT(CASE WHEN t.estado = 'Cancelado'  THEN 1 END) as cancelados,
                        COALESCE(SUM((SELECT COUNT(*) FROM participantes_taller pt
                                      WHERE pt.id_taller = t.id AND pt.is_active = TRUE)), 0) as total_participantes
                    FROM talleres t WHERE {$where}");
        if ($estado) $db->bind(':estado', $estado);
        if ($tipo)   $db->bind(':tipo',   $tipo);
        if ($nombre) $db->bind(':nombre', '%' . $nombre . '%');
        if ($fi)     $db->bind(':fi', $fi);
        if ($ff)     $db->bind(':ff', $ff);
        return $db->single();
    }

    // =========================================================================
    // RF29: Reporte de Rutas Turísticas
    // =========================================================================
    public function rutas() {
        $this->requireRoles([1, 3]);
        try {
            $filtroEstado     = trim($_GET['estado']            ?? '');
            $filtroDificultad = trim($_GET['nivel_dificultad']  ?? '');
            $rutas            = $this->queryRutas($filtroEstado, $filtroDificultad);
            $stats            = $this->statsRutas();

            $data = [
                'titulo'            => 'Reporte de Rutas Turísticas',
                'rutas'             => $rutas,
                'stats'             => $stats,
                'filtro_estado'     => $filtroEstado,
                'filtro_dificultad' => $filtroDificultad,
            ];
            $this->view('reportes/rutas', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de rutas: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarRutasCsv() {
        $this->requireRoles([1, 3]);
        try {
            $rutas   = $this->queryRutas();
            $headers = ['Ruta', 'Fecha Visita', 'Departamento', 'Dificultad', 'Estado', 'Participantes', 'Paradas', 'Equipos'];
            $rows    = [];
            foreach ($rutas as $r) {
                $rows[] = [
                    $r->nombre,
                    $r->fecha_visita ? date('d/m/Y', strtotime($r->fecha_visita)) : '-',
                    $r->departamento_nombre ?? '-',
                    $r->nivel_dificultad,
                    $r->estado,
                    (int)$r->total_participantes,
                    (int)$r->total_puntos,
                    (int)$r->total_equipos,
                ];
            }
            $this->exportCsv('reporte_rutas', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarRutasPdf() {
        $this->requireRoles([1, 3]);
        try {
            $rutas  = $this->queryRutas();
            $stats  = $this->statsRutas();

            $headers = ['Ruta', 'Fecha Visita', 'Departamento', 'Estado', 'Participantes', 'Paradas'];
            $rows    = [];
            foreach ($rutas as $r) {
                $rows[] = [
                    $r->nombre,
                    $r->fecha_visita ? date('d/m/Y', strtotime($r->fecha_visita)) : '-',
                    $r->departamento_nombre ?? '-',
                    $r->estado,
                    (int)$r->total_participantes,
                    (int)$r->total_puntos,
                ];
            }
            $kpis = [
                'Total Rutas'    => $stats->total_rutas,
                'Activas'        => $stats->activas,
                'Inactivas'      => $stats->inactivas,
                'En Mantenimiento' => $stats->mantenimiento,
            ];
            $this->exportPdf("Reporte de Rutas Turísticas", "IMATUR — Gestión Turística", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryRutas(string $estado = '', string $dificultad = '') {
        $db    = new Database();
        $where = "r.is_active = TRUE";
        if ($estado)     $where .= " AND r.estado = :estado";
        if ($dificultad) $where .= " AND r.nivel_dificultad = :dificultad";
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           (SELECT COUNT(*) FROM puntos_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) as total_puntos,
                           (SELECT COUNT(*) FROM participantes_ruta par WHERE par.id_ruta = r.id) as total_participantes,
                           (SELECT COUNT(*) FROM ruta_inventario ri WHERE ri.id_ruta = r.id) as total_equipos
                    FROM rutas r
                    LEFT JOIN departamentos d ON r.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY r.created_at DESC");
        if ($estado)     $db->bind(':estado', $estado);
        if ($dificultad) $db->bind(':dificultad', $dificultad);
        return $db->resultSet();
    }

    private function statsRutas() {
        $db = new Database();
        $db->query("SELECT COUNT(*) as total_rutas,
                        COUNT(CASE WHEN estado = 'Activa'          THEN 1 END) as activas,
                        COUNT(CASE WHEN estado = 'Inactiva'        THEN 1 END) as inactivas,
                        COUNT(CASE WHEN estado = 'En Mantenimiento' THEN 1 END) as mantenimiento
                    FROM rutas WHERE is_active = TRUE");
        return $db->single();
    }

    public function exportarParticipantesCsv($id_taller) {
        $this->requireRoles([1, 3]);
        try {
            $db = new Database();
            $db->query("SELECT
                               CASE WHEN pt.id_persona IS NULL THEN 'Niño/a' ELSE 'Participante' END AS tipo,
                               COALESCE(p.cedula, pt.cedula_libre, '')    AS cedula,
                               COALESCE(p.nombre, pt.nombre_libre, '')    AS nombre,
                               COALESCE(p.apellido, pt.apellido_libre, '') AS apellido,
                               COALESCE(p.telefono, '')                   AS telefono,
                               pt.asistio,
                               COALESCE(pt.nombre_docente, '')            AS nombre_docente,
                               COALESCE(pt.cedula_docente, '')            AS cedula_docente,
                               CASE pt.genero_libre
                                   WHEN 'M' THEN 'Masculino'
                                   WHEN 'F' THEN 'Femenino'
                                   WHEN 'O' THEN 'Otro'
                                   ELSE '' END                            AS genero_libre,
                               COALESCE(par.nombre, '')                   AS parroquia_libre,
                               COALESCE(pt.direccion_libre, '')           AS direccion_libre
                        FROM participantes_taller pt
                        LEFT JOIN personas  p   ON pt.id_persona        = p.id
                        LEFT JOIN parroquia par ON pt.parroquia_id_libre = par.id
                        WHERE pt.id_taller = :id_taller AND pt.is_active = TRUE
                        ORDER BY COALESCE(p.apellido, pt.apellido_libre) ASC");
            $db->bind(':id_taller', $id_taller);
            $participantes = $db->resultSet();

            $db->query("SELECT nombre FROM talleres WHERE id = :id_taller");
            $db->bind(':id_taller', $id_taller);
            $t = $db->single();

            $headers = ['Tipo', 'Cédula/ID', 'Nombre', 'Apellido', 'Teléfono', 'Asistió', 'Docente/Tutor', 'C.I. Docente', 'Género', 'Parroquia', 'Dirección'];
            $rows    = [];
            foreach ($participantes as $p) {
                $rows[] = [
                    $p->tipo,
                    $p->cedula,
                    $p->nombre,
                    $p->apellido,
                    $p->telefono,
                    $p->asistio ? 'Sí' : 'No',
                    $p->nombre_docente,
                    $p->cedula_docente,
                    $p->genero_libre,
                    $p->parroquia_libre,
                    $p->direccion_libre,
                ];
            }
            $this->exportCsv("Inscritos_" . str_replace(' ', '_', $t->nombre ?? 'taller'), $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar participantes: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/talleres');
        }
    }

    // =========================================================================
    // DOSSIER INTEGRAL DE TALLER (PAGINA CONTINUA)
    // =========================================================================

    public function dossier($id) {
        $this->requireRoles([1, 3]);
        try {
            $db = new Database();
            $db->query("SELECT t.*, uf.nombre as sede,
                               p.nombre as fac_nom, p.apellido as fac_ape, e.nro_expediente
                        FROM talleres t
                        LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                        INNER JOIN empleados e             ON t.id_facilitador = e.id
                        INNER JOIN personas p              ON e.id_persona = p.id
                        WHERE t.id = :id");
            $db->bind(':id', $id);
            $taller = $db->single();

            if (!$taller) {
                header('Location: ' . URL_ROOT . '/reportes/talleres');
                exit;
            }

            $db->query("SELECT * FROM taller_informes WHERE id_taller = :id");
            $db->bind(':id', $id);
            $informe = $db->single();

            $db->query("SELECT
                               CASE WHEN pt.id_persona IS NULL THEN TRUE ELSE FALSE END AS es_libre,
                               COALESCE(p.cedula, pt.cedula_libre, '')    AS cedula,
                               COALESCE(p.nombre, pt.nombre_libre, '')    AS nombre,
                               COALESCE(p.apellido, pt.apellido_libre, '') AS apellido,
                               pt.asistio,
                               COALESCE(pt.nombre_docente, '')            AS nombre_docente,
                               COALESCE(pt.cedula_docente, '')            AS cedula_docente
                        FROM participantes_taller pt
                        LEFT JOIN personas p ON pt.id_persona = p.id
                        WHERE pt.id_taller = :id AND pt.is_active = TRUE
                        ORDER BY COALESCE(p.apellido, pt.apellido_libre) ASC");
            $db->bind(':id', $id);
            $participantes = $db->resultSet();

            $data = [
                'titulo'        => 'Dossier de Taller',
                'taller'        => $taller,
                'informe'       => $informe,
                'participantes' => $participantes,
            ];
            $this->view('reportes/taller_detalle', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al cargar el dossier: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/talleres');
        }
    }

    public function exportarDossierCsv($id) {
        $this->requireRoles([1, 3]);
        try {
            $db = new Database();
            $db->query("SELECT t.*, p.nombre || ' ' || p.apellido as facilitador, uf.nombre as sede
                        FROM talleres t
                        INNER JOIN empleados e ON t.id_facilitador = e.id
                        INNER JOIN personas p  ON e.id_persona = p.id
                        LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                        WHERE t.id = :id");
            $db->bind(':id', $id);
            $t = $db->single();

            if (!$t) throw new Exception('Taller no encontrado.');

            $db->query("SELECT * FROM taller_informes WHERE id_taller = :id");
            $db->bind(':id', $id);
            $inf = $db->single();

            $db->query("SELECT
                               CASE WHEN pt.id_persona IS NULL THEN 'Niño/a' ELSE 'Participante' END AS tipo,
                               COALESCE(p.cedula, pt.cedula_libre, '')    AS cedula,
                               COALESCE(p.nombre, pt.nombre_libre, '') || ' ' || COALESCE(p.apellido, pt.apellido_libre, '') AS nombre,
                               pt.asistio,
                               COALESCE(pt.nombre_docente, '') AS nombre_docente,
                               COALESCE(pt.cedula_docente, '') AS cedula_docente
                        FROM participantes_taller pt
                        LEFT JOIN personas p ON pt.id_persona = p.id
                        WHERE pt.id_taller = :id AND pt.is_active = TRUE
                        ORDER BY COALESCE(p.apellido, pt.apellido_libre) ASC");
            $db->bind(':id', $id);
            $participantes = $db->resultSet();
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar dossier: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/talleres');
            return;
        }

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Dossier_Taller_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

        fputcsv($output, ['REPÚBLICA BOLIVARIANA DE VENEZUELA'], ';');
        fputcsv($output, ['ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE'], ';');
        fputcsv($output, ['Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)  —  RIF. G-20008498-7'], ';');
        fputcsv($output, ['Cumaná, Estado Sucre'], ';');
        fputcsv($output, ['Generado por: ' . ($_SESSION['user_username'] ?? 'Sistema') . '    Fecha: ' . date('d/m/Y H:i')], ';');
        fputcsv($output, [''], ';');

        fputcsv($output, ['DOSSIER INTEGRAL DE ACTIVIDAD - IMATUR'], ';');
        fputcsv($output, ['Taller:',     $t->nombre], ';');
        fputcsv($output, ['Facilitador:', $t->facilitador], ';');
        fputcsv($output, ['Lugar:',       $t->sede ?: 'No especificada'], ';');
        fputcsv($output, ['Fecha:',       $t->fecha_inicio], ';');
        fputcsv($output, ['Estado:',      $t->estado], ';');
        fputcsv($output, [], ';');

        fputcsv($output, ['RESUMEN DEMOGRÁFICO'], ';');
        fputcsv($output, ['Mujeres', 'Hombres', 'Niñas (5-11)', 'Niños (5-11)', 'Total Atendidos'], ';');
        fputcsv($output, [
            $inf->mujeres         ?? 0,
            $inf->hombres         ?? 0,
            $inf->ninas           ?? 0,
            $inf->ninos           ?? 0,
            $inf->total_atendidas ?? 0,
        ], ';');
        fputcsv($output, [], ';');

        fputcsv($output, ['LISTADO DE PERSONAS INSCRITAS'], ';');
        fputcsv($output, ['Tipo', 'Cédula', 'Nombre Completo', 'Docente/Tutor', 'C.I. Docente', 'Asistencia'], ';');
        foreach ($participantes as $p) {
            fputcsv($output, [
                $p->tipo,
                $p->cedula,
                $p->nombre,
                $p->nombre_docente,
                $p->cedula_docente,
                $p->asistio ? 'Presente' : 'Ausente',
            ], ';');
        }

        fclose($output);
        exit;
    }

    // =========================================================================
    // EXPORTACIÓN DE PASANTES
    // =========================================================================

    public function pasantes() {
        $this->requireRoles([1, 3]);
        try {
            $filtroEstado = trim($_GET['estado']       ?? '');
            $fi           = trim($_GET['fecha_inicio'] ?? '');
            $ff           = trim($_GET['fecha_fin']    ?? '');
            $busca        = trim($_GET['buscar']       ?? '');

            $db    = new Database();
            $where = "p.is_active = TRUE";
            if ($filtroEstado) $where .= " AND p.estado = :estado";
            if ($fi)           $where .= " AND p.fecha_inicio >= :fi";
            if ($ff)           $where .= " AND (p.fecha_fin <= :ff OR p.fecha_fin IS NULL)";
            if ($busca)        $where .= " AND (pp.nombre ILIKE :busca OR pp.apellido ILIKE :busca OR pp.cedula ILIKE :busca)";

            $db->query("SELECT p.*,
                               pp.cedula, pp.nombre, pp.apellido,
                               pt.nombre AS tutor_nombre, pt.apellido AS tutor_apellido
                        FROM pasantes p
                        INNER JOIN personas pp ON p.id_persona = pp.id
                        LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
                        LEFT  JOIN personas pt  ON e.id_persona = pt.id
                        WHERE {$where} ORDER BY p.fecha_inicio DESC");
            if ($filtroEstado) $db->bind(':estado', $filtroEstado);
            if ($fi)           $db->bind(':fi', $fi);
            if ($ff)           $db->bind(':ff', $ff);
            if ($busca)        $db->bind(':busca', '%' . $busca . '%');
            $pasantes = $db->resultSet();

            $db->query("SELECT COUNT(*) as total,
                            COUNT(CASE WHEN estado = 'En Curso'  THEN 1 END) as en_curso,
                            COUNT(CASE WHEN estado = 'Culminado' THEN 1 END) as culminados,
                            COUNT(CASE WHEN estado = 'Postulado' THEN 1 END) as postulados,
                            COUNT(CASE WHEN estado = 'Aceptado'  THEN 1 END) as aceptados,
                            COUNT(CASE WHEN estado = 'Rechazado' THEN 1 END) as rechazados
                        FROM pasantes WHERE is_active = TRUE");
            $stats = $db->single();

            $data = [
                'titulo'        => 'Reporte de Pasantes',
                'pasantes'      => $pasantes,
                'stats'         => $stats,
                'filtro_estado' => $filtroEstado,
                'fecha_inicio'  => $fi,
                'fecha_fin'     => $ff,
                'filtro_busca'  => $busca,
            ];
            $this->view('reportes/pasantes', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de pasantes: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarPasantesCsv() {
        $this->requireRoles([1, 3]);
        try {
            $db = new Database();
            $db->query("SELECT p.*,
                               pp.cedula, pp.nombre, pp.apellido,
                               pt.nombre || ' ' || pt.apellido AS tutor
                        FROM pasantes p
                        INNER JOIN personas pp ON p.id_persona = pp.id
                        LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
                        LEFT  JOIN personas pt  ON e.id_persona = pt.id
                        WHERE p.is_active = TRUE ORDER BY pp.cedula ASC");
            $pasantes = $db->resultSet();

            $headers = ['Cédula', 'Nombre', 'Apellido', 'Institución', 'Carrera', 'Tutor', 'Inicio', 'Fin', 'Estado'];
            $rows    = [];
            foreach ($pasantes as $p) {
                $rows[] = [$p->cedula, $p->nombre, $p->apellido, $p->institucion, $p->carrera, $p->tutor ?? 'N/A', $p->fecha_inicio, $p->fecha_fin, $p->estado];
            }
            $this->exportCsv("Reporte_Pasantes", $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarPasantesPdf() {
        $this->requireRoles([1, 3]);
        try {
            $db = new Database();
            $db->query("SELECT p.*,
                               pp.cedula, pp.nombre, pp.apellido,
                               pt.nombre || ' ' || pt.apellido AS tutor
                        FROM pasantes p
                        INNER JOIN personas pp ON p.id_persona = pp.id
                        LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
                        LEFT  JOIN personas pt  ON e.id_persona = pt.id
                        WHERE p.is_active = TRUE ORDER BY pp.cedula ASC");
            $pasantes = $db->resultSet();

            $headers = ['Cédula', 'Nombre', 'Institución', 'Tutor', 'Estado'];
            $rows    = [];
            foreach ($pasantes as $p) {
                $rows[] = [$p->cedula, $p->nombre . ' ' . $p->apellido, $p->institucion, $p->tutor ?? '-', $p->estado];
            }
            $this->exportPdf("Listado Maestro de Pasantes", "IMATUR — Control de Formación Institucional", $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    // =========================================================================
    // Reporte de Visitantes y Visitas
    // =========================================================================
    public function visitantes() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryVisitantes();
            $stats     = $this->statsVisitantes();

            $data = [
                'titulo'          => 'Reporte de Visitantes',
                'registros'       => $registros,
                'stats'           => $stats,
                'fecha_inicio'    => $_GET['fecha_inicio'] ?? date('Y-m-01'),
                'fecha_fin'       => $_GET['fecha_fin']    ?? date('Y-m-d'),
                'filtro_motivo'   => $_GET['motivo']       ?? '',
                'filtro_empleado' => $_GET['empleado']     ?? '',
            ];
            $this->view('reportes/visitantes', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de visitantes: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarVisitantesCsv() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryVisitantes();
            $headers   = ['Fecha', 'Hora Entrada', 'Cédula', 'Nombre', 'Apellido', 'Procedencia', 'Motivo', 'Empleado Visitado', 'Observaciones'];
            $rows      = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->fecha ?? date('Y-m-d', strtotime($r->hora_entrada)),
                    date('H:i', strtotime($r->hora_entrada)),
                    $r->cedula      ?? '',
                    $r->nombre      ?? '',
                    $r->apellido    ?? '',
                    $r->procedencia ?? '',
                    $r->motivo      ?? '',
                    trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? '')) ?: 'N/A',
                    $r->observaciones ?? '',
                ];
            }
            $this->exportCsv('reporte_visitantes', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarVisitantesPdf() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryVisitantes();
            $stats     = $this->statsVisitantes();
            $fi        = $_GET['fecha_inicio'] ?? date('Y-m-01');
            $ff        = $_GET['fecha_fin']    ?? date('Y-m-d');

            $headers = ['Fecha', 'Hora Entrada', 'Cédula', 'Nombre y Apellido', 'Procedencia', 'Motivo', 'Empleado Visitado'];
            $rows    = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->fecha ?? '-',
                    date('H:i', strtotime($r->hora_entrada)),
                    $r->cedula ?? '-',
                    trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                    $r->procedencia ?? '-',
                    $r->motivo ?? '-',
                    trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? '')) ?: '-',
                ];
            }
            $kpis = [
                'Total Visitas'     => $stats->total_visitas,
                'Visitantes Únicos' => $stats->visitantes_unicos,
                'Período'           => "$fi a $ff",
            ];
            $this->exportPdf("Reporte de Visitantes", "Período: $fi — $ff", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryVisitantes() {
        $db       = new Database();
        $fi       = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $ff       = $_GET['fecha_fin']    ?? date('Y-m-d');
        $motivo   = trim($_GET['motivo']   ?? '');
        $empleado = trim($_GET['empleado'] ?? '');

        $where = "v.is_active = TRUE AND DATE(v.hora_entrada) BETWEEN :fi AND :ff";
        if ($motivo   !== '') $where .= " AND v.motivo ILIKE :motivo";
        if ($empleado !== '') $where .= " AND (pe.nombre ILIKE :empleado OR pe.apellido ILIKE :empleado)";

        $db->query("SELECT v.hora_entrada, v.hora_salida, v.motivo, v.observaciones,
                           DATE(v.hora_entrada) AS fecha,
                           COALESCE(pe2.cedula,   vis.cedula)   AS cedula,
                           COALESCE(pe2.nombre,   vis.nombre)   AS nombre,
                           COALESCE(pe2.apellido, vis.apellido) AS apellido,
                           vis.procedencia,
                           pe.nombre   AS emp_nombre,
                           pe.apellido AS emp_apellido
                    FROM visitas v
                    INNER JOIN visitantes vis ON v.id_visitante = vis.id
                    LEFT  JOIN personas pe2  ON vis.id_persona  = pe2.id
                    LEFT  JOIN empleados e   ON v.id_empleado   = e.id
                    LEFT  JOIN personas pe   ON e.id_persona    = pe.id
                    WHERE {$where}
                    ORDER BY v.hora_entrada DESC");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        if ($motivo   !== '') $db->bind(':motivo',   '%' . $motivo . '%');
        if ($empleado !== '') $db->bind(':empleado', '%' . $empleado . '%');
        return $db->resultSet();
    }

    private function statsVisitantes() {
        $db = new Database();
        $fi = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $ff = $_GET['fecha_fin']    ?? date('Y-m-d');
        $db->query("SELECT
                        COUNT(*) AS total_visitas,
                        COUNT(DISTINCT id_visitante) AS visitantes_unicos
                    FROM visitas
                    WHERE is_active = TRUE AND DATE(hora_entrada) BETWEEN :fi AND :ff");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        return $db->single();
    }

    // =========================================================================
    // Reporte de Inventario
    // =========================================================================
    public function inventario() {
        $this->requireRoles([1, 4]);
        try {
            $registros = $this->queryInventario();
            $stats     = $this->statsInventario();

            $data = [
                'titulo'           => 'Reporte de Inventario',
                'registros'        => $registros,
                'stats'            => $stats,
                'filtro_condicion' => $_GET['condicion'] ?? '',
                'filtro_categoria' => $_GET['categoria'] ?? '',
                'filtro_ubicacion' => $_GET['ubicacion'] ?? '',
            ];
            $this->view('reportes/inventario', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de inventario: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarInventarioCsv() {
        $this->requireRoles([1, 4]);
        try {
            $registros = $this->queryInventario();
            $headers   = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición', 'Marca', 'Modelo', 'Serial'];
            $rows      = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->codigo_bn,
                    $r->nombre,
                    $r->categoria ?? '-',
                    $r->ubicacion ?? '-',
                    $r->condicion,
                    $r->marca     ?? '-',
                    $r->modelo    ?? '-',
                    $r->serial    ?? '-',
                ];
            }
            $this->exportCsv('reporte_inventario', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarInventarioPdf() {
        $this->requireRoles([1, 4]);
        try {
            $registros = $this->queryInventario();
            $stats     = $this->statsInventario();

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición'];
            $rows    = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->codigo_bn,
                    $r->nombre,
                    $r->categoria ?? '-',
                    $r->ubicacion ?? '-',
                    $r->condicion,
                ];
            }
            $kpis = [
                'Total Bienes' => $stats->total,
                'Nuevos'       => $stats->nuevos,
                'Buenos'       => $stats->buenos,
                'Regulares'    => $stats->regulares,
                'Dañados'      => $stats->danados,
            ];
            $this->exportPdf("Reporte de Inventario de Bienes", "IMATUR — Control Patrimonial", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryInventario() {
        $db        = new Database();
        $condicion = trim($_GET['condicion'] ?? '');
        $categoria = trim($_GET['categoria'] ?? '');
        $ubicacion = trim($_GET['ubicacion'] ?? '');

        $where = "i.is_active = TRUE";
        if ($condicion !== '') $where .= " AND i.condicion = :condicion";
        if ($categoria !== '') $where .= " AND c.nombre ILIKE :categoria";
        if ($ubicacion !== '') $where .= " AND u.nombre ILIKE :ubicacion";

        $db->query("SELECT i.codigo_bn, i.nombre, i.condicion, i.marca, i.modelo, i.serial,
                           c.nombre AS categoria,
                           u.nombre AS ubicacion
                    FROM inventario i
                    LEFT JOIN categorias c  ON i.id_categoria = c.id
                    LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                    WHERE {$where}
                    ORDER BY c.nombre ASC, i.nombre ASC");
        if ($condicion !== '') $db->bind(':condicion', $condicion);
        if ($categoria !== '') $db->bind(':categoria', '%' . $categoria . '%');
        if ($ubicacion !== '') $db->bind(':ubicacion', '%' . $ubicacion . '%');
        return $db->resultSet();
    }

    private function statsInventario() {
        $db = new Database();
        $db->query("SELECT
                        COUNT(*) AS total,
                        COUNT(CASE WHEN condicion = 'Nuevo'         THEN 1 END) AS nuevos,
                        COUNT(CASE WHEN condicion = 'Bueno'         THEN 1 END) AS buenos,
                        COUNT(CASE WHEN condicion = 'Regular'       THEN 1 END) AS regulares,
                        COUNT(CASE WHEN condicion = 'Dañado'        THEN 1 END) AS danados,
                        COUNT(CASE WHEN condicion = 'En Reparación' THEN 1 END) AS reparacion
                    FROM inventario WHERE is_active = TRUE");
        return $db->single();
    }

    // =========================================================================
    // RF30: Indicadores Generales de Gestión
    // =========================================================================
    public function indicadores() {
        try {
            $db = new Database();

            // ── KPIs de resumen ───────────────────────────────────────────
            $db->query("SELECT COUNT(*) as total FROM empleados WHERE is_active = TRUE");
            $kpiEmpleados = $db->single();

            $db->query("SELECT COUNT(*) as total FROM visitas WHERE is_active = TRUE AND DATE(hora_entrada) = CURRENT_DATE");
            $kpiVisitasHoy = $db->single();

            $db->query("SELECT COUNT(*) as total FROM talleres WHERE estado IN ('En Curso', 'Programado') AND is_active = TRUE");
            $kpiActividadesActivas = $db->single();

            $db->query("SELECT COUNT(*) as total
                        FROM participantes_taller pt
                        JOIN talleres t ON pt.id_taller = t.id
                        WHERE EXTRACT(YEAR FROM t.fecha_inicio) = EXTRACT(YEAR FROM CURRENT_DATE)
                          AND pt.is_active = TRUE AND t.is_active = TRUE");
            $kpiFormadosAnio = $db->single();

            $db->query("SELECT COUNT(*) as total FROM rutas WHERE estado = 'Activa' AND is_active = TRUE");
            $kpiRutasActivas = $db->single();

            $db->query("SELECT COUNT(*) as total FROM pasantes WHERE estado = 'En Curso' AND is_active = TRUE");
            $kpiPasantesEnCurso = $db->single();

            $db->query("SELECT COUNT(*) as total FROM inventario WHERE is_active = TRUE");
            $kpiBienesActivos = $db->single();

            $db->query("SELECT COUNT(*) as total FROM inventario WHERE condicion IN ('Dañado', 'En Reparación') AND is_active = TRUE");
            $kpiBienesAlerta = $db->single();

            // ── Sección Personal ──────────────────────────────────────────
            $db->query("SELECT d.nombre as departamento, COUNT(e.id) as total
                        FROM departamentos d
                        LEFT JOIN empleados e ON d.id = e.id_departamento AND e.is_active = TRUE
                        WHERE d.is_active = TRUE GROUP BY d.nombre ORDER BY total DESC");
            $empPorDepto = $db->resultSet();

            $db->query("SELECT TO_CHAR(a.fecha, 'YYYY-MM') as mes, COUNT(*) as total
                        FROM asistencias a
                        WHERE a.is_active = TRUE AND a.fecha >= (CURRENT_DATE - INTERVAL '4 months')
                        GROUP BY mes ORDER BY mes ASC");
            $asistenciaPorMes = $db->resultSet();

            // ── Sección Formación ─────────────────────────────────────────
            $db->query("SELECT TO_CHAR(fecha_inicio, 'YYYY-MM') as mes, COUNT(*) as total
                        FROM talleres WHERE is_active = TRUE AND fecha_inicio >= (CURRENT_DATE - INTERVAL '6 months')
                        GROUP BY mes ORDER BY mes ASC");
            $talleresPorMes = $db->resultSet();

            $db->query("SELECT tipo_actividad, COUNT(*) as total
                        FROM talleres WHERE is_active = TRUE
                        GROUP BY tipo_actividad ORDER BY total DESC");
            $talleresPorTipo = $db->resultSet();

            $db->query("SELECT
                          COALESCE(SUM(CASE WHEN t.es_interna = TRUE  THEN 1 ELSE 0 END), 0) as internos,
                          COALESCE(SUM(CASE WHEN t.es_interna = FALSE THEN 1 ELSE 0 END), 0) as externos
                        FROM participantes_taller pt
                        JOIN talleres t ON pt.id_taller = t.id
                        WHERE pt.is_active = TRUE AND t.is_active = TRUE");
            $participantesTipo = $db->single();

            // ── Sección Recepción ─────────────────────────────────────────
            $db->query("SELECT DATE(hora_entrada) as dia, COUNT(*) as total
                        FROM visitas
                        WHERE is_active = TRUE AND hora_entrada >= (CURRENT_DATE - INTERVAL '14 days')
                        GROUP BY dia ORDER BY dia ASC");
            $visitasPorDia = $db->resultSet();

            $db->query("SELECT COALESCE(NULLIF(TRIM(motivo), ''), 'Sin especificar') as motivo, COUNT(*) as total
                        FROM visitas WHERE is_active = TRUE
                        GROUP BY COALESCE(NULLIF(TRIM(motivo), ''), 'Sin especificar')
                        ORDER BY total DESC LIMIT 6");
            $visitasPorMotivo = $db->resultSet();

            // ── Sección Inventario ────────────────────────────────────────
            $db->query("SELECT c.nombre as categoria, COUNT(i.id) as total
                        FROM categorias c
                        LEFT JOIN inventario i ON c.id = i.id_categoria AND i.is_active = TRUE
                        WHERE c.is_active = TRUE GROUP BY c.nombre ORDER BY total DESC");
            $invPorCat = $db->resultSet();

            $db->query("SELECT condicion, COUNT(*) as total FROM inventario WHERE is_active = TRUE GROUP BY condicion ORDER BY total DESC");
            $invPorCondicion = $db->resultSet();

            // ── F-3: Demografía de formación (año actual) ─────────────────
            $anioActual = (int)date('Y');
            $db->query("SELECT
                            COALESCE(SUM(ti.mujeres), 0) as mujeres,
                            COALESCE(SUM(ti.hombres), 0) as hombres,
                            COALESCE(SUM(ti.ninas),   0) as ninas,
                            COALESCE(SUM(ti.ninos),   0) as ninos,
                            COALESCE(SUM(ti.total_atendidas), 0) as total
                        FROM taller_informes ti
                        JOIN talleres t ON ti.id_taller = t.id
                        WHERE t.is_active = TRUE
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio");
            $db->bind(':anio', $anioActual);
            $demografiaFormacion = $db->single();

            // ── F-4: Cobertura territorial por sede de actividades ────────
            $db->query("SELECT COUNT(DISTINCT par.id_municipio) as municipios_cubiertos,
                               (SELECT COUNT(*) FROM municipio WHERE is_active = TRUE) as total_municipios
                        FROM talleres t
                        JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                        JOIN parroquia par ON uf.parroquia = par.id
                        WHERE t.is_active = TRUE
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio");
            $db->bind(':anio', $anioActual);
            $coberturaTerrForma = $db->single();

            $db->query("SELECT DISTINCT m.nombre as municipio
                        FROM talleres t
                        JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                        JOIN parroquia par ON uf.parroquia = par.id
                        JOIN municipio m ON par.id_municipio = m.id
                        WHERE t.is_active = TRUE
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                        ORDER BY m.nombre");
            $db->bind(':anio', $anioActual);
            $municipiosCubiertos = $db->resultSet();

            // ── F-2: Tipo de entidad atendida ─────────────────────────────
            $db->query("SELECT
                            CASE
                                WHEN t.es_interna = TRUE THEN 'Personal IMATUR'
                                WHEN t.tipo_ente IS NOT NULL AND t.tipo_ente <> '' THEN t.tipo_ente
                                ELSE 'Sin especificar'
                            END as tipo_ente,
                            COUNT(DISTINCT t.id) as talleres,
                            COALESCE(SUM(pt_cnt.cnt), 0) as participantes
                        FROM talleres t
                        LEFT JOIN (
                            SELECT id_taller, COUNT(*) as cnt
                            FROM participantes_taller WHERE is_active = TRUE GROUP BY id_taller
                        ) pt_cnt ON pt_cnt.id_taller = t.id
                        WHERE t.is_active = TRUE
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                        GROUP BY CASE
                            WHEN t.es_interna = TRUE THEN 'Personal IMATUR'
                            WHEN t.tipo_ente IS NOT NULL AND t.tipo_ente <> '' THEN t.tipo_ente
                            ELSE 'Sin especificar'
                        END
                        ORDER BY participantes DESC");
            $db->bind(':anio', $anioActual);
            $tipoEntidad = $db->resultSet();

            // ── F-5: Capacitadores activos ────────────────────────────────
            $db->query("SELECT p.nombre || ' ' || p.apellido as facilitador,
                               COUNT(t.id) as actividades,
                               COALESCE(SUM(pt_cnt.cnt), 0) as formados
                        FROM talleres t
                        JOIN empleados e ON t.id_facilitador = e.id
                        JOIN personas p ON e.id_persona = p.id
                        LEFT JOIN (
                            SELECT id_taller, COUNT(*) as cnt
                            FROM participantes_taller WHERE is_active = TRUE GROUP BY id_taller
                        ) pt_cnt ON pt_cnt.id_taller = t.id
                        WHERE t.is_active = TRUE
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                        GROUP BY p.nombre, p.apellido
                        ORDER BY actividades DESC
                        LIMIT 10");
            $db->bind(':anio', $anioActual);
            $capacitadores = $db->resultSet();

            // ── T-2: Participantes por tipo de ruta ───────────────────────
            $rutasPorTipo   = [];
            $institucionesRutas = [];
            $metaRutas      = null;
            $rutasAnio      = null;
            try {
                $db->query("SELECT COALESCE(r.tipo_ruta, 'General') as tipo_ruta,
                                   COUNT(DISTINCT r.id) as rutas,
                                   COUNT(pr.id) as participantes
                            FROM rutas r
                            LEFT JOIN participantes_ruta pr ON pr.id_ruta = r.id AND pr.is_active = TRUE
                            WHERE r.is_active = TRUE
                            GROUP BY r.tipo_ruta ORDER BY participantes DESC");
                $rutasPorTipo = $db->resultSet();

                // ── T-3: Instituciones participantes en rutas ──────────────
                $db->query("SELECT ie.nombre as institucion,
                                   ie.tipo, ie.municipio,
                                   COUNT(pr.id) as participantes,
                                   COUNT(DISTINCT pr.id_ruta) as rutas_participadas
                            FROM participantes_ruta pr
                            JOIN instituciones_externas ie ON pr.id_institucion = ie.id
                            JOIN rutas r ON pr.id_ruta = r.id
                            WHERE pr.is_active = TRUE AND r.is_active = TRUE AND ie.is_active = TRUE
                            GROUP BY ie.nombre, ie.tipo, ie.municipio
                            ORDER BY participantes DESC
                            LIMIT 10");
                $institucionesRutas = $db->resultSet();

                // ── T-1: Meta cobertura rutas ──────────────────────────────
                $db->query("SELECT valor FROM configuracion_sistema WHERE clave = 'meta_rutas_anio' LIMIT 1");
                $metaRutas = $db->single();

                $db->query("SELECT COUNT(*) as total FROM rutas
                            WHERE is_active = TRUE
                              AND EXTRACT(YEAR FROM created_at) = :anio");
                $db->bind(':anio', $anioActual);
                $rutasAnio = $db->single();

                // ── F-META: Meta anual de formación ────────────────────────
                $db->query("SELECT valor FROM configuracion_sistema WHERE clave = 'meta_talleres_anio' LIMIT 1");
                $metaTalleres = $db->single();

                $db->query("SELECT COUNT(*) AS total FROM talleres
                            WHERE is_active = TRUE AND estado = 'Finalizado'
                              AND EXTRACT(YEAR FROM fecha_inicio) = :anio");
                $db->bind(':anio', $anioActual);
                $talleresAnio = $db->single();

                // ── T-DEMO: Demografía de participantes en rutas ────────────
                $db->query("SELECT
                                COUNT(CASE WHEN pr.id_persona IS NOT NULL AND p.genero = 'F' THEN 1 END) AS mujeres,
                                COUNT(CASE WHEN pr.id_persona IS NOT NULL AND p.genero = 'M' THEN 1 END) AS hombres,
                                COUNT(CASE WHEN pr.id_persona IS NULL AND pr.genero_libre = 'F'  THEN 1 END) AS ninas,
                                COUNT(CASE WHEN pr.id_persona IS NULL AND pr.genero_libre = 'M'  THEN 1 END) AS ninos,
                                COUNT(*) AS total
                            FROM participantes_ruta pr
                            LEFT JOIN personas p ON pr.id_persona = p.id
                            WHERE pr.is_active = TRUE
                              AND EXISTS (
                                  SELECT 1 FROM rutas r
                                  WHERE r.id = pr.id_ruta AND r.is_active = TRUE
                                    AND EXTRACT(YEAR FROM COALESCE(r.fecha_visita, r.created_at)) = :anio
                              )");
                $db->bind(':anio', $anioActual);
                $demografiaRutas = $db->single();
            } catch (Exception $ignored) {
                $metaTalleres    = null;
                $talleresAnio    = null;
                $demografiaRutas = null;
            }

            // ── PROP-F01: Tasa de ocupación de actividades (año actual) ──────────────
            $db->query("SELECT
                            COALESCE(SUM(sub.inscritos), 0)           AS total_inscritos,
                            COALESCE(SUM(COALESCE(t.cupo_maximo, 0)), 0) AS total_cupos
                        FROM talleres t
                        LEFT JOIN (
                            SELECT id_taller, COUNT(*) AS inscritos
                            FROM participantes_taller WHERE is_active = TRUE GROUP BY id_taller
                        ) sub ON sub.id_taller = t.id
                        WHERE t.is_active = TRUE AND t.estado <> 'Cancelado'
                          AND t.cupo_maximo > 0
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio");
            $db->bind(':anio', $anioActual);
            $kpiOcupacion = $db->single();

            // ── PROP-F02 + PROP-F05: Tasas de finalización y cancelación ─────────────
            $db->query("SELECT
                            COUNT(*) AS total,
                            COUNT(CASE WHEN estado = 'Finalizado' THEN 1 END) AS finalizadas,
                            COUNT(CASE WHEN estado = 'Cancelado'  THEN 1 END) AS canceladas
                        FROM talleres
                        WHERE is_active = TRUE
                          AND EXTRACT(YEAR FROM fecha_inicio) = :anio");
            $db->bind(':anio', $anioActual);
            $kpiEficienciaActs = $db->single();

            // ── PROP-I01: Tasa de depreciación operativa del patrimonio ───────────────
            $db->query("SELECT
                            COUNT(*) AS total,
                            COUNT(CASE WHEN condicion IN ('Dañado','En Reparación') THEN 1 END) AS deteriorados
                        FROM inventario WHERE is_active = TRUE");
            $kpiDepreciacion = $db->single();

            // ── PROP-P01: Distribución por tipo de contrato ───────────────────────────
            $db->query("SELECT
                            COALESCE(NULLIF(TRIM(tipo_contrato), ''), 'Sin especificar') AS tipo_contrato,
                            COUNT(*) AS total
                        FROM empleados WHERE is_active = TRUE
                        GROUP BY COALESCE(NULLIF(TRIM(tipo_contrato), ''), 'Sin especificar')
                        ORDER BY total DESC");
            $empPorContrato = $db->resultSet();

            $data = [
                'titulo'                => 'Indicadores de Gestión',
                'anioActual'            => $anioActual,
                // KPIs resumen
                'kpiEmpleados'          => (int)($kpiEmpleados->total          ?? 0),
                'kpiVisitasHoy'         => (int)($kpiVisitasHoy->total         ?? 0),
                'kpiActividadesActivas' => (int)($kpiActividadesActivas->total ?? 0),
                'kpiFormadosAnio'       => (int)($kpiFormadosAnio->total       ?? 0),
                'kpiRutasActivas'       => (int)($kpiRutasActivas->total       ?? 0),
                'kpiPasantesEnCurso'    => (int)($kpiPasantesEnCurso->total    ?? 0),
                'kpiBienesActivos'      => (int)($kpiBienesActivos->total      ?? 0),
                'kpiBienesAlerta'       => (int)($kpiBienesAlerta->total       ?? 0),
                // Secciones existentes
                'empPorDepto'           => $empPorDepto,
                'asistenciaPorMes'      => $asistenciaPorMes,
                'talleresPorMes'        => $talleresPorMes,
                'talleresPorTipo'       => $talleresPorTipo,
                'participantesTipo'     => $participantesTipo,
                'visitasPorDia'         => $visitasPorDia,
                'visitasPorMotivo'      => $visitasPorMotivo,
                'invPorCat'             => $invPorCat,
                'invPorCondicion'       => $invPorCondicion,
                // KPIs nuevos — Formación
                'demografiaFormacion'   => $demografiaFormacion,
                'coberturaTerrForma'    => $coberturaTerrForma,
                'municipiosCubiertos'   => $municipiosCubiertos,
                'tipoEntidad'           => $tipoEntidad,
                'capacitadores'         => $capacitadores,
                // KPIs nuevos — Turismo
                'rutasPorTipo'          => $rutasPorTipo,
                'institucionesRutas'    => $institucionesRutas,
                'metaRutas'             => (int)($metaRutas->valor   ?? 0),
                'rutasAnio'             => (int)($rutasAnio->total  ?? 0),
                'metaTalleres'          => (int)($metaTalleres->valor ?? 0),
                'talleresAnio'          => (int)($talleresAnio->total ?? 0),
                'demografiaRutas'       => $demografiaRutas   ?? null,
                // Indicadores de eficiencia operativa
                'kpiOcupacion'          => $kpiOcupacion,
                'kpiEficienciaActs'     => $kpiEficienciaActs,
                'kpiDepreciacion'       => $kpiDepreciacion,
                'empPorContrato'        => $empPorContrato,
            ];
            $this->view('reportes/indicadores', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al cargar los indicadores: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    // =========================================================================
    // Reporte de Bienes Dados de Baja
    // =========================================================================
    public function bajasInventario() {
        $this->requireRoles([1, 4]);
        try {
            $fi        = trim($_GET['fecha_inicio'] ?? '');
            $ff        = trim($_GET['fecha_fin']    ?? '');
            $categoria = trim($_GET['categoria']    ?? '');

            $db    = new Database();
            $where = "i.is_active = FALSE AND i.deleted_at IS NOT NULL";
            if ($fi)        $where .= " AND i.deleted_at >= :fi";
            if ($ff)        $where .= " AND i.deleted_at < :ff::date + INTERVAL '1 day'";
            if ($categoria) $where .= " AND c.nombre ILIKE :categoria";

            $db->query("SELECT i.codigo_bn, i.nombre, i.condicion, i.marca, i.modelo, i.serial,
                               c.nombre AS categoria,
                               u.nombre AS ubicacion,
                               i.deleted_at,
                               pu.username AS eliminado_por
                        FROM inventario i
                        LEFT JOIN categorias c  ON i.id_categoria = c.id
                        LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                        LEFT JOIN usuarios pu   ON i.deleted_by   = pu.id
                        WHERE {$where}
                        ORDER BY i.deleted_at DESC");
            if ($fi)        $db->bind(':fi', $fi);
            if ($ff)        $db->bind(':ff', $ff);
            if ($categoria) $db->bind(':categoria', '%' . $categoria . '%');
            $bajas = $db->resultSet();

            $db->query("SELECT COUNT(*) as total FROM inventario WHERE is_active = FALSE AND deleted_at IS NOT NULL");
            $totalHist = $db->single();

            $db->query("SELECT COUNT(*) as este_anio FROM inventario
                        WHERE is_active = FALSE AND deleted_at IS NOT NULL
                          AND EXTRACT(YEAR FROM deleted_at) = EXTRACT(YEAR FROM CURRENT_DATE)");
            $bajasAnio = $db->single();

            $data = [
                'titulo'       => 'Bienes Dados de Baja',
                'bajas'        => $bajas,
                'total_hist'   => (int)($totalHist->total    ?? 0),
                'bajas_anio'   => (int)($bajasAnio->este_anio ?? 0),
                'fecha_inicio' => $fi,
                'fecha_fin'    => $ff,
                'filtro_cat'   => $categoria,
            ];
            $this->view('reportes/bajas_inventario', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al cargar bajas: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarBajasInventarioCsv() {
        $this->requireRoles([1, 4]);
        try {
            $fi        = trim($_GET['fecha_inicio'] ?? '');
            $ff        = trim($_GET['fecha_fin']    ?? '');
            $categoria = trim($_GET['categoria']    ?? '');

            $db    = new Database();
            $where = "i.is_active = FALSE AND i.deleted_at IS NOT NULL";
            if ($fi)        $where .= " AND i.deleted_at >= :fi";
            if ($ff)        $where .= " AND i.deleted_at < :ff::date + INTERVAL '1 day'";
            if ($categoria) $where .= " AND c.nombre ILIKE :categoria";

            $db->query("SELECT i.codigo_bn, i.nombre, i.condicion, i.marca, i.modelo, i.serial,
                               c.nombre AS categoria, u.nombre AS ubicacion,
                               i.deleted_at, pu.username AS eliminado_por
                        FROM inventario i
                        LEFT JOIN categorias c ON i.id_categoria = c.id
                        LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                        LEFT JOIN usuarios pu  ON i.deleted_by   = pu.id
                        WHERE {$where} ORDER BY i.deleted_at DESC");
            if ($fi)        $db->bind(':fi', $fi);
            if ($ff)        $db->bind(':ff', $ff);
            if ($categoria) $db->bind(':categoria', '%' . $categoria . '%');
            $bajas   = $db->resultSet();

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición', 'Marca', 'Modelo', 'Serial', 'Fecha Baja', 'Dado de baja por'];
            $rows    = [];
            foreach ($bajas as $b) {
                $rows[] = [
                    $b->codigo_bn    ?? 'S/N',
                    $b->nombre,
                    $b->categoria    ?? '-',
                    $b->ubicacion    ?? '-',
                    $b->condicion,
                    $b->marca        ?? '-',
                    $b->modelo       ?? '-',
                    $b->serial       ?? '-',
                    $b->deleted_at ? date('d/m/Y H:i', strtotime($b->deleted_at)) : '-',
                    $b->eliminado_por ?? '-',
                ];
            }
            $this->exportCsv('bajas_inventario', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarBajasInventarioPdf() {
        $this->requireRoles([1, 4]);
        try {
            $fi        = trim($_GET['fecha_inicio'] ?? '');
            $ff        = trim($_GET['fecha_fin']    ?? '');
            $categoria = trim($_GET['categoria']    ?? '');

            $db    = new Database();
            $where = "i.is_active = FALSE AND i.deleted_at IS NOT NULL";
            if ($fi)        $where .= " AND i.deleted_at >= :fi";
            if ($ff)        $where .= " AND i.deleted_at < :ff::date + INTERVAL '1 day'";
            if ($categoria) $where .= " AND c.nombre ILIKE :categoria";

            $db->query("SELECT i.codigo_bn, i.nombre, i.condicion, i.marca, i.modelo,
                               c.nombre AS categoria, u.nombre AS ubicacion,
                               i.deleted_at, pu.username AS eliminado_por
                        FROM inventario i
                        LEFT JOIN categorias c ON i.id_categoria = c.id
                        LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                        LEFT JOIN usuarios pu  ON i.deleted_by   = pu.id
                        WHERE {$where} ORDER BY i.deleted_at DESC");
            if ($fi)        $db->bind(':fi', $fi);
            if ($ff)        $db->bind(':ff', $ff);
            if ($categoria) $db->bind(':categoria', '%' . $categoria . '%');
            $bajas = $db->resultSet();

            $db->query("SELECT COUNT(*) as total FROM inventario WHERE is_active = FALSE AND deleted_at IS NOT NULL");
            $totalHist = $db->single();

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición', 'Fecha Baja', 'Dado de baja por'];
            $rows    = [];
            foreach ($bajas as $b) {
                $rows[] = [
                    $b->codigo_bn    ?? 'S/N',
                    $b->nombre,
                    $b->categoria    ?? '-',
                    $b->ubicacion    ?? '-',
                    $b->condicion,
                    $b->deleted_at ? date('d/m/Y', strtotime($b->deleted_at)) : '-',
                    $b->eliminado_por ?? '-',
                ];
            }
            $kpis = [
                'Total Histórico' => (int)($totalHist->total ?? 0),
                'Filtrados'       => count($bajas),
                'Período'         => ($fi && $ff) ? "$fi a $ff" : 'Todo el historial',
            ];
            $this->exportPdf("Bienes Dados de Baja", "IMATUR — Control Patrimonial — Desincorporaciones", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    // =========================================================================
    // HELPERS DE EXPORTACIÓN
    // =========================================================================

    private function exportCsv($filename, $headers, $rows) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

        fputcsv($output, ['REPÚBLICA BOLIVARIANA DE VENEZUELA'], ';');
        fputcsv($output, ['ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE'], ';');
        fputcsv($output, ['Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)  —  RIF. G-20008498-7'], ';');
        fputcsv($output, ['Cumaná, Estado Sucre'], ';');
        fputcsv($output, [''], ';');
        fputcsv($output, ['Reporte: ' . $filename], ';');
        fputcsv($output, ['Generado por: ' . ($_SESSION['user_username'] ?? 'Sistema') . '    Fecha: ' . date('d/m/Y H:i')], ';');
        fputcsv($output, [''], ';');

        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        fputcsv($output, [''], ';');
        fputcsv($output, ['Total de registros: ' . count($rows)], ';');

        fclose($output);
        exit;
    }

    private function exportPdf($titulo, $subtitulo, $headers, $rows, $kpis = []) {
        $data = [
            'titulo'    => $titulo,
            'subtitulo' => $subtitulo,
            'headers'   => $headers,
            'rows'      => $rows,
            'kpis'      => $kpis,
        ];
        $this->view('reportes/pdf_template', $data);
    }
}
