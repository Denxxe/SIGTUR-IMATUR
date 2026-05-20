<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Inventario
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Control patrimonial de bienes institucionales filtrado por condición y categoría.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarInventarioCsv?condicion=<?php echo urlencode($data['filtro_condicion'] ?? ''); ?>&categoria=<?php echo urlencode($data['filtro_categoria'] ?? ''); ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarInventarioPdf?condicion=<?php echo urlencode($data['filtro_condicion'] ?? ''); ?>&categoria=<?php echo urlencode($data['filtro_categoria'] ?? ''); ?>" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/inventario" class="row g-4 align-items-end">
            <div class="col-md-4">
                <div class="sig-field">
                    <label class="sig-field__label">Condición</label>
                    <select name="condicion" class="sig-select">
                        <option value="">Todas las condiciones</option>
                        <?php foreach (['Nuevo', 'Bueno', 'Regular', 'Dañado'] as $c): ?>
                            <option value="<?php echo $c; ?>" <?php if (($data['filtro_condicion'] ?? '') === $c) echo 'selected'; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sig-field">
                    <label class="sig-field__label">Categoría (contiene)</label>
                    <input type="text" name="categoria" class="sig-input" placeholder="Ej: mobiliario, tecnología..." value="<?php echo htmlspecialchars($data['filtro_categoria'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-sig btn-sig--primary" style="width:100%; height:42px;">
                    <i class="bi bi-filter"></i> Filtrar Resultados
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-2 col-6">
        <div class="sig-card" style="border-bottom: 3px solid var(--brand-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Total</span>
                <span style="font-size:28px; font-weight:900; color:var(--brand-600);"><?php echo $data['stats']->total ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="sig-card" style="border-bottom: 3px solid var(--success-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Nuevos</span>
                <span style="font-size:28px; font-weight:900; color:var(--success-600);"><?php echo $data['stats']->nuevos ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-2 col-6">
        <div class="sig-card" style="border-bottom: 3px solid var(--teal-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Buenos</span>
                <span style="font-size:28px; font-weight:900; color:var(--teal-600);"><?php echo $data['stats']->buenos ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sig-card" style="border-bottom: 3px solid var(--warning-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Regulares</span>
                <span style="font-size:28px; font-weight:900; color:var(--warning-600);"><?php echo $data['stats']->regulares ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sig-card" style="border-bottom: 3px solid var(--danger-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Dañados</span>
                <span style="font-size:28px; font-weight:900; color:var(--danger-600);"><?php echo $data['stats']->danados ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Código BN</th>
                <th>Nombre del Bien</th>
                <th>Categoría</th>
                <th>Ubicación</th>
                <th style="text-align:center;">Condición</th>
                <th>Marca / Modelo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['registros'])): ?>
                <tr>
                    <td colspan="6" class="sig-table-empty">Sin registros con los filtros seleccionados.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['registros'] ?? [] as $r): ?>
                    <?php
                        $condBadge = 'sig-badge--neutral';
                        if ($r->condicion === 'Nuevo')    $condBadge = 'sig-badge--success';
                        elseif ($r->condicion === 'Bueno')   $condBadge = 'sig-badge--brand';
                        elseif ($r->condicion === 'Regular') $condBadge = 'sig-badge--warning';
                        elseif ($r->condicion === 'Dañado')  $condBadge = 'sig-badge--danger';
                    ?>
                    <tr>
                        <td><span class="cell-id" style="font-family:var(--font-mono);"><?php echo htmlspecialchars($r->codigo_bn ?? '—'); ?></span></td>
                        <td><span class="cell-strong"><?php echo htmlspecialchars($r->nombre ?? '—'); ?></span></td>
                        <td><span class="sig-badge sig-badge--neutral"><?php echo htmlspecialchars($r->categoria ?? 'Sin categoría'); ?></span></td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($r->ubicacion ?? '—'); ?></td>
                        <td style="text-align:center;"><span class="sig-badge <?php echo $condBadge; ?>"><?php echo htmlspecialchars($r->condicion ?? '—'); ?></span></td>
                        <td style="font-size:12px; color:var(--text-secondary);">
                            <?php echo htmlspecialchars($r->marca ?? ''); ?>
                            <?php if (!empty($r->modelo)): ?> / <?php echo htmlspecialchars($r->modelo); ?><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
