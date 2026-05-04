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
        $registros = $this->queryAsistencia();
        $stats = $this->statsAsistencia();

        $data = [
            'titulo' => 'Reporte de Asistencia',
            'registros' => $registros,
            'stats' => $stats,
            'fecha_inicio' => $_GET['fecha_inicio'] ?? date('Y-m-01'),
            'fecha_fin' => $_GET['fecha_fin'] ?? date('Y-m-d')
        ];
        $this->view('reportes/asistencia', $data);
    }

    public function exportarAsistenciaCsv() {
        $this->requireRoles([1, 2]);
        $registros = $this->queryAsistencia();
        $headers = ['Fecha', 'Nombre', 'Apellido', 'Cédula', 'Departamento', 'Entrada', 'Salida', 'Observación'];
        $rows = [];
        foreach ($registros as $r) {
            $rows[] = [$r->fecha, $r->nombre, $r->apellido, $r->cedula, $r->departamento, $r->hora_entrada, $r->hora_salida ?? '', $r->observacion ?? ''];
        }
        $this->exportCsv('reporte_asistencia', $headers, $rows);
    }

    public function exportarAsistenciaPdf() {
        $this->requireRoles([1, 2]);
        $registros = $this->queryAsistencia();
        $stats = $this->statsAsistencia();
        $fi = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $ff = $_GET['fecha_fin'] ?? date('Y-m-d');

        $headers = ['Fecha', 'Empleado', 'Cédula', 'Departamento', 'Entrada', 'Salida'];
        $rows = [];
        foreach ($registros as $r) {
            $rows[] = [$r->fecha, $r->nombre . ' ' . $r->apellido, $r->cedula, $r->departamento, $r->hora_entrada, $r->hora_salida ?? '-'];
        }
        $kpis = [
            'Total Registros' => $stats->total,
            'Empleados Únicos' => $stats->empleados_unicos,
            'Período' => "$fi a $ff"
        ];
        $this->exportPdf("Reporte de Asistencia", "Período: $fi — $ff", $headers, $rows, $kpis);
    }

    private function queryAsistencia() {
        $db = new Database();
        $fi = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $ff = $_GET['fecha_fin'] ?? date('Y-m-d');

        $db->query("SELECT a.fecha, a.hora_entrada, a.hora_salida, a.observacion,
                           p.nombre, p.apellido, p.cedula, d.nombre as departamento
                    FROM asistencias a
                    INNER JOIN empleados e ON a.id_empleado = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE a.is_active = TRUE 
                      AND a.fecha BETWEEN :fecha_inicio AND :fecha_fin
                    ORDER BY a.fecha DESC, p.apellido ASC");
        $db->bind(':fecha_inicio', $fi);
        $db->bind(':fecha_fin', $ff);
        return $db->resultSet();
    }

    private function statsAsistencia() {
        $db = new Database();
        $fi = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $ff = $_GET['fecha_fin'] ?? date('Y-m-d');
        $db->query("SELECT COUNT(*) as total, COUNT(DISTINCT id_empleado) as empleados_unicos
                    FROM asistencias WHERE is_active = TRUE AND fecha BETWEEN :fi AND :ff");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        return $db->single();
    }

    // =========================================================================
    // RF28: Reporte de Talleres
    // =========================================================================
    public function talleres() {
        $this->requireRoles([1, 3]);
        $talleres = $this->queryTalleres();
        $stats = $this->statsTalleres();

        $data = [
            'titulo' => 'Reporte de Talleres y Formación',
            'talleres' => $talleres,
            'stats' => $stats,
            'estado_filtro' => $_GET['estado'] ?? ''
        ];
        $this->view('reportes/talleres', $data);
    }

    public function exportarTalleresCsv() {
        $this->requireRoles([1, 3]);
        $talleres = $this->queryTalleres();
        $headers = ['Taller', 'Facilitador', 'Sede', 'Fecha Inicio', 'Estado', 'Inscritos', 'Cupo Máximo'];
        $rows = [];
        foreach ($talleres as $t) {
            $rows[] = [$t->nombre, $t->facilitador_nombre . ' ' . $t->facilitador_apellido, $t->sede ?? 'Sin sede', $t->fecha_inicio, $t->estado, $t->total_inscritos, $t->cupo_maximo];
        }
        $this->exportCsv('reporte_talleres', $headers, $rows);
    }

    public function exportarTalleresPdf() {
        $this->requireRoles([1, 3]);
        $talleres = $this->queryTalleres();
        $stats = $this->statsTalleres();

        $headers = ['Taller', 'Facilitador', 'Sede', 'Fecha', 'Estado', 'Inscritos/Cupo'];
        $rows = [];
        foreach ($talleres as $t) {
            $rows[] = [$t->nombre, $t->facilitador_nombre . ' ' . $t->facilitador_apellido, $t->sede ?? '-', $t->fecha_inicio, $t->estado, $t->total_inscritos . '/' . $t->cupo_maximo];
        }
        $kpis = [
            'Total Talleres' => $stats->total_talleres,
            'Finalizados' => $stats->finalizados,
            'En Curso' => $stats->en_curso,
            'Participantes Totales' => $stats->total_participantes
        ];
        $this->exportPdf("Reporte de Talleres y Formación", "IMATUR — Formación Comunitaria", $headers, $rows, $kpis);
    }

    private function queryTalleres() {
        $db = new Database();
        $estado = $_GET['estado'] ?? '';
        $sql = "SELECT t.*, uf.nombre as sede, 
                       p.nombre as facilitador_nombre, p.apellido as facilitador_apellido,
                       inf.mujeres, inf.hombres, inf.ninas, inf.ninos, inf.total_atendidas,
                       (SELECT COUNT(*) FROM participantes_taller pt WHERE pt.id_taller = t.id) as total_inscritos
                FROM talleres t
                LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                LEFT JOIN taller_informes inf ON t.id = inf.id_taller
                INNER JOIN empleados e ON t.id_facilitador = e.id
                INNER JOIN personas p ON e.id_persona = p.id
                WHERE t.is_active = TRUE";
        if (!empty($estado)) $sql .= " AND t.estado = :estado";
        $sql .= " ORDER BY t.fecha_inicio DESC";
        $db->query($sql);
        if (!empty($estado)) $db->bind(':estado', $estado);
        return $db->resultSet();
    }

    private function statsTalleres() {
        $db = new Database();
        $db->query("SELECT COUNT(*) as total_talleres,
                        COUNT(CASE WHEN estado = 'Finalizado' THEN 1 END) as finalizados,
                        COUNT(CASE WHEN estado = 'En Curso' THEN 1 END) as en_curso,
                        COUNT(CASE WHEN estado = 'Programado' THEN 1 END) as programados,
                        (SELECT COUNT(*) FROM participantes_taller) as total_participantes
                    FROM talleres WHERE is_active = TRUE");
        return $db->single();
    }

    // =========================================================================
    // RF29: Reporte de Rutas Turísticas
    // =========================================================================
    public function rutas() {
        $this->requireRoles([1, 3]);
        $rutas = $this->queryRutas();
        $stats = $this->statsRutas();

        $data = [
            'titulo' => 'Reporte de Rutas Turísticas',
            'rutas' => $rutas,
            'stats' => $stats
        ];
        $this->view('reportes/rutas', $data);
    }

    public function exportarRutasCsv() {
        $this->requireRoles([1, 3]);
        $rutas = $this->queryRutas();
        $headers = ['Ruta', 'Dificultad', 'Duración', 'Estado', 'Puntos', 'Actividades', 'Equipos'];
        $rows = [];
        foreach ($rutas as $r) {
            $rows[] = [$r->nombre, $r->nivel_dificultad, $r->duracion_estimada ?? '-', $r->estado, $r->total_puntos, $r->total_actividades, $r->total_equipos];
        }
        $this->exportCsv('reporte_rutas', $headers, $rows);
    }

    public function exportarRutasPdf() {
        $this->requireRoles([1, 3]);
        $rutas = $this->queryRutas();
        $stats = $this->statsRutas();

        $headers = ['Ruta', 'Dificultad', 'Duración', 'Estado', 'Puntos', 'Actividades', 'Equipos'];
        $rows = [];
        foreach ($rutas as $r) {
            $rows[] = [$r->nombre, $r->nivel_dificultad, $r->duracion_estimada ?? '-', $r->estado, $r->total_puntos, $r->total_actividades, $r->total_equipos];
        }
        $kpis = [
            'Total Rutas' => $stats->total_rutas,
            'Activas' => $stats->activas,
            'Inactivas' => $stats->inactivas,
            'En Mantenimiento' => $stats->mantenimiento
        ];
        $this->exportPdf("Reporte de Rutas Turísticas", "IMATUR — Gestión Turística", $headers, $rows, $kpis);
    }

    private function queryRutas() {
        $db = new Database();
        $db->query("SELECT r.*,
                           (SELECT COUNT(*) FROM puntos_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) as total_puntos,
                           (SELECT COUNT(*) FROM actividades_ruta ar WHERE ar.id_ruta = r.id AND ar.is_active = TRUE) as total_actividades,
                           (SELECT COUNT(*) FROM ruta_inventario ri WHERE ri.id_ruta = r.id) as total_equipos
                    FROM rutas r WHERE r.is_active = TRUE ORDER BY r.created_at DESC");
        return $db->resultSet();
    }

    private function statsRutas() {
        $db = new Database();
        $db->query("SELECT COUNT(*) as total_rutas,
                        COUNT(CASE WHEN estado = 'Activa' THEN 1 END) as activas,
                        COUNT(CASE WHEN estado = 'Inactiva' THEN 1 END) as inactivas,
                        COUNT(CASE WHEN estado = 'En Mantenimiento' THEN 1 END) as mantenimiento
                    FROM rutas WHERE is_active = TRUE");
        return $db->single();
    }

    public function exportarParticipantesCsv($id_taller) {
        $this->requireRoles([1, 3]);
        $db = new Database();
        $db->query("SELECT p.cedula, p.nombre, p.apellido, p.telefono, pt.asistio
                    FROM participantes_taller pt
                    INNER JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id_taller = :id_taller");
        $db->bind(':id_taller', $id_taller);
        $participantes = $db->resultSet();

        $taller = $db->query("SELECT nombre FROM talleres WHERE id = :id_taller");
        $db->bind(':id_taller', $id_taller);
        $t = $db->single();

        $headers = ['Cédula', 'Nombre', 'Apellido', 'Teléfono', 'Asistió'];
        $rows = [];
        foreach($participantes as $p) {
            $rows[] = [$p->cedula, $p->nombre, $p->apellido, $p->telefono, $p->asistio ? 'Sí' : 'No'];
        }

        $this->exportCsv("Inscritos_" . str_replace(' ', '_', $t->nombre), $headers, $rows);
    }

    // =========================================================================
    // DOSSIER INTEGRAL DE TALLER (PAGINA CONTINUA)
    // =========================================================================

    public function dossier($id) {
        $this->requireRoles([1, 3]);
        $db = new Database();
        // 1. Info básica y facilitador
        $db->query("SELECT t.*, uf.nombre as sede, 
                           p.nombre as fac_nom, p.apellido as fac_ape, e.nro_expediente
                    FROM talleres t
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    INNER JOIN empleados e ON t.id_facilitador = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE t.id = :id");
        $db->bind(':id', $id);
        $taller = $db->single();

        if (!$taller) { header('Location: ' . URL_ROOT . '/reportes/talleres'); exit; }

        // 2. Informe demográfico
        $db->query("SELECT * FROM taller_informes WHERE id_taller = :id");
        $db->bind(':id', $id);
        $informe = $db->single();

        // 3. Participantes
        $db->query("SELECT p.cedula, p.nombre, p.apellido, pt.asistio
                    FROM participantes_taller pt
                    INNER JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id_taller = :id ORDER BY p.apellido ASC");
        $db->bind(':id', $id);
        $participantes = $db->resultSet();

        $data = [
            'titulo' => 'Dossier de Taller',
            'taller' => $taller,
            'informe' => $informe,
            'participantes' => $participantes
        ];

        $this->view('reportes/taller_detalle', $data);
    }

    public function exportarDossierCsv($id) {
        $this->requireRoles([1, 3]);
        $db = new Database();
        $db->query("SELECT t.*, p.nombre || ' ' || p.apellido as facilitador, uf.nombre as sede
                    FROM talleres t
                    INNER JOIN empleados e ON t.id_facilitador = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    WHERE t.id = :id");
        $db->bind(':id', $id);
        $t = $db->single();

        $db->query("SELECT * FROM taller_informes WHERE id_taller = :id");
        $db->bind(':id', $id);
        $inf = $db->single();

        $db->query("SELECT p.cedula, p.nombre || ' ' || p.apellido as nombre, pt.asistio
                    FROM participantes_taller pt
                    INNER JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id_taller = :id");
        $db->bind(':id', $id);
        $participantes = $db->resultSet();

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="Dossier_Taller_' . time() . '.csv"');
        
        $output = fopen('php://output', 'w');
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM
        
        // Bloque 1: Generalidades
        fputcsv($output, ['DOSSIER INTEGRAL DE ACTIVIDAD - IMATUR'], ';');
        fputcsv($output, ['Taller:', $t->nombre], ';');
        fputcsv($output, ['Facilitador:', $t->facilitador], ';');
        fputcsv($output, ['Lugar:', $t->sede ?: 'No especificada'], ';');
        fputcsv($output, ['Fecha:', $t->fecha_inicio], ';');
        fputcsv($output, ['Estado:', $t->estado], ';');
        fputcsv($output, [], ';');

        // Bloque 2: Estadística Demográfica
        fputcsv($output, ['RESUMEN DEMOGRÁFICO'], ';');
        fputcsv($output, ['Mujeres', 'Hombres', 'Niñas', 'Niños', 'Total Atendidos'], ';');
        fputcsv($output, [
            $inf->mujeres ?? 0, 
            $inf->hombres ?? 0, 
            $inf->ninas ?? 0, 
            $inf->ninos ?? 0, 
            $inf->total_atendidas ?? 0
        ], ';');
        fputcsv($output, [], ';');

        // Bloque 3: Lista de Participantes
        fputcsv($output, ['LISTADO DE PERSONAS INSCRITAS'], ';');
        fputcsv($output, ['Cédula', 'Nombre Completo', 'Asistencia'], ';');
        foreach($participantes as $p) {
            fputcsv($output, [$p->cedula, $p->nombre, $p->asistio ? 'Presente' : 'Ausente'], ';');
        }
        
        fclose($output);
        exit;
    }

    // =========================================================================
    // EXPORTACIÓN DE PASANTES
    // =========================================================================

    public function pasantes() {
        $this->requireRoles([1, 3]);
        $db = new Database();
        $db->query("SELECT p.*,
                           pp.cedula, pp.nombre, pp.apellido,
                           pt.nombre AS tutor_nombre, pt.apellido AS tutor_apellido
                    FROM pasantes p
                    INNER JOIN personas pp ON p.id_persona = pp.id
                    LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
                    LEFT  JOIN personas pt  ON e.id_persona = pt.id
                    WHERE p.is_active = TRUE ORDER BY p.fecha_inicio DESC");
        $pasantes = $db->resultSet();

        $data = [
            'titulo' => 'Reporte de Pasantes',
            'pasantes' => $pasantes
        ];
        $this->view('reportes/pasantes', $data);
    }

    public function exportarPasantesCsv() {
        $this->requireRoles([1, 3]);
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
        $rows = [];
        foreach($pasantes as $p) {
            $rows[] = [$p->cedula, $p->nombre, $p->apellido, $p->institucion, $p->carrera, $p->tutor ?? 'N/A', $p->fecha_inicio, $p->fecha_fin, $p->estado];
        }

        $this->exportCsv("Reporte_Pasantes", $headers, $rows);
    }

    public function exportarPasantesPdf() {
        $this->requireRoles([1, 3]);
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
        $rows = [];
        foreach($pasantes as $p) {
            $rows[] = [$p->cedula, $p->nombre . ' ' . $p->apellido, $p->institucion, $p->tutor ?? '-', $p->estado];
        }

        $this->exportPdf("Listado Maestro de Pasantes", "IMATUR — Control de Formación Institucional", $headers, $rows);
    }

    // =========================================================================
    // RF30: Indicadores Generales de Gestión
    // =========================================================================
    public function indicadores() {
        $db = new Database();

        $db->query("SELECT d.nombre as departamento, COUNT(e.id) as total
                    FROM departamentos d LEFT JOIN empleados e ON d.id = e.id_departamento AND e.is_active = TRUE
                    WHERE d.is_active = TRUE GROUP BY d.nombre ORDER BY total DESC");
        $empPorDepto = $db->resultSet();

        $db->query("SELECT c.nombre as categoria, COUNT(i.id) as total
                    FROM categorias c LEFT JOIN inventario i ON c.id = i.id_categoria AND i.is_active = TRUE
                    WHERE c.is_active = TRUE GROUP BY c.nombre ORDER BY total DESC");
        $invPorCat = $db->resultSet();

        $db->query("SELECT condicion, COUNT(*) as total FROM inventario WHERE is_active = TRUE GROUP BY condicion ORDER BY total DESC");
        $invPorCondicion = $db->resultSet();

        $db->query("SELECT TO_CHAR(fecha_inicio, 'YYYY-MM') as mes, COUNT(*) as total
                    FROM talleres WHERE is_active = TRUE AND fecha_inicio >= (CURRENT_DATE - INTERVAL '6 months')
                    GROUP BY mes ORDER BY mes ASC");
        $talleresPorMes = $db->resultSet();

        $data = [
            'titulo' => 'Indicadores de Gestión',
            'empPorDepto' => $empPorDepto,
            'invPorCat' => $invPorCat,
            'invPorCondicion' => $invPorCondicion,
            'talleresPorMes' => $talleresPorMes
        ];
        $this->view('reportes/indicadores', $data);
    }

    // =========================================================================
    // HELPERS DE EXPORTACIÓN
    // =========================================================================

    /**
     * Exportar a CSV (Compatible con Excel y LibreOffice)
     */
    private function exportCsv($filename, $headers, $rows) {
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');
        header('Pragma: no-cache');
        header('Expires: 0');

        $output = fopen('php://output', 'w');
        // BOM UTF-8 para que Excel interprete los acentos correctamente
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
        fputcsv($output, $headers, ';');
        foreach ($rows as $row) {
            fputcsv($output, $row, ';');
        }
        fclose($output);
        exit;
    }

    /**
     * Exportar a PDF (Documento HTML optimizado para impresión directa)
     */
    private function exportPdf($titulo, $subtitulo, $headers, $rows, $kpis = []) {
        $data = [
            'titulo' => $titulo,
            'subtitulo' => $subtitulo,
            'headers' => $headers,
            'rows' => $rows,
            'kpis' => $kpis
        ];
        $this->view('reportes/pdf_template', $data);
    }
}
