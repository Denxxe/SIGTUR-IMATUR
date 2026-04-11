<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-mortarboard"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTaller" onclick="nuevoTaller()">
            Programar Taller
        </button>
    </div>
</div>

<div class="row">
    <?php if (empty($data['talleres'])): ?>
        <div class="col-12 text-center py-5"><h5 class="text-muted">No hay talleres registrados.</h5></div>
    <?php else: ?>
        <?php foreach ($data['talleres'] as $t): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header fw-bold <?php 
                        echo $t->estado == 'Programado' ? 'bg-warning text-dark' : ($t->estado == 'En Curso' ? 'bg-primary text-white' : ($t->estado == 'Finalizado' ? 'bg-success text-white' : 'bg-secondary text-white')); ?>">
                        <?php echo $t->estado; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?php echo $t->nombre; ?></h5>
                        <p class="card-text text-muted small"><?php echo substr($t->descripcion, 0, 80); ?></p>
                        <ul class="list-unstyled small">
                            <li><strong>Fecha:</strong> <?php echo $t->fecha_inicio; ?></li>
                            <li><strong>Sede:</strong> <?php echo $t->ubicacion ?? 'Sin asignar'; ?></li>
                            <li><strong>Facilitador:</strong> <?php echo $t->facilitador_nombre . ' ' . $t->facilitador_apellido; ?></li>
                            <li><strong>Inscritos:</strong> <?php echo $t->total_inscritos; ?> / <?php echo $t->cupo_maximo; ?></li>
                        </ul>
                    </div>
                    <div class="card-footer bg-light d-flex justify-content-between">
                        <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo $t->id; ?>" class="btn btn-sm btn-outline-primary">Ver Detalle</a>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-info" onclick='editarTaller(<?php echo json_encode($t); ?>)'>Editar</button>
                            <a href="<?php echo URL_ROOT; ?>/talleres/delete/<?php echo $t->id; ?>" class="btn btn-outline-danger delete-btn">Borrar</a>
                        </div>
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
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTallerLabel">Programar Taller</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="tal_id">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nombre del Taller</label>
                        <input type="text" name="nombre" id="tal_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" id="tal_estado" class="form-select">
                            <option value="Programado">Programado</option>
                            <option value="En Curso">En Curso</option>
                            <option value="Finalizado">Finalizado</option>
                            <option value="Cancelado">Cancelado</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" id="tal_descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="tal_fecha_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="tal_fecha_fin" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Hora Inicio</label>
                        <input type="time" name="hora_inicio" id="tal_hora_inicio" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Hora Fin</label>
                        <input type="time" name="hora_fin" id="tal_hora_fin" class="form-control">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Sede de Formación</label>
                        <select name="id_ubicacion_formacion" id="tal_ubicacion" class="form-select">
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['ubicaciones'] as $u): ?>
                                <option value="<?php echo $u->id; ?>"><?php echo $u->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-bold">Facilitador (Empleado)</label>
                        <select name="id_facilitador" id="tal_facilitador" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['empleados'] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Cupo</label>
                        <input type="number" name="cupo_maximo" id="tal_cupo" class="form-control" value="30">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Taller</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoTaller() {
        document.getElementById('modalTallerLabel').innerText = 'Programar Taller';
        document.getElementById('tal_id').value = '';
        document.querySelector('#modalTaller form').reset();
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
