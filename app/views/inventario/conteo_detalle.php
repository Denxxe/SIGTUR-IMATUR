<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Detalle de un conteo: lo esperado (congelado) vs lo hallado (B-50).
 * Se verifica estatus, lugar y condición de cada bien.
 */
$c   = $data['conteo'];
$r   = $data['resumen'];
$abierto = ($c->estado === ConteoInventario::ABIERTO);
$puedeEscribir = InventarioController::puedeEscribir();
$filtro = $data['filtro'] ?? '';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Conteo <?php echo htmlspecialchars($c->estado); ?></div>
        <h1 class="page__title"><?php echo htmlspecialchars($c->motivo); ?></h1>
        <p class="page__subtitle">
            Iniciado el <?php echo htmlspecialchars($c->fecha_inicio); ?>
            <?php if ($c->responsable): ?> · responsable: <?php echo htmlspecialchars($c->responsable); ?><?php endif; ?>
            <?php if ($c->fecha_cierre): ?> · cerrado el <?php echo htmlspecialchars($c->fecha_cierre); ?><?php endif; ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/conteos" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Conteos</a>
        <a href="<?php echo URL_ROOT; ?>/inventario/actaConteo/<?php echo (int)$c->id; ?>" target="_blank" class="btn-sig btn-sig--ghost"><i class="bi bi-file-earmark-text"></i> Acta</a>
        <?php if ($abierto && $puedeEscribir): ?>
            <a href="<?php echo URL_ROOT; ?>/inventario/cerrarConteo/<?php echo (int)$c->id; ?>" class="btn-sig btn-sig--primary"
               onclick="return confirm('¿Cerrar el conteo? Después no se podrán registrar más verificaciones.');">
               <i class="bi bi-lock"></i> Cerrar conteo</a>
        <?php endif; ?>
    </div>
</div>

<!-- Resumen -->
<div class="row g-3 anim-slide-up" style="margin-bottom:var(--sp-4);">
    <?php
    $tiles = [
        ['Total',            $r['total'],            'var(--brand-500)'],
        ['Verificados',      $r['hallados'] + $r['faltantes'], 'var(--teal-500)'],
        ['Pendientes',       $r['pendientes'],       'var(--warning-500)'],
        ['No aparecieron',   $r['faltantes'],        'var(--danger-500)'],
        ['En otro lugar',    $r['movidos'],          '#8B5CF6'],
        ['Cambió condición', $r['cambio_condicion'], '#0891B2'],
    ];
    foreach ($tiles as [$lbl, $val, $col]): ?>
        <div class="col-md-2 col-4">
            <div class="sig-card" style="border-bottom:3px solid <?php echo $col; ?>;">
                <div class="sig-card__body" style="text-align:center;padding:var(--sp-3);">
                    <div style="font-size:1.5rem;font-weight:800;color:<?php echo $col; ?>;"><?php echo $val; ?></div>
                    <div style="font-size:.68rem;text-transform:uppercase;letter-spacing:.05em;color:var(--text-secondary);"><?php echo $lbl; ?></div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="anim-slide-up" style="display:flex;gap:var(--sp-2);flex-wrap:wrap;margin-bottom:var(--sp-4);">
    <?php
    $tabs = ['' => 'Todos', 'pendientes' => 'Sin verificar', 'faltantes' => 'No aparecieron', 'diferencias' => 'Con diferencias'];
    foreach ($tabs as $k => $lbl):
        $url = URL_ROOT . '/inventario/verConteo/' . (int)$c->id . ($k ? '?f=' . $k : '');
    ?>
        <a href="<?php echo $url; ?>" class="btn-sig <?php echo $filtro === $k ? 'btn-sig--primary' : 'btn-sig--ghost'; ?>"><?php echo $lbl; ?></a>
    <?php endforeach; ?>
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="20" data-buscar-placeholder="Buscar bien o código…">
    <table class="sig-table">
        <thead><tr>
            <th>Bien</th><th>Ubicación esperada</th><th>Condición esperada</th>
            <th>Hallado</th><th>Diferencias</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($data['detalle'])): ?>
                <tr><td colspan="6" class="sig-table-empty">Sin bienes con ese filtro.</td></tr>
            <?php else: ?>
                <?php foreach ($data['detalle'] as $d): ?>
                    <?php
                    $difs = [];
                    if ($d->hallado === false) $difs[] = '<span class="sig-badge sig-badge--danger">No apareció</span>';
                    if ($d->hallado && $d->hallado_ubicacion && (int)$d->hallado_ubicacion !== (int)$d->esperado_ubicacion)
                        $difs[] = '<span class="sig-badge sig-badge--warning">Está en ' . htmlspecialchars($d->ubic_hallada ?? '?') . '</span>';
                    if ($d->hallado && $d->hallado_condicion && $d->hallado_condicion !== $d->esperado_condicion)
                        $difs[] = '<span class="sig-badge sig-badge--info">Ahora ' . htmlspecialchars($d->hallado_condicion) . '</span>';
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo URL_ROOT; ?>/inventario/detalle/<?php echo (int)$d->id_inventario; ?>" class="cell-strong"><?php echo htmlspecialchars($d->bien); ?></a>
                            <?php if ($d->codigo_bn): ?><br><small style="color:var(--text-tertiary);font-family:var(--font-mono);"><?php echo htmlspecialchars($d->codigo_bn); ?></small><?php endif; ?>
                        </td>
                        <td style="font-size:12.5px;"><?php echo htmlspecialchars($d->ubic_esperada ?? '—'); ?></td>
                        <td style="font-size:12.5px;"><?php echo htmlspecialchars($d->esperado_condicion ?? '—'); ?></td>
                        <td>
                            <?php if ($d->hallado === null): ?>
                                <span class="sig-badge sig-badge--neutral">Sin verificar</span>
                            <?php elseif ($d->hallado): ?>
                                <span class="sig-badge sig-badge--success">Sí</span>
                            <?php else: ?>
                                <span class="sig-badge sig-badge--danger">No</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $difs ? implode(' ', $difs) : '<span style="color:var(--text-tertiary)">—</span>'; ?>
                            <?php if ($d->observaciones): ?><br><small style="color:var(--text-tertiary)"><?php echo htmlspecialchars($d->observaciones); ?></small><?php endif; ?>
                        </td>
                        <td class="col-actions">
                            <?php if ($abierto && $puedeEscribir): ?>
                                <button class="row-action row-action--edit" onclick='verificar(<?php echo htmlspecialchars(json_encode($d), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-check2-square"></i> Verificar</button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($abierto && $puedeEscribir): ?>
<div class="modal fade" id="modalVer" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/verificarConteo" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Verificar bien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_conteo" value="<?php echo (int)$c->id; ?>">
                <input type="hidden" name="id_inventario" id="v_bien">
                <p style="margin-bottom:var(--sp-3);"><strong id="v_nombre"></strong></p>
                <div class="row g-3">
                    <div class="col-12"><div class="sig-field" style="margin:0;">
                        <label class="sig-field__label" style="display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="hallado" id="v_hallado" value="1" checked onchange="vToggle()"> El bien apareció físicamente
                        </label></div></div>
                    <div class="col-md-7" id="v_wrap_ubi"><div class="sig-field"><label class="sig-field__label" for="v_ubi">¿Dónde está?</label>
                        <select name="hallado_ubicacion" id="v_ubi" class="sig-select js-search">
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['ubicaciones'] ?? [] as $u): ?>
                                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->nombre); ?></option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-5" id="v_wrap_cond"><div class="sig-field"><label class="sig-field__label" for="v_cond">Condición real</label>
                        <select name="hallado_condicion" id="v_cond" class="sig-select">
                            <?php foreach (Inventario::CONDICIONES as $x): ?><option value="<?php echo $x; ?>"><?php echo $x; ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="v_obs">Observaciones</label>
                        <textarea name="observaciones" id="v_obs" class="sig-textarea" rows="2"></textarea></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
function vToggle() {
    var on = document.getElementById('v_hallado').checked;
    document.getElementById('v_wrap_ubi').style.display  = on ? '' : 'none';
    document.getElementById('v_wrap_cond').style.display = on ? '' : 'none';
}
function verificar(d) {
    document.getElementById('v_bien').value = d.id_inventario;
    document.getElementById('v_nombre').innerText = d.bien + (d.codigo_bn ? ' (' + d.codigo_bn + ')' : '');
    document.getElementById('v_hallado').checked = (d.hallado === null ? true : !!d.hallado);
    // Se precarga con lo esperado: lo normal es que coincida y solo haya que confirmar.
    document.getElementById('v_ubi').value  = d.hallado_ubicacion || d.esperado_ubicacion || '';
    document.getElementById('v_cond').value = d.hallado_condicion || d.esperado_condicion || 'Bueno';
    document.getElementById('v_obs').value  = d.observaciones || '';
    vToggle();
    if (window.initSearchSelect) window.initSearchSelect(document.getElementById('v_ubi'));
    new bootstrap.Modal(document.getElementById('modalVer')).show();
}
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
