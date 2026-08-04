<?php require_once '../app/views/inc/header.php'; ?>
<?php
/**
 * Movimientos de bienes — Fase 2 (mig. 063).
 *
 * El formulario cambia según el tipo: un traslado pide destino, una
 * asignación pide responsable, y el mantenimiento pide los datos del
 * proceso (quién repara y la falla; al retornar: trabajo, costo y
 * resultado).
 *
 * La autorización NO se elige: la resuelve el sistema por cargo +
 * departamento (B-32/B-64) y aquí solo se muestra quién firma.
 */
$autorizador = $data['autorizador'] ?? null;
$enCurso     = $data['enCurso'] ?? [];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Inventario · Trazabilidad</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Movimientos de Inventario'; ?></h1>
        <p class="page__subtitle">Traslados, asignación de responsable y mantenimiento de los bienes.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalMov" onclick="nuevoMov()" <?php echo $autorizador ? '' : 'disabled'; ?>>
            <i class="bi bi-arrow-left-right"></i> Registrar Movimiento
        </button>
    </div>
</div>

<?php if (!$autorizador): ?>
    <div class="sig-alert sig-alert--danger anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-exclamation-triangle"></i>
        <strong>No se pueden registrar movimientos.</strong>
        Todo movimiento de bienes debe autorizarlo la Coordinación de Bienes, y no hay ningún
        empleado activo con el cargo y el departamento configurados para ello.
        Asigna ese cargo en <a href="<?php echo URL_ROOT; ?>/empleados/index">Personal</a>
        o revisa <a href="<?php echo URL_ROOT; ?>/config/index">Configuración</a>.
    </div>
<?php else: ?>
    <div class="sig-alert sig-alert--info anim-slide-up" style="margin-bottom:var(--sp-4);">
        <i class="bi bi-person-check"></i>
        Los movimientos se registran con la autorización de
        <strong><?php echo htmlspecialchars($autorizador->nombre); ?></strong>
        (<?php echo htmlspecialchars($autorizador->cargo ?? ''); ?> ·
        <?php echo htmlspecialchars($autorizador->departamento ?? ''); ?>).
    </div>
<?php endif; ?>

<?php if (!empty($enCurso)): ?>
    <div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);border-left:3px solid var(--warning-500);">
        <div class="sig-card__body" style="padding:var(--sp-4);">
            <div style="font-weight:700;margin-bottom:var(--sp-2);">
                <i class="bi bi-tools" style="color:var(--warning-600)"></i>
                Mantenimientos en curso (<?php echo count($enCurso); ?>)
            </div>
            <ul style="margin:0;padding-left:1.2rem;font-size:13px;">
                <?php foreach ($enCurso as $m): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($m->bien); ?></strong>
                        <?php if ($m->codigo_bn): ?> (<?php echo htmlspecialchars($m->codigo_bn); ?>)<?php endif; ?>
                        — desde <?php echo htmlspecialchars($m->fecha_salida); ?>
                        <?php if (!empty($m->encargado)): ?> · a cargo de <?php echo htmlspecialchars($m->encargado); ?>
                        <?php elseif (!empty($m->proveedor_externo)): ?> · taller: <?php echo htmlspecialchars($m->proveedor_externo); ?><?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
<?php endif; ?>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="12" data-buscar-placeholder="Buscar por bien, código, tipo o responsable…">
    <table class="sig-table">
        <thead><tr>
            <th>Fecha</th><th>Bien</th><th>Movimiento</th><th>Recorrido</th>
            <th>Responsable</th><th>Autorizó</th><th class="col-actions">Acciones</th>
        </tr></thead>
        <tbody>
            <?php if (empty($data['actividades'])): ?>
                <tr><td colspan="7" class="sig-table-empty">No hay movimientos registrados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['actividades'] as $a): ?>
                    <?php $tcls = ActividadInventario::TIPO_BADGES[$a->tipo_movimiento ?? ''] ?? 'sig-badge--neutral'; ?>
                    <tr>
                        <td><?php echo htmlspecialchars($a->fecha ?? '—'); ?></td>
                        <td>
                            <span class="cell-strong"><?php echo htmlspecialchars($a->item_nombre ?? '—'); ?></span>
                            <?php if (!empty($a->codigo_bn)): ?><br><small style="color:var(--text-tertiary);font-family:var(--font-mono);"><?php echo htmlspecialchars($a->codigo_bn); ?></small><?php endif; ?>
                        </td>
                        <td>
                            <span class="sig-badge <?php echo $tcls; ?>"><?php echo htmlspecialchars($a->tipo_movimiento ?? '—'); ?></span>
                            <?php if (!empty($a->descripcion)): ?><br><small style="color:var(--text-tertiary)"><?php echo htmlspecialchars($a->descripcion); ?></small><?php endif; ?>
                        </td>
                        <td style="font-size:12.5px;">
                            <?php if (!empty($a->ubicacion_origen) || !empty($a->ubicacion_destino)): ?>
                                <?php echo htmlspecialchars($a->ubicacion_origen ?: '—'); ?>
                                <i class="bi bi-arrow-right" style="color:var(--text-tertiary)"></i>
                                <?php echo htmlspecialchars($a->ubicacion_destino ?: '—'); ?>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary)">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo trim(($a->emp_nombre ?? '') . ' ' . ($a->emp_apellido ?? '')) ?: '<span style="color:var(--text-tertiary)">—</span>'; ?></td>
                        <td style="font-size:12.5px;"><?php echo htmlspecialchars($a->autorizador ?: '—'); ?></td>
                        <td class="col-actions">
                            <a href="<?php echo URL_ROOT; ?>/actividadesinventario/delete/<?php echo $a->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i> Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ══════════ Modal: registrar movimiento ══════════ -->
<div class="modal fade" id="modalMov" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form action="<?php echo URL_ROOT; ?>/actividadesinventario/store" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Movimiento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-md-7"><div class="sig-field"><label class="sig-field__label" for="mov_bien">Bien <span class="req">*</span></label>
                        <select name="id_inventario" id="mov_bien" class="sig-select js-search" required>
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['inventario'] ?? [] as $i): ?>
                                <option value="<?php echo $i->id; ?>">
                                    <?php echo htmlspecialchars(($i->codigo_bn ? $i->codigo_bn . ' · ' : '') . $i->nombre); ?>
                                </option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-md-5"><div class="sig-field"><label class="sig-field__label" for="mov_fecha">Fecha <span class="req">*</span></label>
                        <input type="date" name="fecha" id="mov_fecha" class="sig-input" required max="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>"></div></div>

                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="mov_tipo">Tipo de movimiento <span class="req">*</span></label>
                        <select name="tipo_movimiento" id="mov_tipo" class="sig-select" required onchange="movToggle()">
                            <option value="">Seleccione...</option>
                            <?php foreach (ActividadInventario::TIPOS_MANUALES as $t): ?>
                                <option value="<?php echo htmlspecialchars($t); ?>"><?php echo htmlspecialchars($t); ?></option>
                            <?php endforeach; ?>
                        </select></div></div>

                    <!-- Traslado -->
                    <div class="col-md-6" id="mov_wrap_destino" style="display:none;"><div class="sig-field">
                        <label class="sig-field__label" for="mov_destino">Ubicación de destino <span class="req">*</span></label>
                        <select name="id_ubicacion_destino" id="mov_destino" class="sig-select js-search">
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['ubicaciones'] ?? [] as $u): ?>
                                <option value="<?php echo $u->id; ?>"><?php echo htmlspecialchars($u->nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small style="color:var(--text-tertiary)">El origen se toma de donde está el bien ahora.</small></div></div>

                    <!-- Asignación de responsable -->
                    <div class="col-md-6" id="mov_wrap_resp" style="display:none;"><div class="sig-field">
                        <label class="sig-field__label" for="mov_resp">Nuevo responsable <span class="req">*</span></label>
                        <select name="id_empleado_responsable" id="mov_resp" class="sig-select js-search">
                            <option value="">Seleccione...</option>
                            <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?></option>
                            <?php endforeach; ?>
                        </select></div></div>

                    <!-- Salida a mantenimiento -->
                    <div class="col-12" id="mov_wrap_salida" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label" for="mov_encargado">Encargado (Servicios Generales)</label>
                                <select name="id_empleado_encargado" id="mov_encargado" class="sig-select js-search">
                                    <option value="">Seleccione...</option>
                                    <?php foreach ($data['empleados'] ?? [] as $e): ?>
                                        <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?></option>
                                    <?php endforeach; ?>
                                </select></div></div>
                            <div class="col-md-6"><div class="sig-field"><label class="sig-field__label" for="mov_taller">…o taller externo</label>
                                <input type="text" name="proveedor_externo" id="mov_taller" class="sig-input" placeholder="Nombre del taller"></div></div>
                            <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="mov_falla">Falla reportada</label>
                                <textarea name="descripcion_falla" id="mov_falla" class="sig-textarea" rows="2"></textarea></div></div>
                        </div>
                    </div>

                    <!-- Retorno de mantenimiento -->
                    <div class="col-12" id="mov_wrap_retorno" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="mov_resultado">Resultado <span class="req">*</span></label>
                                <select name="resultado" id="mov_resultado" class="sig-select">
                                    <?php foreach (Mantenimiento::RESULTADOS as $r): ?><option value="<?php echo $r; ?>"><?php echo $r; ?></option><?php endforeach; ?>
                                </select></div></div>
                            <div class="col-md-4"><div class="sig-field"><label class="sig-field__label" for="mov_costo">Costo (Bs.)</label>
                                <input type="number" step="0.01" min="0" name="costo" id="mov_costo" class="sig-input"></div></div>
                            <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="mov_trabajo">Trabajo realizado</label>
                                <textarea name="trabajo_realizado" id="mov_trabajo" class="sig-textarea" rows="2"></textarea></div></div>
                            <div class="col-12"><div class="sig-alert sig-alert--warning" style="margin:0;">
                                Si el resultado es <strong>Irrecuperable</strong>, el bien vuelve a Activo con condición
                                <em>Dañado</em>, a la espera del acto administrativo de baja.
                            </div></div>
                        </div>
                    </div>

                    <div class="col-12"><div class="sig-field"><label class="sig-field__label" for="mov_desc">Observaciones</label>
                        <textarea name="descripcion" id="mov_desc" class="sig-textarea" rows="2"></textarea></div></div>

                    <?php if ($autorizador): ?>
                    <div class="col-12"><div class="sig-alert sig-alert--info" style="margin:0;">
                        <i class="bi bi-person-check"></i> Autoriza:
                        <strong><?php echo htmlspecialchars($autorizador->nombre); ?></strong>
                        (<?php echo htmlspecialchars($autorizador->cargo ?? ''); ?>)
                    </div></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
var MOV = {
    traslado: <?php echo json_encode(ActividadInventario::MOV_TRASLADO); ?>,
    resp:     <?php echo json_encode(ActividadInventario::MOV_RESPONSABLE); ?>,
    salida:   <?php echo json_encode(ActividadInventario::MOV_SALIDA_MANT); ?>,
    retorno:  <?php echo json_encode(ActividadInventario::MOV_RETORNO_MANT); ?>
};

function movToggle() {
    var t = document.getElementById('mov_tipo').value;
    var mostrar = function (id, on, requeridos) {
        var box = document.getElementById(id);
        if (box) box.style.display = on ? '' : 'none';
        (requeridos || []).forEach(function (rid) {
            var el = document.getElementById(rid);
            if (el) el.required = on;
        });
    };
    mostrar('mov_wrap_destino', t === MOV.traslado, ['mov_destino']);
    mostrar('mov_wrap_resp',    t === MOV.resp,     ['mov_resp']);
    mostrar('mov_wrap_salida',  t === MOV.salida,   []);
    mostrar('mov_wrap_retorno', t === MOV.retorno,  ['mov_resultado']);
}

function nuevoMov() {
    var f = document.querySelector('#modalMov form');
    if (f) f.reset();
    var fe = document.getElementById('mov_fecha');
    if (fe) fe.value = '<?php echo date('Y-m-d'); ?>';
    movToggle();
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
