<?php
require_once '../app/views/inc/header.php';
$l = $data['liq'];
$esBorrador = ($l->estado === 'Borrador');
$m  = fn($v) => number_format((float)$v, 2, ',', '.');
$m4 = fn($v) => number_format((float)$v, 4, ',', '.');
$d  = fn($v) => rtrim(rtrim(number_format((float)$v, 2, ',', '.'), '0'), ',');
$fecha = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '—';
$diasMes = (float)($data['params']['nomina_dias_mes'] ?? 30) ?: 30;
$nombre = trim($l->nombre . ' ' . $l->apellido);
$mora = json_decode($l->detalle_mora ?: '[]', true) ?: [];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/nomina/liquidaciones" style="text-decoration:none;">Liquidaciones</a>
            · <?php echo htmlspecialchars($l->cedula); ?>
        </div>
        <h1 class="page__title">
            <?php echo htmlspecialchars($nombre); ?>
            <span class="sig-badge <?php echo Liquidacion::ESTADO_BADGES[$l->estado] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($l->estado); ?></span>
            <?php if ($l->numero): ?><span class="sig-badge sig-badge--info"><?php echo htmlspecialchars($l->numero); ?></span><?php endif; ?>
        </h1>
        <p class="page__subtitle">
            <?php echo htmlspecialchars($l->cargo ?: '—'); ?> · <?php echo htmlspecialchars($l->departamento ?: '—'); ?> ·
            <?php echo htmlspecialchars($l->condicion ?: '—'); ?> (<?php echo htmlspecialchars($l->tipo_personal ?: '—'); ?>)
            <br>Del <?php echo $fecha($l->fecha_ingreso); ?> al <?php echo $fecha($l->fecha_egreso); ?>
            · <strong><?php echo (int)$l->anios; ?> años, <?php echo (int)$l->meses; ?> meses y <?php echo (int)$l->dias; ?> días</strong>
            · egreso por <?php echo htmlspecialchars(mb_strtolower($l->motivo_egreso ?: '—')); ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/nomina/imprimirLiquidacion/<?php echo $l->id; ?>" target="_blank" class="btn-sig btn-sig--ghost"><i class="bi bi-printer"></i> Imprimir</a>
        <a href="<?php echo URL_ROOT; ?>/nomina/exportarLiquidacion/<?php echo $l->id; ?>" class="btn-sig btn-sig--ghost"><i class="bi bi-file-earmark-excel"></i> Exportar (3 hojas)</a>
        <?php if ($esBorrador): ?>
            <form action="<?php echo URL_ROOT; ?>/nomina/recalcularLiquidacion/<?php echo $l->id; ?>" method="POST" style="display:inline;">
                <button type="submit" class="btn-sig btn-sig--ghost" title="Toma los parámetros y las tasas vigentes; conserva las correcciones"><i class="bi bi-arrow-repeat"></i> Recalcular</button>
            </form>
            <form action="<?php echo URL_ROOT; ?>/nomina/cerrarLiquidacion/<?php echo $l->id; ?>" method="POST" style="display:inline;"
                  onsubmit="return confirm('<?php echo !empty($l->advertencias) ? 'Hay advertencias sin resolver. ' : ''; ?>Al cerrar, la liquidación recibe su número y ya no se puede modificar. ¿Continuar?');">
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-lock"></i> Cerrar</button>
            </form>
        <?php endif; ?>
        <?php if ($l->estado !== 'Anulada'): ?>
            <button type="button" class="btn-sig btn-sig--ghost" data-bs-toggle="modal" data-bs-target="#modalAnular"><i class="bi bi-x-circle"></i> Anular</button>
        <?php endif; ?>
    </div>
</div>

<?php if ($l->estado === 'Anulada'): ?>
<div class="sig-alert sig-alert--danger anim-slide-up u-mb-sp-4">
    <i class="bi bi-x-octagon"></i>
    <div><strong>Anulada el <?php echo $fecha($l->anulada_at); ?>.</strong> Motivo: <?php echo htmlspecialchars($l->motivo_anulacion ?? '—'); ?></div>
</div>
<?php endif; ?>

<?php if (!empty($l->advertencias) && $esBorrador): ?>
<div class="sig-alert sig-alert--warning anim-slide-up u-mb-sp-4">
    <i class="bi bi-exclamation-triangle"></i>
    <div>
        <strong>Antes de cerrar, revise esto.</strong> El sistema calculó con lo que sabe y avisa de lo que no sabe, en vez de inventarlo.
        <ul class="u-m-8px-0-0 u-pl-18px">
            <?php foreach (explode(' · ', $l->advertencias) as $a): ?>
                <li class="u-mb-4px"><?php echo htmlspecialchars($a); ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<div class="row">
    <!-- Último salario (bloque superior del FORMATO) -->
    <div class="col-lg-5">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-wallet2"></i> Último salario</div></div>
            <div class="sig-card__body u-p-0">
                <div class="sig-table-wrap" data-no-export>
                    <table class="sig-table">
                        <thead><tr><th>Concepto</th><th class="text-end">Mensual</th><th class="text-end">Diario</th></tr></thead>
                        <tbody>
                            <?php foreach ([
                                'Sueldo base' => $l->sueldo_base, 'Prima profesional' => $l->prima_profesional,
                                'Prima responsabilidad' => $l->prima_responsabilidad, 'Prima antigüedad' => $l->prima_antiguedad,
                                'Prima por hijos' => $l->prima_hijos, 'Bono de transporte' => $l->bono_transporte,
                                'Prima FOND' => $l->prima_fond, 'Prima discapacidad' => $l->prima_discapacidad,
                                'Caja de ahorro' => $l->caja_ahorro,
                            ] as $c => $v): if ((float)$v == 0 && in_array($c, ['Prima FOND', 'Prima discapacidad', 'Caja de ahorro', 'Prima responsabilidad'], true)) continue; ?>
                                <tr><td><?php echo $c; ?></td><td class="text-end u-num"><?php echo $m($v); ?></td><td class="text-end u-num"><?php echo $m((float)$v / $diasMes); ?></td></tr>
                            <?php endforeach; ?>
                            <tr class="u-fw-700"><td>Sub-total salario</td><td class="text-end u-num"><?php echo $m($l->subtotal_mensual); ?></td><td class="text-end u-num"><?php echo $m($l->salario_normal_diario); ?></td></tr>
                            <tr><td>Alíc. bono vacacional (<?php echo $d($l->dias_alic_vac); ?> días)</td><td></td><td class="text-end u-num"><?php echo $m($l->alic_vac_diaria); ?></td></tr>
                            <tr><td>Alíc. bonif. fin de año (<?php echo $d($l->dias_alic_fin); ?> días)</td><td></td><td class="text-end u-num"><?php echo $m($l->alic_fin_diaria); ?></td></tr>
                            <tr class="u-fw-700 u-bdt-2px-solid-border-subtle"><td>Salario integral diario</td><td></td><td class="text-end u-num"><?php echo $m($l->salario_integral_diario); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Conceptos de la liquidación -->
    <div class="col-lg-7">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-receipt"></i> Liquidación</div></div>
            <div class="sig-card__body u-p-0">
                <div class="sig-table-wrap" data-no-export>
                    <table class="sig-table">
                        <thead><tr><th>Concepto</th><th class="text-end">Días</th><th class="text-end">Alícuota</th><th class="text-end">Total</th></tr></thead>
                        <tbody>
                            <?php foreach (NominaController::conceptosLiquidacion($l) as $c): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($c['concepto']); ?></td>
                                    <td class="text-end u-num"><?php echo $c['tiempo'] === null ? '' : $d($c['tiempo']); ?></td>
                                    <td class="text-end u-num"><?php echo $c['alicuota'] === null ? '' : $m($c['alicuota']); ?></td>
                                    <td class="text-end u-num"><?php echo $m($c['total']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="u-fw-700"><td colspan="3">Sub-total prestaciones sociales</td><td class="text-end u-num"><?php echo $m($l->subtotal); ?></td></tr>
                            <tr><td colspan="3">Interés de mora<?php echo $l->fecha_pago ? ' (pago el ' . $fecha($l->fecha_pago) . ')' : ' — sin fecha de pago'; ?></td><td class="text-end u-num"><?php echo $m($l->interes_mora); ?></td></tr>
                            <tr class="u-fw-800 u-fs-15px u-bdt-2px-solid-border-subtle"><td colspan="3">TOTAL DE PRESTACIONES SOCIALES</td><td class="text-end u-num"><?php echo $m($l->total); ?></td></tr>
                        </tbody>
                    </table>
                </div>
                <div class="u-p-10px-14px u-bdt-1px-solid-border-subtle u-fs-12_5px u-c-text-secondary">
                    <i class="bi bi-scale"></i> <strong>Art. 142 d LOTTT — se paga la mayor:</strong>
                    garantía depositada <strong><?php echo $m($l->monto_garantia); ?></strong> (<?php echo $d($l->dias_garantia); ?> días, literales a y b)
                    vs. retroactivo <strong><?php echo $m($l->monto_retroactivo); ?></strong> (<?php echo $d($l->dias_retroactivo); ?> días × <?php echo $m($l->salario_integral_diario); ?>, literal c).
                    Aplicó: <span class="sig-badge sig-badge--info"><?php echo $l->antiguedad_aplicada === 'garantia' ? 'garantía' : 'retroactivo'; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($esBorrador): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-pencil-square"></i> Ajustes</div></div>
    <div class="sig-card__body">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarLiquidacion/<?php echo $l->id; ?>" method="POST">
            <p class="u-fs-12_5px u-c-text-secondary u-mb-sp-3">
                Los días de las fracciones los propone el sistema; si Talento Humano llega a otro número
                (por ejemplo, años de bono vacacional sin pagar), escríbalo aquí. Vacío = usar el cálculo del sistema.
            </p>
            <div class="row g-3">
                <div class="col-md-3">
                    <div class="sig-field u-m-0">
                        <label class="sig-field__label" for="lq_pago">Fecha de pago</label>
                        <input type="date" name="fecha_pago" id="lq_pago" class="sig-input" min="<?php echo htmlspecialchars($l->fecha_egreso); ?>" value="<?php echo htmlspecialchars($l->fecha_pago ?? ''); ?>">
                        <small class="u-c-text-tertiary u-fs-11_5px">Para el interés de mora.</small>
                    </div>
                </div>
                <?php foreach ([
                    'dias_vacaciones_manual' => ['Días de vacaciones', $l->dias_vacaciones],
                    'dias_bono_vac_manual'   => ['Días de bono vacacional', $l->dias_bono_vac],
                    'dias_fin_anio_manual'   => ['Días de fin de año', $l->dias_fin_anio],
                ] as $campo => [$lbl, $actual]): ?>
                    <div class="col-md-3">
                        <div class="sig-field u-m-0">
                            <label class="sig-field__label" for="lq_<?php echo $campo; ?>"><?php echo $lbl; ?></label>
                            <input type="number" step="0.01" min="0" name="<?php echo $campo; ?>" id="lq_<?php echo $campo; ?>" class="sig-input"
                                   value="<?php echo $l->{$campo} === null ? '' : htmlspecialchars((string)$l->{$campo}); ?>"
                                   placeholder="Sistema: <?php echo $d($actual); ?>">
                            <?php if ($l->{$campo} !== null): ?><small class="u-fs-11_5px" style="color:var(--warning-700,#b45309);">Corregido a mano</small><?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
                <div class="col-12">
                    <div class="sig-field u-m-0">
                        <label class="sig-field__label" for="lq_obs">Observaciones</label>
                        <input type="text" name="observaciones" id="lq_obs" class="sig-input" maxlength="500" value="<?php echo htmlspecialchars($l->observaciones ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="u-mt-sp-3 u-ta-right">
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar y recalcular</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($mora): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-clock-history"></i> Interés de mora</div></div>
    <div class="sig-card__body u-p-0">
        <div class="sig-table-wrap" data-no-export>
            <table class="sig-table">
                <thead><tr><th>Mes</th><th class="text-end">Tasa mensual %</th><th class="text-end">Interés sobre <?php echo $m($l->subtotal); ?></th></tr></thead>
                <tbody>
                    <?php foreach ($mora as $r): ?>
                        <tr><td><?php echo htmlspecialchars($r['periodo']); ?></td><td class="text-end u-num"><?php echo $m($r['tasa_mensual']); ?></td><td class="text-end u-num"><?php echo $m($r['interes']); ?></td></tr>
                    <?php endforeach; ?>
                    <tr class="u-fw-700"><td colspan="2">Total</td><td class="text-end u-num"><?php echo $m($l->interes_mora); ?></td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Hoja INTERESES: un renglón por mes -->
<div class="sig-card anim-slide-up" id="tabla-intereses" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-table"></i> Cálculo mes a mes (garantía e intereses)</div>
        <?php if ($esBorrador): ?>
            <form action="<?php echo URL_ROOT; ?>/nomina/reconstruirLiquidacion/<?php echo $l->id; ?>" method="POST" style="display:inline;"
                  onsubmit="return confirm('Se volverán a armar todos los meses desde la ficha del trabajador y se PERDERÁN las correcciones hechas a mano. ¿Continuar?');">
                <button type="submit" class="btn-sig btn-sig--ghost btn-sig--sm"><i class="bi bi-arrow-counterclockwise"></i> Reconstruir desde la ficha</button>
            </form>
        <?php endif; ?>
    </div>
    <div class="sig-card__body u-p-0">
        <div class="u-p-10px-14px u-fs-12_5px u-c-text-secondary u-bdb-1px-solid-border-subtle">
            Depósitos <strong><?php echo $m($l->monto_garantia); ?></strong> · intereses <strong><?php echo $m($l->intereses); ?></strong>
            · anticipos <strong><?php echo $m($l->anticipos); ?></strong>.
            <?php if ($esBorrador): ?>Corrija un mes con el lápiz (un sueldo anterior al sistema, un anticipo): la cadena se recalcula entera.<?php endif; ?>
        </div>
        <div class="sig-table-wrap" data-tabla-buscable data-por-pagina="24"
             data-titulo-export="Cálculo de prestaciones sociales — <?php echo htmlspecialchars($nombre); ?>">
            <table class="sig-table" style="font-size:12.5px;">
                <thead>
                    <tr>
                        <th>Mes</th><th class="text-end">Salario base</th><th class="text-end">Primas + bonos</th>
                        <th class="text-end">Total mensual</th><th class="text-end">Integral diario</th>
                        <th class="text-end">Días</th><th class="text-end">Depósito</th><th class="text-end">Anticipo</th>
                        <th class="text-end">Tasa %</th><th class="text-end">Interés</th><th class="text-end">Saldo</th>
                        <?php if ($esBorrador): ?><th class="col-actions">Acciones</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($data['meses'] as $r):
                        $primas = (float)$r->prima_profesional + (float)$r->prima_antiguedad + (float)$r->bono_transporte + (float)$r->bono_hijos; ?>
                        <tr<?php echo (float)$r->salario_base <= 0 ? ' style="background:var(--warning-50,#fffbeb);"' : ''; ?>>
                            <td class="cell-strong">
                                <?php echo date('m/Y', strtotime($r->mes)); ?>
                                <?php if ($r->editado === true || $r->editado === 't' || $r->editado === 1): ?><i class="bi bi-pencil-fill u-c-text-tertiary u-fs-10px" title="Corregido a mano"></i><?php endif; ?>
                            </td>
                            <td class="text-end u-num"><?php echo $m($r->salario_base); ?></td>
                            <td class="text-end u-num"><?php echo $m($primas); ?></td>
                            <td class="text-end u-num"><?php echo $m($r->total_mensual); ?></td>
                            <td class="text-end u-num"><?php echo $m4($r->salario_integral); ?></td>
                            <td class="text-end u-num"><?php echo (float)$r->dias_deposito > 0 ? $d($r->dias_deposito) : ''; ?></td>
                            <td class="text-end u-num"><?php echo (float)$r->monto_deposito != 0 ? $m($r->monto_deposito) : ''; ?></td>
                            <td class="text-end u-num"><?php echo (float)$r->anticipo > 0 ? $m($r->anticipo) : ''; ?></td>
                            <td class="text-end u-num"><?php echo $r->tasa_mensual === null ? '<span class="sig-badge sig-badge--danger">falta</span>' : $m4($r->tasa_mensual); ?></td>
                            <td class="text-end u-num"><?php echo $m($r->interes); ?></td>
                            <td class="text-end u-num" style="font-weight:700;"><?php echo $m($r->saldo); ?></td>
                            <?php if ($esBorrador): ?>
                                <td class="col-actions">
                                    <button type="button" class="row-action row-action--edit"
                                            onclick='editarMesLiq(<?php echo json_encode($r, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'>
                                        <i class="bi bi-pencil"></i> Corregir mes
                                    </button>
                                </td>
                            <?php endif; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php if ($esBorrador): ?>
<!-- Modal: corregir un mes -->
<div class="modal fade" id="modalMesLiq" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/nomina/guardarMesLiquidacion" method="POST" class="modal-content">
            <input type="hidden" name="id_mes" id="ml_id">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMesLiqLabel">Corregir mes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <?php foreach ([
                        'salario_base'      => ['Salario base mensual', '0.01'],
                        'prima_profesional' => ['Prima profesional', '0.01'],
                        'prima_antiguedad'  => ['Prima de antigüedad', '0.01'],
                        'bono_transporte'   => ['Bono de transporte', '0.01'],
                        'bono_hijos'        => ['Bono por hijos', '0.01'],
                        'anticipo'          => ['Anticipo otorgado', '0.01'],
                        'dias_deposito'     => ['Días de prestaciones depositados', '0.01'],
                        'dias_alic_vac'     => ['Días alíc. bono vacacional', '0.01'],
                        'dias_alic_fin'     => ['Días alíc. fin de año', '0.01'],
                    ] as $campo => [$lbl, $paso]): ?>
                        <div class="col-md-4 col-sm-6">
                            <div class="sig-field u-m-0">
                                <label class="sig-field__label" for="ml_<?php echo $campo; ?>"><?php echo $lbl; ?></label>
                                <input type="number" step="<?php echo $paso; ?>" min="0" name="<?php echo $campo; ?>" id="ml_<?php echo $campo; ?>" class="sig-input" required>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <small class="u-c-text-tertiary u-fs-12px" style="display:block; margin-top:8px;">
                    La tasa del BCV no se corrige aquí: se carga por mes en Nómina → Parámetros y vale para todas las liquidaciones.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar y recalcular</button>
            </div>
        </form>
    </div>
</div>
<script>
    function editarMesLiq(r) {
        var p = r.mes.split('-');
        document.getElementById('modalMesLiqLabel').innerText = 'Corregir ' + p[1] + '/' + p[0];
        document.getElementById('ml_id').value = r.id;
        ['salario_base','prima_profesional','prima_antiguedad','bono_transporte','bono_hijos',
         'anticipo','dias_deposito','dias_alic_vac','dias_alic_fin'].forEach(function (c) {
            document.getElementById('ml_' + c).value = r[c];
        });
        new bootstrap.Modal(document.getElementById('modalMesLiq')).show();
    }
</script>
<?php endif; ?>

<?php if ($l->estado !== 'Anulada'): ?>
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/nomina/anularLiquidacion/<?php echo $l->id; ?>" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Anular liquidación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="u-fs-13px u-c-text-secondary">
                    No se borra: queda registrada como anulada<?php echo $l->numero ? ' y su número no se reutiliza' : ''; ?>.
                    El egreso queda libre para liquidarse de nuevo.
                </p>
                <div class="sig-field">
                    <label class="sig-field__label" for="an_motivo">Motivo <span class="req">*</span></label>
                    <textarea name="motivo_anulacion" id="an_motivo" class="sig-input" rows="3" minlength="5" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-x-circle"></i> Anular</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
