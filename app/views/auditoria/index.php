<?php require_once '../app/views/inc/header.php'; ?>

<?php
// ──────────────────────────────────────────────────────────────────────────
// Helpers de humanización de la bitácora (un usuario promedio no lee JSON)
// ──────────────────────────────────────────────────────────────────────────

/** Nombre amigable del módulo/tabla. */
function auditModulo(string $t): string {
    $map = [
        'personas' => 'Personas', 'empleados' => 'Empleados', 'cargos' => 'Cargos',
        'departamentos' => 'Departamentos', 'asistencias' => 'Asistencias',
        'inventario' => 'Inventario / Bienes', 'categorias' => 'Categorías',
        'ubicaciones' => 'Ubicaciones', 'actividad_inventario' => 'Movimientos de inventario',
        'talleres' => 'Talleres / Formación', 'taller_informes' => 'Informes de taller',
        'taller_evidencias' => 'Evidencias de taller', 'participantes_taller' => 'Participantes de taller',
        'pasantes' => 'Pasantes', 'pasante_documentos' => 'Documentos de pasante',
        'ubicaciones_formacion' => 'Sedes de formación', 'rutas' => 'Rutas turísticas',
        'puntos_ruta' => 'Paradas de ruta', 'participantes_ruta' => 'Participantes de ruta',
        'ruta_informes' => 'Informes de ruta', 'oficios' => 'Oficios', 'oficios_emitidos' => 'Oficios emitidos',
        'visitantes' => 'Visitantes', 'visitas' => 'Visitas', 'usuarios' => 'Usuarios',
        'roles' => 'Roles', 'permisos_rol' => 'Permisos de rol',
        'configuracion_sistema' => 'Configuración', 'municipio' => 'Municipios', 'parroquia' => 'Parroquias',
    ];
    return $map[$t] ?? ucwords(str_replace('_', ' ', $t));
}

/** Nombre amigable de un campo/columna. */
function auditCampo(string $k): string {
    $map = [
        'nombre' => 'Nombre', 'apellido' => 'Apellido', 'cedula' => 'Cédula', 'correo' => 'Correo',
        'telefono' => 'Teléfono', 'direccion' => 'Dirección', 'genero' => 'Género',
        'fecha_nacimiento' => 'Fecha de nacimiento', 'estado' => 'Estado', 'descripcion' => 'Descripción',
        'fecha_inicio' => 'Fecha de inicio', 'fecha_fin' => 'Fecha de fin', 'hora_inicio' => 'Hora de inicio',
        'hora_fin' => 'Hora de fin', 'cupo_maximo' => 'Cupo máximo', 'tipo_actividad' => 'Tipo de actividad',
        'tipo_ente' => 'Tipo de ente', 'es_interna' => 'Actividad interna', 'motivo_cancelacion' => 'Motivo de cancelación',
        'nota' => 'Nota', 'carrera' => 'Carrera', 'institucion' => 'Institución', 'evaluacion' => 'Evaluación',
        'tutor' => 'Tutor', 'observaciones' => 'Observaciones', 'condicion' => 'Condición', 'marca' => 'Marca',
        'modelo' => 'Modelo', 'serial' => 'Serial', 'codigo_bn' => 'Código BN', 'id_categoria' => 'Categoría',
        'id_ubicacion' => 'Ubicación', 'id_departamento' => 'Departamento', 'id_cargo' => 'Cargo',
        'tipo_contrato' => 'Tipo de contrato', 'fecha_egreso' => 'Fecha de egreso', 'nivel_jerarquico' => 'Nivel jerárquico',
        'tipo_ruta' => 'Tipo de ruta', 'requiere_formacion' => 'Requiere formación', 'tarifa_monto' => 'Tarifa',
        'duracion_estimada' => 'Duración estimada', 'motivo' => 'Motivo', 'motivo_mantenimiento' => 'Motivo de mantenimiento',
        'parroquia_id' => 'Parroquia', 'parroquia' => 'Parroquia', 'municipio' => 'Municipio',
        'codigo_postal' => 'Código postal', 'username' => 'Usuario', 'id_rol' => 'Rol', 'modulos' => 'Módulos',
        'valor' => 'Valor', 'clave' => 'Parámetro', 'mujeres' => 'Mujeres', 'hombres' => 'Hombres',
        'ninas' => 'Niñas', 'ninos' => 'Niños', 'total_atendidas' => 'Total atendidos', 'total_atendidos' => 'Total atendidos',
        'lugar_exacto' => 'Lugar exacto', 'instituciones_presentes' => 'Instituciones presentes', 'resumen' => 'Resumen',
        'fecha' => 'Fecha', 'asistio' => 'Asistió', 'fecha_visita' => 'Fecha de visita', 'hora_visita' => 'Hora de visita',
        'orden' => 'Orden', 'latitud' => 'Latitud', 'longitud' => 'Longitud', 'nivel' => 'Nivel',
        'nombre_libre' => 'Nombre', 'apellido_libre' => 'Apellido', 'cedula_libre' => 'Documento',
        'genero_libre' => 'Género', 'fecha_nac_libre' => 'Fecha de nacimiento', 'nombre_docente' => 'Docente',
        'id_taller' => 'Taller (ID)', 'id_ruta' => 'Ruta (ID)', 'id_facilitador' => 'Facilitador (ID)',
        // 'id_oficio' se eliminó de la BD en la migración 060, pero se conserva aquí:
        // hay entradas de bitácora anteriores cuyo JSON lo menciona y sin la etiqueta
        // se mostrarían con el nombre crudo de la columna.
        'id_visitante' => 'Visitante (ID)', 'id_empleado' => 'Empleado (ID)', 'id_oficio' => 'Oficio (ID)',
        'id_ubicacion_formacion' => 'Sede (ID)', 'ubicacion' => 'Ubicación', 'cargo' => 'Cargo', 'sueldo' => 'Sueldo',
    ];
    return $map[$k] ?? ucfirst(str_replace('_', ' ', $k));
}

/** Convierte un valor crudo en texto legible. */
function auditValor($v): string {
    if (is_bool($v))            return $v ? 'Sí' : 'No';
    if ($v === null)            return '—';
    if ($v === 't')             return 'Sí';
    if ($v === 'f')             return 'No';
    $s = trim((string)$v);
    if ($s === '')              return '(vacío)';
    if ($s === 'true')          return 'Sí';
    if ($s === 'false')         return 'No';
    // Fecha o fecha-hora ISO → formato local
    if (preg_match('/^(\d{4})-(\d{2})-(\d{2})([ T](\d{2}):(\d{2}))?/', $s, $m)) {
        $fecha = "{$m[3]}/{$m[2]}/{$m[1]}";
        if (!empty($m[5])) $fecha .= " {$m[5]}:{$m[6]}";
        return $fecha;
    }
    if (mb_strlen($s) > 120) return mb_substr($s, 0, 120) . '…';
    return $s;
}

/** Para comparar si un valor cambió (normalización). */
function auditNorm($v): string {
    if (is_bool($v)) return $v ? '1' : '0';
    if ($v === null) return '';
    return trim((string)$v);
}

/** Traduce un nombre de controlador (token RBAC) a etiqueta amigable. */
function auditCtrlLabel(string $ctrl): string {
    static $map = null;
    if ($map === null) {
        $map = [];
        if (class_exists('RolesController')) {
            foreach (RolesController::getModulos() as $k => $v) $map[$k] = $v['label'] ?? $k;
        }
    }
    if (isset($map[$ctrl])) return $map[$ctrl];
    $base = preg_replace('/Controller$/', '', $ctrl);
    return $base !== '' ? $base : $ctrl;
}

/** Campos internos que no aportan nada al usuario. */
function auditOcultar(): array {
    return ['id','created_at','updated_at','deleted_at','created_by','updated_by','deleted_by',
            'is_active','password','ip_direccion','create_at','update_at','delete_at',
            'create_by','update_by','delete_by','id_persona'];
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Auditoría</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Auditoría'; ?></h1>
        <p class="page__subtitle">Quién hizo qué y cuándo. Toca “Ver detalles” para leer los cambios en lenguaje claro.</p>
    </div>
    <div class="page__actions">
        <?php if (RolesController::roleHasModulo('AuditoriaPapelera')): ?>
        <a href="<?php echo URL_ROOT; ?>/auditoria/papelera" class="btn-sig btn-sig--ghost">
            <i class="bi bi-recycle"></i> Ver Papelera
        </a>
        <?php endif; ?>
    </div>
</div>

<?php
$logs         = $data['logs'] ?? [];
$filtros      = $data['filtros'] ?? ['fecha_inicio'=>'','fecha_fin'=>'','modulo'=>'','operacion'=>'','buscar'=>''];
$modulosF     = $data['modulos'] ?? [];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$total        = (int)($data['total'] ?? count($logs));
$porPagina    = (int)($data['por_pagina'] ?? 50);
$desde        = $total === 0 ? 0 : (($pagina - 1) * $porPagina) + 1;
$hasta        = min($total, $pagina * $porPagina);
$hayFiltro    = ($filtros['fecha_inicio'] || $filtros['fecha_fin'] || $filtros['modulo'] || $filtros['operacion'] || $filtros['buscar']);

$opMeta = [
    'INSERT'  => ['Creación',     'sig-badge--success', 'bi-plus-circle-fill',          '#059669'],
    'UPDATE'  => ['Edición',      'sig-badge--info',    'bi-pencil-fill',               '#2563EB'],
    'DELETE'  => ['Eliminación',  'sig-badge--danger',  'bi-trash-fill',                '#DC2626'],
    'RESTORE' => ['Restauración', 'sig-badge--brand',   'bi-arrow-counterclockwise',    '#7C3AED'],
];

/** URL de una página preservando los filtros activos. */
function auditUrl(array $filtros, int $p): string {
    $q = array_filter($filtros, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/auditoria/index?' . http_build_query($q);
}
?>

<!-- Barra de filtros (server-side) -->
<form class="sig-card anim-slide-up" method="GET" action="<?php echo URL_ROOT; ?>/auditoria/index" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);display:flex;align-items:flex-end;gap:var(--sp-3);flex-wrap:wrap;">
        <div style="flex:1;min-width:200px;">
            <label class="sig-field__label" style="font-size:11px;">Buscar</label>
            <div style="position:relative;">
                <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-tertiary);font-size:13px;"></i>
                <input type="text" name="buscar" class="sig-input" placeholder="Responsable o módulo…" style="padding-left:34px;" value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
            </div>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Desde</label>
            <input type="date" name="fecha_inicio" class="sig-input" style="max-width:160px;" value="<?php echo htmlspecialchars($filtros['fecha_inicio']); ?>">
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Hasta</label>
            <input type="date" name="fecha_fin" class="sig-input" style="max-width:160px;" value="<?php echo htmlspecialchars($filtros['fecha_fin']); ?>">
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Módulo</label>
            <select name="modulo" class="sig-input" style="max-width:200px;">
                <option value="">Todos</option>
                <?php foreach ($modulosF as $m): ?>
                    <option value="<?php echo htmlspecialchars($m->tabla_afectada); ?>" <?php echo $filtros['modulo'] === $m->tabla_afectada ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars(auditModulo($m->tabla_afectada)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Acción</label>
            <select name="operacion" class="sig-input" style="max-width:170px;">
                <option value="">Todas</option>
                <?php foreach ($opMeta as $opKey => $opInfo): ?>
                    <option value="<?php echo $opKey; ?>" <?php echo $filtros['operacion'] === $opKey ? 'selected' : ''; ?>><?php echo $opInfo[0]; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div style="display:flex;gap:8px;">
            <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($hayFiltro): ?>
            <a href="<?php echo URL_ROOT; ?>/auditoria/index" class="btn-sig btn-sig--ghost" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Resumen de resultados -->
<div class="anim-slide-up" style="display:flex;align-items:center;justify-content:space-between;gap:var(--sp-3);margin-bottom:var(--sp-3);flex-wrap:wrap;">
    <span style="font-size:13px;color:var(--text-secondary);">
        <?php if ($total > 0): ?>
            Mostrando <strong><?php echo $desde; ?>–<?php echo $hasta; ?></strong> de <strong><?php echo number_format($total); ?></strong> registros<?php echo $hayFiltro ? ' (filtrados)' : ''; ?>
        <?php else: ?>
            Sin registros<?php echo $hayFiltro ? ' para el filtro aplicado' : ''; ?>
        <?php endif; ?>
    </span>
    <span class="sig-badge sig-badge--neutral" style="font-weight:700;">Página <?php echo $pagina; ?> de <?php echo $totalPaginas; ?></span>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table" id="auditTable">
        <thead>
            <tr>
                <th>Fecha y Hora</th>
                <th>Responsable</th>
                <th>Módulo</th>
                <th>Acción</th>
                <th>ID Reg.</th>
                <th>Detalles</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6" class="sig-table-empty">
                        <i class="bi bi-search" style="opacity:.5;margin-right:6px;"></i>
                        <?php echo $hayFiltro ? 'No hay registros que coincidan con el filtro.' : 'No se han registrado acciones de auditoría.'; ?>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log):
                    $actor = $log->actor_name ?: 'Sistema';
                    $esSistema = empty($log->actor_name);
                    [$opLbl, $opCls, $opIco] = $opMeta[$log->operacion] ?? [$log->operacion, 'sig-badge--neutral', 'bi-dot'];

                    // ── Construir detalle humano ──────────────────────────────
                    $prev = json_decode($log->datos_previos ?? 'null', true);
                    $new  = json_decode($log->datos_nuevos  ?? 'null', true);
                    $prev = is_array($prev) ? $prev : [];
                    $new  = is_array($new)  ? $new  : [];
                    $ocultar = auditOcultar();
                ?>
                    <tr>
                        <td class="cell-strong" style="white-space:nowrap"><i class="bi bi-clock-history" style="opacity:.45;margin-right:5px;"></i><?php echo date('d/m/Y H:i:s', strtotime($log->fecha)); ?></td>
                        <td>
                            <span class="sig-badge sig-badge--neutral">
                                <i class="bi <?php echo $esSistema ? 'bi-robot' : 'bi-person-circle'; ?>"></i>
                                <?php echo htmlspecialchars($actor); ?>
                            </span>
                        </td>
                        <td style="font-size:12px;font-weight:700;color:var(--brand-600)"><?php echo htmlspecialchars(auditModulo($log->tabla_afectada)); ?></td>
                        <td>
                            <span class="sig-badge <?php echo $opCls; ?>"><i class="bi <?php echo $opIco; ?>"></i> <?php echo $opLbl; ?></span>
                        </td>
                        <td>
                            <?php if ($log->record_id !== null && $log->record_id !== ''): ?>
                                <span class="cell-id">#<?php echo (int)$log->record_id; ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary);font-size:12px;" title="Operación sin un único registro asociado">—</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="row-action row-action--view" type="button" data-bs-toggle="collapse" data-bs-target="#log_<?php echo $log->id; ?>">
                                <i class="bi bi-eye"></i> Ver detalles
                            </button>
                            <div class="collapse mt-2" id="log_<?php echo $log->id; ?>">
                                <?php
                                // ── INSERT ────────────────────────────────────
                                if ($log->operacion === 'INSERT' || (empty($prev) && !empty($new))):
                                    $filas = array_filter($new, fn($k) => !in_array($k, $ocultar), ARRAY_FILTER_USE_KEY);
                                ?>
                                    <div class="audit-detail audit-detail--new">
                                        <div class="audit-detail__title" style="color:#059669;"><i class="bi bi-plus-circle-fill"></i> Datos registrados</div>
                                        <?php if (empty($filas)): ?>
                                            <div class="audit-detail__empty">Sin datos visibles.</div>
                                        <?php else: ?>
                                        <table class="audit-kv">
                                            <?php foreach ($filas as $k => $v): ?>
                                            <tr><th><?php echo htmlspecialchars(auditCampo($k)); ?></th><td><?php echo htmlspecialchars(auditValor($v)); ?></td></tr>
                                            <?php endforeach; ?>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                <?php
                                // ── DELETE ────────────────────────────────────
                                elseif ($log->operacion === 'DELETE' || (!empty($prev) && empty($new))):
                                    $filas = array_filter($prev, fn($k) => !in_array($k, $ocultar), ARRAY_FILTER_USE_KEY);
                                ?>
                                    <div class="audit-detail audit-detail--del">
                                        <div class="audit-detail__title" style="color:#DC2626;"><i class="bi bi-trash-fill"></i> Datos eliminados</div>
                                        <?php if (empty($filas)): ?>
                                            <div class="audit-detail__empty">Sin datos visibles.</div>
                                        <?php else: ?>
                                        <table class="audit-kv">
                                            <?php foreach ($filas as $k => $v): ?>
                                            <tr><th><?php echo htmlspecialchars(auditCampo($k)); ?></th><td><?php echo htmlspecialchars(auditValor($v)); ?></td></tr>
                                            <?php endforeach; ?>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                <?php
                                // ── UPDATE (diff) ─────────────────────────────
                                else:
                                    // Solo es un cambio real si el campo existe en AMBOS lados y su valor difiere.
                                    // (datos_nuevos suele traer solo el subconjunto editado; los campos
                                    //  presentes solo en 'previo' no se modificaron en esta acción.)
                                    $cambios = [];
                                    foreach ($new as $k => $b) {
                                        if (in_array($k, $ocultar)) continue;
                                        if (!array_key_exists($k, $prev)) continue; // sin contraparte previa comparable
                                        $a = $prev[$k];
                                        if (auditNorm($a) !== auditNorm($b)) $cambios[$k] = [$a, $b];
                                    }
                                    // ¿Solo cambió is_active? → restauración / desactivación.
                                    // Requiere que is_active exista en AMBOS lados; si datos_nuevos no la trae
                                    // (log parcial), NO es un cambio real (evita falsos "Se desactivó").
                                    $soloActivo = empty($cambios)
                                        && array_key_exists('is_active', $prev)
                                        && array_key_exists('is_active', $new)
                                        && auditNorm($prev['is_active']) !== auditNorm($new['is_active']);
                                ?>
                                    <div class="audit-detail audit-detail--upd">
                                        <div class="audit-detail__title" style="color:#2563EB;"><i class="bi bi-pencil-fill"></i> Cambios realizados</div>
                                        <?php if ($soloActivo): ?>
                                            <div class="audit-detail__empty"><?php echo (auditNorm($new['is_active'] ?? null) === '1') ? 'Se reactivó el registro.' : 'Se desactivó el registro.'; ?></div>
                                        <?php elseif (empty($cambios)): ?>
                                            <div class="audit-detail__empty">No hubo cambios visibles.</div>
                                        <?php else: ?>
                                        <table class="audit-kv audit-kv--diff">
                                            <thead><tr><th>Campo</th><th>Antes</th><th></th><th>Después</th></tr></thead>
                                            <tbody>
                                            <?php foreach ($cambios as $k => $par): ?>
                                                <?php if ($k === 'modulos'):
                                                    // Lista de permisos: mostrar qué se agregó / quitó, con etiquetas amigables
                                                    $prevArr   = array_filter(array_map('trim', explode(',', (string)$par[0])));
                                                    $newArr    = array_filter(array_map('trim', explode(',', (string)$par[1])));
                                                    $agregados = array_diff($newArr, $prevArr);
                                                    $quitados  = array_diff($prevArr, $newArr);
                                                ?>
                                                <tr>
                                                    <th><?php echo htmlspecialchars(auditCampo($k)); ?></th>
                                                    <td colspan="3">
                                                        <?php if ($agregados): ?>
                                                            <div style="margin-bottom:4px;display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                                                                <span style="font-size:11px;font-weight:700;color:#047857;">Agregados:</span>
                                                                <?php foreach ($agregados as $c): ?>
                                                                    <span class="sig-badge sig-badge--success"><i class="bi bi-plus-lg"></i> <?php echo htmlspecialchars(auditCtrlLabel($c)); ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if ($quitados): ?>
                                                            <div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;">
                                                                <span style="font-size:11px;font-weight:700;color:#b91c1c;">Quitados:</span>
                                                                <?php foreach ($quitados as $c): ?>
                                                                    <span class="sig-badge sig-badge--danger"><i class="bi bi-dash-lg"></i> <?php echo htmlspecialchars(auditCtrlLabel($c)); ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>
                                                        <?php if (!$agregados && !$quitados): ?>
                                                            <span style="color:var(--text-tertiary);">Sin cambios en la lista.</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php else: ?>
                                                <tr>
                                                    <th><?php echo htmlspecialchars(auditCampo($k)); ?></th>
                                                    <td class="audit-kv__before"><?php echo htmlspecialchars(auditValor($par[0])); ?></td>
                                                    <td style="text-align:center;color:var(--text-tertiary);"><i class="bi bi-arrow-right"></i></td>
                                                    <td class="audit-kv__after"><?php echo htmlspecialchars(auditValor($par[1])); ?></td>
                                                </tr>
                                                <?php endif; ?>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Paginación -->
<?php if ($totalPaginas > 1):
    $win = 2;
    $ini = max(1, $pagina - $win);
    $fin = min($totalPaginas, $pagina + $win);
?>
<nav class="anim-slide-up" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-top:var(--sp-4);flex-wrap:wrap;">
    <?php if ($pagina > 1): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo auditUrl($filtros, 1); ?>" title="Primera"><i class="bi bi-chevron-double-left"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo auditUrl($filtros, $pagina - 1); ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
    <?php endif; ?>
    <?php if ($ini > 1): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php for ($n = $ini; $n <= $fin; $n++): ?>
        <?php if ($n === $pagina): ?>
            <span class="btn-sig btn-sig--primary btn-sig--sm" style="pointer-events:none;min-width:38px;justify-content:center;"><?php echo $n; ?></span>
        <?php else: ?>
            <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo auditUrl($filtros, $n); ?>" style="min-width:38px;justify-content:center;"><?php echo $n; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($fin < $totalPaginas): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php if ($pagina < $totalPaginas): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo auditUrl($filtros, $pagina + 1); ?>">Siguiente <i class="bi bi-chevron-right"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo auditUrl($filtros, $totalPaginas); ?>" title="Última"><i class="bi bi-chevron-double-right"></i></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<style>
.audit-detail { background:var(--bg-muted); border:1px solid var(--border-subtle); border-radius:10px; padding:var(--sp-3) var(--sp-4); max-width:720px; }
.audit-detail__title { font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:.04em; margin-bottom:8px; display:flex; align-items:center; gap:6px; }
.audit-detail__empty { font-size:13px; color:var(--text-secondary); font-style:italic; }
.audit-kv { width:100%; border-collapse:collapse; font-size:13px; }
.audit-kv th { text-align:left; font-weight:700; color:var(--text-secondary); padding:4px 12px 4px 0; white-space:nowrap; vertical-align:top; width:1%; }
.audit-kv td { color:var(--text-primary); padding:4px 0; word-break:break-word; }
.audit-kv tr + tr th, .audit-kv tr + tr td { border-top:1px dashed var(--border-subtle); }
.audit-kv--diff thead th { font-size:10px; text-transform:uppercase; letter-spacing:.04em; color:var(--text-tertiary); border-bottom:1px solid var(--border-subtle); padding-bottom:6px; }
.audit-kv--diff tbody th { padding-right:16px; }
.audit-kv__before { color:#b91c1c; text-decoration:line-through; opacity:.85; padding-right:10px; }
.audit-kv__after  { color:#047857; font-weight:600; padding-left:10px; }
</style>

<?php require_once '../app/views/inc/footer.php'; ?>
