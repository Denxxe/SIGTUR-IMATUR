<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Planificación · Agenda Institucional</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Agenda Institucional'; ?></h1>
        <p class="page__subtitle">Control y seguimiento de actividades institucionales, turísticas y comunitarias.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalActividad"
            onclick="nuevaActividad()">
            <i class="bi bi-calendar-plus"></i> Programar Actividad
        </button>
    </div>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['actividades'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary); background:var(--bg-surface); border-radius:var(--r-lg); border:1px dashed var(--border-default);">
            <i class="bi bi-calendar-x" style="font-size:48px; display:block; margin-bottom:var(--sp-4); color:var(--text-tertiary);"></i>
            <p>No hay actividades programadas en la agenda actualmente.</p>
        </div>
    <?php else: ?>
        <?php foreach ($data['actividades'] as $act): ?>
            <div class="sig-card h-100" style="display:flex; flex-direction:column;">
                <div class="sig-card__head" style="padding:var(--sp-3) var(--sp-4); border-bottom:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                    <?php
                    $typeBadge = 'sig-badge--neutral';
                    if ($act->tipo == 'Turística') $typeBadge = 'sig-badge--info';
                    elseif ($act->tipo == 'Institucional') $typeBadge = 'sig-badge--brand';
                    ?>
                    <span class="sig-badge <?php echo $typeBadge; ?>"><?php echo $act->tipo; ?></span>
                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:600;">ID #<?php echo $act->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;"><?php echo $act->nombre; ?></h3>
                    <p class="text-clamp-2" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <?php echo strip_tags($act->descripcion); ?>
                    </p>

                    <div style="display:grid; gap:var(--sp-2);">
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-calendar-event" style="color:var(--brand-500);"></i>
                            <span><?php echo date('d/m/Y', strtotime($act->fecha_inicio)); ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-geo-alt" style="color:var(--brand-500);"></i>
                            <span><?php echo $act->lugar; ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-currency-dollar" style="color:var(--brand-500);"></i>
                            <span>Presupuesto: <strong style="color:var(--text-primary);">$<?php echo number_format($act->presupuesto ?? 0, 2); ?></strong></span>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-3) var(--sp-4); border-top:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center; background:var(--bg-muted-subtle);">
                    <?php
                    $statusBadge = 'sig-badge--neutral';
                    if ($act->estado == 'Planificada') $statusBadge = 'sig-badge--warning';
                    elseif ($act->estado == 'En Ejecución') $statusBadge = 'sig-badge--brand';
                    elseif ($act->estado == 'Culminada') $statusBadge = 'sig-badge--success';
                    ?>
                    <span class="sig-badge <?php echo $statusBadge; ?>"><?php echo $act->estado; ?></span>
                    <div style="display:flex; gap:var(--sp-1);">
                        <button class="row-action row-action--edit" onclick='editarActividad(<?php echo htmlspecialchars(json_encode($act), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="<?php echo URL_ROOT; ?>/actividades/delete/<?php echo $act->id; ?>" class="row-action row-action--del delete-btn">
                            <i class="bi bi-trash"></i>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="modalActividad" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/actividades/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalActividadLabel">Planificar Actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="act_id">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre de la Actividad <span class="req">*</span></label>
                            <input type="text" name="nombre" id="act_nombre" class="sig-input" required placeholder="Ej: Operativo Carnaval 2024">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Tipo <span class="req">*</span></label>
                            <select name="tipo" id="act_tipo" class="sig-select" required>
                                <option value="Institucional">Institucional</option>
                                <option value="Turística">Turística</option>
                                <option value="Comunitaria">Comunitaria</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Descripción</label>
                            <textarea name="descripcion" id="act_descripcion" class="sig-textarea" rows="3" placeholder="Detalles, objetivos y logística de la actividad..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Inicio <span class="req">*</span></label>
                            <input type="date" name="fecha_inicio" id="act_fecha_inicio" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha Fin <span class="req">*</span></label>
                            <input type="date" name="fecha_fin" id="act_fecha_fin" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Lugar / Ubicación <span class="req">*</span></label>
                            <input type="text" name="lugar" id="act_lugar" class="sig-input" required placeholder="Ej: Playa San Luis, Cumaná">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Presupuesto ($)</label>
                            <input type="number" step="0.01" name="presupuesto" id="act_presupuesto" class="sig-input" placeholder="0.00">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado <span class="req">*</span></label>
                            <select name="estado" id="act_estado" class="sig-select" required>
                                <option value="Planificada">Planificada</option>
                                <option value="En Ejecución">En Ejecución</option>
                                <option value="Culminada">Culminada</option>
                                <option value="Cancelada">Cancelada</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar Agenda</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevaActividad() {
        document.getElementById('modalActividadLabel').innerText = 'Planificar Actividad';
        document.getElementById('act_id').value = '';
        document.querySelector('#modalActividad form').reset();
    }

    function editarActividad(act) {
        document.getElementById('modalActividadLabel').innerText = 'Editar: ' + act.nombre;
        document.getElementById('act_id').value = act.id;
        document.getElementById('act_nombre').value = act.nombre;
        document.getElementById('act_tipo').value = act.tipo;
        document.getElementById('act_descripcion').value = act.descripcion;
        document.getElementById('act_fecha_inicio').value = act.fecha_inicio;
        document.getElementById('act_fecha_fin').value = act.fecha_fin;
        document.getElementById('act_lugar').value = act.lugar;
        document.getElementById('act_presupuesto').value = act.presupuesto;
        document.getElementById('act_estado').value = act.estado;
        new bootstrap.Modal(document.getElementById('modalActividad')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>