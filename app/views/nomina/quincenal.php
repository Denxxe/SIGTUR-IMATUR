<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Talento Humano · Nómina</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Nómina quincenal'; ?></h1>
        <p class="page__subtitle">
            El pago corriente del personal. El sistema calcula primas, deducciones, aportes y
            alícuotas a partir del sueldo base, el grado de instrucción, los años en la
            administración pública y el número de hijos.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/parametros" class="btn-sig btn-sig--ghost"><i class="bi bi-sliders"></i> Parámetros</a>
        <a href="<?php echo URL_ROOT; ?>/nomina/index" class="btn-sig btn-sig--ghost"><i class="bi bi-umbrella"></i> Bono vacacional</a>
        <?php if (!empty($data['meses'])): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalQuincena">
                <i class="bi bi-plus-lg"></i> Generar quincena
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if (empty($data['meses'])): ?>
    <div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-exclamation-triangle"></i>
        <div>
            <strong>Falta cargar los parámetros del mes.</strong>
            La cesta ticket y la tasa del dólar cambian cada mes y entran en las alícuotas y en el
            bono de responsabilidad. Sin ellos la nómina saldría con números que parecen correctos
            pero no lo son, así que el sistema no deja generarla.
            <a href="<?php echo URL_ROOT; ?>/nomina/parametros">Cargar el mes</a>.
        </div>
    </div>
<?php endif; ?>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="12">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Período</th>
                <th>Quincena</th>
                <th>Corte</th>
                <th style="text-align:center;">Trabajadores</th>
                <th style="text-align:right;">Total neto</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['periodos'])): ?>
                <tr><td colspan="7" class="sig-table-empty">Aún no se ha generado ninguna quincena.</td></tr>
            <?php else: foreach ($data['periodos'] as $p): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($p->periodo); ?></td>
                    <td>
                        <span class="sig-badge sig-badge--neutral">
                            <?php echo (int)$p->quincena === 1 ? '1.ª (1-15)' : '2.ª (16-fin)'; ?>
                        </span>
                    </td>
                    <td><?php echo date('d/m/Y', strtotime($p->fecha_corte)); ?></td>
                    <td style="text-align:center;font-weight:700;"><?php echo (int)$p->total_empleados; ?></td>
                    <td style="text-align:right;font-variant-numeric:tabular-nums;font-weight:700;">
                        <?php echo number_format((float)$p->total_neto, 2, ',', '.'); ?>
                    </td>
                    <td>
                        <span class="sig-badge <?php echo $p->estado === 'Cerrado' ? 'sig-badge--neutral' : 'sig-badge--warning'; ?>">
                            <?php echo htmlspecialchars($p->estado); ?>
                        </span>
                        <?php if ((int)$p->con_advertencias > 0): ?>
                            <br><span class="sig-badge sig-badge--danger" title="Empleados con datos sin resolver">
                                <i class="bi bi-exclamation-triangle"></i> <?php echo (int)$p->con_advertencias; ?> con avisos
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/nomina/verQuincena/<?php echo $p->id; ?>" class="row-action row-action--view"><i class="bi bi-eye"></i> Ver</a>
                        <a href="<?php echo URL_ROOT; ?>/nomina/exportarQuincena/<?php echo $p->id; ?>" class="row-action"><i class="bi bi-file-earmark-excel"></i> Exportar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: generar quincena -->
<div class="modal fade" id="modalQuincena" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/nuevaQuincena" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-plus-lg"></i> Generar quincena</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p style="font-size:13px;color:var(--text-secondary);">
                    Calcula y congela la nómina de todo el personal activo. Se puede recalcular
                    mientras esté en Borrador; al cerrarla queda inmutable.
                </p>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="q_periodo">Mes <span class="req">*</span></label>
                    <select name="periodo" id="q_periodo" class="sig-input" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($data['meses'] ?? [] as $m): ?>
                            <option value="<?php echo htmlspecialchars($m->periodo); ?>">
                                <?php echo htmlspecialchars($m->periodo); ?>
                                — cesta <?php echo number_format((float)$m->monto_cesta_ticket, 2, ',', '.'); ?>
                                · tasa <?php echo number_format((float)$m->tasa_dolar, 4, ',', '.'); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small style="color:var(--text-tertiary);font-size:12px;">
                        Solo aparecen los meses con parámetros cargados.
                    </small>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="q_quincena">Quincena <span class="req">*</span></label>
                    <select name="quincena" id="q_quincena" class="sig-input" required>
                        <option value="1">1.ª — días 1 al 15</option>
                        <option value="2">2.ª — día 16 al fin de mes</option>
                    </select>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label" for="q_semanas">Semanas para SSO / LRPPF / aportes</label>
                    <select name="semanas" id="q_semanas" class="sig-input">
                        <option value="4"<?php echo (int)($data['semanas'] ?? 4) === 4 ? ' selected' : ''; ?>>4 semanas</option>
                        <option value="5"<?php echo (int)($data['semanas'] ?? 4) === 5 ? ' selected' : ''; ?>>5 semanas</option>
                    </select>
                    <small style="color:var(--text-tertiary);font-size:12px;">
                        La plantilla del cliente usa 4 en unas hojas y 5 en otras el mismo mes, sin
                        criterio explicado (pregunta pendiente). Queda visible por período en vez de
                        escondido en el código.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-calculator"></i> Calcular</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
