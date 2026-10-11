<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Hoja de vida del bien (B-36) — Fase 3.
 *
 * Reúne en una sola pantalla todo lo que el cliente pidió poder consultar:
 * "desde que se compró, cada movimiento, cada reparación, hasta la baja".
 */
$b    = $data['bien'];
// Sin «Bienes: registrar y modificar» (InventarioEscritura) la hoja de vida es de consulta.
$puedeEscribir = InventarioController::puedeEscribir();
$ecls = Inventario::ESTATUS_BADGES[$b->estatus ?? ''] ?? 'sig-badge--neutral';
$ccls = Inventario::CONDICION_BADGES[$b->condicion ?? ''] ?? 'sig-badge--neutral';
$cons = $data['consolidado'] ?? null;
$fmt  = fn($v) => $v !== null && $v !== '' ? htmlspecialchars((string)$v) : '<span class="u-c-text-tertiary">—</span>';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Hoja de vida del bien</div>
        <h1 class="page__title"><?php echo htmlspecialchars($b->nombre); ?></h1>
        <p class="page__subtitle">
            <?php if (!empty($b->codigo_bn)): ?>
                Código oficial <strong class="u-ff-font-mono"><?php echo htmlspecialchars($b->codigo_bn); ?></strong>
                <?php if (($b->codificado_por ?? '') === CodificacionBienes::QUIEN_IMATUR): ?>
                    · <i class="bi bi-building u-c-brand-600"></i> codificado por IMATUR
                    <?php if (!empty($b->fecha_codificacion)): ?> el <?php echo htmlspecialchars($b->fecha_codificacion); ?><?php endif; ?>
                <?php endif; ?>
                <?php if (!empty($b->verificado_alcaldia)): ?>
                    · <i class="bi bi-patch-check-fill u-c-success-600"></i> reconocido por la Alcaldía
                    <?php if ($b->fecha_verificacion): ?> el <?php echo htmlspecialchars($b->fecha_verificacion); ?><?php endif; ?>
                <?php endif; ?>
            <?php else: ?>
                Sin código todavía.
            <?php endif; ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="row g-4 anim-slide-up">
    <!-- ── Ficha ─────────────────────────────────────────────────── -->
    <div class="col-lg-8">
        <div class="sig-card">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-box-seam"></i> Ficha del bien</div></div>
            <div class="sig-card__body u-p-sp-4">
                <div class="row g-3">
                    <div class="col-md-4"><small class="u-c-text-tertiary">Estatus</small><br><span class="sig-badge <?php echo $ecls; ?>"><?php echo htmlspecialchars($b->estatus); ?></span></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Condición</small><br><span class="sig-badge <?php echo $ccls; ?>"><?php echo htmlspecialchars($b->condicion ?? '—'); ?></span></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Categoría</small><br><?php echo $fmt($b->categoria); ?></div>

                    <div class="col-md-4"><small class="u-c-text-tertiary">Ubicación</small><br><?php echo $fmt($b->ubicacion); ?><?php if (!empty($b->sede)): ?><br><small class="u-c-text-tertiary"><?php echo htmlspecialchars($b->sede); ?></small><?php endif; ?></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Departamento</small><br><?php echo $fmt($b->departamento); ?></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Responsable</small><br><?php echo $fmt($b->responsable); ?></div>

                    <div class="col-md-4"><small class="u-c-text-tertiary">Marca / Modelo</small><br><?php echo $fmt(trim(($b->marca ?: '') . ' ' . ($b->modelo ?: ''))); ?></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Serial</small><br><?php echo $fmt($b->serial); ?></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Origen</small><br>
                        <?php echo $fmt($b->origen); ?>
                        <?php if (!empty($b->donante)): ?><br><small class="u-c-text-tertiary">Donado por <?php echo htmlspecialchars($b->donante); ?></small><?php endif; ?>
                    </div>

                    <div class="col-md-4"><small class="u-c-text-tertiary">Costo</small><br><?php echo $b->costo_adquisicion !== null ? 'Bs. ' . number_format((float)$b->costo_adquisicion, 2, ',', '.') : $fmt(null); ?></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Adquirido el</small><br><?php echo $fmt($b->fecha_adquisicion); ?></div>
                    <div class="col-md-4"><small class="u-c-text-tertiary">Proveedor</small><br><?php echo $fmt($b->proveedor); ?></div>

                    <?php if (!empty($b->tiene_garantia)): ?>
                        <div class="col-md-4"><small class="u-c-text-tertiary">Garantía vence</small><br>
                            <?php
                            $vence = $b->garantia_vence;
                            $vencida = $vence && $vence < date('Y-m-d');
                            ?>
                            <span class="sig-badge <?php echo $vencida ? 'sig-badge--danger' : 'sig-badge--success'; ?>">
                                <?php echo htmlspecialchars($vence ?: '—'); ?><?php echo $vencida ? ' (vencida)' : ''; ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($b->descripcion)): ?>
                        <div class="col-12"><small class="u-c-text-tertiary">Descripción</small><br><?php echo nl2br(htmlspecialchars($b->descripcion)); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($b->observaciones)): ?>
                        <div class="col-12"><small class="u-c-text-tertiary">Observaciones</small><br><?php echo nl2br(htmlspecialchars($b->observaciones)); ?></div>
                    <?php endif; ?>
                </div>

                <?php if ($cons): ?>
                    <div class="sig-alert sig-alert--info u-mt-sp-4">
                        <i class="bi bi-file-earmark-check"></i>
                        Codificado con el <strong>BM-1 recibido el <?php echo htmlspecialchars($cons->fecha_recepcion); ?></strong>
                        <?php if ($cons->referencia): ?> (<?php echo htmlspecialchars($cons->referencia); ?>)<?php endif; ?>.
                        <?php if ($cons->archivo_url): ?>
                            <a href="<?php echo URL_ROOT; ?>/descarga/bm1/<?php echo (int)$cons->id; ?>" target="_blank">Ver formulario</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── Foto ──────────────────────────────────────────────────── -->
    <div class="col-lg-4">
        <div class="sig-card">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-camera"></i> Foto del bien</div></div>
            <div class="sig-card__body u-p-sp-4 u-ta-center">
                <?php if (!empty($b->foto_url)): ?>
                    <img src="<?php echo URL_ROOT; ?>/descarga/fotoBien/<?php echo (int)$b->id; ?>" alt="Foto del bien"
                         style="max-width:100%;max-height:200px;border-radius:var(--radius-md);border:1px solid var(--border-subtle);">
                <?php else: ?>
                    <div class="u-p-sp-5 u-c-text-tertiary">
                        <i class="bi bi-image" style="font-size:2.5rem;"></i><br>Sin foto
                    </div>
                <?php endif; ?>
                <?php if ($puedeEscribir): ?>
                <form action="<?php echo URL_ROOT; ?>/inventario/subirFoto" method="POST" enctype="multipart/form-data" style="margin-top:var(--sp-3);">
                    <input type="hidden" name="id_inventario" value="<?php echo (int)$b->id; ?>">
                    <input type="file" name="foto" class="sig-input u-mb-sp-2" accept=".jpg,.jpeg,.png" required aria-label="Foto del bien">
                    <button type="submit" class="btn-sig btn-sig--ghost u-w-100pct"><i class="bi bi-upload"></i> Subir foto</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if (($b->origen ?? '') === 'Donación'): ?>
<!-- ── Donación: datos del acto y su documento (mig. 075) ────────── -->
<?php
// El documento de donación es el título del bien cuando no hay factura: sin
// donante identificado no puede redactarse, así que los datos se capturan aquí.
$listoDonacion = !empty($b->donante) && !empty($b->donante_cedula);
?>
<div class="sig-card anim-slide-up" style="margin-top:var(--sp-4);border-left:3px solid var(--brand-500,#3b82f6);">
    <div class="sig-card__head u-jc-space-between u-ai-center" style="display:flex;">
        <div class="sig-card__title"><i class="bi bi-gift"></i> Donación — datos del acto</div>
        <?php if ($listoDonacion): ?>
            <a href="<?php echo URL_ROOT; ?>/inventario/donacion/<?php echo (int)$b->id; ?>"
               target="_blank" class="btn-sig btn-sig--primary">
                <i class="bi bi-printer"></i> Documento de donación
            </a>
        <?php else: ?>
            <span class="sig-badge sig-badge--warning">Faltan nombre y cédula del donante</span>
        <?php endif; ?>
    </div>
    <div class="sig-card__body u-p-sp-4">
        <form action="<?php echo URL_ROOT; ?>/inventario/guardarDonacion" method="POST">
            <input type="hidden" name="id_inventario" value="<?php echo (int)$b->id; ?>">
            <div class="u-gap-sp-3" style="display:grid; grid-template-columns:repeat(auto-fit,minmax(220px,1fr));">
                <div class="sig-field">
                    <label for="invedeta_donante" class="sig-field__label">Donante <span class="req">*</span></label>
                    <input id="invedeta_donante" type="text" name="donante" class="sig-input" required
                           value="<?php echo htmlspecialchars((string)$b->donante); ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
                <div class="sig-field">
                    <label for="invedeta_donante_cedula" class="sig-field__label">Cédula</label>
                    <input id="invedeta_donante_cedula" type="text" name="donante_cedula" class="sig-input" placeholder="V-00000000"
                           value="<?php echo htmlspecialchars((string)$b->donante_cedula); ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
                <div class="sig-field">
                    <label for="invedeta_donante_estado_civil" class="sig-field__label">Estado civil</label>
                    <input id="invedeta_donante_estado_civil" type="text" name="donante_estado_civil" class="sig-input" placeholder="soltero, casado…"
                           value="<?php echo htmlspecialchars((string)$b->donante_estado_civil); ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
                <div class="sig-field">
                    <label for="invedeta_donante_domicilio" class="sig-field__label">Domicilio</label>
                    <input id="invedeta_donante_domicilio" type="text" name="donante_domicilio" class="sig-input"
                           value="<?php echo htmlspecialchars((string)$b->donante_domicilio); ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
                <div class="sig-field">
                    <label for="invedeta_valor_bs" class="sig-field__label">Valor estimado (Bs)</label>
                    <input id="invedeta_valor_bs" type="number" step="0.01" min="0" name="valor_bs" class="sig-input"
                           value="<?php echo $b->costo_adquisicion !== null ? htmlspecialchars((string)$b->costo_adquisicion) : ''; ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
                <div class="sig-field">
                    <label for="invedeta_valor_usd" class="sig-field__label">Equivalente (USD)</label>
                    <input id="invedeta_valor_usd" type="number" step="0.01" min="0" name="valor_usd" class="sig-input"
                           value="<?php echo $b->donacion_valor_usd !== null ? htmlspecialchars((string)$b->donacion_valor_usd) : ''; ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
                <div class="sig-field">
                    <label for="invedeta_donacion_fecha" class="sig-field__label">Fecha del acto</label>
                    <input id="invedeta_donacion_fecha" type="date" name="donacion_fecha" class="sig-input" max="<?php echo date('Y-m-d'); ?>"
                           value="<?php echo htmlspecialchars((string)$b->donacion_fecha); ?>"
                           <?php echo $puedeEscribir ? '' : 'disabled'; ?>>
                </div>
            </div>
            <div class="sig-field u-mt-sp-3">
                <label for="invedeta_donacion_procedencia" class="sig-field__label">
                    Procedencia del bien
                    <small class="u-c-text-tertiary u-fw-400">
                        — cómo lo obtuvo el donante y por qué no hay factura. Va literal en el documento.
                    </small>
                </label>
                <textarea id="invedeta_donacion_procedencia" name="donacion_procedencia" class="sig-input" rows="2"
                          placeholder="Ej.: Dicho bien me pertenece por haberlo obtenido como premio de un Bingo en el que participé, motivo por el cual no poseo factura de compra."
                          <?php echo $puedeEscribir ? '' : 'disabled'; ?>><?php echo htmlspecialchars((string)$b->donacion_procedencia); ?></textarea>
            </div>
            <?php if ($puedeEscribir): ?>
            <div class="u-mt-sp-3">
                <button type="submit" class="btn-sig btn-sig--primary">
                    <i class="bi bi-check-lg"></i> Guardar datos de la donación
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ── Documentos de respaldo ────────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-top:var(--sp-4);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-paperclip"></i> Documentos de respaldo</div></div>
    <div class="sig-card__body u-p-sp-4">
        <?php if ($puedeEscribir): ?>
        <form action="<?php echo URL_ROOT; ?>/inventario/subirDocumento" method="POST" enctype="multipart/form-data"
              style="display:flex;gap:var(--sp-2);align-items:flex-end;flex-wrap:wrap;margin-bottom:var(--sp-4);">
            <input type="hidden" name="id_inventario" value="<?php echo (int)$b->id; ?>">
            <div class="sig-field u-m-0 u-minw-240px">
                <label class="sig-field__label" for="doc_tipo">Tipo de documento</label>
                <select name="tipo_documento" id="doc_tipo" class="sig-select" required>
                    <?php foreach (InventarioDocumento::TIPOS as $k => $v): ?>
                        <option value="<?php echo $k; ?>"><?php echo htmlspecialchars($v[0]); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="sig-field u-m-0" style="min-width:220px;">
                <label class="sig-field__label" for="doc_file">Archivo (PDF o imagen, máx. 5 MB)</label>
                <input type="file" name="documento" id="doc_file" class="sig-input" accept=".pdf,.jpg,.jpeg,.png" required>
            </div>
            <div class="sig-field u-m-0 u-flex-1" style="min-width:180px;">
                <label class="sig-field__label" for="doc_obs">Observaciones</label>
                <input type="text" name="observaciones" id="doc_obs" class="sig-input">
            </div>
            <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-upload"></i> Adjuntar</button>
        </form>
        <?php endif; ?>

        <?php if (empty($data['documentos'])): ?>
            <p class="u-c-text-tertiary u-m-0">Este bien todavía no tiene documentos adjuntos.</p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr><th>Tipo</th><th>Archivo</th><th>Observaciones</th><th>Subido</th><th class="col-actions">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['documentos'] as $d): ?>
                        <tr>
                            <td><i class="bi <?php echo InventarioDocumento::iconoTipo($d->tipo_documento); ?>"></i>
                                <?php echo htmlspecialchars(InventarioDocumento::labelTipo($d->tipo_documento)); ?></td>
                            <td class="u-fs-12_5px"><?php echo htmlspecialchars($d->nombre_original ?: $d->archivo_url); ?></td>
                            <td class="u-fs-12_5px u-c-text-secondary"><?php echo htmlspecialchars($d->observaciones ?: '—'); ?></td>
                            <td class="u-fs-12px u-c-text-tertiary"><?php echo htmlspecialchars(substr((string)$d->created_at, 0, 10)); ?></td>
                            <td class="col-actions">
                                <a href="<?php echo URL_ROOT; ?>/descarga/bien/<?php echo (int)$d->id; ?>" target="_blank" class="row-action row-action--view"><i class="bi bi-eye"></i> Ver</a>
                                <?php if ($puedeEscribir): ?>
                                <a href="<?php echo URL_ROOT; ?>/inventario/eliminarDocumento/<?php echo (int)$d->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Mantenimientos ────────────────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-top:var(--sp-4);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-tools"></i> Mantenimientos</div></div>
    <div class="sig-card__body u-p-sp-4">
        <?php if (empty($data['mantenimientos'])): ?>
            <p class="u-c-text-tertiary u-m-0">Este bien no ha pasado por mantenimiento.</p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr><th>Salida</th><th>Retorno</th><th>Encargado / Taller</th><th>Falla</th><th>Trabajo</th><th>Costo</th><th>Resultado</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['mantenimientos'] as $m): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($m->fecha_salida); ?></td>
                            <td><?php echo $m->fecha_retorno ? htmlspecialchars($m->fecha_retorno) : '<span class="sig-badge sig-badge--warning">En curso</span>'; ?></td>
                            <td class="u-fs-12_5px"><?php echo htmlspecialchars($m->encargado ?: ($m->proveedor_externo ?: '—')); ?></td>
                            <td class="u-fs-12_5px"><?php echo htmlspecialchars($m->descripcion_falla ?: '—'); ?></td>
                            <td class="u-fs-12_5px"><?php echo htmlspecialchars($m->trabajo_realizado ?: '—'); ?></td>
                            <td><?php echo $m->costo !== null ? 'Bs. ' . number_format((float)$m->costo, 2, ',', '.') : '—'; ?></td>
                            <td><?php if ($m->resultado): ?><span class="sig-badge <?php echo Mantenimiento::RESULTADO_BADGES[$m->resultado] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($m->resultado); ?></span><?php else: ?>—<?php endif; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Movimientos ───────────────────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-top:var(--sp-4);margin-bottom:var(--sp-5);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-clock-history"></i> Historial de movimientos</div></div>
    <div class="sig-card__body u-p-sp-4">
        <?php if (empty($data['movimientos'])): ?>
            <p class="u-c-text-tertiary u-m-0">Sin movimientos registrados.</p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr><th>Fecha</th><th>Movimiento</th><th>Recorrido</th><th>Responsable</th><th>Autorizó</th><th>Observaciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['movimientos'] as $a): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($a->fecha); ?></td>
                            <td><span class="sig-badge <?php echo ActividadInventario::TIPO_BADGES[$a->tipo_movimiento] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($a->tipo_movimiento); ?></span></td>
                            <td class="u-fs-12_5px">
                                <?php if (!empty($a->ubicacion_origen) || !empty($a->ubicacion_destino)): ?>
                                    <?php echo htmlspecialchars($a->ubicacion_origen ?: '—'); ?>
                                    <i class="bi bi-arrow-right u-c-text-tertiary"></i>
                                    <?php echo htmlspecialchars($a->ubicacion_destino ?: '—'); ?>
                                <?php else: ?>—<?php endif; ?>
                            </td>
                            <td class="u-fs-12_5px"><?php echo htmlspecialchars(trim(($a->emp_nombre ?? '') . ' ' . ($a->emp_apellido ?? '')) ?: '—'); ?></td>
                            <td class="u-fs-12_5px"><?php echo htmlspecialchars($a->autorizador ?: '—'); ?></td>
                            <td class="u-fs-12_5px u-c-text-secondary"><?php echo htmlspecialchars($a->descripcion ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
