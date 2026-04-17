<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-mortarboard"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarTalleresCsv?estado=<?php echo $data['estado_filtro']; ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarTalleresPdf?estado=<?php echo $data['estado_filtro']; ?>" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn btn-outline-secondary">← Volver</a>
    </div>
</div>

<!-- Filtro por estado -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/talleres" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Filtrar por Estado</label>
                <select name="estado" class="form-select">
                    <option value="">-- Todos --</option>
                    <option value="Programado" <?php echo $data['estado_filtro'] == 'Programado' ? 'selected' : ''; ?>>Programado</option>
                    <option value="En Curso" <?php echo $data['estado_filtro'] == 'En Curso' ? 'selected' : ''; ?>>En Curso</option>
                    <option value="Finalizado" <?php echo $data['estado_filtro'] == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                    <option value="Cancelado" <?php echo $data['estado_filtro'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-warning w-100">Aplicar Filtro</button>
            </div>
        </form>
    </div>
</div>

<!-- Indicadores -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-primary"><?php echo $data['stats']->total_talleres ?? 0; ?></div>
            <small class="text-muted">Total Talleres</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-success"><?php echo $data['stats']->finalizados ?? 0; ?></div>
            <small class="text-muted">Finalizados</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-warning bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-warning"><?php echo $data['stats']->en_curso ?? 0; ?></div>
            <small class="text-muted">En Curso</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 bg-info bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-info"><?php echo $data['stats']->total_participantes ?? 0; ?></div>
            <small class="text-muted">Total Participantes</small>
        </div>
    </div>
</div>

<!-- Gráfica de distribución -->
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-pie-chart-fill text-warning"></i> Distribución por Estado
            </div>
            <div class="card-body">
                <div id="chartTalleresEstado"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white fw-bold border-0 pt-3 ps-3">
                <i class="bi bi-bar-chart-fill text-primary"></i> Inscritos vs Cupo por Taller
            </div>
            <div class="card-body">
                <div id="chartTalleresCupo"></div>
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
                        <th>Taller</th>
                        <th>Facilitador</th>
                        <th>Sede</th>
                        <th>Fecha Inicio</th>
                        <th>Estado</th>
                        <th class="text-center">Inscritos / Cupo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['talleres'])): ?>
                        <tr><td colspan="6" class="text-center py-4 text-muted">Sin talleres registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['talleres'] as $t): ?>
                            <tr>
                                <td class="fw-bold"><?php echo $t->nombre; ?></td>
                                <td><?php echo $t->facilitador_nombre . ' ' . $t->facilitador_apellido; ?></td>
                                <td><?php echo $t->sede ?: '<span class="text-muted">Sin sede</span>'; ?></td>
                                <td><?php echo $t->fecha_inicio; ?></td>
                                <td>
                                    <?php 
                                        $color = 'bg-secondary';
                                        if ($t->estado == 'En Curso') $color = 'bg-success';
                                        if ($t->estado == 'Programado') $color = 'bg-primary';
                                        if ($t->estado == 'Finalizado') $color = 'bg-dark';
                                        if ($t->estado == 'Cancelado') $color = 'bg-danger';
                                    ?>
                                    <span class="badge <?php echo $color; ?>"><?php echo $t->estado; ?></span>
                                </td>
                                <td class="text-center fw-bold">
                                    <?php 
                                        $porcentaje = $t->cupo_maximo > 0 ? round(($t->total_inscritos / $t->cupo_maximo) * 100) : 0;
                                        $colorBarra = $porcentaje > 80 ? 'bg-danger' : ($porcentaje > 50 ? 'bg-warning' : 'bg-success');
                                    ?>
                                    <?php echo $t->total_inscritos; ?> / <?php echo $t->cupo_maximo; ?>
                                    <div class="progress mt-1" style="height: 5px;">
                                        <div class="progress-bar <?php echo $colorBarra; ?>" style="width: <?php echo $porcentaje; ?>%"></div>
                                    </div>
                                </td>
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
    // Donut: Talleres por Estado
    var stats = {
        total: <?php echo $data['stats']->total_talleres ?? 0; ?>,
        finalizados: <?php echo $data['stats']->finalizados ?? 0; ?>,
        enCurso: <?php echo $data['stats']->en_curso ?? 0; ?>,
        programados: <?php echo $data['stats']->programados ?? 0; ?>
    };
    var cancelados = stats.total - stats.finalizados - stats.enCurso - stats.programados;
    if (cancelados < 0) cancelados = 0;

    new ApexCharts(document.querySelector("#chartTalleresEstado"), {
        chart: { type: 'donut', height: 280 },
        series: [stats.programados, stats.enCurso, stats.finalizados, cancelados],
        labels: ['Programado', 'En Curso', 'Finalizado', 'Cancelado'],
        colors: ['#3b82f6','#10b981','#1e293b','#ef4444'],
        legend: { position: 'bottom', fontSize: '12px' },
        plotOptions: { pie: { donut: { size: '55%', labels: { show: true, total: { show: true, label: 'Total', fontSize: '15px', fontWeight: 700 } } } } },
        dataLabels: { enabled: true, dropShadow: { enabled: false } },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin talleres', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();

    // Barras: Inscritos vs Cupo
    <?php
        $nombres = []; $inscritos = []; $cupos = [];
        if (!empty($data['talleres'])) {
            $count = 0;
            foreach ($data['talleres'] as $t) {
                if ($count >= 8) break;
                $nombres[] = mb_substr($t->nombre, 0, 20);
                $inscritos[] = (int)$t->total_inscritos;
                $cupos[] = (int)$t->cupo_maximo;
                $count++;
            }
        }
    ?>
    new ApexCharts(document.querySelector("#chartTalleresCupo"), {
        chart: { type: 'bar', height: 280, toolbar: { show: false } },
        series: [
            { name: 'Inscritos', data: <?php echo json_encode($inscritos); ?> },
            { name: 'Cupo Máximo', data: <?php echo json_encode($cupos); ?> }
        ],
        xaxis: { categories: <?php echo json_encode($nombres); ?>, labels: { style: { fontSize: '10px' }, rotate: -30 } },
        colors: ['#3b82f6','#e2e8f0'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%' } },
        dataLabels: { enabled: false },
        legend: { position: 'top', fontSize: '12px' },
        grid: { borderColor: '#f1f5f9' },
        tooltip: { theme: 'dark' },
        noData: { text: 'Sin talleres para graficar', style: { fontSize: '14px', color: '#94a3b8' } }
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
