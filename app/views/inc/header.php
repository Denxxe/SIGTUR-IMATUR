<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['titulo']) ? $data['titulo'] . ' - ' : ''; ?>SIGTUR-IMATUR</title>
    
    <!-- Bootstrap 5 Local -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css">
    
    <style>
        :root {
            --sidebar-width: 260px;
            --primary: #1a73e8;
            --dark: #1e293b;
        }
        body {
            background-color: #f1f5f9;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .sidebar {
            width: var(--sidebar-width);
            min-height: 100vh;
            background: var(--dark);
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            padding-top: 0;
            transition: transform .3s;
        }
        .sidebar .brand {
            padding: 1.2rem 1rem;
            background: rgba(0,0,0,.2);
            color: #fff;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: .5px;
        }
        .sidebar .nav-section {
            padding: .5rem 1rem .2rem;
            color: #64748b;
            font-size: .7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }
        .sidebar .nav-link {
            color: #94a3b8;
            padding: .55rem 1rem;
            font-size: .88rem;
            border-radius: 0;
            transition: all .15s;
        }
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            color: #fff;
            background: rgba(255,255,255,.08);
        }
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 1.5rem 2rem;
        }
        .card {
            border: none;
            border-radius: .5rem;
        }
        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<nav class="sidebar" id="sidebar">
    <div class="brand">
        SIGTUR-IMATUR
    </div>
    <ul class="nav flex-column mt-2">
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>">Panel Principal</a></li>

        <li class="nav-section mt-3">RRHH</li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/empleados/index">Empleados</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/cargos/index">Cargos</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/departamentos/index">Departamentos</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/asistencias/index">Asistencia</a></li>

        <li class="nav-section mt-3">Inventario</li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/inventario/index">Bienes</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/categorias/index">Categorías</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/ubicaciones/index">Ubicaciones</a></li>

        <li class="nav-section mt-3">Formación</li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/talleres/index">Talleres</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/ubicacionesformacion/index">Sedes</a></li>

        <li class="nav-section mt-3">Turismo</li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/rutas/index">Rutas</a></li>

        <li class="nav-section mt-3">Sistema</li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/usuarios/index">Usuarios</a></li>
        <li><a class="nav-link" href="<?php echo URL_ROOT; ?>/roles/index">Roles</a></li>
    </ul>
</nav>

<!-- Main -->
<div class="main-content">
    <!-- Top bar mobile -->
    <div class="d-md-none mb-3">
        <button class="btn btn-dark" onclick="document.getElementById('sidebar').classList.toggle('show')">☰ Menú</button>
    </div>

    <main>
