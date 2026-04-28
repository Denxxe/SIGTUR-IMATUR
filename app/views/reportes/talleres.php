<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Formación
        </div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Estadísticas de impacto, demografía de asistentes y ocupación de talleres de capacitación.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarTalleresCsv?estado=<?php echo $data['estado_filtro']; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarTalleresPdf?estado=<?php echo $data['estado_filtro']; ?>" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/talleres" class="row g-4 align-items-end">
            <div class="col-md-4">
                <div class="sig-field">
                    <label class="sig-field__label">Filtrar por Estado</label>
                    <select name="estado" class="sig-select">
                        <option value="">-- Todos los estados --</option>
                        <option value="Programado" <?php echo $data['estado_filtro'] == 'Programado' ? 'selected' : ''; ?>>Programado</option>
                        <option value="En Curso" <?php echo $data['estado_filtro'] == 'En Curso' ? 'selected' : ''; ?>>En Curso</option>
                        <option value="Finalizado" <?php echo $data['estado_filtro'] == 'Finalizado' ? 'selected' : ''; ?>>Finalizado</option>
                        <option value="Cancelado" <?php echo $data['estado_filtro'] == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn-sig btn-sig--primary" style="width:100%; height:42px; background:var(--accent-600);">
                    <i class="bi bi-filter"></i> Aplicar Filtro
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Indicadores Rápidos -->
<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--brand-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Total Talleres</span>
                <span style="font-size:28px; font-weight:800; color:var(--text-primary);"><?php echo $data['stats']->total_talleres ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--success-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Finalizados</span>
                <span style="font-size:28px; font-weight:800; color:var(--success-600);"><?php echo $data['stats']->finalizados ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--accent-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">En Curso</span>
                <span style="font-size:28px; font-weight:800; color:var(--accent-600);"><?php echo $data['stats']->en_curso ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--brand-400);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Total Asistentes</span>
                <span style="font-size:28px; font-weight:800; color:var(--brand-600);"><?php echo $data['stats']->total_participantes ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Gráficas -->
<div class="row g-4 mb-8 anim-slide-up">
    <div class="col-md-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pie-chart" style="color:var(--accent-500);"></i> Distribución por Estado</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartTalleresEstado"></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-bar-chart" style="color:var(--brand-500);"></i> Inscritos vs Cupo (Top 8)</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <div id="chartTalleresCupo"></div>
            </div>
        </div>
    </div>
</div>

<!-- Tabla de Resultados -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Taller / Sede</th>
                <th>Facilitador</th>
                <th>Fecha</th>
                <th style="text-align:center;">Resumen Demográfico</th>
                <th style="text-align:center;">Ocupación</th>
                <th>Estado</th>
                <th class="col-actions">Dossier</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['talleres'])): ?>
                <tr><td colspan="7" class="sig-table-empty">No se encontraron talleres con el filtro aplicado.</td></tr>
            <?php else: ?>
                <?php foreach ($data['talleres'] as $t): ?>
                    <tr>
                        <td>
                            <div style="display:flex; flex-direction:column; gap:2px;">
                                <span class="cell-strong"><?php echo $t->nombre; ?></span>
                                <span style="font-size:11px; color:var(--text-tertiary);"><i class="bi bi-geo-alt"></i> <?php echo $t->sede ?: 'Sin sede asignada'; ?></span>
                            </div>
                        </td>
                        <td style="font-size:13px; font-weight:600; color:var(--text-secondary);"><?php echo $t->facilitador_nombre . ' ' . $t->facilitador_apellido; ?></td>
                        <td style="font-size:12px; color:var(--text-tertiary);"><?php echo date('d/m/Y', strtotime($t->fecha_inicio)); ?></td>
                        <td style="text-align:center;">
                            <?php if(isset($t->total_atendidas)): ?>
                                <div style="display:flex; justify-content:center; gap:var(--sp-3); margin-bottom:2px;">
                                    <span title="Mujeres" style="font-size:12px; font-weight:700; color:var(--brand-500);">👩 <?php echo $t->mujeres; ?></span>
                                    <span title="Hombres" style="font-size:12px; font-weight:700; color:var(--text-secondary);">👨 <?php echo $t->hombres; ?></span>
                                    <span title="Niños/as" style="font-size:12px; font-weight:700; color:var(--success-500);">👶 <?php echo (int)$t->ninas + (int)$t->ninos; ?></span>
                                </div>
                                <div style="font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">Total: <?php echo $t->total_atendidas; ?></div>
                            <?php else: ?>
                                <span style="font-size:11px; color:var(--text-tertiary); font-style:italic;">Sin informe final</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;">
                            <?php 
                                $porcentaje = $t->cupo_maximo > 0 ? round(($t->total_inscritos / $t->cupo_maximo) * 100) : 0;
                            ?>
                            <div style="font-size:12px; font-weight:700; color:var(--text-primary); margin-bottom:4px;"><?php echo $t->total_inscritos; ?> / <?php echo $t->cupo_maximo; ?></div>
                            <div style="height:4px; width:80px; background:var(--bg-muted); border-radius:2px; margin:0 auto; overflow:hidden;">
                                <div style="height:100%; width:<?php echo $porcentaje; ?>%; background:var(--brand-500);"></div>
                            </div>
                        </td>
                        <td>
                            <?php 
                                $color = 'sig-badge--neutral';
                                if ($t->estado == 'En Curso') $color = 'sig-badge--brand';
                                elseif ($t->estado == 'Programado') $color = 'sig-badge--warning';
                                elseif ($t->estado == 'Finalizado') $color = 'sig-badge--success';
                                elseif ($t->estado == 'Cancelado') $color = 'sig-badge--danger';
                            ?>
                            <span class="sig-badge <?php echo $color; ?>"><?php echo $t->estado; ?></span>
                        </td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/reportes/dossier/<?php echo $t->id; ?>" class="row-action row-action--view" style="width:auto; padding:0 var(--sp-3);" title="Ver Dossier Completo">
                                <i class="bi bi-file-earmark-person"></i> Dossier
                            </a>
                        </td>
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

    // Chart Talleres por Estado
    const stats = {
        total: <?php echo $data['stats']->total_talleres ?? 0; ?>,
        finalizados: <?php echo $data['stats']->finalizados ?? 0; ?>,
        enCurso: <?php echo $data['stats']->en_curso ?? 0; ?>,
        programados: <?php echo $data['stats']->programados ?? 0; ?>
    };
    let cancelados = stats.total - stats.finalizados - stats.enCurso - stats.programados;
    if (cancelados < 0) cancelados = 0;

    new ApexCharts(document.querySelector("#chartTalleresEstado"), {
        chart: { type: 'donut', height: 300, background: 'transparent' },
        series: [stats.programados, stats.enCurso, stats.finalizados, cancelados],
        labels: ['Programado', 'En Curso', 'Finalizado', 'Cancelado'],
        colors: ['#FBBF24', '#3B82F6', '#10B981', '#EF4444'],
        theme: { mode: isDark ? 'dark' : 'light' },
        legend: { position: 'bottom', labels: { colors: textPrimary } },
        stroke: { show: false },
        plotOptions: { pie: { donut: { size: '65%', labels: { show: true, total: { show: true, label: 'TOTAL', color: textPrimary, fontSize: '14px', fontWeight: 800 } } } } },
        dataLabels: { enabled: false }
    }).render();

    // Chart Inscritos vs Cupo
    <?php
        $nombres = []; $inscritos = []; $cupos = [];
        if (!empty($data['talleres'])) {
            $count = 0;
            foreach ($data['talleres'] as $t) {
                if ($count >= 8) break;
                $nombres[] = mb_substr($t->nombre, 0, 15) . '...';
                $inscritos[] = (int)$t->total_inscritos;
                $cupos[] = (int)$t->cupo_maximo;
                $count++;
            }
        }
    ?>
    new ApexCharts(document.querySelector("#chartTalleresCupo"), {
        chart: { type: 'bar', height: 300, background: 'transparent', toolbar: { show: false } },
        series: [
            { name: 'Inscritos', data: <?php echo json_encode($inscritos); ?> },
            { name: 'Cupo Máximo', data: <?php echo json_encode($cupos); ?> }
        ],
        xaxis: { categories: <?php echo json_encode($nombres); ?>, labels: { style: { colors: textPrimary, fontSize: '10px' } }, axisBorder: { show: false } },
        yaxis: { labels: { style: { colors: textPrimary } } },
        theme: { mode: isDark ? 'dark' : 'light' },
        colors: ['#3B82F6', '#E2E8F0'],
        plotOptions: { bar: { borderRadius: 4, columnWidth: '55%' } },
        grid: { borderColor: borderSubtle, strokeDashArray: 4 },
        dataLabels: { enabled: false },
        legend: { position: 'top', labels: { colors: textPrimary } }
    }).render();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
