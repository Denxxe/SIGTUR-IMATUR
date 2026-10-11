<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Pasantes y Practicantes</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Gestión de Pasantes'; ?></h1>
        <p class="page__subtitle">Gestión institucional de practicantes universitarios y pasantes de media técnica.</p>
    </div>
    <div class="page__actions">
        <div class="u-gap-sp-2" style="display:flex;">
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

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10"
     data-titulo-export="Listado de Pasantes Registrados">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre y Apellido</th>
                <th>Institución / Carrera</th>
                <th>Tutor Institucional</th>
                <th class="text-center">Inicio</th>
                <th class="text-center">Fin</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['pasantes'])): ?>
                <tr>
                    <td colspan="8" class="sig-table-empty">No hay pasantes registrados actualmente.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['pasantes'] as $p): ?>
                    <tr>
                        <td class="cell-id"><?php echo htmlspecialchars($p->cedula ?? ''); ?></td>
                        <td class="cell-strong"><?php echo htmlspecialchars(trim(($p->nombre ?? '') . ' ' . ($p->apellido ?? ''))); ?></td>
                        <td>
                            <div class="u-fw-600 u-fs-13px u-c-text-primary"><?php echo htmlspecialchars($p->institucion ?? ''); ?></div>
                            <div class="u-fs-12px u-c-text-tertiary"><?php echo htmlspecialchars($p->carrera ?? ''); ?></div>
                        </td>
                        <td>
                            <?php if ($p->id_tutor_institucional): ?>
                                <div class="u-ai-center u-gap-sp-2 u-fs-13px u-c-text-secondary" style="display:flex;">
                                    <i class="bi bi-person-check u-c-brand-500"></i>
                                    <span><?php echo $p->tutor_nombre . ' ' . $p->tutor_apellido; ?></span>
                                </div>
                            <?php else: ?>
                                <span class="u-fs-12px u-c-text-tertiary u-fst-italic">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td class="u-ta-center u-fs-12px u-ws-nowrap">
                            <?php echo $p->fecha_inicio ? date('d/m/Y', strtotime($p->fecha_inicio)) : '—'; ?>
                        </td>
                        <td class="u-ta-center u-fs-12px u-ws-nowrap">
                            <?php echo $p->fecha_fin ? date('d/m/Y', strtotime($p->fecha_fin)) : '<span class="u-c-text-tertiary u-fst-italic">En curso</span>'; ?>
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
                            <span class="sig-badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($p->estado ?? ''); ?></span>
                        </td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/pasantes/detalle/<?php echo $p->id; ?>" class="row-action row-action--view" title="Ver Expediente">
                                <i class="bi bi-folder2-open"></i> Expediente
                            </a>
                            <a href="<?php echo URL_ROOT; ?>/pasantes/editar/<?php echo $p->id; ?>" class="row-action row-action--edit" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?php echo URL_ROOT; ?>/pasantes/eliminar/<?php echo $p->id; ?>" class="row-action row-action--del delete-btn" title="Eliminar" data-nombre="<?php echo htmlspecialchars($p->nombre . ' ' . $p->apellido); ?>">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>