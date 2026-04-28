<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Catálogo</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalCat" onclick="nuevaCat()">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php foreach ($data['categorias'] as $cat): ?>
                <tr>
                    <td><span class="cell-id"><?php echo $cat->id; ?></span></td>
                    <td class="cell-strong"><?php echo $cat->nombre; ?></td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo $cat->descripcion; ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarCat(<?php echo json_encode($cat); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                        <a href="<?php echo URL_ROOT; ?>/categorias/delete/<?php echo $cat->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalCat" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/categorias/store" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalCatLabel">Categoría</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="cat_id">
                <div class="sig-field mb-3"><label class="sig-field__label">Nombre <span class="req">*</span></label><input type="text" name="nombre" id="cat_nombre" class="sig-input" required placeholder="Ej: Electrónica"></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Descripción</label><textarea name="descripcion" id="cat_descripcion" class="sig-textarea" rows="3"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button></div>
        </form>
    </div>
</div>

<script>
    function nuevaCat() { document.getElementById('modalCatLabel').innerText='Nueva Categoría'; document.getElementById('cat_id').value=''; document.querySelector('#modalCat form').reset(); }
    function editarCat(cat) { document.getElementById('modalCatLabel').innerText='Editar: '+cat.nombre; document.getElementById('cat_id').value=cat.id; document.getElementById('cat_nombre').value=cat.nombre; document.getElementById('cat_descripcion').value=cat.descripcion; new bootstrap.Modal(document.getElementById('modalCat')).show(); }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
