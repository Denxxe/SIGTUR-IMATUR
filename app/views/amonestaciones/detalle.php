<?php require_once '../app/views/inc/header.php';
$e = $data['empleado'];
$eid = (int)$e->id;
$limite = (int)($data['limite'] ?? 3);
$nAmones = count($data['amonestaciones'] ?? []);
$nFaltas = count($data['faltas'] ?? []);
$ffecha = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '—';
$contratado = ($e->tipo_contrato === 'Contratado');
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Disciplina</div>
        <h1 class="page__title"><?php echo htmlspecialchars($e->nombre . ' ' . $e->apellido); ?></h1>
        <p class="page__subtitle"><?php echo htmlspecialchars($e->tipo_contrato ?? ''); ?> · <?php echo htmlspecialchars($e->departamento ?? ''); ?> · C.I. <?php echo htmlspecialchars($e->cedula ?? ''); ?></p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/amonestaciones/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<?php if ($nAmones >= $limite): ?>
    <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);border-left:4px solid var(--danger,#EF4444)">
        <div class="sig-card__body" style="padding:var(--sp-4)">
            <strong style="color:var(--danger,#EF4444)"><i class="bi bi-exclamation-octagon"></i> Causa de despido:</strong>
            este empleado acumula <?php echo $nAmones; ?> amonestaciones (límite <?php echo $limite; ?>)<?php echo $contratado ? '' : ' — nota: el despido por amonestaciones aplica a Contratados'; ?>.
        </div>
    </div>
<?php elseif ($nAmones === $limite - 1): ?>
    <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);border-left:4px solid var(--warning,#F59E0B)">
        <div class="sig-card__body" style="padding:var(--sp-4)">
            <strong style="color:var(--warning,#D97706)"><i class="bi bi-exclamation-triangle"></i> En riesgo:</strong>
            una amonestación más alcanza el límite de <?php echo $limite; ?>.
        </div>
    </div>
<?php endif; ?>

<div class="row g-4 anim-slide-up">
    <!-- Faltas -->
    <div class="col-md-6">
        <div class="sig-table-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px">
                <h5 style="margin:0"><i class="bi bi-exclamation-circle"></i> Faltas (<?php echo $nFaltas; ?>)</h5>
                <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" data-bs-toggle="modal" data-bs-target="#modalFalta"><i class="bi bi-plus-lg"></i> Agregar</button>
            </div>
            <table class="sig-table">
                <thead><tr><th>Fecha</th><th>Motivo</th><th class="col-actions">Acción</th></tr></thead>
                <tbody>
                    <?php if (empty($data['faltas'])): ?>
                        <tr><td colspan="3" class="sig-table-empty">Sin faltas registradas.</td></tr>
                    <?php else: foreach ($data['faltas'] as $f): ?>
                        <tr>
                            <td class="cell-strong"><?php echo $ffecha($f->fecha); ?></td>
                            <td style="font-size:13px"><?php echo htmlspecialchars($f->motivo ?? '—'); ?></td>
                            <td class="col-actions"><a href="<?php echo URL_ROOT; ?>/amonestaciones/eliminarFalta/<?php echo $f->id; ?>/<?php echo $eid; ?>" class="row-action row-action--del" onclick="return confirm('¿Eliminar esta falta?')"><i class="bi bi-trash"></i></a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <!-- Amonestaciones -->
    <div class="col-md-6">
        <div class="sig-table-wrap">
            <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px">
                <h5 style="margin:0"><i class="bi bi-flag"></i> Amonestaciones (<?php echo $nAmones; ?>/<?php echo $limite; ?>)</h5>
                <button type="button" class="btn-sig btn-sig--primary btn-sig--sm" data-bs-toggle="modal" data-bs-target="#modalAmonestacion"><i class="bi bi-plus-lg"></i> Agregar</button>
            </div>
            <table class="sig-table">
                <thead><tr><th>Fecha</th><th>Motivo</th><th class="col-actions">Acción</th></tr></thead>
                <tbody>
                    <?php if (empty($data['amonestaciones'])): ?>
                        <tr><td colspan="3" class="sig-table-empty">Sin amonestaciones.</td></tr>
                    <?php else: foreach ($data['amonestaciones'] as $a): ?>
                        <tr>
                            <td class="cell-strong"><?php echo $ffecha($a->fecha); ?></td>
                            <td style="font-size:13px"><?php echo htmlspecialchars($a->motivo ?? ''); ?></td>
                            <td class="col-actions"><a href="<?php echo URL_ROOT; ?>/amonestaciones/eliminarAmonestacion/<?php echo $a->id; ?>/<?php echo $eid; ?>" class="row-action row-action--del" onclick="return confirm('¿Eliminar esta amonestación?')"><i class="bi bi-trash"></i></a></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Falta (empleado fijo) -->
<div class="modal fade" id="modalFalta" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/amonestaciones/registrarFalta" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar falta</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <div class="sig-field mb-3"><label class="sig-field__label">Fecha <span class="req">*</span></label>
                    <input type="date" name="fecha" class="sig-input" required value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="sig-field"><label class="sig-field__label">Motivo / observación</label>
                    <textarea name="motivo" class="sig-textarea" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Amonestación (empleado fijo) -->
<div class="modal fade" id="modalAmonestacion" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/amonestaciones/registrarAmonestacion" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar amonestación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <div class="sig-field mb-3"><label class="sig-field__label">Fecha <span class="req">*</span></label>
                    <input type="date" name="fecha" class="sig-input" required value="<?php echo date('Y-m-d'); ?>"></div>
                <div class="sig-field"><label class="sig-field__label">Motivo <span class="req">*</span></label>
                    <textarea name="motivo" class="sig-textarea" rows="3" required></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
