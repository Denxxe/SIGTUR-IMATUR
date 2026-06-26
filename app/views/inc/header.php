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
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/sigtur-tokens.css?v=<?php echo @filemtime('../public/assets/css/sigtur-tokens.css'); ?>">
    <link rel="stylesheet" href="<?php echo URL_ROOT; ?>/assets/css/sigtur-components.css?v=<?php echo @filemtime('../public/assets/css/sigtur-components.css'); ?>">
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
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/horarios/index">
                <i class="bi bi-clock"></i> <span>Horarios</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/amonestaciones/index">
                <i class="bi bi-flag"></i> <span>Amonestaciones</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/permisos/index">
                <i class="bi bi-calendar2-week"></i> <span>Permisos y Reposos</span>
            </a>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/vacaciones/index">
                <i class="bi bi-umbrella"></i> <span>Vacaciones</span>
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
            <?php endif; ?>

            <!-- Reportes — todos los roles -->
            <div class="sidebar__group-label">Análisis</div>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/reportes/index">
                <i class="bi bi-bar-chart-line"></i> <span>Reportes</span>
            </a>

            <!-- Sistema — Configuración, administración y recuperación (gating por permiso) -->
            <?php
            $canConfig   = in_array($rol, [1, 2]);
            $canSysAdmin = ($rol == 1);
            $canBitacora = ($rol == 1) || RolesController::roleHasModulo('AuditoriaController');
            $canPapelera = ($rol == 1) || RolesController::roleHasModulo('AuditoriaPapelera');
            if ($canConfig || $canSysAdmin || $canBitacora || $canPapelera):
            ?>
            <div class="sidebar__group-label">Sistema</div>
            <?php if ($canConfig): ?>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/config/index">
                <i class="bi bi-gear"></i> <span>Configuración</span>
            </a>
            <?php endif; ?>
            <?php if ($canSysAdmin): ?>
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
            <?php endif; ?>
            <?php if ($canBitacora): ?>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/auditoria/index">
                <i class="bi bi-shield-check"></i> <span>Auditoría</span>
            </a>
            <?php endif; ?>
            <?php if ($canPapelera): ?>
            <a class="sidebar__item" href="<?php echo URL_ROOT; ?>/auditoria/papelera">
                <i class="bi bi-recycle"></i> <span>Papelera</span>
            </a>
            <?php endif; ?>
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

            <!-- Búsqueda global -->
            <form method="get" action="<?php echo URL_ROOT; ?>/buscar/index" class="sig-header__search" role="search"
                  style="margin-left:auto;display:flex;align-items:center;gap:6px;max-width:340px;flex:1;">
                <div style="position:relative;flex:1;">
                    <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);font-size:13px;color:var(--text-tertiary);"></i>
                    <input type="text" name="q" placeholder="Buscar…" aria-label="Buscar"
                           value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
                           style="width:100%;padding:7px 10px 7px 30px;border:1px solid var(--border-subtle);border-radius:8px;background:var(--bg-surface);color:var(--text-primary);font-size:13px;">
                </div>
            </form>

            <div class="sig-header__actions">
                <!-- Centro de notificaciones (campana) -->
                <?php
                $___alertas  = CentroAlertas::resumenCacheado($rol);
                $___total    = 0; $___visibles = [];
                foreach ($___alertas as $___a) {
                    if ((int)$___a['n'] > 0) {
                        $___visibles[] = $___a;
                        if (in_array($___a['sev'], ['warning', 'danger'], true)) $___total += (int)$___a['n'];
                    }
                }
                $___sevColor = ['info' => '#0891B2', 'warning' => '#D97706', 'danger' => '#DC2626'];
                ?>
                <div class="dropdown">
                    <button class="sig-header__icon-btn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notificaciones" title="Notificaciones" style="position:relative;">
                        <i class="bi bi-bell" style="font-size:17px;"></i>
                        <?php if ($___total > 0): ?>
                            <span style="position:absolute;top:1px;right:1px;min-width:16px;height:16px;padding:0 4px;border-radius:8px;background:#DC2626;color:#fff;font-size:10px;font-weight:700;line-height:16px;text-align:center;">
                                <?php echo $___total > 99 ? '99+' : $___total; ?>
                            </span>
                        <?php endif; ?>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end shadow" style="width:330px;max-width:90vw;padding:0;overflow:hidden;">
                        <div style="padding:10px 14px;border-bottom:1px solid var(--border-subtle);font-weight:700;font-size:13px;display:flex;align-items:center;justify-content:space-between;">
                            <span><i class="bi bi-bell"></i> Notificaciones</span>
                            <span style="font-size:11px;color:var(--text-tertiary);font-weight:500;"><?php echo count($___visibles); ?> aviso(s)</span>
                        </div>
                        <div style="max-height:360px;overflow-y:auto;">
                            <?php if (empty($___visibles)): ?>
                                <div style="padding:22px 14px;text-align:center;color:var(--text-tertiary);font-size:13px;">
                                    <i class="bi bi-check2-circle" style="font-size:1.4rem;display:block;margin-bottom:6px;color:#059669;"></i>
                                    Sin alertas pendientes.
                                </div>
                            <?php else: foreach ($___visibles as $___a):
                                $___c = $___sevColor[$___a['sev']] ?? '#64748B'; ?>
                                <a href="<?php echo $___a['url']; ?>" class="dropdown-item" style="display:flex;gap:10px;align-items:flex-start;padding:10px 14px;white-space:normal;border-bottom:1px solid var(--border-subtle);">
                                    <span style="flex-shrink:0;width:30px;height:30px;border-radius:8px;background:<?php echo $___c; ?>1f;display:flex;align-items:center;justify-content:center;">
                                        <i class="bi <?php echo $___a['icono']; ?>" style="color:<?php echo $___c; ?>;font-size:15px;"></i>
                                    </span>
                                    <span style="min-width:0;flex:1;">
                                        <span style="display:flex;align-items:center;gap:6px;">
                                            <span style="font-weight:700;font-size:12.5px;color:var(--text-primary);"><?php echo htmlspecialchars($___a['titulo']); ?></span>
                                            <span style="margin-left:auto;background:<?php echo $___c; ?>;color:#fff;border-radius:10px;padding:0 7px;font-size:11px;font-weight:700;"><?php echo (int)$___a['n']; ?></span>
                                        </span>
                                        <span style="display:block;font-size:11.5px;color:var(--text-tertiary);"><?php echo htmlspecialchars($___a['desc']); ?></span>
                                    </span>
                                </a>
                            <?php endforeach; endif; ?>
                        </div>
                        <?php if (in_array($rol, [1, 2], true)): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/alertas" class="dropdown-item" style="text-align:center;padding:9px;font-size:12px;font-weight:600;color:var(--brand-600);">
                            Ver centro de alertas <i class="bi bi-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <button class="sig-header__icon-btn" onclick="toggleTheme()" title="Cambiar tema" aria-label="Cambiar tema claro/oscuro" id="themeToggleBtn">
                    <i class="bi bi-moon" id="themeIcon" style="font-size:17px;"></i>
                </button>
                <a href="<?php echo URL_ROOT; ?>/perfil/index" class="sig-header__user-pill" style="text-decoration:none;color:inherit;" title="Mi perfil">
                    <div class="sig-header__user-avatar">
                        <?php echo strtoupper(substr($_SESSION['user_username'] ?? 'A', 0, 1)); ?>
                    </div>
                    <div class="sig-header__user-name">Hola, <?php echo $_SESSION['user_username'] ?? 'Admin'; ?></div>
                </a>
                <a href="<?php echo URL_ROOT; ?>/auth/logout" class="btn-sig btn-sig--danger btn-sig--sm" style="font-size:12px;">
                    <i class="bi bi-power"></i> <span class="sig-header__logout-text">Salir</span>
                </a>
            </div>
        </header>

        <!-- Content -->
        <div class="page">
            <?php flash('global_msg'); ?>