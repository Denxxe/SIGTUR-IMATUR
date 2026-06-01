<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Bienes Nacionales</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Inventario Institucional'; ?></h1>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInv" onclick="nuevoInv()">
            <i class="bi bi-plus-circle"></i> Registrar Bien
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead><tr><th>Código BN</th><th>Nombre</th><th>Marca/Modelo</th><th>Categoría</th><th>Ubicación</th><th>Condición</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php if (empty($data['items'])): ?>
                <tr><td colspan="7" class="sig-table-empty">No hay bienes registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['items'] ?? [] as $item): ?>
                    <tr>
                        <td class="cell-strong" style="color:var(--brand-600)"><?php echo $item->codigo_bn ?? 'N/A'; ?></td>
                        <td><?php echo $item->nombre ?? 'Sin nombre'; ?></td>
                        <td style="font-size:12.5px"><?php echo $item->marca ?? 'S/M'; ?> / <?php echo $item->modelo ?? 'S/M'; ?><br><span style="color:var(--text-tertiary)">S/N: <?php echo $item->serial ?? 'S/N'; ?></span></td>
                        <td><?php echo $item->categoria ?? 'Sin cat.'; ?></td>
                        <td><?php echo $item->ubicacion ?? 'Sin ubi.'; ?></td>
                        <td>
                            <?php $cls = Inventario::CONDICION_BADGES[$item->condicion ?? ''] ?? 'sig-badge--neutral'; ?>
                            <span class="sig-badge <?php echo $cls; ?>"><?php echo $item->condicion; ?></span>
                        </td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarInv(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                            <a href="<?php echo URL_ROOT; ?>/inventario/delete/<?php echo $item->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Baja</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalInv" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/inventario/store" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalInvLabel">Registro de Bien Nacional</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="inv_id">
                <div class="row g-3">
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Código B.N. <span class="req">*</span></label><input type="text" name="codigo_bn" id="inv_codigo" class="sig-input" required placeholder="IMATUR-001"></div></div>
                    <div class="col-md-8"><div class="sig-field"><label class="sig-field__label">Nombre <span class="req">*</span></label><input type="text" name="nombre" id="inv_nombre" class="sig-input" required placeholder="Escritorio Ejecutivo"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Categoría <span class="req">*</span></label><select name="id_categoria" id="inv_id_cat" class="sig-select" required><option value="">Seleccione...</option><?php foreach ($data['categorias'] ?? [] as $c): ?><option value="<?php echo $c->id; ?>"><?php echo $c->nombre; ?></option><?php endforeach; ?></select></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Ubicación <span class="req">*</span></label><select name="id_ubicacion" id="inv_id_ubi" class="sig-select" required><option value="">Seleccione...</option><?php foreach ($data['ubicaciones'] ?? [] as $u): ?><option value="<?php echo $u->id; ?>"><?php echo $u->nombre; ?></option><?php endforeach; ?></select></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Condición</label><select name="condicion" id="inv_condicion" class="sig-select"><?php foreach(Inventario::CONDICIONES as $c): ?><option value="<?php echo $c; ?>"><?php echo $c; ?></option><?php endforeach; ?></select></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Marca</label><input type="text" name="marca" id="inv_marca" class="sig-input"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Modelo</label><input type="text" name="modelo" id="inv_modelo" class="sig-input"></div></div>
                    <div class="col-md-4"><div class="sig-field"><label class="sig-field__label">Serial</label><input type="text" name="serial" id="inv_serial" class="sig-input"></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label">Descripción</label><textarea name="descripcion" id="inv_descripcion" class="sig-textarea" rows="2"></textarea></div></div>
                    <div class="col-12"><div class="sig-field"><label class="sig-field__label">Observaciones</label><textarea name="observaciones" id="inv_observaciones" class="sig-textarea" rows="2"></textarea></div></div>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button></div>
        </form>
    </div>
</div>

<script>
    function nuevoInv() { document.getElementById('modalInvLabel').innerText='Registro de Bien Nacional'; document.getElementById('inv_id').value=''; document.querySelector('#modalInv form').reset(); }
    function editarInv(item) {
        document.getElementById('modalInvLabel').innerText='Editar: '+item.nombre; document.getElementById('inv_id').value=item.id;
        document.getElementById('inv_codigo').value=item.codigo_bn; document.getElementById('inv_nombre').value=item.nombre;
        document.getElementById('inv_id_cat').value=item.id_categoria; document.getElementById('inv_id_ubi').value=item.id_ubicacion;
        document.getElementById('inv_condicion').value=item.condicion; document.getElementById('inv_marca').value=item.marca;
        document.getElementById('inv_modelo').value=item.modelo; document.getElementById('inv_serial').value=item.serial;
        document.getElementById('inv_descripcion').value=item.descripcion; document.getElementById('inv_observaciones').value=item.observaciones;
        new bootstrap.Modal(document.getElementById('modalInv')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
