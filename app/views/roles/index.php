<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Seguridad</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalRol" onclick="nuevoRol()"><i class="bi bi-plus-lg"></i> Nuevo Rol</button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead><tr><th>ID</th><th>Nombre</th><th>Descripción</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php foreach ($data['roles'] as $rol): ?>
                <tr>
                    <td><span class="cell-id"><?php echo $rol->id; ?></span></td>
                    <td class="cell-strong"><?php echo $rol->nombre; ?></td>
                    <td style="color:var(--text-secondary)"><?php echo $rol->descripcion; ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarRol(<?php echo json_encode($rol); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                        <a href="<?php echo URL_ROOT; ?>/roles/delete/<?php echo $rol->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalRol" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/roles/store" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalRolLabel">Nuevo Rol</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="rol_id">
                <div class="sig-field mb-3"><label class="sig-field__label">Nombre <span class="req">*</span></label><input type="text" name="nombre" id="rol_nombre" class="sig-input" required></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Descripción</label><textarea name="descripcion" id="rol_descripcion" class="sig-textarea" rows="3"></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button></div>
        </form>
    </div>
</div>

<script>
    function nuevoRol() { document.getElementById('modalRolLabel').innerText='Nuevo Rol'; document.getElementById('rol_id').value=''; document.getElementById('rol_nombre').value=''; document.getElementById('rol_descripcion').value=''; }
    function editarRol(rol) { document.getElementById('modalRolLabel').innerText='Editar: '+rol.nombre; document.getElementById('rol_id').value=rol.id; document.getElementById('rol_nombre').value=rol.nombre; document.getElementById('rol_descripcion').value=rol.descripcion; new bootstrap.Modal(document.getElementById('modalRol')).show(); }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
