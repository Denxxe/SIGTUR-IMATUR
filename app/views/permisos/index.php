<?php require_once '../app/views/inc/header.php';
$ff = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '—';
$estadoBadge = ['Pendiente'=>'sig-badge--warning','Aprobado'=>'sig-badge--success','Rechazado'=>'sig-badge--danger','Anulado'=>'sig-badge--neutral'];
$flt = $data['filtros'] ?? ['estado'=>'','categoria'=>''];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Permisos y Reposos</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Permisos y Reposos'; ?></h1>
        <p class="page__subtitle">Reposos médicos y permisos laborales. Talento Humano oficializa; los especiales los aprueba la Dirección General.</p>
    </div>
    <div class="page__actions">
        <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalPermiso"><i class="bi bi-plus-lg"></i> Registrar</button>
    </div>
</div>

<!-- Filtros -->
<form method="GET" action="<?php echo URL_ROOT; ?>/permisos/index" class="anim-slide-up" style="display:flex;gap:var(--sp-3);align-items:flex-end;margin-bottom:var(--sp-4);flex-wrap:wrap">
    <div class="sig-field" style="margin:0"><label class="sig-field__label">Categoría</label>
        <select name="categoria" class="sig-select">
            <option value="">Todas</option>
            <?php foreach (PermisoLaboral::CATEGORIAS as $c): ?>
                <option value="<?php echo $c; ?>" <?php echo $flt['categoria']===$c?'selected':''; ?>><?php echo $c; ?></option>
            <?php endforeach; ?>
        </select></div>
    <div class="sig-field" style="margin:0"><label class="sig-field__label">Estado</label>
        <select name="estado" class="sig-select">
            <option value="">Todos</option>
            <?php foreach (PermisoLaboral::ESTADOS as $es): ?>
                <option value="<?php echo $es; ?>" <?php echo $flt['estado']===$es?'selected':''; ?>><?php echo $es; ?></option>
            <?php endforeach; ?>
        </select></div>
    <button type="submit" class="btn-sig btn-sig--ghost"><i class="bi bi-funnel"></i> Filtrar</button>
</form>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="10">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Empleado</th><th>Categoría</th><th>Tipo</th><th>Desde</th><th>Hasta</th>
                <th>Duración</th><th>Período</th><th>Estado</th><th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['permisos'])): ?>
                <tr><td colspan="9" class="sig-table-empty">No hay registros.</td></tr>
            <?php else: foreach ($data['permisos'] as $pl): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($pl->nombre . ' ' . $pl->apellido); ?></td>
                    <td><span class="sig-badge <?php echo $pl->categoria==='Reposo'?'sig-badge--danger':'sig-badge--info'; ?>"><?php echo htmlspecialchars($pl->categoria ?? '—'); ?></span></td>
                    <td style="font-size:13px"><?php echo htmlspecialchars($pl->tipo_permiso); ?></td>
                    <td><?php echo $ff($pl->fecha_inicio); ?></td>
                    <td><?php echo $ff($pl->fecha_fin); ?></td>
                    <td style="font-size:13px"><?php echo htmlspecialchars($pl->duracion ?? ($pl->dias_solicitados . ' días')); ?></td>
                    <td><span class="sig-badge <?php echo $pl->estatus_periodo==='En curso'?'sig-badge--info':'sig-badge--neutral'; ?>"><?php echo $pl->estatus_periodo; ?></span></td>
                    <td><span class="sig-badge <?php echo $estadoBadge[$pl->estado] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($pl->estado); ?></span></td>
                    <td class="col-actions">
                        <?php if ($pl->estado === 'Pendiente'): ?>
                            <a href="<?php echo URL_ROOT; ?>/permisos/aprobar/<?php echo $pl->id; ?>" class="row-action" onclick="return confirm('¿Aprobar este permiso?')"><i class="bi bi-check2"></i> Aprobar</a>
                            <a href="<?php echo URL_ROOT; ?>/permisos/rechazar/<?php echo $pl->id; ?>" class="row-action" onclick="return confirm('¿Rechazar este permiso?')"><i class="bi bi-x"></i> Rechazar</a>
                        <?php endif; ?>
                        <a href="<?php echo URL_ROOT; ?>/permisos/delete/<?php echo $pl->id; ?>" class="row-action row-action--del delete-btn"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Registrar permiso/reposo -->
<div class="modal fade" id="modalPermiso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/permisos/store" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Registrar permiso / reposo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="sig-field mb-3"><label class="sig-field__label">Empleado <span class="req">*</span></label>
                    <select name="id_empleado" class="sig-select js-search" required>
                        <option value="">Seleccione empleado...</option>
                        <?php foreach ($data['empleados'] ?? [] as $e): ?>
                            <option value="<?php echo $e->id; ?>"><?php echo htmlspecialchars(($e->nombre ?? '').' '.($e->apellido ?? '')); ?> (<?php echo htmlspecialchars($e->cedula ?? ''); ?>)</option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="row g-3 mb-3">
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Categoría <span class="req">*</span></label>
                        <select name="categoria" id="pl_categoria" class="sig-select" required onchange="plCascada()">
                            <option value="">Seleccione...</option>
                            <?php foreach (PermisoLaboral::CATEGORIAS as $c): ?><option value="<?php echo $c; ?>"><?php echo $c; ?></option><?php endforeach; ?>
                        </select></div></div>
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Tipo <span class="req">*</span></label>
                        <select name="tipo_permiso" id="pl_tipo" class="sig-select" required><option value="">Seleccione categoría primero</option></select></div></div>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Desde <span class="req">*</span></label>
                        <input type="date" name="fecha_inicio" class="sig-input" required value="<?php echo date('Y-m-d'); ?>"></div></div>
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Hasta <span class="req">*</span></label>
                        <input type="date" name="fecha_fin" class="sig-input" required value="<?php echo date('Y-m-d'); ?>"></div></div>
                </div>
                <div class="sig-field mb-3"><label class="sig-field__label">Duración <small style="color:var(--text-secondary)">(texto, ej. "72 horas", "10 días", "6 meses")</small></label>
                    <input type="text" name="duracion" class="sig-input"></div>
                <div class="sig-field"><label class="sig-field__label">Motivo / observación</label>
                    <textarea name="motivo" class="sig-textarea" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar</button>
            </div>
        </form>
    </div>
</div>

<script>
const PL_TIPOS = <?php echo json_encode(PermisoLaboral::TIPOS, JSON_UNESCAPED_UNICODE); ?>;
function plCascada() {
    const cat = document.getElementById('pl_categoria').value;
    const tipo = document.getElementById('pl_tipo');
    tipo.innerHTML = '';
    const opts = PL_TIPOS[cat] || [];
    if (!opts.length) { tipo.innerHTML = '<option value="">Seleccione categoría primero</option>'; return; }
    opts.forEach(t => { const o = document.createElement('option'); o.value = t; o.textContent = t; tipo.appendChild(o); });
}
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
