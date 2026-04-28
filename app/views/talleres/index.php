<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Formación · Capacitación</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Administración de talleres, cursos y jornadas de capacitación comunitaria.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalTaller" onclick="nuevoTaller()">
            <i class="bi bi-calendar-plus"></i> Programar Taller
        </button>
    </div>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['talleres'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-mortarboard" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p>No hay talleres registrados actualmente.</p>
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
                    <span class="sig-badge <?php echo $badgeClass; ?>"><?php echo $t->estado; ?></span>
                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:600;">ID #<?php echo $t->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;"><?php echo $t->nombre; ?></h3>
                    <p class="text-clamp-2" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <?php echo strip_tags($t->descripcion); ?>
                    </p>
                    
                    <div style="display:grid; gap:var(--sp-2);">
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-calendar-event" style="color:var(--brand-500);"></i>
                            <span><?php echo date('d/m/Y', strtotime($t->fecha_inicio)); ?></span>
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
                        <button class="row-action row-action--edit" onclick='editarTaller(<?php echo json_encode($t); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="<?php echo URL_ROOT; ?>/talleres/delete/<?php echo $t->id; ?>" class="row-action row-action--del delete-btn">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="modalTaller" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/talleres/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTallerLabel">Programar Taller</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="tal_id">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre del Taller <span class="req">*</span></label>
                            <input type="text" name="nombre" id="tal_nombre" class="sig-input" required placeholder="Ej: Introducción al Turismo Sostenible">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado <span class="req">*</span></label>
                            <select name="estado" id="tal_estado" class="sig-select" required>
                                <option value="Programado">Programado</option>
                                <option value="En Curso">En Curso</option>
                                <option value="Finalizado">Finalizado</option>
                                <option value="Cancelado">Cancelado</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Descripción</label>
                            <textarea name="descripcion" id="tal_descripcion" class="sig-textarea" rows="3" placeholder="Objetivos y contenido del taller..."></textarea>
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
                            <label class="sig-field__label">Sede de Formación</label>
                            <select name="id_ubicacion_formacion" id="tal_ubicacion" class="sig-select">
                                <option value="">Seleccione una sede...</option>
                                <?php if (!empty($data['ubicaciones'])): ?>
                                    <?php foreach ($data['ubicaciones'] as $u): ?>
                                        <option value="<?php echo $u->id; ?>"><?php echo $u->nombre; ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="sig-field">
                            <label class="sig-field__label">Facilitador <span class="req">*</span></label>
                            <select name="id_facilitador" id="tal_facilitador" class="sig-select" required>
                                <option value="">Seleccione un facilitador...</option>
                                <?php foreach ($data['empleados'] as $e): ?>
                                    <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?></option>
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
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar Taller</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoTaller() {
        document.getElementById('modalTallerLabel').innerText = 'Programar Taller';
        document.getElementById('tal_id').value = '';
        document.querySelector('#modalTaller form').reset();
        document.getElementById('tal_cupo').value = '30';
        document.getElementById('tal_estado').value = 'Programado';
    }
    function editarTaller(t) {
        document.getElementById('modalTallerLabel').innerText = 'Editar: ' + t.nombre;
        document.getElementById('tal_id').value = t.id;
        document.getElementById('tal_nombre').value = t.nombre;
        document.getElementById('tal_descripcion').value = t.descripcion;
        document.getElementById('tal_fecha_inicio').value = t.fecha_inicio;
        document.getElementById('tal_fecha_fin').value = t.fecha_fin;
        document.getElementById('tal_hora_inicio').value = t.hora_inicio;
        document.getElementById('tal_hora_fin').value = t.hora_fin;
        document.getElementById('tal_ubicacion').value = t.id_ubicacion_formacion;
        document.getElementById('tal_facilitador').value = t.id_facilitador;
        document.getElementById('tal_cupo').value = t.cupo_maximo;
        document.getElementById('tal_estado').value = t.estado;
        new bootstrap.Modal(document.getElementById('modalTaller')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
