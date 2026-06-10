<?php require_once '../app/views/inc/header.php'; ?>

<?php
// Construir querystring completo para exportaciones
$qs = http_build_query(array_filter([
    'fecha_inicio' => $data['fecha_inicio'] ?? '',
    'fecha_fin'    => $data['fecha_fin']    ?? '',
    'departamento' => $data['filtro_depto'] ?? '',
    'buscar'       => $data['filtro_busca'] ?? '',
]));
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/reportes/index" style="color:inherit;text-decoration:none;">Reportes</a> · Personal
        </div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? ''; ?></h1>
        <p class="page__subtitle">Control de asistencia del personal con filtros por fecha, departamento y nombre.</p>
    </div>
    <div class="page__actions">
        <div style="display:flex;gap:var(--sp-2);">
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarAsistenciaCsv?<?php echo $qs; ?>" class="btn-sig btn-sig--success btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Excel
            </a>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarAsistenciaPdf?<?php echo $qs; ?>" class="btn-sig btn-sig--danger btn-sig--sm" target="_blank">
                <i class="bi bi-file-earmark-pdf"></i> PDF
            </a>
        </div>
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<!-- Filtros -->
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <form method="GET" action="<?php echo URL_ROOT; ?>/reportes/asistencia" class="row g-3 align-items-end">
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_inicio'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div class="sig-field">
                    <label class="sig-field__label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="sig-input" value="<?php echo htmlspecialchars($data['fecha_fin'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Departamento</label>
                    <select name="departamento" class="sig-select">
                        <option value="">Todos los departamentos</option>
                        <?php foreach ($data['departamentos'] ?? [] as $dep): ?>
                            <option value="<?php echo $dep->id; ?>" <?php if (($data['filtro_depto'] ?? '') == $dep->id) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($dep->nombre); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="sig-field">
                    <label class="sig-field__label">Buscar empleado</label>
                    <input type="text" name="buscar" class="sig-input" placeholder="Nombre, apellido o cédula..." value="<?php echo htmlspecialchars($data['filtro_busca'] ?? ''); ?>">
                </div>
            </div>
            <div class="col-md-2">
                <div style="display:flex;gap:var(--sp-2);">
                    <button type="submit" class="btn-sig btn-sig--primary" style="flex:1;height:42px;">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                    <?php if (!empty($data['filtro_depto']) || !empty($data['filtro_busca'])): ?>
                        <a href="<?php echo URL_ROOT; ?>/reportes/asistencia?fecha_inicio=<?php echo $data['fecha_inicio']; ?>&fecha_fin=<?php echo $data['fecha_fin']; ?>" class="btn-sig btn-sig--ghost" style="height:42px;padding:0 var(--sp-3);" title="Limpiar filtros adicionales">
                            <i class="bi bi-x-lg"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- KPIs -->
<?php $tol = (int)($data['tolerancia'] ?? 15); ?>
<div class="row g-3 mb-6 anim-slide-up">
    <div class="col-md-3 col-6">
        <div class="sig-card" style="border-bottom:3px solid var(--brand-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Total Registros</div>
                <div style="font-size:28px;font-weight:900;color:var(--brand-600);"><?php echo number_format($data['stats']->total ?? 0); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">en el período</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sig-card" style="border-bottom:3px solid var(--success-500);">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Empleados con Registro</div>
                <div style="font-size:28px;font-weight:900;color:var(--success-600);"><?php echo number_format($data['stats']->empleados_unicos ?? 0); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">individuos únicos</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sig-card" style="border-bottom:3px solid #EF4444;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Impuntuales</div>
                <div style="font-size:28px;font-weight:900;color:#EF4444;"><?php echo number_format($data['stats']->impuntuales ?? 0); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">tolerancia <?php echo $tol; ?> min</div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="sig-card" style="border-bottom:3px solid #7C3AED;">
            <div class="sig-card__body" style="text-align:center;padding:var(--sp-5);">
                <div style="font-size:10px;font-weight:700;color:var(--text-tertiary);text-transform:uppercase;letter-spacing:.05em;margin-bottom:4px;">Horas Totales</div>
                <div style="font-size:28px;font-weight:900;color:#7C3AED;"><?php echo number_format($data['stats']->horas_totales ?? 0, 1); ?></div>
                <div style="font-size:11px;color:var(--text-tertiary);">solo reporte (no nómina)</div>
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
                <th>Empleado / Cédula</th>
                <th>Departamento</th>
                <th>Tipo Contrato</th>
                <th style="text-align:center;">Entrada</th>
                <th style="text-align:center;">Salida</th>
                <th style="text-align:center;">Horas</th>
                <th style="text-align:center;">Puntualidad</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['registros'])): ?>
                <tr><td colspan="9" class="sig-table-empty">Sin registros en el rango y filtros seleccionados.</td></tr>
            <?php else: ?>
                <?php foreach ($data['registros'] as $r): ?>
                    <tr>
                        <td style="white-space:nowrap;font-weight:600;"><?php echo date('d/m/Y', strtotime($r->fecha)); ?></td>
                        <td>
                            <div style="display:flex;flex-direction:column;">
                                <span class="cell-strong"><?php echo htmlspecialchars(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')); ?></span>
                                <span class="cell-id"><?php echo htmlspecialchars($r->cedula ?? 'S/C'); ?></span>
                            </div>
                        </td>
                        <td><span class="sig-badge sig-badge--neutral"><?php echo htmlspecialchars($r->departamento ?? '—'); ?></span></td>
                        <td style="font-size:12px;color:var(--text-secondary);">
                            <?php
                            $tc = $r->tipo_contrato ?? 'Fijo';
                            $tcColor = ['Fijo' => 'sig-badge--brand', 'Contratado' => 'sig-badge--info', 'Suplente' => 'sig-badge--warning', 'Comisión de Servicio' => 'sig-badge--neutral'];
                            ?>
                            <span class="sig-badge sig-badge--sm <?php echo $tcColor[$tc] ?? 'sig-badge--neutral'; ?>"><?php echo htmlspecialchars($tc); ?></span>
                        </td>
                        <td style="text-align:center;">
                            <span class="sig-badge sig-badge--success" style="font-family:var(--font-mono);"><?php echo date('H:i', strtotime($r->hora_entrada ?? 'now')); ?></span>
                        </td>
                        <td style="text-align:center;">
                            <?php if ($r->hora_salida): ?>
                                <span class="sig-badge sig-badge--danger" style="font-family:var(--font-mono);"><?php echo date('H:i', strtotime($r->hora_salida)); ?></span>
                            <?php else: ?>
                                <span style="color:var(--text-tertiary);font-size:11px;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center;font-family:var(--font-mono);font-size:12px;"><?php echo $r->horas !== null ? number_format((float)$r->horas, 2) : '—'; ?></td>
                        <td style="text-align:center;">
                            <?php
                            $mt = $r->minutos_tarde;
                            if ($mt === null) {
                                echo '<span class="sig-badge sig-badge--neutral">— sin horario</span>';
                            } elseif ((int)$mt > $tol) {
                                echo '<span class="sig-badge sig-badge--danger">Impuntual (' . (int)$mt . ' min)</span>';
                            } else {
                                echo '<span class="sig-badge sig-badge--success">Puntual</span>';
                            }
                            ?>
                        </td>
                        <td style="font-size:12px;color:var(--text-secondary);"><?php echo htmlspecialchars($r->observacion ?? '—'); ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($data['registros'])): ?>
    <div style="text-align:right;font-size:12px;color:var(--text-tertiary);margin-top:var(--sp-2);">
        <?php echo count($data['registros']); ?> registro(s) mostrados
    </div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
