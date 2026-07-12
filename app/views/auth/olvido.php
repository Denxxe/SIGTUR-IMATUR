<!DOCTYPE html>
<html lang="es" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - SIGTUR-IMATUR</title>
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" />
    <link href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/assets/css/sigtur-tokens.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/assets/css/login.css" rel="stylesheet">
</head>

<body>
    <div class="login-card">
        <div class="login-brand">
            <div class="login-brand-icon">
                <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="Logo">
            </div>
            <h1>SIGTUR-IMATUR</h1>
            <p>Recuperar contraseña</p>
        </div>

        <?php if (!empty($data['enviado'])): ?>
            <div class="error-msg" role="alert" style="margin-bottom:1rem;padding:.6rem .8rem;border:1px solid #badbcc;background:#d1e7dd;color:#0f5132;border-radius:.4rem;text-align:center;">
                Si los datos corresponden a una cuenta con correo registrado, se envió un enlace de recuperación (válido 30 minutos). Revisa tu bandeja de entrada.
            </div>
        <?php endif; ?>

        <form action="<?php echo URL_ROOT; ?>/auth/enviarRecuperacion" method="POST">
            <div class="login-field">
                <label for="identificador">Usuario o correo</label>
                <input type="text" name="identificador" id="identificador" placeholder="Ingresa tu usuario o correo"
                    autocomplete="username"
                    class="<?php echo (!empty($data['identificador_err'])) ? 'is-invalid' : ''; ?>"
                    value="<?php echo htmlspecialchars($data['identificador'] ?? ''); ?>">
                <?php if (!empty($data['identificador_err'])): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($data['identificador_err']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="login-btn">Enviar enlace</button>
        </form>

        <div style="text-align:center;margin-top:1rem;">
            <a href="<?php echo URL_ROOT; ?>/auth/login" style="font-size:.85rem;">Volver a iniciar sesión</a>
        </div>

        <div class="login-footer">&copy; <?php echo date('Y'); ?> IMATUR — Gestión Turística v2.0</div>
    </div>

    <script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
</body>

</html>
