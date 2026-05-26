<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/talleres/index" style="color:inherit; text-decoration:none;">Formación</a> · Detalle de Actividad
        </div>
        <h1 class="page__title"><?php echo htmlspecialchars($data['taller']->nombre ?? ''); ?></h1>
        <div style="display:flex; gap:var(--sp-4); margin-top:var(--sp-2); font-size:13px; color:var(--text-secondary); flex-wrap:wrap; align-items:center;">
            <span><strong>Tipo:</strong> <?php echo $data['taller']->tipo_actividad ?? 'Taller'; ?></span>
            <?php if (!empty($data['taller']->es_interna)): ?>
                <span class="sig-badge sig-badge--brand">Interna</span>
            <?php else: ?>
                <span class="sig-badge sig-badge--neutral">
                    <?php echo !empty($data['taller']->tipo_ente) ? 'Externa · ' . htmlspecialchars($data['taller']->tipo_ente) : 'Externa'; ?>
                </span>
            <?php endif; ?>
            <span><strong>Facilitador:</strong> <?php echo $data['taller']->facilitador_nombre ?? 'N/A'; ?></span>
            <span><strong>Sede:</strong> <?php echo $data['taller']->ubicacion ?? 'N/A'; ?></span>
            <span><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($data['taller']->fecha_inicio ?? 'now')); ?></span>
            <span><strong>Estado:</strong>
                <?php
                $e = $data['taller']->estado ?? '';
                $cls = ['Programado'=>'sig-badge--warning','En Curso'=>'sig-badge--brand','Finalizado'=>'sig-badge--success','Cancelado'=>'sig-badge--danger'][$e] ?? 'sig-badge--neutral';
                echo "<span class='sig-badge {$cls}'>{$e}</span>";
                ?>
            </span>
        </div>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/talleres/index" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <a href="<?php echo URL_ROOT; ?>/talleres/informe/<?php echo $data['taller']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-file-earmark-text"></i> Informe Oficial
        </a>
        <a href="<?php echo URL_ROOT; ?>/talleres/listaAsistencia/<?php echo $data['taller']->id; ?>"
           class="btn-sig btn-sig--ghost" target="_blank">
            <i class="bi bi-list-check"></i> Lista de Asistencia
        </a>
        <?php if (!in_array($data['taller']->estado ?? '', ['Finalizado', 'Cancelado'])): ?>
            <button type="button" class="btn-sig btn-sig--primary" data-bs-toggle="modal" data-bs-target="#modalInscripcion">
                <i class="bi bi-person-plus"></i> Agregar Participante
            </button>
        <?php endif; ?>
    </div>
</div>

<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head" style="display:flex; justify-content:space-between; align-items:center;">
        <div class="sig-card__title">Participantes</div>
        <div style="display:flex; align-items:center; gap:var(--sp-4);">
            <?php
            $inscritos  = count($data['participantes'] ?? []);
            $cupo       = $data['taller']->cupo_maximo ?? 0;
            $porcentaje = ($cupo > 0) ? round(($inscritos / $cupo) * 100) : 0;
            ?>
            <a href="<?php echo URL_ROOT; ?>/reportes/exportarParticipantesCsv/<?php echo $data['taller']->id; ?>" class="btn-sig btn-sig--ghost btn-sig--sm">
                <i class="bi bi-file-earmark-spreadsheet"></i> Exportar CSV
            </a>
            <div style="text-align:right;">
                <div style="font-size:12px; font-weight:700; color:var(--text-primary);">
                    <?php echo $inscritos; ?> / <?php echo $cupo; ?> <span style="color:var(--text-tertiary); font-weight:500;">(<?php echo $porcentaje; ?>%)</span>
                </div>
                <div style="height:4px; width:100px; background:var(--bg-muted); border-radius:2px; margin-top:4px; overflow:hidden;">
                    <div style="height:100%; width:<?php echo $porcentaje; ?>%; background:var(--brand-500);"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="sig-table-wrap">
        <table class="sig-table">
            <thead>
                <tr>
                    <th>Cédula / ID</th>
                    <th>Nombre Completo</th>
                    <th>Teléfono</th>
                    <th class="text-center">Asistencia</th>
                    <th>Brigadista / Docente</th>
                    <th>Observaciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($data['participantes'])): ?>
                    <tr>
                        <td colspan="6" class="sig-table-empty">No hay participantes registrados aún.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($data['participantes'] as $p): ?>
                        <?php $esLibre = empty($p->id_persona); ?>
                        <tr>
                            <td class="cell-id">
                                <?php if ($esLibre): ?>
                                    <?php echo $p->cedula_libre ? htmlspecialchars($p->cedula_libre) : '<em style="color:var(--text-tertiary);">Sin cédula</em>'; ?>
                                <?php else: ?>
                                    <?php echo htmlspecialchars($p->cedula ?? '—'); ?>
                                <?php endif; ?>
                            </td>
                            <td class="cell-strong">
                                <?php if ($esLibre): ?>
                                    <?php echo htmlspecialchars(trim(($p->nombre_libre ?? '') . ' ' . ($p->apellido_libre ?? ''))); ?>
                                    <span class="sig-badge sig-badge--neutral" style="font-size:10px; margin-left:4px;">Niño/a</span>
                                <?php else: ?>
                                    <?php echo htmlspecialchars(($p->nombre ?? '') . ' ' . ($p->apellido ?? '')); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $esLibre ? '—' : htmlspecialchars($p->telefono ?? '—'); ?></td>
                            <td class="text-center">
                                <?php if ($p->asistio): ?>
                                    <span class="sig-badge sig-badge--success">Asistió</span>
                                <?php else: ?>
                                    <span class="sig-badge sig-badge--neutral">Pendiente</span>
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-secondary);">
                                <?php if ($esLibre && !empty($p->nombre_docente)): ?>
                                    <span style="display:flex; align-items:center; gap:4px;">
                                        <i class="bi bi-person-badge" style="color:var(--brand-400);"></i>
                                        <?php echo htmlspecialchars($p->nombre_docente); ?>
                                        <?php if (!empty($p->cedula_docente)): ?>
                                            <span style="color:var(--text-tertiary);">(<?php echo htmlspecialchars($p->cedula_docente); ?>)</span>
                                        <?php endif; ?>
                                    </span>
                                <?php elseif (!$esLibre && !empty($p->es_brigadista)): ?>
                                    <span class="sig-badge sig-badge--brand" style="font-size:10px;">Brigadista</span>
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td style="font-size:12px; color:var(--text-secondary);"><?php echo htmlspecialchars($p->observaciones ?? '—'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (($data['taller']->estado ?? '') === 'Cancelado' && !empty($data['taller']->motivo_cancelacion)): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6); border-left:4px solid var(--danger-500);">
    <div class="sig-card__head">
        <div class="sig-card__title" style="color:var(--danger-600);">
            <i class="bi bi-x-circle"></i> Motivo de Cancelación
        </div>
    </div>
    <div class="sig-card__body">
        <p style="font-size:14px; color:var(--text-primary); white-space:pre-wrap; margin:0;">
            <?php echo htmlspecialchars($data['taller']->motivo_cancelacion); ?>
        </p>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($data['evidencias'])): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-6);">
    <div class="sig-card__head">
        <div class="sig-card__title"><i class="bi bi-images"></i> Evidencias</div>
        <div style="font-size:12px; color:var(--text-tertiary);"><?php echo count($data['evidencias']); ?> archivo(s)</div>
    </div>
    <div class="sig-card__body">
        <div style="display:grid; grid-template-columns:repeat(auto-fill,minmax(140px,1fr)); gap:var(--sp-3);">
            <?php foreach ($data['evidencias'] as $ev): ?>
                <?php
                $url      = URL_ROOT . '/public/uploads/talleres/' . $ev->archivo;
                $esPdf    = strtolower(pathinfo($ev->archivo, PATHINFO_EXTENSION)) === 'pdf';
                $nombre   = htmlspecialchars($ev->nombre_original);
                $fecha    = date('d/m/Y H:i', strtotime($ev->uploaded_at));
                ?>
                <a href="<?php echo $url; ?>" target="_blank" title="<?php echo $nombre; ?> — <?php echo $fecha; ?>"
                   style="display:flex; flex-direction:column; align-items:center; gap:var(--sp-1); padding:var(--sp-3);
                          background:var(--bg-muted-subtle); border-radius:8px; border:1px solid var(--border-subtle);
                          text-decoration:none; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 2px 8px rgba(0,0,0,.1)'" onmouseout="this.style.boxShadow=''">
                    <?php if ($esPdf): ?>
                        <i class="bi bi-file-earmark-pdf" style="font-size:40px; color:var(--danger-500);"></i>
                    <?php else: ?>
                        <img src="<?php echo $url; ?>" alt="<?php echo $nombre; ?>"
                             style="width:100%; height:80px; object-fit:cover; border-radius:4px;">
                    <?php endif; ?>
                    <span style="font-size:10px; color:var(--text-secondary); text-align:center; word-break:break-all; max-width:100%;">
                        <?php echo mb_strimwidth($ev->nombre_original, 0, 28, '…'); ?>
                    </span>
                    <span style="font-size:9px; color:var(--text-tertiary);"><?php echo $fecha; ?></span>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Modal agregar participante -->
<div class="modal fade" id="modalInscripcion" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form action="<?php echo URL_ROOT; ?>/talleres/inscribir" method="POST" class="modal-content" id="formInscripcion">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-person-plus"></i> Agregar Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:var(--sp-4);">
                <input type="hidden" name="id_taller" value="<?php echo $data['taller']->id; ?>">

                <!-- Toggle niño/a sin cédula (RN-F16) -->
                <div style="padding:var(--sp-3); background:var(--bg-muted-subtle); border-radius:8px;">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="insc_es_libre" name="tipo_participante_libre" value="1">
                        <label class="form-check-label" for="insc_es_libre" style="font-size:13px; cursor:pointer; user-select:none;">
                            <i class="bi bi-person-x"></i> Participante sin cédula (niño/a sin documento de identidad)
                        </label>
                    </div>
                </div>

                <!-- ── BLOQUE PERSONA CON CÉDULA ─────────────────────────── -->
                <div id="bloque_persona">

                    <!-- Búsqueda rápida -->
                    <div class="row g-3" style="margin-bottom:var(--sp-1);">
                        <div class="col-md-8">
                            <div class="sig-field" style="margin:0;">
                                <label class="sig-field__label">
                                    Cédula
                                    <span style="font-size:11px; color:var(--text-tertiary); font-weight:400; margin-left:4px;">— busca si ya está registrado, o completa para registrar</span>
                                </label>
                                <input type="text" id="insc_cedula_busqueda" name="cedula_participante"
                                       class="sig-input" placeholder="Ej: V-12345678" autocomplete="off">
                            </div>
                        </div>
                        <div class="col-md-4" style="display:flex; align-items:flex-end;">
                            <button type="button" id="btn_buscar_cedula" class="btn-sig btn-sig--ghost" style="width:100%;">
                                <i class="bi bi-search" id="ico_buscar"></i> Buscar
                            </button>
                        </div>
                    </div>

                    <!-- Resultado búsqueda -->
                    <div id="insc_status" style="display:none;"></div>

                    <!-- Datos personales -->
                    <div id="bloque_datos_persona" style="display:none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="sig-field">
                                    <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                    <input type="text" name="nombre" id="insc_nombre" class="sig-input" placeholder="Ej: Carlos">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="sig-field">
                                    <label class="sig-field__label">Apellido <span class="req">*</span></label>
                                    <input type="text" name="apellido" id="insc_apellido" class="sig-input" placeholder="Ej: González">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field">
                                    <label class="sig-field__label">Teléfono</label>
                                    <input type="text" name="telefono" id="insc_telefono" class="sig-input" placeholder="0412-1234567">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="sig-field">
                                    <label class="sig-field__label">Correo electrónico</label>
                                    <input type="email" name="correo" id="insc_correo" class="sig-input" placeholder="ejemplo@correo.com">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="sig-field">
                                    <label class="sig-field__label">Género</label>
                                    <select name="genero" id="insc_genero" class="sig-select">
                                        <option value="">—</option>
                                        <option value="M">Masculino</option>
                                        <option value="F">Femenino</option>
                                        <option value="O">Otro</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="sig-field">
                                    <label class="sig-field__label">Fecha de nacimiento <span id="insc_edad_label" style="color:var(--text-tertiary); font-weight:400;"></span></label>
                                    <input type="date" name="fecha_nacimiento" id="insc_fecha_nac" class="sig-input">
                                </div>
                            </div>
                            <div class="col-md-8" style="display:flex; align-items:flex-end; padding-bottom:4px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="insc_brigadista" name="es_brigadista" value="1">
                                    <label class="form-check-label" for="insc_brigadista" style="font-size:13px;">
                                        <i class="bi bi-shield-check"></i> Es brigadista de la institución
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ── BLOQUE LIBRE — niños/as (RN-F16) ─────────────────── -->
                <div id="bloque_libre" style="display:none;">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre <span class="req">*</span></label>
                                <input type="text" name="nombre_libre" id="insc_nombre_libre" class="sig-input" placeholder="Ej: Carlos">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="sig-field">
                                <label class="sig-field__label">Apellido</label>
                                <input type="text" name="apellido_libre" class="sig-input" placeholder="Ej: González">
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="sig-field">
                                <label class="sig-field__label">N° ID Escolar (opcional)</label>
                                <input type="text" name="cedula_libre" class="sig-input" placeholder="Si tiene identificación escolar...">
                            </div>
                        </div>
                        <div class="col-12">
                            <hr style="margin:var(--sp-1) 0;">
                            <p style="font-size:12px; font-weight:600; color:var(--brand-500); margin-bottom:var(--sp-2);">
                                <i class="bi bi-person-badge"></i> Docente acompañante (opcional)
                            </p>
                        </div>
                        <div class="col-md-7">
                            <div class="sig-field">
                                <label class="sig-field__label">Nombre del docente</label>
                                <input type="text" name="nombre_docente" class="sig-input" placeholder="Ej: María Rodríguez">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="sig-field">
                                <label class="sig-field__label">Cédula del docente</label>
                                <input type="text" name="cedula_docente" class="sig-input" placeholder="V-12345678">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sig btn-sig--ghost" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" id="btn_insc_submit" class="btn-sig btn-sig--primary" disabled>
                    <i class="bi bi-person-plus"></i> Agregar
                </button>
            </div>
        </form>
    </div>
</div>

<style>
#btn_insc_submit:disabled { opacity:.5; cursor:not-allowed; pointer-events:none; }
input[readonly].sig-input, select:disabled.sig-select {
    background: var(--bg-muted-subtle);
    color: var(--text-secondary);
    cursor: default;
}
</style>
<script>
// ── Utilidades ────────────────────────────────────────────────────────────
function calcularEdad(fechaNac) {
    if (!fechaNac) return null;
    var hoy = new Date(), nac = new Date(fechaNac);
    var a = hoy.getFullYear() - nac.getFullYear();
    if (hoy.getMonth() < nac.getMonth() || (hoy.getMonth() === nac.getMonth() && hoy.getDate() < nac.getDate())) a--;
    return a >= 0 ? a : null;
}

function checkInscripcionValid() {
    var esLibre = document.getElementById('insc_es_libre').checked;
    var btn = document.getElementById('btn_insc_submit');
    if (esLibre) {
        btn.disabled = (document.getElementById('insc_nombre_libre').value || '').trim() === '';
    } else {
        var bloqueVisible = document.getElementById('bloque_datos_persona').style.display !== 'none';
        var nombre    = (document.getElementById('insc_nombre').value   || '').trim();
        var apellido  = (document.getElementById('insc_apellido').value || '').trim();
        btn.disabled  = !bloqueVisible || !nombre || !apellido;
    }
}

// ── Reset completo del bloque persona ─────────────────────────────────────
function resetBloquePersona() {
    ['insc_nombre','insc_apellido','insc_telefono','insc_correo','insc_fecha_nac'].forEach(function(id) {
        var el = document.getElementById(id);
        if (el) { el.value = ''; el.readOnly = false; }
    });
    var gen = document.getElementById('insc_genero');
    gen.value = ''; gen.disabled = false;
    document.getElementById('insc_brigadista').checked = false;
    document.getElementById('insc_status').style.display = 'none';
    document.getElementById('bloque_datos_persona').style.display = 'none';
    document.getElementById('insc_edad_label').textContent = '';
    checkInscripcionValid();
}

function setPersonaReadonly(readonly) {
    ['insc_nombre','insc_apellido','insc_telefono','insc_correo','insc_fecha_nac'].forEach(function(id) {
        document.getElementById(id).readOnly = readonly;
    });
    document.getElementById('insc_genero').disabled = readonly;
}

function mostrarStatus(tipo, html) {
    var s = document.getElementById('insc_status');
    var estilos = {
        ok:   'background:rgba(34,197,94,.1);  border-left:3px solid var(--success-600); color:var(--success-700);',
        warn: 'background:rgba(234,179,8,.1);  border-left:3px solid #ca8a04; color:#92400e;',
        err:  'background:rgba(239,68,68,.1);  border-left:3px solid var(--danger-600);  color:var(--danger-700);'
    };
    s.style.cssText = 'padding:var(--sp-2) var(--sp-3); border-radius:6px; font-size:13px; ' + (estilos[tipo] || '');
    s.innerHTML = html;
    s.style.display = 'block';
}

// ── Búsqueda por cédula (AJAX) ────────────────────────────────────────────
document.getElementById('btn_buscar_cedula').addEventListener('click', function() {
    var cedula = (document.getElementById('insc_cedula_busqueda').value || '').trim();
    var btn = this;
    var ico = document.getElementById('ico_buscar');

    // Sin cédula: mostrar formulario en blanco para registro manual
    if (!cedula) {
        resetBloquePersona();
        document.getElementById('bloque_datos_persona').style.display = 'block';
        mostrarStatus('warn', '<i class="bi bi-pencil"></i> Complete los datos para registrar un nuevo participante.');
        checkInscripcionValid();
        return;
    }

    btn.disabled = true; ico.className = 'bi bi-hourglass-split';

    fetch('<?php echo URL_ROOT; ?>/talleres/buscarPersona?cedula=' + encodeURIComponent(cedula))
        .then(function(r) { return r.json(); })
        .then(function(res) {
            document.getElementById('bloque_datos_persona').style.display = 'block';

            if (res.found) {
                var p = res.persona;
                document.getElementById('insc_nombre').value    = p.nombre;
                document.getElementById('insc_apellido').value  = p.apellido;
                document.getElementById('insc_telefono').value  = p.telefono || '';
                document.getElementById('insc_correo').value    = p.correo   || '';
                document.getElementById('insc_genero').value    = p.genero   || '';
                document.getElementById('insc_fecha_nac').value = p.fecha_nacimiento || '';
                setPersonaReadonly(true);

                var edad = calcularEdad(p.fecha_nacimiento);
                var edadTxt = edad !== null ? '· ' + edad + ' años' : '';
                document.getElementById('insc_edad_label').textContent = edadTxt;

                mostrarStatus('ok', '<i class="bi bi-check-circle"></i> <strong>Persona encontrada</strong> ' + edadTxt + ' — datos cargados automáticamente.');
            } else {
                setPersonaReadonly(false);
                mostrarStatus('warn', '<i class="bi bi-person-plus"></i> Persona no registrada — complete los datos para crear el registro.');
            }
            checkInscripcionValid();
        })
        .catch(function() {
            mostrarStatus('err', '<i class="bi bi-exclamation-circle"></i> Error al consultar. Intente nuevamente.');
        })
        .finally(function() { btn.disabled = false; ico.className = 'bi bi-search'; });
});

// Enter en campo cédula también dispara la búsqueda
document.getElementById('insc_cedula_busqueda').addEventListener('keydown', function(e) {
    if (e.key === 'Enter') { e.preventDefault(); document.getElementById('btn_buscar_cedula').click(); }
});

// Toggle libre / persona
document.getElementById('insc_es_libre').addEventListener('change', function() {
    var esLibre = this.checked;
    document.getElementById('bloque_persona').style.display = esLibre ? 'none' : 'block';
    document.getElementById('bloque_libre').style.display   = esLibre ? 'block' : 'none';
    if (!esLibre) {
        document.getElementById('insc_cedula_busqueda').value = '';
        resetBloquePersona();
    }
    checkInscripcionValid();
});

// Fecha → mostrar edad calculada en tiempo real
document.getElementById('insc_fecha_nac').addEventListener('change', function() {
    var edad = calcularEdad(this.value);
    document.getElementById('insc_edad_label').textContent = edad !== null ? '· ' + edad + ' años' : '';
});

// Habilitar submit cuando cambien campos requeridos
document.getElementById('insc_nombre').addEventListener('input', checkInscripcionValid);
document.getElementById('insc_apellido').addEventListener('input', checkInscripcionValid);
document.getElementById('insc_nombre_libre').addEventListener('input', checkInscripcionValid);

// Reset al abrir el modal
document.getElementById('modalInscripcion').addEventListener('show.bs.modal', function() {
    document.getElementById('insc_es_libre').checked           = false;
    document.getElementById('bloque_persona').style.display    = 'block';
    document.getElementById('bloque_libre').style.display      = 'none';
    document.getElementById('insc_cedula_busqueda').value      = '';
    resetBloquePersona();
});
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
