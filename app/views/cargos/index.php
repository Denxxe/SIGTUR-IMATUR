<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-briefcase"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Administración de puestos y servicios institucionales.</p>
    </div>
    <div class="col-md-6 text-end">
        <!-- Botón para abrir modal de creación -->
        <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalCargo" onclick="nuevoCargo()">
            <i class="bi bi-plus-lg"></i> Nuevo Cargo
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
                        <th>Descripción</th>
                        <th>Sueldo Base</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['cargos'])): ?>
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No hay cargos registrados.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['cargos'] as $cargo): ?>
                            <tr>
                                <td class="ps-4"><?php echo $cargo->id; ?></td>
                                <td class="fw-bold"><?php echo $cargo->nombre; ?></td>
                                <td><?php echo $cargo->descripcion; ?></td>
                                <td><?php echo number_format($cargo->sueldo_base, 2); ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick='editarCargo(<?php echo json_encode($cargo); ?>)'>
                                            Editar
                                        </button>
                                        <a href="<?php echo URL_ROOT; ?>/cargos/delete/<?php echo $cargo->id; ?>" class="btn btn-outline-danger delete-btn">
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

<!-- Modal para Crear/Editar Cargo -->
<div class="modal fade" id="modalCargo" tabindex="-1" aria-labelledby="modalCargoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo URL_ROOT; ?>/cargos/store" method="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCargoLabel">Nuevo Cargo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="cargo_id">
                    
                    <div class="mb-3">
                        <label for="nombre" class="form-label fw-bold">Nombre del Cargo</label>
                        <input type="text" class="form-control" name="nombre" id="cargo_nombre" required placeholder="Ej: Especialista III">
                    </div>
                    
                    <div class="mb-3">
                        <label for="descripcion" class="form-label fw-bold">Descripción</label>
                        <textarea class="form-control" name="descripcion" id="cargo_descripcion" rows="3" placeholder="Información sobre las funciones..."></textarea>
                    </div>
                    
                    <div class="mb-3">
                        <label for="sueldo_base" class="form-label fw-bold">Sueldo Base</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" class="form-control" name="sueldo_base" id="cargo_sueldo" required value="0.00">
                        </div>
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
    function nuevoCargo() {
        document.getElementById('modalCargoLabel').innerText = 'Nuevo Cargo';
        document.getElementById('cargo_id').value = '';
        document.getElementById('cargo_nombre').value = '';
        document.getElementById('cargo_descripcion').value = '';
        document.getElementById('cargo_sueldo').value = '0.00';
    }

    function editarCargo(cargo) {
        document.getElementById('modalCargoLabel').innerText = 'Editar Cargo: ' + cargo.nombre;
        document.getElementById('cargo_id').value = cargo.id;
        document.getElementById('cargo_nombre').value = cargo.nombre;
        document.getElementById('cargo_descripcion').value = cargo.descripcion;
        document.getElementById('cargo_sueldo').value = cargo.sueldo_base;
        
        // Abrir el modal manualmente si no es mediante data-bs
        var myModal = new bootstrap.Modal(document.getElementById('modalCargo'));
        myModal.show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
