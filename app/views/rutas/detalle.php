<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/index" style="color:inherit; text-decoration:none;">Turismo</a> · Detalle de Ruta
        </div>
        <h1 class="page__title"><?php echo $data['ruta']->nombre; ?></h1>
        <div style="display:flex; gap:var(--sp-4); margin-top:var(--sp-2); font-size:13px; color:var(--text-secondary);">
            <span><strong>Duración:</strong> <?php echo $data['ruta']->duracion_estimada; ?></span>
            <span><strong>Dificultad:</strong> <span class="sig-badge sig-badge--sm sig-badge--neutral"><?php echo $data['ruta']->nivel_dificultad; ?></span></span>
            <span><strong>Estado:</strong> 
                <span class="sig-badge sig-badge--sm <?php echo $data['ruta']->estado == 'Activa' ? 'sig-badge--success' : 'sig-badge--neutral'; ?>">
                    <?php echo $data['ruta']->estado; ?>
                </span>
            </span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button type="button" class="btn-sig btn-sig--primary" style="background:var(--teal-600);" data-bs-toggle="modal" data-bs-target="#modalPunto" onclick="nuevoPunto()">
            <i class="bi bi-pin-map"></i> Agregar Parada
        </button>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInventario" onclick="nuevoInventario()">
            <i class="bi bi-box-seam"></i> Asignar Equipo
        </button>
    </div>
</div>

<?php if ($data['ruta']->descripcion): ?>
    <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
        <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-6);">
            <p style="margin:0; font-size:15px; color:var(--text-secondary); line-height:1.6;">
                <?php echo $data['ruta']->descripcion; ?>
            </p>
        </div>
    </div>
<?php endif; ?>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title">Paradas de la Ruta (Orden de recorrido)</div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th style="width:80px; text-align:center;">#</th>
                    <th>Nombre del Punto</th>
                    <th>Descripción</th>
                    <th>Coordenadas</th>
                    <th class="col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['puntos'])): ?>
                    <tr><td colspan="5" class="sig-table-empty">Esta ruta aún no tiene paradas definidas.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['puntos'] as $p): ?>
                        <tr>
                            <td style="text-align:center;">
                                <div style="width:32px; height:32px; background:var(--teal-100); color:var(--teal-700); border-radius:50%; display:grid; place-items:center; font-weight:700; font-size:14px; margin:0 auto; border:2px solid var(--teal-200);">
                                    <?php echo $p->orden; ?>
                                </div>
                            </td>
                            <td class="cell-strong"><?php echo $p->nombre; ?></td>
                            <td style="font-size:13px; color:var(--text-secondary);"><?php echo $p->descripcion ?? '—'; ?></td>
                            <td style="font-family:var(--font-mono); font-size:12px; color:var(--text-tertiary);">
                                <?php if ($p->latitud && $p->longitud): ?>
                                    <i class="bi bi-geo-alt"></i> <?php echo $p->latitud . ', ' . $p->longitud; ?>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td class="col-actions">
                                <button class="row-action row-action--edit" onclick='editarPunto(<?php echo json_encode($p); ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?php echo URL_ROOT; ?>/rutas/deletePunto/<?php echo $p->id; ?>/<?php echo $data['ruta']->id; ?>" class="row-action row-action--del delete-btn">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="sig-card anim-slide-up" style="border-top: 4px solid var(--brand-500);">
    <div class="sig-card__head">
        <div class="sig-card__title">
            <i class="bi bi-box-seam" style="color:var(--brand-500);"></i> Bienes y Equipos Asignados
        </div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th>Código / Bien</th>
                    <th>Condición</th>
                    <th class="text-center">Cantidad</th>
                    <th>Observaciones</th>
                    <th class="col-actions">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['inventario_asignado'])): ?>
                    <tr><td colspan="5" class="sig-table-empty">No se han asignado bienes a esta ruta.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['inventario_asignado'] as $inv): ?>
                        <tr>
                            <td>
                                <div style="display:flex; flex-direction:column; gap:2px;">
                                    <span class="cell-strong"><?php echo $inv->item_nombre; ?></span>
                                    <span class="cell-id"><?php echo $inv->codigo_bn ?: 'Sin Código'; ?></span>
                                </div>
                            </td>
                            <td><span class="sig-badge sig-badge--info"><?php echo $inv->condicion; ?></span></td>
                            <td class="text-center" style="font-weight:700; color:var(--text-primary);"><?php echo $inv->cantidad; ?></td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?php echo $inv->observaciones ?? '—'; ?></td>
                            <td class="col-actions">
                                <a href="<?php echo URL_ROOT; ?>/rutas/deleteInventario/<?php echo $inv->id; ?>/<?php echo $data['ruta']->id; ?>" class="row-action row-action--del delete-btn">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Punto -->
<div class="modal fade" id="modalPunto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/rutas/storePunto" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPuntoLabel">Agregar Parada</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="punto_id" id="pt_id">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id; ?>">
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Nombre del Punto <span class="req">*</span></label>
                    <input type="text" name="punto_nombre" id="pt_nombre" class="sig-input" required placeholder="Ej: Mirador de la Cruz">
                </div>
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Descripción</label>
                    <textarea name="punto_descripcion" id="pt_descripcion" class="sig-textarea" rows="2"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Orden <span class="req">*</span></label>
                            <input type="number" name="orden" id="pt_orden" class="sig-input" value="1" min="1" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Latitud</label>
                            <input type="text" name="latitud" id="pt_lat" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Longitud</label>
                            <input type="text" name="longitud" id="pt_lng" class="sig-input">
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary" style="background:var(--teal-600);"><i class="bi bi-check-lg"></i> Guardar Punto</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Inventario -->
<div class="modal fade" id="modalInventario" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/rutas/storeInventario" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar Equipamiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id; ?>">
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Bien a Asignar <span class="req">*</span></label>
                    <select name="id_inventario" class="sig-select" required>
                        <option value="">Seleccione un bien...</option>
                        <?php foreach($data['inventario_disponible'] as $item): ?>
                            <option value="<?php echo $item->id; ?>"><?php echo ($item->codigo_bn ? $item->codigo_bn.' - ' : '') . $item->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-4">
                    <label class="sig-field__label">Cantidad <span class="req">*</span></label>
                    <input type="number" name="cantidad" class="sig-input" value="1" min="1" required>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Observaciones</label>
                    <textarea name="observaciones" class="sig-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Asignar Bien</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoPunto() {
        document.getElementById('modalPuntoLabel').innerText = 'Agregar Parada';
        document.getElementById('pt_id').value = '';
        document.getElementById('pt_nombre').value = '';
        document.getElementById('pt_descripcion').value = '';
        document.getElementById('pt_orden').value = <?php echo count($data['puntos']) + 1; ?>;
        document.getElementById('pt_lat').value = '';
        document.getElementById('pt_lng').value = '';
    }
    function editarPunto(p) {
        document.getElementById('modalPuntoLabel').innerText = 'Editar: ' + p.nombre;
        document.getElementById('pt_id').value = p.id;
        document.getElementById('pt_nombre').value = p.nombre;
        document.getElementById('pt_descripcion').value = p.descripcion;
        document.getElementById('pt_orden').value = p.orden;
        document.getElementById('pt_lat').value = p.latitud || '';
        document.getElementById('pt_lng').value = p.longitud || '';
        new bootstrap.Modal(document.getElementById('modalPunto')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
