<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Seguridad</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="nuevoUsuario()">
            <i class="bi bi-shield-plus"></i> Crear Cuenta
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead><tr><th>Usuario</th><th>Empleado</th><th>Rol</th><th>Estado</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php foreach ($data['usuarios'] as $user): ?>
                <tr>
                    <td class="cell-strong" style="color:var(--brand-600)"><?php echo $user->username; ?></td>
                    <td><?php echo $user->nombre . ' ' . $user->apellido; ?></td>
                    <td><span class="sig-badge sig-badge--neutral"><?php echo $user->rol; ?></span></td>
                    <td><span class="sig-badge sig-badge--success">Activo</span></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarUsuario(<?php echo json_encode($user); ?>)'><i class="bi bi-key"></i> Credenciales</button>
                        <a href="<?php echo URL_ROOT; ?>/usuarios/delete/<?php echo $user->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-slash-circle"></i> Suspender</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/usuarios/store" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalUsuarioLabel">Configurar Acceso</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="user_id">
                <div class="sig-field mb-3" id="div_empleado">
                    <label class="sig-field__label">Empleado Relacionado</label>
                    <select name="id_empleado" id="user_id_empleado" class="sig-select">
                        <option value="">Seleccione al empleado...</option>
                        <?php foreach ($data['empleados'] as $e): ?>
                            <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?> (<?php echo $e->cedula; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Rol en el Sistema <span class="req">*</span></label>
                    <select name="id_rol" id="user_id_rol" class="sig-select" required>
                        <?php foreach ($data['roles'] as $r): ?>
                            <option value="<?php echo $r->id; ?>"><?php echo $r->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Username <span class="req">*</span></label>
                    <input type="text" name="username" id="user_username" class="sig-input" required placeholder="Ej: jperaza.rrhh">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Contraseña</label>
                    <input type="password" name="password" id="user_password" class="sig-input" placeholder="Escriba la contraseña">
                    <small id="pass_notice" style="display:none;color:var(--danger-500);font-size:11px">Deje en blanco para mantener la actual.</small>
                </div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button></div>
        </form>
    </div>
</div>

<script>
    function nuevoUsuario() {
        document.getElementById('modalUsuarioLabel').innerText = 'Crear Cuenta de Acceso';
        document.getElementById('user_id').value = '';
        document.getElementById('div_empleado').style.display = 'flex';
        document.getElementById('user_id_empleado').required = true;
        document.getElementById('user_password').required = true;
        document.getElementById('pass_notice').style.display = 'none';
        document.querySelector('#modalUsuario form').reset();
    }
    function editarUsuario(user) {
        document.getElementById('modalUsuarioLabel').innerText = 'Actualizar: ' + user.username;
        document.getElementById('user_id').value = user.id;
        document.getElementById('div_empleado').style.display = 'none';
        document.getElementById('user_id_empleado').required = false;
        document.getElementById('user_id_rol').value = user.id_rol;
        document.getElementById('user_username').value = user.username;
        document.getElementById('user_password').value = '';
        document.getElementById('user_password').required = false;
        document.getElementById('pass_notice').style.display = 'block';
        new bootstrap.Modal(document.getElementById('modalUsuario')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
