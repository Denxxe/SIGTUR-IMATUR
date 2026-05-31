<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Seguridad</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Cuentas de acceso al sistema y roles asignados.</p>
    </div>
    <div class="page__actions">
        <?php if (!empty($data['empleados_sin_cuenta'])): ?>
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalUsuario" onclick="nuevoUsuario()">
            <i class="bi bi-shield-plus"></i> Crear Cuenta
        </button>
        <?php else: ?>
        <button type="button" class="btn-sig btn-sig--primary" disabled title="Todos los empleados ya tienen cuenta">
            <i class="bi bi-shield-check"></i> Todos con acceso
        </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Usuario</th>
                <th>Empleado</th>
                <th>Rol</th>
                <th>Último acceso</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['usuarios'])): ?>
                <tr><td colspan="5" class="sig-table-empty">No hay cuentas registradas.</td></tr>
            <?php else: ?>
            <?php foreach ($data['usuarios'] ?? [] as $user): ?>
                <tr>
                    <td class="cell-strong" style="color:var(--brand-600)">
                        <i class="bi bi-person-circle" style="margin-right:4px;"></i><?php echo htmlspecialchars($user->username ?? ''); ?>
                    </td>
                    <td><?php echo htmlspecialchars(($user->nombre ?? 'N/A') . ' ' . ($user->apellido ?? '')); ?></td>
                    <td>
                        <?php
                        $rolClase = match((int)($user->id_rol ?? 0)) {
                            1 => 'sig-badge--danger',
                            2 => 'sig-badge--info',
                            3 => 'sig-badge--success',
                            4 => 'sig-badge--warning',
                            5 => 'sig-badge--neutral',
                            default => 'sig-badge--neutral',
                        };
                        ?>
                        <span class="sig-badge <?php echo $rolClase; ?>"><?php echo htmlspecialchars($user->rol ?? 'Sin rol'); ?></span>
                    </td>
                    <td style="font-size:12px; color:var(--text-secondary);">
                        <?php echo $user->ultimo_login ? date('d/m/Y H:i', strtotime($user->ultimo_login)) : '—'; ?>
                    </td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit"
                                onclick='editarUsuario(<?php echo htmlspecialchars(json_encode($user), ENT_QUOTES, "UTF-8"); ?>)'>
                            <i class="bi bi-key"></i> Credenciales
                        </button>
                        <?php if ((int)$user->id !== (int)($_SESSION['user_id'] ?? 0)): ?>
                        <a href="<?php echo URL_ROOT; ?>/usuarios/delete/<?php echo $user->id; ?>"
                           class="row-action row-action--del delete-btn">
                            <i class="bi bi-slash-circle"></i> Suspender
                        </a>
                        <?php else: ?>
                        <span class="row-action" style="opacity:.5;cursor:not-allowed;" title="No puedes suspender tu propia cuenta">
                            <i class="bi bi-person-fill-check"></i> Tu cuenta
                        </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="modalUsuario" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/usuarios/store" method="POST" class="modal-content" id="formUsuario" onsubmit="return validarPassword()">
            <div class="modal-header">
                <h5 class="modal-title" id="modalUsuarioLabel">Configurar Acceso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="user_id">

                <!-- Solo en creación -->
                <div class="sig-field mb-3" id="div_empleado">
                    <label class="sig-field__label">Empleado <span class="req">*</span></label>
                    <select name="id_empleado" id="user_id_empleado" class="sig-select">
                        <option value="">Seleccione al empleado...</option>
                        <?php foreach ($data['empleados_sin_cuenta'] ?? [] as $e): ?>
                            <option value="<?php echo $e->id; ?>">
                                <?php echo htmlspecialchars($e->nombre . ' ' . $e->apellido); ?>
                                (<?php echo htmlspecialchars($e->cedula); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($data['empleados_sin_cuenta'])): ?>
                    <small style="color:var(--text-secondary); font-size:11px;">
                        <i class="bi bi-info-circle"></i> Todos los empleados activos ya tienen cuenta.
                    </small>
                    <?php endif; ?>
                </div>

                <div class="sig-field mb-3">
                    <label class="sig-field__label">Rol en el Sistema <span class="req">*</span></label>
                    <select name="id_rol" id="user_id_rol" class="sig-select" required>
                        <?php foreach ($data['roles'] ?? [] as $r): ?>
                            <option value="<?php echo $r->id; ?>"><?php echo htmlspecialchars($r->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="sig-field mb-3">
                    <label class="sig-field__label">Username <span class="req">*</span></label>
                    <input type="text" name="username" id="user_username" class="sig-input" required
                           placeholder="Ej: jperaza.rrhh" autocomplete="off">
                </div>

                <div class="sig-field mb-2">
                    <label class="sig-field__label">Contraseña <span class="req" id="pass_req_star">*</span></label>
                    <input type="password" name="password" id="user_password" class="sig-input"
                           placeholder="Mínimo 6 caracteres" autocomplete="new-password">
                </div>

                <div class="sig-field mb-3" id="div_confirmar">
                    <label class="sig-field__label">Confirmar Contraseña <span class="req">*</span></label>
                    <input type="password" id="user_password2" class="sig-input"
                           placeholder="Repita la contraseña" autocomplete="new-password">
                    <small id="pass_mismatch" style="display:none; color:var(--danger-500); font-size:11px;">
                        <i class="bi bi-exclamation-triangle"></i> Las contraseñas no coinciden.
                    </small>
                </div>

                <small id="pass_notice" style="display:none; color:var(--text-secondary); font-size:11px;">
                    <i class="bi bi-info-circle"></i> Deje en blanco para mantener la contraseña actual.
                </small>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
function nuevoUsuario() {
    document.getElementById('modalUsuarioLabel').innerText = 'Crear Cuenta de Acceso';
    document.getElementById('user_id').value = '';
    document.getElementById('div_empleado').style.display = 'flex';
    document.getElementById('div_confirmar').style.display = 'flex';
    document.getElementById('user_id_empleado').required = true;
    document.getElementById('user_password').required = true;
    document.getElementById('pass_req_star').style.display = 'inline';
    document.getElementById('pass_notice').style.display = 'none';
    document.querySelector('#formUsuario').reset();
}

function editarUsuario(user) {
    document.getElementById('modalUsuarioLabel').innerText = 'Actualizar: ' + user.username;
    document.getElementById('user_id').value          = user.id;
    document.getElementById('div_empleado').style.display  = 'none';
    document.getElementById('div_confirmar').style.display = 'none';
    document.getElementById('user_id_empleado').required   = false;
    document.getElementById('user_id_rol').value      = user.id_rol;
    document.getElementById('user_username').value    = user.username;
    document.getElementById('user_password').value    = '';
    document.getElementById('user_password').required = false;
    document.getElementById('pass_req_star').style.display = 'none';
    document.getElementById('pass_notice').style.display = 'block';
    document.getElementById('pass_mismatch').style.display = 'none';
    new bootstrap.Modal(document.getElementById('modalUsuario')).show();
}

function validarPassword() {
    const esNuevo = document.getElementById('user_id').value === '';
    const pass1 = document.getElementById('user_password').value;
    const pass2 = document.getElementById('user_password2').value;

    if (esNuevo) {
        if (pass1.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres.');
            return false;
        }
        if (pass1 !== pass2) {
            document.getElementById('pass_mismatch').style.display = 'block';
            return false;
        }
    } else if (pass1 && pass1 !== pass2) {
        document.getElementById('pass_mismatch').style.display = 'block';
        return false;
    }
    return true;
}

document.getElementById('user_password2').addEventListener('input', function () {
    const pass1 = document.getElementById('user_password').value;
    document.getElementById('pass_mismatch').style.display =
        (this.value && this.value !== pass1) ? 'block' : 'none';
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
