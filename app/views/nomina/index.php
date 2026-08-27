<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Talento Humano</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Nómina — Bono Vacacional'; ?></h1>
        <p class="page__subtitle">Registro y reporte del Bono Vacacional en el formato exacto que se envía a la Alcaldía. Talento Humano captura/verifica los montos; el sistema los organiza y exporta.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/quincenal" class="btn-sig btn-sig--ghost"><i class="bi bi-cash-stack"></i> Nómina quincenal</a>
        <a href="<?php echo URL_ROOT; ?>/nomina/parametros" class="btn-sig btn-sig--ghost"><i class="bi bi-sliders"></i> Parámetros</a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPeriodo">
            <i class="bi bi-plus-lg"></i> Generar período
        </button>
    </div>
</div>

<div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-info-circle"></i>
    <div>
        <strong>Este módulo lleva tres documentos.</strong>
        La <a href="<?php echo URL_ROOT; ?>/nomina/quincenal">nómina quincenal</a> es el pago corriente y
        el sistema la <em>calcula</em>; el bono vacacional de esta pantalla sigue con el total de
        captura manual (v1) hasta migrarlo al motor de cálculo; la liquidación de prestaciones
        sociales está pendiente de un insumo del cliente.
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Fecha de corte</th>
                <th>Empleados</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['periodos'])): ?>
                <tr><td colspan="5" class="sig-table-empty">Aún no se ha generado ningún período.</td></tr>
            <?php else: foreach ($data['periodos'] as $p): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($p->periodo); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($p->fecha_corte)); ?></td>
                    <td><?php echo (int)$p->total_empleados; ?></td>
                    <td>
                        <span class="sig-badge <?php echo $p->estado === 'Cerrado' ? 'sig-badge--neutral' : 'sig-badge--warning'; ?>">
                            <?php echo htmlspecialchars($p->estado); ?>
                        </span>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/nomina/verPeriodo/<?php echo $p->id; ?>" class="row-action row-action--view"><i class="bi bi-eye"></i> Ver</a>
                        <a href="<?php echo URL_ROOT; ?>/nomina/exportarPeriodo/<?php echo $p->id; ?>" class="row-action"><i class="bi bi-file-earmark-excel"></i> Exportar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Generar período -->
<div class="modal fade" id="modalNuevoPeriodo" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/nuevoPeriodo" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Generar período de Bono Vacacional</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:13px;">
                    Toma una foto de todo el personal activo (sueldo/primas vigentes, días de bono según su tipo de personal) a la fecha de corte indicada.
                </p>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Período <span class="req">*</span></label>
                    <input type="month" name="periodo" class="sig-input" required>
                </div>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Fecha de corte</label>
                    <input type="date" name="fecha_corte" class="sig-input" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Generar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
