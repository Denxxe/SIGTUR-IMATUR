<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">SIGTUR-IMATUR</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Resumen operativo del Sistema Integral de Gestión Turística y Administrativa.</p>
    </div>
</div>

<!-- KPI Grid -->
<div class="kpi-grid anim-slide-up">
    <div class="kpi kpi--brand">
        <div class="kpi__icon"><i class="bi bi-people" style="font-size:20px"></i></div>
        <div class="kpi__label">Empleados Activos</div>
        <div class="kpi__value"><?php echo $data['totalEmpleados']; ?></div>
        <a href="<?php echo URL_ROOT; ?>/empleados/index" style="font-size:12px;font-weight:600;color:var(--brand-600);display:inline-flex;align-items:center;gap:4px">Ver Personal →</a>
    </div>
    <div class="kpi kpi--success">
        <div class="kpi__icon"><i class="bi bi-box-seam" style="font-size:20px"></i></div>
        <div class="kpi__label">Bienes en Inventario</div>
        <div class="kpi__value"><?php echo $data['totalInventario']; ?></div>
        <a href="<?php echo URL_ROOT; ?>/inventario/index" style="font-size:12px;font-weight:600;color:var(--success-600);display:inline-flex;align-items:center;gap:4px">Ver Inventario →</a>
    </div>
    <div class="kpi kpi--accent">
        <div class="kpi__icon"><i class="bi bi-mortarboard" style="font-size:20px"></i></div>
        <div class="kpi__label">Talleres Vigentes</div>
        <div class="kpi__value"><?php echo $data['totalTalleres']; ?></div>
        <a href="<?php echo URL_ROOT; ?>/talleres/index" style="font-size:12px;font-weight:600;color:var(--accent-600);display:inline-flex;align-items:center;gap:4px">Ver Formación →</a>
    </div>
    <div class="kpi kpi--teal">
        <div class="kpi__icon"><i class="bi bi-compass" style="font-size:20px"></i></div>
        <div class="kpi__label">Rutas Activas</div>
        <div class="kpi__value"><?php echo $data['totalRutas']; ?></div>
        <a href="<?php echo URL_ROOT; ?>/rutas/index" style="font-size:12px;font-weight:600;color:var(--teal-600);display:inline-flex;align-items:center;gap:4px">Ver Turismo →</a>
    </div>
</div>

<!-- Gráficas Fila 1 -->
<div style="display:grid;grid-template-columns:7fr 5fr;gap:var(--sp-4);margin-bottom:var(--sp-6)" class="anim-slide-up">
    <div class="sig-card">
        <div class="sig-card__head">
            <div class="sig-card__title"><i class="bi bi-bar-chart-fill" style="color:var(--brand-600)"></i> Empleados por Departamento</div>
        </div>
        <div class="sig-card__body"><div id="chartEmpleados"></div></div>
    </div>
    <div class="sig-card">
        <div class="sig-card__head">
            <div class="sig-card__title"><i class="bi bi-pie-chart-fill" style="color:var(--success-600)"></i> Estado del Inventario</div>
        </div>
        <div class="sig-card__body"><div id="chartInventario"></div></div>
    </div>
</div>

<!-- Gráficas Fila 2 -->
<div style="display:grid;grid-template-columns:7fr 5fr;gap:var(--sp-4);margin-bottom:var(--sp-6)" class="anim-slide-up">
    <div class="sig-card">
        <div class="sig-card__head">
            <div class="sig-card__title"><i class="bi bi-graph-up" style="color:var(--teal-600)"></i> Asistencia — Últimos 7 Días</div>
        </div>
        <div class="sig-card__body"><div id="chartAsistencia"></div></div>
    </div>
    <div class="sig-card">
        <div class="sig-card__head">
            <div class="sig-card__title"><i class="bi bi-mortarboard-fill" style="color:var(--accent-600)"></i> Talleres por Estado</div>
        </div>
        <div class="sig-card__body"><div id="chartTalleres"></div></div>
    </div>
</div>

<!-- Accesos rápidos -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-4)" class="anim-slide-up">
    <div class="sig-card">
        <div class="sig-card__head" style="background:var(--slate-900);color:white;border-radius:var(--r-lg) var(--r-lg) 0 0">
            <div class="sig-card__title" style="color:white"><i class="bi bi-clock-history"></i> Accesos Rápidos — RRHH</div>
        </div>
        <div class="sig-card__body--flush">
            <a href="<?php echo URL_ROOT; ?>/asistencias/index" style="display:block;padding:12px 20px;border-bottom:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Control de Asistencia</a>
            <a href="<?php echo URL_ROOT; ?>/empleados/index" style="display:block;padding:12px 20px;border-bottom:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Gestión de Personal</a>
            <a href="<?php echo URL_ROOT; ?>/cargos/index" style="display:block;padding:12px 20px;border-bottom:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Puestos y Cargos</a>
            <a href="<?php echo URL_ROOT; ?>/departamentos/index" style="display:block;padding:12px 20px;color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Estructura Organizativa</a>
        </div>
    </div>
    <div class="sig-card">
        <div class="sig-card__head" style="background:var(--slate-900);color:white;border-radius:var(--r-lg) var(--r-lg) 0 0">
            <div class="sig-card__title" style="color:white"><i class="bi bi-compass"></i> Accesos Rápidos — Turismo</div>
        </div>
        <div class="sig-card__body--flush">
            <a href="<?php echo URL_ROOT; ?>/talleres/index" style="display:block;padding:12px 20px;border-bottom:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Talleres Comunitarios</a>
            <a href="<?php echo URL_ROOT; ?>/rutas/index" style="display:block;padding:12px 20px;border-bottom:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Rutas Turísticas</a>
            <a href="<?php echo URL_ROOT; ?>/inventario/index" style="display:block;padding:12px 20px;border-bottom:1px solid var(--border-subtle);color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Inventario Institucional</a>
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="display:block;padding:12px 20px;color:var(--text-primary);text-decoration:none;transition:background var(--t-fast)" onmouseover="this.style.background='var(--bg-muted)'" onmouseout="this.style.background=''">Centro de Reportes</a>
        </div>
    </div>
</div>

<!-- ============ ApexCharts Scripts ============ -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    var isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    var gridColor = isDark ? '#1f2740' : '#f1f5f9';
    var tooltipTheme = isDark ? 'dark' : 'light';

    <?php
        $labelsEmp = []; $valuesEmp = [];
        foreach ($data['chartEmpleados'] as $item) { $labelsEmp[] = $item->label; $valuesEmp[] = (int)$item->value; }
    ?>
    new ApexCharts(document.querySelector("#chartEmpleados"), {
        chart: { type: 'bar', height: 280, toolbar: { show: false }, background: 'transparent' },
        series: [{ name: 'Empleados', data: <?php echo json_encode($valuesEmp); ?> }],
        xaxis: { categories: <?php echo json_encode($labelsEmp); ?> },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
        colors: ['#3461f6'],
        dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700 } },
        grid: { borderColor: gridColor },
        tooltip: { theme: tooltipTheme },
        theme: { mode: isDark ? 'dark' : 'light' }
    }).render();

    <?php
        $labelsInv = []; $valuesInv = [];
        foreach ($data['chartInventario'] as $item) { $labelsInv[] = $item->label; $valuesInv[] = (int)$item->value; }
    ?>
    new ApexCharts(document.querySelector("#chartInventario"), {
        chart: { type: 'donut', height: 300, background: 'transparent' },
        series: <?php echo json_encode($valuesInv); ?>,
        labels: <?php echo json_encode($labelsInv); ?>,
        colors: ['#10b981','#3b82f6','#f59e0b','#ef4444','#1e293b'],
        legend: { position: 'bottom', fontSize: '13px' },
        plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '16px', fontWeight: 700 } } } } },
        dataLabels: { enabled: true, dropShadow: { enabled: false } },
        tooltip: { theme: tooltipTheme },
        theme: { mode: isDark ? 'dark' : 'light' }
    }).render();

    <?php
        $labelsAsis = []; $valuesAsis = [];
        foreach ($data['chartAsistencia'] as $item) { $labelsAsis[] = $item->label; $valuesAsis[] = (int)$item->value; }
    ?>
    new ApexCharts(document.querySelector("#chartAsistencia"), {
        chart: { type: 'area', height: 280, toolbar: { show: false }, background: 'transparent' },
        series: [{ name: 'Registros', data: <?php echo json_encode($valuesAsis); ?> }],
        xaxis: { categories: <?php echo json_encode($labelsAsis); ?>, labels: { style: { fontSize: '11px' } } },
        colors: ['#14b8a6'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: true, style: { fontSize: '12px' } },
        grid: { borderColor: gridColor },
        tooltip: { theme: tooltipTheme },
        theme: { mode: isDark ? 'dark' : 'light' }
    }).render();

    <?php
        $labelsTall = []; $valuesTall = [];
        foreach ($data['chartTalleres'] as $item) { $labelsTall[] = $item->label; $valuesTall[] = (int)$item->value; }
    ?>
    new ApexCharts(document.querySelector("#chartTalleres"), {
        chart: { type: 'polarArea', height: 300, background: 'transparent' },
        series: <?php echo json_encode($valuesTall); ?>,
        labels: <?php echo json_encode($labelsTall); ?>,
        colors: ['#3b82f6','#10b981','#1e293b','#ef4444'],
        legend: { position: 'bottom', fontSize: '13px' },
        fill: { opacity: 0.85 },
        stroke: { width: 1, colors: ['#fff'] },
        tooltip: { theme: tooltipTheme },
        theme: { mode: isDark ? 'dark' : 'light' }
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
