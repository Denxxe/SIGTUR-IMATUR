<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit; text-decoration:none;">Reportes</a> · Visitantes
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Registro de visitas institucionales filtrado por fecha y motivo.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex; gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarVisitantesCsv?fecha_inicio=<?php echo $data['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $data['fecha_fin'] ?? ''; ?>&motivo=<?php echo urlencode($data['filtro_motivo'] ?? ''); ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel (CSV)
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarVisitantesPdf?fecha_inicio=<?php echo $data['fecha_inicio'] ?? ''; ?>&fecha_fin=<?php echo $data['fecha_fin'] ?? ''; ?>&motivo=<?php echo urlencode($data['filtro_motivo'] ?? ''); ?>" class="btn-sig btn-sig--ghost btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-5) var(--sp-6);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/visitantes" class="row g-4 align-items-end">
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="sig-input" value="<?php echo $data['fecha_inicio'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="sig-input" value="<?php echo $data['fecha_fin'] ?? ''; ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Motivo (contiene)</label>
                    <input type="text" name="motivo" class="sig-input" placeholder="Ej: reunión, trámite..." value="<?php echo htmlspecialchars($data['filtro_motivo'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn-sig btn-sig--primary" style="width:100%; height:42px;">
                    <i class="bi bi-filter"></i> Filtrar Resultados
                </button>
            </div>
        </form>
    </div>
</div>

<!-- KPI Cards -->
<div class="row g-4 mb-6 anim-slide-up">
    <div class="col-md-4">
        <div class="sig-card" style="border-bottom: 3px solid var(--brand-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-6);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Total Visitas</span>
                <span style="font-size:32px; font-weight:900; color:var(--brand-600);"><?php echo $data['stats']->total_visitas ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sig-card" style="border-bottom: 3px solid var(--success-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-6);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">Visitantes Únicos</span>
                <span style="font-size:32px; font-weight:900; color:var(--success-600);"><?php echo $data['stats']->visitantes_unicos ?? 0; ?></span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="sig-card" style="border-bottom: 3px solid var(--warning-500);">
            <div class="sig-card__body" style="text-align:center; padding:var(--sp-6);">
                <span style="display:block; font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:4px;">En Visita Ahora</span>
                <span style="font-size:32px; font-weight:900; color:var(--warning-600);"><?php echo $data['stats']->en_visita ?? 0; ?></span>
            </div>
        </div>
    </div>
</div>

<!-- Tabla -->
<div class="sig-table-wrap anim-slide-up">
    <table class="sig-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th style="text-align:center;">Hora Entrada</th>
                <th style="text-align:center;">Hora Salida</th>
                <th>Visitante / Cédula</th>
                <th>Procedencia</th>
                <th>Motivo</th>
                <th>Empleado Visitado</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['registros'])): ?>
                <tr>
                    <td colspan="7" class="sig-table-empty">Sin registros en el rango seleccionado.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['registros'] ?? [] as $r): ?>
                    <tr>
                        <td style="white-space:nowrap; font-weight:600;"><?php echo date('d/m/Y', strtotime($r->fecha ?? 'now')); ?></td>
                        <td style="text-align:center;"><span class="sig-badge sig-badge--success" style="font-family:var(--font-mono);"><?php echo date('H:i', strtotime($r->hora_entrada)); ?></span></td>
                        <td style="text-align:center;">
                            <?php if ($r->hora_salida): ?>
                                <span class="sig-badge sig-badge--danger" style="font-family:var(--font-mono);"><?php echo date('H:i', strtotime($r->hora_salida)); ?></span>
                            <?php else: ?>
                                <span class="sig-badge sig-badge--warning">En visita</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex; flex-direction:column;">
                                <span class="cell-strong"><?php echo htmlspecialchars(($r->nombre ?? 'N/A') . ' ' . ($r->apellido ?? '')); ?></span>
                                <span class="cell-id"><?php echo htmlspecialchars($r->cedula ?? 'S/C'); ?></span>
                            </div>
                        </td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars($r->procedencia ?? '—'); ?></td>
                        <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($r->motivo ?? '—'); ?></td>
                        <td style="font-size:13px;"><?php echo htmlspecialchars(trim(($r->emp_nombre ?? '') . ' ' . ($r->emp_apellido ?? '')) ?: 'N/A'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
