<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Análisis · Reportes</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Selecciona un tipo de reporte para generar.</p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(3,1fr);gap:var(--sp-4);margin-bottom:var(--sp-4)" class="anim-slide-up">
    <div class="sig-card" style="text-align:center;padding:var(--sp-8) var(--sp-6)">
        <div style="font-size:48px;margin-bottom:12px">📋</div>
        <h5 style="font-weight:700;color:var(--text-primary);margin-bottom:8px">Reporte de Asistencia</h5>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">Historial de asistencia del personal con filtros por fecha</p>
        <a href="<?php echo URL_ROOT; ?>/reportes/asistencia" class="btn-sig btn-sig--primary">Generar</a>
    </div>
    <div class="sig-card" style="text-align:center;padding:var(--sp-8) var(--sp-6)">
        <div style="font-size:48px;margin-bottom:12px">🎓</div>
        <h5 style="font-weight:700;color:var(--text-primary);margin-bottom:8px">Reporte de Talleres</h5>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">Estadísticas de talleres, participantes e instructores</p>
        <a href="<?php echo URL_ROOT; ?>/reportes/talleres" class="btn-sig btn-sig--primary" style="--brand-500:var(--accent-500);--brand-600:var(--accent-600);--brand-700:var(--accent-700)">Generar</a>
    </div>
    <div class="sig-card" style="text-align:center;padding:var(--sp-8) var(--sp-6)">
        <div style="font-size:48px;margin-bottom:12px">🗺️</div>
        <h5 style="font-weight:700;color:var(--text-primary);margin-bottom:8px">Reporte de Rutas</h5>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">Estado de rutas turísticas y equipamiento asignado</p>
        <a href="<?php echo URL_ROOT; ?>/reportes/rutas" class="btn-sig btn-sig--primary" style="background:linear-gradient(180deg,var(--teal-500),var(--teal-600))">Generar</a>
    </div>
</div>
<div style="display:grid;grid-template-columns:1fr 2fr;gap:var(--sp-4)" class="anim-slide-up">
    <div class="sig-card" style="text-align:center;padding:var(--sp-8) var(--sp-6)">
        <div style="font-size:48px;margin-bottom:12px">👨‍🎓</div>
        <h5 style="font-weight:700;color:var(--text-primary);margin-bottom:8px">Reporte de Pasantes</h5>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">Control de practicantes, tutores y documentos</p>
        <a href="<?php echo URL_ROOT; ?>/reportes/pasantes" class="btn-sig btn-sig--ghost">Generar</a>
    </div>
    <div class="sig-card" style="text-align:center;padding:var(--sp-8) var(--sp-6)">
        <div style="font-size:48px;margin-bottom:12px">📊</div>
        <h5 style="font-weight:700;color:var(--text-primary);margin-bottom:8px">Indicadores de Gestión</h5>
        <p style="font-size:13px;color:var(--text-secondary);margin-bottom:16px">KPIs globales: empleados por departamento, inventario por categoría, tendencias</p>
        <a href="<?php echo URL_ROOT; ?>/reportes/indicadores" class="btn-sig btn-sig--success" style="height:42px;font-size:15px">Ver Indicadores</a>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>