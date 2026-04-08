<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-geo"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUbi" onclick="nuevaUbi()">
            Nueva Ubicación
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">ID</th>
                    <th>Nombre de Sede/Almacén</th>
                    <th>Referencia</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['ubicaciones'] as $ubi): ?>
                    <tr>
                        <td class="ps-4"><?php echo $ubi->id; ?></td>
                        <td class="fw-bold"><?php echo $ubi->nombre; ?></td>
                        <td><?php echo $ubi->descripcion; ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarUbi(<?php echo json_encode($ubi); ?>)'>Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/ubicaciones/delete/<?php echo $ubi->id; ?>" class="btn btn-outline-danger delete-btn">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUbi" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/ubicaciones/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUbiLabel">Ubicación Física</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="ubi_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre de la Sede/Oficina</label>
                    <input type="text" name="nombre" id="ubi_nombre" class="form-control" required placeholder="Ej: Mezzanina - Oficina RRHH">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Referencia / Descripción</label>
                    <textarea name="descripcion" id="ubi_descripcion" class="form-control" rows="3"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar Ubicación</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevaUbi() {
        document.getElementById('modalUbiLabel').innerText = 'Nueva Ubicación';
        document.getElementById('ubi_id').value = '';
        document.querySelector('#modalUbi form').reset();
    }
    function editarUbi(ubi) {
        document.getElementById('modalUbiLabel').innerText = 'Editar: ' + ubi.nombre;
        document.getElementById('ubi_id').value = ubi.id;
        document.getElementById('ubi_nombre').value = ubi.nombre;
        document.getElementById('ubi_descripcion').value = ubi.descripcion;
        new bootstrap.Modal(document.getElementById('modalUbi')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
