<?php require_once '../app/views/inc/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-person-video3"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Gestión institucional de practicantes y pasantes.</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/pasantes/crear" class="btn btn-primary shadow-sm"><i class="bi bi-plus-lg"></i> Registrar Pasante</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre y Apellido</th>
                        <th>Institución Educativa</th>
                        <th>Tutor Asignado</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['pasantes'])): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">No hay pasantes registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach($data['pasantes'] as $p): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $p->cedula; ?></td>
                            <td><?php echo $p->nombre . ' ' . $p->apellido; ?></td>
                            <td>
                                <div><?php echo $p->institucion; ?></div>
                                <small class="text-muted"><?php echo $p->carrera; ?></small>
                            </td>
                            <td>
                                <?php if($p->id_tutor_institucional): ?>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-person"></i> <?php echo $p->tutor_nombre . ' ' . $p->tutor_apellido; ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Sin asignar</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $color = 'bg-secondary';
                                    if ($p->estado == 'Aceptado') $color = 'bg-primary';
                                    if ($p->estado == 'En Curso') $color = 'bg-success';
                                    if ($p->estado == 'Culminado') $color = 'bg-dark';
                                    if ($p->estado == 'Rechazado') $color = 'bg-danger';
                                    if ($p->estado == 'Postulado') $color = 'bg-warning text-dark';
                                ?>
                                <span class="badge <?php echo $color; ?>"><?php echo $p->estado; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="<?php echo URL_ROOT; ?>/pasantes/detalle/<?php echo $p->id; ?>" class="btn btn-sm btn-info" title="Expediente y Documentos"><i class="bi bi-folder2-open"></i> Expediente</a>
                                <a href="<?php echo URL_ROOT; ?>/pasantes/editar/<?php echo $p->id; ?>" class="btn btn-sm btn-warning" title="Editar"><i class="bi bi-pencil"></i></a>
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
