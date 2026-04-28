<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SIGTUR-IMATUR</title>
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" />
    <link href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/assets/css/sigtur-tokens.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, var(--brand-950) 0%, var(--slate-900) 50%, var(--brand-900) 100%);
            font-family: var(--font-sans);
            position: relative; overflow: hidden;
        }
        body::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 600px 400px at 30% 20%, rgba(52, 97, 246, 0.15), transparent),
                        radial-gradient(ellipse 500px 300px at 70% 80%, rgba(251, 191, 36, 0.08), transparent);
            pointer-events: none;
        }
        .login-card {
            background: var(--bg-surface);
            border-radius: var(--r-xl);
            box-shadow: var(--sh-xl);
            width: 100%; max-width: 420px;
            padding: 48px 40px 36px;
            animation: slideUp 0.6s var(--ease-out);
            position: relative; z-index: 1;
        }
        .login-brand {
            display: flex; flex-direction: column; align-items: center; gap: 12px;
            margin-bottom: 32px;
        }
        .login-brand-icon {
            width: 64px; height: 64px; border-radius: 18px;
            background: linear-gradient(135deg, var(--brand-500), var(--brand-700));
            display: grid; place-items: center;
            box-shadow: 0 8px 24px -4px rgba(34, 71, 219, 0.5);
        }
        .login-brand-icon img { width: 48px; height: 48px; object-fit: contain; }
        .login-brand h1 { font-size: 24px; font-weight: 800; color: var(--text-primary); letter-spacing: -0.02em; }
        .login-brand p { font-size: 13px; color: var(--text-secondary); margin: 0; }
        .login-field { margin-bottom: 20px; }
        .login-field label { display: block; font-size: 12px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px; }
        .login-field input {
            width: 100%; height: 46px; padding: 0 14px;
            border: 1.5px solid var(--border-default); border-radius: 11px;
            font-size: 14px; font-family: inherit;
            color: var(--text-primary); background: var(--bg-surface);
            transition: all var(--t-fast);
        }
        .login-field input:focus { outline: none; border-color: var(--brand-400); box-shadow: 0 0 0 3px rgba(52, 97, 246, 0.12); }
        .login-field input.is-invalid { border-color: var(--danger-500); }
        .login-field .error-msg { font-size: 11px; color: var(--danger-500); margin-top: 4px; }
        .login-btn {
            width: 100%; height: 46px; border: none;
            background: linear-gradient(180deg, var(--brand-500), var(--brand-700));
            color: white; font-size: 15px; font-weight: 700;
            border-radius: 11px; cursor: pointer;
            box-shadow: var(--sh-glow-brand);
            transition: all var(--t-fast) var(--ease-out);
        }
        .login-btn:hover { transform: translateY(-1px); box-shadow: 0 12px 32px -4px rgba(34, 71, 219, 0.5); }
        .login-btn:active { transform: translateY(1px); }
        .login-footer { text-align: center; margin-top: 32px; font-size: 12px; color: var(--text-tertiary); }
        @keyframes slideUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
    </style>
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

        <form action="<?php echo URL_ROOT; ?>/auth/login" method="POST">
            <div class="login-field">
                <label>Usuario</label>
                <input type="text" name="username" placeholder="Ingrese su usuario"
                    class="<?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>"
                    value="<?php echo $data['username']; ?>">
                <?php if(!empty($data['username_err'])): ?>
                    <div class="error-msg"><?php echo $data['username_err']; ?></div>
                <?php endif; ?>
            </div>

            <div class="login-field">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="Ingrese su contraseña"
                    class="<?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>">
                <?php if(!empty($data['password_err'])): ?>
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