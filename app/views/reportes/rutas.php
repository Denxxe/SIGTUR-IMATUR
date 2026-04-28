<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Turismo
        </div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Métricas de infraestructura turística, puntos de interés y equipamiento de las rutas municipales.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarRutasCsv" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarRutasPdf" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Indicadores Rápidos -->
<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--teal-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Total Rutas</span>
                <span style="font-size:28px; font-weight:800; color:var(--text-primary);"><?php echo $data['stats']->total_rutas ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--success-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Activas</span>
                <span style="font-size:28px; font-weight:800; color:var(--success-600);"><?php echo $data['stats']->activas ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--slate-400);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Inactivas</span>
                <span style="font-size:28px; font-weight:800; color:var(--text-secondary);"><?php echo $data['stats']->inactivas ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--warning-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">En Mantenimiento</span>
                <span style="font-size:28px; font-weight:800; color:var(--warning-600);"><?php echo $data['stats']->mantenimiento ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas -->
<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pie-chart" style="color:var(--teal-500);"></i> Estado de Operatividad</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartRutasEstado"></div>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-bar-chart" style="color:var(--brand-500);"></i> Métricas por Ruta (Infraestructura y Uso)</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartRutasMetricas"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Nombre de la Ruta</th>
                <th>Dificultad</th>
                <th>Estado</th>
                <th style="text-align:center;">Paradas</th>
                <th style="text-align:center;">Actividades</th>
                <th style="text-align:center;">Equipamiento</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['rutas'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No hay rutas registradas para generar el reporte.</td></tr>
            <?php else: ?>
                <?php foreach ($data['rutas'] as $r): ?>
                    <tr>
                        <td class="cell-strong"><?php echo $r->nombre; ?></td>
                        <td>
                            <?php 
                                $diffBadge = 'sig-badge--neutral';
                                if ($r->nivel_dificultad == 'Fácil') $diffBadge = 'sig-badge--success';
                                elseif ($r->nivel_dificultad == 'Moderado') $diffBadge = 'sig-badge--info';
                                elseif ($r->nivel_dificultad == 'Difícil') $diffBadge = 'sig-badge--warning';
                                elseif ($r->nivel_dificultad == 'Extremo') $diffBadge = 'sig-badge--danger';
                            ?>
                            <span class="sig-badge sig-badge--sm <?php echo $diffBadge; ?>"><?php echo $r->nivel_dificultad; ?></span>
                        </td>
                        <td>
                            <?php 
                                $statusBadge = 'sig-badge--neutral';
                                if ($r->estado == 'Activa') $statusBadge = 'sig-badge--success';
                                elseif ($r->estado == 'En Mantenimiento') $statusBadge = 'sig-badge--warning';
                            ?>
                            <span class="sig-badge sig-badge--sm <?php echo $statusBadge; ?>"><?php echo $r->estado; ?></span>
                        </td>
                        <td style="text-align:center; font-weight:700; color:var(--text-primary);"><?php echo $r->total_puntos; ?></td>
                        <td style="text-align:center; font-weight:700; color:var(--text-primary);"><?php echo $r->total_actividades; ?></td>
                        <td style="text-align:center; font-weight:700; color:var(--text-primary);"><?php echo $r->total_equipos; ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ApexCharts Config -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
    const textPrimary = getComputedStyle(document.body).getPropertyValue('--text-primary').trim();
    const borderSubtle = getComputedStyle(document.body).getPropertyValue('--border-subtle').trim();

    // Donut: Rutas por Estado
    new ApexCharts(document.querySelector("#chartRutasEstado"), {
        chart: { type: 'donut', height: 320, background: 'transparent' },
        series: [
            <?php echo $data['stats']->activas ?? 0; ?>,
            <?php echo $data['stats']->inactivas ?? 0; ?>,
            <?php echo $data['stats']->mantenimiento ?? 0; ?>
        ],
        labels: ['Activa', 'Inactiva', 'En Mantenimiento'],
        colors: ['#10B981', '#64748B', '#F59E0B'],
        theme: { mode: isDark ? 'dark' : 'light' },
        legend: { position: 'bottom', labels: { colors: textPrimary } },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'TOTAL', color: textPrimary, fontSize: '14px', fontWeight: 800 } } } } },
        dataLabels: { enabled: false }
    }).render();

    // Barras agrupadas: Métricas por Ruta
    <?php
        $nR = []; $puntosR = []; $actR = []; $eqR = [];
        if (!empty($data['rutas'])) {
            $cnt = 0;
            foreach ($data['rutas'] as $r) {
                if ($cnt >= 8) break;
                $nR[] = mb_substr($r->nombre, 0, 15) . '...';
                $puntosR[] = (int)$r->total_puntos;
                $actR[] = (int)$r->total_actividades;
                $eqR[] = (int)$r->total_equipos;
                $cnt++;
            }
        }
    ?>
    new ApexCharts(document.querySelector("#chartRutasMetricas"), {
        chart: { type: 'bar', height: 320, background: 'transparent', toolbar: { show: false } },
        series: [
            { name: 'Puntos de Interés', data: <?php echo json_encode($puntosR); ?> },
            { name: 'Actividades', data: <?php echo json_encode($actR); ?> },
            { name: 'Equipamiento', data: <?php echo json_encode($eqR); ?> }
        ],
        xaxis: { categories: <?php echo json_encode($nR); ?>, labels: { style: { colors: textPrimary, fontSize: '10px' } }, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: textPrimary } } },
        theme: { mode: isDark ? 'dark' : 'light' },
        colors: ['#14B8A6', '#F59E0B', '#3B82F6'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '60%', dataLabels: { position: 'top' } } },
        grid: { borderColor: borderSubtle, strokeDashArray: 4 },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: textPrimary } }
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
