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
        <?php
        $qsI = http_build_query(array_filter([
            'condicion' => $data['filtro_condicion'] ?? '',
            'categoria' => $data['filtro_categoria'] ?? '',
            'ubicacion' => $data['filtro_ubicacion'] ?? '',
        ]));
        ?>
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarInventarioCsv?<?php echo $qsI; ?>" class="btn-sig btn-sig--success btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarInventarioPdf?<?php echo $qsI; ?>" class="btn-sig btn-sig--danger btn-sig--sm" target="_blank">
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
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/inventario" class="row g-3 align-items-end">
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Condición</label>
                    <select name="condicion" class="sig-select">
                        <option value="">Todas las condiciones</option>
                        <?php foreach (Inventario::CONDICIONES as $c): ?>
                            <option value="<?php echo $c; ?>" <?php if (($data['filtro_condicion'] ?? '') === $c) echo 'selected'; ?>><?php echo $c; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Categoría (contiene)</label>
                    <input type="text" name="categoria" class="sig-input" placeholder="Ej: mobiliario, tecnología..." value="<?php echo htmlspecialchars($data['filtro_categoria'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Ubicación (contiene)</label>
                    <input type="text" name="ubicacion" class="sig-input" placeholder="Ej: oficina, almacén..." value="<?php echo htmlspecialchars($data['filtro_ubicacion'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div style="display:flex;gap:var(--sp-2);">
                    <button type="submit" class="btn-sig btn-sig--primary" style="flex:1;height:42px;">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php if (!empty($data['filtro_condicion']) || !empty($data['filtro_categoria']) || !empty($data['filtro_ubicacion'])): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/inventario" class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-3 mb-6 anim-slide-up">
    <?php
    $invKpis = [
        ['label' => 'Total Bienes',    'val' => $data['stats']->total     ?? 0, 'color' => 'var(--brand-500)',   'txt' => 'var(--brand-600)'],
        ['label' => 'Nuevos',          'val' => $data['stats']->nuevos    ?? 0, 'color' => 'var(--success-500)', 'txt' => 'var(--success-600)'],
        ['label' => 'Buenos',          'val' => $data['stats']->buenos    ?? 0, 'color' => 'var(--teal-500)',    'txt' => 'var(--teal-600)'],
        ['label' => 'Regulares',       'val' => $data['stats']->regulares ?? 0, 'color' => 'var(--warning-500)', 'txt' => 'var(--warning-600)'],
        ['label' => 'Dañados',         'val' => $data['stats']->danados   ?? 0, 'color' => 'var(--danger-500)',  'txt' => 'var(--danger-600)'],
        ['label' => 'En mantenim.',    'val' => $data['stats']->reparacion ?? 0,'color' => '#8B5CF6',            'txt' => '#7C3AED'],
    ];
    foreach ($invKpis as $k): ?>
    <div class="col-md-2 col-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $k['color']; ?>;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-4) var(--sp-3);">
                <div style="font-size:9px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;"><?php echo $k['label']; ?></div>
                <div style="font-size:26px;font-weight:900;color:<?php echo $k['txt']; ?>;"><?php echo number_format($k['val']); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15" data-buscar-placeholder="Buscar por código, nombre, categoría o ubicación…" data-no-export>
    <table class="sig-table">
        <thead>
            <tr>
                <th>Código BN</th>
                <th>Nombre del Bien</th>
                <th>Categoría</th>
                <th>Ubicación</th>
                <th>Responsable</th>
                <th style="text-align:center;">Estatus</th>
                <th style="text-align:center;">Condición</th>
                <th>Marca / Modelo</th>
                <th>Serial</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['registros'])): ?>
                <tr>
                    <td colspan="9" class="sig-table-empty">Sin registros con los filtros seleccionados.</td>
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
                        <td><span class="cell-id" style="font-family:var(--font-mono);"><?php echo htmlspecialchars($r->codigo_bn ?: 'Sin asignar'); ?></span></td>
                        <td><span class="cell-strong"><?php echo htmlspecialchars($r->nombre ?? '—'); ?></span></td>
                        <td><span class="sig-badge sig-badge--neutral"><?php echo htmlspecialchars($r->categoria ?? 'Sin cat.'); ?></span></td>
                        <td style="font-size:12px;"><?php echo htmlspecialchars($r->ubicacion ?? '—'); ?></td>
                        <td style="font-size:12px;"><?php echo !empty($r->responsable) ? htmlspecialchars($r->responsable) : '<span style="color:var(--text-tertiary);">Sin asignar</span>'; ?></td>
                        <td style="text-align:center;"><span class="sig-badge <?php echo Inventario::ESTATUS_BADGES[$r->estatus ?? ''] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($r->estatus ?? '—'); ?></span></td>
                        <td style="text-align:center;"><span class="sig-badge <?php echo $condBadge; ?>"><?php echo htmlspecialchars($r->condicion ?? '—'); ?></span></td>
                        <td style="font-size:12px;color:var(--text-secondary);">
                            <?php echo htmlspecialchars($r->marca ?? '—'); ?>
                            <?php if (!empty($r->modelo)): ?><br><span style="color:var(--text-tertiary);"><?php echo htmlspecialchars($r->modelo); ?></span><?php endif; ?>
                        </td>
                        <td style="font-size:11px;font-family:var(--font-mono);color:var(--text-tertiary);"><?php echo htmlspecialchars($r->serial ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
