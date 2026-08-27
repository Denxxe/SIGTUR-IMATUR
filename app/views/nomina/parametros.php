<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Talento Humano · Nómina</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Parámetros del mes'; ?></h1>
        <p class="page__subtitle">
            La cesta ticket y la tasa del dólar cambian cada mes, así que se guardan con su mes.
            Una quincena solo se puede generar si el mes está cargado aquí.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/quincenal" class="btn-sig btn-sig--ghost"><i class="bi bi-cash-stack"></i> Nómina quincenal</a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalMes" onclick="nuevoMes()">
            <i class="bi bi-plus-lg"></i> Cargar mes
        </button>
    </div>
</div>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-calendar-month"></i> Cesta ticket y tasa del dólar por mes</div></div>
    <div class="sig-card__body" style="padding:0;">
        <div class="sig-table-wrap" data-tabla-buscable data-por-pagina="12">
            <table class="sig-table">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th style="text-align:right;">Cesta ticket</th>
                        <th style="text-align:right;">Tasa del dólar</th>
                        <th>Observaciones</th>
                        <th class="col-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['meses'])): ?>
                        <tr><td colspan="5" class="sig-table-empty">
                            Ningún mes cargado. Sin la cesta ticket y la tasa del dólar del mes no se puede generar la nómina.
                        </td></tr>
                    <?php else: foreach ($data['meses'] as $m): ?>
                        <tr>
                            <td class="cell-strong"><?php echo htmlspecialchars($m->periodo); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo number_format((float)$m->monto_cesta_ticket, 2, ',', '.'); ?></td>
                            <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo number_format((float)$m->tasa_dolar, 4, ',', '.'); ?></td>
                            <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($m->observaciones ?? '—'); ?></td>
                            <td class="col-actions">
                                <button class="row-action row-action--edit"
                                        onclick='editarMes(<?php echo json_encode($m, JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                    <i class="bi bi-pencil"></i> Editar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-mortarboard"></i> Prima de profesionalización</div>
                <div style="font-size:12px;color:var(--text-tertiary);">% sobre el sueldo base quincenal</div>
            </div>
            <div class="sig-card__body" style="padding:0;">
                <div class="sig-table-wrap" data-no-export>
                    <table class="sig-table">
                        <thead><tr><th>Código</th><th>Grado de instrucción</th><th style="text-align:right;">%</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['grados'] ?? [] as $cod => $g): ?>
                                <tr>
                                    <td class="cell-strong"><?php echo htmlspecialchars($cod); ?></td>
                                    <td><?php echo htmlspecialchars($g['nombre']); ?></td>
                                    <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo number_format($g['porcentaje'], 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-graph-up-arrow"></i> Prima de antigüedad</div>
                <div style="font-size:12px;color:var(--text-tertiary);">Años en la administración pública</div>
            </div>
            <div class="sig-card__body" style="padding:0;">
                <div class="sig-table-wrap" data-no-export style="max-height:340px;overflow-y:auto;">
                    <table class="sig-table">
                        <thead><tr><th>Años</th><th style="text-align:right;">%</th></tr></thead>
                        <tbody>
                            <?php foreach ($data['escala'] ?? [] as $t): ?>
                                <tr>
                                    <td class="cell-strong">
                                        <?php echo (int)$t->anios; ?>
                                        <?php if ($t->es_tope): ?><span class="sig-badge sig-badge--info">y más — tope</span><?php endif; ?>
                                    </td>
                                    <td style="text-align:right;font-variant-numeric:tabular-nums;"><?php echo number_format((float)$t->porcentaje, 2, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sig-card anim-slide-up">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-sliders"></i> Montos y porcentajes del cálculo</div></div>
    <div class="sig-card__body">
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:var(--sp-3);">
            Se editan en <a href="<?php echo URL_ROOT; ?>/config/index">Configuración</a>. Ninguno está
            escrito en el código: son parámetros de contratación colectiva.
        </p>
        <div class="row">
            <?php
            $etiquetas = [
                'nomina_bono_transporte_mensual' => 'Bono de transporte (mensual)',
                'nomina_monto_por_hijo'          => 'Prima por hijo (quincenal)',
                'nomina_becas_por_hijo'          => 'Becas por hijo',
                'nomina_semanas_default'         => 'Semanas (SSO/LRPPF/aportes)',
                'nomina_pct_sso_trabajador'      => 'SSO trabajador %',
                'nomina_pct_faov_trabajador'     => 'FAOV trabajador %',
                'nomina_pct_lrppf_trabajador'    => 'LRPPF trabajador %',
                'nomina_pct_sso_patronal'        => 'SSO patronal %',
                'nomina_pct_faov_patronal'       => 'FAOV patronal %',
                'nomina_pct_rpe_patronal'        => 'RPE patronal %',
                'nomina_dias_bono_vac_base'      => 'Días base bono vacacional',
                'nomina_dias_bono_fin_anio'      => 'Días bono fin de año',
                'nomina_dias_base_anio'          => 'Días base del año',
            ];
            foreach ($etiquetas as $clave => $label):
                $val = $data['escalares'][$clave] ?? null;
            ?>
                <div class="col-md-3 col-sm-6" style="margin-bottom:var(--sp-3);">
                    <div style="font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--text-tertiary);"><?php echo $label; ?></div>
                    <div style="font-weight:700;font-variant-numeric:tabular-nums;"><?php echo $val === null ? '—' : rtrim(rtrim(number_format((float)$val, 2, ',', '.'), '0'), ','); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <div class="sig-alert sig-alert--warning" style="margin-top:var(--sp-3);">
            <i class="bi bi-exclamation-triangle"></i>
            <div>
                <strong>Dos valores están pendientes de confirmación del cliente.</strong>
                Los <em>días base del bono vacacional</em> (la plantilla de nómina usa 75 en todas las hojas,
                la configuración del bono vacacional tiene 85 y 45) y el criterio de las
                <em>semanas</em> (la plantilla usa 4 en unas hojas y 5 en otras el mismo mes).
                Están como parámetros, así que el cálculo funciona, pero el número final no es
                definitivo hasta que se aclaren.
            </div>
        </div>
    </div>
</div>

<!-- Modal: cargar/editar mes -->
<div class="modal fade" id="modalMes" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarParametros" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMesLabel">Parámetros del mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="pm_periodo">Mes <span class="req">*</span></label>
                    <input type="month" name="periodo" id="pm_periodo" class="sig-input" required>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="pm_cesta">Monto de cesta ticket <span class="req">*</span></label>
                    <input type="number" step="0.01" min="0" name="monto_cesta_ticket" id="pm_cesta" class="sig-input" required>
                    <small style="color:var(--text-tertiary);font-size:12px;">Lo publica la UNAPRE; cambia todos los meses.</small>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label" for="pm_tasa">Tasa del dólar <span class="req">*</span></label>
                    <input type="number" step="0.0001" min="0" name="tasa_dolar" id="pm_tasa" class="sig-input" required>
                    <small style="color:var(--text-tertiary);font-size:12px;">Con ella se paga el bono de responsabilidad, que se pacta en divisas.</small>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label" for="pm_obs">Observaciones</label>
                    <input type="text" name="observaciones" id="pm_obs" class="sig-input" maxlength="255" placeholder="Ej: gaceta o fuente del monto">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoMes() {
        document.getElementById('modalMesLabel').innerText = 'Cargar parámetros del mes';
        document.querySelector('#modalMes form').reset();
        document.getElementById('pm_periodo').readOnly = false;
    }
    function editarMes(m) {
        document.getElementById('modalMesLabel').innerText = 'Editar ' + m.periodo;
        document.getElementById('pm_periodo').value = m.periodo;
        document.getElementById('pm_periodo').readOnly = true;   // el mes es la clave
        document.getElementById('pm_cesta').value = m.monto_cesta_ticket;
        document.getElementById('pm_tasa').value  = m.tasa_dolar;
        document.getElementById('pm_obs').value   = m.observaciones || '';
        new bootstrap.Modal(document.getElementById('modalMes')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
