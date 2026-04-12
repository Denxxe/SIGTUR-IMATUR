<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-arrow-left-right"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Despachos, devoluciones, bajas y mantenimiento preventivo</p>
    </div>
    <div class="col-md-4 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalMovimiento" onclick="nuevoMovimiento()">
            Registrar Movimiento
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">Fecha</th>
                    <th>Bien Cod#</th>
                    <th>Operación</th>
                    <th>Responsable</th>
                    <th>Descripción</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($data['actividades'])): ?>
                    <tr><td colspan="6" class="text-center py-4">No hay movimientos registrados.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['actividades'] as $act): ?>
                        <tr>
                            <td class="ps-4"><?php echo $act->fecha; ?></td>
                            <td>
                                <strong><?php echo $act->item_nombre; ?></strong><br>
                                <span class="badge bg-secondary"><?php echo $act->codigo_bn ?: 'Sin Cod'; ?></span>
                            </td>
                            <td>
                                <?php 
                                    $color = 'bg-secondary';
                                    if ($act->tipo_movimiento == 'Asignacion') $color = 'bg-primary';
                                    if ($act->tipo_movimiento == 'Devolucion') $color = 'bg-success';
                                    if ($act->tipo_movimiento == 'Mantenimiento') $color = 'bg-warning text-dark';
                                    if ($act->tipo_movimiento == 'Baja') $color = 'bg-danger';
                                ?>
                                <span class="badge <?php echo $color; ?>"><?php echo $act->tipo_movimiento; ?></span>
                            </td>
                            <td class="small">
                                <?php echo $act->emp_nombre ? ($act->emp_nombre . ' ' . $act->emp_apellido) : '<span class="text-muted">N/A</span>'; ?>
                            </td>
                            <td class="small"><?php echo substr($act->descripcion, 0, 50) . '...'; ?></td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick='editarMovimiento(<?php echo json_encode($act); ?>)'>Editar</button>
                                    <a href="<?php echo URL_ROOT; ?>/actividadesinventario/delete/<?php echo $act->id; ?>" class="btn btn-outline-danger delete-btn">Borrar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/actividadesinventario/store" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalMovimientoLabel">Registrar Movimiento de Inventario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="mov_id">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Bien Nacional / Ítem</label>
                    <select name="id_inventario" id="mov_inventario" class="form-select" required>
                        <option value="">Seleccione...</option>
                        <?php foreach($data['inventario'] as $inv): ?>
                            <option value="<?php echo $inv->id; ?>"><?php echo $inv->codigo_bn . ' - ' . $inv->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" name="fecha" id="mov_fecha" value="<?php echo date('Y-m-d'); ?>" class="form-control" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Operación</label>
                        <select name="tipo_movimiento" id="mov_tipo" class="form-select" required>
                            <option value="Asignacion">Asignación</option>
                            <option value="Devolucion">Devolución</option>
                            <option value="Traslado">Traslado</option>
                            <option value="Mantenimiento">Mantenimiento</option>
                            <option value="Baja">Dar de Baja</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Empleado Responsable (Opcional)</label>
                    <select name="id_empleado_responsable" id="mov_empleado" class="form-select">
                        <option value="">N/A (Ninguno en específico)</option>
                        <?php foreach($data['empleados'] as $emp): ?>
                            <option value="<?php echo $emp->id; ?>"><?php echo $emp->nombre . ' ' . $emp->apellido; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción / Novedad</label>
                    <textarea name="descripcion" id="mov_descripcion" class="form-control" rows="3" required placeholder="Motivo del movimiento, estado físico entregado, etc..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoMovimiento() {
        document.getElementById('modalMovimientoLabel').innerText = 'Registrar Movimiento';
        document.getElementById('mov_id').value = '';
        document.querySelector('#modalMovimiento form').reset();
    }
    function editarMovimiento(act) {
        document.getElementById('modalMovimientoLabel').innerText = 'Modificar Registro';
        document.getElementById('mov_id').value = act.id;
        document.getElementById('mov_inventario').value = act.id_inventario;
        document.getElementById('mov_fecha').value = act.fecha;
        document.getElementById('mov_tipo').value = act.tipo_movimiento;
        document.getElementById('mov_empleado').value = act.id_empleado_responsable || '';
        document.getElementById('mov_descripcion').value = act.descripcion;
        new bootstrap.Modal(document.getElementById('modalMovimiento')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
