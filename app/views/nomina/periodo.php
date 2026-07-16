<?php
require_once '../app/views/inc/header.php';

$periodo = $data['periodo'];
$grupos  = $data['grupos'] ?? [];
$resumen = $data['resumen'] ?? [];
$cerrado = ($periodo->estado === 'Cerrado');
$fmt = fn($v) => number_format((float)$v, 2, ',', '.');
$totalGeneral = 0.0; $cantidadGeneral = 0;
foreach ($resumen as $r) { $totalGeneral += $r['total']; $cantidadGeneral += $r['cantidad']; }
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Nómina — Bono Vacacional</div>
        <h1 class="page__title"><?php echo htmlspecialchars($periodo->periodo); ?></h1>
        <p class="page__subtitle">
            Fecha de corte <?php echo date('d/m/Y', strtotime($periodo->fecha_corte)); ?> ·
            <span class="sig-badge <?php echo $cerrado ? 'sig-badge--neutral' : 'sig-badge--warning'; ?>"><?php echo htmlspecialchars($periodo->estado); ?></span>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/exportarPeriodo/<?php echo $periodo->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-excel"></i> Exportar .xlsx
        </a>
        <?php if (!$cerrado): ?>
        <form action="<?php echo URL_ROOT; ?>/nomina/cerrarPeriodo/<?php echo $periodo->id; ?>" method="POST" style="display:inline;"
              onsubmit="return confirm('¿Cerrar el período? Ya no se podrán editar los montos capturados.');">
            <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-lock"></i> Cerrar período</button>
        </form>
        <?php endif; ?>
        <a href="<?php echo URL_ROOT; ?>/nomina/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;"><h5 style="margin:0;"><i class="bi bi-bar-chart"></i> Cuadro resumen</h5></div>
    <table class="sig-table">
        <thead><tr><th>Tipo de personal</th><th>Cantidad</th><th>Monto total</th></tr></thead>
        <tbody>
            <?php foreach ($resumen as $tipo => $r): ?>
            <tr>
                <td class="cell-strong"><?php echo htmlspecialchars($tipo); ?></td>
                <td><?php echo (int)$r['cantidad']; ?></td>
                <td><?php echo $fmt($r['total']); ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="cell-strong"><td>TOTAL</td><td><?php echo $cantidadGeneral; ?></td><td><?php echo $fmt($totalGeneral); ?></td></tr>
        </tbody>
    </table>
</div>

<?php foreach ($grupos as $tipo => $filas): ?>
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;"><h5 style="margin:0;"><i class="bi bi-people"></i> <?php echo htmlspecialchars($tipo); ?> (<?php echo count($filas); ?>)</h5></div>
    <table class="sig-table">
        <thead>
            <tr>
                <th>Cédula</th><th>Nombre</th><th>Cargo</th><th>Días</th>
                <th>Grado/Escala</th><th>Sueldo Básico</th><th>Prima Prof.</th><th>Prima Antig.</th>
                <th>Hijos</th><th>Prima/Hijo</th><th>Bono Transp.</th><th>Sueldo Integral</th>
                <th>Cuenta Bancaria</th><th>Cesta Ticket</th><th>Alícuotas</th><th>Total Bono Vac.</th>
                <?php if (!$cerrado): ?><th class="col-actions">Acciones</th><?php endif; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="<?php echo $cerrado ? 16 : 17; ?>" class="sig-table-empty">Sin empleados en esta categoría.</td></tr>
            <?php else: foreach ($filas as $f): ?>
                <tr>
                    <td><?php echo htmlspecialchars($f->cedula); ?></td>
                    <td class="cell-strong"><?php echo htmlspecialchars(trim($f->apellido . ' ' . $f->nombre)); ?></td>
                    <td><?php echo htmlspecialchars($f->cargo ?? ''); ?></td>
                    <td><?php echo (int)$f->dias_vacaciones; ?></td>
                    <?php if ($cerrado): ?>
                    <td><?php echo htmlspecialchars($f->grado_escala ?? '—'); ?></td>
                    <td><?php echo $fmt($f->sueldo_basico); ?></td>
                    <td><?php echo $fmt($f->prima_profesional); ?></td>
                    <td><?php echo $fmt($f->prima_antiguedad); ?></td>
                    <td><?php echo (int)$f->n_hijos; ?></td>
                    <td><?php echo $fmt($f->prima_por_hijo); ?></td>
                    <td><?php echo $fmt($f->bono_transporte); ?></td>
                    <td><?php echo $fmt($f->sueldo_integral); ?></td>
                    <td><?php echo htmlspecialchars($f->cuenta_bancaria ?? '—'); ?></td>
                    <td><?php echo $fmt($f->monto_cesta_ticket); ?></td>
                    <td><?php echo $fmt($f->alicuotas); ?></td>
                    <td class="cell-strong"><?php echo $f->total_bono_vacacional !== null ? $fmt($f->total_bono_vacacional) : '—'; ?></td>
                    <?php else: ?>
                    <form action="<?php echo URL_ROOT; ?>/nomina/guardarDetalle" method="POST" style="display:contents;">
                        <input type="hidden" name="id_detalle" value="<?php echo $f->id; ?>">
                        <td><input type="text" name="grado_escala" class="sig-input" style="width:80px;" value="<?php echo htmlspecialchars($f->grado_escala ?? ''); ?>"></td>
                        <td><?php echo $fmt($f->sueldo_basico); ?></td>
                        <td><?php echo $fmt($f->prima_profesional); ?></td>
                        <td><?php echo $fmt($f->prima_antiguedad); ?></td>
                        <td><?php echo (int)$f->n_hijos; ?></td>
                        <td><?php echo $fmt($f->prima_por_hijo); ?></td>
                        <td><?php echo $fmt($f->bono_transporte); ?></td>
                        <td><?php echo $fmt($f->sueldo_integral); ?></td>
                        <td><input type="text" name="cuenta_bancaria" class="sig-input" style="width:110px;" value="<?php echo htmlspecialchars($f->cuenta_bancaria ?? ''); ?>"></td>
                        <td><?php echo $fmt($f->monto_cesta_ticket); ?></td>
                        <td><input type="number" step="0.01" name="alicuotas" class="sig-input" style="width:90px;" value="<?php echo $f->alicuotas; ?>"></td>
                        <td><input type="number" step="0.01" name="total_bono_vacacional" class="sig-input" style="width:100px;" value="<?php echo $f->total_bono_vacacional ?? ''; ?>" placeholder="Capturar"></td>
                        <td class="col-actions"><button type="submit" class="btn-sig btn-sig--xs btn-sig--primary"><i class="bi bi-check-lg"></i></button></td>
                    </form>
                    <?php endif; ?>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>
<?php endforeach; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
