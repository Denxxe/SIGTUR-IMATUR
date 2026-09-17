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
        $estBaja   = Inventario::sqlEstBaja();   // mig. 076: el texto no se cablea
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
        $where = "i.is_active = TRUE AND i.estatus <> {$estBaja}";
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
        $estBaja = Inventario::sqlEstBaja();   // mig. 076: el texto no se cablea
        $db->query("SELECT
                        COUNT(*) AS total,
                        COUNT(CASE WHEN condicion = 'Nuevo'         THEN 1 END) AS nuevos,
                        COUNT(CASE WHEN condicion = 'Bueno'         THEN 1 END) AS buenos,
                        COUNT(CASE WHEN condicion = 'Regular'       THEN 1 END) AS regulares,
                        COUNT(CASE WHEN condicion = 'Dañado'        THEN 1 END) AS danados,
                        COUNT(CASE WHEN estatus = 'En mantenimiento' THEN 1 END) AS reparacion
                    FROM inventario WHERE is_active = TRUE AND estatus <> {$estBaja}");
        return $db->single();
    }

    // =========================================================================
    // Reporte de Bienes Dados de Baja (desincorporaciones)
    //
    // ⚠️ H-16 — este reporte medía la PAPELERA, no las desincorporaciones.
    // Desde la mig. 062 una baja es `estatus = 'Desincorporado'` (antes 'Dado de baja', mig. 076) CONSERVANDO
    // `is_active = TRUE`: el bien sale del inventario activo pero su registro se
    // preserva (B-38). `is_active = FALSE` es otra cosa: la papelera de registros
    // creados por error. Las tres consultas filtraban por la papelera, así que
    // una desincorporación real nunca aparecía y un registro borrado por
    // equivocación sí, contado como baja.
    //
    // Además la fecha salía de `deleted_at` (cuándo se borró el registro) en vez
    // de la fecha del movimiento de Baja, y "dado de baja por" de `deleted_by`.
    //
    // Las tres copias casi idénticas de la consulta son la razón por la que el
    // error sobrevivió a la reconstrucción del módulo: ahora hay UN solo origen
    // (`bajasQuery()`), que los tres consumen.
    // =========================================================================

    /** Filtros comunes del reporte, leídos de la query string. */
    private function bajasFiltros(): array {
        return [
            'fi'        => trim($_GET['fecha_inicio'] ?? ''),
            'ff'        => trim($_GET['fecha_fin']    ?? ''),
            'categoria' => trim($_GET['categoria']    ?? ''),
        ];
    }

    /**
     * Bienes desincorporados, con la fecha y el autor del ACTO de baja.
     * Fuente única del listado y de las dos exportaciones.
     */
    private function bajasQuery(array $f): array {
        $db = new Database();

        // La fecha del reporte es la del movimiento de Baja; si por alguna razón
        // ese movimiento no existe, se cae a updated_at para no ocultar la fila.
        $where = "i.is_active = TRUE AND i.estatus = :baja";
        if ($f['fi'])        $where .= " AND COALESCE(ai_baja.fecha, i.updated_at::date) >= :fi::date";
        if ($f['ff'])        $where .= " AND COALESCE(ai_baja.fecha, i.updated_at::date) <= :ff::date";
        if ($f['categoria']) $where .= " AND c.nombre ILIKE :categoria";

        $db->query("SELECT i.codigo_bn, i.nombre, i.condicion, i.marca, i.modelo, i.serial,
                           c.nombre AS categoria,
                           u.nombre AS ubicacion,
                           COALESCE(ai_baja.fecha, i.updated_at::date) AS fecha_baja,
                           ai_baja.usuario   AS dado_baja_por,
                           ai_baja.descripcion AS motivo_baja,
                           i.retirado_alcaldia, i.fecha_retiro
                    FROM inventario i
                    LEFT JOIN categorias  c ON i.id_categoria = c.id
                    LEFT JOIN ubicaciones u ON i.id_ubicacion = u.id
                    LEFT JOIN LATERAL (
                        SELECT ai.fecha, ai.descripcion, us.username AS usuario
                          FROM actividad_inventario ai
                          LEFT JOIN usuarios us ON ai.created_by = us.id
                         WHERE ai.id_inventario = i.id
                           AND ai.tipo_movimiento = 'Baja'
                           AND ai.is_active = TRUE
                         ORDER BY ai.fecha DESC, ai.id DESC LIMIT 1
                    ) ai_baja ON TRUE
                    WHERE {$where}
                    ORDER BY fecha_baja DESC NULLS LAST, i.nombre ASC");
        $db->bind(':baja', Inventario::EST_BAJA);
        if ($f['fi'])        $db->bind(':fi', $f['fi']);
        if ($f['ff'])        $db->bind(':ff', $f['ff']);
        if ($f['categoria']) $db->bind(':categoria', '%' . $f['categoria'] . '%');
        return $db->resultSet();
    }

    /** Totales del reporte: histórico completo y desincorporaciones del año. */
    private function bajasTotales(): array {
        $db = new Database();
        $db->query("SELECT
                        COUNT(*) AS total,
                        COUNT(CASE WHEN EXTRACT(YEAR FROM COALESCE(ai.fecha, i.updated_at::date))
                                        = EXTRACT(YEAR FROM CURRENT_DATE) THEN 1 END) AS este_anio,
                        COUNT(CASE WHEN i.retirado_alcaldia THEN 1 END) AS retirados
                    FROM inventario i
                    LEFT JOIN LATERAL (
                        SELECT fecha FROM actividad_inventario
                         WHERE id_inventario = i.id AND tipo_movimiento = 'Baja' AND is_active = TRUE
                         ORDER BY fecha DESC, id DESC LIMIT 1
                    ) ai ON TRUE
                    WHERE i.is_active = TRUE AND i.estatus = :baja");
        $db->bind(':baja', Inventario::EST_BAJA);
        $r = $db->single();
        return [
            'total'     => (int)($r->total     ?? 0),
            'este_anio' => (int)($r->este_anio ?? 0),
            'retirados' => (int)($r->retirados ?? 0),
        ];
    }

    public function bajasInventario() {
        $this->requireRoles([1, 4]);
        try {
            $f       = $this->bajasFiltros();
            $bajas   = $this->bajasQuery($f);
            $totales = $this->bajasTotales();

            $data = [
                'titulo'       => 'Bienes Desincorporados',
                'bajas'        => $bajas,
                'total_hist'   => $totales['total'],
                'bajas_anio'   => $totales['este_anio'],
                'retirados'    => $totales['retirados'],
                'fecha_inicio' => $f['fi'],
                'fecha_fin'    => $f['ff'],
                'filtro_cat'   => $f['categoria'],
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
            $bajas = $this->bajasQuery($this->bajasFiltros());

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición', 'Marca', 'Modelo', 'Serial',
                        'Fecha de baja', 'Desincorporado por', 'Motivo', 'Retiro de la Alcaldía'];
            $rows    = [];
            foreach ($bajas as $b) {
                $rows[] = [
                    $b->codigo_bn     ?? 'S/N',
                    $b->nombre,
                    $b->categoria     ?? '-',
                    $b->ubicacion     ?? '-',
                    $b->condicion,
                    $b->marca         ?? '-',
                    $b->modelo        ?? '-',
                    $b->serial        ?? '-',
                    $b->fecha_baja ? date('d/m/Y', strtotime($b->fecha_baja)) : '-',
                    $b->dado_baja_por ?? '-',
                    $b->motivo_baja   ?? '-',
                    $b->retirado_alcaldia
                        ? ('Retirado' . ($b->fecha_retiro ? ' ' . date('d/m/Y', strtotime($b->fecha_retiro)) : ''))
                        : 'Por retirar',
                ];
            }
            $this->exportCsv('bienes_desincorporados', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarBajasInventarioPdf() {
        $this->requireRoles([1, 4]);
        try {
            $f       = $this->bajasFiltros();
            $bajas   = $this->bajasQuery($f);
            $totales = $this->bajasTotales();

            $headers = ['Código BN', 'Nombre', 'Categoría', 'Ubicación', 'Condición',
                        'Fecha de baja', 'Desincorporado por', 'Motivo', 'Retiro'];
            $rows    = [];
            foreach ($bajas as $b) {
                $rows[] = [
                    $b->codigo_bn     ?? 'S/N',
                    $b->nombre,
                    $b->categoria     ?? '-',
                    $b->ubicacion     ?? '-',
                    $b->condicion,
                    $b->fecha_baja ? date('d/m/Y', strtotime($b->fecha_baja)) : '-',
                    $b->dado_baja_por ?? '-',
                    $b->motivo_baja   ?? '-',
                    $b->retirado_alcaldia ? 'Retirado' : 'Por retirar',
                ];
            }
            $kpis = [
                'Total histórico' => $totales['total'],
                'Este año'        => $totales['este_anio'],
                'Ya retirados'    => $totales['retirados'],
                'Filtrados'       => count($bajas),
                'Período'         => ($f['fi'] && $f['ff']) ? "{$f['fi']} a {$f['ff']}" : 'Todo el historial',
            ];
            $this->exportPdf("Bienes Desincorporados", "IMATUR — Control Patrimonial — Desincorporaciones", $headers, $rows, $kpis);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar PDF: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }
}
