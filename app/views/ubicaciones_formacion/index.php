<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Formación · Sedes</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Gestión de sedes y centros para talleres comunitarios.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalUbiForm" onclick="nuevaUbi()">
            <i class="bi bi-plus-lg"></i> Nueva Sede
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Tipo</th>
                <th>Parroquia</th>
                <th>Dirección</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['ubicaciones'])): ?>
                <tr><td colspan="5" class="sig-table-empty">No hay sedes registradas.</td></tr>
            <?php else: ?>
                <?php foreach ($data['ubicaciones'] as $u): ?>
                    <tr>
                        <td class="cell-strong"><?php echo $u->nombre; ?></td>
                        <td><span class="sig-badge sig-badge--info"><?php echo $u->tipo; ?></span></td>
                        <td><?php echo $u->parroquia; ?></td>
                        <td style="font-size:12.5px;color:var(--text-secondary)"><?php echo $u->direccion; ?></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarUbi(<?php echo json_encode($u); ?>)'>
                                <i class="bi bi-pencil"></i> Editar
                            </button>
                            <a href="<?php echo URL_ROOT; ?>/ubicacionesformacion/delete/<?php echo $u->id; ?>" class="row-action row-action--del delete-btn">
                                <i class="bi bi-trash"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUbiForm" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/ubicacionesformacion/store" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title" id="modalUbiFormLabel">Nueva Sede de Formación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="ubif_id">
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" id="ubif_nombre" class="sig-input" required placeholder="Ej: Liceo Bolivariano">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Tipo de Espacio</label>
                    <select name="tipo" id="ubif_tipo" class="sig-select">
                        <option value="Liceo">Liceo</option>
                        <option value="Plaza">Plaza</option>
                        <option value="Centro Comunitario">Centro Comunitario</option>
                        <option value="Auditorio">Auditorio</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Parroquia <span class="req">*</span></label>
                    <select name="parroquia" id="ubif_parroquia" class="sig-select" required>
                        <option value="">Seleccione una parroquia</option>
                        <?php foreach ($data['parroquias'] as $p): ?>
                            <option value="<?php echo $p->id; ?>"><?php echo $p->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Dirección <span class="req">*</span></label>
                    <textarea name="direccion" id="ubif_direccion" class="sig-textarea" rows="2" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
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