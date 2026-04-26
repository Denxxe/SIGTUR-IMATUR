<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-geo-alt"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Administración de municipios del estado.</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMunicipio" onclick="nuevoMunicipio()">
            <i class="bi bi-plus-lg"></i> Nuevo Municipio
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Nombre</th>
                        <th>Código Postal</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['municipio'])): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No hay municipios registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['municipio'] as $mun): ?>
                            <tr>
                                <td class="ps-4"><?php echo $mun->id; ?></td>
                                <td class="fw-bold"><?php echo $mun->nombre; ?></td>
                                <td><?php echo $mun->codigo_postal; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick='editarMunicipio(<?php echo json_encode($mun); ?>)'>
                                            Editar
                                        </button>
                                        <a href="<?php echo URL_ROOT; ?>/municipio/delete/<?php echo $mun->id; ?>" class="btn btn-outline-danger delete-btn">
                                            Eliminar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Crear/Editar Municipio -->
<div class="modal fade" id="modalMunicipio" tabindex="-1" aria-labelledby="modalMunicipioLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo URL_ROOT; ?>/municipio/store" method="POST" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalMunicipioLabel">Nuevo Municipio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="municipio_id">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre del Municipio</label>
                        <input type="text" class="form-control" name="nombre" id="municipio_nombre" required placeholder="Ej: Sucre">
                        <div class="invalid-feedback">Por favor, ingrese el nombre del municipio.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="codigo_postal" class="form-label fw-bold">Código Postal</label>
                        <input type="text" class="form-control" name="codigo_postal" id="municipio_cp" required placeholder="Ej: 6101">
                        <div class="invalid-feedback">Por favor, ingrese el código postal.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Registro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function nuevoMunicipio() {
        document.getElementById('modalMunicipioLabel').innerText = 'Nuevo Municipio';
        document.getElementById('municipio_id').value = '';
        document.getElementById('municipio_nombre').value = '';
        document.getElementById('municipio_cp').value = '';
    }

    function editarMunicipio(mun) {
        document.getElementById('modalMunicipioLabel').innerText = 'Editar Municipio: ' + mun.nombre;
        document.getElementById('municipio_id').value = mun.id;
        document.getElementById('municipio_nombre').value = mun.nombre;
        document.getElementById('municipio_cp').value = mun.codigo_postal;
        
        var myModal = new bootstrap.Modal(document.getElementById('modalMunicipio'));
        myModal.show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
