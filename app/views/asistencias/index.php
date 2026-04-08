<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-7">
        <h1><i class="bi bi-clock-history"></i> <?php echo $data['titulo']; ?></h1>
        <p class="text-muted">Registro de entrada y salida del personal IMATUR.</p>
    </div>
    <div class="col-md-5">
        <div class="card bg-light border-primary shadow-sm">
            <div class="card-body">
                <form action="<?php echo URL_ROOT; ?>/asistencias/marcar" method="POST" class="row g-2">
                    <div class="col-8">
                        <select name="id_empleado" class="form-select form-select-lg" required>
                            <option value="">Seleccione su nombre...</option>
                            <?php foreach ($data['empleados'] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo $e->nombre . ' ' . $e->apellido; ?> (<?php echo $e->cedula; ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <button type="submit" class="btn btn-primary btn-lg w-100">MARCAR</button>
                    </div>
                </form>
                <div class="text-center mt-2">
                    <span class="badge bg-dark" id="clock">--:--:--</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0 text-dark">Registros Recientes</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light text-uppercase small">
                    <tr>
                        <th class="ps-4">Fecha</th>
                        <th>Empleado</th>
                        <th>Expediente</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Observación</th>
                        <th class="text-center">Eliminar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['asistencias'])): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Aún no hay marcajes registrados hoy.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data['asistencias'] as $as): ?>
                            <tr>
                                <td class="ps-4 fw-bold"><?php echo date('d/m/Y', strtotime($as->fecha)); ?></td>
                                <td><?php echo $as->nombre . ' ' . $as->apellido; ?></td>
                                <td><?php echo $as->nro_expediente; ?></td>
                                <td><span class="text-success fw-bold"><?php echo $as->hora_entrada; ?></span></td>
                                <td>
                                    <span class="text-danger fw-bold">
                                        <?php echo $as->hora_salida ? $as->hora_salida : 'PENDIENTE'; ?>
                                    </span>
                                </td>
                                <td><small class="text-muted"><?php echo $as->observacion; ?></small></td>
                                <td class="text-center">
                                    <a href="<?php echo URL_ROOT; ?>/asistencias/delete/<?php echo $as->id; ?>" class="text-danger delete-btn">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // Reloj dinámico
    function updateClock() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('es-ES', { hour12: false });
        document.getElementById('clock').innerText = timeStr;
    }
    setInterval(updateClock, 1000);
    updateClock();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
