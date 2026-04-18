<?php require_once '../app/views/inc/header.php'; ?>

<div class="row mb-4">
    <div class="col-md-8">
        <h1><i class="bi bi-mortarboard"></i> <?php echo $data['titulo']; ?></h1>
        <p class="mb-0">
            <strong>Facilitador:</strong> <?php echo $data['taller']->facilitador_nombre . ' ' . $data['taller']->facilitador_apellido; ?> |
            <strong>Sede:</strong> <?php echo $data['taller']->ubicacion ?? 'Sin asignar'; ?> |
            <strong>Fecha:</strong> <?php echo $data['taller']->fecha_inicio; ?>
        </p>
    </div>
    <div class="col-md-4 text-end">
        <a href="<?php echo URL_ROOT; ?>/talleres/index" class="btn btn-outline-secondary mb-2">← Volver</a>
        <a href="<?php echo URL_ROOT; ?>/talleres/informe/<?php echo $data['taller']->id; ?>" class="btn btn-info text-white mb-2"><i class="bi bi-file-earmark-text"></i> Informe Oficial</a>
        <button type="button" class="btn btn-primary mb-2" data-bs-toggle="modal" data-bs-target="#modalInscripcion">
            <i class="bi bi-person-plus"></i> Inscribir
        </button>
    </div>
</div>

<!-- Lista de participantes -->
<div class="card shadow-sm">
    <div class="card-header bg-dark text-white fw-bold d-flex justify-content-between align-items-center">
        <span>Participantes Inscritos</span>
        <div class="text-end">
            <?php 
                $inscritos = count($data['participantes']);
                $cupo = $data['taller']->cupo_maximo;
                $porcentaje = $cupo > 0 ? round(($inscritos / $cupo) * 100) : 0;
                $colorBarra = $porcentaje > 80 ? 'bg-danger' : ($porcentaje > 50 ? 'bg-warning' : 'bg-success');
            ?>
            <span class="badge bg-light text-dark"><?php echo $inscritos; ?> / <?php echo $cupo; ?> (<?php echo $porcentaje; ?>%)</span>
            <div class="progress mt-1" style="height: 6px; width: 120px;">
                <div class="progress-bar <?php echo $colorBarra; ?>" style="width: <?php echo $porcentaje; ?>%"></div>
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead class="table-light">
                <tr>
                    <th class="ps-4">Cédula</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th class="text-center">¿Asistió?</th>
                    <th class="text-center">Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['participantes'])): ?>
                    <tr><td colspan="5" class="text-center py-4 text-muted">No hay participantes inscritos aún.</td></tr>
                <?php else: ?>
                    <?php foreach ($data['participantes'] as $p): ?>
                        <tr>
                            <td class="ps-4"><?php echo $p->cedula; ?></td>
                            <td class="fw-bold"><?php echo $p->nombre . ' ' . $p->apellido; ?></td>
                            <td><?php echo $p->telefono; ?></td>
                            <td class="text-center">
                                <?php if ($p->asistio): ?>
                                    <span class="badge bg-success">Sí</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small"><?php echo $p->observaciones ?? '—'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Inscripción -->
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/talleres/inscribir" method="POST" class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Inscribir Persona</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id; ?>">
                <div class="mb-3">
                    <label class="form-label fw-bold">Cédula del Participante</label>
                    <input type="text" name="id_persona" id="insc_persona" class="form-control" required placeholder="ID de persona registrada">
                    <small class="text-muted">Ingrese el ID de la persona ya registrada en el sistema.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn btn-primary">Inscribir</button>
            </div>
        </form>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
