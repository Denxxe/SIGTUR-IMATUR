<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="fw-bold"><i class="bi bi-speedometer2"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Resumen operativo de SIGTUR-IMATUR</p>
    </div>
</div>

<!-- Indicadores KPI -->
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-primary fw-bold"><?php echo $data['totalEmpleados']; ?></div>
                <p class="mb-0 text-muted">Empleados Activos</p>
            </div>
            <div class="card-footer bg-primary bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/empleados/index" class="text-primary text-decoration-none small fw-bold">Ver Personal →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-success fw-bold"><?php echo $data['totalInventario']; ?></div>
                <p class="mb-0 text-muted">Bienes en Inventario</p>
            </div>
            <div class="card-footer bg-success bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/inventario/index" class="text-success text-decoration-none small fw-bold">Ver Inventario →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-warning fw-bold"><?php echo $data['totalTalleres']; ?></div>
                <p class="mb-0 text-muted">Talleres Vigentes</p>
            </div>
            <div class="card-footer bg-warning bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/talleres/index" class="text-warning text-decoration-none small fw-bold">Ver Formación →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-info fw-bold"><?php echo $data['totalRutas']; ?></div>
                <p class="mb-0 text-muted">Rutas Activas</p>
            </div>
            <div class="card-footer bg-info bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/rutas/index" class="text-info text-decoration-none small fw-bold">Ver Turismo →</a>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas Fila 1 -->
<div class="row g-4 mb-4">
    <!-- Empleados por Departamento (Barras) -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-bar-chart-fill text-primary"></i> Empleados por Departamento
            </div>
            <div class="card-body">
                <div id="chartEmpleados"></div>
            </div>
        </div>
    </div>

    <!-- Inventario por Condición (Donut) -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-pie-chart-fill text-success"></i> Estado del Inventario
            </div>
            <div class="card-body">
                <div id="chartInventario"></div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas Fila 2 -->
<div class="row g-4 mb-4">
    <!-- Asistencia Últimos 7 Días (Área) -->
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-graph-up text-info"></i> Asistencia — Últimos 7 Días
            </div>
            <div class="card-body">
                <div id="chartAsistencia"></div>
            </div>
        </div>
    </div>

    <!-- Talleres por Estado (Radial) -->
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-mortarboard-fill text-warning"></i> Talleres por Estado
            </div>
            <div class="card-body">
                <div id="chartTalleres"></div>
            </div>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-clock-history"></i> Accesos Rápidos — RRHH
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo URL_ROOT; ?>/asistencias/index" class="list-group-item list-group-item-action">Control de Asistencia</a>
                <a href="<?php echo URL_ROOT; ?>/empleados/index" class="list-group-item list-group-item-action">Gestión de Personal</a>
                <a href="<?php echo URL_ROOT; ?>/cargos/index" class="list-group-item list-group-item-action">Puestos y Cargos</a>
                <a href="<?php echo URL_ROOT; ?>/departamentos/index" class="list-group-item list-group-item-action">Estructura Organizativa</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-compass"></i> Accesos Rápidos — Turismo
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo URL_ROOT; ?>/talleres/index" class="list-group-item list-group-item-action">Talleres Comunitarios</a>
                <a href="<?php echo URL_ROOT; ?>/rutas/index" class="list-group-item list-group-item-action">Rutas Turísticas</a>
                <a href="<?php echo URL_ROOT; ?>/inventario/index" class="list-group-item list-group-item-action">Inventario Institucional</a>
                <a href="<?php echo URL_ROOT; ?>/reportes/index" class="list-group-item list-group-item-action">Centro de Reportes</a>
            </div>
        </div>
    </div>
</div>

<!-- ============ ApexCharts Scripts ============ -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // --- 1. EMPLEADOS POR DEPARTAMENTO (Barras Horizontales) ---
    <?php
        $labelsEmp = []; $valuesEmp = [];
        foreach ($data['chartEmpleados'] as $item) {
            $labelsEmp[] = $item->label;
            $valuesEmp[] = (int)$item->value;
        }
    ?>
    new ApexCharts(document.querySelector("#chartEmpleados"), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [{ name: 'Empleados', data: <?php echo json_encode($valuesEmp); ?> }],
        xaxis: { categories: <?php echo json_encode($labelsEmp); ?> },
        plotOptions: { bar: { horizontal: true, borderRadius: 4, barHeight: '60%' } },
        colors: ['#1a73e8'],
        dataLabels: { enabled: true, style: { fontSize: '12px', fontWeight: 700 } },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { theme: 'dark' }
    }).render();

    // --- 2. INVENTARIO POR CONDICIÓN (Donut) ---
    <?php
        $labelsInv = []; $valuesInv = [];
        foreach ($data['chartInventario'] as $item) {
            $labelsInv[] = $item->label;
            $valuesInv[] = (int)$item->value;
        }
    ?>
    new ApexCharts(document.querySelector("#chartInventario"), {
        chart: { type: 'donut', height: 300 },
        series: <?php echo json_encode($valuesInv); ?>,
        labels: <?php echo json_encode($labelsInv); ?>,
        colors: ['#10b981','#3b82f6','#f59e0b','#ef4444','#1e293b'],
        legend: { position: 'bottom', fontSize: '13px' },
        plotOptions: {
            pie: {
                donut: {
                    size: '55%',
                    labels: {
                        show: true,
                        total: { show: true, label: 'Total', fontSize: '16px', fontWeight: 700 }
                    }
                }
            }
        },
        dataLabels: { enabled: true, dropShadow: { enabled: false } },
        tooltip: { theme: 'dark' }
    }).render();

    // --- 3. ASISTENCIA ÚLTIMOS 7 DÍAS (Área) ---
    <?php
        $labelsAsis = []; $valuesAsis = [];
        foreach ($data['chartAsistencia'] as $item) {
            $labelsAsis[] = $item->label;
            $valuesAsis[] = (int)$item->value;
        }
    ?>
    new ApexCharts(document.querySelector("#chartAsistencia"), {
        chart: { type: 'area', height: 280, toolbar: { show: false }, sparkline: { enabled: false } },
        series: [{ name: 'Registros', data: <?php echo json_encode($valuesAsis); ?> }],
        xaxis: { categories: <?php echo json_encode($labelsAsis); ?>, labels: { style: { fontSize: '11px' } } },
        colors: ['#06b6d4'],
        fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.4, opacityTo: 0.1 } },
        stroke: { curve: 'smooth', width: 3 },
        dataLabels: { enabled: true, style: { fontSize: '12px' } },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin registros de asistencia recientes', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

    // --- 4. TALLERES POR ESTADO (Polar Area) ---
    <?php
        $labelsTall = []; $valuesTall = [];
        foreach ($data['chartTalleres'] as $item) {
            $labelsTall[] = $item->label;
            $valuesTall[] = (int)$item->value;
        }
    ?>
    new ApexCharts(document.querySelector("#chartTalleres"), {
        chart: { type: 'polarArea', height: 300 },
        series: <?php echo json_encode($valuesTall); ?>,
        labels: <?php echo json_encode($labelsTall); ?>,
        colors: ['#3b82f6','#10b981','#1e293b','#ef4444'],
        legend: { position: 'bottom', fontSize: '13px' },
        fill: { opacity: 0.85 },
        stroke: { width: 1, colors: ['#fff'] },
        plotOptions: { polarArea: { rings: { strokeWidth: 1, strokeColor: '#e2e8f0' } } },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin talleres registrados', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
