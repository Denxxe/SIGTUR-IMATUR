<?php require_once '../app/views/inc/header.php';
$m = fn($v) => number_format((float)$v, 2, ',', '.');
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Talento Humano · Nómina</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Liquidación de Prestaciones Sociales'; ?></h1>
        <p class="page__subtitle">
            Al egresar un trabajador, el sistema arma su liquidación mes a mes: garantía de prestaciones,
            intereses del BCV, salario integral, fracciones de vacaciones, bono vacacional y aguinaldo, y
            el interés de mora si el pago se atrasa. Se paga lo mayor entre la garantía y el retroactivo
            (art. 142 LOTTT).
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/quincenal" class="btn-sig btn-sig--ghost"><i class="bi bi-cash-stack"></i> Nómina quincenal</a>
        <a href="<?php echo URL_ROOT; ?>/nomina/index" class="btn-sig btn-sig--ghost"><i class="bi bi-umbrella"></i> Bono vacacional</a>
        <a href="<?php echo URL_ROOT; ?>/nomina/parametros" class="btn-sig btn-sig--ghost"><i class="bi bi-sliders"></i> Parámetros</a>
    </div>
</div>

<?php if (!empty($data['pendientes'])): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-hourglass-split"></i> Egresados sin liquidación</div>
        <span class="sig-badge sig-badge--warning"><?php echo count($data['pendientes']); ?></span>
    </div>
    <div class="sig-card__body" style="padding:0;">
        <div class="sig-table-wrap" data-no-export>
            <table class="sig-table">
                <thead>
                    <tr><th>Cédula</th><th>Trabajador</th><th>Cargo</th><th>Ingreso</th><th>Egreso</th><th>Motivo</th><th class="col-actions">Acciones</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($data['pendientes'] as $p): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($p->cedula); ?></td>
                            <td class="cell-strong"><?php echo htmlspecialchars(trim($p->apellido . ' ' . $p->nombre)); ?></td>
                            <td><?php echo htmlspecialchars($p->cargo ?? '—'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($p->fecha_ingreso)); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($p->fecha_egreso)); ?></td>
                            <td><?php echo htmlspecialchars($p->motivo_egreso ?? '—'); ?></td>
                            <td class="col-actions">
                                <form action="<?php echo URL_ROOT; ?>/nomina/nuevaLiquidacion" method="POST" style="display:inline;">
                                    <input type="hidden" name="id_empleado" value="<?php echo (int)$p->id; ?>">
                                    <button type="submit" class="btn-sig btn-sig--primary btn-sig--sm"><i class="bi bi-calculator"></i> Liquidar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="12" data-titulo-export="Liquidaciones de Prestaciones Sociales">
    <table class="sig-table">
        <thead>
            <tr>
                <th>N.º</th>
                <th>Cédula</th>
                <th>Trabajador</th>
                <th>Egreso</th>
                <th>Tiempo</th>
                <th class="text-end">Total</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['liquidaciones'])): ?>
                <tr><td colspan="8" class="sig-table-empty">
                    Aún no hay liquidaciones. Se crean desde aquí cuando un trabajador egresa
                    (el egreso se procesa en su expediente).
                </td></tr>
            <?php else: foreach ($data['liquidaciones'] as $l): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($l->numero ?: '—'); ?></td>
                    <td><?php echo htmlspecialchars($l->cedula); ?></td>
                    <td><?php echo htmlspecialchars(trim($l->apellido . ' ' . $l->nombre)); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($l->fecha_egreso)); ?></td>
                    <td><?php echo (int)$l->anios; ?>a <?php echo (int)$l->meses; ?>m <?php echo (int)$l->dias; ?>d</td>
                    <td class="text-end u-num" style="font-weight:700;"><?php echo $m($l->total); ?></td>
                    <td>
                        <span class="sig-badge <?php echo Liquidacion::ESTADO_BADGES[$l->estado] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($l->estado); ?></span>
                        <?php if ($l->estado === 'Borrador' && !empty($l->advertencias)): ?>
                            <span class="sig-badge sig-badge--danger" title="<?php echo htmlspecialchars($l->advertencias); ?>"><i class="bi bi-exclamation-triangle"></i> Con avisos</span>
                        <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/nomina/verLiquidacion/<?php echo $l->id; ?>" class="row-action row-action--view"><i class="bi bi-eye"></i> Ver</a>
                        <a href="<?php echo URL_ROOT; ?>/nomina/imprimirLiquidacion/<?php echo $l->id; ?>" target="_blank" class="row-action"><i class="bi bi-printer"></i> Imprimir</a>
                        <a href="<?php echo URL_ROOT; ?>/nomina/exportarLiquidacion/<?php echo $l->id; ?>" class="row-action"><i class="bi bi-file-earmark-excel"></i> Exportar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
