<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Turismo
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Métricas de infraestructura turística, puntos de interés y equipamiento de las rutas municipales.</p>
    </div>
    <div class="page__actions">
        <?php
        $qsR = http_build_query(array_filter([
            'estado' => $data['filtro_estado'] ?? '',
        ]));
        ?>
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarRutasCsv?<?php echo $qsR; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarRutasPdf?<?php echo $qsR; ?>" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
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
        <div class="sig-card" style="border-bottom: 3px solid var(--brand-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Finalizadas</span>
                <span style="font-size:28px; font-weight:800; color:var(--brand-600);"><?php echo $data['stats']->finalizadas ?? 0; ?></span>
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
    <div class="col-md-3">
        <div class="sig-card" style="border-bottom: 3px solid var(--slate-400);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-5);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:2px;">Inactivas</span>
                <span style="font-size:28px; font-weight:800; color:var(--text-secondary);"><?php echo $data['stats']->inactivas ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="" class="anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card">
        <div class="sig-card__body" style="padding:var(--sp-3) var(--sp-5);">
            <div style="display:flex; flex-wrap:wrap; gap:var(--sp-3); align-items:flex-end;">
                <div class="sig-field" style="margin:0; min-width:170px;">
                    <label class="sig-field__label">Estado</label>
                    <select name="estado" class="sig-select">
                        <option value="">Todos los estados</option>
                        <?php foreach (['Activa','Inactiva','En Mantenimiento'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if (($data['filtro_estado'] ?? '') === $opt) echo 'selected'; ?>>
                                <?php echo $opt; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-sig btn-sig--primary btn-sig--sm">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <?php if (!empty($data['filtro_estado'])): ?>
                <a href="<?php echo URL_ROOT; ?>/reportes/rutas" class="btn-sig btn-sig--ghost btn-sig--sm">
                    <i class="bi bi-x-circle"></i> Limpiar
                </a>
                <?php endif; ?>
                <span style="font-size:12px; color:var(--text-tertiary); margin-left:auto;">
                    <?php echo count($data['rutas']); ?> resultado(s)
                </span>
            </div>
        </div>
    </div>
</form>

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

<!-- Demografía agregada por tipo de ruta -->
<?php if (!empty($data['statsPorTipo'])): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6); border-top:3px solid #D97706;">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-diagram-3-fill" style="color:#D97706;"></i> Demografía Consolidada por Tipo de Ruta</div>
        <span style="font-size:11px; color:var(--text-tertiary);">Suma de informes de visita registrados</span>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th>Tipo de Ruta</th>
                    <th style="text-align:center;">Rutas</th>
                    <th style="text-align:center;">Finalizadas</th>
                    <th style="text-align:center;">Mujeres</th>
                    <th style="text-align:center;">Hombres</th>
                    <th style="text-align:center;">Niñas</th>
                    <th style="text-align:center;">Niños</th>
                    <th style="text-align:center;">Total Atendidos</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $gM=0;$gH=0;$gNa=0;$gNo=0;$gT=0;$gR=0;$gF=0;
                foreach ($data['statsPorTipo'] as $st):
                    $gM+=(int)$st->mujeres; $gH+=(int)$st->hombres; $gNa+=(int)$st->ninas;
                    $gNo+=(int)$st->ninos; $gT+=(int)$st->total_atendidos;
                    $gR+=(int)$st->rutas; $gF+=(int)$st->finalizadas;
                ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($st->tipo_ruta); ?></td>
                    <td style="text-align:center;"><?php echo (int)$st->rutas; ?></td>
                    <td style="text-align:center; color:#7C3AED; font-weight:700;"><?php echo (int)$st->finalizadas; ?></td>
                    <td style="text-align:center;"><?php echo (int)$st->mujeres; ?></td>
                    <td style="text-align:center;"><?php echo (int)$st->hombres; ?></td>
                    <td style="text-align:center;"><?php echo (int)$st->ninas; ?></td>
                    <td style="text-align:center;"><?php echo (int)$st->ninos; ?></td>
                    <td style="text-align:center; font-weight:800; color:var(--success-600);"><?php echo (int)$st->total_atendidos; ?></td>
                </tr>
                <?php endforeach; ?>
                <tr style="background:var(--bg-muted-subtle); font-weight:800;">
                    <td>TOTAL</td>
                    <td style="text-align:center;"><?php echo $gR; ?></td>
                    <td style="text-align:center; color:#7C3AED;"><?php echo $gF; ?></td>
                    <td style="text-align:center;"><?php echo $gM; ?></td>
                    <td style="text-align:center;"><?php echo $gH; ?></td>
                    <td style="text-align:center;"><?php echo $gNa; ?></td>
                    <td style="text-align:center;"><?php echo $gNo; ?></td>
                    <td style="text-align:center; color:var(--success-600);"><?php echo $gT; ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Tabla de Resultados -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Nombre de la Ruta</th>
                <th>Tipo</th>
                <th>Fecha Visita</th>
                <th>Guía</th>
                <th>Estado</th>
                <th style="text-align:center;">Paradas</th>
                <th style="text-align:center;">Particip.</th>
                <th style="text-align:center;">Atendidos</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['rutas'])): ?>
                <tr>
                    <td colspan="8" class="sig-table-empty">No hay rutas registradas para generar el reporte.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['rutas'] as $r): ?>
                    <tr>
                        <td class="cell-strong"><?php echo htmlspecialchars($r->nombre); ?></td>
                        <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($r->tipo_ruta ?? 'General'); ?></td>
                        <td style="font-size:12px; color:var(--text-secondary);">
                            <?php if ($r->fecha_visita): ?>
                                <?php echo date('d/m/Y', strtotime($r->fecha_visita)); ?>
                                <?php if ($r->hora_visita): ?><br><span style="color:var(--text-tertiary);"><?php echo substr($r->hora_visita, 0, 5); ?></span><?php endif; ?>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary);">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($r->facilitador_nombre ?? '—'); ?></td>
                        <td>
                            <?php
                            $statusBadge = 'sig-badge--neutral';
                            if ($r->estado == 'Activa') $statusBadge = 'sig-badge--success';
                            elseif ($r->estado == 'Inactiva') $statusBadge = 'sig-badge--danger';
                            elseif ($r->estado == 'En Mantenimiento') $statusBadge = 'sig-badge--warning';
                            elseif ($r->estado == 'Finalizada') $statusBadge = 'sig-badge--brand';
                            ?>
                            <span class="sig-badge sig-badge--sm <?php echo $statusBadge; ?>"><?php echo $r->estado; ?></span>
                        </td>
                        <td style="text-align:center; font-weight:700; color:var(--text-primary);"><?php echo (int)$r->total_puntos; ?></td>
                        <td style="text-align:center; font-weight:700; color:var(--text-primary);"><?php echo (int)$r->total_participantes; ?></td>
                        <td style="text-align:center; font-weight:700; color:var(--success-600);"><?php echo (int)($r->total_atendidos ?? 0); ?></td>
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
            chart: {
                type: 'donut',
                height: 320,
                background: 'transparent'
            },
            series: [
                <?php echo $data['stats']->activas ?? 0; ?>,
                <?php echo $data['stats']->inactivas ?? 0; ?>,
                <?php echo $data['stats']->mantenimiento ?? 0; ?>
            ],
            labels: ['Activa', 'Inactiva', 'En Mantenimiento'],
            colors: ['#10B981', '#64748B', '#F59E0B'],
            theme: {
                mode: isDark ? 'dark' : 'light'
            },
            legend: {
                position: 'bottom',
                labels: {
                    colors: textPrimary
                }
            },
            stroke: {
                show: false
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'TOTAL',
                                color: textPrimary,
                                fontSize: '14px',
                                fontWeight: 800
                            }
                        }
                    }
                }
            },
            dataLabels: {
                enabled: false
            }
        }).render();

        // Barras agrupadas: Métricas por Ruta
        <?php
        $nR = [];
        $puntosR = [];
        $partR = [];
        $atR = [];
        if (!empty($data['rutas'])) {
            $cnt = 0;
            foreach ($data['rutas'] as $r) {
                if ($cnt >= 8) break;
                $lbl = mb_strlen($r->nombre) > 16 ? mb_substr($r->nombre, 0, 14) . '…' : $r->nombre;
                $nR[]      = $lbl;
                $puntosR[] = (int)$r->total_puntos;
                $partR[]   = (int)$r->total_participantes;
                $atR[]     = (int)($r->total_atendidos ?? 0);
                $cnt++;
            }
        }
        ?>
        new ApexCharts(document.querySelector("#chartRutasMetricas"), {
            chart: {
                type: 'bar',
                height: 320,
                background: 'transparent',
                toolbar: { show: false }
            },
            series: [{
                    name: 'Paradas',
                    data: <?php echo json_encode($puntosR); ?>
                },
                {
                    name: 'Participantes',
                    data: <?php echo json_encode($partR); ?>
                },
                {
                    name: 'Atendidos (informe)',
                    data: <?php echo json_encode($atR); ?>
                }
            ],
            xaxis: {
                categories: <?php echo json_encode($nR); ?>,
                labels: {
                    style: {
                        colors: textPrimary,
                        fontSize: '10px'
                    }
                },
                axisBorder: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: textPrimary
                    }
                }
            },
            theme: {
                mode: isDark ? 'dark' : 'light'
            },
            colors: ['#14B8A6', '#F59E0B', '#3B82F6'],
            plotOptions: {
                bar: {
                    borderRadius: 4,
                    columnWidth: '60%',
                    dataLabels: {
                        position: 'top'
                    }
                }
            },
            grid: {
                borderColor: borderSubtle,
                strokeDashArray: 4
            },
            dataLabels: {
                enabled: false
            },
            legend: {
                position: 'top',
                labels: {
                    colors: textPrimary
                }
            }
        }).render();
    });
</script>

<?php require_once '../app/views/inc/footer.php'; ?>