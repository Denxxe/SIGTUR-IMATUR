<?php require_once '../app/views/inc/header.php';
$registros = $data['registros'] ?? [];
$resumen   = $data['resumen'] ?? [];
$org       = trim($_GET['origen'] ?? '');
$ff = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '—';
function comUrl(string $org): string {
    $q = $org !== '' ? ['origen' => $org] : [];
    return URL_ROOT . '/reportes/comisionServicio' . ($q ? '?' . http_build_query($q) : '');
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Reporte</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Personal proveniente de Alcaldía o Gobernación (comisión de servicio). El personal propio de IMATUR no se incluye.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarComisionCsv<?php echo $org !== '' ? '?origen=' . urlencode($org) : ''; ?>" class="btn-sig btn-sig--success btn-sig--sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel
        </a>
        <button type="button" class="btn-sig btn-sig--danger btn-sig--sm no-print" onclick="window.print()"><i class="bi bi-file-earmark-pdf"></i> PDF</button>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost btn-sig--sm no-print"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Resumen por institución -->
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-4);">
    <?php
    $total = array_sum($resumen);
    $tiles = ['Total en comisión' => $total];
    foreach ($resumen as $k => $v) $tiles[$k] = $v;
    foreach ($tiles as $lbl => $val): ?>
        <div class="col">
            <div class="sig-card"><div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                <div style="font-size:1.8rem;font-weight:800;color:var(--text-primary);line-height:1.1;"><?php echo (int)$val; ?></div>
                <div style="font-size:11px;color:var(--text-secondary);text-transform:uppercase;letter-spacing:.04em;"><?php echo htmlspecialchars($lbl); ?></div>
            </div></div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filtro por institución de origen -->
<div class="anim-slide-up" style="display:flex;gap:8px;margin-bottom:var(--sp-4);flex-wrap:wrap;">
    <a href="<?php echo comUrl(''); ?>" class="btn-sig btn-sig--sm <?php echo $org === '' ? 'btn-sig--primary' : 'btn-sig--ghost'; ?>">Todas</a>
    <a href="<?php echo comUrl('Alcaldía'); ?>" class="btn-sig btn-sig--sm <?php echo $org === 'Alcaldía' ? 'btn-sig--primary' : 'btn-sig--ghost'; ?>">Alcaldía</a>
    <a href="<?php echo comUrl('Gobernación'); ?>" class="btn-sig btn-sig--sm <?php echo $org === 'Gobernación' ? 'btn-sig--primary' : 'btn-sig--ghost'; ?>">Gobernación</a>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15" data-buscar-placeholder="Buscar por nombre, cédula, cargo…">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Empleado</th>
                <th>Cédula</th>
                <th>Expediente</th>
                <th>Cargo</th>
                <th>Departamento</th>
                <th>Origen</th>
                <th>F. Ingreso</th>
                <th>Tiempo de servicio</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($registros)): ?>
                <tr><td colspan="8" class="sig-table-empty">No hay personal en comisión de servicio<?php echo $org !== '' ? ' de ' . htmlspecialchars($org) : ''; ?>.</td></tr>
            <?php else: foreach ($registros as $r): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')); ?></td>
                    <td><?php echo htmlspecialchars($r->cedula ?? '—'); ?></td>
                    <td><?php echo htmlspecialchars($r->nro_expediente ?? '—'); ?></td>
                    <td><span class="sig-badge sig-badge--info"><?php echo htmlspecialchars($r->cargo ?? '—'); ?></span></td>
                    <td style="font-size:13px;color:var(--text-secondary);"><?php echo htmlspecialchars($r->departamento ?? '—'); ?></td>
                    <td><span class="sig-badge sig-badge--warning"><i class="bi bi-arrow-left-right"></i> <?php echo htmlspecialchars($r->institucion_origen ?? '—'); ?></span></td>
                    <td><?php echo $ff($r->fecha_ingreso); ?></td>
                    <td><?php echo htmlspecialchars(Empleado::tiempoServicio($r->fecha_ingreso ?? null)); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
