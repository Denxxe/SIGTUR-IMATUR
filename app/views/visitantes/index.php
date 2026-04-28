<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Gestión de Usuarios Externos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Registro y control de visitantes, turistas y personas externas al instituto.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalVisitante" onclick="nuevoVisitante()">
            <i class="bi bi-person-plus"></i> Registrar Visitante
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Nombre y Apellido</th>
                <th>Procedencia</th>
                <th>Teléfono</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['visitantes'])): ?>
                <tr>
                    <td colspan="5" class="sig-table-empty">No hay visitantes registrados actualmente.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['visitantes'] as $v): ?>
                    <tr>
                        <td class="cell-id"><?php echo $v->cedula; ?></td>
                        <td class="cell-strong"><?php echo $v->nombre . ' ' . $v->apellido; ?></td>
                        <td><?php echo $v->procedencia; ?></td>
                        <td><?php echo $v->telefono; ?></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarVisitante(<?php echo json_encode($v); ?>)'>
                                <i class="bi bi-pencil"></i>
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/visitantes/delete/<?php echo $v->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
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

                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Cédula <span class="req">*</span></label>
                            <input type="text" name="cedula" id="vis_cedula" class="sig-input" required placeholder="V-00.000.000">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Nombres <span class="req">*</span></label>
                            <input type="text" name="nombre" id="vis_nombre" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Apellidos <span class="req">*</span></label>
                            <input type="text" name="apellido" id="vis_apellido" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Procedencia</label>
                            <input type="text" name="procedencia" id="vis_procedencia" class="sig-input" placeholder="Ciudad o Entidad">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Teléfono</label>
                            <input type="text" name="telefono" id="vis_telefono" class="sig-input">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Género</label>
                            <select name="genero" id="vis_genero" class="sig-select">
                                <option value="M">Masculino</option>
                                <option value="F">Femenino</option>
                                <option value="O">Otro</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <div class="sig-field">
                            <label class="sig-field__label">Correo Electrónico</label>
                            <input type="email" name="correo" id="vis_correo" class="sig-input" placeholder="ejemplo@correo.com">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field">
                            <label class="sig-field__label">Observaciones</label>
                            <textarea name="motivo_frecuente" id="vis_motivo" class="sig-textarea" rows="2" placeholder="Motivo de visita frecuente, notas especiales..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar Visitante</button>
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