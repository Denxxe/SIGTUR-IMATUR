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
                        <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:var(--sp-3);">
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
                            <input type="number" name="cupo_maximo" id="tal_cupo" class="sig-input" value="30" min="1">
                        </div>
                    </div>

                    <!-- RN-F06: Oficio — solo para actividades externas (nueva actividad) -->
                    <div id="seccion_oficio" class="col-12" style="display:none;">
                        <hr style="margin:0 0 var(--sp-2);">
                        <p style="font-size:13px; font-weight:600; color:var(--brand-500); margin-bottom:var(--sp-3);">
                            <i class="bi bi-file-earmark-text"></i> Actividad externa — datos del oficio recibido
                        </p>
                        <div class="row g-3">
                            <div class="col-md-3">
                                <div class="sig-field">
                                    <label class="sig-field__label">N° de Oficio</label>
                                    <input type="text" name="oficio_numero" class="sig-input" placeholder="OF-2025-001">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="sig-field">
                                    <label class="sig-field__label">Fecha del Oficio <span class="req">*</span></label>
                                    <input type="date" name="oficio_fecha" id="oficio_fecha" class="sig-input">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="sig-field">
                                    <label class="sig-field__label">Asunto</label>
                                    <input type="text" name="oficio_asunto" class="sig-input" placeholder="Tema o motivo de la solicitud...">
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
// Transiciones válidas de estado (RN-F13)
const TRANSICIONES = {
    'Programado': ['Programado', 'En Curso', 'Cancelado'],
    'En Curso':   ['En Curso', 'Finalizado', 'Cancelado'],
    'Finalizado': ['Finalizado'],
    'Cancelado':  ['Cancelado']
};

function setOpcionesEstado(estadoActual) {
    const sel = document.getElementById('tal_estado');
    sel.innerHTML = '';
    (TRANSICIONES[estadoActual] || [estadoActual]).forEach(op => {
        const opt = document.createElement('option');
        opt.value = op;
        opt.textContent = op;
        if (op === estadoActual) opt.selected = true;
        sel.appendChild(opt);
    });
}

function nuevoTaller() {
    document.getElementById('modalTallerLabel').innerText = 'Programar Actividad';
    document.getElementById('tal_id').value = '';
    document.querySelector('#modalTaller form').reset();
    document.getElementById('tal_cupo').value = '30';
    document.getElementById('tal_tipo').value = 'Taller';
    setOpcionesEstado('Programado');
    document.getElementById('tal_es_interna').checked = false;
    actualizarModoInterno(false);
    ocultarOficio();
}

function editarTaller(t) {
    document.getElementById('modalTallerLabel').innerText = 'Editar: ' + t.nombre;
    document.getElementById('tal_id').value          = t.id;
    document.getElementById('tal_nombre').value      = t.nombre;
    document.getElementById('tal_descripcion').value = t.descripcion;
    document.getElementById('tal_fecha_inicio').value = t.fecha_inicio;
    document.getElementById('tal_fecha_fin').value   = t.fecha_fin || '';
    document.getElementById('tal_hora_inicio').value = t.hora_inicio || '';
    document.getElementById('tal_hora_fin').value    = t.hora_fin || '';
    document.getElementById('tal_ubicacion').value   = t.id_ubicacion_formacion || '';
    document.getElementById('tal_facilitador').value = t.id_facilitador;
    document.getElementById('tal_cupo').value        = t.cupo_maximo;
    document.getElementById('tal_tipo').value        = t.tipo_actividad || 'Taller';
    const esInterna = t.es_interna == true || t.es_interna === 't' || t.es_interna === '1';
    document.getElementById('tal_es_interna').checked = esInterna;
    document.getElementById('tal_tipo_ente').value = t.tipo_ente || '';
    actualizarModoInterno(esInterna);
    setOpcionesEstado(t.estado);
    // En edición nunca se muestra el oficio (ya fue registrado al crear)
    ocultarOficio();
    new bootstrap.Modal(document.getElementById('modalTaller')).show();
}

function ocultarOficio() {
    document.getElementById('seccion_oficio').style.display = 'none';
    document.getElementById('oficio_fecha').required = false;
}

function actualizarModoInterno(esInterna) {
    document.getElementById('bloque_tipo_ente').style.display = esInterna ? 'none' : 'flex';
    if (esInterna) ocultarOficio();
    // Si es interna, forzar re-evaluar la visibilidad de oficio
    if (!esInterna) evaluarOficio();
}

function evaluarOficio() {
    const esNueva    = !document.getElementById('tal_id').value;
    const esInterna  = document.getElementById('tal_es_interna').checked;
    const sel        = document.getElementById('tal_ubicacion');
    const opt        = sel.options[sel.selectedIndex];
    const esPropia   = opt && opt.value && opt.dataset.sedePropia === '1';
    const seccion    = document.getElementById('seccion_oficio');
    const fechaInput = document.getElementById('oficio_fecha');

    if (esNueva && !esInterna && opt && opt.value && !esPropia) {
        seccion.style.display = 'block';
        fechaInput.required   = true;
    } else {
        ocultarOficio();
    }
}

document.getElementById('tal_es_interna').addEventListener('change', function () {
    actualizarModoInterno(this.checked);
});

// RN-F06: mostrar sección oficio solo para nuevas actividades externas no internas
document.getElementById('tal_ubicacion').addEventListener('change', evaluarOficio);
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
