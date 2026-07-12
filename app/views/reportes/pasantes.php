<?php require_once '../app/views/inc/header.php'; ?>

<?php
$qs = http_build_query(array_filter([
    'estado'       => $data['filtro_estado'] ?? '',
    'fecha_inicio' => $data['fecha_inicio']  ?? '',
    'fecha_fin'    => $data['fecha_fin']     ?? '',
    'buscar'       => $data['filtro_busca']  ?? '',
]));
$hayFiltro = !empty($data['filtro_estado']) || !empty($data['fecha_inicio']) || !empty($data['fecha_fin']) || !empty($data['filtro_busca']);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Académico
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Control de practicantes y pasantes con estado académico, fechas de vigencia y tutor asignado.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex;gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesCsv?<?php echo $qs; ?>" class="btn-sig btn-sig--success btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarPasantesPdf?<?php echo $qs; ?>" class="btn-sig btn-sig--danger btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- KPIs globales (siempre del total, no del filtro) -->
<div class="row g-3 mb-6 anim-slide-up">
    <?php
    $statsKpis = [
        ['label' => 'Total Pasantes',  'val' => $data['stats']->total      ?? 0, 'color' => 'var(--brand-500)',   'txt' => 'var(--brand-600)'],
        ['label' => 'En Curso',        'val' => $data['stats']->en_curso   ?? 0, 'color' => 'var(--accent-500)',  'txt' => 'var(--accent-600)'],
        ['label' => 'Culminados',      'val' => $data['stats']->culminados ?? 0, 'color' => 'var(--success-500)', 'txt' => 'var(--success-600)'],
        ['label' => 'Postulados',      'val' => $data['stats']->postulados ?? 0, 'color' => 'var(--warning-500)', 'txt' => 'var(--warning-600)'],
        ['label' => 'Aceptados',       'val' => $data['stats']->aceptados  ?? 0, 'color' => '#0EA5E9',            'txt' => '#0284C7'],
        ['label' => 'Rechazados',      'val' => $data['stats']->rechazados ?? 0, 'color' => 'var(--danger-500)',  'txt' => 'var(--danger-600)'],
    ];
    foreach ($statsKpis as $k): ?>
    <div class="col-md-2 col-4">
        <div class="sig-card" style="border-bottom:3px solid <?php echo $k['color']; ?>;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-4) var(--sp-3);">
                <div style="font-size:9px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:2px;"><?php echo $k['label']; ?></div>
                <div style="font-size:26px;font-weight:900;color:<?php echo $k['txt']; ?>;"><?php echo number_format($k['val']); ?></div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/pasantes" class="row g-3 align-items-end">
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha inicio desde</label>
                    <input type="date" name="fecha_inicio" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_inicio'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha fin hasta</label>
                    <input type="date" name="fecha_fin" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_fin'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Estado</label>
                    <select name="estado" class="sig-select">
                        <option value="">Todos</option>
                        <?php foreach (['Postulado','Aceptado','En Curso','Culminado','Rechazado'] as $opt): ?>
                            <option value="<?php echo $opt; ?>" <?php if (($data['filtro_estado'] ?? '') === $opt) echo 'selected'; ?>><?php echo $opt; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="sig-field">
                    <label class="sig-field__label">Buscar</label>
                    <input type="text" name="buscar" class="sig-input" placeholder="Nombre, apellido o cédula..." value="<?php echo htmlspecialchars($data['filtro_busca'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div style="display:flex;gap:var(--sp-2);">
                    <button type="submit" class="btn-sig btn-sig--primary" style="flex:1;height:42px;">
                        <i class="bi bi-funnel"></i> Filtrar
                    </button>
                    <?php if ($hayFiltro): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/pasantes" class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up" data-tabla-buscable data-por-pagina="15" data-buscar-placeholder="Buscar por pasante, cédula o institución…" data-no-export>
    <table class="sig-table">
        <thead>
            <tr>
                <th>Cédula</th>
                <th>Pasante</th>
                <th>Contacto</th>
                <th>Institución / Carrera</th>
                <th>Tutor IMATUR</th>
                <th style="text-align:center;">Fecha Inicio</th>
                <th style="text-align:center;">Fecha Fin</th>
                <th style="text-align:center;">Estado</th>
                <th style="text-align:center;">Nota / Evaluación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['pasantes'])): ?>
                <tr><td colspan="9" class="sig-table-empty">No se encontraron pasantes con los filtros aplicados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['pasantes'] as $p):
                    $bdg = ['Postulado' => 'sig-badge--warning', 'Aceptado' => 'sig-badge--info', 'En Curso' => 'sig-badge--brand', 'Culminado' => 'sig-badge--success', 'Rechazado' => 'sig-badge--danger'];
                    $isVigente = ($p->estado ?? '') === 'En Curso';
                ?>
                <tr <?php if ($isVigente) echo 'style="border-left:3px solid var(--accent-500);"'; ?>>
                    <td class="cell-id"><?php echo htmlspecialchars($p->cedula ?? '—'); ?></td>
                    <td class="cell-strong"><?php echo htmlspecialchars(($p->nombre ?? '') . ' ' . ($p->apellido ?? '')); ?></td>
                    <td style="font-size:12px;color:var(--text-secondary);">
                        <?php if (!empty($p->telefono)): ?><div><i class="bi bi-telephone" style="color:var(--text-tertiary);"></i> <?php echo htmlspecialchars($p->telefono); ?></div><?php endif; ?>
                        <?php if (!empty($p->correo)): ?><div style="word-break:break-all;"><i class="bi bi-envelope" style="color:var(--text-tertiary);"></i> <?php echo htmlspecialchars($p->correo); ?></div><?php endif; ?>
                        <?php if (empty($p->telefono) && empty($p->correo)): ?>—<?php endif; ?>
                    </td>
                    <td>
                        <div style="font-weight:600;font-size:13px;color:var(--text-primary);"><?php echo htmlspecialchars($p->institucion ?? '—'); ?></div>
                        <div style="font-size:11px;color:var(--text-tertiary);"><?php echo htmlspecialchars($p->carrera ?? '—'); ?></div>
                    </td>
                    <td>
                        <?php if (!empty($p->tutor_nombre)): ?>
                            <div style="display:flex;align-items:center;gap:var(--sp-2);font-size:13px;color:var(--text-secondary);">
                                <i class="bi bi-person-check" style="color:var(--brand-500);"></i>
                                <span><?php echo htmlspecialchars($p->tutor_nombre . ' ' . $p->tutor_apellido); ?></span>
                            </div>
                        <?php else: ?>
                            <span style="font-size:12px;color:var(--text-tertiary);font-style:italic;">No asignado</span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;font-size:12px;white-space:nowrap;">
                        <?php echo $p->fecha_inicio ? date('d/m/Y', strtotime($p->fecha_inicio)) : '—'; ?>
                    </td>
                    <td style="text-align:center;font-size:12px;white-space:nowrap;">
                        <?php echo $p->fecha_fin ? date('d/m/Y', strtotime($p->fecha_fin)) : '<span style="color:var(--text-tertiary);font-style:italic;">En curso</span>'; ?>
                    </td>
                    <td style="text-align:center;">
                        <span class="sig-badge <?php echo $bdg[$p->estado ?? ''] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($p->estado ?? '—'); ?></span>
                    </td>
                    <?php $tieneNota = $p->nota !== null && $p->nota !== ''; $tieneEval = !empty($p->evaluacion); ?>
                    <td style="text-align:center;font-size:12px;">
                        <?php if ($tieneNota): ?>
                            <span class="sig-badge sig-badge--info"><?php echo htmlspecialchars((string)$p->nota); ?></span>
                        <?php endif; ?>
                        <?php if ($tieneEval): ?>
                            <div style="color:var(--text-tertiary);margin-top:2px;max-width:220px;white-space:normal;"><?php echo htmlspecialchars($p->evaluacion); ?></div>
                        <?php endif; ?>
                        <?php if (!$tieneNota && !$tieneEval): ?>—<?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($data['pasantes'])): ?>
    <div style="text-align:right;font-size:12px;color:var(--text-tertiary);margin-top:var(--sp-2);">
        <?php echo count($data['pasantes']); ?> resultado(s) mostrados
    </div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
