<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-building"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUbiForm" onclick="nuevaUbi()">
            Nueva Sede
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">Nombre</th>
                    <th>Tipo</th>
                    <th>Parroquia</th>
                    <th>Dirección</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['ubicaciones'] as $u): ?>
                    <tr>
                        <td class="ps-4 fw-bold"><?php echo $u->nombre; ?></td>
                        <td><span class="badge bg-info text-dark"><?php echo $u->tipo; ?></span></td>
                        <td><?php echo $u->parroquia; ?></td>
                        <td class="small"><?php echo $u->direccion; ?></td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarUbi(<?php echo json_encode($u); ?>)'>Editar</button>
                                <a href="<?php echo URL_ROOT; ?>/ubicacionesformacion/delete/<?php echo $u->id; ?>" class="btn btn-outline-danger delete-btn">Eliminar</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUbiForm" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/ubicacionesformacion/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUbiFormLabel">Sede de Formación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="ubif_id">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="nombre" id="ubif_nombre" class="form-control" required placeholder="Ej: Liceo Bolivariano">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Tipo de Espacio</label>
                    <select name="tipo" id="ubif_tipo" class="form-select">
                        <option value="Liceo">Liceo</option>
                        <option value="Plaza">Plaza</option>
                        <option value="Centro Comunitario">Centro Comunitario</option>
                        <option value="Auditorio">Auditorio</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Parroquia</label>
                    <select name="parroquia" id="ubif_parroquia" class="form-select" required>
                        <option value="">Seleccione una parroquia</option>
                        <?php foreach ($data['parroquias'] as $p): ?>
                            <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Dirección</label>
                    <textarea name="direccion" id="ubif_direccion" class="form-control" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevaUbi() {
        document.getElementById('modalUbiFormLabel').innerText = 'Nueva Sede de Formación';
        document.getElementById('ubif_id').value = '';
        document.querySelector('#modalUbiForm form').reset();
    }

    function editarUbi(u) {
        document.getElementById('modalUbiFormLabel').innerText = 'Editar: ' + u.nombre;
        document.getElementById('ubif_id').value = u.id;
        document.getElementById('ubif_nombre').value = u.nombre;
        document.getElementById('ubif_tipo').value = u.tipo;
        document.getElementById('ubif_parroquia').value = u.id_parroquia;
        document.getElementById('ubif_direccion').value = u.direccion;
        new bootstrap.Modal(document.getElementById('modalUbiForm')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>