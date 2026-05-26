<!DOCTYPE html>
<html lang="es" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" />
    <title><?php echo isset($data['titulo']) ? $data['titulo'] . ' - ' : ''; ?>SIGTUR-IMATUR</title>

    <!-- Bootstrap 5 Local -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css">
    <!-- Bootstrap Icons Local -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap-icons.min.css">
    <!-- SIGTUR Design System -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/sigtur-tokens.css">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/sigtur-components.css">
</head>
<body>

<!-- Overlay móvil -->
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<!-- ========== APP SHELL ========== -->
<div class="app-shell">

    <!-- ========== SIDEBAR ========== -->
    <nav class="sidebar" id="sidebar">
        <div class="sidebar__brand">
            <div class="sidebar__logo">
                <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="Logo">
            </div>
            <div>
                <div class="sidebar__brand-name">SIGTUR-IMATUR</div>
                <div class="sidebar__brand-sub">Gestión Turística v2.0</div>
            </div>
        </div>

        <?php $rol = (int)($_SESSION['user_rol'] ?? 0); ?>
        <div class="sidebar__nav">
            <!-- Dashboard — todos los roles -->
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>">
                <i class="bi bi-speedometer2"></i> <span>Panel Principal</span>
            </a>

            <!-- RRHH — Administrador (1) y RRHH (2) -->
            <?php if(in_array($rol, [1, 2])): ?>
            <div class="sidebar__group-label">RRHH</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/empleados/index">
                <i class="bi bi-person-badge"></i> <span>Empleados</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/cargos/index">
                <i class="bi bi-briefcase"></i> <span>Cargos</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/departamentos/index">
                <i class="bi bi-building"></i> <span>Departamentos</span>
            </a>
            <?php endif; ?>
            <?php if(in_array($rol, [1, 2, 5])): ?>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/asistencias/index">
                <i class="bi bi-clock-history"></i> <span>Asistencia</span>
            </a>
            <?php endif; ?>

            <!-- Recepción — Administrador (1), RRHH (2), Turismo (3) y Recepción (5) -->
            <?php if(in_array($rol, [1, 2, 3, 5])): ?>
            <div class="sidebar__group-label">Recepción</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/visitantes/index">
                <i class="bi bi-door-open"></i> <span>Visitas</span>
            </a>
            <?php endif; ?>

            <!-- Inventario — Administrador (1) e Inventario (4) -->
            <?php if(in_array($rol, [1, 4])): ?>
            <div class="sidebar__group-label">Inventario</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/inventario/index">
                <i class="bi bi-box-seam"></i> <span>Bienes</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/categorias/index">
                <i class="bi bi-tags"></i> <span>Categorías</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/ubicaciones/index">
                <i class="bi bi-geo-alt"></i> <span>Ubicaciones</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/actividadesinventario/index">
                <i class="bi bi-arrow-left-right"></i> <span>Movimientos</span>
            </a>
            <?php endif; ?>

            <!-- Formación — Administrador (1) y Turismo (3) -->
            <?php if(in_array($rol, [1, 3])): ?>
            <div class="sidebar__group-label">Formación</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/talleres/index">
                <i class="bi bi-mortarboard"></i> <span>Talleres</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/ubicacionesformacion/index">
                <i class="bi bi-pin-map"></i> <span>Sedes Formación</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/pasantes/index">
                <i class="bi bi-person-video3"></i> <span>Pasantes</span>
            </a>
            <?php endif; ?>

            <!-- Turismo — Administrador (1) y Turismo (3) -->
            <?php if(in_array($rol, [1, 3])): ?>
            <div class="sidebar__group-label">Turismo</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/rutas/index">
                <i class="bi bi-compass"></i> <span>Rutas Turísticas</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/actividadesruta/index">
                <i class="bi bi-calendar-event"></i> <span>Actividades</span>
            </a>
            <?php endif; ?>

            <!-- Reportes — todos los roles -->
            <div class="sidebar__group-label">Análisis</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/reportes/index">
                <i class="bi bi-bar-chart-line"></i> <span>Reportes</span>
            </a>

            <!-- Configuración — Admin (1) y RRHH (2) -->
            <?php if(in_array($rol, [1, 2])): ?>
            <div class="sidebar__group-label">Sistema</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/config/index">
                <i class="bi bi-gear"></i> <span>Configuración</span>
            </a>
            <?php endif; ?>

            <!-- Sistema — solo Administrador (1) -->
            <?php if($rol == 1): ?>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/usuarios/index">
                <i class="bi bi-people"></i> <span>Usuarios</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/roles/index">
                <i class="bi bi-shield-lock"></i> <span>Roles y Permisos</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/municipio/index">
                <i class="bi bi-map"></i> <span>Municipios</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/parroquia/index">
                <i class="bi bi-signpost"></i> <span>Parroquias</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/auditoria/index">
                <i class="bi bi-shield-check"></i> <span>Auditoría</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/auditoria/papelera">
                <i class="bi bi-recycle"></i> <span>Papelera</span>
            </a>
            <?php endif; ?>
        </div>

        <!-- Footer: Usuario -->
        <div class="sidebar__user">
            <div class="sidebar__user-avatar">
                <?php echo strtoupper(substr($_SESSION['user_username'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="sidebar__user-meta">
                <div class="sidebar__user-name"><?php echo $_SESSION['user_username'] ?? 'Usuario'; ?></div>
                <div class="sidebar__user-role"><?php echo $_SESSION['user_rol_name'] ?? 'Administrador'; ?></div>
            </div>
            <a href="<?php echo URL_ROOT; ?>/auth/logout" class="sidebar__logout" title="Cerrar Sesión">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </nav>

    <!-- ========== MAIN AREA ========== -->
    <div class="main-area">
        <!-- Header -->
        <header class="sig-header">
            <button class="sig-header__icon-btn sidebar-toggle" onclick="toggleSidebar()" aria-label="Menú">
                <i class="bi bi-list" style="font-size:20px;"></i>
            </button>
            <div class="sig-header__breadcrumb">
                <i class="bi bi-house-door" style="font-size:14px; color:var(--brand-500);"></i>
                <span class="sig-header__breadcrumb-sep">/</span>
                <span class="sig-header__breadcrumb-current">Gestión</span>
            </div>
            <div class="sig-header__actions">
                <button class="sig-header__icon-btn" onclick="toggleTheme()" title="Cambiar tema" id="themeToggleBtn">
                    <i class="bi bi-moon" id="themeIcon" style="font-size:17px;"></i>
                </button>
                <div class="sig-header__user-pill">
                    <div class="sig-header__user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="sig-header__user-name">Hola, <?php echo $_SESSION['user_username'] ?? 'Admin'; ?></div>
                </div>
                <a href="<?php echo URL_ROOT; ?>/auth/logout" class="btn-sig btn-sig--danger btn-sig--sm" style="font-size:12px;">
                    <i class="bi bi-power"></i> <span class="sig-header__logout-text">Salir</span>
                </a>
            </div>
        </header>

        <!-- Content -->
        <div class="page">
            <?php flash('global_msg'); ?>