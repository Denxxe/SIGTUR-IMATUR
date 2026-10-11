<?php require_once '../app/views/inc/header.php';
/** Ficha de un oficio de permiso — T-H (mig. 081). */
$p    = $data['permiso'];
$sals = $data['salidas'] ?? [];
$fmt  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$v    = fn($x) => htmlspecialchars((string)$x);
$respondido = in_array($p->estado, PermisoRuta::ESTADOS_RESPONDIDOS, true);
$anulado    = $p->estado === PermisoRuta::EST_ANULADO;
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/permisos" style="color:inherit;text-decoration:none;">Permisos de acceso</a>
            · <?php echo $v($p->numero); ?>
        </div>
        <h1 class="page__title"><?php echo $v($p->institucion); ?></h1>
        <p class="page__subtitle u-ai-center u-gap-sp-2 u-fwr-wrap" style="display:flex;">
            <span class="sig-badge sig-badge--sm <?php echo PermisoRuta::ESTADO_BADGES[$p->estado] ?? 'sig-badge--neutral'; ?>">
                <?php echo $v($p->estado); ?>
            </span>
            · Semana del <?php echo $fmt($p->semana_desde); ?> al <?php echo $fmt($p->semana_hasta); ?>
            · Emitido el <?php echo $fmt($p->fecha); ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/permisos" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/rutas/permisoImprimible/<?php echo (int)$p->id; ?>"
           target="_blank" class="btn-sig btn-sig--primary">
            <i class="bi bi-printer"></i> Imprimir
        </a>
    </div>
</div>

<?php if ($anulado): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);border-left:3px solid var(--danger-500);">
    <div class="sig-card__body u-p-sp-4 u-fs-13px">
        <strong><i class="bi bi-x-octagon u-c-danger-600"></i> Permiso anulado.</strong>
        <?php echo $v($p->motivo_anulacion); ?>
        <div class="u-fs-12px u-c-text-secondary u-mt-2px">
            El número <strong><?php echo $v($p->numero); ?></strong> no se reutiliza: el oficio ya salió.
        </div>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 anim-slide-up" style="margin-bottom:var(--sp-5);">
    <!-- El oficio -->
    <div class="col-lg-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-file-earmark-text u-c-x6366F1"></i> El oficio</div>
            </div>
            <div class="sig-card__body u-p-sp-4 u-fs-13px">
                <div style="display:grid;grid-template-columns:auto 1fr;gap:6px var(--sp-4);">
                    <strong>N°</strong>          <span class="u-ff-font-mono"><?php echo $v($p->numero); ?></span>
                    <strong>Dirigido a</strong>  <span><?php echo $v($p->destinatario_nombre ?: '—'); ?></span>
                    <strong>Cargo</strong>       <span><?php echo $v($p->destinatario_cargo ?: '—'); ?></span>
                    <strong>Responsable</strong> <span>
                        <?php echo $v($p->responsable_nombre ?: '—'); ?>
                        <?php if (!empty($p->responsable_cargo)): ?>
                            <br><span class="u-fs-11px u-c-text-tertiary"><?php echo $v($p->responsable_cargo); ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <?php if (!empty($p->observaciones)): ?>
                    <div class="u-mt-sp-3 u-pt-sp-3 u-bdt-1px-solid-border-subtle">
                        <div class="u-fs-11px u-fw-700 u-c-text-tertiary u-tt-uppercase">Observaciones</div>
                        <?php echo nl2br($v($p->observaciones)); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- El trámite -->
    <div class="col-lg-7">
        <div class="sig-card h-100" style="border-top:3px solid var(--warning-500);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-hourglass-split u-c-warning-600"></i> Respuesta de la institución</div>
            </div>
            <div class="sig-card__body u-p-sp-4">
                <?php if ($respondido): ?>
                    <div style="font-size:15px;font-weight:700;color:<?php echo $p->estado === PermisoRuta::EST_ACEPTADO ? 'var(--success-600)' : 'var(--danger-600)'; ?>;">
                        <i class="bi <?php echo $p->estado === PermisoRuta::EST_ACEPTADO ? 'bi-check2-circle' : 'bi-x-circle'; ?>"></i>
                        <?php echo $v($p->estado); ?>
                        <span class="u-fs-12px u-fw-500 u-c-text-secondary">
                            el <?php echo $fmt($p->fecha_respuesta); ?>
                        </span>
                    </div>
                    <?php if (!empty($p->observaciones)): ?>
                        <div class="u-fs-13px u-c-text-secondary u-mt-sp-2">
                            <?php echo nl2br($v($p->observaciones)); ?>
                        </div>
                    <?php endif; ?>

                    <div class="u-mt-sp-4 u-pt-sp-3 u-bdt-1px-solid-border-subtle">
                        <?php if (!empty($p->respuesta_archivo)): ?>
                            <a href="<?php echo URL_ROOT; ?>/descarga/permisoRuta/<?php echo (int)$p->id; ?>"
                               target="_blank" class="btn-sig btn-sig--ghost btn-sig--sm">
                                <i class="bi bi-file-earmark-text"></i> Ver el pase recibido
                            </a>
                            <div class="u-fs-11px u-c-text-tertiary u-mt-4px">
                                <?php echo $v($p->respuesta_original); ?>
                            </div>
                        <?php else: ?>
                            <form method="POST" action="<?php echo URL_ROOT; ?>/rutas/subirRespuestaPermiso"
                                  enctype="multipart/form-data" style="margin:0;display:flex;gap:var(--sp-2);align-items:flex-end;flex-wrap:wrap;">
                                <input type="hidden" name="id" value="<?php echo (int)$p->id; ?>">
                                <div class="sig-field u-m-0 u-flex-1 u-minw-200px">
                                    <label class="sig-field__label" for="pm_resp_file">Adjuntar el pase recibido</label>
                                    <input type="file" name="respuesta" id="pm_resp_file" class="sig-input"
                                           accept=".pdf,.jpg,.jpeg,.png" required style="font-size:12px;padding:4px;">
                                </div>
                                <button type="submit" class="btn-sig btn-sig--ghost btn-sig--sm">
                                    <i class="bi bi-upload"></i> Archivar
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>

                <?php elseif ($anulado): ?>
                    <p class="u-m-0 u-c-text-tertiary u-fs-13px">
                        El permiso quedó anulado, así que no hay respuesta que registrar.
                    </p>
                <?php else: ?>
                    <p class="u-fs-13px u-c-text-secondary" style="margin:0 0 var(--sp-3);">
                        El oficio está <strong>en espera</strong>. Cuando la institución conteste, se registra aquí.
                    </p>
                    <form method="POST" action="<?php echo URL_ROOT; ?>/rutas/responderPermiso">
                        <input type="hidden" name="id" value="<?php echo (int)$p->id; ?>">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="sig-field u-m-0">
                                    <label class="sig-field__label" for="rp_estado">Respuesta <span class="req">*</span></label>
                                    <select name="estado" id="rp_estado" class="sig-select" required>
                                        <option value="<?php echo PermisoRuta::EST_ACEPTADO; ?>">Aceptado — dieron el pase</option>
                                        <option value="<?php echo PermisoRuta::EST_RECHAZADO; ?>">Rechazado</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field u-m-0">
                                    <label class="sig-field__label" for="rp_fecha">Fecha</label>
                                    <input type="date" name="fecha_respuesta" id="rp_fecha" class="sig-input"
                                           value="<?php echo date('Y-m-d'); ?>" min="<?php echo $v($p->fecha); ?>">
                                </div>
                            </div>
                            <div class="col-md-4 u-ai-flex-end" style="display:flex;">
                                <button type="submit" class="btn-sig btn-sig--primary btn-sig--sm">
                                    <i class="bi bi-check-lg"></i> Registrar respuesta
                                </button>
                            </div>
                            <div class="col-12">
                                <div class="sig-field u-m-0">
                                    <label class="sig-field__label" for="rp_obs">
                                        Observaciones <span class="u-c-text-tertiary u-fw-400">(obligatorias si se rechaza)</span>
                                    </label>
                                    <textarea name="observaciones" id="rp_obs" class="sig-input" rows="2"
                                              placeholder="Motivo del rechazo, condiciones del pase…"></textarea>
                                </div>
                            </div>
                        </div>
                    </form>

                    <div class="u-mt-sp-4 u-pt-sp-3 u-bdt-1px-solid-border-subtle">
                        <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm u-c-danger-600"
                                data-bs-toggle="modal" data-bs-target="#modalAnularPermiso">
                            <i class="bi bi-x-octagon"></i> Anular el oficio
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Las salidas que cubre -->
<div class="sig-card anim-slide-up" style="border-top:4px solid var(--teal-500);">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-calendar-event u-c-teal-500"></i>
            Salidas que cubre (<?php echo count($sals); ?>)
        </div>
        <span class="u-fs-11px u-c-text-tertiary">Un oficio cubre toda la semana (R-20)</span>
    </div>
    <div class="sig-table-wrap" data-no-export>
        <table class="sig-table">
            <thead>
                <tr><th>Fecha</th><th>Recorrido</th><th>Grupo / Institución</th><th>Estado de la salida</th><th class="col-actions"></th></tr>
            </thead>
            <tbody>
            <?php if (empty($sals)): ?>
                <tr><td colspan="5" class="sig-table-empty">Este permiso no cubre ninguna salida.</td></tr>
            <?php else: foreach ($sals as $s): ?>
                <tr>
                    <td class="u-fs-12px">
                        <?php echo $fmt($s->fecha); ?>
                        <?php if (!empty($s->hora)): ?>
                            <span class="u-c-text-tertiary"><?php echo substr($s->hora, 0, 5); ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="cell-strong"><?php echo $v($s->ruta_nombre); ?></td>
                    <td class="u-fs-12px u-c-text-secondary"><?php echo $v($s->institucion_nombre ?: $s->origen); ?></td>
                    <td>
                        <span class="sig-badge sig-badge--sm <?php echo RutaEjecucion::ESTADO_BADGES[$s->estado] ?? 'sig-badge--neutral'; ?>">
                            <?php echo $v($s->estado); ?>
                        </span>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo (int)$s->id; ?>" class="row-action" title="Abrir la salida">
                            <i class="bi bi-box-arrow-up-right"></i>
                        </a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: anular -->
<div class="modal fade" id="modalAnularPermiso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/rutas/anularPermiso" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-octagon"></i> Anular el oficio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" value="<?php echo (int)$p->id; ?>">
                <div class="u-p-sp-3 u-bg-bg-muted-subtle u-br-8px u-fs-12_5px u-mb-sp-4">
                    El registro se conserva con su motivo — el oficio ya salió de la institución. El
                    número <strong><?php echo $v($p->numero); ?></strong> <strong>no se reutiliza</strong>.
                </div>
                <div class="sig-field u-m-0">
                    <label class="sig-field__label" for="an_motivo">¿Por qué se anula? <span class="req">*</span></label>
                    <textarea name="motivo" id="an_motivo" class="sig-input" rows="3" required
                              placeholder="Ej: se emitió con la institución equivocada; la semana se reprogramó…"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--danger-600);">Anular</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
