<?php require_once '../app/views/inc/header.php';
$flt          = $data['filtros'] ?? ['buscar'=>'','estado'=>'','tipo'=>'','fecha_desde'=>'','fecha_hasta'=>''];
$pagina       = (int)($data['pagina'] ?? 1);
$totalPaginas = (int)($data['total_paginas'] ?? 1);
$totalReg     = (int)($data['total'] ?? 0);
$porPagina    = (int)($data['por_pagina'] ?? 12);
$hayFiltro    = array_filter($flt, fn($v) => $v !== '');
function rutaUrl(array $f, int $p): string {
    $q = array_filter($f, fn($v) => $v !== '' && $v !== null);
    $q['p'] = $p;
    return URL_ROOT . '/rutas/index?' . http_build_query($q);
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Gestión de Destinos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Rutas'; ?></h1>
        <p class="page__subtitle">Planificación y control de rutas turísticas y puntos de interés del municipio.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/rutas" class="btn-sig btn-sig--success" title="Exportar listado completo (Excel/PDF)">
            <i class="bi bi-file-earmark-spreadsheet"></i> Exportar
        </a>
        <button type="button" class="btn-sig btn-sig--primary"
                style="background:linear-gradient(180deg, var(--teal-500), var(--teal-700)); box-shadow: var(--sh-glow-teal);"
                data-bs-toggle="modal" data-bs-target="#modalRuta" onclick="nuevaRuta()">
            <i class="bi bi-map"></i> Crear Nueva Ruta
        </button>
    </div>
</div>

<!-- Filtros (servidor) -->
<form class="sig-card anim-slide-up" method="GET" action="<?php echo URL_ROOT; ?>/rutas/index" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5); display:flex; align-items:flex-end; gap:var(--sp-3); flex-wrap:wrap;">
        <div style="flex:1; min-width:200px;">
            <label class="sig-field__label" style="font-size:11px;">Buscar</label>
            <div class="tabla-search">
                <i class="bi bi-search"></i>
                <input type="text" name="buscar" class="sig-input" style="padding-left:32px;width:100%;" placeholder="Nombre, descripción o guía…" value="<?php echo htmlspecialchars($flt['buscar'] ?? ''); ?>">
            </div>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Estado</label>
            <select name="estado" class="sig-input" style="min-width:150px;">
                <option value="">Todos</option>
                <?php foreach (Ruta::ESTADOS as $est): ?>
                    <option value="<?php echo $est; ?>" <?php echo ($flt['estado'] ?? '') === $est ? 'selected' : ''; ?>><?php echo $est; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Tipo</label>
            <select name="tipo" class="sig-input" style="min-width:150px;">
                <option value="">Todos</option>
                <?php foreach (Ruta::$TIPOS_RUTA as $tp): ?>
                    <option value="<?php echo htmlspecialchars($tp); ?>" <?php echo ($flt['tipo'] ?? '') === $tp ? 'selected' : ''; ?>><?php echo htmlspecialchars($tp); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Período</label>
            <?php $perActual = $flt['periodo'] ?? '';
                  $periodos = ['' => 'Todos', 'proximos' => 'Próximas', 'hoy' => 'Hoy', 'semana' => 'Esta semana', 'mes' => 'Este mes', 'pasados' => 'Pasadas']; ?>
            <select name="periodo" class="sig-input" style="min-width:130px;">
                <?php foreach ($periodos as $val => $lbl): ?>
                    <option value="<?php echo $val; ?>" <?php echo $perActual === $val ? 'selected' : ''; ?>><?php echo $lbl; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Desde</label>
            <input type="date" name="fecha_desde" class="sig-input" style="max-width:148px;" value="<?php echo htmlspecialchars($flt['fecha_desde'] ?? ''); ?>">
        </div>
        <div>
            <label class="sig-field__label" style="font-size:11px;">Hasta</label>
            <input type="date" name="fecha_hasta" class="sig-input" style="max-width:148px;" value="<?php echo htmlspecialchars($flt['fecha_hasta'] ?? ''); ?>">
        </div>
        <div style="display:flex; gap:var(--sp-2);">
            <button type="submit" class="btn-sig btn-sig--primary" style="height:42px;"><i class="bi bi-funnel"></i> Filtrar</button>
            <?php if ($hayFiltro): ?>
                <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost" style="height:42px; padding:0 var(--sp-3);" title="Limpiar"><i class="bi bi-x-lg"></i></a>
            <?php endif; ?>
        </div>
    </div>
</form>

<!-- Resumen -->
<div class="anim-slide-up" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-3);">
    <?php if ($totalReg > 0): ?>
        Mostrando <strong><?php echo (($pagina-1)*$porPagina)+1; ?>–<?php echo min($totalReg, $pagina*$porPagina); ?></strong> de <strong><?php echo number_format($totalReg); ?></strong> rutas<?php echo $hayFiltro ? ' (filtradas)' : ''; ?>
    <?php else: ?>Sin rutas<?php echo $hayFiltro ? ' para el filtro aplicado' : ''; ?><?php endif; ?>
</div>

<?php
// Color de la tarjeta (acento + pill) según estado de la ruta
$estadoColores = [
    'Activa'           => '#059669', // verde
    'En Mantenimiento' => '#F59E0B', // ámbar
    'Finalizada'       => '#2563EB', // azul
    'Inactiva'         => '#DC2626', // rojo
];
?>
<!-- Leyenda de colores -->
<div class="anim-slide-up" style="display:flex; gap:var(--sp-3); flex-wrap:wrap; justify-content:flex-end; margin-bottom:var(--sp-3); font-size:11px; color:var(--text-secondary);">
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#059669;"></span> Activa</span>
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#F59E0B;"></span> En Mantenimiento</span>
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#2563EB;"></span> Finalizada</span>
    <span style="display:flex;align-items:center;gap:5px;"><span style="width:9px;height:9px;border-radius:50%;background:#DC2626;"></span> Inactiva</span>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['rutas'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-compass" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p><?php echo $hayFiltro ? 'No hay rutas que coincidan con el filtro.' : 'No hay rutas turísticas registradas.'; ?></p>
        </div>
    <?php else: ?>
        <?php foreach ($data['rutas'] ?? [] as $r): ?>
            <?php
                $color = $estadoColores[$r->estado ?? ''] ?? '#64748B';
                $rutaFinalizada = ($r->estado === Ruta::ESTADO_TERMINAL);
                $enMant = ($r->estado === 'En Mantenimiento');
                $cupo   = (int)($r->cupo_maximo ?? 0);
                $insc   = (int)($r->total_participantes ?? 0);
                $pct    = $cupo > 0 ? min(100, ($insc / $cupo) * 100) : 0;
                $occCls = $cupo > 0 && $insc >= $cupo ? 'is-full' : ($pct >= 80 ? 'is-high' : '');
            ?>
            <div class="sig-card act-card h-100" style="border-left-color:<?php echo $color; ?>;">
                <div class="act-card__head">
                    <span class="act-status" style="color:<?php echo $color; ?>; background:<?php echo $color; ?>1f;">
                        <span class="act-status__dot"></span><?php echo htmlspecialchars($r->estado ?? '—'); ?>
                    </span>
                    <span class="act-id">#<?php echo $r->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;">
                        <?php echo htmlspecialchars($r->nombre ?? ''); ?>
                    </h3>
                    <p class="text-clamp-2" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-3);">
                        <?php echo htmlspecialchars(strip_tags($r->descripcion ?? 'Sin descripción')); ?>
                    </p>
                    <?php if ($enMant && !empty($r->motivo_mantenimiento)): ?>
                    <div class="act-late" style="margin-bottom:12px; white-space:normal; align-items:flex-start;">
                        <i class="bi bi-tools" style="margin-top:1px;"></i> <?php echo htmlspecialchars($r->motivo_mantenimiento); ?>
                    </div>
                    <?php endif; ?>
                    <div class="act-chips">
                        <span class="act-chip"><span class="act-chip__dot"></span><?php echo htmlspecialchars($r->tipo_ruta ?: 'General'); ?></span>
                        <span class="act-chip"><i class="bi bi-pin-map"></i> <?php echo (int)$r->total_puntos; ?> paradas</span>
                    </div>
                    <div class="act-meta-list">
                        <?php if ($r->fecha_visita): ?>
                        <div class="act-meta"><i class="bi bi-calendar-event"></i><span><?php echo date('d/m/Y', strtotime($r->fecha_visita)); ?><?php if ($r->hora_visita): ?> — <?php echo substr($r->hora_visita, 0, 5); ?><?php endif; ?></span></div>
                        <?php endif; ?>
                        <?php if ($r->departamento_nombre): ?>
                        <div class="act-meta"><i class="bi bi-geo-alt"></i><span><?php echo htmlspecialchars($r->departamento_nombre); ?></span></div>
                        <?php endif; ?>
                        <div class="act-meta"><i class="bi bi-clock"></i><span><?php echo htmlspecialchars($r->duracion_estimada ?: 'Duración no definida'); ?></span></div>
                        <?php if (!empty($r->facilitador_nombre)): ?>
                        <div class="act-meta"><i class="bi bi-person-badge"></i><span><?php echo htmlspecialchars(trim($r->facilitador_nombre . ' ' . ($r->facilitador_apellido ?? ''))); ?></span></div>
                        <?php endif; ?>
                        <div style="margin-top:var(--sp-1);">
                            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:5px; color:var(--text-secondary);">
                                <span>Participantes</span>
                                <span style="color:var(--text-primary);"><?php echo $insc; ?> / <?php echo $cupo; ?></span>
                            </div>
                            <div class="act-occ-bar <?php echo $occCls; ?>"><span style="width:<?php echo $pct; ?>%;"></span></div>
                        </div>
                    </div>
                </div>
                <div class="act-card__foot">
                    <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $r->id; ?>"
                       class="btn-sig btn-sig--ghost btn-sig--sm" style="flex:1; justify-content:center; color:var(--teal-600); border-color:var(--teal-200);">
                        <i class="bi bi-geo"></i> Ver Ruta
                    </a>
                    <div style="display:flex; gap:var(--sp-1);">
                        <?php if (!$rutaFinalizada): ?>
                        <button class="row-action row-action--edit" title="Editar"
                                onclick='editarRuta(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <?php endif; ?>
                        <a href="<?php echo URL_ROOT; ?>/rutas/delete/<?php echo $r->id; ?>"
                           class="row-action row-action--del delete-btn" title="Eliminar">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php if ($totalPaginas > 1): ?>
<nav class="anim-slide-up" style="display:flex;align-items:center;justify-content:center;gap:6px;margin-bottom:var(--sp-8);flex-wrap:wrap;">
    <?php
    $win = 2;
    $ini = max(1, $pagina - $win);
    $fin = min($totalPaginas, $pagina + $win);
    ?>
    <?php if ($pagina > 1): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, 1); ?>"><i class="bi bi-chevron-double-left"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $pagina - 1); ?>"><i class="bi bi-chevron-left"></i> Anterior</a>
    <?php endif; ?>
    <?php if ($ini > 1): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php for ($n = $ini; $n <= $fin; $n++): ?>
        <?php if ($n === $pagina): ?>
            <span class="btn-sig btn-sig--primary btn-sig--sm" style="pointer-events:none;min-width:38px;justify-content:center;"><?php echo $n; ?></span>
        <?php else: ?>
            <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $n); ?>" style="min-width:38px;justify-content:center;"><?php echo $n; ?></a>
        <?php endif; ?>
    <?php endfor; ?>
    <?php if ($fin < $totalPaginas): ?><span style="color:var(--text-tertiary);padding:0 4px;">…</span><?php endif; ?>
    <?php if ($pagina < $totalPaginas): ?>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $pagina + 1); ?>">Siguiente <i class="bi bi-chevron-right"></i></a>
        <a class="btn-sig btn-sig--ghost btn-sig--sm" href="<?php echo rutaUrl($flt, $totalPaginas); ?>"><i class="bi bi-chevron-double-right"></i></a>
    <?php endif; ?>
</nav>
<?php endif; ?>

<!-- Modal Ruta -->
<div class="modal fade" id="modalRuta" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <form action="<?php echo URL_ROOT; ?>/rutas/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRutaLabel">Nueva Ruta Turística</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rut_id">
                <div class="row g-4">

                    <!-- Nombre + duración -->
                    <div class="col-md-7">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre de la Ruta <span class="req">*</span></label>
                            <input type="text" name="nombre" id="rut_nombre" class="sig-input" required
                                   minlength="3" placeholder="Ej: Ruta Histórica de Cumaná">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Duración <span style="font-size:11px;font-weight:400;color:var(--text-tertiary);">H:MM</span></label>
                            <input type="text" name="duracion_estimada" id="rut_duracion" class="sig-input"
                                   pattern="^\d{1,2}:\d{2}$"
                                   placeholder="Ej: 2:30"
                                   title="Formato H:MM — Ej: 2:30 para 2h y media, 0:45 para 45 min">
                            <div class="invalid-feedback" id="msg_duracion">Formato requerido: H:MM (ej: 2:30)</div>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sig-field">
                            <label class="sig-field__label">Cupo Máx.</label>
                            <input type="number" name="cupo_maximo" id="rut_cupo" class="sig-input" value="20" min="1" max="200">
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Descripción</label>
                            <textarea name="descripcion" id="rut_descripcion" class="sig-textarea" rows="2"
                                      placeholder="Objetivos del recorrido y atractivos..."></textarea>
                        </div>
                    </div>

                    <!-- Tipo de ruta + Dificultad + Estado -->
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo de Ruta <span class="req">*</span></label>
                            <select name="tipo_ruta" id="rut_tipo" class="sig-select">
                                <option value="General">General</option>
                                <option value="Cumaná Histórica">Cumaná Histórica</option>
                                <option value="Exploradores de Cumaná">Exploradores de Cumaná</option>
                                <option value="Comunitaria">Comunitaria</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado</label>
                            <select name="estado" id="rut_estado" class="sig-select">
                                <?php foreach (Ruta::ESTADOS as $est): ?>
                                <option value="<?php echo $est; ?>"><?php echo $est === Ruta::ESTADO_TERMINAL ? $est . ' (ejecutada)' : $est; ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="sig-field__hint" id="rut_estado_hint" style="display:none; color:var(--danger-600);">
                                <i class="bi bi-exclamation-triangle"></i> Finalizada es definitiva: la ruta no podrá editarse después.
                            </span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Departamento responsable</label>
                            <select name="id_departamento" id="rut_depto" class="sig-select">
                                <option value="">Sin asignar</option>
                                <?php foreach ($data['departamentos'] ?? [] as $d): ?>
                                    <option value="<?php echo $d->id; ?>"><?php echo htmlspecialchars($d->nombre); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Facilitador -->
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Facilitador / Guía responsable</label>
                            <select name="id_facilitador" id="rut_facilitador" class="sig-select js-search">
                                <option value="">Sin asignar</option>
                                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                    <option value="<?php echo $e->id; ?>">
                                        <?php echo htmlspecialchars(($e->nombre ?? '') . ' ' . ($e->apellido ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Fecha y hora de visita -->
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha de Visita</label>
                            <input type="date" name="fecha_visita" id="rut_fecha" class="sig-input"
                                   min="<?php echo date('Y-m-d'); ?>">
                            <div class="invalid-feedback" id="msg_fecha_ruta">La fecha no puede ser anterior a hoy.</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora de Visita</label>
                            <input type="time" name="hora_visita" id="rut_hora" class="sig-input">
                        </div>
                    </div>

                    <!-- Prerequisito de formación (RN-F12) -->
                    <div class="col-12">
                        <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px;">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="rut_req_form" name="requiere_formacion" value="1">
                                <label class="form-check-label" for="rut_req_form" style="font-size:13px; cursor:pointer; user-select:none;">
                                    <i class="bi bi-mortarboard"></i> Requiere formación previa para inscribirse
                                    <span style="color:var(--text-tertiary); font-size:11px;">(ej: Exploradores de Cumaná)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Motivo de mantenimiento — solo visible cuando estado = En Mantenimiento -->
                    <div id="sec_motivo_mant" class="col-12" style="display:none;">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label">
                                <i class="bi bi-tools" style="color:#F59E0B;"></i>
                                Motivo de Mantenimiento <span class="req">*</span>
                            </label>
                            <textarea name="motivo_mantenimiento" id="rut_motivo_mant" class="sig-textarea" rows="2"
                                      placeholder="Describa el motivo por el que la ruta pasa a mantenimiento (ej: reparación de sendero, revisión de seguridad)..."></textarea>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);">
                    <i class="bi bi-check-lg"></i> Guardar Ruta
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleMotivoMant(estado) {
    var sec  = document.getElementById('sec_motivo_mant');
    var txt  = document.getElementById('rut_motivo_mant');
    var esMant = (estado === 'En Mantenimiento');
    sec.style.display = esMant ? 'block' : 'none';
    txt.required      = esMant;
    if (!esMant) txt.value = '';
}

function nuevaRuta() {
    document.getElementById('modalRutaLabel').innerText = 'Nueva Ruta Turística';
    document.getElementById('rut_id').value = '';
    document.querySelector('#modalRuta form').reset();
    document.getElementById('rut_cupo').value = '20';
    toggleMotivoMant('Activa');
}

function editarRuta(r) {
    document.getElementById('modalRutaLabel').innerText   = 'Editar: ' + r.nombre;
    document.getElementById('rut_id').value               = r.id;
    document.getElementById('rut_nombre').value           = r.nombre;
    document.getElementById('rut_descripcion').value      = r.descripcion;
    document.getElementById('rut_duracion').value         = r.duracion_estimada;
    document.getElementById('rut_estado').value           = r.estado;
    document.getElementById('rut_cupo').value             = r.cupo_maximo || 20;
    document.getElementById('rut_depto').value            = r.id_departamento || '';
    document.getElementById('rut_facilitador').value      = r.id_facilitador || '';
    document.getElementById('rut_fecha').value            = r.fecha_visita || '';
    document.getElementById('rut_hora').value             = r.hora_visita ? r.hora_visita.substring(0,5) : '';
    document.getElementById('rut_req_form').checked       = r.requiere_formacion == true || r.requiere_formacion === 't' || r.requiere_formacion === '1';
    document.getElementById('rut_tipo').value             = r.tipo_ruta || 'General';
    // Pre-rellenar motivo de mantenimiento
    document.getElementById('rut_motivo_mant').value      = r.motivo_mantenimiento || '';
    toggleMotivoMant(r.estado);
    new bootstrap.Modal(document.getElementById('modalRuta')).show();
}

// Mostrar/ocultar motivo al cambiar estado en el selector
document.getElementById('rut_estado').addEventListener('change', function() {
    toggleMotivoMant(this.value);
    var hint = document.getElementById('rut_estado_hint');
    if (hint) hint.style.display = (this.value === 'Finalizada') ? 'block' : 'none';
});

// Validación de duración en formato H:MM
document.getElementById('rut_duracion').addEventListener('input', function() {
    var val = this.value.trim();
    var msgEl = document.getElementById('msg_duracion');
    var ok = !val || /^\d{1,2}:\d{2}$/.test(val);
    this.classList.toggle('is-invalid', !ok);
    if (msgEl) msgEl.style.display = ok ? 'none' : 'block';
});

// Validación de fecha de visita >= hoy
document.getElementById('rut_fecha').addEventListener('change', function() {
    var val   = this.value;
    var hoy   = '<?php echo date('Y-m-d'); ?>';
    var msgEl = document.getElementById('msg_fecha_ruta');
    var ok    = !val || val >= hoy;
    this.classList.toggle('is-invalid', !ok);
    if (msgEl) msgEl.style.display = ok ? 'none' : 'block';
});

// Submit: bloquear si hay campos inválidos
document.querySelector('#modalRuta form').addEventListener('submit', function(e) {
    var durVal  = document.getElementById('rut_duracion').value.trim();
    var fechaVal = document.getElementById('rut_fecha').value;
    var hoy     = '<?php echo date('Y-m-d'); ?>';
    var errDur  = durVal && !/^\d{1,2}:\d{2}$/.test(durVal);
    var errFecha = fechaVal && fechaVal < hoy;
    if (errDur || errFecha) {
        e.preventDefault();
        if (errDur)   { document.getElementById('rut_duracion').classList.add('is-invalid'); document.getElementById('msg_duracion').style.display='block'; }
        if (errFecha) { document.getElementById('rut_fecha').classList.add('is-invalid');    document.getElementById('msg_fecha_ruta').style.display='block'; }
    }
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
