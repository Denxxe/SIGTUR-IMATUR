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

    /**
     * Query string con los filtros actuales para los enlaces de exportación,
     * EXCLUYENDO la clave 'url' del enrutador (.htaccess usa ?url=… con QSA;
     * si se arrastra, el enlace volvería al reporte en vez de exportar).
     */
    private function qsFiltros(): string {
        $q = $_GET;
        unset($q['url']);
        return http_build_query($q);
    }

    /**
     * Renderiza un reporte tabular: HTML normal, o PDF con membrete institucional
     * (misma plantilla/estilo que el Excel) cuando ?formato=pdf. Reutiliza
     * reportes/pdf_template aplanando las celdas con HTML (badges) a texto.
     */
    private function renderReporte(array $data) {
        if (($_GET['formato'] ?? '') === 'pdf') {
            $rows = [];
            foreach ($data['filas'] ?? [] as $f) {
                $r = [];
                foreach ($f as $c) {
                    $r[] = is_array($c)
                        ? trim(html_entity_decode(strip_tags($c['raw'] ?? ''), ENT_QUOTES, 'UTF-8'))
                        : (string)$c;
                }
                $rows[] = $r;
            }
            $this->exportPdf($data['titulo'] ?? 'Reporte', $data['subtitulo'] ?? '', $data['columnas'] ?? [], $rows, $data['resumen'] ?? []);
            return;
        }
        $this->view('reportes/tabla', $data);
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
                'tolerancia'    => Asistencia::toleranciaPuntualidad(),
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
            $tol       = Asistencia::toleranciaPuntualidad();
            $headers   = ['Fecha', 'Cédula', 'Nombre', 'Apellido', 'Departamento', 'Tipo Contrato', 'Entrada', 'Salida', 'Horas', 'Puntualidad', 'Observación'];
            $rows      = [];
            foreach ($registros as $r) {
                $punt = $r->minutos_tarde === null ? 'Sin horario' : ((int)$r->minutos_tarde > $tol ? 'Impuntual (' . $r->minutos_tarde . ' min)' : 'Puntual');
                $rows[] = [
                    $r->fecha,
                    $r->cedula,
                    $r->nombre,
                    $r->apellido,
                    $r->departamento,
                    $r->tipo_contrato ?? '-',
                    $r->hora_entrada,
                    $r->hora_salida ?? '',
                    $r->horas !== null ? $r->horas : '',
                    $punt,
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

            $tol     = Asistencia::toleranciaPuntualidad();
            $headers = ['Fecha', 'Empleado', 'Cédula', 'Departamento', 'Entrada', 'Salida', 'Horas', 'Puntualidad'];
            $rows    = [];
            foreach ($registros as $r) {
                $punt = $r->minutos_tarde === null ? 'Sin horario' : ((int)$r->minutos_tarde > $tol ? 'Impuntual' : 'Puntual');
                $rows[] = [
                    $r->fecha,
                    $r->nombre . ' ' . $r->apellido,
                    $r->cedula,
                    $r->departamento,
                    $r->hora_entrada,
                    $r->hora_salida ?? '-',
                    $r->horas !== null ? $r->horas : '-',
                    $punt,
                ];
            }
            $kpis = [
                'Total Registros'    => $stats->total,
                'Empleados con Reg.' => $stats->empleados_unicos,
                'Impuntuales'        => $stats->impuntuales,
                'Horas totales'      => $stats->horas_totales,
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

        $db->query("SELECT a.fecha, a.hora_entrada, a.hora_salida, a.observacion, a.minutos_tarde,
                           CASE WHEN a.hora_salida IS NOT NULL
                                THEN ROUND(EXTRACT(EPOCH FROM (a.hora_salida - a.hora_entrada))/3600.0, 2)
                           END AS horas,
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

        $tol = Asistencia::toleranciaPuntualidad();
        $db->query("SELECT COUNT(*) as total,
                           COUNT(DISTINCT a.id_empleado) as empleados_unicos,
                           COUNT(DISTINCT a.fecha) as dias_con_registros,
                           COUNT(CASE WHEN a.minutos_tarde > :tol THEN 1 END) as impuntuales,
                           COALESCE(ROUND(SUM(CASE WHEN a.hora_salida IS NOT NULL
                                THEN EXTRACT(EPOCH FROM (a.hora_salida - a.hora_entrada))/3600.0 END)::numeric, 1), 0) as horas_totales
                    FROM asistencias a {$joins}
                    WHERE {$where}");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        $db->bind(':tol', $tol);
        if ($depto) $db->bind(':depto', (int)$depto);
        if ($busca) $db->bind(':busca', '%' . $busca . '%');
        return $db->single();
    }

    // =========================================================================
    // Reporte de Permisos y Reposos (R-8)
    // =========================================================================
    public function permisos() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryPermisos();
            if (($_GET['formato'] ?? '') === 'pdf') {
                $kpis = ['Total' => count($registros), 'Reposos' => 0, 'Permisos' => 0, 'En curso' => 0];
                $rows = [];
                foreach ($registros as $r) {
                    if ($r->categoria === 'Reposo') $kpis['Reposos']++; else $kpis['Permisos']++;
                    if ($r->estatus_periodo === 'En curso') $kpis['En curso']++;
                    $rows[] = [
                        trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                        $r->cedula, $r->departamento, $r->categoria, $r->tipo_permiso,
                        !empty($r->fecha_inicio) ? date('d/m/Y', strtotime($r->fecha_inicio)) : '—',
                        !empty($r->fecha_fin) ? date('d/m/Y', strtotime($r->fecha_fin)) : '—',
                        $r->duracion ?? ($r->dias_solicitados . ' días'),
                        $r->estatus_periodo, $r->estado,
                    ];
                }
                $this->exportPdf('Reporte de Permisos y Reposos', 'Reposos médicos y permisos laborales del personal, por período y estado.',
                    ['Empleado', 'Cédula', 'Departamento', 'Categoría', 'Tipo', 'Desde', 'Hasta', 'Duración', 'Período', 'Estado'], $rows, $kpis);
                return;
            }
            $data = [
                'titulo'        => 'Reporte de Permisos y Reposos',
                'registros'     => $registros,
                'fecha_inicio'  => $_GET['fecha_inicio'] ?? date('Y-01-01'),
                'fecha_fin'     => $_GET['fecha_fin']    ?? date('Y-m-d'),
                'filtro_cat'    => $_GET['categoria']    ?? '',
                'filtro_estado' => $_GET['estado']       ?? '',
            ];
            $this->view('reportes/permisos', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de permisos: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarPermisosCsv() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryPermisos();
            $headers = ['Empleado', 'Cédula', 'Departamento', 'Categoría', 'Tipo', 'Desde', 'Hasta', 'Duración', 'Período', 'Estado'];
            $rows = [];
            foreach ($registros as $r) {
                $rows[] = [
                    trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                    $r->cedula, $r->departamento, $r->categoria, $r->tipo_permiso,
                    $r->fecha_inicio, $r->fecha_fin,
                    $r->duracion ?? ($r->dias_solicitados . ' días'),
                    $r->estatus_periodo, $r->estado,
                ];
            }
            $this->exportCsv('reporte_permisos_reposos', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryPermisos() {
        $db  = new Database();
        $fi  = $_GET['fecha_inicio'] ?? date('Y-01-01');
        $ff  = $_GET['fecha_fin']    ?? date('Y-m-d');
        $cat = trim($_GET['categoria'] ?? '');
        $est = trim($_GET['estado']    ?? '');

        // Solapamiento de período: el permiso intersecta el rango [fi, ff]
        $where = "pl.is_active = TRUE AND pl.fecha_inicio <= :ff AND pl.fecha_fin >= :fi";
        if ($cat) $where .= " AND pl.categoria = :cat";
        if ($est) $where .= " AND pl.estado = :est";

        $db->query("SELECT pl.*, p.nombre, p.apellido, p.cedula, d.nombre AS departamento,
                           CASE WHEN pl.fecha_fin >= CURRENT_DATE THEN 'En curso' ELSE 'Concluido' END AS estatus_periodo
                    FROM permisos_laborales pl
                    INNER JOIN empleados e     ON pl.id_empleado    = e.id
                    INNER JOIN personas p      ON e.id_persona      = p.id
                    LEFT  JOIN departamentos d ON e.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY pl.fecha_inicio DESC, pl.id DESC");
        $db->bind(':fi', $fi);
        $db->bind(':ff', $ff);
        if ($cat) $db->bind(':cat', $cat);
        if ($est) $db->bind(':est', $est);
        return $db->resultSet();
    }

    // =========================================================================
    // Personal en comisión de servicio (origen Alcaldía / Gobernación)
    // =========================================================================
    public function comisionServicio() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryComision();
            // Resumen por institución de origen
            $resumen = [];
            foreach ($registros as $r) {
                $k = $r->institucion_origen ?? '—';
                $resumen[$k] = ($resumen[$k] ?? 0) + 1;
            }
            if (($_GET['formato'] ?? '') === 'pdf') {
                $rows = [];
                foreach ($registros as $r) {
                    $rows[] = [trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->cedula, $r->nro_expediente,
                               $r->cargo, $r->departamento, $r->institucion_origen,
                               !empty($r->fecha_ingreso) ? date('d/m/Y', strtotime($r->fecha_ingreso)) : '—',
                               Empleado::tiempoServicio($r->fecha_ingreso ?? null)];
                }
                $this->exportPdf('Personal en Comisión de Servicio', 'Personal proveniente de Alcaldía o Gobernación, con su tiempo de servicio.',
                    ['Empleado', 'Cédula', 'Expediente', 'Cargo', 'Departamento', 'Origen', 'F. Ingreso', 'Tiempo de servicio'], $rows, $resumen);
                return;
            }
            $data = [
                'titulo'    => 'Personal en Comisión de Servicio',
                'registros' => $registros,
                'resumen'   => $resumen,
            ];
            $this->view('reportes/comision', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarComisionCsv() {
        $this->requireRoles([1, 2]);
        try {
            $registros = $this->queryComision();
            $headers = ['Empleado', 'Cédula', 'Expediente', 'Cargo', 'Departamento', 'Origen', 'F. Ingreso', 'Tiempo de servicio'];
            $rows = [];
            foreach ($registros as $r) {
                $rows[] = [
                    trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                    $r->cedula, $r->nro_expediente, $r->cargo, $r->departamento,
                    $r->institucion_origen,
                    $r->fecha_ingreso,
                    Empleado::tiempoServicio($r->fecha_ingreso ?? null),
                ];
            }
            $this->exportCsv('personal_comision_servicio', $headers, $rows);
        } catch (Exception $e) {
            flash('global_msg', 'Error al exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    private function queryComision() {
        $db  = new Database();
        $org = trim($_GET['origen'] ?? '');
        $where = "e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL
                  AND e.institucion_origen <> 'IMATUR'";
        if (in_array($org, ['Alcaldía', 'Gobernación'], true)) $where .= " AND e.institucion_origen = :org";
        $db->query("SELECT p.cedula, p.nombre, p.apellido, e.nro_expediente, e.fecha_ingreso,
                           e.institucion_origen, c.nombre AS cargo, d.nombre AS departamento
                    FROM empleados e
                    INNER JOIN personas p      ON e.id_persona      = p.id
                    INNER JOIN cargos c        ON e.id_cargo        = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY e.institucion_origen ASC, p.nombre ASC");
        if (in_array($org, ['Alcaldía', 'Gobernación'], true)) $db->bind(':org', $org);
        return $db->resultSet();
    }

    // =========================================================================
    // Centro de Alertas — consolida avisos accionables de RRHH
    // =========================================================================
    public function alertas() {
        $this->requireRoles([1, 2]);
        $rol = (int)($_SESSION['user_rol'] ?? 0);
        // Fuente única: CentroAlertas (compartida con la campana del header).
        // Recalcula en fresco y refresca el cache de la campana (el usuario viene a actuar).
        CentroAlertas::invalidarCache();
        $this->view('reportes/alertas', ['titulo' => 'Centro de Alertas', 'alertas' => CentroAlertas::resumenCacheado($rol)]);
    }

    // =========================================================================
    // RRHH — Directorio de personal
    // =========================================================================
    public function directorio() {
        $this->requireRoles([1, 2]);
        $regs = $this->queryDirectorio();
        $filas = [];
        foreach ($regs as $r) {
            $esCom = ($r->institucion_origen ?? 'IMATUR') !== 'IMATUR';
            $vencTxt = ($r->tipo_contrato ?? '') === 'Fijo'
                ? 'Indefinido'
                : (!empty($r->fecha_vencimiento_contrato) ? date('d/m/Y', strtotime($r->fecha_vencimiento_contrato)) : '—');
            $filas[] = [
                trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                $r->cedula ?? '—',
                ['raw' => '<span class="sig-badge sig-badge--info">' . htmlspecialchars($r->cargo ?? '—') . '</span>'],
                $r->departamento ?? '—',
                $r->clasificacion ?? '—',
                $r->tipo_contrato ?? '—',
                $vencTxt,
                ['raw' => $esCom
                    ? '<span class="sig-badge sig-badge--warning">' . htmlspecialchars($r->institucion_origen) . '</span>'
                    : '<span class="sig-badge sig-badge--neutral">IMATUR</span>'],
                !empty($r->fecha_ingreso) ? date('d/m/Y', strtotime($r->fecha_ingreso)) : '—',
                $r->telefono ?? '—',
                $r->correo ?? '—',
            ];
        }
        $deptos = []; foreach (Departamento::all() as $d) $deptos[$d->id] = $d->nombre;
        $cargos = []; foreach (Cargo::all() as $c) $cargos[$c->id] = $c->nombre;
        $this->renderReporte([
            'eyebrow' => 'RRHH · Reporte', 'titulo' => 'Directorio de Personal',
            'subtitulo' => 'Plantilla activa del instituto con filtros por área, cargo, clasificación, contrato y origen.',
            'resumen' => ['Total' => count($regs)],
            'columnas' => ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'Clasificación', 'Contrato', 'Vencimiento', 'Origen', 'F. Ingreso', 'Teléfono', 'Correo'],
            'filas' => $filas,
            'accion' => URL_ROOT . '/reportes/directorio',
            'filtros' => [
                ['name' => 'buscar', 'label' => 'Buscar', 'type' => 'text', 'placeholder' => 'Nombre o cédula…', 'value' => $_GET['buscar'] ?? ''],
                ['name' => 'departamento', 'label' => 'Departamento', 'type' => 'select', 'options' => ['' => 'Todos'] + $deptos, 'value' => $_GET['departamento'] ?? ''],
                ['name' => 'cargo', 'label' => 'Cargo', 'type' => 'select', 'options' => ['' => 'Todos'] + $cargos, 'value' => $_GET['cargo'] ?? ''],
                ['name' => 'clasificacion', 'label' => 'Clasificación', 'type' => 'select', 'options' => array_merge(['' => 'Todas'], array_combine(Empleado::CLASIFICACIONES, Empleado::CLASIFICACIONES)), 'value' => $_GET['clasificacion'] ?? ''],
                ['name' => 'origen', 'label' => 'Origen', 'type' => 'select', 'options' => ['' => 'Todos', 'comision' => 'Comisión de servicio'] + array_combine(Empleado::INSTITUCIONES_ORIGEN, Empleado::INSTITUCIONES_ORIGEN), 'value' => $_GET['origen'] ?? ''],
            ],
            'export_url' => URL_ROOT . '/reportes/exportarDirectorioCsv?' . $this->qsFiltros(),
        ]);
    }

    public function exportarDirectorioCsv() {
        $this->requireRoles([1, 2]);
        $regs = $this->queryDirectorio();
        $rows = [];
        foreach ($regs as $r) {
            $venc = ($r->tipo_contrato ?? '') === 'Fijo' ? 'Indefinido' : ($r->fecha_vencimiento_contrato ?? '—');
            $rows[] = [trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->cedula, $r->cargo, $r->departamento,
                       $r->clasificacion, $r->tipo_contrato, $venc, $r->institucion_origen, $r->fecha_ingreso,
                       $r->telefono, $r->correo];
        }
        $this->exportCsv('directorio_personal', ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'Clasificación', 'Contrato', 'Vencimiento', 'Origen', 'F. Ingreso', 'Teléfono', 'Correo'], $rows);
    }

    private function queryDirectorio() {
        $db = new Database();
        $binds = [];
        $where = "e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL";
        if (!empty($_GET['buscar']))       { $where .= " AND ((p.nombre||' '||p.apellido) ILIKE :q OR p.cedula ILIKE :q)"; $binds[':q'] = '%' . trim($_GET['buscar']) . '%'; }
        if (!empty($_GET['departamento'])) { $where .= " AND e.id_departamento = :dep"; $binds[':dep'] = (int)$_GET['departamento']; }
        if (!empty($_GET['cargo']))        { $where .= " AND e.id_cargo = :car"; $binds[':car'] = (int)$_GET['cargo']; }
        if (!empty($_GET['clasificacion'])){ $where .= " AND e.clasificacion = :cla"; $binds[':cla'] = trim($_GET['clasificacion']); }
        $org = trim($_GET['origen'] ?? '');
        if ($org === 'comision')                                       $where .= " AND e.institucion_origen <> 'IMATUR'";
        elseif (in_array($org, Empleado::INSTITUCIONES_ORIGEN, true)){ $where .= " AND e.institucion_origen = :org"; $binds[':org'] = $org; }
        $db->query("SELECT p.nombre, p.apellido, p.cedula, p.telefono, p.correo, e.clasificacion, e.tipo_contrato,
                           e.institucion_origen, e.fecha_ingreso, e.fecha_vencimiento_contrato,
                           c.nombre AS cargo, d.nombre AS departamento
                    FROM empleados e
                    INNER JOIN personas p      ON e.id_persona = p.id
                    INNER JOIN cargos c        ON e.id_cargo = c.id
                    INNER JOIN departamentos d ON e.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY p.nombre ASC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    // =========================================================================
    // RRHH — Amonestaciones y faltas
    // =========================================================================
    public function amonestaciones() {
        $this->requireRoles([1, 2]);
        $roster = Amonestacion::roster();
        $limite = Amonestacion::LIMITE_DESPIDO;
        $filas = []; $despido = 0; $conObs = 0;
        foreach ($roster as $r) {
            $am = (int)$r->amonestaciones;
            if ($am >= $limite) $despido++;
            if ($am > 0 || (int)$r->faltas > 0) $conObs++;
            $estadoBadge = $am >= $limite ? 'sig-badge--danger' : ($am === $limite - 1 ? 'sig-badge--warning' : ((int)$r->faltas >= 3 ? 'sig-badge--warning' : ($am > 0 || (int)$r->faltas > 0 ? 'sig-badge--info' : 'sig-badge--success')));
            $estadoTxt = $am >= $limite ? 'Causa de despido' : ($am === $limite - 1 ? 'En riesgo' : ((int)$r->faltas >= 3 ? 'Faltas acumuladas' : ($am > 0 || (int)$r->faltas > 0 ? 'Con observaciones' : 'Sin novedades')));
            $filas[] = [
                trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                $r->cedula ?? '—',
                $r->cargo ?? '—',
                $r->departamento ?? '—',
                $r->tipo_contrato ?? '—',
                (string)(int)$r->faltas,
                (int)$r->amonestaciones . '/' . $limite,
                !empty($r->ultima_fecha) ? date('d/m/Y', strtotime($r->ultima_fecha)) : '—',
                ['raw' => '<span class="sig-badge ' . $estadoBadge . '">' . $estadoTxt . '</span>'],
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'RRHH · Disciplina', 'titulo' => 'Amonestaciones y Faltas',
            'subtitulo' => "Conteo por empleado. {$limite} amonestaciones = causa de despido (Contratado).",
            'resumen' => ['Empleados' => count($roster), 'Con observaciones' => $conObs, 'En causa de despido' => $despido],
            'columnas' => ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'Contrato', 'Faltas', 'Amonestaciones', 'Última fecha', 'Estado'],
            'filas' => $filas,
            'export_url' => URL_ROOT . '/reportes/exportarAmonestacionesCsv',
        ]);
    }

    public function exportarAmonestacionesCsv() {
        $this->requireRoles([1, 2]);
        $limite = Amonestacion::LIMITE_DESPIDO;
        $rows = [];
        foreach (Amonestacion::roster() as $r) {
            $rows[] = [trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->cedula, $r->cargo, $r->departamento, $r->tipo_contrato,
                       (int)$r->faltas, (int)$r->amonestaciones . '/' . $limite, $r->ultima_fecha];
        }
        $this->exportCsv('amonestaciones_faltas', ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'Contrato', 'Faltas', 'Amonestaciones', 'Última fecha'], $rows);
    }

    // =========================================================================
    // RRHH — Egresos / rotación de personal
    // =========================================================================
    public function egresos() {
        $this->requireRoles([1, 2]);
        $regs = $this->queryEgresos();
        $filas = []; $porMotivo = [];
        foreach ($regs as $r) {
            $porMotivo[$r->motivo_egreso] = ($porMotivo[$r->motivo_egreso] ?? 0) + 1;
            $filas[] = [
                trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                $r->cedula ?? '—',
                $r->cargo ?? '—',
                $r->departamento ?? '—',
                !empty($r->fecha_egreso) ? date('d/m/Y', strtotime($r->fecha_egreso)) : '—',
                ['raw' => '<span class="sig-badge sig-badge--warning">' . htmlspecialchars($r->motivo_egreso ?? '—') . '</span>'],
                Empleado::tiempoServicio($r->fecha_ingreso ?? null, $r->fecha_egreso ?? null),
                $r->observacion ?: '—',
                ['raw' => !empty($r->fecha_reingreso)
                    ? date('d/m/Y', strtotime($r->fecha_reingreso))
                    : '<span class="text-muted">— vigente —</span>'],
            ];
        }
        $resumen = ['Total egresos' => count($regs)] + $porMotivo;
        $this->renderReporte([
            'eyebrow' => 'RRHH · Reporte', 'titulo' => 'Egresos y Rotación de Personal',
            'subtitulo' => 'Personal desincorporado por motivo y período (renuncias, despidos, jubilaciones…).',
            'resumen' => $resumen,
            'columnas' => ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'F. Egreso', 'Motivo', 'Tiempo de servicio', 'Observación', 'F. Reingreso'],
            'filas' => $filas,
            'accion' => URL_ROOT . '/reportes/egresos',
            'filtros' => [
                ['name' => 'motivo', 'label' => 'Motivo', 'type' => 'select', 'options' => array_merge(['' => 'Todos'], array_combine(Empleado::MOTIVOS_EGRESO, Empleado::MOTIVOS_EGRESO)), 'value' => $_GET['motivo'] ?? ''],
                ['name' => 'fecha_desde', 'label' => 'Desde', 'type' => 'date', 'value' => $_GET['fecha_desde'] ?? ''],
                ['name' => 'fecha_hasta', 'label' => 'Hasta', 'type' => 'date', 'value' => $_GET['fecha_hasta'] ?? ''],
            ],
            'export_url' => URL_ROOT . '/reportes/exportarEgresosCsv?' . $this->qsFiltros(),
            'vacio' => 'No hay egresos registrados para el filtro.',
        ]);
    }

    public function exportarEgresosCsv() {
        $this->requireRoles([1, 2]);
        $rows = [];
        foreach ($this->queryEgresos() as $r) {
            $rows[] = [trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->cedula, $r->cargo, $r->departamento,
                       $r->fecha_egreso, $r->motivo_egreso, Empleado::tiempoServicio($r->fecha_ingreso ?? null, $r->fecha_egreso ?? null),
                       $r->observacion, $r->fecha_reingreso];
        }
        $this->exportCsv('egresos_personal', ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'F. Egreso', 'Motivo', 'Tiempo de servicio', 'Observación', 'F. Reingreso'], $rows);
    }

    private function queryEgresos() {
        $db = new Database();
        $binds = [];
        $where = "1=1";
        if (!empty($_GET['motivo']))      { $where .= " AND ee.motivo_egreso = :m"; $binds[':m'] = trim($_GET['motivo']); }
        if (!empty($_GET['fecha_desde'])) { $where .= " AND ee.fecha_egreso >= :fd"; $binds[':fd'] = trim($_GET['fecha_desde']); }
        if (!empty($_GET['fecha_hasta'])) { $where .= " AND ee.fecha_egreso <= :fh"; $binds[':fh'] = trim($_GET['fecha_hasta']); }
        $db->query("SELECT ee.fecha_egreso, ee.motivo_egreso, ee.observacion, ee.fecha_reingreso,
                           p.nombre, p.apellido, p.cedula, c.nombre AS cargo, d.nombre AS departamento,
                           e.fecha_ingreso
                    FROM empleados_egresos ee
                    INNER JOIN empleados e ON ee.id_empleado = e.id
                    INNER JOIN personas p  ON e.id_persona = p.id
                    LEFT  JOIN cargos c    ON e.id_cargo = c.id
                    LEFT  JOIN departamentos d ON e.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY ee.fecha_egreso DESC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    // =========================================================================
    // RRHH — Constancias de trabajo emitidas
    // =========================================================================
    public function constancias() {
        $this->requireRoles([1, 2]);
        $regs = $this->queryConstancias();
        $filas = [];
        foreach ($regs as $r) {
            $filas[] = [
                ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->numero) . '</span>'],
                Constancia::labelTipo($r->tipo ?? ''),
                trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                $r->cedula ?? '—',
                $r->cargo ?? '—',
                $r->departamento ?? '—',
                !empty($r->fecha_emision) ? date('d/m/Y H:i', strtotime($r->fecha_emision)) : '—',
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'RRHH · Reporte', 'titulo' => 'Constancias Emitidas',
            'subtitulo' => 'Bitácora de constancias de trabajo generadas, con su correlativo.',
            'resumen' => ['Total emitidas' => count($regs)],
            'columnas' => ['N° Documento', 'Tipo', 'Empleado', 'Cédula', 'Cargo', 'Departamento', 'Emisión'],
            'filas' => $filas,
            'accion' => URL_ROOT . '/reportes/constancias',
            'filtros' => [
                ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'options' => array_merge(['' => 'Todos'], Constancia::TIPOS), 'value' => $_GET['tipo'] ?? ''],
                ['name' => 'fecha_desde', 'label' => 'Desde', 'type' => 'date', 'value' => $_GET['fecha_desde'] ?? ''],
                ['name' => 'fecha_hasta', 'label' => 'Hasta', 'type' => 'date', 'value' => $_GET['fecha_hasta'] ?? ''],
            ],
            'export_url' => URL_ROOT . '/reportes/exportarConstanciasCsv?' . $this->qsFiltros(),
        ]);
    }

    public function exportarConstanciasCsv() {
        $this->requireRoles([1, 2]);
        $rows = [];
        foreach ($this->queryConstancias() as $r) {
            $rows[] = [$r->numero, Constancia::labelTipo($r->tipo ?? ''), trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')), $r->cedula, $r->cargo, $r->departamento, $r->fecha_emision];
        }
        $this->exportCsv('constancias_emitidas', ['N° Documento', 'Tipo', 'Empleado', 'Cédula', 'Cargo', 'Departamento', 'Emisión'], $rows);
    }

    private function queryConstancias() {
        $db = new Database();
        $binds = [];
        $where = "co.is_active = TRUE";
        if (!empty($_GET['fecha_desde'])) { $where .= " AND co.fecha_emision >= :fd"; $binds[':fd'] = trim($_GET['fecha_desde']); }
        if (!empty($_GET['fecha_hasta'])) { $where .= " AND co.fecha_emision <= (:fh::date + 1)"; $binds[':fh'] = trim($_GET['fecha_hasta']); }
        if (!empty($_GET['tipo']))        { $where .= " AND co.tipo = :tipo"; $binds[':tipo'] = trim($_GET['tipo']); }
        $db->query("SELECT co.numero, co.tipo, co.fecha_emision, p.nombre, p.apellido, p.cedula,
                           c.nombre AS cargo, d.nombre AS departamento
                    FROM constancias co
                    INNER JOIN empleados e      ON co.id_empleado = e.id
                    INNER JOIN personas p       ON e.id_persona = p.id
                    LEFT  JOIN cargos c         ON e.id_cargo = c.id
                    LEFT  JOIN departamentos d  ON e.id_departamento = d.id
                    WHERE {$where}
                    ORDER BY co.fecha_emision DESC");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    // =========================================================================
    // RRHH — Expedientes incompletos (recaudos obligatorios faltantes)
    // =========================================================================
    public function expedientesIncompletos() {
        $this->requireRoles([1, 2]);
        // Consultas agregadas (sin N+1): faltantes por empleado + recaudos entregados.
        $faltMap   = ExpedienteDocumento::faltantesObligatorios();
        $entregMap = ExpedienteDocumento::entregadosPorEmpleado();
        $filas = []; $totalIncompletos = 0;
        foreach (Empleado::all() as $e) {
            $faltan = (int)($faltMap[(int)$e->id] ?? 0);
            if ($faltan <= 0) continue;
            $totalIncompletos++;
            $entregados = $entregMap[(int)$e->id] ?? [];
            $faltantes = [];
            foreach (ExpedienteDocumento::RECAUDOS as $clave => [$label, $obligatorio]) {
                if ($obligatorio && empty($entregados[$clave])) $faltantes[] = $label;
            }
            $filas[] = [
                trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')),
                $e->cedula ?? '—',
                $e->cargo ?? '—',
                ['raw' => '<span class="sig-badge sig-badge--danger">' . $faltan . '</span>'],
                implode(', ', $faltantes),
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'RRHH · Reporte', 'titulo' => 'Expedientes Incompletos',
            'subtitulo' => 'Personal con recaudos OBLIGATORIOS faltantes en su expediente.',
            'resumen' => ['Con recaudos faltantes' => $totalIncompletos],
            'columnas' => ['Empleado', 'Cédula', 'Cargo', 'Faltan', 'Recaudos faltantes'],
            'filas' => $filas,
            'vacio' => '¡Todos los expedientes tienen sus recaudos obligatorios completos!',
        ]);
    }

    // =========================================================================
    // RRHH — Carga familiar del personal (detallado, con filtros configurables)
    // =========================================================================
    public function cargaFamiliar() {
        $this->requireRoles([1, 2]);
        $regs = $this->queryCargaFamiliar();
        $filas = []; $empleados = [];
        foreach ($regs as $r) {
            $empleados[$r->emp_cedula ?: $r->emp_nombre . $r->emp_apellido] = true;
            $vive = ($r->vive === true || $r->vive === 't' || $r->vive === null);
            $sexo = $r->genero === 'M' ? 'Masculino' : ($r->genero === 'F' ? 'Femenino' : '—');
            $filas[] = [
                ['raw' => '<span class="cell-strong">' . htmlspecialchars(trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? ''))) . '</span>'],
                $r->emp_cedula ?? '—',
                $r->cargo ?? '—',
                $r->departamento ?? '—',
                $r->fam_nombre ?? '—',
                ['raw' => '<span class="sig-badge sig-badge--info">' . htmlspecialchars($r->parentesco ?? '—') . '</span>'],
                $sexo,
                !empty($r->fecha_nacimiento) ? date('d/m/Y', strtotime($r->fecha_nacimiento)) : '—',
                ($r->edad !== null ? $r->edad . ' años' : '—'),
                ['raw' => '<span class="sig-badge ' . ($vive ? 'sig-badge--success' : 'sig-badge--danger') . '">' . ($vive ? 'Vivo' : 'Fallecido') . '</span>'],
                $r->fam_cedula ?? '—',
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'RRHH · Reporte', 'titulo' => 'Carga Familiar del Personal',
            'subtitulo' => 'Detalle de la carga familiar de cada trabajador, con filtros configurables.',
            'resumen' => ['Trabajadores' => count($empleados), 'Familiares' => count($regs)],
            'columnas' => ['Empleado', 'C.I. Empleado', 'Cargo', 'Departamento', 'Familiar', 'Parentesco', 'Sexo', 'F. Nacimiento', 'Edad', 'Estado', 'C.I. Familiar'],
            'filas' => $filas,
            'accion' => URL_ROOT . '/reportes/cargaFamiliar',
            'filtros' => [
                ['name' => 'buscar', 'label' => 'Buscar', 'type' => 'text', 'placeholder' => 'Empleado o familiar…', 'value' => $_GET['buscar'] ?? ''],
                ['name' => 'parentesco', 'label' => 'Parentesco', 'type' => 'select', 'options' => array_merge(['' => 'Todos'], array_combine(CargaFamiliar::PARENTESCOS, CargaFamiliar::PARENTESCOS)), 'value' => $_GET['parentesco'] ?? ''],
                ['name' => 'sexo', 'label' => 'Sexo', 'type' => 'select', 'options' => ['' => 'Todos', 'M' => 'Masculino', 'F' => 'Femenino'], 'value' => $_GET['sexo'] ?? ''],
                ['name' => 'estado', 'label' => 'Estado', 'type' => 'select', 'options' => ['' => 'Todos', 'vivo' => 'Vivo', 'fallecido' => 'Fallecido'], 'value' => $_GET['estado'] ?? ''],
                ['name' => 'edad_min', 'label' => 'Edad mín.', 'type' => 'number', 'value' => $_GET['edad_min'] ?? ''],
                ['name' => 'edad_max', 'label' => 'Edad máx.', 'type' => 'number', 'value' => $_GET['edad_max'] ?? ''],
                ['name' => 'min_fam', 'label' => 'N° familiares ≥', 'type' => 'number', 'placeholder' => 'ej: 3', 'value' => $_GET['min_fam'] ?? ''],
            ],
            'export_url' => URL_ROOT . '/reportes/exportarCargaFamiliarCsv?' . $this->qsFiltros(),
            'vacio' => 'No hay carga familiar registrada para el filtro (o aún no se ha cargado).',
        ]);
    }

    public function exportarCargaFamiliarCsv() {
        $this->requireRoles([1, 2]);
        $rows = [];
        foreach ($this->queryCargaFamiliar() as $r) {
            $vive = ($r->vive === true || $r->vive === 't' || $r->vive === null);
            $rows[] = [
                trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? '')), $r->emp_cedula, $r->cargo, $r->departamento,
                $r->fam_nombre, $r->parentesco,
                $r->genero === 'M' ? 'Masculino' : ($r->genero === 'F' ? 'Femenino' : ''),
                $r->fecha_nacimiento, ($r->edad !== null ? $r->edad . ' años' : ''),
                $vive ? 'Vivo' : 'Fallecido', $r->fam_cedula,
            ];
        }
        $this->exportCsv('carga_familiar', ['Empleado', 'C.I. Empleado', 'Cargo', 'Departamento', 'Familiar', 'Parentesco', 'Sexo', 'F. Nacimiento', 'Edad', 'Estado', 'C.I. Familiar'], $rows);
    }

    private function queryCargaFamiliar() {
        $db = new Database();
        $binds = [];
        $w = "e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL AND cf.is_active = TRUE";
        if (!empty($_GET['buscar']))      { $w .= " AND ((p.nombre||' '||p.apellido) ILIKE :q OR p.cedula ILIKE :q OR cf.nombre_apellido ILIKE :q)"; $binds[':q'] = '%' . trim($_GET['buscar']) . '%'; }
        if (!empty($_GET['parentesco']))  { $w .= " AND cf.parentesco = :par"; $binds[':par'] = trim($_GET['parentesco']); }
        if (in_array($_GET['sexo'] ?? '', ['M', 'F'], true)) { $w .= " AND cf.genero = :sx"; $binds[':sx'] = $_GET['sexo']; }
        $est = $_GET['estado'] ?? '';
        if ($est === 'vivo')           $w .= " AND COALESCE(cf.vive, TRUE) = TRUE";
        elseif ($est === 'fallecido')  $w .= " AND cf.vive = FALSE";
        if (($_GET['edad_min'] ?? '') !== '') { $w .= " AND cf.fecha_nacimiento IS NOT NULL AND EXTRACT(YEAR FROM age(cf.fecha_nacimiento)) >= :emin"; $binds[':emin'] = (int)$_GET['edad_min']; }
        if (($_GET['edad_max'] ?? '') !== '') { $w .= " AND cf.fecha_nacimiento IS NOT NULL AND EXTRACT(YEAR FROM age(cf.fecha_nacimiento)) <= :emax"; $binds[':emax'] = (int)$_GET['edad_max']; }
        $minFam = max(1, (int)($_GET['min_fam'] ?? 1));

        $db->query("SELECT * FROM (
                        SELECT p.nombre AS emp_nombre, p.apellido AS emp_apellido, p.cedula AS emp_cedula,
                               c.nombre AS cargo, d.nombre AS departamento,
                               cf.nombre_apellido AS fam_nombre, cf.parentesco, cf.genero, cf.cedula AS fam_cedula,
                               cf.fecha_nacimiento, COALESCE(cf.vive, TRUE) AS vive,
                               CASE WHEN cf.fecha_nacimiento IS NOT NULL THEN EXTRACT(YEAR FROM age(cf.fecha_nacimiento))::int END AS edad,
                               COUNT(*) OVER (PARTITION BY e.id) AS fam_count
                        FROM empleados e
                        INNER JOIN personas p      ON e.id_persona = p.id
                        INNER JOIN cargos c        ON e.id_cargo = c.id
                        INNER JOIN departamentos d ON e.id_departamento = d.id
                        INNER JOIN carga_familiar cf ON cf.id_persona = p.id
                        WHERE {$w}
                    ) z
                    WHERE z.fam_count >= :minfam
                    ORDER BY emp_nombre, emp_apellido, parentesco");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->bind(':minfam', $minFam);
        return $db->resultSet();
    }

    // =========================================================================
    // Formación — Cobertura comunitaria por parroquia (beneficiarios)
    // =========================================================================
    public function coberturaFormacion() {
        $this->requireRoles([1, 3]);
        $regs = $this->queryCoberturaFormacion();
        $filas = []; $totalPart = 0;
        foreach ($regs as $r) {
            $totalPart += (int)$r->participaciones;
            $filas[] = [
                $r->parroquia ?? '(Sin parroquia)',
                $r->municipio ?: '—',
                ['raw' => '<strong>' . (int)$r->participaciones . '</strong>'],
            ];
        }
        $this->renderReporte([
            'eyebrow' => 'Formación · Impacto', 'titulo' => 'Cobertura Comunitaria (Formación)',
            'subtitulo' => 'Participaciones en talleres y charlas agrupadas por parroquia (alcance territorial).',
            'resumen' => ['Participaciones' => $totalPart, 'Parroquias alcanzadas' => count($regs)],
            'columnas' => ['Parroquia', 'Municipio', 'Participaciones'],
            'filas' => $filas,
            'export_url' => URL_ROOT . '/reportes/exportarCoberturaCsv',
            'vacio' => 'Aún no hay participantes registrados en actividades de formación.',
        ]);
    }

    public function exportarCoberturaCsv() {
        $this->requireRoles([1, 3]);
        $rows = [];
        foreach ($this->queryCoberturaFormacion() as $r) {
            $rows[] = [$r->parroquia, $r->municipio, (int)$r->participaciones];
        }
        $this->exportCsv('cobertura_formacion_parroquia', ['Parroquia', 'Municipio', 'Participaciones'], $rows);
    }

    private function queryCoberturaFormacion() {
        $db = new Database();
        $db->query("SELECT parroquia, municipio, COUNT(*) AS participaciones
                    FROM (
                        SELECT COALESCE(par.nombre, '(Sin parroquia)') AS parroquia, m.nombre AS municipio
                        FROM participantes_taller pt
                        JOIN talleres t   ON pt.id_taller = t.id AND t.is_active = TRUE
                        JOIN personas p   ON pt.id_persona = p.id
                        LEFT JOIN parroquia par ON p.parroquia_id = par.id
                        LEFT JOIN municipio m   ON par.id_municipio = m.id
                        WHERE pt.is_active = TRUE AND pt.id_persona IS NOT NULL
                        UNION ALL
                        SELECT COALESCE(par.nombre, '(Sin parroquia)'), m.nombre
                        FROM participantes_taller pt
                        JOIN talleres t   ON pt.id_taller = t.id AND t.is_active = TRUE
                        LEFT JOIN parroquia par ON pt.parroquia_id_libre = par.id
                        LEFT JOIN municipio m   ON par.id_municipio = m.id
                        WHERE pt.is_active = TRUE AND pt.id_persona IS NULL
                    ) z
                    GROUP BY parroquia, municipio
                    ORDER BY participaciones DESC, parroquia ASC");
        return $db->resultSet();
    }

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
    // Transversal — Auditoría (bitácora exportable)
    // =========================================================================
    public function auditoria() {
        $this->requireRoles([1]);
        $regs = $this->queryAuditoria();
        $filas = [];
        foreach ($regs as $r) {
            $opBadge = ['INSERT' => 'sig-badge--success', 'UPDATE' => 'sig-badge--info', 'DELETE' => 'sig-badge--danger'][$r->operacion] ?? 'sig-badge--neutral';
            $filas[] = [
                !empty($r->fecha) ? date('d/m/Y H:i', strtotime($r->fecha)) : '—',
                $r->actor ?: 'Sistema',
                ['raw' => '<span class="sig-badge ' . $opBadge . '">' . htmlspecialchars($r->operacion ?? '—') . '</span>'],
                $r->tabla_afectada ?? '—',
                (string)($r->record_id ?? '—'),
                $r->ip_direccion ?? '—',
            ];
        }
        // Catálogo de tablas para el filtro
        $db = new Database();
        $db->query("SELECT DISTINCT tabla_afectada FROM audit_logs WHERE tabla_afectada IS NOT NULL ORDER BY tabla_afectada");
        $tablas = ['' => 'Todas'];
        foreach ($db->resultSet() as $t) $tablas[$t->tabla_afectada] = $t->tabla_afectada;
        $this->renderReporte([
            'eyebrow' => 'Seguridad · Reporte', 'titulo' => 'Auditoría del Sistema',
            'subtitulo' => 'Bitácora de cambios (máx. 1000 registros recientes según filtro). Para exploración completa use el módulo de Auditoría.',
            'resumen' => ['Eventos mostrados' => count($regs)],
            'columnas' => ['Fecha', 'Usuario', 'Operación', 'Tabla', 'Registro', 'IP'],
            'filas' => $filas,
            'accion' => URL_ROOT . '/reportes/auditoria',
            'filtros' => [
                ['name' => 'tabla', 'label' => 'Tabla', 'type' => 'select', 'options' => $tablas, 'value' => $_GET['tabla'] ?? ''],
                ['name' => 'operacion', 'label' => 'Operación', 'type' => 'select', 'options' => ['' => 'Todas', 'INSERT' => 'INSERT', 'UPDATE' => 'UPDATE', 'DELETE' => 'DELETE'], 'value' => $_GET['operacion'] ?? ''],
                ['name' => 'fecha_desde', 'label' => 'Desde', 'type' => 'date', 'value' => $_GET['fecha_desde'] ?? ''],
                ['name' => 'fecha_hasta', 'label' => 'Hasta', 'type' => 'date', 'value' => $_GET['fecha_hasta'] ?? ''],
            ],
            'export_url' => URL_ROOT . '/reportes/exportarAuditoriaCsv?' . $this->qsFiltros(),
            'vacio' => 'Sin eventos de auditoría para el filtro.',
        ]);
    }

    public function exportarAuditoriaCsv() {
        $this->requireRoles([1]);
        $rows = [];
        foreach ($this->queryAuditoria() as $r) {
            $rows[] = [$r->fecha, $r->actor, $r->operacion, $r->tabla_afectada, $r->record_id, $r->ip_direccion];
        }
        $this->exportCsv('auditoria_sistema', ['Fecha', 'Usuario', 'Operación', 'Tabla', 'Registro', 'IP'], $rows);
    }

    private function queryAuditoria() {
        $db = new Database();
        $binds = [];
        $where = "1=1";
        if (!empty($_GET['tabla']))       { $where .= " AND a.tabla_afectada = :t"; $binds[':t'] = trim($_GET['tabla']); }
        if (!empty($_GET['operacion']))   { $where .= " AND a.operacion = :op"; $binds[':op'] = trim($_GET['operacion']); }
        if (!empty($_GET['fecha_desde'])) { $where .= " AND a.fecha >= :fd"; $binds[':fd'] = trim($_GET['fecha_desde']); }
        if (!empty($_GET['fecha_hasta'])) { $where .= " AND a.fecha <= (:fh::date + 1)"; $binds[':fh'] = trim($_GET['fecha_hasta']); }
        $db->query("SELECT a.fecha, a.operacion, a.tabla_afectada, a.record_id, a.ip_direccion,
                           COALESCE(per.nombre || ' ' || per.apellido, u.username) AS actor
                    FROM audit_logs a
                    LEFT JOIN usuarios u   ON a.id_usuario  = u.id
                    LEFT JOIN empleados e  ON u.id_empleado = e.id
                    LEFT JOIN personas per ON e.id_persona  = per.id
                    WHERE {$where}
                    ORDER BY a.fecha DESC
                    LIMIT 1000");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
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
            $headers = ['Ruta', 'Tipo', 'Fecha Visita', 'Hora', 'Departamento', 'Guía', 'Estado', 'Tarifa',
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
                    !empty($r->tiene_tarifa) ? number_format((float)$r->tarifa_monto, 2) : 'Gratuita',
                    (int)$r->total_puntos,
                    (int)$r->total_participantes,
                    (int)($r->mujeres ?? 0),
                    (int)($r->hombres ?? 0),
                    (int)($r->ninas ?? 0),
                    (int)($r->ninos ?? 0),
                    (int)($r->total_atendidos ?? 0),
                ];
            }
            // Fila de totales
            $rows[] = ['TOTALES', '', '', '', '', '', '', '', '', $tpInsc, '', '', '', '', $tpAt];
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
                           COALESCE(p.nombre || ' ' || p.apellido, r.nombre_facilitador_externo) AS facilitador_nombre,
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

        $filasParticipantes = [];
        foreach ($participantes as $p) {
            $filasParticipantes[] = [
                $p->tipo, $p->cedula, $p->nombre,
                $p->nombre_docente, $p->cedula_docente,
                $p->asistio ? 'Presente' : 'Ausente',
            ];
        }

        $secciones = [
            [
                'titulo'  => 'Datos del Taller',
                'headers' => ['Campo', 'Valor'],
                'rows'    => [
                    ['Taller', $t->nombre],
                    ['Facilitador', $t->facilitador],
                    ['Lugar', $t->sede ?: 'No especificada'],
                    ['Fecha', $t->fecha_inicio],
                    ['Estado', $t->estado],
                ],
            ],
            [
                'titulo'  => 'Resumen Demográfico',
                'headers' => ['Mujeres', 'Hombres', 'Niñas (5-11)', 'Niños (5-11)', 'Total Atendidos'],
                'rows'    => [[
                    $inf->mujeres         ?? 0,
                    $inf->hombres         ?? 0,
                    $inf->ninas           ?? 0,
                    $inf->ninos           ?? 0,
                    $inf->total_atendidas ?? 0,
                ]],
            ],
            [
                'titulo'  => 'Listado de Personas Inscritas',
                'headers' => ['Tipo', 'Cédula', 'Nombre Completo', 'Docente/Tutor', 'C.I. Docente', 'Asistencia'],
                'rows'    => $filasParticipantes,
            ],
        ];

        $this->exportCsvSecciones('Dossier_Taller', 'Dossier Integral de Actividad — ' . $t->nombre, $secciones);
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
                               pp.cedula, pp.nombre, pp.apellido, pp.telefono, pp.correo,
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
                               pp.cedula, pp.nombre, pp.apellido, pp.telefono, pp.correo,
                               pt.nombre || ' ' || pt.apellido AS tutor
                        FROM pasantes p
                        INNER JOIN personas pp ON p.id_persona = pp.id
                        LEFT  JOIN empleados e  ON p.id_tutor_institucional = e.id
                        LEFT  JOIN personas pt  ON e.id_persona = pt.id
                        WHERE p.is_active = TRUE ORDER BY pp.cedula ASC");
            $pasantes = $db->resultSet();

            $headers = ['Cédula', 'Nombre', 'Apellido', 'Teléfono', 'Correo', 'Institución', 'Carrera', 'Tutor', 'Inicio', 'Fin', 'Estado', 'Nota', 'Evaluación'];
            $rows    = [];
            foreach ($pasantes as $p) {
                $rows[] = [$p->cedula, $p->nombre, $p->apellido, $p->telefono, $p->correo, $p->institucion, $p->carrera,
                           $p->tutor ?? 'N/A', $p->fecha_inicio, $p->fecha_fin, $p->estado, $p->nota, $p->evaluacion];
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

            $headers = ['Cédula', 'Nombre', 'Institución', 'Tutor', 'Estado', 'Nota'];
            $rows    = [];
            foreach ($pasantes as $p) {
                $rows[] = [$p->cedula, $p->nombre . ' ' . $p->apellido, $p->institucion, $p->tutor ?? '-', $p->estado, $p->nota ?? '-'];
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
    // =========================================================================
    // Posibles duplicados de participantes (control de registros basura)
    // =========================================================================
    public function duplicados() {
        $this->requireRoles([1, 3]);
        $db = new Database();

        // 1) Personas con la MISMA cédula normalizada (incluye colisiones que la
        //    normalización no pudo unificar y cédulas "basura" repetidas).
        $db->query("SELECT regexp_replace(cedula,'\\D','','g') AS cedula_norm,
                           COUNT(*) AS total,
                           string_agg(nombre || ' ' || apellido || ' (#' || id || ' · ' || cedula || ')', '  |  ' ORDER BY id) AS detalle
                    FROM personas
                    WHERE is_active = TRUE AND cedula IS NOT NULL
                      AND regexp_replace(cedula,'\\D','','g') <> ''
                    GROUP BY 1 HAVING COUNT(*) > 1
                    ORDER BY total DESC, cedula_norm");
        $dupCedula = $db->resultSet() ?: [];

        // 2) Personas (con cédula) que coinciden en nombre + apellido + fecha de
        //    nacimiento → posible misma persona registrada dos veces.
        $db->query("SELECT count(*) AS total, fecha_nacimiento AS fnac,
                           string_agg(nombre || ' ' || apellido || ' (#' || id || ' · C.I. ' || COALESCE(cedula,'—') || ')', '  |  ' ORDER BY id) AS detalle
                    FROM personas
                    WHERE is_active = TRUE AND fecha_nacimiento IS NOT NULL
                      AND nombre IS NOT NULL AND apellido IS NOT NULL
                    GROUP BY lower(trim(nombre)), lower(trim(apellido)), fecha_nacimiento
                    HAVING COUNT(*) > 1
                    ORDER BY total DESC");
        $dupPersona = $db->resultSet() ?: [];

        // 3) Participantes SIN cédula (libre) repetidos, unificando talleres y
        //    rutas. La identidad del menor se ancla en su REPRESENTANTE (cédula
        //    del adulto): se agrupa por nombre + apellido + fecha de nacimiento +
        //    cédula del representante. Así dos homónimos con representantes
        //    distintos NO se marcan como duplicados, y la misma persona (mismo
        //    representante) en varias actividades sí se detecta.
        $db->query("WITH libre AS (
                        SELECT trim(pt.nombre_libre) AS nom, trim(COALESCE(pt.apellido_libre,'')) AS ape,
                               pt.fecha_nac_libre AS fnac,
                               regexp_replace(COALESCE(pt.cedula_docente,''),'\\D','','g') AS ced_rep,
                               'Taller: ' || t.nombre AS actividad, t.fecha_inicio AS fecha
                        FROM participantes_taller pt JOIN talleres t ON pt.id_taller = t.id
                        WHERE pt.is_active = TRUE AND pt.id_persona IS NULL AND t.is_active = TRUE
                          AND pt.nombre_libre IS NOT NULL
                        UNION ALL
                        SELECT trim(pr.nombre_libre), trim(COALESCE(pr.apellido_libre,'')),
                               pr.fecha_nac_libre,
                               regexp_replace(COALESCE(pr.cedula_representante,''),'\\D','','g'),
                               'Ruta: ' || r.nombre, r.fecha_visita
                        FROM participantes_ruta pr JOIN rutas r ON pr.id_ruta = r.id
                        WHERE pr.is_active = TRUE AND pr.id_persona IS NULL AND r.is_active = TRUE
                          AND pr.nombre_libre IS NOT NULL
                    )
                    SELECT count(*) AS total, fnac,
                           NULLIF(max(ced_rep),'') AS ced_rep,
                           string_agg(nom || ' ' || ape || ' — ' || actividad || ' (' || COALESCE(to_char(fecha,'DD/MM/YYYY'),'s/f') || ')', '  |  ' ORDER BY fecha) AS detalle
                    FROM libre
                    GROUP BY lower(nom), lower(ape), fnac, ced_rep
                    HAVING COUNT(*) > 1
                    ORDER BY total DESC");
        $dupLibre = $db->resultSet() ?: [];

        $this->view('reportes/duplicados', [
            'titulo'     => 'Posibles duplicados de participantes',
            'dupCedula'  => $dupCedula,
            'dupPersona' => $dupPersona,
            'dupLibre'   => $dupLibre,
        ]);
    }

    public function indicadores() {
        try {
            $db = new Database();

            // Año del panel: configurable por ?anio (default = año del servidor).
            // Gobierna todos los indicadores anuales; las métricas "del mes" y las
            // tendencias "últimos N meses" siguen siendo relativas a hoy.
            $anioActual = (int)($_GET['anio'] ?? date('Y'));
            if ($anioActual < 2000 || $anioActual > (int)date('Y') + 1) $anioActual = (int)date('Y');

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
                        WHERE EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                          AND pt.is_active = TRUE AND t.is_active = TRUE");
            $db->bind(':anio', $anioActual);
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

            // ── F-3: Demografía de formación (año seleccionado) ───────────
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

                // ── T-1: Meta cobertura rutas ──────────────────────────────
                $db->query("SELECT valor FROM configuracion_sistema WHERE clave = 'meta_rutas_anio' LIMIT 1");
                $metaRutas = $db->single();

                // Meta = rutas EJECUTADAS (Finalizadas) en el año, por fecha de visita
                $db->query("SELECT COUNT(*) as total FROM rutas
                            WHERE is_active = TRUE AND estado = 'Finalizada'
                              AND EXTRACT(YEAR FROM COALESCE(fecha_visita, created_at)) = :anio");
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

            // ── RRHH: distribución por clasificación (Empleado/Obrero) ────────────────
            $db->query("SELECT COALESCE(NULLIF(TRIM(clasificacion), ''), 'Sin especificar') AS clasificacion,
                               COUNT(*) AS total
                        FROM empleados WHERE is_active = TRUE
                        GROUP BY COALESCE(NULLIF(TRIM(clasificacion), ''), 'Sin especificar')
                        ORDER BY total DESC");
            $empPorClasificacion = $db->resultSet();

            // ── RRHH: permisos/reposos vigentes hoy (aprobados, en curso) + pendientes ─
            $db->query("SELECT COALESCE(categoria, '—') AS categoria, COUNT(*) AS total
                        FROM permisos_laborales
                        WHERE is_active = TRUE AND estado = 'Aprobado'
                          AND CURRENT_DATE BETWEEN fecha_inicio AND fecha_fin
                        GROUP BY categoria ORDER BY total DESC");
            $permisosVigentes = $db->resultSet();
            $db->query("SELECT COUNT(*) AS total FROM permisos_laborales WHERE is_active = TRUE AND estado = 'Pendiente'");
            $permisosPendientes = (int)($db->single()->total ?? 0);

            // ── RRHH: amonestaciones (empleados con ≥1 y empleados en causa de despido ≥3) ─
            $db->query("SELECT COUNT(*) AS total, COUNT(DISTINCT id_empleado) AS empleados
                        FROM amonestaciones WHERE is_active = TRUE");
            $amonResumen = $db->single();
            $db->query("SELECT COUNT(*) AS total FROM (
                            SELECT id_empleado FROM amonestaciones WHERE is_active = TRUE
                            GROUP BY id_empleado HAVING COUNT(*) >= :lim) q");
            $db->bind(':lim', Amonestacion::LIMITE_DESPIDO);
            $amonDespido = (int)($db->single()->total ?? 0);

            // ── RRHH: impuntualidad del mes actual ────────────────────────────────────
            $db->query("SELECT COUNT(CASE WHEN minutos_tarde IS NOT NULL THEN 1 END) AS con_horario,
                               COUNT(CASE WHEN minutos_tarde > :tol THEN 1 END) AS impuntuales
                        FROM asistencias
                        WHERE is_active = TRUE AND fecha >= date_trunc('month', CURRENT_DATE)");
            $db->bind(':tol', Asistencia::toleranciaPuntualidad());
            $puntualidadMes = $db->single();

            // ══ BLOQUE VERDE — indicadores adicionales (cuadre con el documento CMI) ══

            // RRHH: Cumplimiento de jornada (horas reales vs programadas) — mes actual.
            // Compara solo días con marcaje completo (entrada+salida) Y horario asignado,
            // para que un check-out faltante no distorsione el cumplimiento.
            $db->query("SELECT
                            COALESCE(SUM(CASE WHEN a.hora_salida IS NOT NULL AND h.hora_entrada IS NOT NULL AND h.hora_salida IS NOT NULL
                                 THEN EXTRACT(EPOCH FROM (a.hora_salida - a.hora_entrada))/3600.0 END), 0) AS horas_reales,
                            COALESCE(SUM(CASE WHEN a.hora_salida IS NOT NULL AND h.hora_entrada IS NOT NULL AND h.hora_salida IS NOT NULL
                                 THEN EXTRACT(EPOCH FROM (h.hora_salida - h.hora_entrada))/3600.0 END), 0) AS horas_programadas
                        FROM asistencias a
                        INNER JOIN empleados e ON a.id_empleado = e.id
                        LEFT  JOIN horarios  h ON e.id_horario  = h.id
                        WHERE a.is_active = TRUE AND a.fecha >= date_trunc('month', CURRENT_DATE)");
            $jornadaMes = $db->single();

            // RRHH: Precisión del registro de asistencia (registros con salida / total) — mes actual.
            $db->query("SELECT COUNT(*) AS total, COUNT(hora_salida) AS completos
                        FROM asistencias
                        WHERE is_active = TRUE AND fecha >= date_trunc('month', CURRENT_DATE)");
            $precisionAsist = $db->single();

            // RRHH: Documentación completa del personal (expedientes con recaudos obligatorios completos).
            // Una sola consulta agregada (sin N+1 por empleado).
            $faltMap = ExpedienteDocumento::faltantesObligatorios();
            $empDocTotal = count($faltMap);
            $empDocCompletos = 0;
            foreach ($faltMap as $f) if ((int)$f === 0) $empDocCompletos++;

            // INVENTARIO: Precisión del registro (durables con código BN; fungibles siempre cuentan).
            $db->query("SELECT COUNT(*) AS total,
                               COUNT(CASE WHEN (tipo_bien = 'Durable' AND codigo_bn IS NOT NULL AND TRIM(codigo_bn) <> '')
                                            OR tipo_bien = 'Fungible' THEN 1 END) AS completos
                        FROM inventario WHERE is_active = TRUE");
            $precisionInv = $db->single();

            // INVENTARIO: Movimientos por tipo (entradas/salidas/asignaciones) — año actual.
            $db->query("SELECT tipo_movimiento, COUNT(*) AS total
                        FROM actividad_inventario
                        WHERE is_active = TRUE AND EXTRACT(YEAR FROM fecha) = :anio
                        GROUP BY tipo_movimiento ORDER BY total DESC");
            $db->bind(':anio', $anioActual);
            $movInventario = $db->resultSet();

            // INVENTARIO: Asignación responsable — durables cuyo ÚLTIMO movimiento es 'Asignacion'.
            $db->query("SELECT
                            (SELECT COUNT(*) FROM inventario WHERE is_active = TRUE AND tipo_bien = 'Durable') AS total_durables,
                            COUNT(CASE WHEN u.tipo_movimiento = 'Asignacion' THEN 1 END) AS asignados
                        FROM (
                            SELECT DISTINCT ON (ai.id_inventario) ai.id_inventario, ai.tipo_movimiento
                            FROM actividad_inventario ai
                            INNER JOIN inventario i ON i.id = ai.id_inventario AND i.is_active = TRUE AND i.tipo_bien = 'Durable'
                            WHERE ai.is_active = TRUE
                            ORDER BY ai.id_inventario, ai.fecha DESC, ai.id DESC
                        ) u");
            $asignacionInv = $db->single();

            // FORMACIÓN: Cobertura territorial por parroquia (año actual).
            $db->query("SELECT COUNT(DISTINCT uf.parroquia) AS parroquias_cubiertas,
                               (SELECT COUNT(*) FROM parroquia WHERE is_active = TRUE) AS total_parroquias
                        FROM talleres t
                        INNER JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                        WHERE t.is_active = TRUE AND uf.parroquia IS NOT NULL
                          AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio");
            $db->bind(':anio', $anioActual);
            $coberturaParroquia = $db->single();

            // TURISMO: Frecuencia de rutas ejecutadas (Finalizadas) por mes — últimos 6 meses.
            $db->query("SELECT TO_CHAR(COALESCE(fecha_visita, created_at), 'YYYY-MM') AS mes, COUNT(*) AS total
                        FROM rutas
                        WHERE is_active = TRUE AND estado = 'Finalizada'
                          AND COALESCE(fecha_visita, created_at) >= (date_trunc('month', CURRENT_DATE) - INTERVAL '5 months')
                        GROUP BY mes ORDER BY mes ASC");
            $rutasPorMes = $db->resultSet();

            $data = [
                'titulo'                => 'Indicadores de Gestión',
                'anioActual'            => $anioActual,
                'aniosDisponibles'      => $this->aniosDisponibles('talleres', 'fecha_inicio', $anioActual),
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
                // KPIs nuevos — RRHH (módulos 025-034)
                'empPorClasificacion'   => $empPorClasificacion,
                'permisosVigentes'      => $permisosVigentes,
                'permisosPendientes'    => $permisosPendientes,
                'amonResumen'           => $amonResumen,
                'amonDespido'           => $amonDespido,
                'puntualidadMes'        => $puntualidadMes,
                'tolPunt'               => Asistencia::toleranciaPuntualidad(),
                // Bloque verde — indicadores adicionales (cuadre con el documento CMI)
                'jornadaMes'            => $jornadaMes,
                'precisionAsist'        => $precisionAsist,
                'empDocTotal'           => $empDocTotal,
                'empDocCompletos'       => $empDocCompletos,
                'precisionInv'          => $precisionInv,
                'movInventario'         => $movInventario,
                'asignacionInv'         => $asignacionInv,
                'coberturaParroquia'    => $coberturaParroquia,
                'rutasPorMes'           => $rutasPorMes,
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

    // =========================================================================
    // RRHH — Saldo de vacaciones por empleado (BRH-07)
    // =========================================================================
    public function vacacionesSaldo() {
        $this->requireRoles([1, 2]);
        try {
            $regs = $this->queryVacacionesSaldo();
            $filas = []; $totSaldo = 0;
            foreach ($regs as $r) {
                $totSaldo += $r['saldo'];
                $badge = $r['saldo'] <= 0 ? 'sig-badge--neutral' : ($r['saldo'] > 30 ? 'sig-badge--warning' : 'sig-badge--success');
                $filas[] = [
                    ['raw' => '<span class="cell-strong">' . htmlspecialchars($r['empleado']) . '</span>'],
                    $r['cedula'] ?? '—',
                    $r['cargo'] ?? '—',
                    $r['departamento'] ?? '—',
                    (string)$r['anios'],
                    (string)$r['derecho'],
                    (string)$r['acumulado'],
                    (string)$r['ajuste'],
                    (string)$r['disfrutado'],
                    ['raw' => '<span class="sig-badge ' . $badge . '">' . $r['saldo'] . ' días</span>'],
                ];
            }
            $deptos = []; foreach (Departamento::all() as $d) $deptos[$d->id] = $d->nombre;
            $this->renderReporte([
                'eyebrow' => 'RRHH · Reporte', 'titulo' => 'Saldo de Vacaciones',
                'subtitulo' => 'Derecho acumulado, días disfrutados y saldo disponible por empleado (15 días hábiles + 1 por año de servicio, tope 30).',
                'resumen' => ['Empleados' => count($regs), 'Saldo total (días)' => $totSaldo],
                'columnas' => ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'Años serv.', 'Derecho año', 'Acumulado', 'Ajuste', 'Disfrutado', 'Saldo'],
                'filas' => $filas,
                'accion' => URL_ROOT . '/reportes/vacacionesSaldo',
                'filtros' => [
                    ['name' => 'buscar', 'label' => 'Buscar', 'type' => 'text', 'placeholder' => 'Nombre o cédula…', 'value' => $_GET['buscar'] ?? ''],
                    ['name' => 'departamento', 'label' => 'Departamento', 'type' => 'select', 'options' => ['' => 'Todos'] + $deptos, 'value' => $_GET['departamento'] ?? ''],
                ],
                'export_url' => URL_ROOT . '/reportes/exportarVacacionesSaldoCsv?' . $this->qsFiltros(),
                'vacio' => 'No hay empleados activos para el filtro.',
            ]);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de vacaciones: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarVacacionesSaldoCsv() {
        $this->requireRoles([1, 2]);
        $rows = [];
        foreach ($this->queryVacacionesSaldo() as $r) {
            $rows[] = [$r['empleado'], $r['cedula'], $r['cargo'], $r['departamento'],
                       $r['anios'], $r['derecho'], $r['acumulado'], $r['ajuste'], $r['disfrutado'], $r['saldo']];
        }
        $this->exportCsv('saldo_vacaciones', ['Empleado', 'Cédula', 'Cargo', 'Departamento', 'Años servicio', 'Derecho año', 'Acumulado', 'Ajuste', 'Disfrutado', 'Saldo'], $rows);
    }

    private function queryVacacionesSaldo(): array {
        $buscar = trim($_GET['buscar'] ?? '');
        $depto  = trim($_GET['departamento'] ?? '');
        $out = [];
        foreach (Empleado::all() as $e) {
            if ($depto !== '' && (int)$e->id_departamento !== (int)$depto) continue;
            if ($buscar !== '') {
                $hay = stripos(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')), $buscar) !== false
                    || stripos((string)($e->cedula ?? ''), $buscar) !== false;
                if (!$hay) continue;
            }
            $out[] = [
                'empleado'    => trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')),
                'cedula'      => $e->cedula ?? null,
                'cargo'       => $e->cargo ?? null,
                'departamento'=> $e->departamento ?? null,
                'anios'       => Vacacion::aniosServicio($e),
                'derecho'     => Vacacion::derechoAnioActual($e),
                'acumulado'   => Vacacion::derechoAcumulado($e),
                'ajuste'      => (int)($e->vacaciones_ajuste_dias ?? 0),
                'disfrutado'  => Vacacion::totalDisfrutado((int)$e->id),
                'saldo'       => Vacacion::saldo($e),
            ];
        }
        return $out;
    }

    // =========================================================================
    // Formación — Informe trimestral consolidado (D-RE01/02)
    // =========================================================================
    public function formacionTrimestral() {
        $this->requireRoles([1, 3]);
        try {
            $anio = (int)($_GET['anio'] ?? date('Y'));
            $q = $this->queryFormacionTrimestral($anio);
            $romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
            $rangos  = [1 => 'Ene–Mar', 2 => 'Abr–Jun', 3 => 'Jul–Sep', 4 => 'Oct–Dic'];
            $filas = [];
            $tot = ['act' => 0, 'fin' => 0, 'can' => 0, 'ins' => 0, 'ate' => 0];
            foreach ([1, 2, 3, 4] as $t) {
                $d = $q[$t];
                $tot['act'] += $d['actividades']; $tot['fin'] += $d['finalizadas'];
                $tot['can'] += $d['canceladas'];  $tot['ins'] += $d['inscritos']; $tot['ate'] += $d['atendidos'];
                $filas[] = [
                    ['raw' => '<span class="cell-strong">Trimestre ' . $romanos[$t] . '</span> <span style="color:var(--text-tertiary);font-size:11px;">(' . $rangos[$t] . ')</span>'],
                    (string)$d['actividades'],
                    (string)$d['finalizadas'],
                    (string)$d['canceladas'],
                    (string)$d['inscritos'],
                    (string)$d['atendidos'],
                    (string)$d['mujeres'],
                    (string)$d['hombres'],
                    (string)($d['ninas'] + $d['ninos']),
                ];
            }
            $anios = $this->aniosDisponibles('talleres', 'fecha_inicio', $anio);
            $this->renderReporte([
                'eyebrow' => 'Formación · Informe', 'titulo' => 'Informe Trimestral de Formación — ' . $anio,
                'subtitulo' => 'Actividades, ejecución y personas atendidas (según informe demográfico) por trimestre.',
                'resumen' => ['Año' => $anio, 'Actividades' => $tot['act'], 'Finalizadas' => $tot['fin'], 'Inscritos' => $tot['ins'], 'Atendidos (informe)' => $tot['ate']],
                'columnas' => ['Trimestre', 'Actividades', 'Finalizadas', 'Canceladas', 'Inscritos', 'Atendidos', 'Mujeres', 'Hombres', 'Niños/as'],
                'filas' => $filas,
                'accion' => URL_ROOT . '/reportes/formacionTrimestral',
                'filtros' => [
                    ['name' => 'anio', 'label' => 'Año', 'type' => 'select', 'options' => $anios, 'value' => (string)$anio],
                ],
                'export_url' => URL_ROOT . '/reportes/exportarFormacionTrimestralCsv?' . $this->qsFiltros(),
            ]);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el informe trimestral: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarFormacionTrimestralCsv() {
        $this->requireRoles([1, 3]);
        $anio = (int)($_GET['anio'] ?? date('Y'));
        $q = $this->queryFormacionTrimestral($anio);
        $romanos = [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'];
        $rows = [];
        foreach ([1, 2, 3, 4] as $t) {
            $d = $q[$t];
            $rows[] = ['Trimestre ' . $romanos[$t], $d['actividades'], $d['finalizadas'], $d['canceladas'],
                       $d['inscritos'], $d['atendidos'], $d['mujeres'], $d['hombres'], $d['ninas'] + $d['ninos']];
        }
        $this->exportCsv('formacion_trimestral_' . $anio, ['Trimestre', 'Actividades', 'Finalizadas', 'Canceladas', 'Inscritos', 'Atendidos', 'Mujeres', 'Hombres', 'Niños/as'], $rows);
    }

    private function queryFormacionTrimestral(int $anio): array {
        $base = [];
        foreach ([1, 2, 3, 4] as $t) {
            $base[$t] = ['actividades' => 0, 'finalizadas' => 0, 'canceladas' => 0, 'inscritos' => 0,
                         'atendidos' => 0, 'mujeres' => 0, 'hombres' => 0, 'ninas' => 0, 'ninos' => 0];
        }
        $db = new Database();
        $db->query("SELECT EXTRACT(QUARTER FROM t.fecha_inicio)::int AS tri,
                           COUNT(*) AS actividades,
                           COUNT(*) FILTER (WHERE t.estado = 'Finalizado') AS finalizadas,
                           COUNT(*) FILTER (WHERE t.estado = 'Cancelado')  AS canceladas,
                           COALESCE(SUM(pc.cnt), 0) AS inscritos
                    FROM talleres t
                    LEFT JOIN (SELECT id_taller, COUNT(*) AS cnt FROM participantes_taller WHERE is_active = TRUE GROUP BY id_taller) pc ON pc.id_taller = t.id
                    WHERE t.is_active = TRUE AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                    GROUP BY tri");
        $db->bind(':anio', $anio);
        foreach ($db->resultSet() as $r) {
            $t = (int)$r->tri; if (!isset($base[$t])) continue;
            $base[$t]['actividades'] = (int)$r->actividades;
            $base[$t]['finalizadas'] = (int)$r->finalizadas;
            $base[$t]['canceladas']  = (int)$r->canceladas;
            $base[$t]['inscritos']   = (int)$r->inscritos;
        }
        $db->query("SELECT EXTRACT(QUARTER FROM t.fecha_inicio)::int AS tri,
                           COALESCE(SUM(ti.mujeres), 0) AS mujeres, COALESCE(SUM(ti.hombres), 0) AS hombres,
                           COALESCE(SUM(ti.ninas), 0) AS ninas, COALESCE(SUM(ti.ninos), 0) AS ninos,
                           COALESCE(SUM(ti.total_atendidas), 0) AS atendidos
                    FROM taller_informes ti
                    JOIN talleres t ON ti.id_taller = t.id
                    WHERE t.is_active = TRUE AND EXTRACT(YEAR FROM t.fecha_inicio) = :anio
                    GROUP BY tri");
        $db->bind(':anio', $anio);
        foreach ($db->resultSet() as $r) {
            $t = (int)$r->tri; if (!isset($base[$t])) continue;
            $base[$t]['mujeres']   = (int)$r->mujeres;
            $base[$t]['hombres']   = (int)$r->hombres;
            $base[$t]['ninas']     = (int)$r->ninas;
            $base[$t]['ninos']     = (int)$r->ninos;
            $base[$t]['atendidos'] = (int)$r->atendidos;
        }
        return $base;
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

    // =========================================================================
    // Seguridad — Accesos al sistema (inicios de sesión) desde audit_logs
    // =========================================================================
    public function accesos() {
        $this->requireRoles([1]);
        try {
            $regs = $this->queryAccesos();
            $filas = []; $ok = 0; $fail = 0;
            foreach ($regs as $r) {
                $esFallo = $r->operacion === 'LOGIN_FALLIDO';
                if ($esFallo) $fail++; else $ok++;
                $badge = $esFallo
                    ? '<span class="sig-badge sig-badge--danger">Intento fallido</span>'
                    : '<span class="sig-badge sig-badge--success">Acceso</span>';
                $filas[] = [
                    !empty($r->fecha) ? date('d/m/Y H:i:s', strtotime($r->fecha)) : '—',
                    ['raw' => '<span class="cell-strong">' . htmlspecialchars($r->actor_name ?? $r->username ?? '—') . '</span>'],
                    ['raw' => $badge],
                    $r->ip_direccion ?? '—',
                ];
            }
            $this->renderReporte([
                'eyebrow' => 'Seguridad · Bitácora', 'titulo' => 'Accesos al Sistema',
                'subtitulo' => 'Inicios de sesión e intentos fallidos: quién entró, cuándo y desde qué IP.',
                'resumen' => ['Registros' => count($regs), 'Accesos' => $ok, 'Intentos fallidos' => $fail],
                'columnas' => ['Fecha y hora', 'Usuario', 'Tipo', 'IP'],
                'filas' => $filas,
                'accion' => URL_ROOT . '/reportes/accesos',
                'filtros' => [
                    ['name' => 'buscar', 'label' => 'Usuario', 'type' => 'text', 'placeholder' => 'Nombre o usuario…', 'value' => $_GET['buscar'] ?? ''],
                    ['name' => 'tipo', 'label' => 'Tipo', 'type' => 'select', 'options' => ['' => 'Todos', 'exito' => 'Accesos', 'fallido' => 'Intentos fallidos'], 'value' => $_GET['tipo'] ?? ''],
                    ['name' => 'fecha_inicio', 'label' => 'Desde', 'type' => 'date', 'value' => $_GET['fecha_inicio'] ?? ''],
                    ['name' => 'fecha_fin', 'label' => 'Hasta', 'type' => 'date', 'value' => $_GET['fecha_fin'] ?? ''],
                ],
                'export_url' => URL_ROOT . '/reportes/exportarAccesosCsv?' . $this->qsFiltros(),
                'vacio' => 'No hay accesos registrados para el filtro.',
            ]);
        } catch (Exception $e) {
            flash('global_msg', 'Error al generar el reporte de accesos: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
        }
    }

    public function exportarAccesosCsv() {
        $this->requireRoles([1]);
        $rows = [];
        foreach ($this->queryAccesos() as $r) {
            $rows[] = [
                !empty($r->fecha) ? date('d/m/Y H:i:s', strtotime($r->fecha)) : '',
                $r->actor_name ?? $r->username ?? '',
                $r->operacion === 'LOGIN_FALLIDO' ? 'Intento fallido' : 'Acceso',
                $r->ip_direccion ?? '',
            ];
        }
        $this->exportCsv('accesos_sistema', ['Fecha y hora', 'Usuario', 'Tipo', 'IP'], $rows);
    }

    private function queryAccesos() {
        $db = new Database();
        $binds = [];
        $where = "a.tabla_afectada = 'usuarios' AND a.operacion IN ('LOGIN','LOGIN_FALLIDO')";
        $tipo = trim($_GET['tipo'] ?? '');
        if ($tipo === 'exito')   $where .= " AND a.operacion = 'LOGIN'";
        if ($tipo === 'fallido') $where .= " AND a.operacion = 'LOGIN_FALLIDO'";
        if (!empty($_GET['fecha_inicio'])) { $where .= " AND a.fecha >= :fi"; $binds[':fi'] = trim($_GET['fecha_inicio']) . ' 00:00:00'; }
        if (!empty($_GET['fecha_fin']))    { $where .= " AND a.fecha <= :ff"; $binds[':ff'] = trim($_GET['fecha_fin']) . ' 23:59:59'; }
        if (!empty($_GET['buscar'])) {
            $where .= " AND (u.username ILIKE :q OR (COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')) ILIKE :q)";
            $binds[':q'] = '%' . trim($_GET['buscar']) . '%';
        }
        $db->query("SELECT a.fecha, a.operacion, a.ip_direccion, u.username,
                           COALESCE(NULLIF(TRIM(COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')), ''), u.username) AS actor_name
                    FROM audit_logs a
                    LEFT JOIN usuarios u   ON a.id_usuario  = u.id
                    LEFT JOIN empleados e  ON u.id_empleado = e.id
                    LEFT JOIN personas per ON e.id_persona  = per.id
                    WHERE {$where}
                    ORDER BY a.fecha DESC
                    LIMIT 1000");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        return $db->resultSet();
    }

    // =========================================================================
    // HELPERS DE EXPORTACIÓN
    // =========================================================================

    /** Índice de columna (1→A, 27→AA) para celdas .xlsx */
    private function colLetter(int $n): string {
        $s = '';
        while ($n > 0) { $m = ($n - 1) % 26; $s = chr(65 + $m) . $s; $n = intdiv($n - 1, 26); }
        return $s;
    }

    /**
     * Exporta a un archivo .xlsx REAL (Office Open XML) con formato — sin
     * librerías externas (usa ZipArchive). Excel lo abre sin advertencias, con
     * membrete, encabezados en color, bordes, filas alternadas y total.
     * Todas las celdas son texto (preserva cédulas/códigos con ceros).
     * Mantiene el nombre exportCsv para no tocar los llamadores.
     */
    private function exportCsv($filename, $headers, $rows) {
        $titulo = ucwords(str_replace('_', ' ', $filename));
        $ncol   = max(1, count($headers));
        [$sheetRows, $merges, $dataStart] = $this->construirHojaMembrete($titulo, $ncol,
            function ($rowMerged, $rowCells) use ($headers, $rows) {
                $rowCells($headers, 4);
                foreach ($rows as $i => $row) $rowCells(array_values((array)$row), ($i % 2 ? 6 : 5));
                $rowMerged('Total de registros: ' . count($rows), 7);
            },
            ' · ' . count($rows) . ' registro(s)'
        );

        // Ancho de columnas según contenido
        $widths = [];
        foreach ($headers as $i => $h) $widths[$i] = strlen((string)$h);
        foreach ($rows as $row) { $j = 0; foreach ((array)$row as $v) { $widths[$j] = max($widths[$j] ?? 8, strlen((string)$v)); $j++; } }

        $this->descargarXlsx($filename, $sheetRows, $merges, $widths, $dataStart);
    }

    /**
     * Exporta un .xlsx con MÚLTIPLES secciones en una sola hoja (cada una con su
     * propio título y su propio encabezado de columnas) — para reportes que no son
     * una tabla plana, como el Dossier de Taller. Comparte el mismo membrete
     * institucional y el mismo empaquetador que exportCsv().
     * $secciones = [['titulo' => .., 'headers' => [...], 'rows' => [...]], ...]
     */
    private function exportCsvSecciones($filename, $tituloReporte, array $secciones) {
        $ncol = max(1, ...array_map(fn($s) => count($s['headers']), $secciones));
        $widths = [];
        [$sheetRows, $merges, $dataStart] = $this->construirHojaMembrete($tituloReporte, $ncol,
            function ($rowMerged, $rowCells) use ($secciones, &$widths) {
                foreach ($secciones as $sec) {
                    $rowMerged($sec['titulo'], 2);
                    $rowCells($sec['headers'], 4);
                    foreach ($sec['headers'] as $i => $h) $widths[$i] = max($widths[$i] ?? 8, strlen((string)$h));
                    foreach ($sec['rows'] as $i => $row) {
                        $rowCells(array_values((array)$row), ($i % 2 ? 6 : 5));
                        $j = 0; foreach ((array)$row as $v) { $widths[$j] = max($widths[$j] ?? 8, strlen((string)$v)); $j++; }
                    }
                }
            }
        );
        $this->descargarXlsx($filename, $sheetRows, $merges, $widths, $dataStart);
    }

    /**
     * Arma el membrete institucional (REPÚBLICA/ALCALDÍA/IMATUR+RIF, título, autor/fecha)
     * y delega el cuerpo de la hoja al callback, que recibe los mismos constructores de
     * fila ($rowMerged para texto fusionado a lo ancho de $ncol columnas, $rowCells para
     * una fila de celdas con estilo) usados por el membrete. Devuelve [sheetRows, merges, dataStart].
     */
    private function construirHojaMembrete(string $titulo, int $ncol, callable $cuerpo, string $metaExtra = ''): array {
        $lastCol = $this->colLetter($ncol);
        $rnum = 0; $sheetRows = ''; $merges = [];
        $esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $rowMerged = function (string $texto, int $style, ?float $alto = null) use (&$rnum, &$sheetRows, &$merges, $lastCol, $esc) {
            $rnum++;
            $rowAttrs = $alto !== null ? ' ht="' . $alto . '" customHeight="1"' : '';
            $sheetRows .= '<row r="' . $rnum . '"' . $rowAttrs . '><c r="A' . $rnum . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $esc($texto) . '</t></is></c></row>';
            $merges[] = 'A' . $rnum . ':' . $lastCol . $rnum;
        };
        $rowCells = function (array $cells, int $style) use (&$rnum, &$sheetRows, $esc) {
            $rnum++; $c = ''; $i = 0;
            foreach ($cells as $v) {
                $i++;
                $c .= '<c r="' . $this->colLetter($i) . $rnum . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $esc($v) . '</t></is></c>';
            }
            $sheetRows .= '<row r="' . $rnum . '">' . $c . '</row>';
        };

        $usuario = $_SESSION['user_username'] ?? 'Sistema';
        $rowMerged('REPÚBLICA BOLIVARIANA DE VENEZUELA', 1, 18);
        $rowMerged('ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE', 1, 18);
        $rowMerged('Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE) — RIF ' . ConfigSistema::rif(), 1, 18);
        $rnum++; $sheetRows .= '<row r="' . $rnum . '" ht="6" customHeight="1"/>'; // fila en blanco (aire para los logos)
        $rowMerged($titulo, 2, 24);
        $rowMerged('Generado por ' . $usuario . ' · ' . date('d/m/Y H:i') . $metaExtra, 3, 16);
        $rnum++; $sheetRows .= '<row r="' . $rnum . '" ht="8" customHeight="1"/>'; // fila en blanco
        $dataStart = $rnum + 1;

        $cuerpo($rowMerged, $rowCells);

        return [$sheetRows, $merges, $dataStart];
    }

    private function descargarXlsx($filename, $sheetRows, array $merges, array $widths, int $dataStart) {
        $cols = '';
        foreach ($widths as $i => $w) { $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . min(60, max(10, $w + 3)) . '" customWidth="1"/>'; }

        $mergeXml = $merges ? '<mergeCells count="' . count($merges) . '">' . implode('', array_map(fn($m) => '<mergeCell ref="' . $m . '"/>', $merges)) . '</mergeCells>' : '';

        // Logos institucionales (Alcaldía + IMATUR) anclados como imagen real.
        $piezas = XlsxLogos::piezasParaHoja(max(1, count($widths)));
        $drawingTag = !empty($piezas['drawingXml']) ? '<drawing r:id="rIdDrawing"/>' : '';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . ($dataStart - 1) . '" topLeftCell="A' . $dataStart . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols><sheetData>' . $sheetRows . '</sheetData>' . $mergeXml . $drawingTag . '</worksheet>';

        // ── Estilos ───────────────────────────────────────────────────────────
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="@"/></numFmts>'
            . '<fonts count="5">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="16"/><color rgb="FF1E3A8A"/><name val="Calibri"/></font>'
            . '<font><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="4">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="8">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'                                                                                  // 0 default
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'              // 1 inst
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'              // 2 título
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'              // 3 meta
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' // 4 header
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'             // 5 data
            . '<xf numFmtId="164" fontId="0" fillId="3" borderId="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>' // 6 zebra
            . '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'                                        // 7 total
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        // ── Empaquetar .xlsx (ZIP) ────────────────────────────────────────────
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        if (!empty($piezas['drawingXml'])) {
            foreach ($piezas['media'] as $nombreImg => $bytes) {
                $zip->addFromString('xl/media/' . $nombreImg, $bytes);
            }
            $zip->addFromString('xl/drawings/drawing1.xml', $piezas['drawingXml']);
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', $piezas['relsXml']);
            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rIdDrawing" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>'
                . '</Relationships>');
        }
        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . (!empty($piezas['drawingXml']) ? '<Default Extension="png" ContentType="image/png"/>' : '')
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . (!empty($piezas['drawingXml']) ? '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>' : '')
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xlsx"');
        header('Content-Length: ' . filesize($tmp));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($tmp);
        @unlink($tmp);
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
