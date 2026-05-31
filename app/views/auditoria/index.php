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
        'tipo_contrato' => 'Tipo de contrato', 'fecha_egreso' => 'Fecha de egreso', 'sueldo_base' => 'Sueldo base',
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
$logs = $data['logs'] ?? [];
// Conteos por operación para los chips de resumen
$cnt = ['INSERT' => 0, 'UPDATE' => 0, 'DELETE' => 0, 'RESTORE' => 0];
foreach ($logs as $l) { if (isset($cnt[$l->operacion])) $cnt[$l->operacion]++; }
$opMeta = [
    'INSERT'  => ['Creación',     'sig-badge--success', 'bi-plus-circle-fill',          '#059669'],
    'UPDATE'  => ['Edición',      'sig-badge--info',    'bi-pencil-fill',               '#2563EB'],
    'DELETE'  => ['Eliminación',  'sig-badge--danger',  'bi-trash-fill',                '#DC2626'],
    'RESTORE' => ['Restauración', 'sig-badge--brand',   'bi-arrow-counterclockwise',    '#7C3AED'],
];
?>

<!-- Barra de filtros + resumen -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);display:flex;align-items:center;gap:var(--sp-3);flex-wrap:wrap;">
        <div style="position:relative;flex:1;min-width:220px;">
            <i class="bi bi-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-tertiary);font-size:13px;"></i>
            <input type="text" id="auditSearch" class="sig-input" placeholder="Buscar por usuario, módulo o acción…" style="padding-left:34px;" oninput="filtrarAudit()">
        </div>
        <select id="auditOp" class="sig-input" style="max-width:200px;" onchange="filtrarAudit()">
            <option value="">Todas las acciones</option>
            <option value="INSERT">Creación (<?php echo $cnt['INSERT']; ?>)</option>
            <option value="UPDATE">Edición (<?php echo $cnt['UPDATE']; ?>)</option>
            <option value="DELETE">Eliminación (<?php echo $cnt['DELETE']; ?>)</option>
            <option value="RESTORE">Restauración (<?php echo $cnt['RESTORE']; ?>)</option>
        </select>
        <span class="sig-badge sig-badge--neutral" id="auditCount" style="font-weight:700;">
            <?php echo count($logs); ?> registros
        </span>
    </div>
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
                    <td colspan="6" class="sig-table-empty">No se han registrado acciones de auditoría.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($logs as $log):
                    $actor = $log->actor_name ?: 'Sistema';
                    $esSistema = empty($log->actor_name);
                    [$opLbl, $opCls, $opIco] = $opMeta[$log->operacion] ?? [$log->operacion, 'sig-badge--neutral', 'bi-dot'];
                    $busca = mb_strtolower($actor . ' ' . auditModulo($log->tabla_afectada) . ' ' . $opLbl . ' ' . $log->operacion);

                    // ── Construir detalle humano ──────────────────────────────
                    $prev = json_decode($log->datos_previos ?? 'null', true);
                    $new  = json_decode($log->datos_nuevos  ?? 'null', true);
                    $prev = is_array($prev) ? $prev : [];
                    $new  = is_array($new)  ? $new  : [];
                    $ocultar = auditOcultar();
                ?>
                    <tr data-search="<?php echo htmlspecialchars($busca); ?>" data-op="<?php echo $log->operacion; ?>">
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
                                    $keys = array_unique(array_merge(array_keys($prev), array_keys($new)));
                                    $cambios = [];
                                    foreach ($keys as $k) {
                                        if (in_array($k, $ocultar)) continue;
                                        $a = $prev[$k] ?? null;
                                        $b = $new[$k]  ?? null;
                                        if (auditNorm($a) !== auditNorm($b)) $cambios[$k] = [$a, $b];
                                    }
                                    // ¿Solo cambió is_active? → restauración / desactivación
                                    $soloActivo = empty($cambios) && (auditNorm($prev['is_active'] ?? null) !== auditNorm($new['is_active'] ?? null));
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
                                                <tr>
                                                    <th><?php echo htmlspecialchars(auditCampo($k)); ?></th>
                                                    <td class="audit-kv__before"><?php echo htmlspecialchars(auditValor($par[0])); ?></td>
                                                    <td style="text-align:center;color:var(--text-tertiary);"><i class="bi bi-arrow-right"></i></td>
                                                    <td class="audit-kv__after"><?php echo htmlspecialchars(auditValor($par[1])); ?></td>
                                                </tr>
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
    <div id="auditNoResults" style="display:none;text-align:center;padding:var(--sp-6);color:var(--text-tertiary);">
        <i class="bi bi-search" style="font-size:1.5rem;display:block;margin-bottom:var(--sp-2);"></i>
        No hay registros que coincidan con el filtro.
    </div>
</div>

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

<script>
function filtrarAudit() {
    const q  = (document.getElementById('auditSearch').value || '').toLowerCase().trim();
    const op = document.getElementById('auditOp').value;
    const rows = document.querySelectorAll('#auditTable tbody tr');
    let visibles = 0;
    rows.forEach(function (tr) {
        if (!tr.dataset.search) return; // fila vacía
        const okText = !q  || tr.dataset.search.indexOf(q) !== -1;
        const okOp   = !op || tr.dataset.op === op;
        const show = okText && okOp;
        tr.style.display = show ? '' : 'none';
        if (show) visibles++;
    });
    document.getElementById('auditCount').textContent = visibles + ' registros';
    document.getElementById('auditNoResults').style.display = visibles === 0 ? 'block' : 'none';
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
