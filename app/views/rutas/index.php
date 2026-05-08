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
                                   placeholder="Ej: Ruta Histórica de Cumaná">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Duración Estimada</label>
                            <input type="text" name="duracion_estimada" id="rut_duracion" class="sig-input"
                                   placeholder="Ej: 3 horas">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="sig-field">
                            <label class="sig-field__label">Cupo Máx.</label>
                            <input type="number" name="cupo_maximo" id="rut_cupo" class="sig-input" value="20" min="1">
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

                    <!-- Dificultad + Estado -->
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
                            <input type="date" name="fecha_visita" id="rut_fecha" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora de Visita</label>
                            <input type="time" name="hora_visita" id="rut_hora" class="sig-input">
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
function nuevaRuta() {
    document.getElementById('modalRutaLabel').innerText = 'Nueva Ruta Turística';
    document.getElementById('rut_id').value = '';
    document.querySelector('#modalRuta form').reset();
    document.getElementById('rut_cupo').value = '20';
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
    new bootstrap.Modal(document.getElementById('modalRuta')).show();
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
