<?php require_once '../app/views/inc/header.php'; ?>

<div class="row align-items-center mb-4 d-print-none">
    <div class="col-md-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?php echo URL_ROOT; ?>/reportes/talleres">Reportes de Formación</a></li>
                <li class="breadcrumb-item active">Dossier de Actividad</li>
            </ol>
        </nav>
        <h1 class="fw-bold"><i class="bi bi-file-earmark-person-fill text-info"></i> Dossier Integral de Actividad</h1>
    </div>
    <div class="col-md-5 text-end">
        <a href="<?php echo URL_ROOT; ?>/reportes/exportarDossierCsv/<?php echo $data['taller']->id; ?>" class="btn btn-success shadow-sm">
            <i class="bi bi-file-earmark-spreadsheet"></i> Dossier Excel
        </a>
        <button onclick="window.print()" class="btn btn-danger shadow-sm">
            <i class="bi bi-printer"></i> Imprimir Informe
        </button>
        <a href="<?php echo URL_ROOT; ?>/reportes/talleres" class="btn btn-outline-secondary shadow-sm">Volver</a>
    </div>
</div>

<div class="print-area">
    <!-- SECCIÓN 1: FICHA TÉCNICA -->
    <div class="card shadow-sm border-0 mb-4 overflow-hidden">
        <div class="card-header bg-dark text-white p-3">
            <h5 class="mb-0"><i class="bi bi-info-circle-fill me-2"></i> I. FICHA TÉCNICA DEL TALLER</h5>
        </div>
        <div class="card-body bg-light bg-opacity-50">
            <div class="row g-4">
                <div class="col-md-6 border-end">
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold">Nombre del Taller / Actividad</small>
                        <h3 class="text-primary fw-bold mb-0"><?php echo $data['taller']->nombre; ?></h3>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <small class="text-muted d-block">Facilitador a cargo</small>
                            <span class="fw-bold"><?php echo $data['taller']->fac_nom . ' ' . $data['taller']->fac_ape; ?></span>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Sede / Ubicación</small>
                            <span class="fw-bold"><?php echo $data['taller']->sede ?: 'No especificada'; ?></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-4">
                            <div class="p-2 border rounded text-center bg-white">
                                <small class="text-muted d-block small">Fecha</small>
                                <span class="fw-bold"><?php echo $data['taller']->fecha_inicio; ?></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded text-center bg-white">
                                <small class="text-muted d-block small">Hora</small>
                                <span class="fw-bold"><?php echo $data['taller']->hora_inicio ?: 'N/A'; ?></span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 border rounded text-center bg-white">
                                <small class="text-muted d-block small">Estado</small>
                                <span class="badge bg-dark"><?php echo $data['taller']->estado; ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 2: MÉTRICAS Y DEMOGRAFÍA -->
    <div class="row mb-4">
        <div class="col-md-5">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-pie-chart-fill text-warning me-2"></i> II. DESGLOSE DEMOGRÁFICO
                </div>
                <div class="card-body">
                    <?php if($data['informe']): ?>
                        <div id="chartDemografia"></div>
                        <div class="row text-center mt-3 g-2">
                            <div class="col-3">
                                <small class="text-muted d-block">Mujeres</small>
                                <span class="fw-bold text-primary"><?php echo $data['informe']->mujeres; ?></span>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Hombres</small>
                                <span class="fw-bold text-secondary"><?php echo $data['informe']->hombres; ?></span>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Niños/as</small>
                                <span class="fw-bold text-success"><?php echo (int)$data['informe']->ninas + (int)$data['informe']->ninos; ?></span>
                            </div>
                            <div class="col-3 border-start">
                                <small class="text-muted d-block">TOTAL</small>
                                <span class="fw-bold text-dark fs-5"><?php echo $data['informe']->total_atendidas; ?></span>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-exclamation-triangle fs-1"></i><br>
                            No hay informe demográfico registrado.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white fw-bold">
                    <i class="bi bi-file-text-fill text-primary me-2"></i> III. RESUMEN DE LA ACTIVIDAD
                </div>
                <div class="card-body">
                    <p class="text-justify mb-0" style="line-height: 1.8;">
                        <?php echo $data['informe'] ? nl2br(htmlspecialchars($data['informe']->resumen_actividad)) : '<em>Sin resumen disponible.</em>'; ?>
                    </p>
                    <hr>
                    <div class="row align-items-center">
                        <div class="col-8">
                            <small class="text-muted fw-bold">Instituciones Presentes:</small><br>
                            <span><?php echo $data['informe']->instituciones_presentes ?? 'Ninguna registrada'; ?></span>
                        </div>
                        <div class="col-4 text-end">
                            <div class="bg-primary bg-opacity-10 p-2 rounded">
                                <small class="text-muted d-block small">Asistencia de Inscritos</small>
                                <?php 
                                    $asistieron = count(array_filter($data['participantes'], function($p){ return $p->asistio; }));
                                    $total_ins = count($data['participantes']);
                                    $porcentaje = $total_ins > 0 ? round(($asistieron / $total_ins) * 100) : 0;
                                ?>
                                <span class="fw-bold fs-4"><?php echo $porcentaje; ?>%</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 3: LISTADO NOMINAL -->
    <div class="card shadow-sm border-0">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="bi bi-people-fill me-2"></i> IV. LISTADO DE PARTICIPANTES INSCRITOS
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Cédula</th>
                            <th>Nombre Completo</th>
                            <th class="text-center">Asistencia</th>
                            <th>Firma (Digital)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(empty($data['participantes'])): ?>
                            <tr><td colspan="4" class="text-center py-4 text-muted">No hay participantes registrados en este taller.</td></tr>
                        <?php else: ?>
                            <?php foreach($data['participantes'] as $p): ?>
                            <tr>
                                <td class="ps-4"><?php echo $p->cedula; ?></td>
                                <td class="fw-bold"><?php echo $p->nombre . ' ' . $p->apellido; ?></td>
                                <td class="text-center">
                                    <?php if($p->asistio): ?>
                                        <span class="text-success"><i class="bi bi-check-circle-fill"></i> Presente</span>
                                    <?php else: ?>
                                        <span class="text-danger"><i class="bi bi-x-circle-fill"></i> Ausente</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted small italic">Verificaci&oacute;n por sistema</td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-0 text-center py-5 d-none d-print-block">
            <div class="row">
                <div class="col-6">
                    <p class="mb-5">_______________________</p>
                    <p class="fw-bold mb-0"><?php echo $data['taller']->fac_nom . ' ' . $data['taller']->fac_ape; ?></p>
                    <small>Facilitador / Instructor</small>
                </div>
                <div class="col-6">
                    <p class="mb-5">_______________________</p>
                    <p class="fw-bold mb-0">IMATUR - COORDINACI&Oacute;N</p>
                    <small>Firma Oficial y Sello</small>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    body { background: white !important; }
    .d-print-none { display: none !important; }
    .print-area { width: 100%; }
    .card { border: 1px solid #ddd !important; box-shadow: none !important; }
    .breadcrumb { display: none; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php if($data['informe']): ?>
    new ApexCharts(document.querySelector("#chartDemografia"), {
        chart: { type: 'donut', height: 250 },
        series: [
            <?php echo (int)$data['informe']->mujeres; ?>, 
            <?php echo (int)$data['informe']->hombres; ?>, 
            <?php echo (int)$data['informe']->ninas + (int)$data['informe']->ninos; ?>
        ],
        labels: ['Mujeres', 'Hombres', 'Niños/as'],
        colors: ['#3b82f6','#64748b','#10b981'],
        legend: { position: 'bottom' },
        plotOptions: { pie: { donut: { size: '60%', labels: { show: true, total: { show: true, label: 'Personas' } } } } }
    }).render();
    <?php endif; ?>
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
