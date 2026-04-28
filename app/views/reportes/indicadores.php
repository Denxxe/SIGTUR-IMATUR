<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Dashboard Global
        </div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Resumen ejecutivo y métricas de desempeño institucional (KPIs) calculadas en tiempo real.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver a Reportes
        </a>
        <button class="btn-sig btn-sig--primary" onclick="window.print()">
            <i class="bi bi-printer"></i> Imprimir Reporte
        </button>
    </div>
</div>

<!-- Fila 1: Distribución de Personal e Inventario -->
<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-people" style="color:var(--brand-500);"></i> Empleados por Departamento
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartEmpDepto"></div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-box-seam" style="color:var(--success-500);"></i> Inventario por Categoría
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartInvCat"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fila 2: Estado de Bienes y Tendencia de Formación -->
<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-clipboard-check" style="color:var(--warning-500);"></i> Estado Físico del Inventario
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartInvCond"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title">
                    <i class="bi bi-graph-up-arrow" style="color:var(--accent-500);"></i> Tendencia de Capacitación (6 meses)
                </div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartTallMes"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tablas de Respaldo -->
<div class="row g-4 anim-slide-up" style="margin-bottom:var(--sp-8);">
    <div class="col-md-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title">Resumen de Personal</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Departamento</th>
                            <th style="text-align:center;">N° Empleados</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['empPorDepto'] as $e): ?>
                            <tr>
                                <td class="cell-strong"><?php echo $e->departamento; ?></td>
                                <td style="text-align:center; font-weight:700; color:var(--brand-600);"><?php echo $e->total; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sig-card">
            <div class="sig-card__head">
                <div class="sig-card__title">Resumen de Inventario</div>
            </div>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th style="text-align:center;">Total Bienes</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($data['invPorCat'] as $i): ?>
                            <tr>
                                <td class="cell-strong"><?php echo $i->categoria; ?></td>
                                <td style="text-align:center; font-weight:700; color:var(--success-600);"><?php echo $i->total; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ApexCharts Config -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textPrimary = getComputedStyle(document.body).getPropertyValue('--text-primary').trim();
    const borderSubtle = getComputedStyle(document.body).getPropertyValue('--border-subtle').trim();

    // 1. Empleados por Departamento
    <?php
        $lblD = []; $valD = [];
        foreach ($data['empPorDepto'] as $e) { $lblD[] = $e->departamento; $valD[] = (int)$e->total; }
    ?>
    new ApexCharts(document.querySelector("#chartEmpDepto"), {
        chart: { type: 'bar', height: 350, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Empleados', data: <?php echo json_encode($valD); ?> }],
        xaxis: { categories: <?php echo json_encode($lblD); ?>, labels: { style: { colors: textPrimary, fontSize: '11px' } }, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: textPrimary } } },
        theme: { mode: isDark ? 'dark' : 'light' },
        colors: ['#3B82F6'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '50%', distributed: true } },
        grid: { borderColor: borderSubtle, strokeDashArray: 4 },
        dataLabels: { enabled: true, style: { fontWeight: 800 } },
        legend: { show: false }
    }).render();

    // 2. Inventario por Categoría
    <?php
        $lblC = []; $valC = [];
        foreach ($data['invPorCat'] as $i) { $lblC[] = $i->categoria; $valC[] = (int)$i->total; }
    ?>
    new ApexCharts(document.querySelector("#chartInvCat"), {
        chart: { type: 'donut', height: 350, background: 'transparent' },
        series: <?php echo json_encode($valC); ?>,
        labels: <?php echo json_encode($lblC); ?>,
        colors: ['#10B981', '#3B82F6', '#F59E0B', '#8B5CF6', '#EC4899', '#06B6D4', '#F97316'],
        theme: { mode: isDark ? 'dark' : 'light' },
        legend: { position: 'bottom', labels: { colors: textPrimary } },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'TOTAL BIENES', color: textPrimary, fontSize: '12px', fontWeight: 800 } } } } },
        dataLabels: { enabled: false }
    }).render();

    // 3. Inventario por Condición
    <?php
        $lblCond = []; $valCond = [];
        $totalInv = 0;
        foreach ($data['invPorCondicion'] as $c) { $totalInv += (int)$c->total; }
        foreach ($data['invPorCondicion'] as $c) {
            $lblCond[] = $c->condicion;
            $valCond[] = $totalInv > 0 ? round(((int)$c->total / $totalInv) * 100) : 0;
        }
    ?>
    new ApexCharts(document.querySelector("#chartInvCond"), {
        chart: { type: 'radialBar', height: 350, background: 'transparent' },
        series: <?php echo json_encode($valCond); ?>,
        labels: <?php echo json_encode($lblCond); ?>,
        colors: ['#10B981', '#3B82F6', '#F59E0B', '#EF4444', '#64748B'],
        theme: { mode: isDark ? 'dark' : 'light' },
        plotOptions: {
            radialBar: {
                dataLabels: {
                    name: { fontSize: '13px', color: textPrimary },
                    value: { fontSize: '20px', fontWeight: 800, color: textPrimary, formatter: val => val + '%' },
                    total: { show: true, label: 'DISTRIBUCIÓN', color: textPrimary, fontSize: '11px', fontWeight: 700 }
                },
                hollow: { size: '40%' },
                track: { background: borderSubtle }
            }
        },
        legend: { show: true, position: 'bottom', labels: { colors: textPrimary } }
    }).render();

    // 4. Talleres por Mes
    <?php
        $lblM = []; $valM = [];
        foreach ($data['talleresPorMes'] as $t) { $lblM[] = $t->mes; $valM[] = (int)$t->total; }
    ?>
    new ApexCharts(document.querySelector("#chartTallMes"), {
        chart: { type: 'area', height: 350, background: 'transparent', toolbar: { show: false } },
        series: [{ name: 'Talleres', data: <?php echo json_encode($valM); ?> }],
        xaxis: { categories: <?php echo json_encode($lblM); ?>, labels: { style: { colors: textPrimary } }, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: textPrimary } } },
        theme: { mode: isDark ? 'dark' : 'light' },
        colors: ['#8B5CF6'],
        stroke: { curve: 'smooth', width: 3 },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.45, opacityTo: 0.05, stops: [20, 100] } },
        grid: { borderColor: borderSubtle, strokeDashArray: 4 },
        markers: { size: 5, colors: ['#8B5CF6'], strokeWidth: 3, strokeColors: isDark ? '#1e1e2d' : '#fff' }
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
