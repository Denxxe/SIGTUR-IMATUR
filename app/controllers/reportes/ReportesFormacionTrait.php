<?php
/**
 * Formación — talleres, cobertura, dossier, pasantes e informe trimestral
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesFormacionTrait {

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
}
