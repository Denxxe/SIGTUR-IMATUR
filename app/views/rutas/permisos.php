<?php require_once '../app/views/inc/header.php';
/**
 * Permisos de acceso a las instituciones custodias — T-H (mig. 081).
 *
 * Dos mitades, y la de arriba es la que el cliente no tiene hoy: **a qué
 * instituciones falta pedirles permiso** para las salidas de la semana. R-06
 * dice que coordinar ese acceso es uno de los tres dolores del módulo, y hoy
 * depende de que alguien lo recuerde. Sale de `puntos_ruta.ente_custodio`.
 */
$fmt  = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
$v    = fn($x) => htmlspecialchars((string)$x);
$res  = $data['resumen'];
$qs   = http_build_query(['desde' => $data['desde'], 'hasta' => $data['hasta']]);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">— Turismo · Trámites</div>
        <h1 class="page__title">Permisos de acceso</h1>
        <p class="page__subtitle">
            Oficios a las instituciones custodias — museos, castillos, fundaciones. Uno cubre
            <strong>todas las salidas de la semana</strong> hacia esa institución.
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/salidas" class="btn-sig btn-sig--ghost">
            <i class="bi bi-calendar-event"></i> Salidas
        </a>
        <button type="button" class="btn-sig btn-sig--primary"
                data-bs-toggle="modal" data-bs-target="#modalPermiso" onclick="nuevoPermiso('')">
            <i class="bi bi-envelope-paper"></i> Emitir permiso
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-4">
        <div class="sig-card" style="border-bottom:3px solid var(--warning-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <span style="display:block;font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">En espera</span>
                <span style="font-size:28px;font-weight:800;color:var(--warning-600);"><?php echo (int)$res[PermisoRuta::EST_ESPERA]; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sig-card" style="border-bottom:3px solid var(--success-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <span style="display:block;font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">Aceptados</span>
                <span style="font-size:28px;font-weight:800;color:var(--success-600);"><?php echo (int)$res[PermisoRuta::EST_ACEPTADO]; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sig-card" style="border-bottom:3px solid var(--danger-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <span style="display:block;font-size:11px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;">Rechazados</span>
                <span style="font-size:28px;font-weight:800;color:var(--danger-600);"><?php echo (int)$res[PermisoRuta::EST_RECHAZADO]; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- ── Qué falta pedir esta semana ───────────────────────────────────────── -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);border-top:4px solid #D97706;">
    <div class="sig-card__head" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:var(--sp-3);">
        <div class="sig-card__title">
            <i class="bi bi-calendar-week" style="color:#D97706;"></i> Qué hay que pedir esta semana
        </div>
        <form method="GET" action="" style="display:flex;gap:var(--sp-2);align-items:flex-end;margin:0;">
            <div class="sig-field" style="margin:0;">
                <label class="sig-field__label" for="desde">Desde</label>
                <input type="date" name="desde" id="desde" class="sig-input" value="<?php echo $v($data['desde']); ?>">
            </div>
            <div class="sig-field" style="margin:0;">
                <label class="sig-field__label" for="hasta">Hasta</label>
                <input type="date" name="hasta" id="hasta" class="sig-input" value="<?php echo $v($data['hasta']); ?>">
            </div>
            <button type="submit" class="btn-sig btn-sig--ghost btn-sig--sm"><i class="bi bi-funnel"></i> Ver</button>
        </form>
    </div>
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <?php if (empty($data['salidas'])): ?>
            <p style="margin:0;color:var(--text-tertiary);">
                No hay salidas programadas entre el <?php echo $fmt($data['desde']); ?> y el
                <?php echo $fmt($data['hasta']); ?>.
            </p>
        <?php elseif (empty($data['custodios'])): ?>
            <p style="margin:0;color:var(--text-tertiary);">
                Hay <strong><?php echo count($data['salidas']); ?></strong> salida(s) esa semana, pero
                ninguna de sus paradas tiene <strong>institución custodia</strong> registrada, así que el
                sistema no puede decir a quién pedirle permiso.
                Se indica en cada parada, desde el detalle de la salida.
            </p>
        <?php else: ?>
            <div class="sig-table-wrap" data-no-export>
                <table class="sig-table">
                    <thead>
                        <tr>
                            <th>Institución custodia</th>
                            <th>Recorridos que la incluyen</th>
                            <th class="text-center">Salidas</th>
                            <th class="text-center">Permiso</th>
                            <th class="col-actions"></th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($data['custodios'] as $cst): $ya = (int)$cst->permisos_emitidos; ?>
                        <tr>
                            <td class="cell-strong"><?php echo $v($cst->institucion); ?></td>
                            <td style="font-size:12px;color:var(--text-secondary);"><?php echo $v($cst->rutas); ?></td>
                            <td class="text-center" style="font-weight:700;"><?php echo (int)$cst->salidas; ?></td>
                            <td class="text-center">
                                <?php if ($ya > 0): ?>
                                    <span class="sig-badge sig-badge--sm sig-badge--success">Emitido</span>
                                <?php else: ?>
                                    <span class="sig-badge sig-badge--sm sig-badge--warning">Falta</span>
                                <?php endif; ?>
                            </td>
                            <td class="col-actions">
                                <?php if ($ya === 0): ?>
                                <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm"
                                        data-bs-toggle="modal" data-bs-target="#modalPermiso"
                                        onclick='nuevoPermiso(<?php echo htmlspecialchars(json_encode($cst->institucion), ENT_QUOTES, "UTF-8"); ?>)'>
                                    <i class="bi bi-envelope-paper"></i> Emitir
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <p style="font-size:11px;color:var(--text-tertiary);margin:var(--sp-3) 0 0;">
                Sale de la <strong>institución custodia</strong> de cada parada de los recorridos que se
                van a hacer. «Emitido» solo mira si ya hay un oficio que cubra esas fechas — no si lo aceptaron.
            </p>
        <?php endif; ?>
    </div>
</div>

<!-- ── Los oficios ───────────────────────────────────────────────────────── -->
<form method="GET" action="" class="anim-slide-up" style="margin-bottom:var(--sp-4);">
    <input type="hidden" name="desde" value="<?php echo $v($data['desde']); ?>">
    <input type="hidden" name="hasta" value="<?php echo $v($data['hasta']); ?>">
    <div class="sig-card">
        <div class="sig-card__body" style="padding:var(--sp-3) var(--sp-5);display:flex;gap:var(--sp-3);align-items:flex-end;flex-wrap:wrap;">
            <div class="sig-field" style="margin:0;min-width:240px;flex:1;">
                <label class="sig-field__label" for="q">Buscar</label>
                <input type="text" name="q" id="q" class="sig-input" value="<?php echo $v($data['filtro_q']); ?>"
                       placeholder="N° de oficio, institución o destinatario…">
            </div>
            <div class="sig-field" style="margin:0;min-width:170px;">
                <label class="sig-field__label" for="estado">Estado</label>
                <select name="estado" id="estado" class="sig-select">
                    <option value="">Todos</option>
                    <?php foreach (array_merge(PermisoRuta::ESTADOS, [PermisoRuta::EST_ANULADO]) as $opt): ?>
                        <option value="<?php echo $v($opt); ?>" <?php if ($data['filtro_estado'] === $opt) echo 'selected'; ?>>
                            <?php echo $v($opt); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-sig btn-sig--ghost"><i class="bi bi-funnel"></i> Filtrar</button>
        </div>
    </div>
</form>

<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15"
     data-buscar-placeholder="Buscar en los oficios…" data-titulo-export="Permisos de Acceso a Instituciones Custodias">
    <table class="sig-table">
        <thead>
            <tr>
                <th>N° de oficio</th>
                <th>Institución custodia</th>
                <th>Semana que cubre</th>
                <th class="text-center">Salidas</th>
                <th>Responsable</th>
                <th>Estado</th>
                <th class="col-actions">Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php if (empty($data['permisos'])): ?>
            <tr><td colspan="7" class="sig-table-empty">No hay oficios de permiso registrados.</td></tr>
        <?php else: foreach ($data['permisos'] as $p): ?>
            <tr>
                <td class="cell-id" style="font-family:var(--font-mono);font-weight:700;"><?php echo $v($p->numero); ?></td>
                <td class="cell-strong"><?php echo $v($p->institucion); ?></td>
                <td style="font-size:12px;color:var(--text-secondary);">
                    <?php echo $fmt($p->semana_desde); ?> — <?php echo $fmt($p->semana_hasta); ?>
                </td>
                <td class="text-center" style="font-weight:700;"><?php echo (int)$p->total_salidas; ?></td>
                <td style="font-size:12px;color:var(--text-secondary);"><?php echo $v($p->responsable_nombre ?: '—'); ?></td>
                <td>
                    <span class="sig-badge sig-badge--sm <?php echo PermisoRuta::ESTADO_BADGES[$p->estado] ?? 'sig-badge--neutral'; ?>">
                        <?php echo $v($p->estado); ?>
                    </span>
                </td>
                <td class="col-actions">
                    <a href="<?php echo URL_ROOT; ?>/rutas/permiso/<?php echo (int)$p->id; ?>" class="row-action" title="Abrir">
                        <i class="bi bi-folder2-open"></i>
                    </a>
                </td>
            </tr>
        <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- ── Modal: emitir el oficio ───────────────────────────────────────────── -->
<div class="modal fade" id="modalPermiso" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/rutas/emitirPermiso" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-envelope-paper"></i> Emitir oficio de permiso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="display:flex;flex-direction:column;gap:var(--sp-4);">
                <div style="padding:var(--sp-3);background:var(--bg-muted-subtle);border-radius:8px;font-size:12.5px;">
                    Un solo oficio cubre <strong>todas las salidas de la semana</strong> hacia esa
                    institución — así lo pidió el cliente, para agilizar el trámite (R-20).
                </div>

                <div class="row g-3">
                    <div class="col-md-7">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_institucion">Institución custodia <span class="req">*</span></label>
                            <input type="text" name="institucion" id="pm_institucion" class="sig-input" required data-nombre-libre
                                   placeholder="Ej: Fundación Castillo San Antonio de la Eminencia">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_fecha">Fecha del oficio</label>
                            <input type="date" name="fecha" id="pm_fecha" class="sig-input" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_dest">Dirigido a</label>
                            <input type="text" name="destinatario_nombre" id="pm_dest" class="sig-input" data-nombre-libre
                                   placeholder="Nombre de quien recibe">
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_cargo">Cargo</label>
                            <input type="text" name="destinatario_cargo" id="pm_cargo" class="sig-input" data-nombre-libre
                                   placeholder="Ej: Directora">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_desde">Semana desde <span class="req">*</span></label>
                            <input type="date" name="semana_desde" id="pm_desde" class="sig-input" required
                                   value="<?php echo $v($data['desde']); ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_hasta">Hasta <span class="req">*</span></label>
                            <input type="date" name="semana_hasta" id="pm_hasta" class="sig-input" required
                                   value="<?php echo $v($data['hasta']); ?>">
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="sig-field" style="margin:0;">
                            <label class="sig-field__label" for="pm_resp">Responsable del trámite</label>
                            <select name="id_responsable" id="pm_resp" class="sig-select js-search">
                                <option value="">— Sin asignar —</option>
                                <?php foreach ($data['empleados'] as $e): ?>
                                    <option value="<?php echo (int)$e->id; ?>"
                                        <?php if (!empty($data['responsable']) && (int)$data['responsable']->id === (int)$e->id) echo 'selected'; ?>>
                                        <?php echo $v(trim(($e->nombre ?? '') . ' ' . ($e->apellido ?? ''))); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small style="color:var(--text-tertiary);font-size:11px;">
                                Lo tramita y lo notifica el <strong>Director de Relaciones Inter-Institucionales</strong> (R-20).
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Qué salidas cubre -->
                <div>
                    <div style="font-size:12px;font-weight:700;color:var(--text-secondary);margin-bottom:var(--sp-2);">
                        Salidas que cubre <span class="req">*</span>
                    </div>
                    <?php if (empty($data['salidas'])): ?>
                        <div style="padding:var(--sp-3);background:var(--bg-muted-subtle);border-radius:8px;font-size:12.5px;color:var(--text-tertiary);">
                            No hay salidas programadas en el rango elegido. Cambie las fechas arriba
                            («Qué hay que pedir esta semana») y vuelva a abrir este formulario.
                        </div>
                    <?php else: ?>
                        <div style="max-height:220px;overflow:auto;border:1px solid var(--border-subtle);border-radius:8px;">
                            <table class="sig-table" style="margin:0;">
                                <thead>
                                    <tr>
                                        <th style="width:40px;">
                                            <input type="checkbox" id="pm_todas" title="Seleccionar todas">
                                        </th>
                                        <th>Fecha</th><th>Recorrido</th><th>Grupo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($data['salidas'] as $s): ?>
                                    <tr>
                                        <td><input type="checkbox" name="salidas[]" class="pm-salida" value="<?php echo (int)$s->id; ?>" checked></td>
                                        <td style="font-size:12px;"><?php echo $fmt($s->fecha); ?>
                                            <?php if (!empty($s->hora)): ?>
                                                <span style="color:var(--text-tertiary);"><?php echo substr($s->hora, 0, 5); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="cell-strong" style="font-size:13px;"><?php echo $v($s->ruta_nombre); ?></td>
                                        <td style="font-size:12px;color:var(--text-secondary);">
                                            <?php echo $v($s->institucion_nombre ?: $s->origen); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="sig-field" style="margin:0;">
                    <label class="sig-field__label" for="pm_obs">Observaciones <span style="color:var(--text-tertiary);font-weight:400;">(opcional)</span></label>
                    <textarea name="observaciones" id="pm_obs" class="sig-input" rows="2"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary" <?php if (empty($data['salidas'])) echo 'disabled'; ?>>
                    <i class="bi bi-check-lg"></i> Emitir
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function nuevoPermiso(institucion) {
    var f = document.getElementById('pm_institucion');
    if (f) f.value = institucion || '';
}
(function () {
    var todas = document.getElementById('pm_todas');
    if (!todas) return;
    todas.checked = true;
    todas.addEventListener('change', function () {
        document.querySelectorAll('.pm-salida').forEach(function (c) { c.checked = todas.checked; });
    });
})();
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
