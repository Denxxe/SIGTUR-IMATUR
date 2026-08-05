<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Suficiencia de bienes por departamento (B-63).
 *
 * El cliente aclaró que NO es stock de consumibles: es saber si **alcanzan**
 * los bienes, medido **por el número de empleados** de cada departamento.
 *
 * Solo se evalúan las categorías con dotación definida. Las que no se
 * reparten por persona (herramientas, material turístico, bienes culturales)
 * no tienen fila y quedan fuera del análisis a propósito.
 */
$filas = $data['analisis'] ?? [];
$puedeEscribir = InventarioController::puedeEscribir();
$conDeficit = array_filter($filas, fn($f) => $f['deficit'] > 0);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Suficiencia</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Suficiencia de bienes'; ?></h1>
        <p class="page__subtitle">¿Alcanzan los bienes para el personal de cada departamento?</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Bienes</a>
        <?php if ($puedeEscribir): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalDot">
                <i class="bi bi-sliders"></i> Definir dotación
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-alert <?php echo $conDeficit ? 'sig-alert--warning' : 'sig-alert--info'; ?> anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-<?php echo $conDeficit ? 'exclamation-triangle' : 'check-circle'; ?>"></i>
    <?php if ($conDeficit): ?>
        <strong><?php echo count($conDeficit); ?></strong> combinación(es) departamento/categoría por debajo de lo esperado.
    <?php else: ?>
        Todos los departamentos tienen la dotación esperada según su personal.
    <?php endif; ?>
    Los bienes se cuentan por dónde <em>están</em> (su ubicación), excluyendo el depósito y los dados de baja, extraviados o robados.
</div>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="20" data-buscar-placeholder="Buscar departamento o categoría…">
    <table class="sig-table">
        <thead><tr>
            <th>Departamento</th><th>Categoría</th><th style="text-align:center;">Empleados</th>
            <th style="text-align:center;">Por empleado</th><th style="text-align:center;">Debería haber</th>
            <th style="text-align:center;">Hay</th><th style="text-align:center;">Faltan</th>
        </tr></thead>
        <tbody>
            <?php if (empty($filas)): ?>
                <tr><td colspan="7" class="sig-table-empty">
                    No hay nada que analizar todavía: hacen falta departamentos con personal y al menos una dotación definida.
                </td></tr>
            <?php else: ?>
                <?php foreach ($filas as $f): ?>
                    <tr>
                        <td class="cell-strong"><?php echo htmlspecialchars($f['departamento']); ?></td>
                        <td><?php echo htmlspecialchars($f['categoria']); ?></td>
                        <td style="text-align:center;"><?php echo $f['empleados']; ?></td>
                        <td style="text-align:center;color:var(--text-tertiary);"><?php echo rtrim(rtrim(number_format($f['ratio'], 2, ',', ''), '0'), ','); ?></td>
                        <td style="text-align:center;"><?php echo $f['deberia']; ?></td>
                        <td style="text-align:center;"><?php echo $f['hay']; ?></td>
                        <td style="text-align:center;">
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
    <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-sliders"></i> Dotación esperada por empleado</div></div>
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <?php if (empty($data['dotaciones'])): ?>
            <p style="color:var(--text-tertiary);margin:0;">Sin dotaciones definidas. Mientras no haya ninguna, no hay nada que evaluar.</p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr><th>Categoría</th><th style="text-align:center;">Unidades por empleado</th><th>Criterio</th><th class="col-actions">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($data['dotaciones'] as $d): ?>
                        <tr>
                            <td class="cell-strong"><?php echo htmlspecialchars($d->categoria); ?></td>
                            <td style="text-align:center;"><?php echo rtrim(rtrim(number_format((float)$d->unidades_por_empleado, 2, ',', ''), '0'), ','); ?></td>
                            <td style="font-size:12.5px;color:var(--text-secondary);"><?php echo htmlspecialchars($d->observaciones ?: '—'); ?></td>
                            <td class="col-actions">
                                <?php if ($puedeEscribir): ?>
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
                <h5 class="modal-title">Definir dotación por empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="dot_cat">Categoría <span class="req">*</span></label>
                        <select name="id_categoria" id="dot_cat" class="sig-select js-search" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['sinDotacion'] ?? [] as $c): ?>
                                <option value="<?php echo (int)$c->id; ?>"><?php echo htmlspecialchars($c->nombre); ?></option>
                            <?php endforeach; ?>
                            <?php foreach ($data['dotaciones'] ?? [] as $d): ?>
                                <option value="<?php echo (int)$d->id_categoria; ?>"><?php echo htmlspecialchars($d->categoria); ?> (ya definida — se actualiza)</option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-5"><div class="sig-field"><label class="sig-field__label" for="dot_u">Unidades por empleado <span class="req">*</span></label>
                        <input type="number" step="0.01" min="0.01" max="99" name="unidades_por_empleado" id="dot_u" class="sig-input" required value="1">
                        <small style="color:var(--text-tertiary)">Ej. 2 = silla + escritorio. 0,5 = uno por cada dos personas.</small></div></div>
                    <div class="col-md-7"><div class="sig-field"><label class="sig-field__label" for="dot_obs">Criterio</label>
                        <input type="text" name="observaciones" id="dot_obs" class="sig-input" placeholder="Un equipo por empleado"></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
