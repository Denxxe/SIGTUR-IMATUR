<?php require_once '../app/views/inc/header.php'; ?>

<?php
$u = $data['usuario'] ?? null;
$tieneFicha = !empty($u->id_persona);
$nombreCompleto = trim(($u->nombre ?? '') . ' ' . ($u->apellido ?? '')) ?: ($_SESSION['user_username'] ?? '');
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Sistema · Mi cuenta</div>
        <h1 class="page__title">Mi Perfil</h1>
        <p class="page__subtitle">Tus datos de contacto, tu nombre de usuario y tu contraseña.</p>
    </div>
</div>

<div class="row g-4 anim-slide-up">

    <!-- Identidad (solo lectura: la mantiene Talento Humano) -->
    <div class="col-lg-4">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-person-badge u-c-brand-600"></i> Mi cuenta</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <div class="u-ta-center u-mb-sp-5">
                    <div class="u-w-64px u-br-50pct u-ai-center u-jc-center u-fw-800 u-mb-sp-3" style="height:64px; background:var(--brand-600); display:inline-flex; font-size:1.75rem; color:#fff;">
                        <?php echo htmlspecialchars(mb_strtoupper(mb_substr($u->nombre ?: ($_SESSION['user_username'] ?? 'U'), 0, 1))); ?>
                    </div>
                    <div class="u-fw-700 u-c-text-primary" style="font-size:1.1rem;"><?php echo htmlspecialchars($nombreCompleto); ?></div>
                    <span class="sig-badge sig-badge--brand u-mt-4px"><?php echo htmlspecialchars($u->rol_nombre ?? ''); ?></span>
                </div>
                <table class="u-w-100pct u-fs-13px" style="border-collapse:collapse;">
                    <?php foreach ([
                        ['bi-person',      'Usuario', $u->username ?? ''],
                        ['bi-card-text',   'Cédula',  ($u->cedula ?? '') ?: '—'],
                        ['bi-clock-history','Último acceso', !empty($u->last_login) ? date('d/m/Y H:i', strtotime($u->last_login)) : '—'],
                    ] as [$ico, $lbl, $val]): ?>
                    <tr class="u-bdt-1px-solid-border-subtle">
                        <td class="u-c-text-secondary u-ws-nowrap" style="padding:8px 8px 8px 0;">
                            <i class="bi <?php echo $ico; ?>" style="margin-right:5px;"></i><?php echo $lbl; ?>
                        </td>
                        <td class="u-fw-600 u-c-text-primary" style="padding:8px 0; overflow-wrap:anywhere;"><?php echo htmlspecialchars($val); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
                <p class="u-fs-12px u-c-text-tertiary u-mt-sp-4 mb-0">
                    <i class="bi bi-info-circle"></i> Nombre, apellido y cédula los actualiza Talento Humano desde tu expediente.
                </p>
            </div>
        </div>
    </div>

    <!-- Datos de contacto -->
    <div class="col-lg-8">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-person-lines-fill u-c-x059669"></i> Datos de contacto</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <?php if (!$tieneFicha): ?>
                    <p class="u-fs-13px u-c-text-secondary mb-0">
                        Tu usuario no está vinculado a una ficha de personal, por eso no tiene correo ni teléfono propios.
                        Pídele a Talento Humano que lo vincule para poder recuperar tu contraseña por correo.
                    </p>
                <?php else: ?>
                <p class="u-fs-13px u-c-text-secondary u-mb-sp-4">
                    El correo es el que recibe el enlace para recuperar tu contraseña y con el que también puedes iniciar sesión.
                </p>
                <form action="<?php echo URL_ROOT; ?>/perfil/actualizarContacto" method="POST" id="pf_contacto">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label" for="pf_correo">Correo electrónico</label>
                                <input id="pf_correo" type="email" name="correo" class="sig-input" maxlength="100"
                                       autocomplete="email" placeholder="nombre@dominio.com"
                                       data-original="<?php echo htmlspecialchars($u->correo ?? ''); ?>"
                                       value="<?php echo htmlspecialchars($u->correo ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label" for="pf_telefono">Teléfono</label>
                                <input id="pf_telefono" type="tel" name="telefono" class="sig-input"
                                       value="<?php echo htmlspecialchars($u->telefono ?? ''); ?>">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sig-field">
                                <label class="sig-field__label" for="pf_direccion">Dirección</label>
                                <textarea id="pf_direccion" name="direccion" class="sig-input" rows="2" maxlength="300"
                                          placeholder="Sector, calle, casa o edificio"><?php echo htmlspecialchars($u->direccion ?? ''); ?></textarea>
                            </div>
                        </div>
                        <!-- Solo aparece si se cambia el correo (ver PerfilController::actualizarContacto). -->
                        <div class="col-md-6" id="pf_confirma_wrap" style="display:none;">
                            <div class="sig-field">
                                <label class="sig-field__label" for="pf_pass_contacto">Contraseña actual <span class="req">*</span></label>
                                <input id="pf_pass_contacto" type="password" name="password_actual_contacto" class="sig-input" autocomplete="current-password">
                                <small class="u-fs-11px u-c-text-tertiary">Por seguridad, para cambiar el correo confirma tu contraseña.</small>
                            </div>
                        </div>
                    </div>
                    <button type="submit" class="btn-sig btn-sig--primary u-mt-sp-4">
                        <i class="bi bi-check-lg"></i> Guardar datos de contacto
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Cambiar username -->
    <div class="col-lg-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-at u-c-x3B82F6"></i> Nombre de usuario</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <p class="u-fs-13px u-c-text-secondary u-mb-sp-4">
                    Es el nombre con el que inicias sesión. Debe ser único: de 3 a 50 caracteres, con letras, números,
                    punto, guion o guion bajo (sin espacios ni @).
                </p>
                <form action="<?php echo URL_ROOT; ?>/perfil/cambiarUsername" method="POST">
                    <div class="sig-field mb-4">
                        <label class="sig-field__label" for="pf_username">Nuevo nombre de usuario</label>
                        <input id="pf_username" type="text" name="username" class="sig-input" required
                               minlength="3" maxlength="50" pattern="[A-Za-z0-9._\-]{3,50}"
                               title="3 a 50 caracteres: letras, números, punto, guion o guion bajo"
                               value="<?php echo htmlspecialchars($u->username ?? ''); ?>"
                               autocomplete="off" autocapitalize="none" spellcheck="false">
                    </div>
                    <button type="submit" class="btn-sig btn-sig--primary u-w-100pct">
                        <i class="bi bi-check-lg"></i> Actualizar usuario
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Cambiar contraseña -->
    <div class="col-lg-6">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-key u-c-x7C3AED"></i> Contraseña</div>
            </div>
            <div class="sig-card__body u-p-sp-5">
                <p class="u-fs-13px u-c-text-secondary u-mb-sp-4">
                    Mínimo 8 caracteres, con al menos una letra y un número. Si olvidaste la actual, usa
                    «¿Olvidaste tu contraseña?» en la pantalla de inicio o pídele al Administrador que la restablezca.
                </p>
                <form action="<?php echo URL_ROOT; ?>/perfil/cambiarPassword" method="POST" onsubmit="return validarFormPerfil(this)">
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="password_actual">Contraseña actual <span class="req">*</span></label>
                        <input id="password_actual" type="password" name="password_actual" class="sig-input" required autocomplete="current-password">
                    </div>
                    <div class="sig-field mb-3">
                        <label class="sig-field__label" for="pf_nuevo">Nueva contraseña <span class="req">*</span></label>
                        <input type="password" name="password_nuevo" id="pf_nuevo" class="sig-input" required minlength="8" autocomplete="new-password" placeholder="Mínimo 8, letra y número">
                    </div>
                    <div class="sig-field mb-4">
                        <label class="sig-field__label" for="pf_confirmar">Confirmar nueva contraseña <span class="req">*</span></label>
                        <input type="password" name="password_confirmar" id="pf_confirmar" class="sig-input" required autocomplete="new-password" placeholder="Repite la contraseña">
                        <small id="pf_mismatch" style="display:none;color:var(--danger-500);font-size:11px;">
                            <i class="bi bi-exclamation-triangle"></i> <span id="pf_mismatch_txt">Las contraseñas no coinciden.</span>
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
// Misma política que Usuario::passwordPolicyError() (mín. 8, una letra y un número).
function validarFormPerfil(form) {
    const nueva    = document.getElementById('pf_nuevo').value;
    const confirma = document.getElementById('pf_confirmar').value;
    const mm  = document.getElementById('pf_mismatch');
    const txt = document.getElementById('pf_mismatch_txt');
    let error = '';
    if (nueva.length < 8 || !/[A-Za-z]/.test(nueva) || !/\d/.test(nueva)) error = 'La nueva contraseña debe tener al menos 8 caracteres, una letra y un número.';
    else if (nueva !== confirma) error = 'Las contraseñas no coinciden.';
    txt.textContent = error;
    mm.style.display = error ? 'block' : 'none';
    return !error;
}
document.getElementById('pf_confirmar').addEventListener('input', function () {
    const mm = document.getElementById('pf_mismatch');
    document.getElementById('pf_mismatch_txt').textContent = 'Las contraseñas no coinciden.';
    mm.style.display = (this.value && this.value !== document.getElementById('pf_nuevo').value) ? 'block' : 'none';
});

// Cambiar el correo pide la contraseña actual: el campo aparece solo entonces.
(function () {
    const correo = document.getElementById('pf_correo');
    if (!correo) return;
    const wrap = document.getElementById('pf_confirma_wrap');
    const pass = document.getElementById('pf_pass_contacto');
    const revisar = () => {
        const cambio = correo.value.trim().toLowerCase() !== (correo.dataset.original || '').trim().toLowerCase();
        wrap.style.display = cambio ? '' : 'none';
        pass.required = cambio;
        if (!cambio) pass.value = '';
    };
    correo.addEventListener('input', revisar);
    revisar();
})();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
