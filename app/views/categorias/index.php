<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-tags"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCat" onclick="nuevaCat()">
            Nueva Categoría
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['categorias'] as $cat): ?>
                    <tr>
                        <td class="ps-4"><?php echo $cat->id; ?></td>
                        <td class="fw-bold"><?php echo $cat->nombre; ?></td>
                        <td><?php echo $cat->descripcion; ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarCat(<?php echo json_encode($cat); ?>)'>Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/categorias/delete/<?php echo $cat->id; ?>" class="btn btn-outline-danger delete-btn">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalCat" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/categorias/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCatLabel">Categoría de Inventario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="cat_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="nombre" id="cat_nombre" class="form-control" required placeholder="Ej: Electrónica">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" id="cat_descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Categoría</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevaCat() {
        document.getElementById('modalCatLabel').innerText = 'Nueva Categoría';
        document.getElementById('cat_id').value = '';
        document.querySelector('#modalCat form').reset();
    }
    function editarCat(cat) {
        document.getElementById('modalCatLabel').innerText = 'Editar: ' + cat.nombre;
        document.getElementById('cat_id').value = cat.id;
        document.getElementById('cat_nombre').value = cat.nombre;
        document.getElementById('cat_descripcion').value = cat.descripcion;
        new bootstrap.Modal(document.getElementById('modalCat')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
