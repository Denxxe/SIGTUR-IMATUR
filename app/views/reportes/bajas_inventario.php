<?php require_once '../app/views/inc/header.php'; ?>

<?php
$qs = http_build_query(array_filter([
    'fecha_inicio' => $data['fecha_inicio'] ?? '',
    'fecha_fin'    => $data['fecha_fin']    ?? '',
    'categoria'    => $data['filtro_cat']   ?? '',
]));
$hayFiltro = !empty($data['fecha_inicio']) || !empty($data['fecha_fin']) || !empty($data['filtro_cat']);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Inventario
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Bienes Dados de Baja'; ?></h1>
        <p class="page__subtitle">Historial completo de bienes desincorporados del inventario activo.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex;gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarBajasInventarioCsv?<?php echo $qs; ?>" class="btn-sig btn-sig--success btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarBajasInventarioPdf?<?php echo $qs; ?>" class="btn-sig btn-sig--danger btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-6 anim-slide-up">
    <div class="col-md-4 col-6">
        <div class="sig-card" style="border-bottom:3px solid var(--danger-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Total Histórico de Bajas</div>
                <div style="font-size:28px;font-weight:900;color:var(--danger-600);"><?php echo number_format($data['total_hist'] ?? 0); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">desde el inicio del sistema</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="sig-card" style="border-bottom:3px solid var(--warning-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Bajas <?php echo date('Y'); ?></div>
                <div style="font-size:28px;font-weight:900;color:var(--warning-600);"><?php echo number_format($data['bajas_anio'] ?? 0); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">en el año en curso</div>
            </div>
        </div>
    </div>
    <div class="col-md-4 col-6">
        <div class="sig-card" style="border-bottom:3px solid var(--brand-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Resultados del Filtro</div>
                <div style="font-size:28px;font-weight:900;color:var(--brand-600);"><?php echo number_format(count($data['bajas'] ?? [])); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">bienes mostrados</div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/bajasInventario" class="row g-3 align-items-end">
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha baja desde</label>
                    <input type="date" name="fecha_inicio" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_inicio'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha baja hasta</label>
                    <input type="date" name="fecha_fin" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_fin'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <div class="sig-field">
                    <label class="sig-field__label">Categoría (contiene)</label>
                    <input type="text" name="categoria" class="sig-input" placeholder="Ej: mobiliario, tecnología..." value="<?php echo htmlspecialchars($data['filtro_cat'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div style="display:flex;gap:var(--sp-2);">
                    <button type="submit" class="btn-sig btn-sig--primary" style="flex:1;height:42px;">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php if ($hayFiltro): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/bajasInventario" class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
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
                <th>Marca / Modelo</th>
                <th>Serial</th>
                <th style="text-align:center;">Condición</th>
                <th>Fecha de Baja</th>
                <th>Dado de baja por</th>
                <th>Motivo</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['bajas'])): ?>
                <tr><td colspan="10" class="sig-table-empty">No hay bienes dados de baja con los filtros seleccionados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['bajas'] as $b):
                    $condCls = 'sig-badge--neutral';
                    if ($b->condicion === 'Nuevo')  $condCls = 'sig-badge--success';
                    elseif ($b->condicion === 'Bueno')  $condCls = 'sig-badge--info';
                    elseif ($b->condicion === 'Regular') $condCls = 'sig-badge--warning';
                    elseif ($b->condicion === 'Dañado') $condCls = 'sig-badge--danger';
                ?>
                <tr>
                    <td><span class="cell-id" style="font-family:var(--font-mono);color:var(--brand-600);"><?php echo htmlspecialchars($b->codigo_bn ?? 'S/N'); ?></span></td>
                    <td><span class="cell-strong"><?php echo htmlspecialchars($b->nombre ?? '—'); ?></span></td>
                    <td><span class="sig-badge sig-badge--neutral"><?php echo htmlspecialchars($b->categoria ?? '—'); ?></span></td>
                    <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($b->ubicacion ?? '—'); ?></td>
                    <td style="font-size:12px;color:var(--text-secondary);">
                        <?php echo htmlspecialchars($b->marca ?? '—'); ?>
                        <?php if (!empty($b->modelo)): ?><br><span style="color:var(--text-tertiary);"><?php echo htmlspecialchars($b->modelo); ?></span><?php endif; ?>
                    </td>
                    <td style="font-size:11px;font-family:var(--font-mono);color:var(--text-tertiary);"><?php echo htmlspecialchars($b->serial ?? '—'); ?></td>
                    <td style="text-align:center;"><span class="sig-badge <?php echo $condCls; ?>"><?php echo htmlspecialchars($b->condicion ?? '—'); ?></span></td>
                    <td style="font-size:12px;white-space:nowrap;"><?php echo $b->deleted_at ? date('d/m/Y H:i', strtotime($b->deleted_at)) : '—'; ?></td>
                    <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($b->eliminado_por ?? '—'); ?></td>
                    <td style="font-size:12px;color:var(--text-secondary);max-width:220px;white-space:normal;"><?php echo htmlspecialchars($b->motivo_baja ?? '—'); ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($data['bajas'])): ?>
    <div style="text-align:right;font-size:12px;color:var(--text-tertiary);margin-top:var(--sp-2);">
        <?php echo count($data['bajas']); ?> registro(s) mostrados
    </div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
