<?php require_once '../app/views/inc/header.php';
$e   = $data['empleado'];
$eid = (int)$e->id;
$ffecha = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '—';
$val    = fn($v) => !empty($v) ? htmlspecialchars($v) : '—';
$esBool = fn($v) => ($v === true || $v === 't' || $v === '1');
$egresado = !empty($e->fecha_egreso);
$motivos  = $data['motivos'] ?? [];
$tiempoServicio = $data['tiempo_servicio'] ?? '—';

// Estatus actual del trabajador (visible en el expediente).
$permVig = $data['permiso_vigente'] ?? null;
if ($egresado) {
    $estatusTxt = 'Egresado · ' . ($e->motivo_egreso ?: '—');
    $estatusCls = 'sig-badge--danger';
} elseif ($permVig) {
    $estatusTxt = ($permVig->categoria === 'Reposo' ? 'En reposo' : 'En permiso') . ' · ' . ($permVig->tipo_permiso ?? '');
    $estatusCls = 'sig-badge--warning';
} else {
    $estatusTxt = 'Activo';
    $estatusCls = 'sig-badge--success';
}
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Expediente del Trabajador</div>
        <h1 class="page__title"><?php echo htmlspecialchars($e->nombre . ' ' . $e->apellido); ?></h1>
        <p class="page__subtitle">C.I. <?php echo $val($e->cedula); ?> · Expediente <?php echo $val($e->nro_expediente); ?></p>
        <div style="margin-top:6px; display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
            <span class="sig-badge <?php echo $estatusCls; ?>" title="Estatus actual del trabajador">
                <i class="bi bi-circle-fill" style="font-size:7px; vertical-align:middle;"></i> <?php echo htmlspecialchars($estatusTxt); ?>
            </span>
            <span class="sig-badge sig-badge--neutral" title="Tiempo de servicio">
                <i class="bi bi-clock-history"></i> <?php echo htmlspecialchars($tiempoServicio); ?><?php echo $egresado ? '' : ' (a la fecha)'; ?>
            </span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/empleados/fichaTecnica/<?php echo $eid; ?>" target="_blank" class="btn-sig btn-sig--primary">
            <i class="bi bi-file-earmark-text"></i> Ficha Técnica
        </a>
        <a href="<?php echo URL_ROOT; ?>/empleados/carnet/<?php echo $eid; ?>" target="_blank" class="btn-sig btn-sig--primary">
            <i class="bi bi-person-vcard"></i> Carnet
        </a>
        <?php if ($egresado): ?>
            <button type="button" class="btn-sig btn-sig--success" data-bs-toggle="modal" data-bs-target="#modalReingreso">
                <i class="bi bi-arrow-counterclockwise"></i> Reingreso
            </button>
        <?php else: ?>
            <button type="button" class="btn-sig btn-sig--danger" data-bs-toggle="modal" data-bs-target="#modalEgreso">
                <i class="bi bi-box-arrow-right"></i> Procesar egreso
            </button>
        <?php endif; ?>
        <a href="<?php echo URL_ROOT; ?>/empleados/index<?php echo $egresado ? '?ver=egresados' : ''; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php if ($egresado): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:20px;border-left:4px solid var(--warning-500,#f59e0b);">
    <div class="sig-card__body" style="display:flex;align-items:center;gap:14px;">
        <i class="bi bi-archive" style="font-size:1.6rem;color:var(--warning-600,#d97706);"></i>
        <div>
            <strong>Empleado egresado</strong> — registro histórico (no aparece en la nómina activa).<br>
            <span style="font-size:13px;color:var(--text-secondary);">
                Egresó el <strong><?php echo $ffecha($e->fecha_egreso); ?></strong>
                por <strong><?php echo $val($e->motivo_egreso); ?></strong>.
                Tiempo de servicio: <strong><?php echo htmlspecialchars($tiempoServicio); ?></strong>.
                <?php if (!empty($e->observacion_egreso)): ?><br>Observación: <?php echo htmlspecialchars($e->observacion_egreso); ?><?php endif; ?>
            </span>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Resumen de datos -->
<div class="sig-card anim-slide-up" style="margin-bottom:20px;">
    <div class="sig-card__body">
        <div class="row g-4">
            <div class="col-md-4">
                <h6 class="text-muted"><i class="bi bi-person-vcard"></i> Datos Personales</h6>
                <div style="display:flex; align-items:center; gap:12px; margin-bottom:12px;">
                    <div style="width:64px; height:80px; border:1px solid var(--border-subtle); border-radius:6px; overflow:hidden; background:var(--bg-muted-subtle); flex-shrink:0; display:flex; align-items:center; justify-content:center;">
                        <img src="<?php echo URL_ROOT; ?>/descarga/foto/<?php echo (int)$e->id_persona; ?>?v=<?php echo time(); ?>"
                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
                             style="width:100%; height:100%; object-fit:cover;" alt="Foto">
                        <span style="display:none; align-items:center; justify-content:center; width:100%; height:100%; font-size:32px; color:var(--text-tertiary);"><i class="bi bi-person"></i></span>
                    </div>
                    <button type="button" class="btn-sig btn-sig--ghost btn-sig--sm" data-bs-toggle="modal" data-bs-target="#modalFoto">
                        <i class="bi bi-camera"></i> Cargar / cambiar foto
                    </button>
                </div>
                <dl class="mb-0">
                    <dt>Teléfono</dt><dd><?php echo $val($e->telefono); ?></dd>
                    <dt>Correo</dt><dd><?php echo $val($e->correo); ?></dd>
                    <dt>F. Nacimiento</dt><dd><?php echo $ffecha($e->fecha_nacimiento); ?></dd>
                    <dt>Parroquia</dt><dd><?php echo $val($e->parroquia); ?></dd>
                    <dt>Dirección</dt><dd><?php echo $val($e->direccion); ?></dd>
                    <dt>RIF</dt><dd><?php echo $val($e->rif); ?></dd>
                    <dt>Estado civil</dt><dd><?php echo $val($e->estado_civil); ?></dd>
                    <dt>Discapacidad</dt><dd><?php echo $esBool($e->discapacidad) ? ('Sí — ' . $val($e->discapacidad_detalle)) : 'No'; ?></dd>
                </dl>
            </div>
            <div class="col-md-4">
                <h6 class="text-muted"><i class="bi bi-mortarboard"></i> Formación Académica</h6>
                <dl class="mb-0">
                    <dt>Nivel académico</dt><dd><?php echo $val($e->nivel_academico); ?></dd>
                    <dt>Profesión</dt><dd><?php echo $val($e->profesion); ?></dd>
                    <dt>F. Graduación</dt><dd><?php echo $ffecha($e->fecha_graduacion); ?></dd>
                    <dt>Institución</dt><dd><?php echo $val($e->institucion_academica); ?></dd>
                </dl>
            </div>
            <div class="col-md-4">
                <h6 class="text-muted"><i class="bi bi-building"></i> Datos Laborales</h6>
                <dl class="mb-0">
                    <dt>Cargo</dt><dd><?php echo $val($e->cargo); ?></dd>
                    <dt>Departamento</dt><dd><?php echo $val($e->departamento); ?></dd>
                    <dt>Tipo de contrato</dt><dd><?php echo $val($e->tipo_contrato); ?></dd>
                    <dt>Clasificación</dt><dd><?php echo $val($e->clasificacion); ?></dd>
                    <dt>Institución / Nómina</dt><dd><?php echo $val($e->institucion_origen); ?><?php echo $esBool($e->es_comision_servicio) ? ' (Comisión de servicio)' : ''; ?></dd>
                    <dt>F. Ingreso</dt><dd><?php echo $ffecha($e->fecha_ingreso); ?></dd>
                    <dt>Tiempo de servicio</dt><dd><?php echo htmlspecialchars($tiempoServicio); ?><?php echo $egresado ? '' : ' (a la fecha)'; ?></dd>
                    <?php if ($egresado): ?>
                        <dt>F. Egreso</dt><dd><?php echo $ffecha($e->fecha_egreso); ?></dd>
                        <dt>Motivo de egreso</dt><dd><?php echo $val($e->motivo_egreso); ?></dd>
                    <?php endif; ?>
                    <dt>Horario</dt><dd><?php echo $val($e->horario); ?></dd>
                </dl>
            </div>
        </div>
    </div>
</div>

<?php
// Helper para renderizar cada sección de tabla hija
function seccionHijo($titulo, $icono, $btnTarget, $cols, $rows, $renderRow) {
    ?>
    <div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;">
            <h5 style="margin:0;"><i class="bi <?php echo $icono; ?>"></i> <?php echo $titulo; ?></h5>
            <button type="button" class="btn-sig btn-sig--primary btn-sig--sm" data-bs-toggle="modal" data-bs-target="#<?php echo $btnTarget; ?>">
                <i class="bi bi-plus-lg"></i> Agregar
            </button>
        </div>
        <table class="sig-table">
            <thead><tr>
                <?php foreach ($cols as $c): ?><th><?php echo $c; ?></th><?php endforeach; ?>
                <th class="col-actions">Acción</th>
            </tr></thead>
            <tbody>
                <?php if (empty($rows)): ?>
                    <tr><td colspan="<?php echo count($cols) + 1; ?>" class="sig-table-empty">Sin registros.</td></tr>
                <?php else: foreach ($rows as $r) { $renderRow($r); } endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}

// ── Carga Familiar ──
$edadDe = fn($f) => Util::edadTexto($f);
seccionHijo('Carga Familiar', 'bi-people', 'modalFamiliar',
    ['Nombre y apellido', 'Cédula', 'Sexo', 'F. Nacimiento', 'Edad', 'Parentesco', 'Estado'],
    $data['familiares'],
    function ($r) use ($eid, $ffecha, $val, $edadDe, $esBool) {
        $vive = !isset($r->vive) || $esBool($r->vive) || $r->vive === null;
        echo '<tr>';
        echo '<td class="cell-strong">' . $val($r->nombre_apellido) . '</td>';
        echo '<td>' . $val($r->cedula) . '</td>';
        echo '<td>' . (($r->genero ?? '') === 'M' ? 'M' : (($r->genero ?? '') === 'F' ? 'F' : '—')) . '</td>';
        echo '<td>' . $ffecha($r->fecha_nacimiento) . '</td>';
        echo '<td>' . htmlspecialchars($edadDe($r->fecha_nacimiento ?? null)) . '</td>';
        echo '<td><span class="sig-badge sig-badge--info">' . $val($r->parentesco) . '</span></td>';
        echo '<td><span class="sig-badge ' . ($vive ? 'sig-badge--success' : 'sig-badge--danger') . '">' . ($vive ? 'Vivo' : 'Fallecido') . '</span></td>';
        echo '<td class="col-actions"><a href="' . URL_ROOT . '/empleados/eliminarFamiliar/' . $r->id . '/' . $eid . '" class="row-action row-action--del" onclick="return confirm(\'¿Eliminar este familiar?\')"><i class="bi bi-trash"></i></a></td>';
        echo '</tr>';
    }
);

// ── Cursos Realizados ──
seccionHijo('Cursos Realizados', 'bi-journal-text', 'modalCurso',
    ['Institución', 'Curso', 'Inicio', 'Culminación'],
    $data['cursos'],
    function ($r) use ($eid, $ffecha, $val) {
        echo '<tr>';
        echo '<td>' . $val($r->institucion) . '</td>';
        echo '<td class="cell-strong">' . $val($r->curso) . '</td>';
        echo '<td>' . $ffecha($r->fecha_inicio) . '</td>';
        echo '<td>' . $ffecha($r->fecha_culminacion) . '</td>';
        echo '<td class="col-actions"><a href="' . URL_ROOT . '/empleados/eliminarCurso/' . $r->id . '/' . $eid . '" class="row-action row-action--del" onclick="return confirm(\'¿Eliminar este curso?\')"><i class="bi bi-trash"></i></a></td>';
        echo '</tr>';
    }
);

// ── Experiencia Laboral ──
seccionHijo('Experiencia Laboral (trabajos anteriores)', 'bi-briefcase', 'modalExperiencia',
    ['Organismo', 'Cargo', 'Inicio', 'Culminación'],
    $data['experiencia'],
    function ($r) use ($eid, $ffecha, $val) {
        echo '<tr>';
        echo '<td class="cell-strong">' . $val($r->organismo) . '</td>';
        echo '<td>' . $val($r->cargo) . '</td>';
        echo '<td>' . $ffecha($r->fecha_inicio) . '</td>';
        echo '<td>' . $ffecha($r->fecha_culminacion) . '</td>';
        echo '<td class="col-actions"><a href="' . URL_ROOT . '/empleados/eliminarExperiencia/' . $r->id . '/' . $eid . '" class="row-action row-action--del" onclick="return confirm(\'¿Eliminar esta experiencia?\')"><i class="bi bi-trash"></i></a></td>';
        echo '</tr>';
    }
);

// ── Recaudos del expediente (checklist + subida) ──
$rec = $data['recaudos'] ?? ['items' => [], 'faltan_obligatorios' => 0];
?>

<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;">
        <h5 style="margin:0;"><i class="bi bi-folder-check"></i> Recaudos del Expediente</h5>
        <div style="display:flex;align-items:center;gap:12px;">
            <?php if ($rec['faltan_obligatorios'] > 0): ?>
                <span class="sig-badge sig-badge--warning"><i class="bi bi-exclamation-triangle"></i> Faltan <?php echo $rec['faltan_obligatorios']; ?> obligatorio(s)</span>
            <?php else: ?>
                <span class="sig-badge sig-badge--success"><i class="bi bi-check2-all"></i> Recaudos obligatorios completos</span>
            <?php endif; ?>
            <button type="button" class="btn-sig btn-sig--primary btn-sig--sm" data-bs-toggle="modal" data-bs-target="#modalDocumento"><i class="bi bi-upload"></i> Cargar recaudo</button>
        </div>
    </div>
    <table class="sig-table">
        <thead><tr><th>Recaudo</th><th>Estado</th><th>Archivo(s)</th></tr></thead>
        <tbody>
            <?php foreach ($rec['items'] as $it): ?>
                <tr>
                    <td class="cell-strong">
                        <?php echo htmlspecialchars($it['label']); ?>
                        <?php echo $it['obligatorio'] ? '<span class="req">*</span>' : '<small style="color:var(--text-tertiary)"> (opcional)</small>'; ?>
                    </td>
                    <td>
                        <?php if ($it['entregado']): ?>
                            <span class="sig-badge sig-badge--success"><i class="bi bi-check-lg"></i> Entregado</span>
                        <?php elseif ($it['obligatorio']): ?>
                            <span class="sig-badge sig-badge--danger">Falta</span>
                        <?php else: ?>
                            <span class="sig-badge sig-badge--neutral">—</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php foreach ($it['documentos'] as $doc): ?>
                            <div style="display:flex;align-items:center;gap:8px;margin:2px 0;">
                                <a href="<?php echo URL_ROOT; ?>/descarga/expediente/<?php echo (int)$doc->id; ?>" target="_blank" style="font-size:13px;">
                                    <i class="bi bi-file-earmark-arrow-down"></i> <?php echo htmlspecialchars($doc->nombre_original ?? 'documento'); ?>
                                </a>
                                <a href="<?php echo URL_ROOT; ?>/empleados/eliminarDocumento/<?php echo $doc->id; ?>/<?php echo $eid; ?>" class="row-action row-action--del" onclick="return confirm('¿Eliminar este recaudo?')"><i class="bi bi-trash"></i></a>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($it['documentos'])): ?><span style="color:var(--text-tertiary)">—</span><?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal: Cargar / cambiar foto (carnetización) -->
<div class="modal fade" id="modalFoto" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/subirFoto" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title"><i class="bi bi-camera"></i> Foto del trabajador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <p style="font-size:13px;color:var(--text-secondary);margin-bottom:10px;">Se usará en el carnet. Recomendado: foto tipo carnet, fondo claro.</p>
                <div class="sig-field"><label class="sig-field__label">Imagen (JPG/PNG, máx. 5 MB) <span class="req">*</span></label>
                    <input type="file" name="foto" class="sig-input" accept=".jpg,.jpeg,.png" required></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-upload"></i> Guardar foto</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Cargar recaudo -->
<div class="modal fade" id="modalDocumento" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/subirDocumento" method="POST" enctype="multipart/form-data" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Cargar recaudo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <div class="sig-field mb-3"><label class="sig-field__label">Tipo de recaudo <span class="req">*</span></label>
                    <select name="tipo_documento" class="sig-select" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach (ExpedienteDocumento::RECAUDOS as $clave => [$label, $obl]): ?>
                            <option value="<?php echo $clave; ?>"><?php echo htmlspecialchars($label) . ($obl ? ' *' : ''); ?></option>
                        <?php endforeach; ?>
                    </select></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Archivo (PDF/JPG/PNG, máx. 5 MB) <span class="req">*</span></label>
                    <input type="file" name="archivo" class="sig-input" accept=".pdf,.jpg,.jpeg,.png" required></div>
                <div class="sig-field"><label class="sig-field__label">Observación</label>
                    <input type="text" name="observaciones" class="sig-input"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-upload"></i> Cargar</button>
            </div>
        </form>
    </div>
</div>

<!-- Constancias / Documentos generados -->
<div class="anim-slide-up" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:4px 4px 10px;">
        <h5 style="margin:0;"><i class="bi bi-file-earmark-text"></i> Constancias / Documentos generados</h5>
        <?php
        // Tipos disponibles según el estatus: egreso solo para egresados;
        // bancaria/horario solo para activos.
        $tiposDisponibles = [];
        foreach (Constancia::TIPOS as $k => $label) {
            if ($k === 'egreso' && !$egresado) continue;
            if (in_array($k, ['bancaria', 'horario'], true) && $egresado) continue;
            $tiposDisponibles[$k] = $label;
        }
        ?>
        <div class="dropdown">
            <button class="btn-sig btn-sig--primary btn-sig--sm dropdown-toggle" data-bs-toggle="dropdown" type="button">
                <i class="bi bi-file-earmark-plus"></i> Generar constancia
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                <?php foreach ($tiposDisponibles as $k => $label): ?>
                    <li>
                        <a class="dropdown-item" href="<?php echo URL_ROOT; ?>/empleados/generarConstancia/<?php echo $eid; ?>/<?php echo $k; ?>"
                           onclick="return confirm('¿Generar <?php echo htmlspecialchars($label, ENT_QUOTES); ?>?')">
                            <i class="bi bi-file-earmark-text"></i> <?php echo htmlspecialchars($label); ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <div class="sig-table-wrap">
    <table class="sig-table">
        <thead><tr><th>N° Documento</th><th>Tipo</th><th>Emisión</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php if (empty($data['constancias'])): ?>
                <tr><td colspan="4" class="sig-table-empty">Sin constancias generadas.</td></tr>
            <?php else: foreach ($data['constancias'] as $co): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($co->numero); ?></td>
                    <td style="font-size:13px"><?php echo htmlspecialchars(Constancia::labelTipo($co->tipo)); ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($co->fecha_emision)); ?></td>
                    <td class="col-actions">
                        <a href="<?php echo URL_ROOT; ?>/empleados/constancia/<?php echo $co->id; ?>" target="_blank" class="row-action"><i class="bi bi-printer"></i> Ver / Imprimir</a>
                        <a href="<?php echo URL_ROOT; ?>/empleados/eliminarConstancia/<?php echo $co->id; ?>/<?php echo $eid; ?>" class="row-action row-action--del" onclick="return confirm('¿Eliminar esta constancia del historial?')"><i class="bi bi-trash"></i></a>
                    </td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
    </div>
</div>

<!-- Modal: Familiar -->
<div class="modal fade" id="modalFamiliar" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/guardarFamiliar" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar Familiar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <input type="hidden" name="id_persona" value="<?php echo (int)$e->id_persona; ?>">
                <div class="sig-field mb-3"><label class="sig-field__label">Nombre y apellido <span class="req">*</span></label>
                    <input type="text" name="nombre_apellido" class="sig-input" required></div>
                <div class="row g-3">
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Cédula</label>
                        <input type="text" name="cedula" class="sig-input"></div></div>
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">F. Nacimiento</label>
                        <input type="date" name="fecha_nacimiento" id="cf_fnac_modal" class="sig-input js-edad"></div></div>
                </div>
                <div class="row g-3 mt-0">
                    <div class="col-4"><div class="sig-field"><label class="sig-field__label">Parentesco <span class="req">*</span></label>
                        <select name="parentesco" class="sig-select" required>
                            <option value="">— Seleccione —</option>
                            <?php foreach (CargaFamiliar::PARENTESCOS as $p): ?>
                                <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
                            <?php endforeach; ?>
                        </select></div></div>
                    <div class="col-4"><div class="sig-field"><label class="sig-field__label">Sexo</label>
                        <select name="genero" class="sig-select">
                            <option value="">—</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select></div></div>
                    <div class="col-4"><div class="sig-field"><label class="sig-field__label">Estado</label>
                        <select name="vive" class="sig-select">
                            <option value="1">Vivo</option>
                            <option value="0">Fallecido</option>
                        </select></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Curso -->
<div class="modal fade" id="modalCurso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/guardarCurso" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar Curso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <input type="hidden" name="id_persona" value="<?php echo (int)$e->id_persona; ?>">
                <div class="sig-field mb-3"><label class="sig-field__label">Curso <span class="req">*</span></label>
                    <input type="text" name="curso" class="sig-input" required></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Institución</label>
                    <input type="text" name="institucion" class="sig-input"></div>
                <div class="row g-3">
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Inicio</label>
                        <input type="date" name="fecha_inicio" class="sig-input"></div></div>
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Culminación</label>
                        <input type="date" name="fecha_culminacion" class="sig-input"></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Experiencia -->
<div class="modal fade" id="modalExperiencia" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/guardarExperiencia" method="POST" class="modal-content">
            <div class="modal-header"><h5 class="modal-title">Agregar Experiencia Laboral</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <input type="hidden" name="id_persona" value="<?php echo (int)$e->id_persona; ?>">
                <div class="sig-field mb-3"><label class="sig-field__label">Organismo / Empleador <span class="req">*</span></label>
                    <input type="text" name="organismo" class="sig-input" required></div>
                <div class="sig-field mb-3"><label class="sig-field__label">Cargo</label>
                    <input type="text" name="cargo" class="sig-input"></div>
                <div class="row g-3">
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Inicio</label>
                        <input type="date" name="fecha_inicio" class="sig-input"></div></div>
                    <div class="col-6"><div class="sig-field"><label class="sig-field__label">Culminación</label>
                        <input type="date" name="fecha_culminacion" class="sig-input"></div></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>

<?php
// ── Historial de egresos / reingresos ──
$hist = $data['historial_egresos'] ?? [];
if (!empty($hist)): ?>
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;"><h5 style="margin:0;"><i class="bi bi-clock-history"></i> Historial de egresos / reingresos</h5></div>
    <table class="sig-table">
        <thead><tr><th>F. Egreso</th><th>Motivo</th><th>Observación</th><th>F. Reingreso</th></tr></thead>
        <tbody>
            <?php foreach ($hist as $h): ?>
                <tr>
                    <td><?php echo $ffecha($h->fecha_egreso); ?></td>
                    <td><span class="sig-badge sig-badge--warning"><?php echo htmlspecialchars($h->motivo_egreso); ?></span></td>
                    <td style="font-size:13px;"><?php echo $val($h->observacion); ?></td>
                    <td><?php echo !empty($h->fecha_reingreso) ? $ffecha($h->fecha_reingreso) : '<span class="text-muted">— vigente —</span>'; ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?php endif; ?>

<?php
// ── Traslados de departamento (3D) ──
$traslados = $data['historial_traslados'] ?? [];
?>
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <h5 style="margin:0;"><i class="bi bi-arrow-left-right"></i> Traslados de departamento</h5>
        <?php if (!$egresado): ?>
        <button type="button" class="btn-sig btn-sig--sm btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalTraslado">
            <i class="bi bi-arrow-left-right"></i> Trasladar
        </button>
        <?php endif; ?>
    </div>
    <table class="sig-table">
        <thead><tr><th>Fecha</th><th>Departamento</th><th>Cargo</th><th>Motivo</th></tr></thead>
        <tbody>
            <?php if (empty($traslados)): ?>
                <tr><td colspan="4" class="sig-table-empty">Sin traslados registrados.</td></tr>
            <?php else: foreach ($traslados as $t): ?>
                <tr>
                    <td><?php echo $ffecha($t->fecha); ?></td>
                    <td><?php echo $val($t->depto_origen); ?> <i class="bi bi-arrow-right" style="color:var(--text-tertiary)"></i> <strong><?php echo $val($t->depto_destino); ?></strong></td>
                    <td><?php echo $val($t->cargo_origen); ?> <i class="bi bi-arrow-right" style="color:var(--text-tertiary)"></i> <?php echo $val($t->cargo_destino); ?></td>
                    <td style="font-size:13px;"><?php echo $val($t->motivo); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php
// ── Datos salariales (R-11 — insumo de Nómina/Bono Vacacional) ──
$sueldoActual   = $data['sueldo_actual'] ?? null;
$historialSueldos = $data['historial_sueldos'] ?? [];
$fmtMonto = fn($v) => number_format((float)$v, 2, ',', '.');
?>
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:8px;">
        <h5 style="margin:0;"><i class="bi bi-cash-coin"></i> Datos salariales</h5>
        <?php if (!$egresado): ?>
        <button type="button" class="btn-sig btn-sig--sm btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalSueldo">
            <i class="bi bi-cash-coin"></i> Registrar sueldo
        </button>
        <?php endif; ?>
    </div>
    <?php if (!$sueldoActual): ?>
        <p class="sig-table-empty" style="padding:12px 16px;">Sin datos salariales registrados.</p>
    <?php else: ?>
        <table class="sig-table">
            <thead><tr><th>Sueldo Básico</th><th>Prima Prof.</th><th>Prima Resp.</th><th>Prima Antig.</th><th>Prima/Hijo</th><th>Bono Transp.</th><th>Prima FOND</th><th>Prima Discap.</th><th>Caja Ahorro</th></tr></thead>
            <tbody>
                <tr>
                    <td><?php echo $fmtMonto($sueldoActual->sueldo_basico); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->prima_profesional); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->prima_responsabilidad); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->prima_antiguedad); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->prima_por_hijo); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->bono_transporte); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->prima_fond); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->prima_discapacidad); ?></td>
                    <td><?php echo $fmtMonto($sueldoActual->caja_ahorro); ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
    <?php if (!empty($historialSueldos)): ?>
    <details style="padding:8px 16px 12px;">
        <summary style="cursor:pointer;color:var(--text-secondary);font-size:13px;">Historial (<?php echo count($historialSueldos); ?>)</summary>
        <table class="sig-table" style="margin-top:8px;">
            <thead><tr><th>Vigente desde</th><th>Sueldo Básico</th><th>Motivo</th></tr></thead>
            <tbody>
                <?php foreach ($historialSueldos as $h): ?>
                <tr>
                    <td><?php echo $ffecha($h->fecha_efectiva); ?></td>
                    <td><?php echo $fmtMonto($h->sueldo_basico); ?></td>
                    <td style="font-size:13px;"><?php echo $val($h->motivo); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </details>
    <?php endif; ?>
</div>

<?php if (!$egresado): ?>
<!-- Modal: Registrar sueldo -->
<div class="modal fade" id="modalSueldo" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/guardarSueldo" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-cash-coin"></i> Registrar sueldo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <p class="text-muted" style="font-size:13px;">
                    Se agrega como un nuevo registro; no borra el historial anterior — es insumo de Nómina/Bono Vacacional.
                </p>
                <div class="row g-2">
                    <div class="col-6 sig-field"><label class="sig-field__label">Sueldo Básico</label><input type="number" step="0.01" min="0" name="sueldo_basico" class="sig-input" value="<?php echo $sueldoActual->sueldo_basico ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Prima Profesional</label><input type="number" step="0.01" min="0" name="prima_profesional" class="sig-input" value="<?php echo $sueldoActual->prima_profesional ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Prima Responsabilidad</label><input type="number" step="0.01" min="0" name="prima_responsabilidad" class="sig-input" value="<?php echo $sueldoActual->prima_responsabilidad ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Prima Antigüedad</label><input type="number" step="0.01" min="0" name="prima_antiguedad" class="sig-input" value="<?php echo $sueldoActual->prima_antiguedad ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Prima por Hijo (unitaria)</label><input type="number" step="0.01" min="0" name="prima_por_hijo" class="sig-input" value="<?php echo $sueldoActual->prima_por_hijo ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Bono Transporte</label><input type="number" step="0.01" min="0" name="bono_transporte" class="sig-input" value="<?php echo $sueldoActual->bono_transporte ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Prima FOND</label><input type="number" step="0.01" min="0" name="prima_fond" class="sig-input" value="<?php echo $sueldoActual->prima_fond ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Prima Discapacidad</label><input type="number" step="0.01" min="0" name="prima_discapacidad" class="sig-input" value="<?php echo $sueldoActual->prima_discapacidad ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Caja de Ahorro</label><input type="number" step="0.01" min="0" name="caja_ahorro" class="sig-input" value="<?php echo $sueldoActual->caja_ahorro ?? 0; ?>"></div>
                    <div class="col-6 sig-field"><label class="sig-field__label">Vigente desde</label><input type="date" name="fecha_efectiva" class="sig-input" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>"></div>
                </div>
                <div class="sig-field mt-2">
                    <label class="sig-field__label">Motivo</label>
                    <input type="text" name="motivo" class="sig-input" placeholder="Ej: aumento decretado, ajuste de escala…">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Guardar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!$egresado): ?>
<!-- Modal: Trasladar de departamento -->
<div class="modal fade" id="modalTraslado" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/trasladar" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-left-right"></i> Trasladar de departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <p class="text-muted" style="font-size:13px;">
                    Actual: <strong><?php echo $val($e->departamento); ?></strong> — <?php echo $val($e->cargo); ?>.
                    El cambio se aplica al expediente y queda registrado en el historial.
                </p>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Departamento destino <span class="req">*</span></label>
                    <select name="id_departamento_destino" class="sig-select js-search" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($data['departamentos'] ?? [] as $d): ?>
                            <option value="<?php echo $d->id; ?>" <?php echo ((int)$d->id === (int)$e->id_departamento) ? 'disabled' : ''; ?>><?php echo htmlspecialchars($d->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Cargo destino <small style="color:var(--text-tertiary)">(opcional; solo si cambia)</small></label>
                    <select name="id_cargo_destino" class="sig-select js-search">
                        <option value="">— Mantener cargo actual —</option>
                        <?php foreach ($data['cargos'] ?? [] as $c): ?>
                            <option value="<?php echo $c->id; ?>"><?php echo htmlspecialchars($c->nombre); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Fecha</label>
                    <input type="date" name="fecha" class="sig-input" value="<?php echo date('Y-m-d'); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Motivo</label>
                    <input type="text" name="motivo" class="sig-input" placeholder="Ej: necesidad del servicio, decisión de reunión de directores…">
                </div>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Observación</label>
                    <textarea name="observacion" class="sig-input" rows="2" placeholder="N° de oficio, aprobación de Directora general / coordinador, etc. (opcional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-check-lg"></i> Registrar traslado</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if (!$egresado): ?>
<!-- Modal: Procesar egreso -->
<div class="modal fade" id="modalEgreso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/egresar" method="POST" class="modal-content needs-validation" novalidate>
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-box-arrow-right"></i> Procesar egreso</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <p class="text-muted" style="font-size:13px;">
                    El expediente se conserva en el histórico de egresados (no se elimina) y seguirá
                    disponible para constancias y tiempo de servicio.
                </p>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Fecha de egreso <span class="req">*</span></label>
                    <input type="date" name="fecha_egreso" class="sig-input" required
                           min="<?php echo htmlspecialchars($e->fecha_ingreso ?? ''); ?>" max="<?php echo date('Y-m-d'); ?>">
                </div>
                <div class="sig-field mb-3">
                    <label class="sig-field__label">Motivo <span class="req">*</span></label>
                    <select name="motivo_egreso" id="eg_motivo" class="sig-input" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach ($motivos as $m): ?>
                            <option value="<?php echo htmlspecialchars($m); ?>"><?php echo htmlspecialchars($m); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Observación</label>
                    <textarea name="observacion_egreso" class="sig-input" rows="2" placeholder="N° de oficio, detalle del motivo, etc. (opcional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--danger"><i class="bi bi-box-arrow-right"></i> Confirmar egreso</button>
            </div>
        </form>
    </div>
</div>
<?php else: ?>
<!-- Modal: Reingreso -->
<div class="modal fade" id="modalReingreso" tabindex="-1">
    <div class="modal-dialog">
        <form action="<?php echo URL_ROOT; ?>/empleados/reingresar" method="POST" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-arrow-counterclockwise"></i> Reingreso de empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id_empleado" value="<?php echo $eid; ?>">
                <p class="text-muted" style="font-size:13px;">
                    Vas a reincorporar a este empleado a la nómina activa. El egreso anterior
                    queda guardado en su historial.
                </p>
                <div class="sig-field mb-2">
                    <label class="sig-field__label">Observación</label>
                    <textarea name="reingreso_observacion" class="sig-input" rows="2" placeholder="Motivo del reingreso (opcional)"></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-arrow-counterclockwise"></i> Confirmar reingreso</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
// Si se llega desde la alerta "Causa de despido" (?egreso=despido), abrir el
// modal de egreso con el motivo "Despido" preseleccionado.
document.addEventListener('DOMContentLoaded', function () {
    var params = new URLSearchParams(window.location.search);
    if (params.get('egreso') === 'despido') {
        var modalEl = document.getElementById('modalEgreso');
        var sel = document.getElementById('eg_motivo');
        if (modalEl) {
            if (sel) sel.value = 'Despido';
            new bootstrap.Modal(modalEl).show();
        }
    }
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
