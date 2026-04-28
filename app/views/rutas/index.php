<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Gestión de Destinos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Rutas'; ?></h1>
        <p class="page__subtitle">Planificación y control de rutas turísticas y puntos de interés del municipio.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" style="background:linear-gradient(180deg, var(--teal-500), var(--teal-700)); box-shadow: var(--sh-glow-teal);" data-bs-toggle="modal" data-bs-target="#modalRuta" onclick="nuevaRuta()">
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
                        if ($r->estado == 'Activa') $statusClass = 'sig-badge--success';
                        elseif ($r->estado == 'Inactiva') $statusClass = 'sig-badge--danger';
                        elseif ($r->estado == 'En Mantenimiento') $statusClass = 'sig-badge--warning';
                    ?>
                    <span class="sig-badge <?php echo $statusClass; ?>"><?php echo $r->estado; ?></span>
                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:600;">#<?php echo $r->id; ?></span>
                </div>
                <div class="sig-card__body" style="flex:1;">
                    <h3 style="font-size:18px; font-weight:700; color:var(--text-primary); margin-bottom:var(--sp-2); line-height:1.3;"><?php echo $r->nombre ?? 'Ruta sin nombre'; ?></h3>
                    <p class="text-clamp-3" style="font-size:13px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                        <?php echo strip_tags($r->descripcion ?? 'Sin descripción'); ?>
                    </p>
                    
                    <div style="display:grid; gap:var(--sp-2);">
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-clock" style="color:var(--teal-500);"></i>
                            <span><strong>Duración:</strong> <?php echo $r->duracion_estimada; ?></span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-bar-chart" style="color:var(--teal-500);"></i>
                            <span><strong>Dificultad:</strong> 
                                <?php 
                                    $diffClass = 'sig-badge--neutral';
                                    if ($r->nivel_dificultad == 'Fácil') $diffClass = 'sig-badge--success';
                                    elseif ($r->nivel_dificultad == 'Moderado') $diffClass = 'sig-badge--info';
                                    elseif ($r->nivel_dificultad == 'Difícil') $diffClass = 'sig-badge--warning';
                                    elseif ($r->nivel_dificultad == 'Extremo') $diffClass = 'sig-badge--danger';
                                ?>
                                <span class="sig-badge sig-badge--sm <?php echo $diffClass; ?>"><?php echo $r->nivel_dificultad; ?></span>
                            </span>
                        </div>
                        <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                            <i class="bi bi-pin-map" style="color:var(--teal-500);"></i>
                            <span><strong>Paradas:</strong> <?php echo $r->total_puntos; ?> puntos</span>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-4); border-top:1px solid var(--border-subtle); display:flex; gap:var(--sp-2); justify-content:space-between; background:var(--bg-muted-subtle);">
                    <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $r->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm" style="flex:1; justify-content:center; color:var(--teal-600); border-color:var(--teal-200);">
                        <i class="bi bi-geo"></i> Ver Ruta
                    </a>
                    <div style="display:flex; gap:var(--sp-1);">
                        <button class="row-action row-action--edit" onclick='editarRuta(<?php echo htmlspecialchars(json_encode($r), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i class="bi bi-pencil"></i>
                        </button>
                        <a href="<?php echo URL_ROOT; ?>/rutas/delete/<?php echo $r->id; ?>" class="row-action row-action--del delete-btn">
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
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/rutas/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRutaLabel">Nueva Ruta Turística</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rut_id">
                <div class="row g-4">
                    <div class="col-md-8">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombre de la Ruta <span class="req">*</span></label>
                            <input type="text" name="nombre" id="rut_nombre" class="sig-input" required placeholder="Ej: Ruta Histórica de Cumaná">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Duración Estimada</label>
                            <input type="text" name="duracion_estimada" id="rut_duracion" class="sig-input" placeholder="Ej: 3 horas, 2 días...">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Descripción</label>
                            <textarea name="descripcion" id="rut_descripcion" class="sig-textarea" rows="3" placeholder="Información sobre el recorrido y atractivos..."></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
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
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Estado</label>
                            <select name="estado" id="rut_estado" class="sig-select">
                                <option value="Activa">Activa</option>
                                <option value="Inactiva">Inactiva</option>
                                <option value="En Mantenimiento">En Mantenimiento</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);"><i class="bi bi-check-lg"></i> Guardar Ruta</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevaRuta() {
        document.getElementById('modalRutaLabel').innerText = 'Nueva Ruta Turística';
        document.getElementById('rut_id').value = '';
        document.querySelector('#modalRuta form').reset();
    }
    function editarRuta(r) {
        document.getElementById('modalRutaLabel').innerText = 'Editar: ' + r.nombre;
        document.getElementById('rut_id').value = r.id;
        document.getElementById('rut_nombre').value = r.nombre;
        document.getElementById('rut_descripcion').value = r.descripcion;
        document.getElementById('rut_duracion').value = r.duracion_estimada;
        document.getElementById('rut_dificultad').value = r.nivel_dificultad;
        document.getElementById('rut_estado').value = r.estado;
        new bootstrap.Modal(document.getElementById('modalRuta')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
