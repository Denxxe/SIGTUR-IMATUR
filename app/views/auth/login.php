<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - SIGTUR-IMATUR</title>
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/assets/images/Logo_imatur.png" />
    <!-- Bootstrap 5 CSS Local -->
    <link href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap-icons.css" rel="stylesheet">
    <link href="<?php echo URL_ROOT; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            padding-top: 40px;
            padding-bottom: 40px;
            min-height: 100vh;
        }
        .form-signin {
            max-width: 400px;
            padding: 15px;
        }
    </style>
</head>
<body class="text-center w-100">

<main class="form-signin w-100 m-auto">
    <div class="card shadow-sm border-0">
        <div class="card-body p-4 p-md-5">
            <h3 class="mb-4 text-primary fw-bold">SIGTUR-IMATUR</h3>
            <h6 class="text-muted mb-4">Ingrese sus credenciales</h6>

            <form action="<?php echo URL_ROOT; ?>/auth/login" method="POST">
                
                <div class="form-floating mb-3">
                    <input type="text" name="username" class="form-control <?php echo (!empty($data['username_err'])) ? 'is-invalid' : ''; ?>" id="floatingInput" placeholder="Usuario" value="<?php echo $data['username']; ?>">
                    <label for="floatingInput">Usuario</label>
                    <span class="invalid-feedback"><?php echo $data['username_err']; ?></span>
                </div>
                
                <div class="form-floating mb-3">
                    <input type="password" name="password" class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" id="floatingPassword" placeholder="Contraseña">
                    <label for="floatingPassword">Contraseña</label>
                    <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                </div>

                <div class="checkbox mb-3 text-start">
                    <label>
                        <input type="checkbox" value="remember-me"> Recordarme
                    </label>
                </div>
                
                <button class="w-100 btn btn-lg btn-primary" type="submit">Iniciar Sesión</button>
            </form>
        </div>
    </div>
    <p class="mt-5 mb-3 text-muted">&copy; <?php echo date('Y'); ?> IMATUR</p>
</main>

<script src="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.bundle.min.js"></script>
</body>
</html>
