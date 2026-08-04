<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Recepción del Formulario BM-1 — Fase 3 (§2-bis del plan).
 *
 * El BM-1 es un documento ENTRANTE: la Alcaldía lo elabora y se lo devuelve
 * a IMATUR ya codificado. Aquí se registra cada recepción, se adjunta el
 * escaneado y se codifican los bienes que trae, dejando la trazabilidad de
 * en qué formulario vino el código de cada bien.
 */
$pendientes = $data['pendientes'] ?? [];
$lista      = $data['consolidados'] ?? [];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Alcaldía</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Formularios BM-1 recibidos'; ?></h1>
        <p class="page__subtitle">Inventarios consolidados que devuelve la Alcaldía con los códigos ya asignados.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Bienes</a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalBM1">
            <i class="bi bi-inbox"></i> Registrar recepción
        </button>
    </div>
</div>

<div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
    <i class="bi bi-info-circle"></i>
    El BM-1 <strong>no lo genera el sistema</strong>: lo elabora la Alcaldía y se lo entrega a IMATUR.
    Aquí se deja constancia de la recepción y se transcriben los códigos que trae.
</div>

<!-- ── Bienes esperando código ───────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);<?php echo $pendientes ? 'border-left:3px solid var(--warning-500);' : ''; ?>">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-hourglass-split"></i> Bienes esperando codificación (<?php echo count($pendientes); ?>)
        </div>
    </div>
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <?php if (empty($pendientes)): ?>
            <p style="color:var(--text-tertiary);margin:0;">Todos los bienes registrados ya tienen su código de la Alcaldía.</p>
        <?php else: ?>
            <div class="sig-table-wrap">
                <table class="sig-table">
                    <thead><tr><th>Bien</th><th>Categoría</th><th>Ubicación</th><th>Registrado</th><th class="col-actions">Acciones</th></tr></thead>
                    <tbody>
                    <?php foreach ($pendientes as $p): ?>
                        <tr>
                            <td><span class="cell-strong"><?php echo htmlspecialchars($p->nombre); ?></span></td>
                            <td><?php echo htmlspecialchars($p->categoria ?? '—'); ?></td>
                            <td><?php echo htmlspecialchars($p->ubicacion ?? '—'); ?></td>
                            <td style="font-size:12px;color:var(--text-tertiary);"><?php echo htmlspecialchars(substr((string)$p->created_at, 0, 10)); ?></td>
                            <td class="col-actions">
                                <button class="row-action" onclick='codificarDesdeBM1(<?php echo htmlspecialchars(json_encode(["id"=>$p->id,"nombre"=>$p->nombre]), ENT_QUOTES, "UTF-8"); ?>)'>
                                    <i class="bi bi-upc-scan"></i> Codificar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Recepciones registradas ───────────────────────────────────── -->
<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead><tr>
            <th>Recibido</th><th>Fecha del documento</th><th>Referencia</th>
            <th>Bienes codificados</th><th>Observaciones</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($lista)): ?>
                <tr><td colspan="6" class="sig-table-empty">Todavía no se ha registrado ninguna recepción del BM-1.</td></tr>
            <?php else: ?>
                <?php foreach ($lista as $c): ?>
                    <tr>
                        <td class="cell-strong"><?php echo htmlspecialchars($c->fecha_recepcion); ?></td>
                        <td><?php echo htmlspecialchars($c->fecha_documento ?: '—'); ?></td>
                        <td><?php echo htmlspecialchars($c->referencia ?: '—'); ?></td>
                        <td><span class="sig-badge sig-badge--info"><?php echo (int)$c->bienes_codificados; ?></span></td>
                        <td style="font-size:12.5px;color:var(--text-secondary);"><?php echo htmlspecialchars($c->observaciones ?: '—'); ?></td>
                        <td class="col-actions">
                            <?php if (!empty($c->archivo_url)): ?>
                                <a href="<?php echo URL_ROOT; ?>/descarga/bm1/<?php echo (int)$c->id; ?>" target="_blank" class="row-action row-action--view"><i class="bi bi-eye"></i> Ver</a>
                            <?php endif; ?>
                            <a href="<?php echo URL_ROOT; ?>/inventario/eliminarBM1/<?php echo (int)$c->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ══════════ Modal: registrar recepción ══════════ -->
<div class="modal fade" id="modalBM1" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/registrarBM1" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar recepción del BM-1</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-6"><div class="sig-field"><label class="sig-field__label" for="bm1_frec">Fecha de recepción <span class="req">*</span></label>
                        <input type="date" name="fecha_recepcion" id="bm1_frec" class="sig-input" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"></div></div>
                    <div class="col-md-6"><div class="sig-field"><label class="sig-field__label" for="bm1_fdoc">Fecha del documento</label>
                        <input type="date" name="fecha_documento" id="bm1_fdoc" class="sig-input"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="bm1_ref">Referencia</label>
                        <input type="text" name="referencia" id="bm1_ref" class="sig-input" placeholder="Formulario BM-1 · abril 2026"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="bm1_file">Documento escaneado (PDF o imagen)</label>
                        <input type="file" name="documento" id="bm1_file" class="sig-input" accept=".pdf,.jpg,.jpeg,.png">
                        <small style="color:var(--text-tertiary)">Opcional: si llegó en papel, puedes registrarlo ahora y adjuntarlo después.</small></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="bm1_obs">Observaciones</label>
                        <textarea name="observaciones" id="bm1_obs" class="sig-textarea" rows="2"></textarea></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════ Modal: codificar bien ══════════ -->
<div class="modal fade" id="modalCod" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/inventario/codificar" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Codificar bien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="cod_id">
                <p style="margin-bottom:var(--sp-3);">
                    Transcribe el código que la Alcaldía asignó a <strong id="cod_nombre"></strong>.
                </p>
                <div class="row g-3">
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="cod_bm1">BM-1 de procedencia</label>
                        <select name="id_consolidado_bm1" id="cod_bm1" class="sig-select">
                            <option value="">— sin asociar —</option>
                            <?php foreach ($lista as $c): ?>
                                <option value="<?php echo (int)$c->id; ?>">
                                    <?php echo htmlspecialchars($c->fecha_recepcion . ($c->referencia ? ' · ' . $c->referencia : '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-3"><div class="sig-field"><label class="sig-field__label" for="cod_g">Grupo <span class="req">*</span></label>
                        <input type="text" name="codigo_grupo" id="cod_g" class="sig-input" required maxlength="4" placeholder="2"></div></div>
                    <div class="col-3"><div class="sig-field"><label class="sig-field__label" for="cod_sg">Sub-grupo <span class="req">*</span></label>
                        <input type="text" name="codigo_subgrupo" id="cod_sg" class="sig-input" required maxlength="4" placeholder="01"></div></div>
                    <div class="col-3"><div class="sig-field"><label class="sig-field__label" for="cod_sec">Sección <span class="req">*</span></label>
                        <input type="text" name="codigo_seccion" id="cod_sec" class="sig-input" required maxlength="6" placeholder="108"></div></div>
                    <div class="col-3"><div class="sig-field"><label class="sig-field__label" for="cod_ord">N° orden <span class="req">*</span></label>
                        <input type="text" name="nro_orden" id="cod_ord" class="sig-input" required maxlength="10" placeholder="084"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="cod_fecha">Fecha de verificación</label>
                        <input type="date" name="fecha_verificacion" id="cod_fecha" class="sig-input" value="<?php echo date('Y-m-d'); ?>"></div></div>
                    <div class="col-12"><div class="sig-alert sig-alert--info" style="margin:0;">
                        Quedará como <strong><span id="cod_preview">— incompleto —</span></strong> y el bien pasará a <strong>Activo</strong>.
                    </div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-patch-check"></i> Codificar</button>
            </div>
        </form>
    </div>
</div>

<script>
function codificarDesdeBM1(item) {
    document.getElementById('cod_id').value = item.id;
    document.getElementById('cod_nombre').innerText = item.nombre || '';
    ['cod_g', 'cod_sg', 'cod_sec', 'cod_ord'].forEach(function (id) { document.getElementById(id).value = ''; });
    codPreview();
    new bootstrap.Modal(document.getElementById('modalCod')).show();
}
function codPreview() {
    var p = ['cod_g', 'cod_sg', 'cod_sec', 'cod_ord'].map(function (id) {
        return (document.getElementById(id).value || '').trim();
    });
    document.getElementById('cod_preview').innerText =
        p.every(function (x) { return x !== ''; }) ? p.join('-') : '— incompleto —';
}
['cod_g', 'cod_sg', 'cod_sec', 'cod_ord'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', codPreview);
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
