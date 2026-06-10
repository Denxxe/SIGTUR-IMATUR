<?php require_once '../app/views/inc/header.php'; ?>

<?php
$qs = http_build_query(array_filter([
    'estado'        => $data['estado_filtro'] ?? '',
    'tipo_actividad'=> $data['tipo_filtro']   ?? '',
    'nombre'        => $data['nombre_filtro'] ?? '',
    'fecha_inicio'  => $data['fecha_inicio']  ?? '',
    'fecha_fin'     => $data['fecha_fin']     ?? '',
]));
$hayFiltro = !empty($data['estado_filtro']) || !empty($data['tipo_filtro']) || !empty($data['nombre_filtro']) || !empty($data['fecha_inicio']) || !empty($data['fecha_fin']);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Formación
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Reporte de Talleres'; ?></h1>
        <p class="page__subtitle">Estadísticas de actividades formativas, demografía de participantes y ocupación.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex;gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarTalleresCsv?<?php echo $qs; ?>" class="btn-sig btn-sig--success btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarTalleresPdf?<?php echo $qs; ?>" class="btn-sig btn-sig--danger btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/talleres" class="row g-3 align-items-end">
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Desde</label>
                    <input type="date" name="fecha_inicio" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_inicio'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Hasta</label>
                    <input type="date" name="fecha_fin" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_fin'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Estado</label>
                    <select name="estado" class="sig-select">
                        <option value="">Todos</option>
                        <?php foreach (['Programado','En Curso','Finalizado','Cancelado'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if (($data['estado_filtro'] ?? '') === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Tipo</label>
                    <select name="tipo_actividad" class="sig-select">
                        <option value="">Todos los tipos</option>
                        <?php foreach (['Taller','Charla','Inducción'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if (($data['tipo_filtro'] ?? '') === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Buscar</label>
                    <input type="text" name="nombre" class="sig-input" placeholder="Nombre de actividad..." value="<?php echo htmlspecialchars($data['nombre_filtro'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div style="display:flex;gap:var(--sp-2);">
                    <button type="submit" class="btn-sig btn-sig--primary" style="flex:1;height:42px;background:var(--accent-600);">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php if ($hayFiltro): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/talleres" class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar">
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
    $kpisDef = [
        ['label' => 'Total Actividades', 'val' => $data['stats']->total_talleres   ?? 0, 'color' => 'var(--brand-500)',   'txt' => 'var(--brand-600)'],
        ['label' => 'Finalizadas',        'val' => $data['stats']->finalizados      ?? 0, 'color' => 'var(--success-500)', 'txt' => 'var(--success-600)'],
        ['label' => 'En Curso',           'val' => $data['stats']->en_curso         ?? 0, 'color' => 'var(--accent-500)',  'txt' => 'var(--accent-600)'],
        ['label' => 'Programadas',        'val' => $data['stats']->programados      ?? 0, 'color' => 'var(--warning-500)', 'txt' => 'var(--warning-600)'],
        ['label' => 'Canceladas',         'val' => $data['stats']->cancelados       ?? 0, 'color' => 'var(--danger-500)',  'txt' => 'var(--danger-600)'],
        ['label' => 'Total Inscritos',    'val' => $data['stats']->total_participantes ?? 0, 'color' => '#8B5CF6', 'txt' => '#7C3AED'],
    ];
    foreach ($kpisDef as $k): ?>
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

<!-- Gráficas -->
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pie-chart" style="color:var(--accent-500);"></i> Distribución por Estado</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartTalleresEstado"></div></div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-bar-chart" style="color:var(--brand-500);"></i> Inscritos vs Cupo (primeros 8)</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);"><div id="chartTalleresCupo"></div></div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Actividad / Sede</th>
                <th>Tipo · Ámbito</th>
                <th>Facilitador</th>
                <th>Fecha</th>
                <th style="text-align:center;">Demografía</th>
                <th style="text-align:center;">Ocupación</th>
                <th>Estado</th>
                <th class="col-actions">Dossier</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['talleres'])): ?>
                <tr><td colspan="8" class="sig-table-empty">No se encontraron actividades con los filtros aplicados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['talleres'] as $t):
                    $cupoMax  = (int)($t->cupo_maximo    ?? 0);
                    $inscritos= (int)($t->total_inscritos ?? 0);
                    $pct      = $cupoMax > 0 ? min(100, round(($inscritos / $cupoMax) * 100)) : 0;
                    $pctColor = $pct >= 90 ? '#EF4444' : ($pct >= 70 ? '#F59E0B' : 'var(--brand-500)');
                    $estadoBadge = ['En Curso' => 'sig-badge--brand', 'Programado' => 'sig-badge--warning', 'Finalizado' => 'sig-badge--success', 'Cancelado' => 'sig-badge--danger'];
                    $estado = $t->estado ?? 'Desconocido';
                ?>
                <tr>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:2px;">
                            <span class="cell-strong"><?php echo htmlspecialchars($t->nombre ?? ''); ?></span>
                            <span style="font-size:11px;color:var(--text-tertiary);"><i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($t->sede ?: 'Sin sede'); ?></span>
                        </div>
                    </td>
                    <td>
                        <div style="display:flex;flex-direction:column;gap:2px;">
                            <span class="sig-badge sig-badge--sm sig-badge--neutral"><?php echo htmlspecialchars($t->tipo_actividad ?? 'Taller'); ?></span>
                            <span style="font-size:10px;color:var(--text-tertiary);"><?php echo ($t->es_interna ?? false) ? '🏛 Interna' : '🌐 Externa'; ?></span>
                        </div>
                    </td>
                    <td style="font-size:13px;font-weight:600;color:var(--text-secondary);"><?php echo htmlspecialchars(($t->facilitador_nombre ?? '') . ' ' . ($t->facilitador_apellido ?? '')); ?></td>
                    <td style="font-size:12px;color:var(--text-tertiary);white-space:nowrap;"><?php echo date('d/m/Y', strtotime($t->fecha_inicio ?? 'now')); ?></td>
                    <td style="text-align:center;">
                        <?php if (isset($t->total_atendidas) && $t->total_atendidas > 0): ?>
                            <div style="display:flex;justify-content:center;gap:var(--sp-3);">
                                <span title="Mujeres"  style="font-size:11px;font-weight:700;color:var(--brand-500);">♀ <?php echo (int)($t->mujeres ?? 0); ?></span>
                                <span title="Hombres"  style="font-size:11px;font-weight:700;color:var(--text-secondary);">♂ <?php echo (int)($t->hombres ?? 0); ?></span>
                                <span title="Niños/as" style="font-size:11px;font-weight:700;color:var(--success-500);">👶 <?php echo (int)($t->ninas ?? 0) + (int)($t->ninos ?? 0); ?></span>
                            </div>
                            <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);margin-top:2px;">Total: <?php echo (int)($t->total_atendidas ?? 0); ?></div>
                        <?php else: ?>
                            <span style="font-size:11px;color:var(--text-tertiary);font-style:italic;">Sin informe</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;">
                        <div style="font-size:12px;font-weight:700;color:var(--text-primary);margin-bottom:4px;"><?php echo $inscritos; ?> / <?php echo $cupoMax; ?></div>
                        <div style="height:5px;width:80px;background:var(--bg-muted);border-radius:3px;margin:0 auto;overflow:hidden;">
                            <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $pctColor; ?>;border-radius:3px;"></div>
                        </div>
                        <div style="font-size:10px;color:var(--text-tertiary);margin-top:2px;"><?php echo $pct; ?>%</div>
                    </td>
                    <td><span class="sig-badge <?php echo $estadoBadge[$estado] ?? 'sig-badge--neutral'; ?>"><?php echo $estado; ?></span></td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/reportes/dossier/<?php echo $t->id; ?>" class="row-action row-action--view" style="width:auto;padding:0 var(--sp-3);" title="Ver Dossier">
                            <i class="bi bi-file-earmark-person"></i> Dossier
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($data['talleres'])): ?>
    <div style="text-align:right;font-size:12px;color:var(--text-tertiary);margin-top:var(--sp-2);">
        <?php echo count($data['talleres']); ?> actividad(es) mostradas
    </div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const tp     = getComputedStyle(document.body).getPropertyValue('--text-primary').trim();
    const bs     = getComputedStyle(document.body).getPropertyValue('--border-subtle').trim();
    const theme  = { mode: isDark ? 'dark' : 'light' };

    new ApexCharts(document.querySelector('#chartTalleresEstado'), {
        chart: { type: 'donut', height: 300, background: 'transparent' },
        series: [
            <?php echo $data['stats']->programados ?? 0; ?>,
            <?php echo $data['stats']->en_curso    ?? 0; ?>,
            <?php echo $data['stats']->finalizados ?? 0; ?>,
            <?php echo $data['stats']->cancelados  ?? 0; ?>
        ],
        labels: ['Programado', 'En Curso', 'Finalizado', 'Cancelado'],
        colors: ['#FBBF24', '#3B82F6', '#10B981', '#EF4444'],
        theme,
        legend: { position: 'bottom', labels: { colors: tp } },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '65%', labels: {
            show: true, total: { show: true, label: 'TOTAL', color: tp, fontSize: '14px', fontWeight: '800' }
        }}}},
        dataLabels: { enabled: false }
    }).render();

    <?php
    $nombres = []; $ins = []; $cups = [];
    foreach (array_slice($data['talleres'] ?? [], 0, 8) as $t) {
        $lbl = mb_strlen($t->nombre ?? '') > 16 ? mb_substr($t->nombre, 0, 14) . '…' : ($t->nombre ?? 'N/A');
        $nombres[] = $lbl;
        $ins[]  = (int)($t->total_inscritos ?? 0);
        $cups[] = (int)($t->cupo_maximo    ?? 0);
    }
    ?>
    new ApexCharts(document.querySelector('#chartTalleresCupo'), {
        chart: { type: 'bar', height: 300, background: 'transparent', toolbar: { show: false } },
        series: [
            { name: 'Inscritos',    data: <?php echo json_encode($ins);  ?> },
            { name: 'Cupo Máximo', data: <?php echo json_encode($cups); ?> }
        ],
        xaxis: { categories: <?php echo json_encode($nombres); ?>, labels: { style: { colors: tp, fontSize: '10px' } }, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: tp } } },
        theme,
        colors: ['#3B82F6', '#E2E8F0'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        grid: { borderColor: bs, strokeDashArray: 4 },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: tp } }
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
