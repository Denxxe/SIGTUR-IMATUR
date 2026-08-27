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
        <?php if (!empty($data['meses'])): ?>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalNuevoPeriodo">
            <i class="bi bi-plus-lg"></i> Generar período
        </button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($data['meses'])): ?>
    <div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-exclamation-triangle"></i>
        <div>
            <strong>Falta cargar los parámetros del mes.</strong>
            La cesta ticket entra en el sueldo normal diario y por tanto en la alícuota, así que sin
            ella el período saldría con números que parecen correctos y no lo son.
            <a href="<?php echo URL_ROOT; ?>/nomina/parametros">Cargar el mes</a>.
        </div>
    </div>
<?php endif; ?>

<div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-info-circle"></i>
    <div>
        <strong>Este módulo lleva tres documentos.</strong>
        La <a href="<?php echo URL_ROOT; ?>/nomina/quincenal">nómina quincenal</a> es el pago corriente.
        El bono vacacional de esta pantalla <strong>ya calcula las primas, el sueldo diario y la
        alícuota</strong> con el mismo motor; solo el <strong>total</strong> sigue confirmándose a mano,
        porque su fórmula no está en ninguna fuente del cliente. La liquidación de prestaciones
        sociales espera un insumo del cliente.
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Fecha de corte</th>
                <th style="text-align:center;">Empleados</th>
                <th style="text-align:right;">Total calculado</th>
                <th style="text-align:right;">Total confirmado</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['periodos'])): ?>
                <tr><td colspan="7" class="sig-table-empty">Aún no se ha generado ningún período.</td></tr>
            <?php else: foreach ($data['periodos'] as $p): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($p->periodo); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($p->fecha_corte)); ?></td>
                    <td style="text-align:center;font-weight:700;"><?php echo (int)$p->total_empleados; ?></td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;color:var(--text-secondary);">
                        <?php echo number_format((float)($p->total_calculado ?? 0), 2, ',', '.'); ?>
                    </td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;">
                        <?php echo number_format((float)($p->total_confirmado ?? 0), 2, ',', '.'); ?>
                    </td>
                    <td>
                        <span class="sig-badge <?php echo $p->estado === 'Cerrado' ? 'sig-badge--neutral' : 'sig-badge--warning'; ?>">
                            <?php echo htmlspecialchars($p->estado); ?>
                        </span>
                        <?php if ((int)($p->sin_confirmar ?? 0) > 0): ?>
                            <br><span class="sig-badge sig-badge--info" title="Totales calculados que aún nadie confirmó">
                                <?php echo (int)$p->sin_confirmar; ?> sin confirmar
                            </span>
                        <?php endif; ?>
                        <?php if ((int)($p->con_advertencias ?? 0) > 0): ?>
                            <br><span class="sig-badge sig-badge--danger" title="Empleados con datos sin resolver">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo (int)$p->con_advertencias; ?> con avisos
                            </span>
                        <?php endif; ?>
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
                    Calcula y congela el bono vacacional de todo el personal activo a la fecha de corte:
                    primas derivadas, sueldo diario, alícuota y días según su tipo de personal.
                </p>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="bv_periodo">Período <span class="req">*</span></label>
                    <select name="periodo" id="bv_periodo" class="sig-input" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($data['meses'] ?? [] as $m): ?>
                            <option value="<?php echo htmlspecialchars($m->periodo); ?>">
                                <?php echo htmlspecialchars($m->periodo); ?>
                                — cesta <?php echo number_format((float)$m->monto_cesta_ticket, 2, ',', '.'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--text-tertiary);font-size:12px;">
                        Solo los meses con parámetros cargados.
                    </small>
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
