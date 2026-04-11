<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-compass"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalRuta" onclick="nuevaRuta()">
            Crear Ruta
        </button>
    </div>
</div>

<div class="row">
    <?php if (empty($data['rutas'])): ?>
        <div class="col-12 text-center py-5"><h5 class="text-muted">No hay rutas registradas.</h5></div>
    <?php else: ?>
        <?php foreach ($data['rutas'] as $r): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title fw-bold mb-0"><?php echo $r->nombre; ?></h5>
                            <span class="badge <?php 
                                echo $r->estado == 'Activa' ? 'bg-success' : ($r->estado == 'Inactiva' ? 'bg-secondary' : 'bg-warning text-dark'); ?>">
                                <?php echo $r->estado; ?>
                            </span>
                        </div>
                        <p class="card-text text-muted small"><?php echo substr($r->descripcion, 0, 100); ?></p>
                        <ul class="list-unstyled small">
                            <li><strong>Duración:</strong> <?php echo $r->duracion_estimada; ?></li>
                            <li><strong>Dificultad:</strong> 
                                <span class="badge <?php 
                                    echo $r->nivel_dificultad == 'Fácil' ? 'bg-success' : ($r->nivel_dificultad == 'Moderado' ? 'bg-info' : ($r->nivel_dificultad == 'Difícil' ? 'bg-warning text-dark' : 'bg-danger')); ?>">
                                    <?php echo $r->nivel_dificultad; ?>
                                </span>
                            </li>
                            <li><strong>Puntos/Paradas:</strong> <?php echo $r->total_puntos; ?></li>
                        </ul>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between">
                        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $r->id; ?>" class="btn btn-sm btn-outline-success">Ver Ruta</a>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick='editarRuta(<?php echo json_encode($r); ?>)'>Editar</button>
                            <a href="<?php echo URL_ROOT; ?>/rutas/delete/<?php echo $r->id; ?>" class="btn btn-outline-danger delete-btn">Borrar</a>
                        </div>
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
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalRutaLabel">Nueva Ruta Turística</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rut_id">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nombre de la Ruta</label>
                        <input type="text" name="nombre" id="rut_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Duración Estimada</label>
                        <input type="text" name="duracion_estimada" id="rut_duracion" class="form-control" placeholder="Ej: 3 horas">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" id="rut_descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nivel de Dificultad</label>
                        <select name="nivel_dificultad" id="rut_dificultad" class="form-select">
                            <option value="Fácil">Fácil</option>
                            <option value="Moderado">Moderado</option>
                            <option value="Difícil">Difícil</option>
                            <option value="Extremo">Extremo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" id="rut_estado" class="form-select">
                            <option value="Activa">Activa</option>
                            <option value="Inactiva">Inactiva</option>
                            <option value="En Mantenimiento">En Mantenimiento</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar Ruta</button>
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
