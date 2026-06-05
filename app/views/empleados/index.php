<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Gestión de Personal</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Personal'; ?></h1>
        <p class="page__subtitle">Registro y administración del personal activo de la institución.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/empleados/nuevo" class="btn-sig btn-sig--primary">
            <i class="bi bi-person-plus"></i> Registrar Empleado
        </a>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Expediente</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['empleados'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No hay empleados registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['empleados'] ?? [] as $emp): ?>
                    <tr>
                        <td class="cell-strong"><?php echo $emp->nro_expediente ?? 'N/A'; ?></td>
                        <td><?php echo $emp->cedula ?? 'N/A'; ?></td>
                        <td><?php echo ($emp->nombre ?? 'N/A') . ' ' . ($emp->apellido ?? ''); ?></td>
                        <td><span class="sig-badge sig-badge--info"><?php echo $emp->cargo ?? 'Sin cargo'; ?></span></td>
                        <td><?php echo $emp->departamento ?? 'Sin dpto.'; ?></td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/empleados/detalle/<?php echo $emp->id; ?>" class="row-action">
                                <i class="bi bi-folder2-open"></i> Expediente
                            </a>
                            <a href="<?php echo URL_ROOT; ?>/empleados/editar/<?php echo $emp->id; ?>" class="row-action row-action--edit">
                                <i class="bi bi-pencil"></i> Editar
                            </a>
                            <a href="<?php echo URL_ROOT; ?>/empleados/delete/<?php echo $emp->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i> Baja
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
