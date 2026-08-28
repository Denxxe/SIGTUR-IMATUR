<?php
/**
 * Transversal — alertas, auditoría, accesos y control de datos
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesSistemaTrait {

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
}
