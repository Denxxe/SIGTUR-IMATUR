<?php
/**
 * Inventario — bienes, kardex, asignaciones y bajas
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesInventarioTrait {

    // =========================================================================
    // Inventario — Kardex / movimientos
    // =========================================================================
    public function kardex() {
        $this->requireRoles([1, 4]);
        $regs = $this->queryKardex();
        $filas = [];
        foreach ($regs as $r) {
            $filas[] = [
                !empty($r->fecha) ? date('d/m/Y', strtotime($r->fecha)) : '—',
                ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->codigo_bn ?? '—') . '</span>'],
                $r->item ?? '—',
                ['raw' => '<span class="sig-badge sig-badge--info">' . htmlspecialchars($r->tipo_movimiento ?? '—') . '</span>'],
                trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')) ?: '—',
                $r->descripcion ?: '—',
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'Inventario · Reporte', 'titulo' => 'Kardex de Movimientos',
            'subtitulo' => 'Entradas, salidas y asignaciones de bienes por período.',
            'resumen' => ['Movimientos' => count($regs)],
            'columnas' => ['Fecha', 'Código BN', 'Bien', 'Tipo', 'Responsable', 'Descripción'],
            'filas' => $filas,
            'accion' => URL_ROOT . '/reportes/kardex',
            'filtros' => [
                ['name' => 'buscar', 'label' => 'Buscar', 'type' => 'text', 'placeholder' => 'Bien, código o responsable…', 'value' => $_GET['buscar'] ?? ''],
                ['name' => 'fecha_desde', 'label' => 'Desde', 'type' => 'date', 'value' => $_GET['fecha_desde'] ?? ''],
                ['name' => 'fecha_hasta', 'label' => 'Hasta', 'type' => 'date', 'value' => $_GET['fecha_hasta'] ?? ''],
            ],
            'export_url' => URL_ROOT . '/reportes/exportarKardexCsv?' . $this->qsFiltros(),
            'vacio' => 'No hay movimientos de inventario para el filtro.',
        ]);
    }

    public function exportarKardexCsv() {
        $this->requireRoles([1, 4]);
        $rows = [];
        foreach ($this->queryKardex() as $r) {
            $rows[] = [$r->fecha, $r->codigo_bn, $r->item, $r->tipo_movimiento, trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->descripcion];
        }
        $this->exportCsv('kardex_inventario', ['Fecha', 'Código BN', 'Bien', 'Tipo', 'Responsable', 'Descripción'], $rows);
    }

    private function queryKardex() {
        $db = new Database();
        $binds = [];
        $where = "ai.is_active = TRUE";
        if (!empty($_GET['buscar']))      { $where .= " AND (i.nombre ILIKE :q OR i.codigo_bn ILIKE :q OR (p.nombre||' '||p.apellido) ILIKE :q OR ai.descripcion ILIKE :q)"; $binds[':q'] = '%' . trim($_GET['buscar']) . '%'; }
        if (!empty($_GET['fecha_desde'])) { $where .= " AND ai.fecha >= :fd"; $binds[':fd'] = trim($_GET['fecha_desde']); }
        if (!empty($_GET['fecha_hasta'])) { $where .= " AND ai.fecha <= :fh"; $binds[':fh'] = trim($_GET['fecha_hasta']); }
        $db->query("SELECT ai.fecha, ai.tipo_movimiento, ai.descripcion, i.codigo_bn, i.nombre AS item,
                           p.nombre, p.apellido
                    FROM actividad_inventario ai
                    INNER JOIN inventario i ON ai.id_inventario = i.id
                    LEFT JOIN empleados e   ON ai.id_empleado_responsable = e.id
                    LEFT JOIN personas p    ON e.id_persona = p.id
                    WHERE {$where}
                    ORDER BY ai.fecha DESC, ai.id DESC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    // =========================================================================
    // Inventario — Bienes asignados (responsable actual)
    // =========================================================================
    public function bienesAsignados() {
        $this->requireRoles([1, 4]);
        $regs = $this->queryBienesAsignados();
        $filas = [];
        foreach ($regs as $r) {
            $filas[] = [
                ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->codigo_bn ?? '—') . '</span>'],
                $r->item ?? '—',
                ['raw' => '<span class="sig-badge sig-badge--neutral">' . htmlspecialchars($r->condicion ?? '—') . '</span>'],
                trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                !empty($r->fecha) ? date('d/m/Y', strtotime($r->fecha)) : '—',
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'Inventario · Reporte', 'titulo' => 'Bienes Asignados',
            'subtitulo' => 'Responsable actual de cada bien (según el último movimiento de asignación).',
            'resumen' => ['Bienes asignados' => count($regs)],
            'columnas' => ['Código BN', 'Bien', 'Condición', 'Responsable', 'Desde'],
            'filas' => $filas,
            'export_url' => URL_ROOT . '/reportes/exportarBienesAsignadosCsv',
            'vacio' => 'No hay bienes con responsable asignado.',
        ]);
    }

    public function exportarBienesAsignadosCsv() {
        $this->requireRoles([1, 4]);
        $rows = [];
        foreach ($this->queryBienesAsignados() as $r) {
            $rows[] = [$r->codigo_bn, $r->item, $r->condicion, trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->fecha];
        }
        $this->exportCsv('bienes_asignados', ['Código BN', 'Bien', 'Condición', 'Responsable', 'Desde'], $rows);
    }

    private function queryBienesAsignados() {
        $db = new Database();
        $db->query("SELECT DISTINCT ON (ai.id_inventario)
                           i.codigo_bn, i.nombre AS item, i.condicion, ai.fecha, p.nombre, p.apellido
                    FROM actividad_inventario ai
                    INNER JOIN inventario i ON ai.id_inventario = i.id AND i.is_active = TRUE
                    INNER JOIN empleados e  ON ai.id_empleado_responsable = e.id
                    INNER JOIN personas p   ON e.id_persona = p.id
                    WHERE ai.is_active = TRUE AND ai.id_empleado_responsable IS NOT NULL
                    ORDER BY ai.id_inventario, ai.fecha DESC, ai.id DESC");
        return $db->resultSet();
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
                'filtro_estatus'   => $_GET['estatus'] ?? '',
                'filtro_origen'    => $_GET['origen'] ?? '',
                'filtro_departamento' => $_GET['departamento'] ?? '',
                'filtro_deposito'  => !empty($_GET['deposito']),
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
        // Filtros añadidos en la Fase 4 (R-5): con ellos, este único reporte
        // cubre las listas que pide la Presidencia en B-51 — activos, dañados,
        // sin código, donaciones, por departamento y los que están en depósito.
        $estatus     = in_array($_GET['estatus'] ?? '', Inventario::ESTATUS, true) ? $_GET['estatus'] : '';
        $origen      = in_array($_GET['origen']  ?? '', Inventario::ORIGENES, true) ? $_GET['origen'] : '';
        $departa     = trim($_GET['departamento'] ?? '');
        $soloDeposito = !empty($_GET['deposito']);

        // Inventario ACTIVO: los dados de baja salen del listado (B-38, mig. 062)
        // y se consultan en el reporte de desincorporados.
        $where = "i.is_active = TRUE AND i.estatus <> 'Dado de baja'";
        if ($estatus     !== '') $where .= " AND i.estatus = :estatus";
        if ($origen      !== '') $where .= " AND i.origen = :origen";
        if ($departa     !== '') $where .= " AND d.nombre ILIKE :departa";
        if ($soloDeposito)       $where .= " AND u.es_deposito = TRUE";
        if ($condicion !== '') $where .= " AND i.condicion = :condicion";
        if ($categoria !== '') $where .= " AND c.nombre ILIKE :categoria";
        if ($ubicacion !== '') $where .= " AND u.nombre ILIKE :ubicacion";

        $db->query("SELECT i.codigo_bn, i.nombre, i.condicion, i.estatus, i.marca, i.modelo, i.serial,
                           c.nombre AS categoria,
                           u.nombre AS ubicacion,
                           p.nombre AS responsable
                    FROM inventario i
                    LEFT JOIN categorias c  ON i.id_categoria = c.id
                    LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                    LEFT JOIN departamentos d ON u.\"departamento _d\" = d.id
                    -- Responsable DERIVADO (B-68, mig. 066): la jefatura del
                    -- departamento donde está el bien. No es una columna.
                    LEFT JOIN LATERAL (
                        SELECT TRIM(COALESCE(pr.nombre,'') || ' ' || COALESCE(pr.apellido,'')) AS nombre
                          FROM empleados er
                          INNER JOIN personas pr ON pr.id = er.id_persona
                          LEFT  JOIN cargos   cr ON cr.id = er.id_cargo
                         WHERE er.is_active = TRUE AND er.fecha_egreso IS NULL
                           AND er.id_departamento = CASE
                                 WHEN u.es_deposito THEN (SELECT NULLIF(valor,'')::int
                                                            FROM configuracion_sistema
                                                           WHERE clave = 'bienes_depto_autoriza')
                                 ELSE u.\"departamento _d\" END
                           AND cr.nivel_jerarquico IN ('Dirección','Coordinación')
                         ORDER BY CASE cr.nivel_jerarquico WHEN 'Dirección' THEN 1 ELSE 2 END, er.id
                         LIMIT 1
                    ) p ON TRUE
                    WHERE {$where}
                    ORDER BY c.nombre ASC, i.nombre ASC");
        if ($estatus  !== '') $db->bind(':estatus', $estatus);
        if ($origen   !== '') $db->bind(':origen', $origen);
        if ($departa  !== '') $db->bind(':departa', '%' . $departa . '%');
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
                        COUNT(CASE WHEN estatus = 'En mantenimiento' THEN 1 END) AS reparacion
                    FROM inventario WHERE is_active = TRUE AND estatus <> 'Dado de baja'");
        return $db->single();
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
                               pu.username AS eliminado_por,
                               ai_baja.descripcion AS motivo_baja
                        FROM inventario i
                        LEFT JOIN categorias c  ON i.id_categoria = c.id
                        LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                        LEFT JOIN usuarios pu   ON i.deleted_by   = pu.id
                        LEFT JOIN LATERAL (
                            SELECT descripcion FROM actividad_inventario ai
                            WHERE ai.id_inventario = i.id AND ai.tipo_movimiento = 'Baja'
                            ORDER BY ai.fecha DESC, ai.id DESC LIMIT 1
                        ) ai_baja ON TRUE
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
                               i.deleted_at, pu.username AS eliminado_por,
                               ai_baja.descripcion AS motivo_baja
                        FROM inventario i
                        LEFT JOIN categorias c ON i.id_categoria = c.id
                        LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                        LEFT JOIN usuarios pu  ON i.deleted_by   = pu.id
                        LEFT JOIN LATERAL (
                            SELECT descripcion FROM actividad_inventario ai
                            WHERE ai.id_inventario = i.id AND ai.tipo_movimiento = 'Baja'
                            ORDER BY ai.fecha DESC, ai.id DESC LIMIT 1
                        ) ai_baja ON TRUE
                        WHERE {$where} ORDER BY i.deleted_at DESC");
            if ($fi)        $db->bind(':fi', $fi);
            if ($ff)        $db->bind(':ff', $ff);
            if ($categoria) $db->bind(':categoria', '%' . $categoria . '%');
            $bajas   = $db->resultSet();

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición', 'Marca', 'Modelo', 'Serial', 'Fecha Baja', 'Dado de baja por', 'Motivo'];
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
                    $b->motivo_baja  ?? '-',
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
                               i.deleted_at, pu.username AS eliminado_por,
                               ai_baja.descripcion AS motivo_baja
                        FROM inventario i
                        LEFT JOIN categorias c ON i.id_categoria = c.id
                        LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                        LEFT JOIN usuarios pu  ON i.deleted_by   = pu.id
                        LEFT JOIN LATERAL (
                            SELECT descripcion FROM actividad_inventario ai
                            WHERE ai.id_inventario = i.id AND ai.tipo_movimiento = 'Baja'
                            ORDER BY ai.fecha DESC, ai.id DESC LIMIT 1
                        ) ai_baja ON TRUE
                        WHERE {$where} ORDER BY i.deleted_at DESC");
            if ($fi)        $db->bind(':fi', $fi);
            if ($ff)        $db->bind(':ff', $ff);
            if ($categoria) $db->bind(':categoria', '%' . $categoria . '%');
            $bajas = $db->resultSet();

            $db->query("SELECT COUNT(*) as total FROM inventario WHERE is_active = FALSE AND deleted_at IS NOT NULL");
            $totalHist = $db->single();

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición', 'Fecha Baja', 'Dado de baja por', 'Motivo'];
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
                    $b->motivo_baja  ?? '-',
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
}
