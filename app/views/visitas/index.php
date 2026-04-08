<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-7">
        <h1><i class="bi bi-person-badge"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Gestión de flujo de personas ajenas a la institución.</p>
    </div>
    <div class="col-md-5">
        <div class="card border-primary shadow-sm bg-light">
            <div class="card-body">
                <form action="<?php echo URL_ROOT; ?>/visitas/registrar" method="POST">
                    <div class="mb-2">
                        <select name="id_visitante" class="form-select" required>
                            <option value="">¿Quién llega/sale? (Visitante)</option>
                            <?php foreach ($data['visitantes'] as $v): ?>
                                <option value="<?php echo $v->id; ?>"><?php echo $v->nombre . ' ' . $v->apellido; ?> (<?php echo $v->cedula; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select name="id_empleado" class="form-select">
                            <option value="">¿A quién visita? (Empleado)</option>
                            <?php foreach ($data['empleados'] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="text-muted small">Opcional si es entrada nueva.</small>
                    </div>
                    <div class="mb-2">
                        <input type="text" name="motivo" class="form-control" placeholder="Motivo de la visita">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 fw-bold">PROCESAR MARCAJE</button>
                    <input type="hidden" name="observaciones" value="Registro manual en recepción">
                </form>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Visitante</th>
                        <th>Visita a:</th>
                        <th>Motivo</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th class="text-center">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['visitas'])): ?>
                        <tr><td colspan="7" class="text-center py-4">Sin movimientos registrados hoy.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['visitas'] as $v): ?>
                            <tr>
                                <td class="ps-4 small"><?php echo date('d/m/Y', strtotime($v->fecha)); ?></td>
                                <td class="fw-bold"><?php echo $v->vis_nombre . ' ' . $v->vis_apellido; ?> <br><small class="text-muted">CI: <?php echo $v->vis_cedula; ?></small></td>
                                <td><?php echo $v->emp_nombre . ' ' . $v->emp_apellido; ?></td>
                                <td><small><?php echo $v->motivo; ?></small></td>
                                <td class="text-success fw-bold"><?php echo $v->hora_entrada; ?></td>
                                <td class="text-danger fw-bold"><?php echo $v->hora_salida ? $v->hora_salida : '---'; ?></td>
                                <td class="text-center">
                                    <a href="<?php echo URL_ROOT; ?>/visitas/delete/<?php echo $v->id; ?>" class="btn btn-sm btn-outline-danger delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
