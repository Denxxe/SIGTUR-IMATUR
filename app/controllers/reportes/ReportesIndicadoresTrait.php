<?php
/**
 * Indicadores de gestión (CMI) — RF30
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesIndicadoresTrait {

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

            $db->query("SELECT COUNT(*) as total FROM inventario
                        WHERE is_active = TRUE AND estatus <> 'Dado de baja'");
            $kpiBienesActivos = $db->single();

            $db->query("SELECT COUNT(*) as total FROM inventario
                        WHERE is_active = TRUE AND estatus <> 'Dado de baja'
                          AND (condicion = 'Dañado' OR estatus = 'En mantenimiento')");
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

            $db->query("SELECT condicion, COUNT(*) as total FROM inventario
                        WHERE is_active = TRUE AND estatus <> 'Dado de baja'
                        GROUP BY condicion ORDER BY total DESC");
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
                            COUNT(CASE WHEN condicion = 'Dañado' OR estatus = 'En mantenimiento' THEN 1 END) AS deteriorados
                        FROM inventario WHERE is_active = TRUE AND estatus <> 'Dado de baja'");
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

            // INVENTARIO: Precisión del registro = bienes ya codificados por la Alcaldía
            // (mig. 062: el código llega con el BM-1; antes de eso el bien no tiene código).
            // Desde la mig. 067 no hay `tipo_bien`: todo bien inventariado es
            // durable y debe tener su código de la Alcaldía.
            $db->query("SELECT COUNT(*) AS total,
                               COUNT(CASE WHEN codigo_bn IS NOT NULL AND TRIM(codigo_bn) <> ''
                                          THEN 1 END) AS completos
                        FROM inventario WHERE is_active = TRUE AND estatus <> 'Dado de baja'");
            $precisionInv = $db->single();

            // INVENTARIO: Movimientos por tipo (entradas/salidas/asignaciones) — año actual.
            $db->query("SELECT tipo_movimiento, COUNT(*) AS total
                        FROM actividad_inventario
                        WHERE is_active = TRUE AND EXTRACT(YEAR FROM fecha) = :anio
                        GROUP BY tipo_movimiento ORDER BY total DESC");
            $db->bind(':anio', $anioActual);
            $movInventario = $db->resultSet();

            // INVENTARIO: Asignación de responsables.
            // Desde la mig. 066 (B-68) el responsable NO se almacena: se deriva del
            // departamento donde está el bien. El indicador mide entonces cuántos
            // bienes están en un departamento CON jefatura asignada — los que caen
            // en un departamento sin director ni coordinador quedan sin responsable.
            $db->query("SELECT COUNT(*) AS total_durables,
                               COUNT(resp.id) AS asignados
                          FROM inventario i
                          INNER JOIN ubicaciones u ON i.id_ubicacion = u.id
                          LEFT JOIN LATERAL (
                              SELECT er.id
                                FROM empleados er
                                LEFT JOIN cargos cr ON cr.id = er.id_cargo
                               WHERE er.is_active = TRUE AND er.fecha_egreso IS NULL
                                 AND er.id_departamento = CASE
                                       WHEN u.es_deposito THEN (SELECT NULLIF(valor,'')::int
                                                                  FROM configuracion_sistema
                                                                 WHERE clave = 'bienes_depto_autoriza')
                                       ELSE u.\"departamento _d\" END
                                 AND cr.nivel_jerarquico IN ('Dirección','Coordinación')
                               LIMIT 1
                          ) resp ON TRUE
                         WHERE i.is_active = TRUE AND i.estatus <> 'Dado de baja'");
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
}
