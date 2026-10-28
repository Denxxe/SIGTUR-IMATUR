<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Suficiencia de bienes por departamento (B-63, mig. 091).
 *
 * El cliente aclaró que NO es stock de consumibles: es saber si **alcanzan**
 * los bienes, y que la cantidad esperada es **por departamento** («cada
 * departamento tendrá una cantidad de bienes»), no por empleado.
 *
 * Solo se evalúan los pares departamento + categoría con dotación definida.
 */
$filas = $data['analisis'] ?? [];
$puedeEscribir = InventarioController::puedeEscribir();
$conDeficit = array_filter($filas, fn($f) => $f['deficit'] > 0);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Suficiencia</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Suficiencia de bienes'; ?></h1>
        <p class="page__subtitle">¿Tiene cada departamento los bienes que le corresponden?</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Bienes</a>
        <?php if ($puedeEscribir): ?>
            <button type="button" class="btn-sig btn-sig--primary" onclick="nuevaDot()">
                <i class="bi bi-sliders"></i> Definir dotación
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-alert <?php echo $conDeficit ? 'sig-alert--warning' : 'sig-alert--info'; ?> anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-<?php echo $conDeficit ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
    <?php if ($conDeficit): ?>
        <strong><?php echo count($conDeficit); ?></strong> combinación(es) departamento/categoría por debajo de su dotación.
    <?php elseif ($filas): ?>
        Todos los departamentos tienen la dotación definida.
    <?php else: ?>
        Aún no hay dotaciones definidas.
    <?php endif; ?>
    Los bienes se cuentan por dónde <em>están</em> (su ubicación), excluyendo el depósito y los dados de baja, extraviados o robados.
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="20" data-buscar-placeholder="Buscar departamento o categoría…">
    <table class="sig-table">
        <thead><tr>
            <th>Departamento</th><th>Categoría</th><th class="text-center">Empleados</th>
            <th class="text-center">Debería haber</th>
            <th class="text-center">Hay</th><th class="text-center">Faltan</th>
        </tr></thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="6" class="sig-table-empty">
                    No hay nada que analizar todavía: define cuántos bienes de cada categoría debería tener cada departamento.
                </td></tr>
            <?php else: ?>
                <?php foreach ($filas as $f): ?>
                    <tr>
                        <td class="cell-strong"><?php echo htmlspecialchars($f['departamento']); ?></td>
                        <td><?php echo htmlspecialchars($f['categoria']); ?></td>
                        <td class="u-ta-center u-c-text-tertiary"><?php echo $f['empleados']; ?></td>
                        <td class="text-center"><?php echo $f['deberia']; ?></td>
                        <td class="text-center"><?php echo $f['hay']; ?></td>
                        <td class="text-center">
                            <?php if ($f['deficit'] > 0): ?>
                                <span class="sig-badge sig-badge--danger"><?php echo $f['deficit']; ?></span>
                            <?php else: ?>
                                <span class="sig-badge sig-badge--success">—</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Dotaciones definidas -->
<div class="sig-card anim-slide-up" style="margin-top:var(--sp-4);margin-bottom:var(--sp-5);">
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-sliders"></i> Dotación esperada por departamento</div></div>
    <div class="sig-card__body u-p-sp-4">
        <?php if (empty($data['dotaciones'])): ?>
            <p class="u-c-text-tertiary u-m-0">Sin dotaciones definidas. Mientras no haya ninguna, no hay nada que evaluar.</p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr><th>Departamento</th><th>Categoría</th><th class="text-center">Unidades</th><th>Criterio</th><th class="col-actions">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['dotaciones'] as $d): ?>
                        <tr>
                            <td class="cell-strong"><?php echo htmlspecialchars($d->departamento); ?></td>
                            <td><?php echo htmlspecialchars($d->categoria); ?></td>
                            <td class="text-center"><?php echo (int)$d->unidades; ?></td>
                            <td class="u-fs-12_5px u-c-text-secondary"><?php echo htmlspecialchars($d->observaciones ?: '—'); ?></td>
                            <td class="col-actions">
                                <?php if ($puedeEscribir): ?>
                                    <button class="row-action row-action--edit" onclick='editarDot(<?php echo htmlspecialchars(json_encode([
                                        "dep" => (int)$d->id_departamento, "cat" => (int)$d->id_categoria,
                                        "u" => (int)$d->unidades, "obs" => $d->observaciones ?? ""]), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                                    <a href="<?php echo URL_ROOT; ?>/inventario/eliminarDotacion/<?php echo (int)$d->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Quitar</a>
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

<?php if ($puedeEscribir): ?>
<div class="modal fade" id="modalDot" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/guardarDotacion" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Dotación del departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="dot_dep">Departamento <span class="req">*</span></label>
                        <select name="id_departamento" id="dot_dep" class="sig-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['departamentos'] ?? [] as $dep): ?>
                                <option value="<?php echo (int)$dep->id; ?>"><?php echo str_repeat('— ', (int)($dep->nivel ?? 0)) . htmlspecialchars($dep->nombre); ?></option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="dot_cat">Categoría <span class="req">*</span></label>
                        <select name="id_categoria" id="dot_cat" class="sig-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['categorias'] ?? [] as $c): ?>
                                <option value="<?php echo (int)$c->id; ?>"><?php echo htmlspecialchars($c->nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small class="u-c-text-tertiary">Si el par ya tiene dotación, se actualiza.</small></div></div>
                    <div class="col-md-5"><div class="sig-field"><label class="sig-field__label" for="dot_u">Unidades <span class="req">*</span></label>
                        <input type="number" step="1" min="1" max="<?php echo DotacionInventario::UNIDADES_MAX; ?>" name="unidades" id="dot_u" class="sig-input" required value="1"></div></div>
                    <div class="col-md-7"><div class="sig-field"><label class="sig-field__label" for="dot_obs">Criterio</label>
                        <input type="text" name="observaciones" id="dot_obs" class="sig-input" placeholder="Un aire acondicionado por oficina"></div></div>
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
function abrirDot(v) {
    document.getElementById('dot_dep').value = v.dep || '';
    document.getElementById('dot_cat').value = v.cat || '';
    document.getElementById('dot_u').value   = v.u || 1;
    document.getElementById('dot_obs').value = v.obs || '';
    new bootstrap.Modal(document.getElementById('modalDot')).show();
}
function nuevaDot()   { abrirDot({}); }
function editarDot(v) { abrirDot(v); }
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
