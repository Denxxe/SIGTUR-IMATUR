<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-diagram-3"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalDpto" onclick="nuevoDpto()">
            <i class="bi bi-plus-lg"></i> Agregar Departamento
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover table-striped mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['departamentos'] as $dpto): ?>
                    <tr>
                        <td class="ps-4"><?php echo $dpto->id; ?></td>
                        <td class="fw-bold"><?php echo $dpto->nombre; ?></td>
                        <td><?php echo $dpto->descripcion; ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarDpto(<?php echo json_encode($dpto); ?>)'>Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/departamentos/delete/<?php echo $dpto->id; ?>" class="btn btn-outline-danger delete-btn">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalDpto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/departamentos/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDptoLabel">Nuevo Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="dpto_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre del Departamento</label>
                    <input type="text" name="nombre" id="dpto_nombre" class="form-control" required placeholder="Ej: Dirección de Turismo">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción / Funciones</label>
                    <textarea name="descripcion" id="dpto_descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Departamento</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoDpto() {
        document.getElementById('modalDptoLabel').innerText = 'Nuevo Departamento';
        document.getElementById('dpto_id').value = '';
        document.getElementById('dpto_nombre').value = '';
        document.getElementById('dpto_descripcion').value = '';
    }
    function editarDpto(dpto) {
        document.getElementById('modalDptoLabel').innerText = 'Editar: ' + dpto.nombre;
        document.getElementById('dpto_id').value = dpto.id;
        document.getElementById('dpto_nombre').value = dpto.nombre;
        document.getElementById('dpto_descripcion').value = dpto.descripcion;
        new bootstrap.Modal(document.getElementById('modalDpto')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
