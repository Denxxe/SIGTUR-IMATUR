<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Visitantes
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Registro completo de visitas institucionales con datos del visitante.</p>
    </div>
    <div class="page__actions">
        <?php
        $qs = http_build_query(array_filter([
            'fecha_inicio' => $data['fecha_inicio']  ?? '',
            'fecha_fin'    => $data['fecha_fin']     ?? '',
            'motivo'       => $data['filtro_motivo'] ?? '',
            'cedula'       => $data['filtro_cedula'] ?? '',
            'genero'       => $data['filtro_genero'] ?? '',
            'buscar'       => $data['filtro_buscar'] ?? '',
        ]));
        ?>
        <div style="display:flex;gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarVisitantesCsv?<?php echo $qs; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarVisitantesPdf?<?php echo $qs; ?>" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-5);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/visitantes" class="row g-3 align-items-end">
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Desde</label>
                    <input type="date" name="fecha_inicio" class="sig-input" value="<?php echo $data['fecha_inicio'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Hasta</label>
                    <input type="date" name="fecha_fin" class="sig-input" value="<?php echo $data['fecha_fin'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Buscar visitante</label>
                    <div style="position:relative;">
                        <i class="bi bi-search" style="position:absolute;left:10px;top:50%;transform:translateY(-50%);color:var(--text-tertiary);font-size:13px;pointer-events:none;"></i>
                        <input type="text" name="buscar" class="sig-input" style="padding-left:32px;" placeholder="Nombre, apellido o cédula..." value="<?php echo htmlspecialchars($data['filtro_buscar'] ?? ''); ?>">
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Motivo</label>
                    <input type="text" name="motivo" class="sig-input" placeholder="Ej: reunión, trámite..." value="<?php echo htmlspecialchars($data['filtro_motivo'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-1">
                <div class="sig-field">
                    <label class="sig-field__label">Género</label>
                    <select name="genero" class="sig-input">
                        <option value="">Todos</option>
                        <option value="M" <?php echo ($data['filtro_genero']??'')==='M'?'selected':''; ?>>Masc.</option>
                        <option value="F" <?php echo ($data['filtro_genero']??'')==='F'?'selected':''; ?>>Fem.</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div style="display:flex;gap:var(--sp-2);">
                    <button type="submit" class="btn-sig btn-sig--primary" style="flex:1;height:42px;">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php
                    $hayFiltro = !empty($data['filtro_motivo']) || !empty($data['filtro_buscar']) || !empty($data['filtro_genero']);
                    if ($hayFiltro): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/visitantes?fecha_inicio=<?php echo $data['fecha_inicio']; ?>&fecha_fin=<?php echo $data['fecha_fin']; ?>"
                           class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar filtros">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<?php
$totalV  = (int)($data['stats']->total_visitas    ?? 0);
$unicosV = (int)($data['stats']->visitantes_unicos ?? 0);
$totalReg = count($data['registros'] ?? []);
$conSalida   = 0; $sinSalida = 0; $masc = 0; $fem = 0;
foreach ($data['registros'] ?? [] as $r) {
    if (!empty($r->hora_salida)) $conSalida++; else $sinSalida++;
    if (($r->genero ?? '') === 'M') $masc++;
    elseif (($r->genero ?? '') === 'F') $fem++;
}
?>
<div class="row g-3 mb-5 anim-slide-up">
    <div class="col-6 col-md-3">
        <div class="sig-card" style="border-bottom:3px solid var(--brand-600);">
            <div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                <div style="font-size:0.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-tertiary);margin-bottom:4px;">Total Visitas</div>
                <div style="font-size:2rem;font-weight:900;color:var(--brand-600);"><?php echo number_format($totalV); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">en el período seleccionado</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sig-card" style="border-bottom:3px solid #059669;">
            <div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                <div style="font-size:0.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-tertiary);margin-bottom:4px;">Visitantes Únicos</div>
                <div style="font-size:2rem;font-weight:900;color:#059669;"><?php echo number_format($unicosV); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">personas distintas</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sig-card" style="border-bottom:3px solid #3B82F6;">
            <div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                <div style="font-size:0.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-tertiary);margin-bottom:4px;">Distribución Género</div>
                <div style="display:flex;justify-content:center;gap:var(--sp-4);margin-top:4px;">
                    <div><span style="font-size:1.4rem;font-weight:900;color:#3B82F6;"><?php echo $masc; ?></span><br><span style="font-size:10px;color:var(--text-tertiary);">Masc.</span></div>
                    <div><span style="font-size:1.4rem;font-weight:900;color:#EC4899;"><?php echo $fem; ?></span><br><span style="font-size:10px;color:var(--text-tertiary);">Fem.</span></div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $sinSalida > 0 ? '#D97706' : '#059669'; ?>;">
            <div class="sig-card__body" style="padding:var(--sp-4);text-align:center;">
                <div style="font-size:0.67rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em;color:var(--text-tertiary);margin-bottom:4px;">Con Salida Registrada</div>
                <div style="font-size:2rem;font-weight:900;color:<?php echo $sinSalida > 0 ? '#D97706' : '#059669'; ?>;"><?php echo $conSalida; ?></div>
                <?php if ($sinSalida > 0): ?>
                <div style="font-size:11px;color:#D97706;"><?php echo $sinSalida; ?> sin salida registrada</div>
                <?php else: ?>
                <div style="font-size:11px;color:#059669;">todas con salida registrada</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Visitante</th>
                <th>Contacto</th>
                <th>Procedencia</th>
                <th>Motivo</th>
                <th>Observaciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['registros'])): ?>
                <tr>
                    <td colspan="6" class="sig-table-empty">
                        <i class="bi bi-search" style="opacity:.5;margin-right:6px;"></i>
                        Sin registros en el rango y filtros seleccionados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['registros'] as $r):
                    $generoLabel = match($r->genero ?? '') { 'M' => ['Masc.','#3B82F6'], 'F' => ['Fem.','#EC4899'], default => null };
                ?>
                    <tr>
                        <td style="white-space:nowrap;">
                            <div style="font-weight:700;font-size:13px;"><?php echo date('d/m/Y', strtotime($r->fecha ?? $r->hora_entrada)); ?></div>
                            <div style="display:flex;flex-direction:column;gap:2px;margin-top:3px;">
                                <span class="sig-badge sig-badge--success" style="font-family:var(--font-mono);font-size:10px;">
                                    <i class="bi bi-arrow-right-circle"></i> <?php echo date('H:i', strtotime($r->hora_entrada)); ?>
                                </span>
                                <?php if (!empty($r->hora_salida)): ?>
                                <span class="sig-badge sig-badge--neutral" style="font-family:var(--font-mono);font-size:10px;">
                                    <i class="bi bi-arrow-left-circle"></i> <?php echo date('H:i', strtotime($r->hora_salida)); ?>
                                </span>
                                <?php else: ?>
                                <span class="sig-badge" style="font-size:10px;background:#FEF9C3;color:#92400E;">Sin salida</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <div style="display:flex;flex-direction:column;gap:2px;">
                                <span class="cell-strong"><?php echo htmlspecialchars(trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')) ?: 'Sin nombre'); ?></span>
                                <span class="cell-id"><?php echo htmlspecialchars($r->cedula ?? 'S/C'); ?></span>
                                <?php if ($generoLabel): ?>
                                <span style="font-size:10px;font-weight:700;color:<?php echo $generoLabel[1]; ?>;"><?php echo $generoLabel[0]; ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="font-size:12px;">
                            <div style="display:flex;flex-direction:column;gap:3px;">
                                <?php if (!empty($r->telefono)): ?>
                                <span><i class="bi bi-telephone" style="color:var(--text-tertiary);margin-right:4px;"></i><?php echo htmlspecialchars($r->telefono); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($r->correo)): ?>
                                <span style="word-break:break-all;"><i class="bi bi-envelope" style="color:var(--text-tertiary);margin-right:4px;"></i><?php echo htmlspecialchars($r->correo); ?></span>
                                <?php endif; ?>
                                <?php if (empty($r->telefono) && empty($r->correo)): ?>
                                <span style="color:var(--text-tertiary);font-style:italic;">—</span>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($r->procedencia ?? '—'); ?></td>
                        <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($r->motivo ?? '—'); ?></td>
                        <td style="font-size:11px;color:var(--text-tertiary);"><?php echo htmlspecialchars($r->observaciones ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
