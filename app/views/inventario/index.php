<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-box-seam"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalInv" onclick="nuevoInv()">
            <i class="bi bi-plus-circle"></i> Registrar Bien
        </button>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Código BN</th>
                        <th>Nombre del Bien</th>
                        <th>Marca/Modelo</th>
                        <th>Categoría</th>
                        <th>Ubicación</th>
                        <th class="text-center">Condición</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['items'])): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">No hay bienes registrados en el inventario.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['items'] as $item): ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?php echo $item->codigo_bn; ?></td>
                                <td><?php echo $item->nombre; ?></td>
                                <td class="small">
                                    <?php echo $item->marca; ?> / <?php echo $item->modelo; ?> <br>
                                    <span class="text-muted">S/N: <?php echo $item->serial; ?></span>
                                </td>
                                <td><?php echo $item->categoria; ?></td>
                                <td><?php echo $item->ubicacion; ?></td>
                                <td class="text-center">
                                    <span class="badge <?php 
                                        echo $item->condicion == 'Nuevo' ? 'bg-success' : ($item->condicion == 'Bueno' ? 'bg-primary' : 'bg-warning text-dark'); 
                                        ?>">
                                        <?php echo $item->condicion; ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick='editarInv(<?php echo json_encode($item); ?>)'>Editar</button>
                                        <a href="<?php echo URL_ROOT; ?>/inventario/delete/<?php echo $item->id; ?>" class="btn btn-outline-danger delete-btn">Baja</a>
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

<!-- Modal -->
<div class="modal fade" id="modalInv" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/inventario/store" method="POST" class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalInvLabel">Registro de Bien Nacional</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="inv_id">
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Código B.N.</label>
                        <input type="text" name="codigo_bn" id="inv_codigo" class="form-control" required placeholder="Ej: IMATUR-001">
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Nombre del Bien</label>
                        <input type="text" name="nombre" id="inv_nombre" class="form-control" required placeholder="Ej: Escritorio Ejecutivo">
                    </div>
                    
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Categoría</label>
                        <select name="id_categoria" id="inv_id_cat" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['categorias'] as $c): ?>
                                <option value="<?php echo $c->id; ?>"><?php echo $c->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Ubicación</label>
                        <select name="id_ubicacion" id="inv_id_ubi" class="form-select" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['ubicaciones'] as $u): ?>
                                <option value="<?php echo $u->id; ?>"><?php echo $u->nombre; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Condición</label>
                        <select name="condicion" id="inv_condicion" class="form-select">
                            <option value="Nuevo">Nuevo</option>
                            <option value="Bueno">Bueno</option>
                            <option value="Regular">Regular</option>
                            <option value="Dañado">Dañado</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label fw-bold">Marca</label>
                        <input type="text" name="marca" id="inv_marca" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Modelo</label>
                        <input type="text" name="modelo" id="inv_modelo" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Serial</label>
                        <input type="text" name="serial" id="inv_serial" class="form-control">
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-bold">Descripción / Características</label>
                        <textarea name="descripcion" id="inv_descripcion" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Observaciones de Entrega/Estado</label>
                        <textarea name="observaciones" id="inv_observaciones" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar en Inventario</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoInv() {
        document.getElementById('modalInvLabel').innerText = 'Registro de Bien Nacional';
        document.getElementById('inv_id').value = '';
        document.querySelector('#modalInv form').reset();
    }
    function editarInv(item) {
        document.getElementById('modalInvLabel').innerText = 'Editar: ' + item.nombre;
        document.getElementById('inv_id').value = item.id;
        document.getElementById('inv_codigo').value = item.codigo_bn;
        document.getElementById('inv_nombre').value = item.nombre;
        document.getElementById('inv_id_cat').value = item.id_categoria;
        document.getElementById('inv_id_ubi').value = item.id_ubicacion;
        document.getElementById('inv_condicion').value = item.condicion;
        document.getElementById('inv_marca').value = item.marca;
        document.getElementById('inv_modelo').value = item.modelo;
        document.getElementById('inv_serial').value = item.serial;
        document.getElementById('inv_descripcion').value = item.descripcion;
        document.getElementById('inv_observaciones').value = item.observaciones;
        new bootstrap.Modal(document.getElementById('modalInv')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
