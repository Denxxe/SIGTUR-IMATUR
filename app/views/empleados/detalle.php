<?php require_once '../app/views/inc/header.php';
$e   = $data['empleado'];
$eid = (int)$e->id;
$ffecha = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '—';
$val    = fn($v) => !empty($v) ? htmlspecialchars($v) : '—';
$esBool = fn($v) => ($v === true || $v === 't' || $v === '1');
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Expediente del Trabajador</div>
        <h1 class="page__title"><?php echo htmlspecialchars($e->nombre . ' ' . $e->apellido); ?></h1>
        <p class="page__subtitle">C.I. <?php echo $val($e->cedula); ?> · Expediente <?php echo $val($e->nro_expediente); ?></p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/empleados/fichaTecnica/<?php echo $eid; ?>" target="_blank" class="btn-sig btn-sig--primary">
            <i class="bi bi-file-earmark-text"></i> Ficha Técnica
        </a>
        <a href="<?php echo URL_ROOT; ?>/empleados/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<!-- Resumen de datos -->
<div class="sig-card anim-slide-up" style="margin-bottom:20px;">
    <div class="sig-card__body">
        <div class="row g-4">
            <div class="col-md-4">
                <h6 class="text-muted"><i class="bi bi-person-vcard"></i> Datos Personales</h6>
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
                    <dt>Título</dt><dd><?php echo $val($e->titulo); ?></dd>
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
seccionHijo('Carga Familiar', 'bi-people', 'modalFamiliar',
    ['Nombre y apellido', 'Cédula', 'F. Nacimiento', 'Parentesco'],
    $data['familiares'],
    function ($r) use ($eid, $ffecha, $val) {
        echo '<tr>';
        echo '<td class="cell-strong">' . $val($r->nombre_apellido) . '</td>';
        echo '<td>' . $val($r->cedula) . '</td>';
        echo '<td>' . $ffecha($r->fecha_nacimiento) . '</td>';
        echo '<td><span class="sig-badge sig-badge--info">' . $val($r->parentesco) . '</span></td>';
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
                                <a href="<?php echo URL_ROOT . htmlspecialchars($doc->archivo_url); ?>" target="_blank" style="font-size:13px;">
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
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="display:flex;justify-content:space-between;align-items:center;padding:12px 16px;">
        <h5 style="margin:0;"><i class="bi bi-file-earmark-text"></i> Constancias / Documentos generados</h5>
        <a href="<?php echo URL_ROOT; ?>/empleados/generarConstancia/<?php echo $eid; ?>" class="btn-sig btn-sig--primary btn-sig--sm" onclick="return confirm('¿Generar una nueva constancia de trabajo?')">
            <i class="bi bi-file-earmark-plus"></i> Generar constancia
        </a>
    </div>
    <table class="sig-table">
        <thead><tr><th>N° Documento</th><th>Tipo</th><th>Emisión</th><th class="col-actions">Acciones</th></tr></thead>
        <tbody>
            <?php if (empty($data['constancias'])): ?>
                <tr><td colspan="4" class="sig-table-empty">Sin constancias generadas.</td></tr>
            <?php else: foreach ($data['constancias'] as $co): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($co->numero); ?></td>
                    <td style="font-size:13px"><?php echo htmlspecialchars($co->tipo); ?></td>
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
                        <input type="date" name="fecha_nacimiento" class="sig-input"></div></div>
                </div>
                <div class="sig-field mt-3"><label class="sig-field__label">Parentesco <span class="req">*</span></label>
                    <select name="parentesco" class="sig-select" required>
                        <option value="">— Seleccione —</option>
                        <?php foreach (CargaFamiliar::PARENTESCOS as $p): ?>
                            <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
                        <?php endforeach; ?>
                    </select></div>
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

<?php require_once '../app/views/inc/footer.php'; ?>
