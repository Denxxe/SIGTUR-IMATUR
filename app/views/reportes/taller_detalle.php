<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up d-print-none">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/talleres" style="color:inherit; text-decoration:none;">Formación</a> · Dossier de Actividad
        </div>
        <h1 class="page__title">Dossier Integral de Actividad</h1>
        <p class="page__subtitle">Documento técnico oficial que consolida la ejecución, demografía y participación del taller.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarDossierCsv/<?php echo isset($data['taller']->id) ? $data['taller']->id : ''; ?>" class="btn-sig btn-sig--success btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <button onclick="window.print()" class="btn-sig btn-sig--danger btn-sig--sm no-print">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </button>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/talleres" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="print-area anim-slide-up">
    <!-- SECCIÓN 1: FICHA TÉCNICA -->
    <div class="sig-card mb-6 overflow-hidden">
        <div class="sig-card__head" style="background:var(--bg-muted); border-bottom:1px solid var(--border-subtle);">
            <div class="sig-card__title">
                <i class="bi bi-info-circle-fill" style="color:var(--brand-500);"></i> I. FICHA TÉCNICA DEL TALLER
            </div>
        </div>
        <div class="sig-card__body" style="padding:var(--sp-8);">
            <div class="row g-4">
                <div class="col-md-7" style="border-right:1px solid var(--border-subtle);">
                    <div style="margin-bottom:var(--sp-6);">
                        <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Nombre de la Actividad</small>
                        <h2 style="font-size:24px; font-weight:800; color:var(--brand-600); line-height:1.2; margin:0;"><?php echo isset($data['taller']->nombre) ? $data['taller']->nombre : 'N/A'; ?></h2>
                    </div>
                    <div class="row g-4">
                        <div class="col-6">
                            <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Facilitador</small>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo (isset($data['taller']->fac_nom) ? $data['taller']->fac_nom : 'N/A') . ' ' . (isset($data['taller']->fac_ape) ? $data['taller']->fac_ape : ''); ?></div>
                        </div>
                        <div class="col-6">
                            <small style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Sede / Ubicación</small>
                            <div style="font-weight:700; color:var(--text-primary);"><?php echo $data['taller']->sede ?? 'No especificada'; ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5">
                    <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:var(--sp-4);">
                        <div style="background:var(--bg-muted-subtle); padding:var(--sp-4); border-radius:var(--r-md); text-align:center;">
                            <small style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Fecha</small>
                            <div style="font-weight:800; font-size:15px; color:var(--text-primary);"><?php echo isset($data['taller']->fecha_inicio) ? date('d/m/Y', strtotime($data['taller']->fecha_inicio)) : 'N/A'; ?></div>
                        </div>
                        <div style="background:var(--bg-muted-subtle); padding:var(--sp-4); border-radius:var(--r-md); text-align:center;">
                            <small style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Hora</small>
                            <div style="font-weight:800; font-size:15px; color:var(--text-primary);"><?php echo $data['taller']->hora_inicio ?? 'N/A'; ?></div>
                        </div>
                        <div style="grid-column: span 2; background:var(--brand-50); padding:var(--sp-4); border-radius:var(--r-md); text-align:center;">
                            <small style="display:block; font-size:10px; font-weight:700; color:var(--brand-600); text-transform:uppercase; margin-bottom:4px;">Estatus de Ejecución</small>
                            <span class="sig-badge sig-badge--brand" style="font-size:13px;"><?php echo isset($data['taller']->estado) ? $data['taller']->estado : 'N/A'; ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: MÉTRICAS Y DEMOGRAFÍA -->
    <div class="row g-6 mb-6">
        <div class="col-md-5">
            <div class="sig-card h-100">
                <div class="sig-card__head">
                    <div class="sig-card__title">
                        <i class="bi bi-pie-chart-fill" style="color:var(--accent-500);"></i> II. DESGLOSE DEMOGRÁFICO
                    </div>
                </div>
                <div class="sig-card__body" style="padding:var(--sp-6);">
                    <?php if (isset($data['informe'])): ?>
                        <div id="chartDemografia" style="margin-bottom:var(--sp-4);"></div>
                        <div style="display:grid; grid-template-columns:repeat(4, 1fr); gap:var(--sp-2); text-align:center; padding-top:var(--sp-4); border-top:1px solid var(--border-subtle);">
                            <div>
                                <small style="display:block; font-size:9px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">Mujeres</small>
                                <span style="font-weight:800; color:var(--brand-600);"><?php echo $data['informe']->mujeres ?? 0; ?></span>
                            </div>
                            <div>
                                <small style="display:block; font-size:9px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">Hombres</small>
                                <span style="font-weight:800; color:var(--text-secondary);"><?php echo $data['informe']->hombres ?? 0; ?></span>
                            </div>
                            <div>
                                <small style="display:block; font-size:9px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">Niños/as</small>
                                <span style="font-weight:800; color:var(--success-600);"><?php echo (int)($data['informe']->ninas ?? 0) + (int)($data['informe']->ninos ?? 0); ?></span>
                            </div>
                            <div style="border-left:1px solid var(--border-subtle);">
                                <small style="display:block; font-size:9px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase;">TOTAL</small>
                                <span style="font-weight:900; color:var(--text-primary); font-size:16px;"><?php echo $data['informe']->total_atendidas ?? 0; ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align:center; padding:var(--sp-12); color:var(--text-tertiary);">
                            <i class="bi bi-exclamation-triangle" style="font-size:32px; display:block; margin-bottom:var(--sp-4);"></i>
                            <p style="font-size:13px;">No hay informe demográfico registrado.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="sig-card h-100">
                <div class="sig-card__head">
                    <div class="sig-card__title">
                        <i class="bi bi-file-text-fill" style="color:var(--brand-500);"></i> III. RESUMEN DE LA ACTIVIDAD
                    </div>
                </div>
                <div class="sig-card__body" style="padding:var(--sp-6);">
                    <div style="font-size:14px; color:var(--text-secondary); line-height:1.7; margin-bottom:var(--sp-6); min-height:100px;">
                        <?php echo (isset($data['informe']->resumen_actividad)) ? nl2br(htmlspecialchars($data['informe']->resumen_actividad)) : '<p style="font-style:italic; opacity:0.6;">Sin resumen ejecutivo disponible.</p>'; ?>
                    </div>
                    <div style="padding-top:var(--sp-6); border-top:1px solid var(--border-subtle); display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <small style="display:block; font-size:10px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; margin-bottom:4px;">Instituciones Aliadas</small>
                            <div style="font-size:13px; font-weight:600; color:var(--text-primary);"><?php echo $data['informe']->instituciones_presentes ?? 'Ninguna registrada'; ?></div>
                        </div>
                        <div style="background:var(--brand-50); padding:var(--sp-3) var(--sp-5); border-radius:var(--r-md); text-align:center;">
                            <small style="display:block; font-size:10px; font-weight:700; color:var(--brand-600); text-transform:uppercase;">Ratio de Asistencia</small>
                            <?php
                            $asistieron = count(array_filter($data['participantes'] ?? [], function ($p) {
                                return $p->asistio ?? false;
                            }));
                            $total_ins = count($data['participantes'] ?? []);
                            $porcentaje = $total_ins > 0 ? round(($asistieron / $total_ins) * 100) : 0;
                            ?>
                            <div style="font-size:24px; font-weight:900; color:var(--brand-700);"><?php echo $porcentaje; ?>%</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: LISTADO NOMINAL -->
    <div class="sig-card">
        <div class="sig-card__head" style="background:var(--bg-muted); border-bottom:1px solid var(--border-subtle);">
            <div class="sig-card__title">
                <i class="bi bi-people-fill" style="color:var(--brand-500);"></i> IV. LISTADO DE PARTICIPANTES INSCRITOS
            </div>
        </div>
        <div class="sig-table-wrap">
            <table class="sig-table">
                <thead>
                    <tr>
                        <th style="padding-left:var(--sp-8);">Cédula</th>
                        <th>Nombre Completo</th>
                        <th style="text-align:center;">Estatus de Asistencia</th>
                        <th style="padding-right:var(--sp-8);">Firma de Control</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['participantes'])): ?>
                        <tr>
                            <td colspan="4" class="sig-table-empty">No se han registrado participantes en este taller.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['participantes'] ?? [] as $p): ?>
                            <tr>
                                <td class="cell-id" style="padding-left:var(--sp-8);">
                                    <?php echo htmlspecialchars($p->cedula ?? '—'); ?>
                                    <?php if (!empty($p->es_libre)): ?>
                                        <span class="sig-badge sig-badge--neutral" style="font-size:9px; display:block; margin-top:2px;">Niño/a</span>
                                    <?php endif; ?>
                                </td>
                                <td class="cell-strong">
                                    <?php echo htmlspecialchars(trim(($p->nombre ?? '') . ' ' . ($p->apellido ?? ''))); ?>
                                    <?php if (!empty($p->es_libre) && !empty($p->nombre_docente)): ?>
                                        <span style="display:block; font-size:11px; color:var(--text-secondary); font-weight:400; margin-top:2px;">
                                            <i class="bi bi-person-badge" style="color:var(--brand-400);"></i>
                                            <?php echo htmlspecialchars($p->nombre_docente); ?>
                                            <?php if (!empty($p->cedula_docente)): ?>
                                                <span style="color:var(--text-tertiary);">(<?php echo htmlspecialchars($p->cedula_docente); ?>)</span>
                                            <?php endif; ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align:center;">
                                    <?php if (isset($p->asistio) && $p->asistio): ?>
                                        <span class="sig-badge sig-badge--success"><i class="bi bi-check-circle"></i> Presente</span>
                                    <?php else: ?>
                                        <span class="sig-badge sig-badge--danger"><i class="bi bi-x-circle"></i> Ausente</span>
                                    <?php endif; ?>
                                </td>
                                <td style="padding-right:var(--sp-8); font-size:11px; font-style:italic; color:var(--text-tertiary);">Verificación Digital Institucional</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- FIRMAS PARA IMPRESIÓN -->
        <div class="d-none d-print-block" style="padding:var(--sp-12) var(--sp-12) var(--sp-16);">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:100px; text-align:center;">
                <div>
                    <div style="width:240px; border-top:1px solid var(--text-primary); margin:0 auto 12px; padding-top:12px;">
                        <div style="font-weight:800; font-size:14px; text-transform:uppercase;"><?php echo (isset($data['taller']->fac_nom) ? $data['taller']->fac_nom : 'N/A') . ' ' . (isset($data['taller']->fac_ape) ? $data['taller']->fac_ape : ''); ?></div>
                        <div style="font-size:12px; color:var(--text-tertiary);">Facilitador / Responsable</div>
                    </div>
                </div>
                <div>
                    <div style="width:240px; border-top:1px solid var(--text-primary); margin:0 auto 12px; padding-top:12px;">
                        <div style="font-weight:800; font-size:14px; text-transform:uppercase;">Coordinación de Formación</div>
                        <div style="font-size:12px; color:var(--text-tertiary);">Sello y Firma Institucional</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @media print {
        :root {
            --bg-main: #ffffff !important;
            --page-padding: 0 !important;
        }

        body {
            background: white !important;
        }

        header,
        aside,
        .d-print-none,
        .page__head {
            display: none !important;
        }

        .print-area {
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .sig-card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
            break-inside: avoid;
        }

        .sig-table th {
            background: #f8f9fa !important;
            color: #333 !important;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
        const textPrimary = getComputedStyle(document.body).getPropertyValue('--text-primary').trim();

        <?php if (isset($data['informe'])): ?>
            new ApexCharts(document.querySelector("#chartDemografia"), {
                chart: {
                    type: 'donut',
                    height: 220,
                    background: 'transparent'
                },
                series: [
                    <?php echo (int)($data['informe']->mujeres ?? 0); ?>,
                    <?php echo (int)($data['informe']->hombres ?? 0); ?>,
                    <?php echo (int)($data['informe']->ninas ?? 0) + (int)($data['informe']->ninos ?? 0); ?>
                ],
                labels: ['Mujeres', 'Hombres', 'Niños/as'],
                colors: ['#3B82F6', '#64748B', '#10B981'],
                theme: {
                    mode: isDark ? 'dark' : 'light'
                },
                legend: {
                    show: false
                },
                stroke: {
                    show: false
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    label: 'TOTAL',
                                    color: textPrimary,
                                    fontSize: '12px',
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
        <?php endif; ?>
    });
</script>

<?php require_once '../app/views/inc/footer.php'; ?>