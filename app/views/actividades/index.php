<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-calendar-event"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalActividad" onclick="nuevaActividad()">
            Programar Actividad
        </button>
    </div>
</div>

<div class="row">
    <?php if (empty($data['actividades'])): ?>
        <div class="col-12 text-center py-5">
            <h4 class="text-muted">No hay actividades programadas.</h4>
        </div>
    <?php else: ?>
        <?php foreach ($data['actividades'] as $act): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header <?php 
                        echo $act->tipo == 'Turística' ? 'bg-primary' : ($act->tipo == 'Institucional' ? 'bg-info' : 'bg-secondary'); 
                        ?> text-white fw-bold">
                        <?php echo $act->tipo; ?>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title fw-bold"><?php echo $act->nombre; ?></h5>
                        <p class="card-text text-muted small"><?php echo substr($act->descripcion, 0, 100); ?>...</p>
                        <ul class="list-unstyled small mb-4">
                            <li><i class="bi bi-calendar-check"></i> <strong>Inicia:</strong> <?php echo $act->fecha_inicio; ?></li>
                            <li><i class="bi bi-geo-alt"></i> <strong>Lugar:</strong> <?php echo $act->lugar; ?></li>
                            <li><i class="bi bi-cash-stack"></i> <strong>Presupuesto:</strong> $<?php echo number_format($act->presupuesto, 2); ?></li>
                        </ul>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge <?php 
                                echo $act->estado == 'Planificada' ? 'bg-warning text-dark' : ($act->estado == 'En Ejecución' ? 'bg-primary' : 'bg-success'); 
                                ?>">
                                <?php echo $act->estado; ?>
                            </span>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarActividad(<?php echo json_encode($act); ?>)'>Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/actividades/delete/<?php echo $act->id; ?>" class="btn btn-outline-danger delete-btn">Borrar</a>
                            </div>
                        </div>
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
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nombre de la Actividad</label>
                        <input type="text" name="nombre" id="act_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Tipo</label>
                        <select name="tipo" id="act_tipo" class="form-select">
                            <option value="Institucional">Institucional</option>
                            <option value="Turística">Turística</option>
                            <option value="Comunitaria">Comunitaria</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" id="act_descripcion" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="act_fecha_inicio" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="act_fecha_fin" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Lugar / Ubicación</label>
                        <input type="text" name="lugar" id="act_lugar" class="form-control" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Presupuesto</label>
                        <input type="number" step="0.01" name="presupuesto" id="act_presupuesto" class="form-control">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Estado</label>
                        <select name="estado" id="act_estado" class="form-select">
                            <option value="Planificada">Planificada</option>
                            <option value="En Ejecución">En Ejecución</option>
                            <option value="Culminada">Culminada</option>
                            <option value="Cancelada">Cancelada</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar Agenda</button>
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
