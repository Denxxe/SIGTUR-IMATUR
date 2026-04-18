<?php require_once '../app/views/inc/header.php'; ?>

<div class="row align-items-center mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-person-plus"></i> Ingresar Nuevo Pasante</h1>
        <p class="text-muted">Apertura de expediente y asignación institucional.</p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo URL_ROOT; ?>/pasantes/index" class="btn btn-outline-secondary">← Cancelar</a>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <form action="<?php echo URL_ROOT; ?>/pasantes/crear" method="POST">
            <h5 class="text-primary border-bottom pb-2 mb-4"><i class="bi bi-person-bounding-box"></i> Datos Personales</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Cédula de Identidad <span class="text-danger">*</span></label>
                    <input type="text" name="cedula" class="form-control" placeholder="V-12345678" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Nombre <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Apellido <span class="text-danger">*</span></label>
                    <input type="text" name="apellido" class="form-control" required>
                </div>
            </div>

            <h5 class="text-primary border-bottom pb-2 mb-4 mt-5"><i class="bi bi-building"></i> Datos Académicos e Institucionales</h5>
            <div class="row g-3 mb-4">
                <div class="col-md-6">
                    <label class="form-label fw-bold">Institución Educativa (Origen) <span class="text-danger">*</span></label>
                    <input type="text" name="institucion" class="form-control" placeholder="Ej: UDO, UPTAEB..." required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Carrera / Especialidad <span class="text-danger">*</span></label>
                    <input type="text" name="carrera" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold">Tutor Asignado (IMATUR)</label>
                    <select name="id_tutor_institucional" class="form-select">
                        <option value="">-- Seleccionar Empleado (Opcional) --</option>
                        <!-- En un sistema completo esto se traería del controlador -->
                        <option value="1">Admin IMATUR (Director)</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha Resuelta Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold">Fecha Est. Culminación</label>
                    <input type="date" name="fecha_fin" class="form-control">
                </div>
            </div>

            <div class="text-end pt-3 border-top mt-4">
                <button type="submit" class="btn btn-primary px-5"><i class="bi bi-save"></i> Registrar y Abrir Expediente</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
