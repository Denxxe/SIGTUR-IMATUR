<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-12">
        <h1 class="fw-bold"><i class="bi bi-speedometer2"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Resumen operativo de SIGTUR-IMATUR</p>
    </div>
</div>

<!-- Indicadores -->
<div class="row g-4 mb-5">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-primary fw-bold"><?php echo $data['totalEmpleados']; ?></div>
                <p class="mb-0 text-muted">Empleados Activos</p>
            </div>
            <div class="card-footer bg-primary bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/empleados/index" class="text-primary text-decoration-none small fw-bold">Ver Personal →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-success fw-bold"><?php echo $data['totalInventario']; ?></div>
                <p class="mb-0 text-muted">Bienes en Inventario</p>
            </div>
            <div class="card-footer bg-success bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/inventario/index" class="text-success text-decoration-none small fw-bold">Ver Inventario →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-warning fw-bold"><?php echo $data['totalTalleres']; ?></div>
                <p class="mb-0 text-muted">Talleres Vigentes</p>
            </div>
            <div class="card-footer bg-warning bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/talleres/index" class="text-warning text-decoration-none small fw-bold">Ver Formación →</a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body text-center">
                <div class="display-4 text-info fw-bold"><?php echo $data['totalRutas']; ?></div>
                <p class="mb-0 text-muted">Rutas Activas</p>
            </div>
            <div class="card-footer bg-info bg-opacity-10 border-0 text-center">
                <a href="<?php echo URL_ROOT; ?>/rutas/index" class="text-info text-decoration-none small fw-bold">Ver Turismo →</a>
            </div>
        </div>
    </div>
</div>

<!-- Accesos rápidos -->
<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-clock-history"></i> Accesos Rápidos — RRHH
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo URL_ROOT; ?>/asistencias/index" class="list-group-item list-group-item-action">Control de Asistencia</a>
                <a href="<?php echo URL_ROOT; ?>/empleados/index" class="list-group-item list-group-item-action">Gestión de Personal</a>
                <a href="<?php echo URL_ROOT; ?>/cargos/index" class="list-group-item list-group-item-action">Puestos y Cargos</a>
                <a href="<?php echo URL_ROOT; ?>/departamentos/index" class="list-group-item list-group-item-action">Estructura Organizativa</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-dark text-white fw-bold">
                <i class="bi bi-compass"></i> Accesos Rápidos — Turismo
            </div>
            <div class="list-group list-group-flush">
                <a href="<?php echo URL_ROOT; ?>/talleres/index" class="list-group-item list-group-item-action">Talleres Comunitarios</a>
                <a href="<?php echo URL_ROOT; ?>/rutas/index" class="list-group-item list-group-item-action">Rutas Turísticas</a>
                <a href="<?php echo URL_ROOT; ?>/inventario/index" class="list-group-item list-group-item-action">Inventario Institucional</a>
                <a href="<?php echo URL_ROOT; ?>/usuarios/index" class="list-group-item list-group-item-action">Gestión de Usuarios</a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
