<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Eventos y Excursiones</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Gestión de excursiones, visitas guiadas y eventos programados en las rutas del municipio.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" style="background:var(--teal-600); box-shadow: var(--sh-glow-teal);" data-bs-toggle="modal" data-bs-target="#modalActividadRuta" onclick="nuevaActividad()">
            <i class="bi bi-calendar-check"></i> Programar Actividad
        </button>
    </div>
</div>

<div class="anim-slide-up" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(320px, 1fr)); gap:var(--sp-6); margin-bottom:var(--sp-8);">
    <?php if (empty($data['actividades'])): ?>
        <div style="grid-column:1/-1; text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
            <i class="bi bi-calendar-event" style="font-size:48px; display:block; margin-bottom:var(--sp-4);"></i>
            <p>No hay actividades turísticas programadas actualmente.</p>
        </div>
    <?php else: ?>
        <?php foreach ($data['actividades'] as $a): ?>
            <div class="sig-card h-100" style="display:flex; flex-direction:column; border-left: 4px solid var(--teal-500);">
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--teal-700); margin-bottom:var(--sp-1);"><?php echo $a->nombre; ?></h3>
                    <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <i class="bi bi-signpost-2"></i>
                        <span><?php echo $a->ruta_nombre; ?></span>
                    </div>
                    
                    <p style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4); line-height:1.5;">
                        <?php echo $a->descripcion; ?>
                    </p>
                    
                    <div style="background:var(--bg-muted); padding:var(--sp-3); border-radius:var(--r-md); display:grid; gap:var(--sp-2);">
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:12px; color:var(--text-primary); font-weight:600;">
                            <i class="bi bi-calendar" style="color:var(--teal-500);"></i>
                            <span><?php echo $a->fecha ? date('d/m/Y', strtotime($a->fecha)) : 'Por definir'; ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:12px; color:var(--text-primary); font-weight:600;">
                            <i class="bi bi-person-badge" style="color:var(--teal-500);"></i>
                            <span><?php echo $a->emp_nombre ? ($a->emp_nombre . ' ' . $a->emp_apellido) : '<span style="color:var(--danger-500);">Sin guía asignado</span>'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-3) var(--sp-4); border-top:1px solid var(--border-subtle); display:flex; justify-content:flex-end; gap:var(--sp-2);">
                    <button class="row-action row-action--edit" onclick='editarActividad(<?php echo htmlspecialchars(json_encode($a), ENT_QUOTES, "UTF-8"); ?>)'>
                        <i class="bi bi-pencil"></i> Editar
                    </button>
                    <a href="<?php echo URL_ROOT; ?>/actividadesruta/delete/<?php echo $a->id; ?>" class="row-action row-action--del delete-btn">
                        <i class="bi bi-trash"></i> Cancelar
                    </a>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="modalActividadRuta" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/actividadesruta/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalActividadRutaLabel">Programar Actividad</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="act_id">
                
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Ruta Turística <span class="req">*</span></label>
                    <select name="id_ruta" id="act_ruta" class="sig-select" required>
                        <option value="">Seleccione ruta principal...</option>
                        <?php foreach($data['rutas'] as $r): ?>
                            <option value="<?php echo $r->id; ?>"><?php echo $r->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Nombre del Evento / Excursión <span class="req">*</span></label>
                    <input type="text" name="nombre" id="act_nombre" class="sig-input" required placeholder="Ej: Full Day Casco Histórico">
                </div>
                
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha</label>
                            <input type="date" name="fecha" id="act_fecha" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Guía Responsable</label>
                            <select name="id_empleado_responsable" id="act_empleado" class="sig-select">
                                <option value="">Sin asignar / Pendiente</option>
                                <?php foreach($data['empleados'] as $emp): ?>
                                    <option value="<?php echo $emp->id; ?>"><?php echo $emp->nombre . ' ' . $emp->apellido; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                
                <div class="sig-field">
                    <label class="sig-field__label">Descripción de Actividades</label>
                    <textarea name="descripcion" id="act_descripcion" class="sig-textarea" rows="3" placeholder="Detalles de la logística, itinerario, etc..."></textarea>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);"><i class="bi bi-check-lg"></i> Guardar Actividad</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevaActividad() {
        document.getElementById('modalActividadRutaLabel').innerText = 'Programar Actividad';
        document.getElementById('act_id').value = '';
        document.querySelector('#modalActividadRuta form').reset();
    }
    function editarActividad(a) {
        document.getElementById('modalActividadRutaLabel').innerText = 'Editar: ' + a.nombre;
        document.getElementById('act_id').value = a.id;
        document.getElementById('act_ruta').value = a.id_ruta;
        document.getElementById('act_nombre').value = a.nombre;
        document.getElementById('act_descripcion').value = a.descripcion;
        document.getElementById('act_fecha').value = a.fecha || '';
        document.getElementById('act_empleado').value = a.id_empleado_responsable || '';
        new bootstrap.Modal(document.getElementById('modalActividadRuta')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
