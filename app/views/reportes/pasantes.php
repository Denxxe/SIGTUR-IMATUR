<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Académico
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Listado consolidado de practicantes y pasantes con estatus académico institucional.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesCsv" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesPdf" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="" class="anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card">
        <div class="sig-card__body" style="padding:var(--sp-3) var(--sp-5);">
            <div style="display:flex; flex-wrap:wrap; gap:var(--sp-3); align-items:flex-end;">
                <div class="sig-field" style="margin:0; min-width:180px;">
                    <label class="sig-field__label">Estado</label>
                    <select name="estado" class="sig-select">
                        <option value="">Todos los estados</option>
                        <?php foreach (['Postulado','Aceptado','En Curso','Culminado','Rechazado'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if (($data['filtro_estado'] ?? '') === $opt) echo 'selected'; ?>>
                                <?php echo $opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-sig btn-sig--primary btn-sig--sm">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <?php if (!empty($data['filtro_estado'])): ?>
                <a href="<?php echo URL_ROOT; ?>/reportes/pasantes" class="btn-sig btn-sig--ghost btn-sig--sm">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
                <?php endif; ?>
                <span style="font-size:12px; color:var(--text-tertiary); margin-left:auto;">
                    <?php echo count($data['pasantes']); ?> resultado(s)
                </span>
            </div>
        </div>
    </div>
</form>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Pasante / Estudiante</th>
                <th>Institución / Carrera</th>
                <th>Tutor IMATUR</th>
                <th style="text-align:center;">Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['pasantes'])): ?>
                <tr>
                    <td colspan="5" class="sig-table-empty">No se encontraron pasantes registrados en el sistema.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['pasantes'] as $p): ?>
                    <tr>
                        <td class="cell-id"><?php echo $p->cedula; ?></td>
                        <td class="cell-strong"><?php echo $p->nombre . ' ' . $p->apellido; ?></td>
                        <td>
                            <div style="font-weight:600; font-size:13px; color:var(--text-primary);"><?php echo $p->institucion; ?></div>
                            <div style="font-size:11px; color:var(--text-tertiary);"><?php echo $p->carrera; ?></div>
                        </td>
                        <td>
                            <?php if ($p->tutor_nombre): ?>
                                <div style="display:flex; align-items:center; gap:var(--sp-2); font-size:13px; color:var(--text-secondary);">
                                    <i class="bi bi-person-check" style="color:var(--brand-500);"></i>
                                    <span><?php echo $p->tutor_nombre . ' ' . $p->tutor_apellido; ?></span>
                                </div>
                            <?php else: ?>
                                <span style="font-size:12px; color:var(--text-tertiary); font-style:italic;">No asignado</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
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
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>