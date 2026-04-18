<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-person-video3"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Listado consolidado de pasantes instituciones de IMATUR</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesCsv" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesPdf" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn btn-outline-secondary">← Volver</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Cédula</th>
                        <th>Pasante</th>
                        <th>Institución</th>
                        <th>Tutor</th>
                        <th>Carrera</th>
                        <th class="text-center">Estado</th>
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
                            <td><?php echo $p->institucion; ?></td>
                            <td><?php echo $p->tutor_nombre ? $p->tutor_nombre . ' ' . $p->tutor_apellido : '<span class="text-muted fst-italic">No asignado</span>'; ?></td>
                            <td><?php echo $p->carrera; ?></td>
                            <td class="text-center">
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
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
