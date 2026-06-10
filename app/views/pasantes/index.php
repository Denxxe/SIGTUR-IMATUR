<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Pasantes y Practicantes</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Pasantes'; ?></h1>
        <p class="page__subtitle">Gestión institucional de practicantes universitarios y pasantes de media técnica.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesCsv" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesPdf" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/pasantes/crear" class="btn-sig btn-sig--primary">
            <i class="bi bi-person-plus"></i> Registrar Pasante
        </a>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre y Apellido</th>
                <th>Institución / Carrera</th>
                <th>Tutor Institucional</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['pasantes'])): ?>
                <tr>
                    <td colspan="6" class="sig-table-empty">No hay pasantes registrados actualmente.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['pasantes'] as $p): ?>
                    <tr>
                        <td class="cell-id"><?php echo $p->cedula; ?></td>
                        <td class="cell-strong"><?php echo $p->nombre . ' ' . $p->apellido; ?></td>
                        <td>
                            <div style="font-weight:600; font-size:13px; color:var(--text-primary);"><?php echo $p->institucion; ?></div>
                            <div style="font-size:12px; color:var(--text-tertiary);"><?php echo $p->carrera; ?></div>
                        </td>
                        <td>
                            <?php if ($p->id_tutor_institucional): ?>
                                <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                                    <i class="bi bi-person-check" style="color:var(--brand-500);"></i>
                                    <span><?php echo $p->tutor_nombre . ' ' . $p->tutor_apellido; ?></span>
                                </div>
                            <?php else: ?>
                                <span style="font-size:12px; color:var(--text-tertiary); font-style:italic;">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php
                            $badgeClass = 'sig-badge--neutral';
                            if ($p->estado == 'Postulado') $badgeClass = 'sig-badge--warning';
                            elseif ($p->estado == 'Aceptado') $badgeClass = 'sig-badge--info';
                            elseif ($p->estado == 'En Curso') $badgeClass = 'sig-badge--brand';
                            elseif ($p->estado == 'Culminado') $badgeClass = 'sig-badge--success';
                            elseif ($p->estado == 'Rechazado') $badgeClass = 'sig-badge--danger';
                            ?>
                            <span class="sig-badge <?php echo $badgeClass; ?>"><?php echo $p->estado; ?></span>
                        </td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/pasantes/detalle/<?php echo $p->id; ?>" class="row-action row-action--view" title="Ver Expediente">
                                <i class="bi bi-folder2-open"></i> Expediente
                            </a>
                            <a href="<?php echo URL_ROOT; ?>/pasantes/editar/<?php echo $p->id; ?>" class="row-action row-action--edit" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>