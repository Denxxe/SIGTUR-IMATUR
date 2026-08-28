<?php
/**
 * RRHH — personal, asistencia, permisos, disciplina, expediente y vacaciones
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesRrhhTrait {

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
}
