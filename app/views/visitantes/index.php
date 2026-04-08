<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-person-walking"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVisitante" onclick="nuevoVisitante()">
            Registrar Visitante
        </button>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Cédula</th>
                        <th>Nombre y Apellido</th>
                        <th>Procedencia</th>
                        <th>Teléfono</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['visitantes'])): ?>
                        <tr><td colspan="5" class="text-center py-4">No hay visitantes registrados.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['visitantes'] as $v): ?>
                            <tr>
                                <td class="ps-4"><?php echo $v->cedula; ?></td>
                                <td class="fw-bold"><?php echo $v->nombre . ' ' . $v->apellido; ?></td>
                                <td><?php echo $v->procedencia; ?></td>
                                <td><?php echo $v->telefono; ?></td>
                                <td class="text-center">
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-info" onclick='editarVisitante(<?php echo json_encode($v); ?>)'>Editar</button>
                                        <a href="<?php echo URL_ROOT; ?>/visitantes/delete/<?php echo $v->id; ?>" class="btn btn-outline-danger delete-btn">Eliminar</a>
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
<div class="modal fade" id="modalVisitante" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/visitantes/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalVisitanteLabel">Nuevo Visitante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="vis_id">
                <input type="hidden" name="id_persona" id="vis_id_persona">
                
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cédula</label>
                        <input type="text" name="cedula" id="vis_cedula" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nombres</label>
                        <input type="text" name="nombre" id="vis_nombre" class="form-control" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Apellidos</label>
                        <input type="text" name="apellido" id="vis_apellido" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Procedencia</label>
                        <input type="text" name="procedencia" id="vis_procedencia" class="form-control" placeholder="Entidad o Ciudad">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Teléfono</label>
                        <input type="text" name="telefono" id="vis_telefono" class="form-control">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Género</label>
                        <select name="genero" id="vis_genero" class="form-select">
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="O">Otro</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label fw-bold">Correo</label>
                        <input type="email" name="correo" id="vis_correo" class="form-control">
                    </div>
                    <div class="col-12">
                        <label class="form-label fw-bold">Motivo Frecuente / Observaciones</label>
                        <textarea name="motivo_frecuente" id="vis_motivo" class="form-control" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar Visitante</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoVisitante() {
        document.getElementById('modalVisitanteLabel').innerText = 'Nuevo Visitante';
        document.getElementById('vis_id').value = '';
        document.getElementById('vis_id_persona').value = '';
        document.querySelector('#modalVisitante form').reset();
    }
    function editarVisitante(v) {
        document.getElementById('modalVisitanteLabel').innerText = 'Editar: ' + v.nombre;
        document.getElementById('vis_id').value = v.id;
        document.getElementById('vis_id_persona').value = v.id_persona;
        document.getElementById('vis_cedula').value = v.cedula;
        document.getElementById('vis_nombre').value = v.nombre;
        document.getElementById('vis_apellido').value = v.apellido;
        document.getElementById('vis_procedencia').value = v.procedencia;
        document.getElementById('vis_telefono').value = v.telefono;
        document.getElementById('vis_genero').value = v.genero;
        document.getElementById('vis_correo').value = v.correo;
        document.getElementById('vis_motivo').value = v.motivo_frecuente;
        new bootstrap.Modal(document.getElementById('modalVisitante')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
