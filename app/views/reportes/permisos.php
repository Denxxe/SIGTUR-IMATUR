<?php require_once '../app/views/inc/header.php';
$qs = http_build_query(array_filter([
    'fecha_inicio' => $data['fecha_inicio'] ?? '',
    'fecha_fin'    => $data['fecha_fin']    ?? '',
    'categoria'    => $data['filtro_cat']   ?? '',
    'estado'       => $data['filtro_estado'] ?? '',
]));
$ff = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '—';
$estadoBadge = ['Pendiente'=>'sig-badge--warning','Aprobado'=>'sig-badge--success','Rechazado'=>'sig-badge--danger','Anulado'=>'sig-badge--neutral'];
$regs = $data['registros'] ?? [];
// Resumen
$tot = count($regs); $aprob = 0; $enCurso = 0; $reposos = 0;
foreach ($regs as $r) {
    if ($r->estado === 'Aprobado') $aprob++;
    if ($r->estatus_periodo === 'En curso') $enCurso++;
    if ($r->categoria === 'Reposo') $reposos++;
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow"><a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Personal</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Permisos y reposos por tipo, estado y período (incluye los que se solapan con el rango).</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarPermisosCsv?<?php echo $qs; ?>" class="btn-sig btn-sig--success btn-sig--sm"><i class="bi bi-file-earmark-spreadsheet"></i> Excel</a>
        <a href="<?php echo URL_ROOT; ?>/reportes/permisos?formato=pdf&<?php echo $qs; ?>" target="_blank" class="btn-sig btn-sig--danger btn-sig--sm"><i class="bi bi-file-earmark-pdf"></i> PDF</a>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5);">
    <div class="sig-card__body u-p-sp-4-sp-5">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/permisos" class="row g-3 align-items-end">
            <div class="col-md-3"><div class="sig-field"><label for="repoperm_fecha_inicio" class="sig-field__label">Desde</label>
                <input id="repoperm_fecha_inicio" type="date" name="fecha_inicio" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_inicio'] ?? ''); ?>"></div></div>
            <div class="col-md-3"><div class="sig-field"><label for="repoperm_fecha_fin" class="sig-field__label">Hasta</label>
                <input id="repoperm_fecha_fin" type="date" name="fecha_fin" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_fin'] ?? ''); ?>"></div></div>
            <div class="col-md-2"><div class="sig-field"><label for="repoperm_categoria" class="sig-field__label">Categoría</label>
                <select id="repoperm_categoria" name="categoria" class="sig-select">
                    <option value="">Todas</option>
                    <?php foreach (PermisoLaboral::CATEGORIAS as $c): ?>
                        <option value="<?php echo $c; ?>" <?php echo ($data['filtro_cat'] ?? '')===$c?'selected':''; ?>><?php echo $c; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-2"><div class="sig-field"><label for="repoperm_estado" class="sig-field__label">Estado</label>
                <select id="repoperm_estado" name="estado" class="sig-select">
                    <option value="">Todos</option>
                    <?php foreach (PermisoLaboral::ESTADOS as $es): ?>
                        <option value="<?php echo $es; ?>" <?php echo ($data['filtro_estado'] ?? '')===$es?'selected':''; ?>><?php echo $es; ?></option>
                    <?php endforeach; ?>
                </select></div></div>
            <div class="col-md-2"><button type="submit" class="btn-sig btn-sig--primary u-w-100pct u-h-42px"><i class="bi bi-search"></i> Filtrar</button></div>
        </form>
    </div>
</div>

<!-- KPIs -->
<div class="row g-3 mb-5 anim-slide-up">
    <?php
    $tiles = [['Total', $tot, '#3B82F6'], ['Aprobados', $aprob, '#059669'], ['En curso hoy', $enCurso, '#0EA5E9'], ['Reposos', $reposos, '#EF4444']];
    foreach ($tiles as $t): ?>
        <div class="col-md-3 col-6"><div class="sig-card" style="border-bottom:3px solid <?php echo $t[2]; ?>;">
            <div class="sig-card__body u-ta-center u-p-sp-5">
                <div class="u-fs-10px u-fw-700 u-c-text-tertiary u-tt-uppercase"><?php echo $t[0]; ?></div>
                <div style="font-size:28px;font-weight:900;color:<?php echo $t[2]; ?>;"><?php echo (int)$t[1]; ?></div>
            </div></div></div>
    <?php endforeach; ?>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr><th>Empleado / Cédula</th><th>Departamento</th><th>Categoría</th><th>Tipo</th><th>Desde</th><th>Hasta</th><th>Duración</th><th>Período</th><th>Estado</th></tr>
        </thead>
        <tbody>
            <?php if (empty($regs)): ?>
                <tr><td colspan="9" class="sig-table-empty">Sin registros en el rango y filtros seleccionados.</td></tr>
            <?php else: foreach ($regs as $r): ?>
                <tr>
                    <td><div class="u-fd-column" style="display:flex;">
                        <span class="cell-strong"><?php echo htmlspecialchars(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')); ?></span>
                        <span class="cell-id"><?php echo htmlspecialchars($r->cedula ?? 'S/C'); ?></span></div></td>
                    <td class="u-fs-12px u-c-text-secondary"><?php echo htmlspecialchars($r->departamento ?? '—'); ?></td>
                    <td><span class="sig-badge <?php echo $r->categoria==='Reposo'?'sig-badge--danger':'sig-badge--info'; ?>"><?php echo htmlspecialchars($r->categoria ?? '—'); ?></span></td>
                    <td class="u-fs-13px"><?php echo htmlspecialchars($r->tipo_permiso); ?></td>
                    <td class="u-ws-nowrap"><?php echo $ff($r->fecha_inicio); ?></td>
                    <td class="u-ws-nowrap"><?php echo $ff($r->fecha_fin); ?></td>
                    <td class="u-fs-12px"><?php echo htmlspecialchars($r->duracion ?? ($r->dias_solicitados . ' días')); ?></td>
                    <td><span class="sig-badge <?php echo $r->estatus_periodo==='En curso'?'sig-badge--info':'sig-badge--neutral'; ?>"><?php echo $r->estatus_periodo; ?></span></td>
                    <td><span class="sig-badge <?php echo $estadoBadge[$r->estado] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($r->estado); ?></span></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($regs)): ?>
    <div class="u-ta-right u-fs-12px u-c-text-tertiary u-mt-sp-2"><?php echo count($regs); ?> registro(s)</div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
