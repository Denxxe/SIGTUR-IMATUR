<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Actas de Desincorporación (C-5, mig. 077).
 *
 * Dos pasos, que es como ocurre en la realidad:
 *   1. Se arma el acta con los bienes desincorporados que siguen en la sede,
 *      se imprime y se lleva a la Alcaldía.
 *   2. Vuelve firmada y sellada → se registra y TODOS sus bienes pasan a
 *      «Retirado» de una sola vez.
 */
$cands = $data['candidatos'] ?? [];
$actas = $data['actas'] ?? [];
$puedeEscribir = InventarioController::puedeEscribir();
$f = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Alcaldía</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Actas de Desincorporación'; ?></h1>
        <p class="page__subtitle">El acta lista los bienes que la Alcaldía retira. Su firma y sello son el aval del retiro.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index?ver=baja" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Desincorporados
        </a>
        <?php if ($puedeEscribir && !empty($cands)): ?>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalActa">
            <i class="bi bi-file-earmark-text"></i> Emitir acta
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-info-circle"></i>
    <div>
        <strong>El formato oficial del acta todavía no lo ha entregado el cliente.</strong>
        El flujo y los datos ya funcionan; la hoja imprimible es <strong>provisional</strong> y se
        sustituye por el formato real cuando llegue, sin tocar nada de lo registrado.
    </div>
</div>

<!-- ── Bienes desincorporados sin acta ───────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);<?php echo $cands ? 'border-left:3px solid var(--warning-500);' : ''; ?>">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-archive"></i> Desincorporados sin acta (<?php echo count($cands); ?>)
        </div>
    </div>
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <?php if (empty($cands)): ?>
            <p style="color:var(--text-tertiary);margin:0;">
                No hay bienes desincorporados esperando acta.
            </p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr>
                        <th>Código BN</th><th>Bien</th><th>Categoría</th>
                        <th>Condición</th><th>Fecha de baja</th><th>Motivo</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($cands as $c): ?>
                        <tr>
                            <td style="font-family:var(--font-mono);color:var(--brand-600);"><?php echo htmlspecialchars($c->codigo_bn ?? 'S/N'); ?></td>
                            <td><span class="cell-strong"><?php echo htmlspecialchars($c->nombre); ?></span></td>
                            <td><?php echo htmlspecialchars($c->categoria ?? '—'); ?></td>
                            <td><span class="sig-badge <?php echo Inventario::CONDICION_BADGES[$c->condicion] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($c->condicion ?? '—'); ?></span></td>
                            <td style="font-size:12px;"><?php echo $f($c->fecha_baja); ?></td>
                            <td style="font-size:12px;color:var(--text-secondary);max-width:260px;white-space:normal;"><?php echo htmlspecialchars($c->motivo_baja ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Actas emitidas ────────────────────────────────────────────── -->
<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead><tr>
            <th>Acta N°</th><th>Fecha</th><th>Bienes</th>
            <th>Estado</th><th>Motivo</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($actas)): ?>
                <tr><td colspan="6" class="sig-table-empty">Todavía no se ha emitido ninguna acta.</td></tr>
            <?php else: foreach ($actas as $a):
                $firmada = ActaDesincorporacion::estaFirmada($a); ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($a->numero); ?></td>
                    <td><?php echo $f($a->fecha); ?></td>
                    <td><span class="sig-badge sig-badge--info"><?php echo (int)$a->total_bienes; ?></span></td>
                    <td>
                        <?php if ($firmada): ?>
                            <span class="sig-badge sig-badge--success" title="La Alcaldía firmó y selló: los bienes quedaron retirados">
                                Firmada · <?php echo $f($a->fecha_firma); ?>
                            </span>
                            <?php if (!empty($a->recibido_por)): ?>
                                <div style="font-size:11px;color:var(--text-tertiary);">
                                    Recibió: <?php echo htmlspecialchars($a->recibido_por); ?>
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="sig-badge sig-badge--warning" title="Emitida: falta llevarla a la Alcaldía y registrarla firmada">Esperando firma</span>
                        <?php endif; ?>
                    </td>
                    <td style="font-size:12px;color:var(--text-secondary);max-width:240px;white-space:normal;">
                        <?php echo htmlspecialchars($a->motivo ?? '—'); ?>
                    </td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/inventario/acta/<?php echo $a->id; ?>" target="_blank" class="row-action">
                            <i class="bi bi-printer"></i> Imprimir
                        </a>
                        <?php if (!empty($a->archivo_url)): ?>
                            <a href="<?php echo URL_ROOT; ?>/descarga/acta/<?php echo $a->id; ?>" class="row-action" title="<?php echo htmlspecialchars($a->nombre_original ?? ''); ?>">
                                <i class="bi bi-paperclip"></i> Escaneado
                            </a>
                        <?php endif; ?>
                        <?php if ($puedeEscribir && !$firmada): ?>
                        <button type="button" class="row-action js-firmar"
                                data-id="<?php echo $a->id; ?>"
                                data-numero="<?php echo htmlspecialchars($a->numero); ?>"
                                data-fecha="<?php echo htmlspecialchars($a->fecha); ?>"
                                data-n="<?php echo (int)$a->total_bienes; ?>">
                            <i class="bi bi-check2-square"></i> Registrar firmada
                        </button>
                        <?php endif; ?>
                        <?php if ($puedeEscribir): ?>
                        <button type="button" class="row-action row-action--del js-anular-acta"
                                data-id="<?php echo $a->id; ?>"
                                data-numero="<?php echo htmlspecialchars($a->numero); ?>"
                                data-firmada="<?php echo $firmada ? '1' : '0'; ?>">
                            <i class="bi bi-x-circle"></i> Anular
                        </button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php if ($puedeEscribir): ?>
<!-- Modal: emitir acta -->
<div class="modal fade" id="modalActa" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/inventario/emitirActa" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Emitir Acta de Desincorporación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:13px;">
                    El número lo asigna el sistema. Los bienes seleccionados quedan enganchados al acta
                    y <strong>no se marcan como retirados todavía</strong>: eso ocurre cuando registres
                    el acta firmada y sellada por la Alcaldía.
                </p>

                <div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--sp-3);">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Fecha del acta <span class="req">*</span></label>
                        <input type="date" name="fecha" class="sig-input" required
                               value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Motivo del lote</label>
                        <input type="text" name="motivo" class="sig-input"
                               placeholder="Ej.: Deterioro irreparable · Obsolescencia tecnológica">
                    </div>
                </div>

                <div class="sig-field mb-3">
                    <label class="sig-field__label">
                        Bienes a desincorporar <span class="req">*</span>
                        <button type="button" id="btnTodosActa" class="row-action" style="margin-left:8px;">
                            <i class="bi bi-check-all"></i> Todos
                        </button>
                    </label>
                    <div style="max-height:260px;overflow:auto;border:1px solid var(--border-subtle,#e5e7eb);border-radius:6px;padding:8px;">
                        <?php foreach ($cands as $c): ?>
                            <label style="display:flex;gap:8px;align-items:flex-start;padding:5px 3px;border-bottom:1px solid var(--border-subtle,#f1f5f9);">
                                <input type="checkbox" name="bienes[]" value="<?php echo $c->id; ?>" class="js-bien-acta">
                                <span style="font-size:13px;line-height:1.4;">
                                    <strong><?php echo htmlspecialchars($c->nombre); ?></strong>
                                    <?php if (!empty($c->codigo_bn)): ?>
                                        <span style="font-family:var(--font-mono);color:var(--brand-600);">· <?php echo htmlspecialchars($c->codigo_bn); ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <span style="color:var(--text-tertiary);font-size:12px;">
                                        <?php echo htmlspecialchars($c->categoria ?? '—'); ?>
                                        · <?php echo htmlspecialchars($c->condicion ?? '—'); ?>
                                        · baja del <?php echo $f($c->fecha_baja); ?>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small id="sinSeleccionActa" style="display:none;color:var(--danger-500);font-size:11px;">
                        <i class="bi bi-exclamation-triangle"></i> Selecciona al menos un bien.
                    </small>
                </div>

                <div class="sig-field mb-2">
                    <label class="sig-field__label">Observación (opcional)</label>
                    <textarea name="observacion" class="sig-input" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Emitir e imprimir</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: registrar acta firmada -->
<div class="modal fade" id="modalFirmar" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/registrarActaFirmada" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-check2-square"></i> Registrar acta firmada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="fi_id">
                <div class="sig-alert sig-alert--warning" style="margin-bottom:var(--sp-3);">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>
                        Al registrar el acta <strong id="fi_numero"></strong>, sus
                        <strong id="fi_n"></strong> bien(es) quedarán marcados como
                        <strong>retirados por la Alcaldía</strong>.
                    </div>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Fecha de la firma <span class="req">*</span></label>
                    <input type="date" name="fecha_firma" id="fi_fecha" class="sig-input" required
                           value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Quién recibió por la Alcaldía</label>
                    <input type="text" name="recibido_por" class="sig-input" placeholder="Nombre y cargo">
                </div>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Escaneado del acta sellada (opcional)</label>
                    <input type="file" name="documento" class="sig-input" accept=".pdf,.jpg,.jpeg,.png">
                    <small style="color:var(--text-tertiary);font-size:11px;">
                        PDF o imagen, máx. 5 MB. Puedes adjuntarlo después si todavía está en papel.
                    </small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary">Confirmar retiro</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: anular acta -->
<div class="modal fade" id="modalAnularActa" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/anularActa" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Anular acta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="an_acta_id">
                <p class="text-muted" style="font-size:13px;">
                    Vas a anular el acta <strong id="an_acta_numero"></strong>. Sus bienes vuelven a
                    quedar desincorporados y sin acta; el número queda consumido.
                </p>
                <div id="an_acta_aviso" class="sig-alert sig-alert--warning" style="display:none;margin-bottom:var(--sp-3);">
                    <i class="bi bi-exclamation-triangle"></i>
                    <div>Esta acta ya estaba firmada: al anularla, a sus bienes se les <strong>revierte el retiro</strong>, porque el aval que lo respaldaba deja de existir.</div>
                </div>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Motivo <span class="req">*</span></label>
                    <textarea name="motivo" class="sig-input" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--danger">Anular</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const btnTodos = document.getElementById('btnTodosActa');
    if (btnTodos) btnTodos.addEventListener('click', () => {
        const cs = document.querySelectorAll('.js-bien-acta');
        const marcar = ![...cs].every(c => c.checked);
        cs.forEach(c => { c.checked = marcar; });
    });

    const formActa = document.querySelector('#modalActa form');
    if (formActa) formActa.addEventListener('submit', (e) => {
        const alguno = document.querySelector('.js-bien-acta:checked');
        document.getElementById('sinSeleccionActa').style.display = alguno ? 'none' : 'block';
        if (!alguno) e.preventDefault();
    });

    const mFi = new bootstrap.Modal(document.getElementById('modalFirmar'));
    document.querySelectorAll('.js-firmar').forEach(b => b.addEventListener('click', () => {
        document.getElementById('fi_id').value = b.dataset.id;
        document.getElementById('fi_numero').textContent = 'N° ' + b.dataset.numero;
        document.getElementById('fi_n').textContent = b.dataset.n;
        // La firma no puede ser anterior al acta.
        document.getElementById('fi_fecha').min = b.dataset.fecha;
        mFi.show();
    }));

    const mAn = new bootstrap.Modal(document.getElementById('modalAnularActa'));
    document.querySelectorAll('.js-anular-acta').forEach(b => b.addEventListener('click', () => {
        document.getElementById('an_acta_id').value = b.dataset.id;
        document.getElementById('an_acta_numero').textContent = 'N° ' + b.dataset.numero;
        document.getElementById('an_acta_aviso').style.display = b.dataset.firmada === '1' ? '' : 'none';
        mAn.show();
    }));
});
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
