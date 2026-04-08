<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-shield-lock"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalRol" onclick="nuevoRol()">
            Nuevo Rol
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
                <?php foreach ($data['roles'] as $rol): ?>
                    <tr>
                        <td class="ps-4"><?php echo $rol->id; ?></td>
                        <td class="fw-bold"><?php echo $rol->nombre; ?></td>
                        <td><?php echo $rol->descripcion; ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarRol(<?php echo json_encode($rol); ?>)'>Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/roles/delete/<?php echo $rol->id; ?>" class="btn btn-outline-danger delete-btn">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/roles/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalRolLabel">Nuevo Rol</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rol_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="nombre" id="rol_nombre" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="descripcion" id="rol_descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoRol() {
        document.getElementById('modalRolLabel').innerText = 'Nuevo Rol';
        document.getElementById('rol_id').value = '';
        document.getElementById('rol_nombre').value = '';
        document.getElementById('rol_descripcion').value = '';
    }
    function editarRol(rol) {
        document.getElementById('modalRolLabel').innerText = 'Editar Rol: ' + rol.nombre;
        document.getElementById('rol_id').value = rol.id;
        document.getElementById('rol_nombre').value = rol.nombre;
        document.getElementById('rol_descripcion').value = rol.descripcion;
        new bootstrap.Modal(document.getElementById('modalRol')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
