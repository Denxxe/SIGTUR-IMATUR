<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-signpost-2"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted mb-0">
            <strong>Duración:</strong> <?php echo $data['ruta']->duracion_estimada; ?> |
            <strong>Dificultad:</strong> <?php echo $data['ruta']->nivel_dificultad; ?> |
            <strong>Estado:</strong> 
            <span class="badge <?php echo $data['ruta']->estado == 'Activa' ? 'bg-success' : 'bg-secondary'; ?>">
                <?php echo $data['ruta']->estado; ?>
            </span>
        </p>
        <?php if ($data['ruta']->descripcion): ?>
            <p class="mt-2"><?php echo $data['ruta']->descripcion; ?></p>
        <?php endif; ?>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn btn-outline-secondary me-2">← Volver</a>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalPunto" onclick="nuevoPunto()">
            Agregar Parada
        </button>
    </div>
</div>

<!-- Lista de puntos -->
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold">
        Paradas de la Ruta (Orden de recorrido)
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4 text-center" style="width:60px">#</th>
                    <th>Nombre del Punto</th>
                    <th>Descripción</th>
                    <th>Coordenadas</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['puntos'])): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">Esta ruta aún no tiene paradas definidas.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['puntos'] as $p): ?>
                        <tr>
                            <td class="ps-4 text-center">
                                <span class="badge bg-dark rounded-circle" style="width:30px;height:30px;line-height:22px;"><?php echo $p->orden; ?></span>
                            </td>
                            <td class="fw-bold"><?php echo $p->nombre; ?></td>
                            <td class="small text-muted"><?php echo $p->descripcion ?? '—'; ?></td>
                            <td class="small">
                                <?php if ($p->latitud && $p->longitud): ?>
                                    <?php echo $p->latitud . ', ' . $p->longitud; ?>
                                <?php else: ?>
                                    <span class="text-muted">Sin GPS</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-info" onclick='editarPunto(<?php echo json_encode($p); ?>)'>Editar</button>
                                    <a href="<?php echo URL_ROOT; ?>/rutas/deletePunto/<?php echo $p->id; ?>/<?php echo $data['ruta']->id; ?>" class="btn btn-outline-danger delete-btn">Quitar</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Punto de Ruta -->
<div class="modal fade" id="modalPunto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/rutas/storePunto" method="POST" class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalPuntoLabel">Agregar Parada</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="punto_id" id="pt_id">
                <input type="hidden" name="id_ruta" value="<?php echo $data['ruta']->id; ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre del Punto</label>
                    <input type="text" name="punto_nombre" id="pt_nombre" class="form-control" required placeholder="Ej: Mirador de la Cruz">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Descripción</label>
                    <textarea name="punto_descripcion" id="pt_descripcion" class="form-control" rows="2"></textarea>
                </div>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Orden</label>
                        <input type="number" name="orden" id="pt_orden" class="form-control" value="1" min="1" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Latitud</label>
                        <input type="text" name="latitud" id="pt_lat" class="form-control" placeholder="Opcional">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Longitud</label>
                        <input type="text" name="longitud" id="pt_lng" class="form-control" placeholder="Opcional">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-success">Guardar Punto</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoPunto() {
        document.getElementById('modalPuntoLabel').innerText = 'Agregar Parada';
        document.getElementById('pt_id').value = '';
        document.getElementById('pt_nombre').value = '';
        document.getElementById('pt_descripcion').value = '';
        document.getElementById('pt_orden').value = <?php echo count($data['puntos']) + 1; ?>;
        document.getElementById('pt_lat').value = '';
        document.getElementById('pt_lng').value = '';
    }
    function editarPunto(p) {
        document.getElementById('modalPuntoLabel').innerText = 'Editar: ' + p.nombre;
        document.getElementById('pt_id').value = p.id;
        document.getElementById('pt_nombre').value = p.nombre;
        document.getElementById('pt_descripcion').value = p.descripcion;
        document.getElementById('pt_orden').value = p.orden;
        document.getElementById('pt_lat').value = p.latitud || '';
        document.getElementById('pt_lng').value = p.longitud || '';
        new bootstrap.Modal(document.getElementById('modalPunto')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
