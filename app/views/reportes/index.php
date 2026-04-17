<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h1><i class="bi bi-bar-chart-line"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Selecciona un tipo de reporte para generar</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <div class="display-4 text-primary mb-3">📋</div>
                <h5 class="fw-bold">Reporte de Asistencia</h5>
                <p class="text-muted small">Consulta el historial de asistencia del personal con filtros por fecha</p>
                <a href="<?php echo URL_ROOT; ?>/reportes/asistencia" class="btn btn-primary">Generar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <div class="display-4 text-warning mb-3">🎓</div>
                <h5 class="fw-bold">Reporte de Talleres</h5>
                <p class="text-muted small">Visualiza estadísticas de talleres, participantes e instructores</p>
                <a href="<?php echo URL_ROOT; ?>/reportes/talleres" class="btn btn-warning">Generar</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center py-5">
                <div class="display-4 text-info mb-3">🗺️</div>
                <h5 class="fw-bold">Reporte de Rutas</h5>
                <p class="text-muted small">Estado de las rutas turísticas, puntos y equipamiento asignado</p>
                <a href="<?php echo URL_ROOT; ?>/reportes/rutas" class="btn btn-info">Generar</a>
            </div>
        </div>
    </div>
    <div class="col-md-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <div class="display-4 text-success mb-3">📊</div>
                <h5 class="fw-bold">Indicadores de Gestión</h5>
                <p class="text-muted small">KPIs globales: empleados por departamento, inventario por categoría, tendencias de formación</p>
                <a href="<?php echo URL_ROOT; ?>/reportes/indicadores" class="btn btn-success btn-lg">Ver Indicadores</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
