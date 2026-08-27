<?php
require_once '../app/views/inc/header.php';

$periodo = $data['periodo'];
$grupos  = $data['grupos'] ?? [];
$resumen = $data['resumen'] ?? [];
$avisos  = $data['advertencias'] ?? [];
$cerrado = ($periodo->estado === 'Cerrado');
$fmt = fn($v) => number_format((float)$v, 2, ',', '.');

$totConf = 0.0; $totCalc = 0.0; $cantidad = 0; $sinConfirmar = 0;
foreach ($resumen as $r) {
    $totConf  += $r['total'];
    $totCalc  += $r['total_calculado'];
    $cantidad += $r['cantidad'];
    $sinConfirmar += $r['sin_confirmar'];
}
$delta = $totConf - $totCalc;
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/nomina/index" style="text-decoration:none;">Nómina — Bono Vacacional</a>
        </div>
        <h1 class="page__title">
            <?php echo htmlspecialchars($periodo->periodo); ?>
            <span class="sig-badge <?php echo $cerrado ? 'sig-badge--neutral' : 'sig-badge--warning'; ?>"><?php echo htmlspecialchars($periodo->estado); ?></span>
        </h1>
        <p class="page__subtitle">
            Corte <?php echo date('d/m/Y', strtotime($periodo->fecha_corte)); ?> ·
            cesta ticket <?php echo $fmt($periodo->monto_cesta_ticket ?? 0); ?>
            <br><span style="font-size:12px;">
                Las primas, el sueldo diario y la alícuota los <strong>calcula</strong> el sistema con el
                mismo motor de la nómina quincenal. El <strong>total</strong> sigue siendo el que
                confirma Talento Humano.
            </span>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/exportarPeriodo/<?php echo $periodo->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-excel"></i> Exportar .xlsx
        </a>
        <?php if (!$cerrado): ?>
            <form action="<?php echo URL_ROOT; ?>/nomina/recalcularPeriodo/<?php echo $periodo->id; ?>" method="POST" style="display:inline;">
                <button type="submit" class="btn-sig btn-sig--ghost" title="Recalcula con los datos actuales de las fichas; conserva los totales ya confirmados">
                    <i class="bi bi-arrow-repeat"></i> Recalcular
                </button>
            </form>
            <?php if ($sinConfirmar > 0): ?>
            <form action="<?php echo URL_ROOT; ?>/nomina/aceptarCalculados/<?php echo $periodo->id; ?>" method="POST" style="display:inline;"
                  onsubmit="return confirm('Se tomarán como confirmados los <?php echo $sinConfirmar; ?> total(es) calculados que están vacíos. ¿Continuar?');">
                <button type="submit" class="btn-sig btn-sig--ghost"><i class="bi bi-check2-all"></i> Aceptar calculados (<?php echo $sinConfirmar; ?>)</button>
            </form>
            <?php endif; ?>
            <form action="<?php echo URL_ROOT; ?>/nomina/cerrarPeriodo/<?php echo $periodo->id; ?>" method="POST" style="display:inline;"
                  onsubmit="return confirm('Al cerrar el período ya no se podrá recalcular ni editar. ¿Continuar?');">
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-lock"></i> Cerrar período</button>
            </form>
        <?php endif; ?>
        <a href="<?php echo URL_ROOT; ?>/nomina/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-question-circle"></i>
    <div>
        <strong>La fórmula del total todavía no está confirmada.</strong>
        La plantilla del cliente documenta la <em>alícuota</em> (el devengo diario), no el monto que se
        paga, y el mes ya calculado que prometió no llegó. La columna <em>Total calculado</em> es la
        estimación del sistema bajo un supuesto explícito
        (<code>sueldo normal diario × días correspondientes</code>);
        la columna <em>Total confirmado</em> es la cifra oficial.
        <strong>Cuando llegue un mes real, la diferencia entre ambas dice si el supuesto acierta.</strong>
    </div>
</div>

<?php if (!empty($avisos)): ?>
<div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-exclamation-triangle"></i>
    <div>
        <strong><?php echo count($avisos); ?> trabajador(es) con datos sin resolver.</strong>
        Corrija la ficha y use <em>Recalcular</em> antes de cerrar.
        <ul style="margin:8px 0 0;padding-left:18px;">
            <?php foreach ($avisos as $a): ?>
                <li style="margin-bottom:4px;">
                    <strong><?php echo htmlspecialchars(trim($a->apellido . ' ' . $a->nombre)); ?></strong>
                    (<?php echo htmlspecialchars($a->cedula); ?>) — <?php echo htmlspecialchars($a->advertencias); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Cuadro resumen -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-bar-chart"></i> Cuadro resumen</div>
        <?php if ($sinConfirmar > 0): ?>
            <span class="sig-badge sig-badge--warning"><?php echo $sinConfirmar; ?> sin confirmar</span>
        <?php endif; ?>
    </div>
    <div class="sig-card__body" style="padding:0;">
        <div class="sig-table-wrap" data-no-export>
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>Tipo de personal</th>
                        <th style="text-align:center;">Trab.</th>
                        <th style="text-align:right;">Total calculado</th>
                        <th style="text-align:right;">Total confirmado</th>
                        <th style="text-align:right;">Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($resumen as $tipo => $r):
                        $d = $r['total'] - $r['total_calculado'];
                    ?>
                    <tr<?php echo $r['cantidad'] === 0 ? ' style="opacity:.5;"' : ''; ?>>
                        <td class="cell-strong"><?php echo htmlspecialchars($tipo); ?></td>
                        <td style="text-align:center;"><?php echo (int)$r['cantidad']; ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($r['total_calculado']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;">
                            <?php echo $r['sin_confirmar'] === $r['cantidad'] && $r['cantidad'] > 0 ? '—' : $fmt($r['total']); ?>
                        </td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;<?php echo abs($d) > 0.01 ? 'color:var(--warning-600);' : ''; ?>">
                            <?php echo $r['sin_confirmar'] === $r['cantidad'] ? '—' : $fmt($d); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr style="border-top:2px solid var(--border-subtle);font-weight:700;">
                        <td>TOTAL</td>
                        <td style="text-align:center;"><?php echo $cantidad; ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($totCalc); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($totConf); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;<?php echo abs($delta) > 0.01 ? 'color:var(--warning-600);' : ''; ?>"><?php echo $fmt($delta); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Una sección por tipo de personal -->
<?php foreach ($grupos as $tipo => $filas): if (empty($filas)) continue; ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-people"></i> <?php echo htmlspecialchars($tipo); ?></div>
        <div style="font-size:12px;color:var(--text-tertiary);"><?php echo count($filas); ?> trabajador(es)</div>
    </div>
    <div class="sig-card__body" style="padding:0;">
        <div class="sig-table-wrap" data-no-export style="overflow-x:auto;">
            <table class="sig-table" style="min-width:1150px;">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Nombre y cargo</th>
                        <th style="text-align:center;">Días</th>
                        <th>Grado</th>
                        <th style="text-align:right;">Base quinc.</th>
                        <th style="text-align:right;">Prima prof.</th>
                        <th style="text-align:right;">Prima antig.</th>
                        <th style="text-align:right;">Transp.</th>
                        <th style="text-align:right;">Hijos</th>
                        <th style="text-align:right;">Diario</th>
                        <th style="text-align:right;">Alícuota</th>
                        <th style="text-align:right;">Total calculado</th>
                        <th style="text-align:right;">Total confirmado</th>
                        <?php if (!$cerrado): ?><th class="col-actions">Guardar</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $f):
                        $tConf = $f->total_bono_vacacional;
                        $tCalc = $f->total_calculado;
                        $dif   = ($tConf !== null && $tCalc !== null) ? ((float)$tConf - (float)$tCalc) : null;
                    ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f->cedula); ?></td>
                            <td class="cell-strong">
                                <?php echo htmlspecialchars(trim($f->apellido . ' ' . $f->nombre)); ?>
                                <?php if (!empty($f->advertencias)): ?>
                                    <i class="bi bi-exclamation-triangle" style="color:var(--warning-600);"
                                       title="<?php echo htmlspecialchars($f->advertencias); ?>"></i>
                                <?php endif; ?>
                                <br><small style="color:var(--text-tertiary);"><?php echo htmlspecialchars($f->cargo ?? ''); ?></small>
                            </td>
                            <td style="text-align:center;font-weight:600;"><?php echo (int)$f->dias_vacaciones; ?></td>
                            <td>
                                <?php if (!empty($f->codigo_grado)): ?>
                                    <span class="sig-badge sig-badge--info"><?php echo htmlspecialchars($f->codigo_grado); ?></span>
                                    <small style="color:var(--text-tertiary);"><?php echo number_format((float)$f->pct_profesionalizacion, 0); ?>%</small>
                                <?php else: ?>
                                    <span style="color:var(--text-tertiary);font-style:italic;">sin grado</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($f->sueldo_base_quincenal); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($f->prima_profesional); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">
                                <?php echo $fmt($f->prima_antiguedad); ?>
                                <br><small style="color:var(--text-tertiary);"><?php echo (int)$f->anios_administracion; ?>a · <?php echo number_format((float)$f->pct_antiguedad, 1, ',', '.'); ?>%</small>
                            </td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($f->bono_transporte); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">
                                <?php echo $fmt($f->prima_por_hijo); ?>
                                <?php if ((int)$f->n_hijos > 0): ?><br><small style="color:var(--text-tertiary);"><?php echo (int)$f->n_hijos; ?> hijo(s)</small><?php endif; ?>
                            </td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($f->sueldo_normal_diario); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $fmt($f->alicuotas); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;color:var(--text-secondary);"><?php echo $tCalc !== null ? $fmt($tCalc) : '—'; ?></td>

                            <?php if ($cerrado): ?>
                                <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;">
                                    <?php echo $tConf !== null ? $fmt($tConf) : '—'; ?>
                                    <?php if ($dif !== null && abs($dif) > 0.01): ?>
                                        <br><small style="color:var(--warning-600);">dif. <?php echo $fmt($dif); ?></small>
                                    <?php endif; ?>
                                </td>
                            <?php else: ?>
                                <form action="<?php echo URL_ROOT; ?>/nomina/guardarDetalle" method="POST" style="display:contents;">
                                    <input type="hidden" name="id_detalle" value="<?php echo (int)$f->id; ?>">
                                    <input type="hidden" name="grado_escala" value="<?php echo htmlspecialchars($f->grado_escala ?? ''); ?>">
                                    <td style="text-align:right;">
                                        <input type="number" step="0.01" min="0" name="total_bono_vacacional" class="sig-input"
                                               style="width:110px;text-align:right;"
                                               value="<?php echo $tConf !== null ? $tConf : ''; ?>"
                                               placeholder="<?php echo $tCalc !== null ? $fmt($tCalc) : 'Capturar'; ?>">
                                        <?php if ($dif !== null && abs($dif) > 0.01): ?>
                                            <br><small style="color:var(--warning-600);">dif. <?php echo $fmt($dif); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td class="col-actions">
                                        <button type="submit" class="btn-sig btn-sig--xs btn-sig--primary" title="Guardar total"><i class="bi bi-check-lg"></i></button>
                                    </td>
                                </form>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (array_sum(array_map('count', $grupos)) === 0): ?>
    <div class="sig-alert sig-alert--info anim-slide-up">
        <i class="bi bi-info-circle"></i>
        <div>No hay trabajadores en este período. Revise que haya personal activo sin fecha de egreso.</div>
    </div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
