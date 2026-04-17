<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-graph-up-arrow"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Métricas globales calculadas del sistema</p>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn btn-outline-secondary">← Volver a Reportes</a>
    </div>
</div>

<!-- Fila 1: Empleados por Depto + Inventario por Categoría -->
<div class="row g-4 mb-4">
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-people-fill text-primary"></i> Distribución de Empleados por Departamento
            </div>
            <div class="card-body">
                <div id="chartEmpDepto"></div>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-box-seam-fill text-success"></i> Inventario por Categoría
            </div>
            <div class="card-body">
                <div id="chartInvCat"></div>
            </div>
        </div>
    </div>
</div>

<!-- Fila 2: Inventario por Condición + Talleres por Mes -->
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-clipboard2-check-fill text-warning"></i> Estado Físico del Inventario
            </div>
            <div class="card-body">
                <div id="chartInvCond"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-calendar-range-fill text-info"></i> Tendencia de Talleres (Últimos 6 Meses)
            </div>
            <div class="card-body">
                <div id="chartTallMes"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tablas de respaldo (datos numéricos) -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">Tabla: Empleados por Departamento</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th class="ps-3">Departamento</th><th class="text-center">Total</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['empPorDepto'] as $e): ?>
                            <tr><td class="ps-3 fw-bold"><?php echo $e->departamento; ?></td><td class="text-center"><?php echo $e->total; ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['empPorDepto'])): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">Tabla: Inventario por Categoría</div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0 small">
                    <thead class="table-light"><tr><th class="ps-3">Categoría</th><th class="text-center">Total Bienes</th></tr></thead>
                    <tbody>
                        <?php foreach ($data['invPorCat'] as $i): ?>
                            <tr><td class="ps-3 fw-bold"><?php echo $i->categoria; ?></td><td class="text-center"><?php echo $i->total; ?></td></tr>
                        <?php endforeach; ?>
                        <?php if (empty($data['invPorCat'])): ?>
                            <tr><td colspan="2" class="text-center text-muted py-3">Sin datos</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ============ ApexCharts Scripts ============ -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. EMPLEADOS POR DEPARTAMENTO (Barras verticales con gradiente) ---
    <?php
        $lblD = []; $valD = [];
        foreach ($data['empPorDepto'] as $e) { $lblD[] = $e->departamento; $valD[] = (int)$e->total; }
    ?>
    new ApexCharts(document.querySelector("#chartEmpDepto"), {
        chart: { type: 'bar', height: 320, toolbar: { show: false } },
        series: [{ name: 'Empleados', data: <?php echo json_encode($valD); ?> }],
        xaxis: { categories: <?php echo json_encode($lblD); ?>, labels: { style: { fontSize: '11px' } } },
        colors: ['#1a73e8'],
        plotOptions: { bar: { borderRadius: 6, columnWidth: '55%', distributed: true } },
        fill: { type: 'gradient', gradient: { shade: 'dark', type: 'vertical', gradientToColors: ['#6dd5fa'], stops: [0, 100] } },
        dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 700 } },
        legend: { show: false },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin departamentos con empleados', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

    // --- 2. INVENTARIO POR CATEGORÍA (Donut) ---
    <?php
        $lblC = []; $valC = [];
        foreach ($data['invPorCat'] as $i) { $lblC[] = $i->categoria; $valC[] = (int)$i->total; }
    ?>
    new ApexCharts(document.querySelector("#chartInvCat"), {
        chart: { type: 'donut', height: 340 },
        series: <?php echo json_encode($valC); ?>,
        labels: <?php echo json_encode($lblC); ?>,
        colors: ['#10b981','#3b82f6','#f59e0b','#8b5cf6','#ec4899','#06b6d4','#f97316'],
        legend: { position: 'bottom', fontSize: '12px' },
        plotOptions: { pie: { donut: { size: '50%', labels: { show: true, total: { show: true, label: 'Total Bienes', fontSize: '14px', fontWeight: 700 } } } } },
        dataLabels: { enabled: true, dropShadow: { enabled: false } },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin inventario categorizado', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

    // --- 3. INVENTARIO POR CONDICIÓN (Radial Bar) ---
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
        chart: { type: 'radialBar', height: 340 },
        series: <?php echo json_encode($valCond); ?>,
        labels: <?php echo json_encode($lblCond); ?>,
        colors: ['#10b981','#3b82f6','#f59e0b','#ef4444','#1e293b'],
        plotOptions: {
            radialBar: {
                dataLabels: {
                    name: { fontSize: '13px' },
                    value: { fontSize: '16px', fontWeight: 700, formatter: function(val) { return val + '%'; } },
                    total: { show: true, label: 'Dist. Total', fontSize: '13px', fontWeight: 600 }
                },
                hollow: { size: '30%' },
                track: { background: '#f1f5f9' }
            }
        },
        legend: { show: true, position: 'bottom', fontSize: '12px' },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin datos de condición', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

    // --- 4. TALLERES POR MES (Línea con marcadores) ---
    <?php
        $lblM = []; $valM = [];
        foreach ($data['talleresPorMes'] as $t) { $lblM[] = $t->mes; $valM[] = (int)$t->total; }
    ?>
    new ApexCharts(document.querySelector("#chartTallMes"), {
        chart: { type: 'line', height: 320, toolbar: { show: false } },
        series: [{ name: 'Talleres', data: <?php echo json_encode($valM); ?> }],
        xaxis: { categories: <?php echo json_encode($lblM); ?>, labels: { style: { fontSize: '12px' } } },
        colors: ['#8b5cf6'],
        stroke: { curve: 'smooth', width: 3 },
        markers: { size: 6, colors: ['#8b5cf6'], strokeColors: '#fff', strokeWidth: 2, hover: { size: 9 } },
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.3, opacityTo: 0.05 } },
        dataLabels: { enabled: true, style: { fontSize: '13px', fontWeight: 700 } },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin talleres en los últimos 6 meses', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
