<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Logística</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Despachos, devoluciones, bajas y mantenimiento preventivo.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalMovimiento" onclick="nuevoMovimiento()">
            <i class="bi bi-plus-lg"></i> Registrar Movimiento
        </button>
    </div>
</div>

<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead><tr><th>Fecha</th><th>Bien Cod#</th><th>Operación</th><th>Responsable</th><th>Descripción</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php if(empty($data['actividades'])): ?>
                <tr><td colspan="6" class="sig-table-empty">No hay movimientos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['actividades'] as $act): ?>
                    <tr>
                        <td class="cell-strong"><?php echo $act->fecha; ?></td>
                        <td><strong><?php echo $act->item_nombre; ?></strong><br><span class="sig-badge sig-badge--neutral"><?php echo $act->codigo_bn ?: 'Sin Cod'; ?></span></td>
                        <td>
                            <?php
                                $cls = 'sig-badge--neutral';
                                if ($act->tipo_movimiento == 'Asignacion') $cls = 'sig-badge--brand';
                                elseif ($act->tipo_movimiento == 'Devolucion') $cls = 'sig-badge--success';
                                elseif ($act->tipo_movimiento == 'Mantenimiento') $cls = 'sig-badge--warning';
                                elseif ($act->tipo_movimiento == 'Baja') $cls = 'sig-badge--danger';
                            ?>
                            <span class="sig-badge <?php echo $cls; ?>"><?php echo $act->tipo_movimiento; ?></span>
                        </td>
                        <td style="font-size:12.5px"><?php echo $act->emp_nombre ? ($act->emp_nombre . ' ' . $act->emp_apellido) : '<span style="color:var(--text-tertiary)">N/A</span>'; ?></td>
                        <td style="font-size:12.5px;color:var(--text-secondary)"><?php echo substr($act->descripcion, 0, 50) . '...'; ?></td>
                        <td class="col-actions">
                            <button class="row-action row-action--edit" onclick='editarMovimiento(<?php echo json_encode($act); ?>)'><i class="bi bi-pencil"></i> Editar</button>
                            <a href="<?php echo URL_ROOT; ?>/actividadesinventario/delete/<?php echo $act->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Borrar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/actividadesinventario/store" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title" id="modalMovimientoLabel">Registrar Movimiento</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="mov_id">
                <div class="sig-field mb-3"><label class="sig-field__label">Bien Nacional <span class="req">*</span></label>
                    <select name="id_inventario" id="mov_inventario" class="sig-select" required><option value="">Seleccione...</option><?php foreach($data['inventario'] as $inv): ?><option value="<?php echo $inv->id; ?>"><?php echo $inv->codigo_bn . ' - ' . $inv->nombre; ?></option><?php endforeach; ?></select>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Fecha <span class="req">*</span></label><input type="date" name="fecha" id="mov_fecha" value="<?php echo date('Y-m-d'); ?>" class="sig-input" required></div></div>
                    <div class="col-md-6"><div class="sig-field"><label class="sig-field__label">Operación <span class="req">*</span></label><select name="tipo_movimiento" id="mov_tipo" class="sig-select" required><option value="Asignacion">Asignación</option><option value="Devolucion">Devolución</option><option value="Traslado">Traslado</option><option value="Mantenimiento">Mantenimiento</option><option value="Baja">Dar de Baja</option></select></div></div>
                </div>
                <div class="sig-field mb-3"><label class="sig-field__label">Responsable (Opcional)</label>
                    <select name="id_empleado_responsable" id="mov_empleado" class="sig-select"><option value="">N/A</option><?php foreach($data['empleados'] as $emp): ?><option value="<?php echo $emp->id; ?>"><?php echo $emp->nombre . ' ' . $emp->apellido; ?></option><?php endforeach; ?></select>
                </div>
                <div class="sig-field mb-3"><label class="sig-field__label">Descripción <span class="req">*</span></label><textarea name="descripcion" id="mov_descripcion" class="sig-textarea" rows="3" required placeholder="Motivo del movimiento..."></textarea></div>
            </div>
            <div class="modal-footer"><button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button><button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button></div>
        </form>
    </div>
</div>

<script>
    function nuevoMovimiento() { document.getElementById('modalMovimientoLabel').innerText='Registrar Movimiento'; document.getElementById('mov_id').value=''; document.querySelector('#modalMovimiento form').reset(); }
    function editarMovimiento(act) {
        document.getElementById('modalMovimientoLabel').innerText='Modificar Registro'; document.getElementById('mov_id').value=act.id;
        document.getElementById('mov_inventario').value=act.id_inventario; document.getElementById('mov_fecha').value=act.fecha;
        document.getElementById('mov_tipo').value=act.tipo_movimiento; document.getElementById('mov_empleado').value=act.id_empleado_responsable||'';
        document.getElementById('mov_descripcion').value=act.descripcion; new bootstrap.Modal(document.getElementById('modalMovimiento')).show();
    }
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
