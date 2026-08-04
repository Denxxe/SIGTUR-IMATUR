<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Bienes / Inventario — Fase 1 (mig. 062), ver docs/PLAN_MODULO_BIENES.md.
 *
 * El listado muestra el inventario ACTIVO. Los dados de baja salen de aquí
 * (B-38) y se consultan en su propia pestaña; los que están en mantenimiento
 * SÍ aparecen, marcados por su estatus (B-34).
 *
 * El código oficial no se teclea al dar de alta: lo asigna la Alcaldía y se
 * transcribe con el botón "Codificar" al recibir el BM-1.
 */
$ver     = $data['ver'] ?? '';
$resumen = $data['resumen'] ?? [];
$hayFiltro = ($data['f_categoria'] ?? 0) || ($data['f_ubicacion'] ?? 0)
          || ($data['f_condicion'] ?? '') !== '' || ($data['f_estatus'] ?? '') !== ''
          || ($data['f_origen'] ?? '') !== '';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Bienes Nacionales</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Inventario Institucional'; ?></h1>
        <p class="page__subtitle">Registro y control de los bienes de la institución. El código oficial lo asigna la Alcaldía.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/inventario/consolidados" class="btn-sig btn-sig--ghost">
            <i class="bi bi-inbox"></i> BM-1 recibidos
        </a>
        <a href="<?php echo URL_ROOT; ?>/inventario/conteos" class="btn-sig btn-sig--ghost">
            <i class="bi bi-clipboard-check"></i> Conteos
        </a>
        <a href="<?php echo URL_ROOT; ?>/inventario/planMantenimiento" class="btn-sig btn-sig--ghost">
            <i class="bi bi-tools"></i> Preventivo
        </a>
        <a href="<?php echo URL_ROOT; ?>/inventario/etiquetas" target="_blank" class="btn-sig btn-sig--ghost">
            <i class="bi bi-upc-scan"></i> Etiquetas
        </a>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInv" onclick="nuevoInv()">
            <i class="bi bi-plus-circle"></i> Registrar Bien
        </button>
    </div>
</div>

<!-- Pestañas: activo · pendientes de codificación · desincorporados -->
<div class="anim-slide-up" style="display:flex;gap:var(--sp-2);flex-wrap:wrap;margin-bottom:var(--sp-4);">
    <?php
    $pend = (int)($resumen[Inventario::EST_SIN_CODIFICAR] ?? 0);
    $baja = (int)($resumen[Inventario::EST_BAJA] ?? 0);
    $tabs = [
        ''           => ['Inventario activo', 'bi-box-seam', null],
        'pendientes' => ['Sin codificar',     'bi-hourglass-split', $pend],
        'baja'       => ['Desincorporados',   'bi-archive',         $baja],
    ];
    foreach ($tabs as $k => [$lbl, $ico, $n]):
        $act = ($ver === $k);
        $url = URL_ROOT . '/inventario/index' . ($k !== '' ? '?ver=' . $k : '');
    ?>
        <a href="<?php echo $url; ?>" class="btn-sig <?php echo $act ? 'btn-sig--primary' : 'btn-sig--ghost'; ?>">
            <i class="bi <?php echo $ico; ?>"></i> <?php echo $lbl; ?>
            <?php if ($n): ?><span class="sig-badge sig-badge--warning" style="margin-left:6px;"><?php echo $n; ?></span><?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($ver === 'pendientes' && !empty($data['items'])): ?>
    <div class="sig-alert sig-alert--warning anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-info-circle"></i>
        Estos bienes ya están registrados pero <strong>aún no tienen código de la Alcaldía</strong>.
        Se le envía un informe para que vengan a inspeccionarlos; cuando devuelvan el
        <strong>Formulario BM-1</strong>, regístralo en
        <a href="<?php echo URL_ROOT; ?>/inventario/consolidados">BM-1 recibidos</a>
        y transcribe desde ahí los códigos asignados.
    </div>
<?php endif; ?>

<!-- Filtros -->
<form method="GET" action="<?php echo URL_ROOT; ?>/inventario/index" class="anim-slide-up" style="display:flex;gap:var(--sp-2);align-items:flex-end;margin-bottom:var(--sp-4);flex-wrap:wrap;">
    <?php if ($ver !== ''): ?><input type="hidden" name="ver" value="<?php echo htmlspecialchars($ver); ?>"><?php endif; ?>
    <div class="sig-field" style="margin:0;">
        <label class="sig-field__label">Categoría</label>
        <select name="categoria" class="sig-select js-search" style="min-width:190px;" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($data['categorias'] ?? [] as $c): ?>
                <option value="<?php echo $c->id; ?>" <?php echo ((int)($data['f_categoria'] ?? 0) === (int)$c->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($c->nombre); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="sig-field" style="margin:0;">
        <label class="sig-field__label">Ubicación</label>
        <select name="ubicacion" class="sig-select js-search" style="min-width:180px;" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($data['ubicaciones'] ?? [] as $u): ?>
                <option value="<?php echo $u->id; ?>" <?php echo ((int)($data['f_ubicacion'] ?? 0) === (int)$u->id) ? 'selected' : ''; ?>><?php echo htmlspecialchars($u->nombre); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($ver === ''): ?>
    <div class="sig-field" style="margin:0;">
        <label class="sig-field__label">Estatus</label>
        <select name="estatus" class="sig-select" style="min-width:170px;" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach (Inventario::ESTATUS as $e): if ($e === Inventario::EST_BAJA) continue; ?>
                <option value="<?php echo $e; ?>" <?php echo (($data['f_estatus'] ?? '') === $e) ? 'selected' : ''; ?>><?php echo $e; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php endif; ?>
    <div class="sig-field" style="margin:0;">
        <label class="sig-field__label">Condición</label>
        <select name="condicion" class="sig-select" style="min-width:150px;" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach (Inventario::CONDICIONES as $c): ?>
                <option value="<?php echo $c; ?>" <?php echo (($data['f_condicion'] ?? '') === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="sig-field" style="margin:0;">
        <label class="sig-field__label">Origen</label>
        <select name="origen" class="sig-select" style="min-width:140px;" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach (Inventario::ORIGENES as $o): ?>
                <option value="<?php echo $o; ?>" <?php echo (($data['f_origen'] ?? '') === $o) ? 'selected' : ''; ?>><?php echo $o; ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <?php if ($hayFiltro): ?>
        <a href="<?php echo URL_ROOT; ?>/inventario/index<?php echo $ver !== '' ? '?ver=' . htmlspecialchars($ver) : ''; ?>" class="btn-sig btn-sig--ghost" title="Limpiar filtros"><i class="bi bi-x-lg"></i></a>
    <?php endif; ?>
</form>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead><tr>
            <th>Código oficial</th><th>Bien</th><th>Categoría</th><th>Ubicación</th>
            <th>Responsable</th><th>Estatus</th><th>Condición</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($data['items'])): ?>
                <tr><td colspan="8" class="sig-table-empty">
                    <?php
                    echo $ver === 'baja'       ? 'No hay bienes desincorporados.'
                       : ($ver === 'pendientes' ? 'No hay bienes esperando codificación.'
                       : 'No hay bienes registrados.');
                    ?>
                </td></tr>
            <?php else: ?>
                <?php foreach ($data['items'] ?? [] as $item): ?>
                    <?php
                        $est  = $item->estatus ?? '';
                        $ecls = Inventario::ESTATUS_BADGES[$est] ?? 'sig-badge--neutral';
                        $ccls = Inventario::CONDICION_BADGES[$item->condicion ?? ''] ?? 'sig-badge--neutral';
                        $sinCodigo = ($est === Inventario::EST_SIN_CODIFICAR);
                    ?>
                    <tr>
                        <td class="cell-strong" style="color:var(--brand-600)">
                            <?php if (!empty($item->codigo_bn)): ?>
                                <?php echo htmlspecialchars($item->codigo_bn); ?>
                                <?php if (!empty($item->verificado_alcaldia)): ?>
                                    <i class="bi bi-patch-check-fill" style="color:var(--success-600)" title="Verificado por la Alcaldía"></i>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary)">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo htmlspecialchars($item->nombre ?? 'Sin nombre'); ?>
                            <?php if ($item->marca || $item->modelo || $item->serial): ?>
                                <br><small style="color:var(--text-tertiary)">
                                    <?php echo htmlspecialchars(trim(($item->marca ?: '') . ' ' . ($item->modelo ?: ''))) ?: 'S/M'; ?>
                                    <?php if ($item->serial): ?> · S/N: <?php echo htmlspecialchars($item->serial); ?><?php endif; ?>
                                </small>
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item->categoria ?? '—'); ?></td>
                        <td>
                            <?php echo htmlspecialchars($item->ubicacion ?? '—'); ?>
                            <?php if (!empty($item->sede)): ?><br><small style="color:var(--text-tertiary)"><?php echo htmlspecialchars($item->sede); ?></small><?php endif; ?>
                        </td>
                        <td><?php echo !empty($item->responsable) ? htmlspecialchars($item->responsable) : '<span style="color:var(--text-tertiary)">Sin asignar</span>'; ?></td>
                        <td><span class="sig-badge <?php echo $ecls; ?>"><?php echo htmlspecialchars($est); ?></span></td>
                        <td><span class="sig-badge <?php echo $ccls; ?>"><?php echo htmlspecialchars($item->condicion ?? '—'); ?></span></td>
                        <td class="col-actions">
                            <?php if ($sinCodigo): ?>
                                <button class="row-action" onclick='codificarInv(<?php echo htmlspecialchars(json_encode(["id"=>$item->id,"nombre"=>$item->nombre]), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-upc-scan"></i> Codificar</button>
                            <?php endif; ?>
                            <a href="<?php echo URL_ROOT; ?>/inventario/detalle/<?php echo (int)$item->id; ?>" class="row-action row-action--view"><i class="bi bi-journal-text"></i> Hoja de vida</a>
                            <button class="row-action row-action--edit" onclick='editarInv(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                            <a href="<?php echo URL_ROOT; ?>/inventario/delete/<?php echo $item->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ══════════ Modal: alta / edición ══════════ -->
<div class="modal fade" id="modalInv" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?php echo URL_ROOT; ?>/inventario/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalInvLabel">Registro de Bien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="inv_id">

                <div class="sig-alert sig-alert--info" id="inv_aviso_codigo" style="margin-bottom:var(--sp-3);">
                    <i class="bi bi-info-circle"></i>
                    El <strong>código oficial</strong> no se registra aquí: lo asigna la Alcaldía tras su inspección.
                    El bien queda <em>En espera de codificación</em> hasta que devuelvan el Formulario BM-1.
                </div>

                <!-- Identificación -->
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin:0 0 var(--sp-2);">Identificación</div>
                <div class="row g-3">
                    <div class="col-md-12"><div class="sig-field"><label class="sig-field__label" for="inv_nombre">Nombre del bien <span class="req">*</span></label>
                        <input type="text" name="nombre" id="inv_nombre" class="sig-input" required placeholder="Silla visitante en semicuero color negro"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_marca">Marca</label>
                        <input type="text" name="marca" id="inv_marca" class="sig-input" placeholder="Hyundai"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_modelo">Modelo</label>
                        <input type="text" name="modelo" id="inv_modelo" class="sig-input" placeholder="Piso techo"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_serial">Serial</label>
                        <input type="text" name="serial" id="inv_serial" class="sig-input"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="inv_descripcion">Descripción</label>
                        <textarea name="descripcion" id="inv_descripcion" class="sig-textarea" rows="2"></textarea>
                        <small style="color:var(--text-tertiary)">Marca, modelo y serial se combinan con esta descripción al exportar en el formato de la Alcaldía.</small></div></div>
                </div>

                <!-- Clasificación y ubicación -->
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin:var(--sp-4) 0 var(--sp-2);">Clasificación y ubicación</div>
                <div class="row g-3">
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_id_cat">Categoría interna <span class="req">*</span></label>
                        <select name="id_categoria" id="inv_id_cat" class="sig-select js-search" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['categorias'] ?? [] as $c): ?><option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->nombre); ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_id_ubi">Ubicación <span class="req">*</span></label>
                        <select name="id_ubicacion" id="inv_id_ubi" class="sig-select js-search" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['ubicaciones'] ?? [] as $u): ?><option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->nombre); ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_id_resp">Responsable</label>
                        <select name="id_responsable" id="inv_id_resp" class="sig-select js-search">
                            <option value="">Sin asignar (en depósito)</option>
                            <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:var(--text-tertiary)">Director o coordinador del departamento.</small></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_condicion">Condición física</label>
                        <select name="condicion" id="inv_condicion" class="sig-select">
                            <?php foreach (Inventario::CONDICIONES as $c): ?><option value="<?php echo $c; ?>"><?php echo $c; ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-4" id="inv_wrap_estatus" style="display:none;"><div class="sig-field"><label class="sig-field__label" for="inv_estatus">Estatus</label>
                        <select name="estatus" id="inv_estatus" class="sig-select">
                            <?php foreach ([Inventario::EST_ACTIVO, Inventario::EST_MANTENIMIENTO, Inventario::EST_EXTRAVIADO, Inventario::EST_ROBADO] as $e): ?>
                                <option value="<?php echo $e; ?>"><?php echo $e; ?></option>
                            <?php endforeach; ?>
                        </select></div></div>
                </div>

                <!-- Adquisición -->
                <div style="font-size:.72rem;font-weight:700;text-transform:uppercase;letter-spacing:.06em;color:var(--text-secondary);margin:var(--sp-4) 0 var(--sp-2);">Adquisición</div>
                <div class="row g-3">
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_origen">Origen</label>
                        <select name="origen" id="inv_origen" class="sig-select" onchange="invToggleOrigen()">
                            <?php foreach (Inventario::ORIGENES as $o): ?><option value="<?php echo $o; ?>"><?php echo $o; ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-8" id="inv_wrap_donante" style="display:none;"><div class="sig-field"><label class="sig-field__label" for="inv_donante">Donado por <span class="req">*</span></label>
                        <input type="text" name="donante" id="inv_donante" class="sig-input" placeholder="Nombre de la persona o ente que dona"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_costo">Costo (Bs.)</label>
                        <input type="number" step="0.01" min="0" name="costo_adquisicion" id="inv_costo" class="sig-input"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_fecha_adq">Fecha de adquisición</label>
                        <input type="date" name="fecha_adquisicion" id="inv_fecha_adq" class="sig-input"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="inv_proveedor">Proveedor</label>
                        <input type="text" name="proveedor" id="inv_proveedor" class="sig-input"></div></div>
                    <div class="col-md-4"><div class="sig-field" style="margin:0;">
                        <label class="sig-field__label" style="display:flex;align-items:center;gap:8px;">
                            <input type="checkbox" name="tiene_garantia" id="inv_tiene_gar" value="1" onchange="invToggleGarantia()"> Tiene garantía
                        </label></div></div>
                    <div class="col-md-4" id="inv_wrap_gar" style="display:none;"><div class="sig-field"><label class="sig-field__label" for="inv_gar_vence">Vence el <span class="req">*</span></label>
                        <input type="date" name="garantia_vence" id="inv_gar_vence" class="sig-input"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="inv_observaciones">Observaciones</label>
                        <textarea name="observaciones" id="inv_observaciones" class="sig-textarea" rows="2"></textarea></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- ══════════ Modal: codificar (conciliación del BM-1) ══════════ -->
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
                    Transcribe el código que la Alcaldía asignó a
                    <strong id="cod_nombre"></strong> en el <strong>Formulario BM-1</strong>.
                </p>
                <div class="row g-3">
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
                    <div class="col-12">
                        <div class="sig-alert sig-alert--info" style="margin:0;">
                            Quedará como <strong><span id="cod_preview">2-01-108-084</span></strong> y el bien pasará a estatus <strong>Activo</strong>.
                        </div>
                    </div>
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
function invToggleOrigen() {
    var esDon = document.getElementById('inv_origen').value === 'Donación';
    document.getElementById('inv_wrap_donante').style.display = esDon ? '' : 'none';
    document.getElementById('inv_donante').required = esDon;
}
function invToggleGarantia() {
    var tiene = document.getElementById('inv_tiene_gar').checked;
    document.getElementById('inv_wrap_gar').style.display = tiene ? '' : 'none';
    document.getElementById('inv_gar_vence').required = tiene;
}

function nuevoInv() {
    var f = document.querySelector('#modalInv form');
    f.reset();
    document.getElementById('modalInvLabel').innerText = 'Registro de Bien';
    document.getElementById('inv_id').value = '';
    // En el alta el estatus lo fija el sistema y el aviso del código aplica.
    document.getElementById('inv_wrap_estatus').style.display = 'none';
    document.getElementById('inv_aviso_codigo').style.display = '';
    invToggleOrigen();
    invToggleGarantia();
}

function editarInv(item) {
    document.getElementById('modalInvLabel').innerText = 'Editar: ' + (item.nombre || '');
    var v = function (id, val) { var el = document.getElementById(id); if (el) el.value = val == null ? '' : val; };
    v('inv_id', item.id);
    v('inv_nombre', item.nombre);
    v('inv_marca', item.marca);
    v('inv_modelo', item.modelo);
    v('inv_serial', item.serial);
    v('inv_descripcion', item.descripcion);
    v('inv_id_cat', item.id_categoria);
    v('inv_id_ubi', item.id_ubicacion);
    v('inv_id_resp', item.id_responsable);
    v('inv_condicion', item.condicion);
    v('inv_origen', item.origen || 'Compra');
    v('inv_donante', item.donante);
    v('inv_costo', item.costo_adquisicion);
    v('inv_fecha_adq', item.fecha_adquisicion);
    v('inv_proveedor', item.proveedor);
    v('inv_gar_vence', item.garantia_vence);
    v('inv_observaciones', item.observaciones);

    var gar = (item.tiene_garantia === true || item.tiene_garantia === 't' || item.tiene_garantia === '1');
    document.getElementById('inv_tiene_gar').checked = gar;

    // El estatus solo se edita cuando el bien ya salió de "en espera de
    // codificación" y no está dado de baja (esos tienen su propio flujo).
    var editable = (item.estatus !== <?php echo json_encode(Inventario::EST_SIN_CODIFICAR); ?>)
                && (item.estatus !== <?php echo json_encode(Inventario::EST_BAJA); ?>);
    document.getElementById('inv_wrap_estatus').style.display = editable ? '' : 'none';
    if (editable) v('inv_estatus', item.estatus);
    document.getElementById('inv_aviso_codigo').style.display = item.codigo_bn ? 'none' : '';

    invToggleOrigen();
    invToggleGarantia();
    if (window.initSearchSelect) {
        document.querySelectorAll('#modalInv select.js-search').forEach(function (s) { window.initSearchSelect(s); });
    }
    new bootstrap.Modal(document.getElementById('modalInv')).show();
}

function codificarInv(item) {
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
    document.getElementById('cod_preview').innerText = p.every(function (x) { return x !== ''; })
        ? p.join('-') : '— incompleto —';
}
['cod_g', 'cod_sg', 'cod_sec', 'cod_ord'].forEach(function (id) {
    var el = document.getElementById(id);
    if (el) el.addEventListener('input', codPreview);
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
