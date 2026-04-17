<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-compass"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarRutasCsv" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarRutasPdf" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn btn-outline-secondary">← Volver</a>
    </div>
</div>

<!-- Indicadores -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-primary"><?php echo $data['stats']->total_rutas ?? 0; ?></div>
            <small class="text-muted">Total Rutas</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-success"><?php echo $data['stats']->activas ?? 0; ?></div>
            <small class="text-muted">Activas</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-secondary bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-secondary"><?php echo $data['stats']->inactivas ?? 0; ?></div>
            <small class="text-muted">Inactivas</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-warning"><?php echo $data['stats']->mantenimiento ?? 0; ?></div>
            <small class="text-muted">En Mantenimiento</small>
        </div>
    </div>
</div>

<!-- Gráficas -->
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-pie-chart-fill text-info"></i> Distribución por Estado
            </div>
            <div class="card-body">
                <div id="chartRutasEstado"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-bar-chart-fill text-primary"></i> Métricas por Ruta (Puntos / Actividades / Equipos)
            </div>
            <div class="card-body">
                <div id="chartRutasMetricas"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Ruta</th>
                        <th>Dificultad</th>
                        <th>Duración</th>
                        <th>Estado</th>
                        <th class="text-center">Puntos</th>
                        <th class="text-center">Actividades</th>
                        <th class="text-center">Equipos Asignados</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['rutas'])): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Sin rutas registradas.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['rutas'] as $r): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $r->nombre; ?></td>
                                <td>
                                    <?php 
                                        $cd = 'bg-success';
                                        if ($r->nivel_dificultad == 'Moderado') $cd = 'bg-warning';
                                        if ($r->nivel_dificultad == 'Difícil') $cd = 'bg-danger';
                                        if ($r->nivel_dificultad == 'Extremo') $cd = 'bg-dark';
                                    ?>
                                    <span class="badge <?php echo $cd; ?>"><?php echo $r->nivel_dificultad; ?></span>
                                </td>
                                <td><?php echo $r->duracion_estimada ?: '-'; ?></td>
                                <td>
                                    <?php 
                                        $ce = 'bg-success';
                                        if ($r->estado == 'Inactiva') $ce = 'bg-secondary';
                                        if ($r->estado == 'En Mantenimiento') $ce = 'bg-warning';
                                    ?>
                                    <span class="badge <?php echo $ce; ?>"><?php echo $r->estado; ?></span>
                                </td>
                                <td class="text-center fw-bold"><?php echo $r->total_puntos; ?></td>
                                <td class="text-center fw-bold"><?php echo $r->total_actividades; ?></td>
                                <td class="text-center fw-bold"><?php echo $r->total_equipos; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- ApexCharts Scripts -->
<script>
document.addEventListener('DOMContentLoaded', function() {

    // Donut: Rutas por Estado
    new ApexCharts(document.querySelector("#chartRutasEstado"), {
        chart: { type: 'donut', height: 300 },
        series: [
            <?php echo $data['stats']->activas ?? 0; ?>,
            <?php echo $data['stats']->inactivas ?? 0; ?>,
            <?php echo $data['stats']->mantenimiento ?? 0; ?>
        ],
        labels: ['Activa', 'Inactiva', 'En Mantenimiento'],
        colors: ['#10b981','#64748b','#f59e0b'],
        legend: { position: 'bottom', fontSize: '13px' },
        plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '15px', fontWeight: 700 } } } } },
        dataLabels: { enabled: true, dropShadow: { enabled: false } },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin rutas', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

    // Barras agrupadas: Métricas por Ruta
    <?php
        $nR = []; $puntosR = []; $actR = []; $eqR = [];
        if (!empty($data['rutas'])) {
            $cnt = 0;
            foreach ($data['rutas'] as $r) {
                if ($cnt >= 8) break;
                $nR[] = mb_substr($r->nombre, 0, 18);
                $puntosR[] = (int)$r->total_puntos;
                $actR[] = (int)$r->total_actividades;
                $eqR[] = (int)$r->total_equipos;
                $cnt++;
            }
        }
    ?>
    new ApexCharts(document.querySelector("#chartRutasMetricas"), {
        chart: { type: 'bar', height: 300, toolbar: { show: false } },
        series: [
            { name: 'Puntos', data: <?php echo json_encode($puntosR); ?> },
            { name: 'Actividades', data: <?php echo json_encode($actR); ?> },
            { name: 'Equipos', data: <?php echo json_encode($eqR); ?> }
        ],
        xaxis: { categories: <?php echo json_encode($nR); ?>, labels: { style: { fontSize: '10px' }, rotate: -30 } },
        colors: ['#3b82f6','#f59e0b','#10b981'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '65%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', fontSize: '12px' },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin rutas para graficar', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
