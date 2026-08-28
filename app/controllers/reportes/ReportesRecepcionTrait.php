<?php
/**
 * Recepción — visitantes, visitas y sus estadísticas
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesRecepcionTrait {

    // =========================================================================
    // Reporte de Visitantes y Visitas
    // =========================================================================
    public function visitantes() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryVisitantes();
            $stats     = $this->statsVisitantes();

            $data = [
                'titulo'        => 'Reporte de Visitantes',
                'registros'     => $registros,
                'stats'         => $stats,
                'fecha_inicio'  => $_GET['fecha_inicio'] ?? date('Y-m-01'),
                'fecha_fin'     => $_GET['fecha_fin']    ?? date('Y-m-d'),
                'filtro_motivo' => $_GET['motivo']       ?? '',
                'filtro_cedula' => $_GET['cedula']       ?? '',
                'filtro_genero' => $_GET['genero']       ?? '',
                'filtro_buscar' => $_GET['buscar']       ?? '',
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
            $headers   = ['Fecha', 'Hora Entrada', 'Hora Salida', 'Cédula', 'Nombre', 'Apellido', 'Género', 'Teléfono', 'Correo', 'Procedencia', 'Atendido por', 'Motivo', 'Observaciones'];
            $rows      = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->fecha ?? date('Y-m-d', strtotime($r->hora_entrada)),
                    date('H:i', strtotime($r->hora_entrada)),
                    $r->hora_salida ? date('H:i', strtotime($r->hora_salida)) : 'En curso',
                    $r->cedula         ?? '',
                    $r->nombre         ?? '',
                    $r->apellido       ?? '',
                    match($r->genero ?? '') { 'M' => 'Masculino', 'F' => 'Femenino', default => '' },
                    $r->telefono       ?? '',
                    $r->correo         ?? '',
                    $r->procedencia    ?? '',
                    trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? '')),
                    $r->motivo         ?? '',
                    $r->observaciones  ?? '',
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

            $headers = ['Fecha', 'Entrada', 'Salida', 'Cédula', 'Nombre y Apellido', 'Género', 'Teléfono', 'Procedencia', 'Atendido por', 'Motivo'];
            $rows    = [];
            foreach ($registros as $r) {
                $rows[] = [
                    $r->fecha ?? date('d/m/Y', strtotime($r->hora_entrada)),
                    date('H:i', strtotime($r->hora_entrada)),
                    $r->hora_salida ? date('H:i', strtotime($r->hora_salida)) : 'En curso',
                    $r->cedula ?? '—',
                    trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                    match($r->genero ?? '') { 'M' => 'M', 'F' => 'F', default => '—' },
                    $r->telefono    ?? '—',
                    $r->procedencia ?? '—',
                    trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? '')) ?: '—',
                    $r->motivo      ?? '—',
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
        $db     = new Database();
        $fi     = $_GET['fecha_inicio'] ?? date('Y-m-01');
        $ff     = $_GET['fecha_fin']    ?? date('Y-m-d');
        $motivo = trim($_GET['motivo']  ?? '');
        $cedula = trim($_GET['cedula']  ?? '');
        $genero = trim($_GET['genero']  ?? '');
        $buscar = trim($_GET['buscar']  ?? '');

        $where = "v.is_active = TRUE AND DATE(v.hora_entrada) BETWEEN :fi AND :ff";
        if ($motivo !== '') $where .= " AND v.motivo ILIKE :motivo";
        if ($cedula !== '') $where .= " AND COALESCE(pe2.cedula, vis.cedula) ILIKE :cedula";
        if ($genero !== '') $where .= " AND COALESCE(pe2.genero, vis.genero) = :genero";
        if ($buscar !== '') $where .= " AND (COALESCE(pe2.nombre, vis.nombre) ILIKE :buscar
                                        OR COALESCE(pe2.apellido, vis.apellido) ILIKE :buscar
                                        OR COALESCE(pe2.cedula, vis.cedula) ILIKE :buscar)";

        $db->query("SELECT v.hora_entrada, v.hora_salida, v.motivo, v.observaciones,
                           DATE(v.hora_entrada) AS fecha,
                           COALESCE(pe2.cedula,    vis.cedula)    AS cedula,
                           COALESCE(pe2.nombre,    vis.nombre)    AS nombre,
                           COALESCE(pe2.apellido,  vis.apellido)  AS apellido,
                           COALESCE(pe2.telefono,  vis.telefono)  AS telefono,
                           COALESCE(pe2.correo,    vis.correo)    AS correo,
                           COALESCE(pe2.genero,    vis.genero)    AS genero,
                           vis.procedencia,
                           ep.nombre AS emp_nombre, ep.apellido AS emp_apellido
                    FROM visitas v
                    INNER JOIN visitantes vis ON v.id_visitante = vis.id
                    LEFT  JOIN personas pe2  ON vis.id_persona  = pe2.id
                    LEFT  JOIN empleados emp ON v.id_empleado   = emp.id
                    LEFT  JOIN personas ep   ON emp.id_persona  = ep.id
                    WHERE {$where}
                    ORDER BY v.hora_entrada DESC");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        if ($motivo !== '') $db->bind(':motivo', '%' . $motivo . '%');
        if ($cedula !== '') $db->bind(':cedula', '%' . $cedula . '%');
        if ($genero !== '') $db->bind(':genero', $genero);
        if ($buscar !== '') $db->bind(':buscar', '%' . $buscar . '%');
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
    // Recepción — Estadísticas de visitas (BVIS-05)
    // =========================================================================
    public function estadisticasVisitas() {
        $this->requireRoles([1, 2]);
        try {
            $fi = $_GET['fecha_inicio'] ?? date('Y-01-01');
            $ff = $_GET['fecha_fin']    ?? date('Y-m-d');
            $db = new Database();

            // Resumen del rango + situación actual
            $db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT id_visitante) AS unicos
                        FROM visitas WHERE is_active = TRUE
                          AND DATE(hora_entrada) BETWEEN :fi AND :ff");
            $db->bind(':fi', $fi); $db->bind(':ff', $ff);
            $st = $db->single();

            $db->query("SELECT COUNT(*) AS hoy,
                               COUNT(*) FILTER (WHERE hora_salida IS NULL) AS activas
                        FROM visitas WHERE is_active = TRUE AND DATE(hora_entrada) = CURRENT_DATE");
            $hoy = $db->single();

            $meses = $this->queryVisitasPorMes($fi, $ff);
            $filas = [];
            foreach ($meses as $m) {
                $filas[] = [
                    ['raw' => '<span class="cell-strong">' . htmlspecialchars(self::fmtMesLargo($m->mes)) . '</span>'],
                    (string)(int)$m->visitas,
                    (string)(int)$m->unicos,
                ];
            }
            $this->renderReporte([
                'eyebrow' => 'Recepción · Estadísticas', 'titulo' => 'Estadísticas de Visitas',
                'subtitulo' => 'Afluencia por mes, visitantes únicos y situación del día.',
                'resumen' => [
                    'Visitas (rango)'    => (int)($st->total ?? 0),
                    'Visitantes únicos'  => (int)($st->unicos ?? 0),
                    'Visitas hoy'        => (int)($hoy->hoy ?? 0),
                    'Activas ahora'      => (int)($hoy->activas ?? 0),
                ],
                'columnas' => ['Mes', 'Visitas', 'Visitantes únicos'],
                'filas' => $filas,
                'accion' => URL_ROOT . '/reportes/estadisticasVisitas',
                'filtros' => [
                    ['name' => 'fecha_inicio', 'label' => 'Desde', 'type' => 'date', 'value' => $fi],
                    ['name' => 'fecha_fin', 'label' => 'Hasta', 'type' => 'date', 'value' => $ff],
                ],
                'export_url' => URL_ROOT . '/reportes/exportarEstadisticasVisitasCsv?' . $this->qsFiltros(),
                'vacio' => 'No hay visitas registradas en el período.',
            ]);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar las estadísticas de visitas: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarEstadisticasVisitasCsv() {
        $this->requireRoles([1, 2]);
        $fi = $_GET['fecha_inicio'] ?? date('Y-01-01');
        $ff = $_GET['fecha_fin']    ?? date('Y-m-d');
        $rows = [];
        foreach ($this->queryVisitasPorMes($fi, $ff) as $m) {
            $rows[] = [self::fmtMesLargo($m->mes), (int)$m->visitas, (int)$m->unicos];
        }
        $this->exportCsv('estadisticas_visitas', ['Mes', 'Visitas', 'Visitantes únicos'], $rows);
    }

    private function queryVisitasPorMes(string $fi, string $ff) {
        $db = new Database();
        $db->query("SELECT TO_CHAR(hora_entrada, 'YYYY-MM') AS mes,
                           COUNT(*) AS visitas, COUNT(DISTINCT id_visitante) AS unicos
                    FROM visitas
                    WHERE is_active = TRUE AND DATE(hora_entrada) BETWEEN :fi AND :ff
                    GROUP BY mes ORDER BY mes DESC");
        $db->bind(':fi', $fi); $db->bind(':ff', $ff);
        return $db->resultSet();
    }

    /** Años con datos en una tabla/columna fecha, para los selects de filtro. */
    private function aniosDisponibles(string $tabla, string $colFecha, int $actual, string $extra = ''): array {
        $db = new Database();
        $w = "is_active = TRUE" . ($extra ? " AND {$extra}" : '');
        $db->query("SELECT DISTINCT EXTRACT(YEAR FROM {$colFecha})::int AS a FROM {$tabla} WHERE {$w} AND {$colFecha} IS NOT NULL ORDER BY a DESC");
        $opts = [];
        foreach ($db->resultSet() as $r) $opts[(string)$r->a] = (string)$r->a;
        if (!isset($opts[(string)$actual])) $opts[(string)$actual] = (string)$actual;
        return $opts;
    }

    /** "2026-06" → "Junio 2026". */
    private static function fmtMesLargo(string $ym): string {
        $m = ['', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
        $p = explode('-', $ym);
        if (count($p) < 2) return $ym;
        return ($m[(int)$p[1]] ?? '?') . ' ' . $p[0];
    }
}
