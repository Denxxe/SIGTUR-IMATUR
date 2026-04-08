<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-person-lock"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <button type="button" class="btn btn-warning shadow-sm" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="nuevoUsuario()">
            <i class="bi bi-shield-plus"></i> Crear Cuenta de Acceso
        </button>
    </div>
</div>

<div class="card shadow-sm border-warning">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-dark">
                <tr>
                    <th class="ps-4">Usuario</th>
                    <th>Empleado</th>
                    <th>Rol de Acceso</th>
                    <th class="text-center">Estado</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['usuarios'] as $user): ?>
                    <tr>
                        <td class="ps-4 fw-bold text-primary"><?php echo $user->username; ?></td>
                        <td><?php echo $user->nombre . ' ' . $user->apellido; ?></td>
                        <td><span class="badge bg-secondary"><?php echo $user->rol; ?></span></td>
                        <td class="text-center">
                            <span class="badge bg-success">Activo</span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-info" onclick='editarUsuario(<?php echo json_encode($user); ?>)'>Credenciales</button>
                                <a href="<?php echo URL_ROOT; ?>/usuarios/delete/<?php echo $user->id; ?>" class="btn btn-outline-danger delete-btn">Suspender</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/usuarios/store" method="POST" class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="modalUsuarioLabel">Configurar Acceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="user_id">
                
                <div class="mb-3" id="div_empleado">
                    <label class="form-label fw-bold">Empleado Relacionado</label>
                    <select name="id_empleado" id="user_id_empleado" class="form-select">
                        <option value="">Seleccione al empleado...</option>
                        <?php foreach ($data['empleados'] as $e): ?>
                            <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?> (<?php echo $e->cedula; ?>)</option>
                        <?php endforeach; ?>
                    </select>
                    <small class="text-muted">Un usuario debe estar vinculado a un empleado registrado.</small>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Rol en el Sistema</label>
                    <select name="id_rol" id="user_id_rol" class="form-select" required>
                        <?php foreach ($data['roles'] as $r): ?>
                            <option value="<?php echo $r->id; ?>"><?php echo $r->nombre; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre de Usuario (Username)</label>
                    <input type="text" name="username" id="user_username" class="form-control" required placeholder="Ej: jperaza.rrhh">
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Contraseña</label>
                    <input type="password" name="password" id="user_password" class="form-control" placeholder="Escriba la contraseña">
                    <small class="text-danger" id="pass_notice" style="display:none;">Deje en blanco para mantener la actual.</small>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-warning">Guardar Credenciales</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoUsuario() {
        document.getElementById('modalUsuarioLabel').innerText = 'Crear Cuenta de Acceso';
        document.getElementById('user_id').value = '';
        document.getElementById('div_empleado').style.display = 'block';
        document.getElementById('user_id_empleado').required = true;
        document.getElementById('user_password').required = true;
        document.getElementById('pass_notice').style.display = 'none';
        
        const form = document.querySelector('#modalUsuario form');
        form.reset();
    }

    function editarUsuario(user) {
        document.getElementById('modalUsuarioLabel').innerText = 'Actualizar Acceso: ' + user.username;
        document.getElementById('user_id').value = user.id;
        document.getElementById('div_empleado').style.display = 'none'; // No se cambia de empleado una vez creado
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
