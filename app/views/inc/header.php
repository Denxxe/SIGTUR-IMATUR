<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['titulo']) ? $data['titulo'] . ' - ' : ''; ?>SIGTUR-IMATUR</title>
    
    <!-- Bootstrap 5 Local -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css">
    
    <!-- Estilos Personalizados (Soporte Offline) -->
    <style>
        :root {
            --primary-color: #0d6efd;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --info-color: #0dcaf0;
            --warning-color: #ffc107;
            --danger-color: #dc3545;
            --light-color: #f8f9fa;
            --dark-color: #212529;
        }
        body {
            background-color: #f4f7f6;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar-brand {
            font-weight: bold;
            letter-spacing: 1px;
        }
        .main-container {
            padding-top: 2rem;
            padding-bottom: 5rem;
        }
        .card {
            border: none;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="<?php echo URL_ROOT; ?>">SIGTUR-IMATUR</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URL_ROOT; ?>/cargos/index">Cargos</a>
                    </li>
                    <!-- Más links luego -->
                </ul>
            </div>
        </div>
    </nav>
    <main class="container main-container">
