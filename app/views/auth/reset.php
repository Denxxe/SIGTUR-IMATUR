<!DOCTYPE html>
<html lang="es" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña - SIGTUR-IMATUR</title>
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
            <p>Restablecer contraseña</p>
        </div>

        <form action="<?php echo URL_ROOT; ?>/auth/procesarReset" method="POST">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($data['token'] ?? ''); ?>">

            <div class="login-field">
                <label for="password">Nueva contraseña</label>
                <input type="password" name="password" id="password" placeholder="Mínimo 8 caracteres, letra y número"
                    autocomplete="new-password"
                    class="<?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>">
                <?php if (!empty($data['password_err'])): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($data['password_err']); ?></div>
                <?php endif; ?>
            </div>

            <div class="login-field">
                <label for="password_confirm">Confirmar contraseña</label>
                <input type="password" name="password_confirm" id="password_confirm" placeholder="Repite la contraseña"
                    autocomplete="new-password"
                    class="<?php echo (!empty($data['confirm_err'])) ? 'is-invalid' : ''; ?>">
                <?php if (!empty($data['confirm_err'])): ?>
                    <div class="error-msg"><?php echo htmlspecialchars($data['confirm_err']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="login-btn">Restablecer contraseña</button>
        </form>

        <div class="login-footer">&copy; <?php echo date('Y'); ?> IMATUR — Gestión Turística v2.0</div>
    </div>

    <script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
</body>

</html>
