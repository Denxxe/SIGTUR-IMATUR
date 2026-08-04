<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Mantenimiento preventivo programado (R-7 · B-56).
 *
 * Distinto del mantenimiento correctivo (que registra una reparación ya
 * ocurrida): esto es el calendario — cada cuántos meses toca y cuándo es
 * la próxima vez. Al retornar de un mantenimiento, el sistema corre solo
 * la fecha al siguiente ciclo.
 */
$planes = $data['planes'] ?? [];
$aviso  = (int)($data['dias_aviso'] ?? 15);
$puedeEscribir = InventarioController::puedeEscribir();
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Preventivo</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Mantenimiento preventivo'; ?></h1>
        <p class="page__subtitle">Calendario de mantenimiento para aires, impresoras, computadoras y demás equipos.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Bienes</a>
        <?php if ($puedeEscribir): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalPlan">
                <i class="bi bi-calendar-plus"></i> Programar
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-info-circle"></i>
    El sistema avisa en el Centro de Alertas cuando faltan <strong><?php echo $aviso; ?> días</strong> o menos.
    Ese umbral se cambia en <a href="<?php echo URL_ROOT; ?>/config/index">Configuración</a>.
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15">
    <table class="sig-table">
        <thead><tr>
            <th>Bien</th><th>Ubicación</th><th>Frecuencia</th>
            <th>Último</th><th>Próximo</th><th>Estado</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($planes)): ?>
                <tr><td colspan="7" class="sig-table-empty">No hay equipos con mantenimiento preventivo programado.</td></tr>
            <?php else: ?>
                <?php foreach ($planes as $p): ?>
                    <?php
                    $dias = (int)$p->dias_restantes;
                    if ($dias < 0)            { $cls = 'sig-badge--danger';  $txt = 'Vencido hace ' . abs($dias) . ' d'; }
                    elseif ($dias <= $aviso)  { $cls = 'sig-badge--warning'; $txt = 'En ' . $dias . ' d'; }
                    else                      { $cls = 'sig-badge--success'; $txt = 'En ' . $dias . ' d'; }
                    ?>
                    <tr>
                        <td>
                            <a href="<?php echo URL_ROOT; ?>/inventario/detalle/<?php echo (int)$p->id_inventario; ?>" class="cell-strong"><?php echo htmlspecialchars($p->bien); ?></a>
                            <?php if ($p->codigo_bn): ?><br><small style="color:var(--text-tertiary);font-family:var(--font-mono);"><?php echo htmlspecialchars($p->codigo_bn); ?></small><?php endif; ?>
                        </td>
                        <td style="font-size:12.5px;"><?php echo htmlspecialchars($p->ubicacion ?? '—'); ?></td>
                        <td><?php echo PlanMantenimiento::FRECUENCIAS[(int)$p->frecuencia_meses] ?? ((int)$p->frecuencia_meses . ' meses'); ?></td>
                        <td style="font-size:12.5px;"><?php echo htmlspecialchars($p->ultima_fecha ?: '—'); ?></td>
                        <td class="cell-strong"><?php echo htmlspecialchars($p->proxima_fecha); ?></td>
                        <td><span class="sig-badge <?php echo $cls; ?>"><?php echo $txt; ?></span></td>
                        <td class="col-actions">
                            <?php if ($puedeEscribir): ?>
                                <button class="row-action row-action--edit" onclick='editarPlan(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/inventario/eliminarPlan/<?php echo (int)$p->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Quitar</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($puedeEscribir): ?>
<div class="modal fade" id="modalPlan" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/guardarPlan" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="planTitulo">Programar mantenimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="plan_bien">Bien <span class="req">*</span></label>
                        <select name="id_inventario" id="plan_bien" class="sig-select js-search" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['inventario'] ?? [] as $i): ?>
                                <option value="<?php echo $i->id; ?>"><?php echo htmlspecialchars(($i->codigo_bn ? $i->codigo_bn . ' · ' : '') . $i->nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:var(--text-tertiary)">Si el bien ya tiene un plan, se actualiza.</small></div></div>
                    <div class="col-md-6"><div class="sig-field"><label class="sig-field__label" for="plan_frec">Frecuencia <span class="req">*</span></label>
                        <select name="frecuencia_meses" id="plan_frec" class="sig-select" required>
                            <?php foreach (PlanMantenimiento::FRECUENCIAS as $m => $lbl): ?>
                                <option value="<?php echo $m; ?>" <?php echo $m === 6 ? 'selected' : ''; ?>><?php echo $lbl; ?> (<?php echo $m; ?> meses)</option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-6"><div class="sig-field"><label class="sig-field__label" for="plan_prox">Próximo mantenimiento <span class="req">*</span></label>
                        <input type="date" name="proxima_fecha" id="plan_prox" class="sig-input" required></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="plan_desc">Qué se le hace</label>
                        <textarea name="descripcion" id="plan_desc" class="sig-textarea" rows="2" placeholder="Limpieza de filtros, carga de gas…"></textarea></div></div>
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
function editarPlan(p) {
    document.getElementById('planTitulo').innerText = 'Editar plan — ' + (p.bien || '');
    document.getElementById('plan_bien').value = p.id_inventario;
    document.getElementById('plan_frec').value = p.frecuencia_meses;
    document.getElementById('plan_prox').value = p.proxima_fecha;
    document.getElementById('plan_desc').value = p.descripcion || '';
    if (window.initSearchSelect) window.initSearchSelect(document.getElementById('plan_bien'));
    new bootstrap.Modal(document.getElementById('modalPlan')).show();
}
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
