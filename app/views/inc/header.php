<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($data['titulo']) ? $data['titulo'] . ' - ' : ''; ?>SIGTUR-IMATUR</title>
    
    <!-- Bootstrap 5 Local -->
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/libs/bootstrap.min.css">
    <!-- Bootstrap Icons CDN (fallback a texto si no carga) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --sb-w: 260px;
            --sb-bg: #0f172a;
            --sb-hover: rgba(255,255,255,.07);
            --sb-active: #1a73e8;
            --sb-text: #94a3b8;
            --sb-text-hover: #e2e8f0;
            --topbar-h: 58px;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f1f5f9;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            overflow-x: hidden;
        }

        /* ==================== SIDEBAR ==================== */
        .sidebar {
            width: var(--sb-w);
            height: 100vh;
            background: var(--sb-bg);
            position: fixed;
            top: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: transform .3s cubic-bezier(.4,0,.2,1);
        }

        /* Brand */
        .sb-brand {
            padding: 16px 20px;
            background: rgba(0,0,0,.25);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-shrink: 0;
        }
        .sb-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #1a73e8, #06b6d4);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: #fff; font-weight: 800;
        }
        .sb-brand-text {
            color: #fff;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .5px;
            line-height: 1.2;
        }
        .sb-brand-text small {
            display: block;
            font-size: 10px;
            color: #64748b;
            font-weight: 400;
            letter-spacing: .3px;
        }

        /* Navegación scrollable */
        .sb-nav {
            flex: 1;
            overflow-y: auto;
            overflow-x: hidden;
            padding: 8px 0 20px;
        }
        .sb-nav::-webkit-scrollbar { width: 4px; }
        .sb-nav::-webkit-scrollbar-track { background: transparent; }
        .sb-nav::-webkit-scrollbar-thumb { background: #334155; border-radius: 4px; }

        /* Secciones colapsables */
        .sb-section {
            padding: 18px 16px 6px;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #475569;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: pointer;
            user-select: none;
        }
        .sb-section:hover { color: #64748b; }
        .sb-section .sb-chevron {
            font-size: 10px;
            transition: transform .2s;
        }
        .sb-section.collapsed .sb-chevron { transform: rotate(-90deg); }
        .sb-group { overflow: hidden; transition: max-height .3s ease; max-height: 500px; }
        .sb-group.collapsed { max-height: 0; }

        /* Links */
        .sb-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 9px 18px;
            color: var(--sb-text);
            text-decoration: none;
            font-size: 13.5px;
            border-left: 3px solid transparent;
            transition: all .15s;
            white-space: nowrap;
        }
        .sb-link i { font-size: 16px; width: 20px; text-align: center; flex-shrink: 0; }
        .sb-link:hover {
            color: var(--sb-text-hover);
            background: var(--sb-hover);
            border-left-color: #334155;
        }
        .sb-link.active {
            color: #fff;
            background: linear-gradient(90deg, rgba(26,115,232,.15), transparent);
            border-left-color: var(--sb-active);
            font-weight: 600;
        }
        .sb-link.active i { color: var(--sb-active); }

        /* Footer del sidebar */
        .sb-footer {
            padding: 12px 18px;
            border-top: 1px solid #1e293b;
            flex-shrink: 0;
            background: rgba(0,0,0,.15);
        }
        .sb-user {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sb-avatar {
            width: 34px; height: 34px;
            background: linear-gradient(135deg, #1a73e8, #8b5cf6);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 14px; flex-shrink: 0;
        }
        .sb-user-info {
            flex: 1;
            min-width: 0;
        }
        .sb-user-name {
            color: #e2e8f0;
            font-size: 13px;
            font-weight: 600;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sb-user-role {
            color: #64748b;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        .sb-logout {
            color: #ef4444;
            font-size: 18px;
            text-decoration: none;
            flex-shrink: 0;
            transition: transform .15s;
        }
        .sb-logout:hover { color: #f87171; transform: scale(1.15); }

        /* ==================== TOPBAR ==================== */
        .topbar {
            position: fixed;
            top: 0;
            left: var(--sb-w);
            right: 0;
            height: var(--topbar-h);
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            z-index: 1030;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            transition: left .3s cubic-bezier(.4,0,.2,1);
        }
        .topbar-left { display: flex; align-items: center; gap: 12px; }
        .topbar-toggle {
            display: none;
            background: none; border: none;
            font-size: 22px; color: #1e293b;
            cursor: pointer; padding: 4px 8px;
            border-radius: 6px;
        }
        .topbar-toggle:hover { background: #f1f5f9; }
        .topbar-breadcrumb {
            font-size: 14px;
            color: #64748b;
        }
        .topbar-breadcrumb strong { color: #1e293b; }
        .topbar-right {
            display: flex; align-items: center; gap: 16px;
        }
        .topbar-user {
            display: flex; align-items: center; gap: 8px;
            font-size: 13px; color: #64748b;
        }
        .topbar-user strong { color: #1e293b; }

        /* ==================== MAIN CONTENT ==================== */
        .main-content {
            margin-left: var(--sb-w);
            padding: calc(var(--topbar-h) + 20px) 24px 24px;
            min-height: 100vh;
            transition: margin-left .3s cubic-bezier(.4,0,.2,1);
        }

        /* ==================== OVERLAY MOBILE ==================== */
        .sb-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,.5);
            z-index: 1035;
            backdrop-filter: blur(2px);
        }
        .sb-overlay.show { display: block; }

        /* ==================== RESPONSIVE ==================== */
        @media (max-width: 991px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-left: 0; }
            .topbar { left: 0; }
            .topbar-toggle { display: block; }
        }

        /* Cards y Tablas */
        .card { border: none; border-radius: .5rem; }
        .table > :not(caption) > * > * { vertical-align: middle; }
    </style>
</head>
<body>

<!-- Overlay móvil -->
<div class="sb-overlay" id="sbOverlay" onclick="closeSidebar()"></div>

<!-- ========== SIDEBAR ========== -->
<nav class="sidebar" id="sidebar">
    <!-- Brand -->
    <div class="sb-brand">
        <div class="sb-brand-icon">S</div>
        <div class="sb-brand-text">
            SIGTUR-IMATUR
            <small>Gestión Turística v2.0</small>
        </div>
    </div>

    <!-- Navegación scrollable -->
    <div class="sb-nav">
        <!-- Dashboard -->
        <a class="sb-link" href="<?php echo URL_ROOT; ?>">
            <i class="bi bi-speedometer2"></i> Panel Principal
        </a>

        <!-- RRHH -->
        <div class="sb-section" onclick="toggleSection(this)">
            <span>RRHH</span>
            <i class="bi bi-chevron-down sb-chevron"></i>
        </div>
        <div class="sb-group">
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/empleados/index">
                <i class="bi bi-person-badge"></i> Empleados
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/cargos/index">
                <i class="bi bi-briefcase"></i> Cargos
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/departamentos/index">
                <i class="bi bi-building"></i> Departamentos
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/asistencias/index">
                <i class="bi bi-clock-history"></i> Asistencia
            </a>
        </div>

        <!-- Inventario -->
        <div class="sb-section" onclick="toggleSection(this)">
            <span>Inventario</span>
            <i class="bi bi-chevron-down sb-chevron"></i>
        </div>
        <div class="sb-group">
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/inventario/index">
                <i class="bi bi-box-seam"></i> Bienes
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/categorias/index">
                <i class="bi bi-tags"></i> Categorías
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/ubicaciones/index">
                <i class="bi bi-geo-alt"></i> Ubicaciones
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/actividadesinventario/index">
                <i class="bi bi-arrow-left-right"></i> Movimientos
            </a>
        </div>

        <!-- Formación -->
        <div class="sb-section" onclick="toggleSection(this)">
            <span>Formación</span>
            <i class="bi bi-chevron-down sb-chevron"></i>
        </div>
        <div class="sb-group">
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/talleres/index">
                <i class="bi bi-mortarboard"></i> Talleres
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/ubicacionesformacion/index">
                <i class="bi bi-pin-map"></i> Sedes de Formación
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/pasantes/index">
                <i class="bi bi-person-video3"></i> Gest. de Pasantes
            </a>
        </div>

        <!-- Turismo -->
        <div class="sb-section" onclick="toggleSection(this)">
            <span>Turismo</span>
            <i class="bi bi-chevron-down sb-chevron"></i>
        </div>
        <div class="sb-group">
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/rutas/index">
                <i class="bi bi-compass"></i> Rutas Turísticas
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/actividadesruta/index">
                <i class="bi bi-calendar-event"></i> Actividades y Eventos
            </a>
        </div>

        <!-- Reportes -->
        <div class="sb-section" onclick="toggleSection(this)">
            <span>Reportes</span>
            <i class="bi bi-chevron-down sb-chevron"></i>
        </div>
        <div class="sb-group">
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/reportes/index">
                <i class="bi bi-bar-chart-line"></i> Centro de Reportes
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/reportes/indicadores">
                <i class="bi bi-graph-up-arrow"></i> Indicadores
            </a>
        </div>

        <!-- ADMINISTRACIÓN (Solo Administrador) -->
        <?php if($_SESSION['user_rol'] == 1): ?>
        <div class="sb-section" onclick="toggleSection(this)">
            <span>Administración</span>
            <i class="bi bi-chevron-down sb-chevron"></i>
        </div>
        <div class="sb-group">
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/usuarios/index">
                <i class="bi bi-people"></i> Usuarios
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/roles/index">
                <i class="bi bi-shield-lock"></i> Roles y Permisos
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/auditoria/index">
                <i class="bi bi-shield-check"></i> Bitácora del Sistema
            </a>
            <a class="sb-link" href="<?php echo URL_ROOT; ?>/auditoria/papelera">
                <i class="bi bi-recycle"></i> Papelera de Reciclaje
            </a>
        </div>
        <?php endif; ?>
    </div>

    <!-- Footer: Usuario -->
    <div class="sb-footer">
        <div class="sb-user">
            <div class="sb-avatar">
                <?php echo strtoupper(substr($_SESSION['user_username'] ?? 'U', 0, 1)); ?>
            </div>
            <div class="sb-user-info">
                <div class="sb-user-name"><?php echo $_SESSION['user_username'] ?? 'Usuario'; ?></div>
                <div class="sb-user-role"><?php echo $_SESSION['user_rol_name'] ?? 'Administrador'; ?></div>
            </div>
            <a href="<?php echo URL_ROOT; ?>/auth/logout" class="sb-logout" title="Cerrar Sesión">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </div>
</nav>

<!-- ========== TOPBAR ========== -->
<div class="topbar">
    <div class="topbar-left">
        <button class="topbar-toggle" onclick="toggleSidebar()" aria-label="Menú">
            <i class="bi bi-list"></i>
        </button>
        <div class="topbar-breadcrumb">
            <strong><?php echo $data['titulo'] ?? 'Panel Principal'; ?></strong>
        </div>
    </div>
    <div class="topbar-right">
        <div class="topbar-user d-none d-md-flex">
            <i class="bi bi-person-circle" style="font-size:20px;"></i>
            <span>Hola, <strong><?php echo $_SESSION['user_username'] ?? 'Admin'; ?></strong></span>
        </div>
        <a href="<?php echo URL_ROOT; ?>/auth/logout" class="btn btn-sm btn-outline-danger d-none d-md-inline-block">
            <i class="bi bi-power"></i> Salir
        </a>
    </div>
</div>

<!-- ========== MAIN CONTENT ========== -->
<div class="main-content">
    <main>
