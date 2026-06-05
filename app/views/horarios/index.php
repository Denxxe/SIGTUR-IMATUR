<?php require_once '../app/views/inc/header.php';
$hm = fn($t) => !empty($t) ? substr($t, 0, 5) : '—';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Organización</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Horarios y Turnos'; ?></h1>
        <p class="page__subtitle">Catálogo de horarios asignables a empleados (modalidades y ajustes personalizados).</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalHorario" onclick="nuevoHorario()">
            <i class="bi bi-plus-lg"></i> Agregar Horario
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Horario</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Días</th>
                <th>Descripción</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['horarios'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No hay horarios registrados.</td></tr>
            <?php else: foreach ($data['horarios'] as $h): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($h->nombre); ?></td>
                    <td><?php echo $hm($h->hora_entrada); ?></td>
                    <td><?php echo $hm($h->hora_salida); ?></td>
                    <td><span class="sig-badge sig-badge--info"><?php echo htmlspecialchars($h->dias_laborales ?? 'L-V'); ?></span></td>
                    <td style="color:var(--text-secondary);font-size:13px"><?php echo htmlspecialchars($h->descripcion ?? ''); ?></td>
                    <td class="col-actions">
                        <button class="row-action row-action--edit" onclick='editarHorario(<?php echo htmlspecialchars(json_encode($h), ENT_QUOTES, "UTF-8"); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                        <a href="<?php echo URL_ROOT; ?>/horarios/delete/<?php echo $h->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalHorario" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/horarios/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalHorarioLabel">Nuevo Horario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="hor_id">
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" id="hor_nombre" class="sig-input" required placeholder="Ej: Estándar (8:00am–2:00pm)">
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Entrada <span class="req">*</span></label>
                            <input type="time" name="hora_entrada" id="hor_hora_entrada" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Salida <span class="req">*</span></label>
                            <input type="time" name="hora_salida" id="hor_hora_salida" class="sig-input" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="sig-field">
                            <label class="sig-field__label">Días laborales</label>
                            <input type="text" name="dias_laborales" id="hor_dias" class="sig-input" placeholder="L-V" value="L-V">
                        </div>
                    </div>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Descripción</label>
                    <textarea name="descripcion" id="hor_descripcion" class="sig-textarea" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function nuevoHorario() {
        document.getElementById('modalHorarioLabel').innerText = 'Nuevo Horario';
        document.getElementById('hor_id').value = '';
        document.getElementById('hor_nombre').value = '';
        document.getElementById('hor_hora_entrada').value = '';
        document.getElementById('hor_hora_salida').value = '';
        document.getElementById('hor_dias').value = 'L-V';
        document.getElementById('hor_descripcion').value = '';
    }
    function editarHorario(h) {
        document.getElementById('modalHorarioLabel').innerText = 'Editar: ' + h.nombre;
        document.getElementById('hor_id').value = h.id;
        document.getElementById('hor_nombre').value = h.nombre;
        document.getElementById('hor_hora_entrada').value = (h.hora_entrada || '').substring(0,5);
        document.getElementById('hor_hora_salida').value = (h.hora_salida || '').substring(0,5);
        document.getElementById('hor_dias').value = h.dias_laborales || 'L-V';
        document.getElementById('hor_descripcion').value = h.descripcion || '';
        new bootstrap.Modal(document.getElementById('modalHorario')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
