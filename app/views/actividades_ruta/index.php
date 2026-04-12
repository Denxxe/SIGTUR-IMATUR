<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-calendar-event"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Gestión de excursiones, visitas guiadas y eventos en rutas</p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalActividadRuta" onclick="nuevaActividad()">
            Programar Actividad
        </button>
    </div>
</div>

<div class="row">
    <?php if (empty($data['actividades'])): ?>
        <div class="col-12 text-center py-5"><h5 class="text-muted">No hay actividades turísticas programadas.</h5></div>
    <?php else: ?>
        <?php foreach ($data['actividades'] as $a): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-success">
                    <div class="card-body">
                        <h5 class="card-title fw-bold text-success"><?php echo $a->nombre; ?></h5>
                        <h6 class="card-subtitle mb-2 text-muted"><i class="bi bi-signpost-2"></i> <?php echo $a->ruta_nombre; ?></h6>
                        
                        <p class="card-text small mt-3"><?php echo substr($a->descripcion, 0, 100); ?></p>
                        
                        <ul class="list-unstyled small bg-light p-2 rounded">
                            <li><strong><i class="bi bi-calendar"></i> Fecha:</strong> <?php echo $a->fecha ?: 'Por definir'; ?></li>
                            <li><strong><i class="bi bi-person-badge"></i> Guía:</strong> <?php echo $a->emp_nombre ? ($a->emp_nombre . ' ' . $a->emp_apellido) : '<span class="text-muted text-warning">Sin asignar</span>'; ?></li>
                        </ul>
                    </div>
                    <div class="card-footer bg-white border-top-0 d-flex justify-content-end">
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick='editarActividad(<?php echo json_encode($a); ?>)'>Editar</button>
                            <a href="<?php echo URL_ROOT; ?>/actividadesruta/delete/<?php echo $a->id; ?>" class="btn btn-outline-danger delete-btn">Cancelar</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal -->
<div class="modal fade" id="modalActividadRuta" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/actividadesruta/store" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalActividadRutaLabel">Programar Actividad Turística</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="act_id">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Ruta Turística</label>
                    <select name="id_ruta" id="act_ruta" class="form-select" required>
                        <option value="">Seleccione ruta principal...</option>
                        <?php foreach($data['rutas'] as $r): ?>
                            <option value="<?php echo $r->id; ?>"><?php echo $r->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre del Evento / Excursión</label>
                    <input type="text" name="nombre" id="act_nombre" class="form-control" required placeholder="Ej: Full Day Casco Histórico">
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha (Opcional)</label>
                        <input type="date" name="fecha" id="act_fecha" class="form-control">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Guía Responsable</label>
                        <select name="id_empleado_responsable" id="act_empleado" class="form-select">
                            <option value="">Sin asignar / Pendiente</option>
                            <?php foreach($data['empleados'] as $emp): ?>
                                <option value="<?php echo $emp->id; ?>"><?php echo $emp->nombre . ' ' . $emp->apellido; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción de Actividades</label>
                    <textarea name="descripcion" id="act_descripcion" class="form-control" rows="3"></textarea>
                </div>
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar Actividad</button>
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
