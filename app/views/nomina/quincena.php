<?php
require_once '../app/views/inc/header.php';
$per = $data['periodo'];
$esBorrador = ($per->estado === 'Borrador');
$m  = fn($v) => number_format((float)$v, 2, ',', '.');
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/nomina/quincenal" style="text-decoration:none;">Nómina quincenal</a>
            · <?php echo htmlspecialchars($per->periodo); ?>
        </div>
        <h1 class="page__title">
            Quincena <?php echo (int)$per->quincena; ?> — <?php echo htmlspecialchars($per->periodo); ?>
            <span class="sig-badge <?php echo $esBorrador ? 'sig-badge--warning' : 'sig-badge--neutral'; ?>">
                <?php echo htmlspecialchars($per->estado); ?>
            </span>
        </h1>
        <p class="page__subtitle">
            Corte <?php echo date('d/m/Y', strtotime($per->fecha_corte)); ?> ·
            cesta ticket <?php echo $m($per->monto_cesta_ticket); ?> ·
            tasa del dólar <?php echo number_format((float)$per->tasa_dolar, 4, ',', '.'); ?> ·
            <?php echo (int)$per->semanas; ?> semanas.
            <br><span style="font-size:12px;">Estos parámetros quedaron congelados al generar, para que la quincena se pueda reconstruir tal como se pagó.</span>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/exportarQuincena/<?php echo $per->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-excel"></i> Exportar (6 hojas)
        </a>
        <?php if ($esBorrador): ?>
            <form action="<?php echo URL_ROOT; ?>/nomina/recalcularQuincena/<?php echo $per->id; ?>" method="POST" style="display:inline;">
                <button type="submit" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-repeat"></i> Recalcular</button>
            </form>
            <form action="<?php echo URL_ROOT; ?>/nomina/cerrarQuincena/<?php echo $per->id; ?>" method="POST" style="display:inline;"
                  onsubmit="return confirm('Al cerrar la quincena ya no se podrá recalcular ni editar. ¿Continuar?');">
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-lock"></i> Cerrar quincena</button>
            </form>
        <?php endif; ?>
    </div>
</div>

<?php if (!empty($data['advertencias'])): ?>
<div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-exclamation-triangle"></i>
    <div>
        <strong><?php echo count($data['advertencias']); ?> trabajador(es) con datos sin resolver.</strong>
        El cálculo se hizo con lo que había, pero estos casos no están completos. Corrija la ficha
        y use <em>Recalcular</em> antes de cerrar — el sistema avisa en vez de pagar 0 en silencio.
        <ul style="margin:8px 0 0;padding-left:18px;">
            <?php foreach ($data['advertencias'] as $a): ?>
                <li style="margin-bottom:4px;">
                    <strong><?php echo htmlspecialchars(trim($a->apellido . ' ' . $a->nombre)); ?></strong>
                    (<?php echo htmlspecialchars($a->cedula); ?>) — <?php echo htmlspecialchars($a->advertencias); ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<!-- Resumen consolidado -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-table"></i> Resumen consolidado</div></div>
    <div class="sig-card__body" style="padding:0;">
        <div class="sig-table-wrap" data-no-export>
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>Tipo de personal</th>
                        <th style="text-align:center;">Trab.</th>
                        <th style="text-align:right;">Sueldo normal</th>
                        <th style="text-align:right;">SSO</th>
                        <th style="text-align:right;">FAOV</th>
                        <th style="text-align:right;">LRPPF</th>
                        <th style="text-align:right;">Deducciones</th>
                        <th style="text-align:right;">Neto a cobrar</th>
                        <th style="text-align:right;">Aportes patronales</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $tot = array_fill_keys(['cantidad','total_sueldo','sso','faov','lrppf','total_deducciones','total_neto','total_aportes'], 0);
                    foreach ($data['resumen'] as $tipo => $r):
                        foreach ($tot as $k => $v) $tot[$k] = $v + $r[$k];
                    ?>
                        <tr<?php echo $r['cantidad'] === 0 ? ' style="opacity:.5;"' : ''; ?>>
                            <td class="cell-strong"><?php echo htmlspecialchars($tipo); ?></td>
                            <td style="text-align:center;"><?php echo $r['cantidad']; ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($r['total_sueldo']); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($r['sso']); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($r['faov']); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($r['lrppf']); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($r['total_deducciones']); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;"><?php echo $m($r['total_neto']); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($r['total_aportes']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr style="border-top:2px solid var(--border-strong,var(--border-subtle));font-weight:700;">
                        <td>TOTAL GENERAL</td>
                        <td style="text-align:center;"><?php echo $tot['cantidad']; ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['total_sueldo']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['sso']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['faov']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['lrppf']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['total_deducciones']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['total_neto']); ?></td>
                        <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($tot['total_aportes']); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Una sección por tipo de personal = una hoja del formato oficial -->
<?php foreach ($data['grupos'] as $tipo => $filas): if (empty($filas)) continue; $esComision = ($tipo === 'Comisión de Servicio'); ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-people"></i> <?php echo htmlspecialchars($tipo); ?></div>
        <div style="font-size:12px;color:var(--text-tertiary);"><?php echo count($filas); ?> trabajador(es)</div>
    </div>
    <div class="sig-card__body" style="padding:0;">
        <div class="sig-table-wrap" data-no-export style="overflow-x:auto;">
            <table class="sig-table" style="min-width:1200px;">
                <thead>
                    <tr>
                        <th>Cédula</th>
                        <th>Apellidos y Nombres</th>
                        <th>Grado</th>
                        <th style="text-align:center;">Años</th>
                        <th style="text-align:right;">Base quincenal</th>
                        <th style="text-align:right;">Prima prof.</th>
                        <th style="text-align:right;">Prima antig.</th>
                        <th style="text-align:right;">Transporte</th>
                        <th style="text-align:right;">Hijos</th>
                        <th style="text-align:right;">Sueldo normal</th>
                        <th style="text-align:right;">Deducciones</th>
                        <th style="text-align:right;">Neto</th>
                        <?php if ($esComision): ?><th style="text-align:right;">Dif. a pagar</th><?php endif; ?>
                        <th style="text-align:right;">Bono resp.</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($filas as $f): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($f->cedula); ?></td>
                            <td class="cell-strong">
                                <?php echo htmlspecialchars(trim($f->apellido . ' ' . $f->nombre)); ?>
                                <?php if (!empty($f->advertencias)): ?>
                                    <i class="bi bi-exclamation-triangle" style="color:var(--warning-600);"
                                       title="<?php echo htmlspecialchars($f->advertencias); ?>"></i>
                                <?php endif; ?>
                                <br><small style="color:var(--text-tertiary);"><?php echo htmlspecialchars($f->cargo); ?></small>
                            </td>
                            <td>
                                <?php if ($f->codigo_grado): ?>
                                    <span class="sig-badge sig-badge--info"><?php echo htmlspecialchars($f->codigo_grado); ?></span>
                                    <small style="color:var(--text-tertiary);"><?php echo number_format((float)$f->pct_profesionalizacion, 0); ?>%</small>
                                <?php else: ?>
                                    <span style="color:var(--text-tertiary);font-style:italic;">sin grado</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align:center;">
                                <?php echo (int)$f->anios_administracion; ?>
                                <br><small style="color:var(--text-tertiary);"><?php echo number_format((float)$f->pct_antiguedad, 1, ',', '.'); ?>%</small>
                            </td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($f->sueldo_base_quincenal); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($f->prima_profesionalizacion); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($f->prima_antiguedad); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($f->bono_transporte); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;">
                                <?php echo $m($f->prima_por_hijos); ?>
                                <?php if ((int)$f->n_hijos > 0): ?><br><small style="color:var(--text-tertiary);"><?php echo (int)$f->n_hijos; ?> hijo(s)</small><?php endif; ?>
                            </td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:600;"><?php echo $m($f->total_sueldo_normal); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;color:var(--danger-600);">−<?php echo $m($f->total_deducciones); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;"><?php echo $m($f->neto_a_cobrar); ?></td>
                            <?php if ($esComision): ?>
                                <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;">
                                    <?php echo $m($f->diferencia_comision); ?>
                                    <br><small style="color:var(--text-tertiary);">origen <?php echo $m($f->sueldo_dependencia_origen); ?></small>
                                </td>
                            <?php endif; ?>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo $m($f->bono_responsabilidad); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php if (array_sum(array_map('count', $data['grupos'])) === 0): ?>
    <div class="sig-alert sig-alert--info anim-slide-up">
        <i class="bi bi-info-circle"></i>
        <div>
            No hay ningún trabajador en esta quincena. Revise que haya personal activo sin fecha de
            egreso en <a href="<?php echo URL_ROOT; ?>/empleados/index">Personal</a>.
        </div>
    </div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
