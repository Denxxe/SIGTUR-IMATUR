<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-6">
        <h1><i class="bi bi-clipboard-data"></i> <?php echo $data['titulo']; ?></h1>
    </div>
    <div class="col-md-6 text-end">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarAsistenciaCsv?fecha_inicio=<?php echo $data['fecha_inicio']; ?>&fecha_fin=<?php echo $data['fecha_fin']; ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarAsistenciaPdf?fecha_inicio=<?php echo $data['fecha_inicio']; ?>&fecha_fin=<?php echo $data['fecha_fin']; ?>" class="btn btn-danger" target="_blank">
            <i class="bi bi-file-earmark-pdf"></i> PDF
        </a>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn btn-outline-secondary">← Volver</a>
    </div>
</div>

<!-- Filtros -->
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/asistencia" class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control" value="<?php echo $data['fecha_inicio']; ?>">
            </div>
            <div class="col-md-4">
                <label class="form-label fw-bold">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control" value="<?php echo $data['fecha_fin']; ?>">
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar Resultados</button>
            </div>
        </form>
    </div>
</div>

<!-- Indicadores -->
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 bg-primary bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-primary"><?php echo $data['stats']->total ?? 0; ?></div>
            <small class="text-muted">Total Registros en el Período</small>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 bg-success bg-opacity-10 text-center py-3">
            <div class="display-6 fw-bold text-success"><?php echo $data['stats']->empleados_unicos ?? 0; ?></div>
            <small class="text-muted">Empleados Únicos Registrados</small>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="card shadow-sm border-0">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-striped mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Empleado</th>
                        <th>Cédula</th>
                        <th>Departamento</th>
                        <th>Entrada</th>
                        <th>Salida</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['registros'])): ?>
                        <tr><td colspan="7" class="text-center py-4 text-muted">Sin registros en el rango seleccionado.</td></tr>
                    <?php else: ?>
                        <?php foreach ($data['registros'] as $r): ?>
                            <tr>
                                <td><?php echo $r->fecha; ?></td>
                                <td class="fw-bold"><?php echo $r->nombre . ' ' . $r->apellido; ?></td>
                                <td><?php echo $r->cedula; ?></td>
                                <td><span class="badge bg-secondary"><?php echo $r->departamento; ?></span></td>
                                <td class="text-success fw-bold"><?php echo $r->hora_entrada; ?></td>
                                <td class="text-danger"><?php echo $r->hora_salida ?: '-'; ?></td>
                                <td class="small text-muted"><?php echo $r->observacion ?: '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
