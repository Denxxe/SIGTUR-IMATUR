<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Conteos de inventario (R-8 · B-05/B-48) — el dolor #2 del cliente.
 *
 * No es un inventario periódico: se dispara al cambiar de coordinador o de
 * presidencia. Solo puede haber un conteo abierto a la vez.
 */
$abierto = $data['abierto'] ?? null;
$puedeEscribir = InventarioController::puedeEscribir();
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Auditoría</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Conteos de inventario'; ?></h1>
        <p class="page__subtitle">Verificación física de todos los bienes al cambiar de gestión.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Bienes</a>
        <?php if ($puedeEscribir && !$abierto): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalConteo">
                <i class="bi bi-clipboard-check"></i> Iniciar conteo
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if ($abierto): ?>
    <div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-clipboard-pulse"></i>
        Hay un <strong>conteo abierto</strong> desde el <?php echo htmlspecialchars($abierto->fecha_inicio); ?>
        (<?php echo htmlspecialchars($abierto->motivo); ?>).
        <a href="<?php echo URL_ROOT; ?>/inventario/verConteo/<?php echo (int)$abierto->id; ?>">Continuar el conteo</a>.
        No se puede iniciar otro hasta cerrarlo.
    </div>
<?php else: ?>
    <div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-info-circle"></i>
        Al iniciar un conteo, el sistema <strong>congela</strong> lo que cree tener de cada bien
        (ubicación, estatus y condición). Después se registra lo hallado físicamente y se comparan.
    </div>
<?php endif; ?>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead><tr>
            <th>Inicio</th><th>Motivo</th><th>Responsable</th><th>Avance</th>
            <th>Faltantes</th><th>Estado</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($data['conteos'])): ?>
                <tr><td colspan="7" class="sig-table-empty">Todavía no se ha hecho ningún conteo.</td></tr>
            <?php else: ?>
                <?php foreach ($data['conteos'] as $c): ?>
                    <?php
                    $tot = (int)$c->total; $ver = (int)$c->verificados;
                    $pct = $tot > 0 ? round($ver * 100 / $tot) : 0;
                    ?>
                    <tr>
                        <td class="cell-strong"><?php echo htmlspecialchars($c->fecha_inicio); ?></td>
                        <td><?php echo htmlspecialchars($c->motivo); ?></td>
                        <td style="font-size:12.5px;"><?php echo htmlspecialchars($c->responsable ?: '—'); ?></td>
                        <td>
                            <span style="font-size:12.5px;"><?php echo $ver; ?> / <?php echo $tot; ?></span>
                            <div style="height:4px;background:var(--border-subtle);border-radius:2px;margin-top:3px;">
                                <div style="height:4px;width:<?php echo $pct; ?>%;background:var(--brand-500);border-radius:2px;"></div>
                            </div>
                        </td>
                        <td><?php echo (int)$c->faltantes > 0 ? '<span class="sig-badge sig-badge--danger">' . (int)$c->faltantes . '</span>' : '<span style="color:var(--text-tertiary)">0</span>'; ?></td>
                        <td><span class="sig-badge <?php echo $c->estado === 'Abierto' ? 'sig-badge--warning' : 'sig-badge--success'; ?>"><?php echo htmlspecialchars($c->estado); ?></span></td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/inventario/verConteo/<?php echo (int)$c->id; ?>" class="row-action row-action--view"><i class="bi bi-list-check"></i> Ver</a>
                            <a href="<?php echo URL_ROOT; ?>/inventario/actaConteo/<?php echo (int)$c->id; ?>" target="_blank" class="row-action"><i class="bi bi-file-earmark-text"></i> Acta</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($puedeEscribir && !$abierto): ?>
<div class="modal fade" id="modalConteo" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/abrirConteo" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Iniciar conteo de inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-7"><div class="sig-field"><label class="sig-field__label" for="ct_motivo">Motivo <span class="req">*</span></label>
                        <select name="motivo" id="ct_motivo" class="sig-select" required>
                            <?php foreach (ConteoInventario::MOTIVOS as $m): ?><option value="<?php echo $m; ?>"><?php echo $m; ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-5"><div class="sig-field"><label class="sig-field__label" for="ct_fecha">Fecha de inicio <span class="req">*</span></label>
                        <input type="date" name="fecha_inicio" id="ct_fecha" class="sig-input" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="ct_resp">Responsable del conteo</label>
                        <select name="id_responsable" id="ct_resp" class="sig-select js-search">
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="ct_obs">Observaciones</label>
                        <textarea name="observaciones" id="ct_obs" class="sig-textarea" rows="2"></textarea></div></div>
                    <div class="col-12"><div class="sig-alert sig-alert--warning" style="margin:0;">
                        Se creará una línea por cada bien del inventario activo con su estado actual congelado.
                    </div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-play-fill"></i> Iniciar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
