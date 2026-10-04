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
 *
 * ─────────────────────────────────────────────────────────────────────────────
 * ⚠️ Repuntado a `ruta_ejecuciones` el 2026-09-17 (mig. 078).
 *
 * Los tres reportes de este trait leían `rutas` como si cada fila fuera una
 * salida: su fecha (`fecha_visita`), su guía (`id_facilitador`), su estado
 * `'Finalizada'`, y los participantes por `participantes_ruta.id_ruta`. Tras la
 * separación catálogo/salida nada de eso se escribe, así que **los tres salían
 * vacíos o en cero sin avisar**: «Ejecuciones de Ruta» filtraba por un estado
 * que ya no existe, y las columnas de participantes y atendidos contaban filas
 * cuya `id_ruta` es NULL.
 *
 * Ahora: lo que es del **recorrido** (nombre, tipo, departamento, paradas,
 * restricciones) se lee de `rutas`; lo que es de **cada salida** (fecha, estado,
 * cupo, participantes, informe) se lee de `ruta_ejecuciones`.
 * ─────────────────────────────────────────────────────────────────────────────
 */
trait ReportesTurismoTrait {

    // =========================================================================
    // Turismo — Participación / ocupación
    // =========================================================================
    // Es un reporte **por salida**: la ocupación es de un grupo en una fecha, no
    // del recorrido (el mismo recorrido puede ir lleno un martes y a la mitad el
    // jueves). Antes agregaba todo contra el catálogo y el cupo era el de la
    // columna muerta `rutas.cupo_maximo`.
    public function participacionRutas() {
        $this->requireModulo('RutasController');
        $regs = $this->queryParticipacionRutas();
        $filas = []; $totalPart = 0; $sumaOcup = 0; $conCupo = 0;
        foreach ($regs as $r) {
            $cupo = (int)$r->cupo_maximo; $part = (int)$r->participantes;
            $totalPart += $part;
            $pct = $cupo > 0 ? round($part / $cupo * 100) : 0;
            if ($cupo > 0) { $sumaOcup += $pct; $conCupo++; }
            $badge = $pct >= 100 ? 'sig-badge--danger' : ($pct >= 80 ? 'sig-badge--warning' : 'sig-badge--success');
            $filas[] = [
                ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->ruta_nombre ?? '—') . '</span>'],
                !empty($r->fecha) ? date('d/m/Y', strtotime($r->fecha)) : '—',
                ['raw' => '<span class="sig-badge ' . (RutaEjecucion::ESTADO_BADGES[$r->estado ?? ''] ?? 'sig-badge--neutral') . '">' . htmlspecialchars($r->estado ?? '—') . '</span>'],
                $r->grupo ?: '—',
                (string)$part,
                $cupo > 0 ? (string)$cupo : '—',
                ['raw' => $cupo > 0 ? '<span class="sig-badge ' . $badge . '">' . $pct . '%</span>' : '—'],
            ];
        }
        $ocupProm = $conCupo > 0 ? round($sumaOcup / $conCupo) . '%' : '—';
        $this->renderReporte([
            'eyebrow' => 'Turismo · Impacto', 'titulo' => 'Participación en Salidas',
            'subtitulo' => 'Ocupación de cada salida programada (participantes registrados vs. cupo previsto).',
            'resumen' => ['Salidas' => count($regs), 'Participaciones' => $totalPart, 'Ocupación promedio' => $ocupProm],
            'columnas' => ['Recorrido', 'Fecha', 'Estado', 'Grupo / Institución', 'Participantes', 'Cupo', 'Ocupación'],
            'filas' => $filas,
            'export_url' => URL_ROOT . '/reportes/exportarParticipacionRutasCsv',
            'vacio' => 'No hay salidas programadas.',
        ]);
    }

    public function exportarParticipacionRutasCsv() {
        $this->requireModulo('RutasController');
        $rows = [];
        foreach ($this->queryParticipacionRutas() as $r) {
            $cupo = (int)$r->cupo_maximo; $part = (int)$r->participantes;
            $rows[] = [
                $r->ruta_nombre,
                $r->fecha ? date('d/m/Y', strtotime($r->fecha)) : '-',
                $r->estado,
                $r->grupo ?: '-',
                $part,
                $cupo > 0 ? $cupo : '-',
                ($cupo > 0 ? round($part / $cupo * 100) . '%' : '-'),
            ];
        }
        $this->exportCsv('participacion_salidas',
            ['Recorrido', 'Fecha', 'Estado', 'Grupo / Institución', 'Participantes', 'Cupo', 'Ocupación'],
            $rows, 'Participación en Salidas de Ruta');
    }

    private function queryParticipacionRutas() {
        $db = new Database();
        $db->query("SELECT r.nombre AS ruta_nombre, ej.fecha, ej.estado, ej.cupo_maximo,
                           COALESCE(NULLIF(ej.institucion_nombre, ''), ej.origen) AS grupo,
                           (SELECT COUNT(*) FROM participantes_ruta pr
                             WHERE pr.id_ejecucion = ej.id AND pr.is_active = TRUE) AS participantes
                    FROM ruta_ejecuciones ej
                    INNER JOIN rutas r ON ej.id_ruta = r.id
                    WHERE ej.is_active = TRUE
                    ORDER BY ej.fecha DESC, r.nombre ASC");
        return $db->resultSet();
    }

    // =========================================================================
    // RF29: Reporte del catálogo de Rutas Turísticas
    // =========================================================================
    // Este SÍ es del catálogo: un recorrido por fila. Lo que aporta de cada uno
    // es cuántas veces se ha ejecutado y a cuánta gente ha atendido — no una
    // fecha ni un guía, que son de la salida.
    public function rutas() {
        $this->requireModulo('RutasController');
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
        $this->requireModulo('RutasController');
        try {
            $estado     = trim($_GET['estado'] ?? '');
            $tipo       = trim($_GET['tipo_ruta'] ?? '');
            $fechaDesde = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
            $rutas      = $this->queryRutas($estado, $tipo, $fechaDesde, $fechaHasta);
            // La columna «Tarifa» VUELVE (H-14 cerrado, mig. 083): se retiró porque
            // `tiene_tarifa`/`tarifa_monto` no se capturaban en ningún formulario y el
            // reporte informaba «Gratuita» para toda ruta, siempre. Desde T-C se capturan.
            $headers = ['Recorrido', 'Tipo', 'Tarifa', 'Departamento', 'Estado', 'Restricciones', 'Paradas',
                        'Salidas', 'Ejecutadas', 'Última salida', 'Participantes',
                        'Mujeres', 'Hombres', 'Niñas', 'Niños', 'Total Atendidos'];
            $rows    = [];
            $tpInsc = 0; $tpAt = 0;
            foreach ($rutas as $r) {
                $tpInsc += (int)$r->total_participantes;
                $tpAt   += (int)($r->total_atendidos ?? 0);
                $rows[] = [
                    $r->nombre,
                    $r->tipo_ruta ?? 'General',
                    Ruta::textoTarifa($r),
                    $r->departamento_nombre ?? '-',
                    $r->estado,
                    $this->textoRestricciones($r),
                    (int)$r->total_puntos,
                    (int)$r->total_salidas,
                    (int)$r->salidas_ejecutadas,
                    $r->ultima_salida ? date('d/m/Y', strtotime($r->ultima_salida)) : '-',
                    (int)$r->total_participantes,
                    (int)($r->mujeres ?? 0),
                    (int)($r->hombres ?? 0),
                    (int)($r->ninas ?? 0),
                    (int)($r->ninos ?? 0),
                    (int)($r->total_atendidos ?? 0),
                ];
            }
            // Fila de totales — 16 columnas: Participantes en la 11.ª, Total Atendidos en la última
            $rows[] = ['TOTALES', '', '', '', '', '', '', '', '', '', $tpInsc, '', '', '', '', $tpAt];
            $this->exportCsv('reporte_rutas', $headers, $rows, 'Catálogo de Rutas Turísticas');
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarRutasPdf() {
        $this->requireModulo('RutasController');
        try {
            $estado     = trim($_GET['estado'] ?? '');
            $tipo       = trim($_GET['tipo_ruta'] ?? '');
            $fechaDesde = trim($_GET['fecha_desde'] ?? '');
            $fechaHasta = trim($_GET['fecha_hasta'] ?? '');
            $rutas  = $this->queryRutas($estado, $tipo, $fechaDesde, $fechaHasta);
            $stats  = $this->statsRutas();

            $headers = ['Recorrido', 'Tipo', 'Tarifa', 'Departamento', 'Estado', 'Paradas', 'Salidas', 'Última salida', 'Particip.', 'Atendidos'];
            $rows    = [];
            foreach ($rutas as $r) {
                $rows[] = [
                    $r->nombre,
                    $r->tipo_ruta ?? 'General',
                    Ruta::textoTarifa($r),
                    $r->departamento_nombre ?? '-',
                    $r->estado,
                    (int)$r->total_puntos,
                    (int)$r->total_salidas,
                    $r->ultima_salida ? date('d/m/Y', strtotime($r->ultima_salida)) : '-',
                    (int)$r->total_participantes,
                    (int)($r->total_atendidos ?? 0),
                ];
            }
            $kpis = [
                'Recorridos'        => $stats->total_rutas,
                'Activos'           => $stats->activas,
                'En Mantenimiento'  => $stats->mantenimiento,
                'Inactivos'         => $stats->inactivas,
                'Salidas ejecutadas'=> $stats->salidas_ejecutadas,
            ];
            $this->exportPdf("Catálogo de Rutas Turísticas", "IMATUR — Gestión Turística", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    /** Texto plano de las restricciones del recorrido (mig. 079), para el export. */
    private function textoRestricciones($r): string {
        $partes = [];
        if (($r->edad_min ?? null) !== null || ($r->edad_max ?? null) !== null) {
            $partes[] = Ruta::textoEdades($r);
        }
        if (!empty($r->restricciones)) $partes[] = $r->restricciones;
        return $partes ? implode(' · ', $partes) : '-';
    }

    // El filtro de fechas ya no mira una columna del recorrido: se cumple si el
    // recorrido TIENE alguna salida en ese rango.
    private function queryRutas(string $estado = '', string $tipo = '', string $fechaDesde = '', string $fechaHasta = '') {
        $db    = new Database();
        $binds = [];
        $where = "r.is_active = TRUE";
        if ($estado)     { $where .= " AND r.estado = :estado"; $binds[':estado'] = $estado; }
        if ($tipo)       { $where .= " AND r.tipo_ruta = :tipo"; $binds[':tipo'] = $tipo; }
        if ($fechaDesde) {
            $where .= " AND EXISTS (SELECT 1 FROM ruta_ejecuciones x
                                     WHERE x.id_ruta = r.id AND x.is_active = TRUE AND x.fecha >= :fd)";
            $binds[':fd'] = $fechaDesde;
        }
        if ($fechaHasta) {
            $where .= " AND EXISTS (SELECT 1 FROM ruta_ejecuciones x
                                     WHERE x.id_ruta = r.id AND x.is_active = TRUE AND x.fecha <= :fh)";
            $binds[':fh'] = $fechaHasta;
        }
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           (SELECT COUNT(*) FROM puntos_ruta pt
                             WHERE pt.id_ruta = r.id AND pt.is_active = TRUE) AS total_puntos,
                           (SELECT COUNT(*) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS total_salidas,
                           (SELECT COUNT(*) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE
                               AND ej.estado = 'Ejecutado') AS salidas_ejecutadas,
                           (SELECT MAX(ej.fecha) FROM ruta_ejecuciones ej
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE) AS ultima_salida,
                           (SELECT COUNT(*) FROM participantes_ruta par
                             INNER JOIN ruta_ejecuciones ej ON par.id_ejecucion = ej.id
                             WHERE ej.id_ruta = r.id AND ej.is_active = TRUE
                               AND par.is_active = TRUE) AS total_participantes,
                           ag.mujeres, ag.hombres, ag.ninas, ag.ninos, ag.total_atendidos
                    FROM rutas r
                    LEFT JOIN departamentos d ON r.id_departamento = d.id
                    LEFT JOIN LATERAL (
                        SELECT COALESCE(SUM(ri.mujeres), 0)         AS mujeres,
                               COALESCE(SUM(ri.hombres), 0)         AS hombres,
                               COALESCE(SUM(ri.ninas), 0)           AS ninas,
                               COALESCE(SUM(ri.ninos), 0)           AS ninos,
                               COALESCE(SUM(ri.total_atendidos), 0) AS total_atendidos
                          FROM ruta_informes ri
                          INNER JOIN ruta_ejecuciones ej ON ri.id_ejecucion = ej.id
                         WHERE ej.id_ruta = r.id AND ej.is_active = TRUE
                    ) ag ON TRUE
                    WHERE {$where}
                    ORDER BY r.nombre ASC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    private function statsRutas() {
        $db = new Database();
        // 'Finalizada' desapareció del catálogo en la mig. 078 — describía una
        // salida, no un recorrido. El KPI equivalente es cuántas SALIDAS se
        // ejecutaron.
        $db->query("SELECT COUNT(*) as total_rutas,
                        COUNT(CASE WHEN estado = 'Activa'           THEN 1 END) as activas,
                        COUNT(CASE WHEN estado = 'Inactiva'         THEN 1 END) as inactivas,
                        COUNT(CASE WHEN estado = 'En Mantenimiento' THEN 1 END) as mantenimiento,
                        (SELECT COUNT(*) FROM ruta_ejecuciones
                          WHERE is_active = TRUE AND estado = 'Ejecutado') as salidas_ejecutadas
                    FROM rutas WHERE is_active = TRUE");
        return $db->single();
    }

    // Demografía agregada por tipo de ruta, desde los informes de cada salida
    private function statsRutasPorTipo() {
        $db = new Database();
        $db->query("SELECT COALESCE(r.tipo_ruta, 'General') AS tipo_ruta,
                           COUNT(DISTINCT r.id)  AS rutas,
                           COUNT(DISTINCT CASE WHEN ej.estado = 'Ejecutado' THEN ej.id END) AS ejecutadas,
                           COALESCE(SUM(ri.mujeres), 0) AS mujeres,
                           COALESCE(SUM(ri.hombres), 0) AS hombres,
                           COALESCE(SUM(ri.ninas), 0)   AS ninas,
                           COALESCE(SUM(ri.ninos), 0)   AS ninos,
                           COALESCE(SUM(ri.total_atendidos), 0) AS total_atendidos
                    FROM rutas r
                    LEFT JOIN ruta_ejecuciones ej ON ej.id_ruta = r.id AND ej.is_active = TRUE
                    LEFT JOIN ruta_informes ri    ON ri.id_ejecucion = ej.id
                    WHERE r.is_active = TRUE
                    GROUP BY COALESCE(r.tipo_ruta, 'General')
                    ORDER BY total_atendidos DESC");
        return $db->resultSet();
    }

    // =========================================================================
    // Turismo — Salidas ejecutadas (BRT-05)
    // =========================================================================
    public function ejecucionesRuta() {
        $this->requireModulo('RutasController');
        try {
            $regs = $this->queryEjecucionesRuta();
            $filas = []; $totPart = 0; $totAte = 0;
            foreach ($regs as $r) {
                $totPart += (int)$r->participantes; $totAte += (int)$r->atendidos;
                $filas[] = [
                    ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->nombre ?? '—') . '</span>'],
                    $r->tipo_ruta ?? 'General',
                    !empty($r->fecha_ejecucion) ? date('d/m/Y', strtotime($r->fecha_ejecucion)) : '—',
                    $r->grupo ?: '—',
                    (string)(int)$r->participantes,
                    (string)(int)$r->atendidos,
                ];
            }
            $anio  = (int)($_GET['anio'] ?? date('Y'));
            $anios = $this->aniosDisponibles('ruta_ejecuciones', 'fecha', $anio, "estado = 'Ejecutado'");
            $db = new Database();
            $db->query("SELECT DISTINCT COALESCE(r.tipo_ruta, 'General') AS t
                          FROM ruta_ejecuciones ej
                          INNER JOIN rutas r ON ej.id_ruta = r.id
                         WHERE ej.is_active = TRUE AND ej.estado = 'Ejecutado'
                         ORDER BY t");
            $tipos = ['' => 'Todos']; foreach ($db->resultSet() as $tt) $tipos[$tt->t] = $tt->t;
            $this->renderReporte([
                'eyebrow' => 'Turismo · Impacto', 'titulo' => 'Salidas Ejecutadas',
                'subtitulo' => 'Salidas que efectivamente se realizaron, con participantes y personas atendidas según su ficha.',
                'resumen' => ['Salidas' => count($regs), 'Participantes' => $totPart, 'Atendidos (ficha)' => $totAte],
                'columnas' => ['Recorrido', 'Tipo', 'Fecha', 'Grupo / Institución', 'Participantes', 'Atendidos'],
                'filas' => $filas,
                'accion' => URL_ROOT . '/reportes/ejecucionesRuta',
                'filtros' => [
                    ['name' => 'anio', 'label' => 'Año', 'type' => 'select', 'options' => $anios, 'value' => (string)$anio],
                    ['name' => 'tipo', 'label' => 'Tipo de ruta', 'type' => 'select', 'options' => $tipos, 'value' => $_GET['tipo'] ?? ''],
                ],
                'export_url' => URL_ROOT . '/reportes/exportarEjecucionesRutaCsv?' . $this->qsFiltros(),
                'vacio' => 'No hay salidas ejecutadas para el filtro.',
            ]);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de ejecuciones: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarEjecucionesRutaCsv() {
        $this->requireModulo('RutasController');
        $rows = [];
        foreach ($this->queryEjecucionesRuta() as $r) {
            $rows[] = [
                $r->nombre,
                $r->tipo_ruta ?? 'General',
                $r->fecha_ejecucion ? date('d/m/Y', strtotime($r->fecha_ejecucion)) : '-',
                $r->grupo ?: '-',
                (int)$r->participantes,
                (int)$r->atendidos,
            ];
        }
        $this->exportCsv('salidas_ejecutadas',
            ['Recorrido', 'Tipo', 'Fecha', 'Grupo / Institución', 'Participantes', 'Atendidos'],
            $rows, 'Salidas de Ruta Ejecutadas');
    }

    private function queryEjecucionesRuta() {
        $db = new Database();
        $anio = (int)($_GET['anio'] ?? date('Y'));
        $tipo = trim($_GET['tipo'] ?? '');
        $where = "ej.is_active = TRUE AND ej.estado = 'Ejecutado'
                  AND EXTRACT(YEAR FROM ej.fecha) = :anio";
        if ($tipo !== '') $where .= " AND COALESCE(r.tipo_ruta, 'General') = :tipo";
        $db->query("SELECT r.nombre, COALESCE(r.tipo_ruta, 'General') AS tipo_ruta,
                           ej.fecha AS fecha_ejecucion,
                           COALESCE(NULLIF(ej.institucion_nombre, ''), ej.origen) AS grupo,
                           (SELECT COUNT(*) FROM participantes_ruta pr
                             WHERE pr.id_ejecucion = ej.id AND pr.is_active = TRUE) AS participantes,
                           COALESCE((SELECT ri.total_atendidos FROM ruta_informes ri
                                      WHERE ri.id_ejecucion = ej.id LIMIT 1), 0) AS atendidos
                    FROM ruta_ejecuciones ej
                    INNER JOIN rutas r ON ej.id_ruta = r.id
                    WHERE {$where}
                    ORDER BY ej.fecha DESC, r.nombre ASC");
        $db->bind(':anio', $anio);
        if ($tipo !== '') $db->bind(':tipo', $tipo);
        return $db->resultSet();
    }
}
