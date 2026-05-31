<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Gestión de Destinos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Rutas'; ?></h1>
        <p class="page__subtitle">Planificación y control de rutas turísticas y puntos de interés del municipio.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary"
                style="background:linear-gradient(180deg, var(--teal-500), var(--teal-700)); box-shadow: var(--sh-glow-teal);"
                data-bs-toggle="modal" data-bs-target="#modalRuta" onclick="nuevaRuta()">
            <i class="bi bi-map"></i> Crear Nueva Ruta
        </button>
    </div>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['rutas'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-compass" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p>No hay rutas turísticas registradas.</p>
        </div>
    <?php else: ?>
        <?php foreach ($data['rutas'] ?? [] as $r): ?>
            <div class="sig-card h-100" style="display:flex; flex-direction:column;">
                <div class="sig-card__head" style="padding:var(--sp-3) var(--sp-4); border-bottom:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                    <?php
                        $statusClass = 'sig-badge--neutral';
                        if ($r->estado == 'Activa')           $statusClass = 'sig-badge--success';
                        elseif ($r->estado == 'Inactiva')     $statusClass = 'sig-badge--danger';
                        elseif ($r->estado == 'En Mantenimiento') $statusClass = 'sig-badge--warning';
                    ?>
                    <span class="sig-badge <?php echo $statusClass; ?>"><?php echo $r->estado; ?></span>
                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:600;">#<?php echo $r->id; ?></span>
                </div>
                <?php if ($r->estado === 'En Mantenimiento' && !empty($r->motivo_mantenimiento)): ?>
                <div style="padding:var(--sp-2) var(--sp-4); background:rgba(245,158,11,.07); border-bottom:1px solid rgba(245,158,11,.15); font-size:11px; color:#92400E; display:flex; align-items:flex-start; gap:var(--sp-2);">
                    <i class="bi bi-tools" style="flex-shrink:0; margin-top:1px;"></i>
                    <span><?php echo htmlspecialchars($r->motivo_mantenimiento); ?></span>
                </div>
                <?php endif; ?>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;">
                        <?php echo htmlspecialchars($r->nombre ?? ''); ?>
                    </h3>
                    <p class="text-clamp-3" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <?php echo strip_tags($r->descripcion ?? 'Sin descripción'); ?>
                    </p>
                    <div style="display:grid; gap:var(--sp-2);">
                        <?php if ($r->fecha_visita): ?>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-calendar-event" style="color:var(--teal-500);"></i>
                            <span><?php echo date('d/m/Y', strtotime($r->fecha_visita)); ?>
                                <?php if ($r->hora_visita): ?>— <?php echo substr($r->hora_visita, 0, 5); ?><?php endif; ?>
                            </span>
                        </div>
                        <?php endif; ?>
                        <?php if ($r->departamento_nombre): ?>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-building" style="color:var(--teal-500);"></i>
                            <span><?php echo htmlspecialchars($r->departamento_nombre); ?></span>
                        </div>
                        <?php endif; ?>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-clock" style="color:var(--teal-500);"></i>
                            <span><?php echo htmlspecialchars($r->duracion_estimada ?: '—'); ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-pin-map" style="color:var(--teal-500);"></i>
                            <span><?php echo (int)$r->total_puntos; ?> paradas &nbsp;·&nbsp;
                                  <?php echo (int)$r->total_participantes; ?> participantes</span>
                        </div>
                        <?php
                        $diffClass = 'sig-badge--neutral';
                        if ($r->nivel_dificultad == 'Fácil')    $diffClass = 'sig-badge--success';
                        elseif ($r->nivel_dificultad == 'Moderado') $diffClass = 'sig-badge--info';
                        elseif ($r->nivel_dificultad == 'Difícil')  $diffClass = 'sig-badge--warning';
                        elseif ($r->nivel_dificultad == 'Extremo')  $diffClass = 'sig-badge--danger';
                        ?>
                        <div>
                            <span class="sig-badge sig-badge--sm <?php echo $diffClass; ?>"><?php echo $r->nivel_dificultad; ?></span>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-4); border-top:1px solid var(--border-subtle); display:flex; gap:var(--sp-2); justify-content:space-between; background:var(--bg-muted-subtle);">
                    <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $r->id; ?>"
                       class="btn-sig btn-sig--ghost btn-sig--sm" style="flex:1; justify-content:center; color:var(--teal-600); border-color:var(--teal-200);">
                        <i class="bi bi-geo"></i> Ver Ruta
                    </a>
                    <div style="display:flex; gap:var(--sp-1);">
                        <button class="row-action row-action--edit"
                                onclick='editarRuta(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="<?php echo URL_ROOT; ?>/rutas/delete/<?php echo $r->id; ?>"
                           class="row-action row-action--del delete-btn">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

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
                            <label class="sig-field__label">Nivel de Dificultad</label>
                            <select name="nivel_dificultad" id="rut_dificultad" class="sig-select">
                                <option value="Fácil">Fácil</option>
                                <option value="Moderado">Moderado</option>
                                <option value="Difícil">Difícil</option>
                                <option value="Extremo">Extremo</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado</label>
                            <select name="estado" id="rut_estado" class="sig-select">
                                <option value="Activa">Activa</option>
                                <option value="Inactiva">Inactiva</option>
                                <option value="En Mantenimiento">En Mantenimiento</option>
                            </select>
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
                            <select name="id_facilitador" id="rut_facilitador" class="sig-select">
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
    document.getElementById('rut_dificultad').value       = r.nivel_dificultad;
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
