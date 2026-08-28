<?php
/**
 * Turismo — rutas, participación y ejecuciones
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesTurismoTrait {

    // =========================================================================
    // Turismo — Participación / ocupación en rutas
    // =========================================================================
    public function participacionRutas() {
        $this->requireRoles([1, 3]);
        $regs = $this->queryParticipacionRutas();
        $filas = []; $totalPart = 0; $sumaOcup = 0; $conCupo = 0;
        foreach ($regs as $r) {
            $cupo = (int)$r->cupo_maximo; $part = (int)$r->participantes;
            $totalPart += $part;
            $pct = $cupo > 0 ? round($part / $cupo * 100) : 0;
            if ($cupo > 0) { $sumaOcup += $pct; $conCupo++; }
            $badge = $pct >= 100 ? 'sig-badge--danger' : ($pct >= 80 ? 'sig-badge--warning' : 'sig-badge--success');
            $filas[] = [
                $r->nombre ?? '—',
                ['raw' => '<span class="sig-badge ' . (Ruta::ESTADO_BADGES[$r->estado ?? ''] ?? 'sig-badge--neutral') . '">' . htmlspecialchars($r->estado ?? '—') . '</span>'],
                !empty($r->fecha_visita) ? date('d/m/Y', strtotime($r->fecha_visita)) : '—',
                (string)$part,
                (string)$cupo,
                ['raw' => '<span class="sig-badge ' . $badge . '">' . $pct . '%</span>'],
            ];
        }
        $ocupProm = $conCupo > 0 ? round($sumaOcup / $conCupo) . '%' : '—';
        $this->renderReporte([
            'eyebrow' => 'Turismo · Impacto', 'titulo' => 'Participación en Rutas',
            'subtitulo' => 'Ocupación por ruta (participantes vs cupo) y estado.',
            'resumen' => ['Rutas' => count($regs), 'Participaciones' => $totalPart, 'Ocupación promedio' => $ocupProm],
            'columnas' => ['Ruta', 'Estado', 'Fecha', 'Participantes', 'Cupo', 'Ocupación'],
            'filas' => $filas,
            'export_url' => URL_ROOT . '/reportes/exportarParticipacionRutasCsv',
            'vacio' => 'No hay rutas registradas.',
        ]);
    }

    public function exportarParticipacionRutasCsv() {
        $this->requireRoles([1, 3]);
        $rows = [];
        foreach ($this->queryParticipacionRutas() as $r) {
            $cupo = (int)$r->cupo_maximo; $part = (int)$r->participantes;
            $rows[] = [$r->nombre, $r->estado, $r->fecha_visita, $part, $cupo, ($cupo > 0 ? round($part / $cupo * 100) : 0) . '%'];
        }
        $this->exportCsv('participacion_rutas', ['Ruta', 'Estado', 'Fecha', 'Participantes', 'Cupo', 'Ocupación'], $rows);
    }

    private function queryParticipacionRutas() {
        $db = new Database();
        $db->query("SELECT r.nombre, r.estado, r.fecha_visita, r.cupo_maximo,
                           (SELECT COUNT(*) FROM participantes_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS participantes
                    FROM rutas r
                    WHERE r.is_active = TRUE
                    ORDER BY r.fecha_visita DESC NULLS LAST, r.nombre ASC");
        return $db->resultSet();
    }

    // =========================================================================
    // RF29: Reporte de Rutas Turísticas
    // =========================================================================
    public function rutas() {
        $this->requireRoles([1, 3]);
        try {
            $filtroEstado     = trim($_GET['estado'] ?? '');
            $filtroTipo       = trim($_GET['tipo_ruta'] ?? '');
            $fechaDesde       = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta       = trim($_GET['fecha_hasta'] ?? '');
            $rutas            = $this->queryRutas($filtroEstado, $filtroTipo, $fechaDesde, $fechaHasta);
            $stats            = $this->statsRutas();

            $data = [
                'titulo'            => 'Reporte de Rutas Turísticas',
                'rutas'             => $rutas,
                'stats'             => $stats,
                'statsPorTipo'      => $this->statsRutasPorTipo(),
                'filtro_estado'     => $filtroEstado,
                'filtro_tipo'       => $filtroTipo,
                'fecha_desde'       => $fechaDesde,
                'fecha_hasta'       => $fechaHasta,
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
            $estado     = trim($_GET['estado'] ?? '');
            $tipo       = trim($_GET['tipo_ruta'] ?? '');
            $fechaDesde = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
            $rutas      = $this->queryRutas($estado, $tipo, $fechaDesde, $fechaHasta);
            // Sin columna "Tarifa" (H-14): `rutas.tiene_tarifa`/`tarifa_monto` no se capturan en
            // ningún formulario, así que exportaba "Gratuita" para toda ruta, siempre. Se reactiva
            // cuando el cliente defina el flujo de cobro (D-RT02).
            $headers = ['Ruta', 'Tipo', 'Fecha Visita', 'Hora', 'Departamento', 'Guía', 'Estado',
                        'Paradas', 'Participantes', 'Mujeres', 'Hombres', 'Niñas', 'Niños', 'Total Atendidos'];
            $rows    = [];
            $tpInsc = 0; $tpAt = 0;
            foreach ($rutas as $r) {
                $tpInsc += (int)$r->total_participantes;
                $tpAt   += (int)($r->total_atendidos ?? 0);
                $rows[] = [
                    $r->nombre,
                    $r->tipo_ruta ?? 'General',
                    $r->fecha_visita ? date('d/m/Y', strtotime($r->fecha_visita)) : '-',
                    $r->hora_visita ? substr($r->hora_visita, 0, 5) : '-',
                    $r->departamento_nombre ?? '-',
                    $r->facilitador_nombre ?? '-',
                    $r->estado,
                    (int)$r->total_puntos,
                    (int)$r->total_participantes,
                    (int)($r->mujeres ?? 0),
                    (int)($r->hombres ?? 0),
                    (int)($r->ninas ?? 0),
                    (int)($r->ninos ?? 0),
                    (int)($r->total_atendidos ?? 0),
                ];
            }
            // Fila de totales — 14 columnas: Participantes en la 9.ª, Total Atendidos en la última
            $rows[] = ['TOTALES', '', '', '', '', '', '', '', $tpInsc, '', '', '', '', $tpAt];
            $this->exportCsv('reporte_rutas', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarRutasPdf() {
        $this->requireRoles([1, 3]);
        try {
            $estado     = trim($_GET['estado'] ?? '');
            $tipo       = trim($_GET['tipo_ruta'] ?? '');
            $fechaDesde = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
            $rutas  = $this->queryRutas($estado, $tipo, $fechaDesde, $fechaHasta);
            $stats  = $this->statsRutas();

            $headers = ['Ruta', 'Tipo', 'Fecha', 'Departamento', 'Guía', 'Estado', 'Paradas', 'Particip.', 'Atendidos'];
            $rows    = [];
            foreach ($rutas as $r) {
                $rows[] = [
                    $r->nombre,
                    $r->tipo_ruta ?? 'General',
                    $r->fecha_visita ? date('d/m/Y', strtotime($r->fecha_visita)) : '-',
                    $r->departamento_nombre ?? '-',
                    $r->facilitador_nombre ?? '-',
                    $r->estado,
                    (int)$r->total_puntos,
                    (int)$r->total_participantes,
                    (int)($r->total_atendidos ?? 0),
                ];
            }
            $kpis = [
                'Total Rutas'      => $stats->total_rutas,
                'Activas'          => $stats->activas,
                'Finalizadas'      => $stats->finalizadas,
                'En Mantenimiento' => $stats->mantenimiento,
                'Inactivas'        => $stats->inactivas,
            ];
            $this->exportPdf("Reporte de Rutas Turísticas", "IMATUR — Gestión Turística", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryRutas(string $estado = '', string $tipo = '', string $fechaDesde = '', string $fechaHasta = '') {
        $db    = new Database();
        $binds = [];
        $where = "r.is_active = TRUE";
        if ($estado)     { $where .= " AND r.estado = :estado"; $binds[':estado'] = $estado; }
        if ($tipo)       { $where .= " AND r.tipo_ruta = :tipo"; $binds[':tipo'] = $tipo; }
        if ($fechaDesde) { $where .= " AND r.fecha_visita >= :fd"; $binds[':fd'] = $fechaDesde; }
        if ($fechaHasta) { $where .= " AND r.fecha_visita <= :fh"; $binds[':fh'] = $fechaHasta; }
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           (p.nombre || ' ' || p.apellido) AS facilitador_nombre,
                           (SELECT COUNT(*) FROM puntos_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) as total_puntos,
                           (SELECT COUNT(*) FROM participantes_ruta par WHERE par.id_ruta = r.id AND par.is_active = TRUE) as total_participantes,
                           ri.mujeres, ri.hombres, ri.ninas, ri.ninos, ri.total_atendidos
                    FROM rutas r
                    LEFT JOIN departamentos d   ON r.id_departamento = d.id
                    LEFT JOIN empleados e        ON r.id_facilitador = e.id
                    LEFT JOIN personas p         ON e.id_persona = p.id
                    LEFT JOIN ruta_informes ri   ON ri.id_ruta = r.id
                    WHERE {$where}
                    ORDER BY r.fecha_visita DESC NULLS LAST, r.created_at DESC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    private function statsRutas() {
        $db = new Database();
        $db->query("SELECT COUNT(*) as total_rutas,
                        COUNT(CASE WHEN estado = 'Activa'           THEN 1 END) as activas,
                        COUNT(CASE WHEN estado = 'Inactiva'         THEN 1 END) as inactivas,
                        COUNT(CASE WHEN estado = 'En Mantenimiento' THEN 1 END) as mantenimiento,
                        COUNT(CASE WHEN estado = 'Finalizada'       THEN 1 END) as finalizadas
                    FROM rutas WHERE is_active = TRUE");
        return $db->single();
    }

    // Demografía agregada de rutas (desde informes) por tipo de ruta
    private function statsRutasPorTipo() {
        $db = new Database();
        $db->query("SELECT COALESCE(r.tipo_ruta, 'General') AS tipo_ruta,
                           COUNT(DISTINCT r.id) AS rutas,
                           COUNT(CASE WHEN r.estado = 'Finalizada' THEN 1 END) AS finalizadas,
                           COALESCE(SUM(ri.mujeres), 0) AS mujeres,
                           COALESCE(SUM(ri.hombres), 0) AS hombres,
                           COALESCE(SUM(ri.ninas), 0)   AS ninas,
                           COALESCE(SUM(ri.ninos), 0)   AS ninos,
                           COALESCE(SUM(ri.total_atendidos), 0) AS total_atendidos
                    FROM rutas r
                    LEFT JOIN ruta_informes ri ON ri.id_ruta = r.id
                    WHERE r.is_active = TRUE
                    GROUP BY COALESCE(r.tipo_ruta, 'General')
                    ORDER BY total_atendidos DESC");
        return $db->resultSet();
    }

    // =========================================================================
    // Turismo — Ejecuciones de ruta (rutas Finalizadas) (BRT-05)
    // =========================================================================
    public function ejecucionesRuta() {
        $this->requireRoles([1, 3]);
        try {
            $regs = $this->queryEjecucionesRuta();
            $filas = []; $totPart = 0; $totAte = 0;
            foreach ($regs as $r) {
                $totPart += (int)$r->participantes; $totAte += (int)$r->atendidos;
                $filas[] = [
                    ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->nombre ?? '—') . '</span>'],
                    $r->tipo_ruta ?? 'General',
                    !empty($r->fecha_ejecucion) ? date('d/m/Y', strtotime($r->fecha_ejecucion)) : '—',
                    (string)(int)$r->participantes,
                    (string)(int)$r->atendidos,
                ];
            }
            $anio = (int)($_GET['anio'] ?? date('Y'));
            $anios = $this->aniosDisponibles('rutas', "COALESCE(fecha_visita, created_at)", $anio, "estado = 'Finalizada'");
            $db = new Database();
            $db->query("SELECT DISTINCT COALESCE(tipo_ruta, 'General') AS t FROM rutas WHERE is_active = TRUE AND estado = 'Finalizada' ORDER BY t");
            $tipos = ['' => 'Todos']; foreach ($db->resultSet() as $tt) $tipos[$tt->t] = $tt->t;
            $this->renderReporte([
                'eyebrow' => 'Turismo · Impacto', 'titulo' => 'Ejecuciones de Ruta',
                'subtitulo' => 'Rutas efectivamente ejecutadas (Finalizadas), con participantes y personas atendidas por ejecución.',
                'resumen' => ['Ejecuciones' => count($regs), 'Participantes' => $totPart, 'Atendidos (informe)' => $totAte],
                'columnas' => ['Ruta', 'Tipo', 'Fecha de ejecución', 'Participantes', 'Atendidos'],
                'filas' => $filas,
                'accion' => URL_ROOT . '/reportes/ejecucionesRuta',
                'filtros' => [
                    ['name' => 'anio', 'label' => 'Año', 'type' => 'select', 'options' => $anios, 'value' => (string)$anio],
                    ['name' => 'tipo', 'label' => 'Tipo de ruta', 'type' => 'select', 'options' => $tipos, 'value' => $_GET['tipo'] ?? ''],
                ],
                'export_url' => URL_ROOT . '/reportes/exportarEjecucionesRutaCsv?' . $this->qsFiltros(),
                'vacio' => 'No hay rutas ejecutadas (Finalizadas) para el filtro.',
            ]);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de ejecuciones: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarEjecucionesRutaCsv() {
        $this->requireRoles([1, 3]);
        $rows = [];
        foreach ($this->queryEjecucionesRuta() as $r) {
            $rows[] = [$r->nombre, $r->tipo_ruta ?? 'General', $r->fecha_ejecucion, (int)$r->participantes, (int)$r->atendidos];
        }
        $this->exportCsv('ejecuciones_ruta', ['Ruta', 'Tipo', 'Fecha de ejecución', 'Participantes', 'Atendidos'], $rows);
    }

    private function queryEjecucionesRuta() {
        $db = new Database();
        $anio = (int)($_GET['anio'] ?? date('Y'));
        $tipo = trim($_GET['tipo'] ?? '');
        $where = "r.is_active = TRUE AND r.estado = 'Finalizada'
                  AND EXTRACT(YEAR FROM COALESCE(r.fecha_visita, r.created_at)) = :anio";
        if ($tipo !== '') $where .= " AND COALESCE(r.tipo_ruta, 'General') = :tipo";
        $db->query("SELECT r.nombre, COALESCE(r.tipo_ruta, 'General') AS tipo_ruta,
                           COALESCE(r.fecha_visita, r.created_at) AS fecha_ejecucion,
                           (SELECT COUNT(*) FROM participantes_ruta pr WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS participantes,
                           COALESCE(ri.total_atendidos, 0) AS atendidos
                    FROM rutas r
                    LEFT JOIN ruta_informes ri ON ri.id_ruta = r.id
                    WHERE {$where}
                    ORDER BY COALESCE(r.fecha_visita, r.created_at) DESC, r.nombre ASC");
        $db->bind(':anio', $anio);
        if ($tipo !== '') $db->bind(':tipo', $tipo);
        return $db->resultSet();
    }
}
