<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-map"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Administración de parroquias del estado.</p>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalParroquia" onclick="nuevaParroquia()">
            <i class="bi bi-plus-lg"></i> Nueva Parroquia
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
                        <th>Municipio</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['parroquia'])): ?>
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">No hay parroquias registradas.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['parroquia'] as $par): ?>
                            <tr>
                                <td class="ps-4"><?php echo $par->id; ?></td>
                                <td class="fw-bold"><?php echo $par->nombre; ?></td>
                                <td><?php echo $par->municipio; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick='editarParroquia(<?php echo json_encode($par); ?>)'>
                                            Editar
                                        </button>
                                        <a href="<?php echo URL_ROOT; ?>/parroquia/delete/<?php echo $par->id; ?>" class="btn btn-outline-danger delete-btn">
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

<!-- Modal para Crear/Editar Parroquia -->
<div class="modal fade" id="modalParroquia" tabindex="-1" aria-labelledby="modalParroquiaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo URL_ROOT; ?>/parroquia/store" method="POST" class="needs-validation" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modalParroquiaLabel">Nueva Parroquia</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="parroquia_id">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre de la Parroquia</label>
                        <input type="text" class="form-control" name="nombre" id="parroquia_nombre" required placeholder="Ej: Altagracia">
                        <div class="invalid-feedback">Por favor, ingrese el nombre de la parroquia.</div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="id_municipio" class="form-label fw-bold">Municipio al que pertenece</label>
                        <select class="form-select" name="id_municipio" id="parroquia_municipio" required>
                            <option value="">Seleccione un municipio...</option>
                            <?php foreach ($data['municipios'] as $mun): ?>
                                <option value="<?php echo $mun->id; ?>"><?php echo $mun->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                        <div class="invalid-feedback">Por favor, seleccione un municipio.</div>
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
    function nuevaParroquia() {
        document.getElementById('modalParroquiaLabel').innerText = 'Nueva Parroquia';
        document.getElementById('parroquia_id').value = '';
        document.getElementById('parroquia_nombre').value = '';
        document.getElementById('parroquia_municipio').value = '';
    }

    function editarParroquia(par) {
        document.getElementById('modalParroquiaLabel').innerText = 'Editar Parroquia: ' + par.nombre;
        document.getElementById('parroquia_id').value = par.id;
        document.getElementById('parroquia_nombre').value = par.nombre;
        document.getElementById('parroquia_municipio').value = par.id_municipio;
        
        var myModal = new bootstrap.Modal(document.getElementById('modalParroquia'));
        myModal.show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
