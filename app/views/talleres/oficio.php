<?php require_once '../app/views/inc/header.php';
$t = $data['taller'];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo (int)$t->id; ?>" style="text-decoration:none;">Formación</a>
            · Oficio
        </div>
        <h1 class="page__title"><?php echo htmlspecialchars($data['titulo']); ?></h1>
        <p class="page__subtitle">
            Al emitirlo recibe su número <strong>FORM-NNN/<?php echo date('Y'); ?></strong>, que no se recicla.
            El texto propuesto sale de los datos de la actividad y se puede ajustar antes de emitir: el oficio
            queda guardado tal como salió y se puede reimprimir.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/talleres/detalle/<?php echo (int)$t->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form action="<?php echo URL_ROOT; ?>/talleres/oficio/<?php echo (int)$t->id; ?>" method="POST" class="sig-card anim-slide-up">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-envelope-paper"></i> Datos del oficio</div></div>
            <div class="sig-card__body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="sig-field">
                            <label class="sig-field__label" for="of_dest">Destinatario <span class="req">*</span></label>
                            <input type="text" name="destinatario_nombre" id="of_dest" class="sig-input" required maxlength="200"
                                   data-nombre-libre placeholder="Ej: Lcda. María Pérez">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="sig-field">
                            <label class="sig-field__label" for="of_cargo">Cargo / institución</label>
                            <input type="text" name="destinatario_cargo" id="of_cargo" class="sig-input" maxlength="200"
                                   placeholder="Ej: Directora de la U.E. …">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label" for="of_asunto">Asunto (registro interno)</label>
                            <input type="text" name="asunto" id="of_asunto" class="sig-input js-no-capitalize" maxlength="500"
                                   value="<?php echo htmlspecialchars(($t->tipo_actividad ?? 'Actividad') . ': ' . $t->nombre); ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label" for="of_cuerpo">Texto del oficio <span class="req">*</span></label>
                            <textarea name="cuerpo" id="of_cuerpo" class="sig-textarea js-no-capitalize" rows="11" required><?php echo htmlspecialchars($data['cuerpo']); ?></textarea>
                            <small class="u-c-text-tertiary u-fs-12px">
                                Deje una línea en blanco entre párrafos. El membrete, la fecha, el número, el destinatario y la
                                firma de la Presidencia se agregan solos.
                            </small>
                        </div>
                    </div>
                </div>
                <div class="text-end mt-3">
                    <button type="submit" class="btn-sig btn-sig--primary"
                            onclick="return confirm('Se asignará el siguiente número FORM y quedará registrado. ¿Emitir el oficio?');">
                        <i class="bi bi-printer"></i> Emitir e imprimir
                    </button>
                </div>
            </div>
        </form>
    </div>
    <div class="col-lg-4">
        <div class="sig-card anim-slide-up">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-clock-history"></i> Emitidos para esta actividad</div></div>
            <div class="sig-card__body" style="padding:0;">
                <?php if (empty($data['previos'])): ?>
                    <p class="u-c-text-tertiary" style="padding:14px;margin:0;">Ninguno todavía.</p>
                <?php else: ?>
                    <div class="sig-table-wrap" data-no-export>
                        <table class="sig-table">
                            <thead><tr><th>N.º</th><th>Fecha</th><th>Destinatario</th><th class="col-actions">Acciones</th></tr></thead>
                            <tbody>
                                <?php foreach ($data['previos'] as $o): ?>
                                    <tr>
                                        <td class="cell-strong"><?php echo htmlspecialchars($o->numero); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($o->fecha)); ?></td>
                                        <td><?php echo htmlspecialchars($o->destinatario_nombre ?? '—'); ?></td>
                                        <td class="col-actions">
                                            <a href="<?php echo URL_ROOT; ?>/talleres/verOficio/<?php echo (int)$o->id; ?>" target="_blank" class="row-action"><i class="bi bi-printer"></i> Reimprimir</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
