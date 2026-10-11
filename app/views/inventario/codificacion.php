<?php require_once '../app/views/inc/header.php';
/**
 * Codificación propia de bienes (R-12 · C-1…C-4, mig. 087).
 * Parámetros de la secuencia de N° de orden + catálogo de grupo-subgrupo-sección.
 * Todo es configurable: el motor queda listo y se enciende cuando el cliente
 * entregue el punto de partida (B-73) y el catálogo (B-75).
 */
$p   = $data['params'];
$esc = !empty($data['puedeEscribir']);
$falta = $data['faltantes'] ?? [];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow"><a href="<?php echo URL_ROOT; ?>/inventario/index" style="text-decoration:none;">Inventario</a> · Codificación</div>
        <h1 class="page__title">
            <?php echo $data['titulo'] ?? 'Codificación de bienes'; ?>
            <span class="sig-badge <?php echo $p['activa'] ? 'sig-badge--success' : 'sig-badge--neutral'; ?>">
                <?php echo $p['activa'] ? 'Codificación propia encendida' : 'Codificación propia apagada'; ?>
            </span>
        </h1>
        <p class="page__subtitle">
            Desde el procedimiento notificado por la Alcaldía el 2026-09-02, <strong>IMATUR asigna el código</strong>
            de sus bienes continuando la secuencia que deje la Alcaldía en su última revisión. Mientras la
            codificación propia esté apagada, el código se sigue <strong>transcribiendo del BM-1</strong>.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<?php if (!$p['activa'] && $falta): ?>
<div class="sig-alert sig-alert--warning anim-slide-up u-mb-sp-4">
    <i class="bi bi-hourglass-split"></i>
    <div>
        <strong>Para encenderla falta:</strong>
        <ul class="u-pl-18px" style="margin:6px 0 0;">
            <?php foreach ($falta as $f): ?><li><?php echo htmlspecialchars($f); ?>.</li><?php endforeach; ?>
        </ul>
        Ambos dependen de la última codificación de la Alcaldía, que todavía no se ha hecho.
    </div>
</div>
<?php endif; ?>

<div class="row">
    <div class="col-lg-5">
        <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head"><div class="sig-card__title"><i class="bi bi-sliders"></i> Secuencia del N° de orden</div></div>
            <div class="sig-card__body">
                <div class="u-gap-sp-4 u-mb-sp-4 u-fwr-wrap" style="display:flex;">
                    <div>
                        <div class="u-fs-11px u-tt-uppercase u-ls-_06em u-c-text-tertiary">Siguiente N° de orden</div>
                        <div class="u-fs-26px u-fw-800 u-fvn-tabular-nums u-ff-font-mono"><?php echo htmlspecialchars($data['siguiente']); ?></div>
                        <small class="u-c-text-tertiary">
                            <?php echo $p['por_clasificacion'] ? 'En la secuencia global; con secuencia por clasificación depende de la elegida.' : 'El mayor entre el punto de partida y lo ya registrado, más uno.'; ?>
                        </small>
                    </div>
                </div>

                <form action="<?php echo URL_ROOT; ?>/inventario/guardarCodificacion" method="POST">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="cf_inicio">Punto de partida (B-73)</label>
                        <input type="number" min="0" step="1" name="inicio" id="cf_inicio" class="sig-input" <?php echo $esc ? '' : 'disabled'; ?>
                               value="<?php echo $p['inicio'] === null ? '' : (int)$p['inicio']; ?>" placeholder="Ej: 171">
                        <small class="u-c-text-tertiary u-fs-12px">
                            El <strong>último N° de orden</strong> que asigne la Alcaldía en su última revisión: el mayor de todo
                            su listado, no el último de una hoja (el listado va ordenado por departamento, B-72).
                        </small>
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="cf_digitos">Dígitos del N° de orden</label>
                        <input type="number" min="1" max="8" step="1" name="digitos" id="cf_digitos" class="sig-input" <?php echo $esc ? '' : 'disabled'; ?>
                               value="<?php echo (int)$p['digitos']; ?>">
                        <small class="u-c-text-tertiary u-fs-12px">
                            El BM-1 usa 3 (<em>084</em>). Si la secuencia pasa de 999, el número se escribe completo (<em>1000</em>) — o se sube a 4 aquí (B-74).
                        </small>
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="cf_porclase">Secuencia (B-74)</label>
                        <select name="por_clasificacion" id="cf_porclase" class="sig-select" <?php echo $esc ? '' : 'disabled'; ?>>
                            <option value="0"<?php echo !$p['por_clasificacion'] ? ' selected' : ''; ?>>Una sola para todo IMATUR</option>
                            <option value="1"<?php echo $p['por_clasificacion'] ? ' selected' : ''; ?>>Una por cada grupo-subgrupo-sección</option>
                        </select>
                    </div>
                    <div class="sig-field mb-3">
                        <label class="u-gap-8px u-ai-center u-cur-pointer" style="display:flex;" for="cf_activa">
                            <input type="checkbox" name="activa" id="cf_activa" value="1" <?php echo $p['activa'] ? 'checked' : ''; ?> <?php echo $esc ? '' : 'disabled'; ?>>
                            <strong>Encender la codificación propia</strong>
                        </label>
                        <small class="u-c-text-tertiary u-fs-12px u-mt-4px" style="display:block;">
                            Encendida, el botón <em>Codificar</em> propone el siguiente número y el bien queda «codificado por IMATUR».
                            Lo que llegue en un BM-1 se sigue transcribiendo como «de la Alcaldía». Ningún código se recicla (B-76).
                        </small>
                    </div>
                    <?php if ($esc): ?>
                        <div class="u-ta-right"><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button></div>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="sig-card anim-slide-up" id="catalogo" style="margin-bottom:var(--sp-6);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-diagram-3"></i> Catálogo de clasificaciones (B-75)</div>
                <?php if ($esc): ?>
                    <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" onclick="nuevaClasif()"><i class="bi bi-plus-lg"></i> Agregar</button>
                <?php endif; ?>
            </div>
            <div class="sig-card__body u-p-0">
                <div class="u-p-10px-14px u-fs-12_5px u-c-text-secondary u-bdb-1px-solid-border-subtle">
                    Los grupos, sub-grupos y secciones con los que IMATUR puede codificar. La Alcaldía aún no ha entregado
                    su lista: hoy solo se conoce <strong>2-01-108</strong>, que en el BM-1 cubre sillas, mesas, aires y router.
                    Recuerda que este código <strong>no clasifica</strong>: para los reportes se usa la categoría interna.
                </div>
                <div class="sig-table-wrap" data-tabla-buscable data-por-pagina="15" data-titulo-export="Catálogo de clasificaciones de bienes">
                    <table class="sig-table">
                        <thead><tr><th>Código</th><th>Descripción</th><th class="text-center">Bienes</th><th>Estado</th><?php if ($esc): ?><th class="col-actions">Acciones</th><?php endif; ?></tr></thead>
                        <tbody>
                            <?php if (empty($data['clasificaciones'])): ?>
                                <tr><td colspan="<?php echo $esc ? 5 : 4; ?>" class="sig-table-empty">Sin clasificaciones.</td></tr>
                            <?php else: foreach ($data['clasificaciones'] as $c): $vig = ($c->is_active === true || $c->is_active === 't' || $c->is_active === 1); ?>
                                <tr<?php echo $vig ? '' : ' style="opacity:.55;"'; ?>>
                                    <td class="cell-strong" style="font-family:var(--font-mono);"><?php echo htmlspecialchars($c->grupo . '-' . $c->subgrupo . '-' . $c->seccion); ?></td>
                                    <td><?php echo htmlspecialchars($c->descripcion ?? '—'); ?></td>
                                    <td class="text-center"><?php echo (int)$c->bienes; ?></td>
                                    <td><span class="sig-badge <?php echo $vig ? 'sig-badge--success' : 'sig-badge--neutral'; ?>"><?php echo $vig ? 'Vigente' : 'De baja'; ?></span></td>
                                    <?php if ($esc): ?>
                                    <td class="col-actions">
                                        <button type="button" class="row-action row-action--edit" onclick='editarClasif(<?php echo json_encode($c, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                                        <form action="<?php echo URL_ROOT; ?>/inventario/vigenciaClasificacion" method="POST" style="display:inline;">
                                            <input type="hidden" name="id" value="<?php echo (int)$c->id; ?>">
                                            <input type="hidden" name="vigente" value="<?php echo $vig ? '0' : '1'; ?>">
                                            <button type="submit" class="row-action"><i class="bi <?php echo $vig ? 'bi-slash-circle' : 'bi-arrow-counterclockwise'; ?>"></i> <?php echo $vig ? 'Dar de baja' : 'Reactivar'; ?></button>
                                        </form>
                                    </td>
                                    <?php endif; ?>
                                </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if ($esc): ?>
<div class="modal fade" id="modalClasif" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/guardarClasificacion" method="POST" class="modal-content">
            <input type="hidden" name="id" id="cl_id">
            <div class="modal-header">
                <h5 class="modal-title" id="modalClasifLabel">Clasificación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-4"><div class="sig-field"><label class="sig-field__label" for="cl_g">Grupo <span class="req">*</span></label>
                        <input type="text" name="grupo" id="cl_g" class="sig-input" required maxlength="4" pattern="\d{1,4}" placeholder="2"></div></div>
                    <div class="col-4"><div class="sig-field"><label class="sig-field__label" for="cl_sg">Sub-grupo <span class="req">*</span></label>
                        <input type="text" name="subgrupo" id="cl_sg" class="sig-input" required maxlength="4" pattern="\d{1,4}" placeholder="01"></div></div>
                    <div class="col-4"><div class="sig-field"><label class="sig-field__label" for="cl_sec">Sección <span class="req">*</span></label>
                        <input type="text" name="seccion" id="cl_sec" class="sig-input" required maxlength="6" pattern="\d{1,6}" placeholder="108"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="cl_desc">Descripción</label>
                        <input type="text" name="descripcion" id="cl_desc" class="sig-input" maxlength="200" data-nombre-libre placeholder="La que figure en el catálogo de la Alcaldía"></div></div>
                </div>
                <small class="u-c-text-tertiary u-fs-12px" style="display:block; margin-top:8px;">
                    Si ya hay bienes codificados con esta clasificación, su código no se puede cambiar (solo la descripción).
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
<script>
    function nuevaClasif() {
        document.getElementById('modalClasifLabel').innerText = 'Agregar clasificación';
        document.querySelector('#modalClasif form').reset();
        document.getElementById('cl_id').value = '';
        new bootstrap.Modal(document.getElementById('modalClasif')).show();
    }
    function editarClasif(c) {
        document.getElementById('modalClasifLabel').innerText = 'Editar ' + c.grupo + '-' + c.subgrupo + '-' + c.seccion;
        document.getElementById('cl_id').value   = c.id;
        document.getElementById('cl_g').value    = c.grupo;
        document.getElementById('cl_sg').value   = c.subgrupo;
        document.getElementById('cl_sec').value  = c.seccion;
        document.getElementById('cl_desc').value = c.descripcion || '';
        new bootstrap.Modal(document.getElementById('modalClasif')).show();
    }
</script>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
