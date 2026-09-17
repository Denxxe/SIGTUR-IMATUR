<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Oficios de relación de bienes nuevos a la Alcaldía (mig. 075).
 *
 * Es el dolor #1 declarado por el cliente (B-05): armar el oficio con los
 * bienes nuevos. Arriba se eligen los bienes que todavía no se han reportado;
 * abajo queda el libro de oficios emitidos, reimprimible.
 */
$cands  = $data['candidatos'] ?? [];
$lista  = $data['relaciones'] ?? [];
$cfg    = $data['config'] ?? [];
$v      = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$puedeEscribir = InventarioController::puedeEscribir();
$bs     = fn($n) => $n === null ? '—' : 'Bs. ' . number_format((float)$n, 2, ',', '.');
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Alcaldía</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Oficios de relación de bienes'; ?></h1>
        <p class="page__subtitle">Comunicación de bienes nuevos a la Coordinación de Bienes de la Alcaldía.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Bienes
        </a>
        <?php if ($puedeEscribir && !empty($cands)): ?>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalOficio">
            <i class="bi bi-file-earmark-text"></i> Emitir oficio
        </button>
        <?php endif; ?>
    </div>
</div>

<!-- ── Bienes sin reportar ───────────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);<?php echo $cands ? 'border-left:3px solid var(--warning-500);' : ''; ?>">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-box-seam"></i> Bienes aún no reportados (<?php echo count($cands); ?>)
        </div>
    </div>
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <?php if (empty($cands)): ?>
            <p style="color:var(--text-tertiary);margin:0;">
                Todos los bienes registrados ya fueron relacionados en un oficio.
            </p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr>
                        <th>Bien</th><th>Categoría</th><th>Origen</th><th>Monto</th><th>Registrado</th>
                    </tr></thead>
                    <tbody>
                    <?php foreach ($cands as $c): ?>
                        <tr>
                            <td><span class="cell-strong"><?php echo htmlspecialchars($c->nombre); ?></span></td>
                            <td><?php echo htmlspecialchars($c->categoria ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($c->origen ?? '—'); ?></td>
                            <td><?php echo $bs($c->costo_adquisicion); ?></td>
                            <td style="font-size:12px;color:var(--text-tertiary);">
                                <?php echo htmlspecialchars(substr((string)$c->fecha_adquisicion, 0, 10) ?: '—'); ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Oficios emitidos ──────────────────────────────────────────── -->
<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead><tr>
            <th>Oficio N°</th><th>Fecha</th><th>Destinatario</th>
            <th>Bienes</th><th>Monto total</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($lista)): ?>
                <tr><td colspan="6" class="sig-table-empty">Todavía no se ha emitido ningún oficio de relación.</td></tr>
            <?php else: foreach ($lista as $r): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($r->numero); ?></td>
                    <td><?php echo date('d/m/Y', strtotime($r->fecha)); ?></td>
                    <td>
                        <?php echo htmlspecialchars($r->destinatario_nombre); ?>
                        <?php if (!empty($r->destinatario_cargo)): ?>
                            <div style="font-size:12px;color:var(--text-tertiary);">
                                <?php echo htmlspecialchars($r->destinatario_cargo); ?>
                            </div>
                        <?php endif; ?>
                    </td>
                    <td><span class="sig-badge sig-badge--info"><?php echo (int)$r->total_bienes; ?></span></td>
                    <td><?php echo $bs($r->monto_total); ?></td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/inventario/relacion/<?php echo $r->id; ?>"
                           target="_blank" class="row-action">
                            <i class="bi bi-printer"></i> Imprimir
                        </a>
                        <?php if ($puedeEscribir): ?>
                        <button type="button" class="row-action row-action--del js-anular"
                                data-id="<?php echo $r->id; ?>"
                                data-numero="<?php echo htmlspecialchars($r->numero); ?>">
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
<!-- Modal: emitir oficio -->
<div class="modal fade" id="modalOficio" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/inventario/emitirRelacion" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-file-earmark-text"></i> Emitir oficio de relación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted" style="font-size:13px;">
                    El número de oficio lo asigna el sistema al guardar. Los bienes seleccionados
                    quedan marcados como reportados y no volverán a aparecer en esta lista.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:var(--sp-3);">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Fecha del oficio <span class="req">*</span></label>
                        <input type="date" name="fecha" class="sig-input" required
                               value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Destinatario <span class="req">*</span></label>
                        <input type="text" name="destinatario_nombre" class="sig-input" required
                               value="<?php echo $v('bienes_destinatario_nombre'); ?>">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Cargo</label>
                        <input type="text" name="destinatario_cargo" class="sig-input"
                               value="<?php echo $v('bienes_destinatario_cargo'); ?>">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label">Ente</label>
                        <input type="text" name="destinatario_ente" class="sig-input"
                               value="<?php echo $v('bienes_destinatario_ente'); ?>">
                    </div>
                </div>

                <div class="sig-field mb-3">
                    <label class="sig-field__label">
                        Bienes a relacionar <span class="req">*</span>
                        <button type="button" id="btnTodos" class="row-action" style="margin-left:8px;">
                            <i class="bi bi-check-all"></i> Todos
                        </button>
                    </label>
                    <div style="max-height:260px;overflow:auto;border:1px solid var(--border-subtle,#e5e7eb);
                                border-radius:6px;padding:8px;">
                        <?php foreach ($cands as $c): ?>
                            <label style="display:flex;gap:8px;align-items:flex-start;padding:5px 3px;
                                          border-bottom:1px solid var(--border-subtle,#f1f5f9);">
                                <input type="checkbox" name="bienes[]" value="<?php echo $c->id; ?>" class="js-bien">
                                <span style="font-size:13px;line-height:1.4;">
                                    <strong><?php echo htmlspecialchars($c->nombre); ?></strong>
                                    <?php if (!empty($c->marca)): ?> — <?php echo htmlspecialchars($c->marca); ?><?php endif; ?>
                                    <?php if (!empty($c->serial)): ?>
                                        <span style="color:var(--text-tertiary);">· Serial <?php echo htmlspecialchars($c->serial); ?></span>
                                    <?php endif; ?>
                                    <br>
                                    <span style="color:var(--text-tertiary);font-size:12px;">
                                        <?php echo htmlspecialchars($c->categoria ?? '—'); ?>
                                        · <?php echo $bs($c->costo_adquisicion); ?>
                                    </span>
                                </span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <small id="sinSeleccion" style="display:none;color:var(--danger-500);font-size:11px;">
                        <i class="bi bi-exclamation-triangle"></i> Selecciona al menos un bien.
                    </small>
                </div>

                <div class="sig-field mb-2">
                    <label class="sig-field__label">Nota adicional (opcional)</label>
                    <textarea name="observacion" class="sig-input" rows="2"
                              placeholder="Párrafo extra que se imprime bajo la tabla"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary">
                    <i class="bi bi-check-lg"></i> Emitir e imprimir
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: anular oficio -->
<div class="modal fade" id="modalAnular" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/anularRelacion" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-x-circle"></i> Anular oficio</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="an_id">
                <p class="text-muted" style="font-size:13px;">
                    Vas a anular el oficio <strong id="an_numero"></strong>. Sus bienes vuelven a quedar
                    sin reportar; el número queda consumido y no se reutiliza.
                </p>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Motivo <span class="req">*</span></label>
                    <textarea name="motivo" class="sig-input" rows="2" required
                              placeholder="Por qué se anula"></textarea>
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
    const form = document.querySelector('#modalOficio form');
    const btnTodos = document.getElementById('btnTodos');

    if (btnTodos) btnTodos.addEventListener('click', () => {
        const casillas = document.querySelectorAll('.js-bien');
        const marcar = ![...casillas].every(c => c.checked);
        casillas.forEach(c => { c.checked = marcar; });
    });

    if (form) form.addEventListener('submit', (e) => {
        const alguno = document.querySelector('.js-bien:checked');
        document.getElementById('sinSeleccion').style.display = alguno ? 'none' : 'block';
        if (!alguno) e.preventDefault();
    });

    const mAn = new bootstrap.Modal(document.getElementById('modalAnular'));
    document.querySelectorAll('.js-anular').forEach(b => b.addEventListener('click', () => {
        document.getElementById('an_id').value = b.dataset.id;
        document.getElementById('an_numero').textContent = 'N° ' + b.dataset.numero;
        mAn.show();
    }));
});
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
