<!DOCTYPE html>
<html lang="es" data-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SIGTUR-IMATUR</title>
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
            <p>Sistema Integral de Gestión Turística</p>
        </div>

        <?php if (!empty($data['login_err'])): ?>
            <div class="error-msg" role="alert" style="margin-bottom:1rem;padding:.6rem .8rem;border:1px solid #f5c2c7;background:#f8d7da;color:#842029;border-radius:.4rem;text-align:center;">
                <?php echo htmlspecialchars($data['login_err']); ?>
            </div>
        <?php elseif (isset($_GET['expired'])): ?>
            <div class="error-msg" role="alert" style="margin-bottom:1rem;padding:.6rem .8rem;border:1px solid #ffecb5;background:#fff3cd;color:#664d03;border-radius:.4rem;text-align:center;">
                Tu sesión se cerró por inactividad. Inicia sesión nuevamente.
            </div>
        <?php endif; ?>

        <form action="<?php echo URL_ROOT; ?>/auth/login" method="POST">
            <div class="login-field">
                <label for="username">Usuario</label>
                <input type="text" name="username" id="username" placeholder="Ingrese su usuario"
                    autocomplete="username"
                    class="<?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $data['username'] ?? ''; ?>">
                <?php if (!empty($data['username_err'])): ?>
                    <div class="error-msg"><?php echo $data['username_err']; ?></div>
                <?php endif; ?>
            </div>

            <div class="login-field">
                <label for="password">Contraseña</label>
                <input type="password" name="password" id="password" placeholder="Ingrese su contraseña"
                    autocomplete="current-password"
                    class="<?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>">
                <?php if (!empty($data['password_err'])): ?>
                    <div class="error-msg"><?php echo $data['password_err']; ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="login-btn">Iniciar Sesión</button>
        </form>

        <div class="login-footer">&copy; <?php echo date('Y'); ?> IMATUR — Gestión Turística v2.0</div>
    </div>

    <script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
</body>

</html>