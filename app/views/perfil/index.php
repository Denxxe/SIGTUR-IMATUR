<?php require_once '../app/views/inc/header.php'; ?>

<?php $u = $data['usuario'] ?? null; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Mi cuenta</div>
        <h1 class="page__title">Mi Perfil</h1>
        <p class="page__subtitle">Administra tu nombre de usuario y contraseña de acceso.</p>
    </div>
</div>

<div class="row g-4 anim-slide-up">

    <!-- Tarjeta de identidad (solo lectura) -->
    <div class="col-md-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-person-badge u-c-brand-600"></i> Datos del empleado</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <div class="u-ta-center u-mb-sp-5">
                    <div class="u-w-64px u-br-50pct u-ai-center u-jc-center u-fw-800 u-mb-sp-3" style="height:64px; background:var(--brand-600); display:inline-flex; font-size:1.75rem; color:#fff;">
                        <?php echo strtoupper(substr($u->nombre ?? $_SESSION['user_username'] ?? 'U', 0, 1)); ?>
                    </div>
                    <div class="u-fw-700 u-c-text-primary" style="font-size:1.1rem;">
                        <?php echo htmlspecialchars(trim(($u->nombre ?? '') . ' ' . ($u->apellido ?? '')) ?: ($_SESSION['user_username'] ?? '')); ?>
                    </div>
                    <span class="sig-badge sig-badge--brand u-mt-4px"><?php echo htmlspecialchars($u->rol_nombre ?? ''); ?></span>
                </div>
                <table class="u-w-100pct u-fs-13px" style="border-collapse:collapse;">
                    <?php foreach ([
                        ['bi-person','Usuario',   $u->username ?? ''],
                        ['bi-card-text','Cédula',  $u->cedula   ?? '—'],
                        ['bi-envelope','Correo',   $u->correo   ?? '—'],
                        ['bi-telephone','Teléfono',$u->telefono ?? '—'],
                    ] as [$ico,$lbl,$val]): ?>
                    <tr class="u-bdt-1px-solid-border-subtle">
                        <td class="u-c-text-secondary u-ws-nowrap" style="padding:8px 8px 8px 0;">
                            <i class="bi <?php echo $ico; ?>" style="margin-right:5px;"></i><?php echo $lbl; ?>
                        </td>
                        <td class="u-fw-600 u-c-text-primary" style="padding:8px 0;">
                            <?php echo htmlspecialchars($val); ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Cambiar username -->
    <div class="col-md-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-at u-c-x3B82F6"></i> Nombre de usuario</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <p class="u-fs-13px u-c-text-secondary u-mb-sp-4">
                    Este es el nombre con el que inicias sesión. Debe ser único en el sistema.
                </p>
                <form action="<?php echo URL_ROOT; ?>/perfil/cambiarUsername" method="POST">
                    <div class="sig-field mb-4">
                        <label class="sig-field__label" for="username">Nuevo nombre de usuario</label>
                        <input id="username" type="text" name="username" class="sig-input" required minlength="3"
                               value="<?php echo htmlspecialchars($u->username ?? ''); ?>"
                               autocomplete="off" placeholder="Mínimo 3 caracteres">
                    </div>
                    <button type="submit" class="btn-sig btn-sig--primary u-w-100pct">
                        <i class="bi bi-check-lg"></i> Actualizar usuario
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cambiar contraseña -->
    <div class="col-md-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-key u-c-x7C3AED"></i> Contraseña</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <p class="u-fs-13px u-c-text-secondary u-mb-sp-4">
                    Mínimo 6 caracteres. Si olvidaste la contraseña actual, el Administrador puede restablecerla.
                </p>
                <form action="<?php echo URL_ROOT; ?>/perfil/cambiarPassword" method="POST" onsubmit="return validarFormPerfil(this)">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="password_actual">Contraseña actual <span class="req">*</span></label>
                        <input id="password_actual" type="password" name="password_actual" class="sig-input" required autocomplete="current-password">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="pf_nuevo">Nueva contraseña <span class="req">*</span></label>
                        <input type="password" name="password_nuevo" id="pf_nuevo" class="sig-input" required minlength="6" autocomplete="new-password" placeholder="Mínimo 6 caracteres">
                    </div>
                    <div class="sig-field mb-4">
                        <label class="sig-field__label" for="pf_confirmar">Confirmar nueva contraseña <span class="req">*</span></label>
                        <input type="password" name="password_confirmar" id="pf_confirmar" class="sig-input" required autocomplete="new-password" placeholder="Repite la contraseña">
                        <small id="pf_mismatch" style="display:none;color:var(--danger-500);font-size:11px;">
                            <i class="bi bi-exclamation-triangle"></i> Las contraseñas no coinciden.
                        </small>
                    </div>
                    <button type="submit" class="btn-sig btn-sig--primary u-w-100pct">
                        <i class="bi bi-shield-lock"></i> Cambiar contraseña
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
function validarFormPerfil(form) {
    const nueva   = document.getElementById('pf_nuevo').value;
    const confirma= document.getElementById('pf_confirmar').value;
    const mm      = document.getElementById('pf_mismatch');
    if (nueva.length < 6) { alert('La nueva contraseña debe tener al menos 6 caracteres.'); return false; }
    if (nueva !== confirma) { mm.style.display = 'block'; return false; }
    mm.style.display = 'none';
    return true;
}
document.getElementById('pf_confirmar').addEventListener('input', function () {
    const mm = document.getElementById('pf_mismatch');
    mm.style.display = (this.value && this.value !== document.getElementById('pf_nuevo').value) ? 'block' : 'none';
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
