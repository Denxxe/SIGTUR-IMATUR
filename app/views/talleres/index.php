<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Formación · Capacitación</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Talleres'; ?></h1>
        <p class="page__subtitle">Administración de talleres y charlas de formación comunitaria.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalTaller" onclick="nuevoTaller()">
            <i class="bi bi-calendar-plus"></i> Programar Actividad
        </button>
    </div>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['talleres'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-mortarboard" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p>No hay actividades registradas actualmente.</p>
        </div>
    <?php else: ?>
        <?php foreach ($data['talleres'] as $t): ?>
            <div class="sig-card h-100" style="display:flex; flex-direction:column;">
                <div class="sig-card__head" style="padding:var(--sp-3) var(--sp-4); border-bottom:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                    <?php
                        $badgeClass = 'sig-badge--neutral';
                        if ($t->estado == 'Programado') $badgeClass = 'sig-badge--warning';
                        elseif ($t->estado == 'En Curso') $badgeClass = 'sig-badge--brand';
                        elseif ($t->estado == 'Finalizado') $badgeClass = 'sig-badge--success';
                        elseif ($t->estado == 'Cancelado') $badgeClass = 'sig-badge--danger';
                    ?>
                    <div style="display:flex; gap:var(--sp-2); align-items:center; flex-wrap:wrap;">
                        <span class="sig-badge <?php echo $badgeClass; ?>"><?php echo $t->estado; ?></span>
                        <span class="sig-badge sig-badge--neutral" style="font-size:10px;"><?php echo $t->tipo_actividad ?? 'Taller'; ?></span>
                        <?php if (!empty($t->es_interna)): ?>
                            <span class="sig-badge sig-badge--brand" style="font-size:10px;">Interna</span>
                        <?php else: ?>
                            <span class="sig-badge sig-badge--neutral" style="font-size:10px; color:var(--text-secondary);">
                                <?php echo $t->tipo_ente ? htmlspecialchars($t->tipo_ente) : 'Externa'; ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:600;">ID #<?php echo $t->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;"><?php echo $t->nombre ?? 'Actividad sin nombre'; ?></h3>
                    <p class="text-clamp-2" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <?php echo strip_tags($t->descripcion ?? 'Sin descripción'); ?>
                    </p>
                    <div style="display:grid; gap:var(--sp-2);">
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-calendar-event" style="color:var(--brand-500);"></i>
                            <span><?php echo date('d/m/Y', strtotime($t->fecha_inicio ?? 'today')); ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-geo-alt" style="color:var(--brand-500);"></i>
                            <span><?php echo $t->ubicacion ?? 'Sin asignar'; ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-person-badge" style="color:var(--brand-500);"></i>
                            <span><?php echo ($t->facilitador_nombre ?? 'Facilitador') . ' ' . ($t->facilitador_apellido ?? 'Pendiente'); ?></span>
                        </div>
                        <div style="margin-top:var(--sp-2);">
                            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:600; margin-bottom:4px;">
                                <span>Ocupación</span>
                                <span><?php echo $t->total_inscritos; ?> / <?php echo $t->cupo_maximo; ?></span>
                            </div>
                            <div style="height:6px; background:var(--bg-muted); border-radius:3px; overflow:hidden;">
                                <?php $porcentaje = ($t->cupo_maximo > 0) ? ($t->total_inscritos / $t->cupo_maximo) * 100 : 0; ?>
                                <div style="height:100%; width:<?php echo $porcentaje; ?>%; background:var(--brand-500); border-radius:3px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-4); border-top:1px solid var(--border-subtle); display:flex; gap:var(--sp-2); justify-content:space-between; background:var(--bg-muted-subtle);">
                    <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo $t->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm" style="flex:1; justify-content:center;">
                        <i class="bi bi-eye"></i> Detalle
                    </a>
                    <div style="display:flex; gap:var(--sp-1);">
                        <?php if (!in_array($t->estado, ['Finalizado', 'Cancelado'])): ?>
                            <button class="row-action" style="color:var(--brand-600);" title="Cambiar estado"
                                    onclick='abrirCambioEstado(<?php echo $t->id; ?>, "<?php echo $t->estado; ?>", <?php echo (int)$t->total_inscritos; ?>)'>
                                <i class="bi bi-arrow-repeat"></i>
                            </button>
                            <button class="row-action row-action--edit" onclick='editarTaller(<?php echo json_encode($t); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                        <?php endif; ?>
                        <a href="<?php echo URL_ROOT; ?>/talleres/delete/<?php echo $t->id; ?>" class="row-action row-action--del delete-btn">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal crear/editar actividad -->
<div class="modal fade" id="modalTaller" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/talleres/store" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="modalTallerLabel">Programar Actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="tal_id">
                <div class="row g-4">

                    <!-- Toggle Interna / Externa -->
                    <div class="col-12">
                        <div id="toggle_inner" style="padding:var(--sp-3) var(--sp-4); background:var(--bg-muted-subtle); border-radius:8px; border:1px solid transparent; transition:background .25s,border-color .25s; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:var(--sp-3);">
                            <div class="form-check form-switch" style="margin:0;">
                                <input class="form-check-input" type="checkbox" id="tal_es_interna" name="es_interna" value="1">
                                <label class="form-check-label" for="tal_es_interna" style="font-size:13px; cursor:pointer; user-select:none;">
                                    <i class="bi bi-building"></i> Actividad interna (para personal IMATUR)
                                </label>
                            </div>
                            <div id="bloque_tipo_ente" style="display:flex; align-items:center; gap:var(--sp-2);">
                                <label class="sig-field__label" style="margin:0; white-space:nowrap;">Dirigida a:</label>
                                <select name="tipo_ente" id="tal_tipo_ente" class="sig-select" style="min-width:180px;">
                                    <option value="">Sin especificar</option>
                                    <option value="Escuela">Escuela</option>
                                    <option value="Liceo">Liceo</option>
                                    <option value="Comunidad">Comunidad / Comuna</option>
                                    <option value="Prestador de Servicio">Prestador de Servicio</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre <span class="req">*</span></label>
                            <input type="text" name="nombre" id="tal_nombre" class="sig-input" required placeholder="Ej: Taller de Turismo Sostenible">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo <span class="req">*</span></label>
                            <select name="tipo_actividad" id="tal_tipo" class="sig-select" required>
                                <option value="Taller">Taller</option>
                                <option value="Charla">Charla / Conversatorio</option>
                                <option value="Inducción">Inducción</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado <span class="req">*</span></label>
                            <select name="estado" id="tal_estado" class="sig-select" required>
                                <option value="Programado">Programado</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Descripción</label>
                            <textarea name="descripcion" id="tal_descripcion" class="sig-textarea" rows="2" placeholder="Objetivos y contenido..."></textarea>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Inicio <span class="req">*</span></label>
                            <input type="date" name="fecha_inicio" id="tal_fecha_inicio" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Fin</label>
                            <input type="date" name="fecha_fin" id="tal_fecha_fin" class="sig-input">
                            <div class="invalid-feedback" id="msg_fecha_fin"></div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora Inicio</label>
                            <input type="time" name="hora_inicio" id="tal_hora_inicio" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora Fin</label>
                            <input type="time" name="hora_fin" id="tal_hora_fin" class="sig-input">
                            <div class="invalid-feedback" id="msg_hora_fin"></div>
                        </div>
                    </div>

                    <div class="col-md-5">
                        <div class="sig-field">
                            <label class="sig-field__label">Sede / Institución</label>
                            <select name="id_ubicacion_formacion" id="tal_ubicacion" class="sig-select">
                                <option value="">Seleccione una sede...</option>
                                <?php foreach ($data['ubicaciones'] ?? [] as $u): ?>
                                    <option value="<?php echo $u->id; ?>"
                                            data-sede-propia="<?php echo !empty($u->es_sede_propia) ? '1' : '0'; ?>">
                                        <?php echo $u->nombre; ?>
                                        <?php if (!empty($u->es_sede_propia)): ?> (IMATUR)<?php endif; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="sig-field">
                            <label class="sig-field__label">Facilitador <span class="req">*</span></label>
                            <select name="id_facilitador" id="tal_facilitador" class="sig-select" required>
                                <option value="">Seleccione un facilitador...</option>
                                <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                    <option value="<?php echo $e->id; ?>"><?php echo ($e->nombre ?? '') . ' ' . ($e->apellido ?? ''); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sig-field">
                            <label class="sig-field__label">Cupo Máx.</label>
                            <input type="number" name="cupo_maximo" id="tal_cupo" class="sig-input" value="30" min="1" max="200">
                        </div>
                    </div>

                    <!-- Motivo cancelación — solo visible al seleccionar Cancelado en edición -->
                    <div id="sec_edit_cancelado" class="col-12" style="display:none;">
                        <div class="sig-field">
                            <label class="sig-field__label">Motivo de cancelación <span class="req">*</span></label>
                            <textarea name="motivo_cancelacion" id="tal_motivo_cancelacion" class="sig-textarea" rows="3"
                                      placeholder="Indique el motivo por el que se cancela esta actividad..."></textarea>
                        </div>
                    </div>

                    <!-- Aviso participantes — visible al seleccionar En Curso en edición -->
                    <div id="sec_edit_en_curso" class="col-12" style="display:none;">
                        <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px; border-left:3px solid var(--brand-300);">
                            <p style="font-size:13px; color:var(--text-secondary); margin:0;">
                                <i class="bi bi-people" style="color:var(--brand-500);"></i>
                                Se requiere al menos 1 participante inscrito para iniciar la actividad.
                            </p>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btn_guardar" class="btn-sig btn-sig--primary" disabled><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal cambio rápido de estado -->
<div class="modal fade" id="modalCambioEstado" tabindex="-1">
    <div class="modal-dialog">
        <form id="formCambioEstado" action="" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-repeat"></i> Cambiar Estado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:var(--sp-4);">
                <input type="hidden" id="ce_id_taller" name="ce_id_taller">
                <input type="hidden" id="ce_estado_actual" name="ce_estado_actual">

                <div class="sig-field">
                    <label class="sig-field__label">Estado actual</label>
                    <div id="ce_badge_actual" style="margin-top:4px;"></div>
                </div>

                <div class="sig-field">
                    <label class="sig-field__label">Nuevo estado <span class="req">*</span></label>
                    <select id="ce_nuevo_estado" name="nuevo_estado" class="sig-select">
                        <option value="">Seleccione...</option>
                    </select>
                </div>

                <!-- Sección Cancelado -->
                <div id="sec_ce_cancelado" style="display:none;">
                    <div class="sig-field">
                        <label class="sig-field__label">Motivo de cancelación <span class="req">*</span></label>
                        <textarea id="ce_motivo" name="motivo_cancelacion" class="sig-textarea" rows="3"
                                  placeholder="Indique el motivo por el que se cancela..."></textarea>
                    </div>
                </div>

                <!-- Sección En Curso -->
                <div id="sec_ce_en_curso" style="display:none;">
                    <div id="ce_msg_participantes" style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px; border-left:3px solid var(--brand-300); font-size:13px;"></div>
                </div>

                <!-- Sección Finalizado — subir evidencias -->
                <div id="sec_ce_finalizado" style="display:none;">
                    <div class="sig-field">
                        <label class="sig-field__label">Evidencias <span class="req">*</span></label>
                        <input type="file" id="ce_evidencias" name="evidencias[]" class="sig-input"
                               multiple accept="image/*,application/pdf">
                        <p style="font-size:11px; color:var(--text-tertiary); margin-top:4px;">
                            <i class="bi bi-info-circle"></i> Imágenes (JPG, PNG, WebP) o PDF. Puede seleccionar varios archivos.
                        </p>
                        <div id="ce_evidencias_existentes" data-count="0" style="font-size:12px; color:var(--text-secondary); margin-top:var(--sp-1);"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" id="btn_ce_guardar" class="btn-sig btn-sig--primary" disabled>
                    <i class="bi bi-arrow-repeat"></i> Cambiar Estado
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.sig-input.is-invalid,
.sig-select.is-invalid {
    border-color: var(--danger-500) !important;
    box-shadow: 0 0 0 2px rgba(239,68,68,.12) !important;
}
.sig-input.is-invalid + .invalid-feedback,
.sig-select.is-invalid + .invalid-feedback {
    display: block;
    color: var(--danger-600);
    font-size: 11px;
    margin-top: 4px;
}
#btn_guardar:disabled,
#btn_ce_guardar:disabled {
    opacity: .5;
    cursor: not-allowed;
    pointer-events: none;
}
</style>
<script>
// ── Captura inicial de todas las opciones de sede ─────────────────────────
var sedesData = [];
(function() {
    var sel = document.getElementById('tal_ubicacion');
    sedesData = Array.from(sel.options).map(function(opt) {
        return { value: opt.value, text: opt.text, esPropia: opt.dataset.sedePropia === '1' };
    });
})();

// ── Filtrar sedes según modo interno/externo ──────────────────────────────
function filtrarSedes(esInterna) {
    var sel = document.getElementById('tal_ubicacion');
    var valorPrevio = sel.value;
    sel.innerHTML = '';
    sedesData.forEach(function(d) {
        if (!d.value) {
            var ph = document.createElement('option');
            ph.value = ''; ph.textContent = d.text;
            sel.appendChild(ph); return;
        }
        var mostrar = esInterna ? d.esPropia : !d.esPropia;
        if (mostrar) {
            var opt = document.createElement('option');
            opt.value = d.value; opt.textContent = d.text;
            opt.dataset.sedePropia = d.esPropia ? '1' : '0';
            sel.appendChild(opt);
        }
    });
    var disponible = Array.from(sel.options).some(function(o) { return o.value === valorPrevio; });
    sel.value = disponible ? valorPrevio : '';
}

// ── Estilo visual del bloque toggle ──────────────────────────────────────
function actualizarEstiloToggle(esInterna) {
    var inner = document.getElementById('toggle_inner');
    if (esInterna) {
        inner.style.background      = 'var(--brand-50)';
        inner.style.borderColor     = 'var(--brand-200)';
        inner.style.borderLeftWidth = '3px';
        inner.style.borderLeftColor = 'var(--brand-500)';
    } else {
        inner.style.background      = 'var(--bg-muted-subtle)';
        inner.style.borderColor     = 'transparent';
        inner.style.borderLeftWidth = '1px';
    }
}

// ── Transiciones válidas de estado (RN-F13) ───────────────────────────────
const TRANSICIONES = {
    'Programado': ['Programado', 'En Curso', 'Cancelado'],
    'En Curso':   ['En Curso', 'Finalizado', 'Cancelado'],
    'Finalizado': ['Finalizado'],
    'Cancelado':  ['Cancelado']
};

function setOpcionesEstado(estadoActual) {
    var sel = document.getElementById('tal_estado');
    sel.innerHTML = '';
    (TRANSICIONES[estadoActual] || [estadoActual]).forEach(function(op) {
        var opt = document.createElement('option');
        opt.value = op; opt.textContent = op;
        if (op === estadoActual) opt.selected = true;
        sel.appendChild(opt);
    });
}

// ── Validación de fechas y horas ──────────────────────────────────────────
function timeToMin(t) {
    var p = t.split(':'); return parseInt(p[0], 10) * 60 + parseInt(p[1], 10);
}

function validarFechas() {
    var fi    = document.getElementById('tal_fecha_inicio').value;
    var ff    = document.getElementById('tal_fecha_fin').value;
    var hi    = document.getElementById('tal_hora_inicio').value;
    var hf    = document.getElementById('tal_hora_fin').value;
    var inFf  = document.getElementById('tal_fecha_fin');
    var inHf  = document.getElementById('tal_hora_fin');
    var msgFf = document.getElementById('msg_fecha_fin');
    var msgHf = document.getElementById('msg_hora_fin');

    inFf.classList.remove('is-invalid'); inHf.classList.remove('is-invalid');
    if (msgFf) msgFf.textContent = ''; if (msgHf) msgHf.textContent = '';

    if (fi && ff && ff < fi) {
        inFf.classList.add('is-invalid');
        if (msgFf) msgFf.textContent = 'La fecha de fin no puede ser anterior a la de inicio.';
        return false;
    }
    // Mismo día (o sin fecha fin): duración mínima 15 minutos
    if (hi && hf && (!ff || ff === fi)) {
        if (timeToMin(hf) - timeToMin(hi) < 15) {
            inHf.classList.add('is-invalid');
            if (msgHf) msgHf.textContent = 'La duración mínima es de 15 minutos.';
            return false;
        }
    }
    return true;
}

// ── Mostrar/ocultar secciones condicionales en modal edición ──────────────
function actualizarSeccionesEstadoEdit(estado) {
    var secCancelado = document.getElementById('sec_edit_cancelado');
    var secEnCurso   = document.getElementById('sec_edit_en_curso');
    var motivo       = document.getElementById('tal_motivo_cancelacion');

    if (estado === 'Cancelado') {
        secCancelado.style.display = 'block';
        motivo.required = true;
    } else {
        secCancelado.style.display = 'none';
        motivo.required = false;
    }
    secEnCurso.style.display = (estado === 'En Curso') ? 'block' : 'none';
}

// ── Habilitar/deshabilitar botón Guardar ──────────────────────────────────
function checkFormValid() {
    var nombre       = (document.getElementById('tal_nombre').value || '').trim();
    var fechaInicio  = document.getElementById('tal_fecha_inicio').value;
    var facil        = document.getElementById('tal_facilitador').value;
    var fechasOk     = validarFechas();
    var canceladoVis = document.getElementById('sec_edit_cancelado').style.display !== 'none';
    var motivoOk     = !canceladoVis || (document.getElementById('tal_motivo_cancelacion').value || '').trim() !== '';
    var cupo         = parseInt(document.getElementById('tal_cupo').value || '0', 10);
    var cupoOk       = cupo >= 1 && cupo <= 200;

    document.getElementById('btn_guardar').disabled =
        !(nombre !== '' && fechaInicio !== '' && facil !== '' && fechasOk && motivoOk && cupoOk);
}

// ── Abrir modal — nueva actividad ─────────────────────────────────────────
function nuevoTaller() {
    document.getElementById('modalTallerLabel').innerText = 'Programar Actividad';
    document.getElementById('tal_id').value = '';
    document.querySelector('#modalTaller form').reset();
    document.getElementById('tal_cupo').value = '30';
    document.getElementById('tal_tipo').value = 'Taller';
    setOpcionesEstado('Programado');
    document.getElementById('tal_es_interna').checked = false;
    actualizarModoInterno(false);
    document.getElementById('sec_edit_cancelado').style.display = 'none';
    document.getElementById('sec_edit_en_curso').style.display  = 'none';
    checkFormValid();
}

// ── Abrir modal — editar actividad ────────────────────────────────────────
function editarTaller(t) {
    document.getElementById('modalTallerLabel').innerText = 'Editar: ' + t.nombre;
    document.getElementById('tal_id').value           = t.id;
    document.getElementById('tal_nombre').value       = t.nombre;
    document.getElementById('tal_descripcion').value  = t.descripcion;
    document.getElementById('tal_fecha_inicio').value = t.fecha_inicio;
    document.getElementById('tal_fecha_fin').value    = t.fecha_fin   || '';
    document.getElementById('tal_hora_inicio').value  = t.hora_inicio || '';
    document.getElementById('tal_hora_fin').value     = t.hora_fin    || '';
    document.getElementById('tal_facilitador').value  = t.id_facilitador;
    document.getElementById('tal_cupo').value         = t.cupo_maximo;
    document.getElementById('tal_tipo').value         = t.tipo_actividad || 'Taller';
    document.getElementById('tal_motivo_cancelacion').value = t.motivo_cancelacion || '';

    var esInterna = t.es_interna == true || t.es_interna === 't' || t.es_interna === '1';
    document.getElementById('tal_es_interna').checked = esInterna;
    document.getElementById('tal_tipo_ente').value    = t.tipo_ente || '';

    actualizarEstiloToggle(esInterna);
    document.getElementById('bloque_tipo_ente').style.display = esInterna ? 'none' : 'flex';
    filtrarSedes(esInterna);
    document.getElementById('tal_ubicacion').value = t.id_ubicacion_formacion || '';

    setOpcionesEstado(t.estado);
    actualizarSeccionesEstadoEdit(t.estado);
    checkFormValid();
    new bootstrap.Modal(document.getElementById('modalTaller')).show();
}

function actualizarModoInterno(esInterna) {
    document.getElementById('bloque_tipo_ente').style.display = esInterna ? 'none' : 'flex';
    actualizarEstiloToggle(esInterna);
    filtrarSedes(esInterna);
    checkFormValid();
}

// ── Cambio rápido de estado ───────────────────────────────────────────────
function estadoBadgeClass(estado) {
    var map = {
        'Programado': 'sig-badge--warning',
        'En Curso':   'sig-badge--brand',
        'Finalizado': 'sig-badge--success',
        'Cancelado':  'sig-badge--danger'
    };
    return map[estado] || 'sig-badge--neutral';
}

function abrirCambioEstado(id, estadoActual, totalInscritos) {
    document.getElementById('ce_id_taller').value      = id;
    document.getElementById('ce_estado_actual').value  = estadoActual;
    document.getElementById('formCambioEstado').action = '<?php echo URL_ROOT; ?>/talleres/cambiarEstado/' + id;

    document.getElementById('ce_badge_actual').innerHTML =
        '<span class="sig-badge ' + estadoBadgeClass(estadoActual) + '">' + estadoActual + '</span>';

    var transiciones = TRANSICIONES[estadoActual] || [];
    var sel = document.getElementById('ce_nuevo_estado');
    sel.innerHTML = '<option value="">Seleccione...</option>';
    transiciones.filter(function(op) { return op !== estadoActual; }).forEach(function(op) {
        var opt = document.createElement('option');
        opt.value = op; opt.textContent = op;
        sel.appendChild(opt);
    });
    sel.dataset.totalInscritos = totalInscritos;

    document.getElementById('sec_ce_cancelado').style.display  = 'none';
    document.getElementById('sec_ce_en_curso').style.display   = 'none';
    document.getElementById('sec_ce_finalizado').style.display = 'none';
    document.getElementById('ce_motivo').value  = '';
    document.getElementById('ce_evidencias').value = '';
    document.getElementById('ce_evidencias_existentes').innerHTML  = '';
    document.getElementById('ce_evidencias_existentes').dataset.count = '0';
    document.getElementById('btn_ce_guardar').disabled = true;

    new bootstrap.Modal(document.getElementById('modalCambioEstado')).show();
}

function checkCambioEstadoValid() {
    var estado = document.getElementById('ce_nuevo_estado').value;
    if (!estado) { document.getElementById('btn_ce_guardar').disabled = true; return; }
    var motivoOk    = estado !== 'Cancelado'  || (document.getElementById('ce_motivo').value || '').trim() !== '';
    var tieneExist  = parseInt(document.getElementById('ce_evidencias_existentes').dataset.count || '0', 10) > 0;
    var evidenciaOk = estado !== 'Finalizado' || tieneExist || document.getElementById('ce_evidencias').files.length > 0;
    document.getElementById('btn_ce_guardar').disabled = !(motivoOk && evidenciaOk);
}

// ── Event listeners ───────────────────────────────────────────────────────
document.getElementById('tal_es_interna').addEventListener('change', function() {
    actualizarModoInterno(this.checked);
});
document.getElementById('tal_estado').addEventListener('change', function() {
    actualizarSeccionesEstadoEdit(this.value);
    checkFormValid();
});
document.getElementById('tal_nombre').addEventListener('input', checkFormValid);
document.getElementById('tal_facilitador').addEventListener('change', checkFormValid);
document.getElementById('tal_fecha_inicio').addEventListener('change', checkFormValid);
document.getElementById('tal_fecha_fin').addEventListener('change', checkFormValid);
document.getElementById('tal_hora_inicio').addEventListener('change', checkFormValid);
document.getElementById('tal_hora_fin').addEventListener('change', checkFormValid);
document.getElementById('tal_motivo_cancelacion').addEventListener('input', checkFormValid);
document.getElementById('tal_cupo').addEventListener('input', checkFormValid);

document.getElementById('ce_nuevo_estado').addEventListener('change', function() {
    var estado         = this.value;
    var totalInscritos = parseInt(this.dataset.totalInscritos || '0', 10);

    document.getElementById('sec_ce_cancelado').style.display  = estado === 'Cancelado'  ? 'block' : 'none';
    document.getElementById('sec_ce_en_curso').style.display   = estado === 'En Curso'   ? 'block' : 'none';
    document.getElementById('sec_ce_finalizado').style.display = estado === 'Finalizado' ? 'block' : 'none';

    if (estado === 'En Curso') {
        var color = totalInscritos > 0 ? 'var(--success-600)' : 'var(--danger-600)';
        var icon  = totalInscritos > 0 ? 'bi-check-circle'    : 'bi-exclamation-circle';
        document.getElementById('ce_msg_participantes').innerHTML =
            '<i class="bi ' + icon + '" style="color:' + color + ';"></i> ' +
            '<strong>' + totalInscritos + '</strong> participante(s) inscrito(s).' +
            (totalInscritos === 0
                ? ' <span style="color:var(--danger-600);">Se requiere al menos 1 para iniciar.</span>'
                : '');
    }
    checkCambioEstadoValid();
});
document.getElementById('ce_motivo').addEventListener('input', checkCambioEstadoValid);
document.getElementById('ce_evidencias').addEventListener('change', checkCambioEstadoValid);

document.querySelector('#modalTaller form').addEventListener('submit', function(e) {
    if (!validarFechas()) e.preventDefault();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
