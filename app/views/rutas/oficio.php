<?php require_once '../app/views/inc/header.php';
$cfg = $data['config'] ?? [];
$v = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$ruta = $data['ruta'];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $ruta->id; ?>" style="color:inherit;text-decoration:none;">
                <?php echo htmlspecialchars($ruta->nombre ?? ''); ?>
            </a> · Generar Oficio
        </div>
        <h1 class="page__title">Oficio de Ruta Turística</h1>
        <p class="page__subtitle">El sistema pre-llena los datos registrados. Solo ingrese el destinatario.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $ruta->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
</div>

<?php
$faltaConfig = empty($cfg['director_nombre']['valor']) || empty($cfg['resolucion_numero']['valor']);
$faltaRuta   = empty($ruta->fecha_visita);
if ($faltaConfig || $faltaRuta):
?>
<div class="sig-card anim-slide-up" style="border-left:4px solid var(--warning-500); margin-bottom:var(--sp-6);">
    <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
        <p style="font-weight:600; color:var(--warning-700); margin-bottom:var(--sp-2);">
            <i class="bi bi-exclamation-triangle"></i> Datos incompletos para generar el oficio
        </p>
        <ul style="font-size:13px; color:var(--text-secondary); padding-left:var(--sp-5); margin:0; line-height:2;">
            <?php if ($faltaConfig): ?>
            <li>La configuración institucional (director, resolución) está incompleta.
                <a href="<?php echo URL_ROOT; ?>/config/index" style="color:var(--brand-500);">Completar configuración →</a>
            </li>
            <?php endif; ?>
            <?php if ($faltaRuta): ?>
            <li>La ruta no tiene fecha de visita registrada.
                <a href="<?php echo URL_ROOT; ?>/rutas/index" style="color:var(--brand-500);">Editar ruta →</a>
            </li>
            <?php endif; ?>
        </ul>
    </div>
</div>
<?php endif; ?>

<div class="row g-6 anim-slide-up">

    <!-- Datos del sistema (solo lectura) + form destinatario -->
    <div class="col-lg-4">
        <div class="sig-card" style="margin-bottom:var(--sp-4);">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-database" style="color:var(--teal-500);"></i> Datos del Sistema</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                <div style="display:grid; gap:var(--sp-3); font-size:13px;">
                    <div>
                        <span style="color:var(--text-tertiary); font-size:11px; font-weight:700; text-transform:uppercase;">Ruta</span>
                        <div style="font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($ruta->nombre ?? '—'); ?></div>
                    </div>
                    <div>
                        <span style="color:var(--text-tertiary); font-size:11px; font-weight:700; text-transform:uppercase;">Fecha de visita</span>
                        <div style="font-weight:600; color:var(--text-primary);">
                            <?php echo $ruta->fecha_visita ? date('d/m/Y', strtotime($ruta->fecha_visita)) : '<span style="color:var(--danger-500);">No registrada</span>'; ?>
                        </div>
                    </div>
                    <div>
                        <span style="color:var(--text-tertiary); font-size:11px; font-weight:700; text-transform:uppercase;">Hora</span>
                        <div style="font-weight:600; color:var(--text-primary);">
                            <?php echo $ruta->hora_visita ? substr($ruta->hora_visita, 0, 5) : '—'; ?>
                        </div>
                    </div>
                    <div>
                        <span style="color:var(--text-tertiary); font-size:11px; font-weight:700; text-transform:uppercase;">Departamento</span>
                        <div style="font-weight:600; color:var(--text-primary);"><?php echo htmlspecialchars($ruta->departamento_nombre ?? '—'); ?></div>
                    </div>
                    <div>
                        <span style="color:var(--text-tertiary); font-size:11px; font-weight:700; text-transform:uppercase;">Participantes inscritos</span>
                        <div style="font-weight:600; color:var(--text-primary);"><?php echo (int)$data['total_participantes']; ?></div>
                    </div>
                    <div>
                        <span style="color:var(--text-tertiary); font-size:11px; font-weight:700; text-transform:uppercase;">Firmante</span>
                        <div style="font-weight:600; color:var(--text-primary);">
                            <?php echo $v('director_nombre') . ' ' . $v('director_apellido') ?: '<span style="color:var(--danger-500);">Sin configurar</span>'; ?>
                        </div>
                    </div>
                </div>
                <hr style="margin:var(--sp-4) 0; border-color:var(--border-subtle);">
                <p style="font-size:11px; color:var(--text-tertiary);">
                    <i class="bi bi-info-circle"></i>
                    El N° de oficio se asigna al generar. Para cambiar datos del director:
                    <a href="<?php echo URL_ROOT; ?>/config/index">Configuración institucional</a>
                </p>
            </div>
        </div>

        <!-- Formulario -->
        <form action="<?php echo URL_ROOT; ?>/rutas/oficio/<?php echo $ruta->id; ?>" method="POST">
            <div class="sig-card">
                <div class="sig-card__head">
                    <div class="sig-card__title"><i class="bi bi-pencil-square" style="color:var(--brand-500);"></i> Datos del Destinatario</div>
                </div>
                <div class="sig-card__body" style="padding:var(--sp-4) var(--sp-5);">
                    <div class="sig-field">
                        <label class="sig-field__label">Nombre completo <span class="req">*</span></label>
                        <input type="text" name="destinatario_nombre" id="of_dest_nombre" class="sig-input"
                               placeholder="Ej: María González" required oninput="actualizar()">
                    </div>
                    <div class="sig-field">
                        <label class="sig-field__label">Cargo / Institución</label>
                        <input type="text" name="destinatario_cargo" id="of_dest_cargo" class="sig-input"
                               placeholder="Ej: Directora del Museo de Cumaná" oninput="actualizar()">
                    </div>
                    <div class="sig-field">
                        <label class="sig-field__label">Espacio a visitar</label>
                        <input type="text" name="espacio" id="of_espacio" class="sig-input"
                               list="list_puntos"
                               value="<?php echo htmlspecialchars($ruta->nombre ?? ''); ?>"
                               oninput="actualizar()">
                        <datalist id="list_puntos">
                            <?php foreach ($data['puntos'] ?? [] as $pt): ?>
                                <option value="<?php echo htmlspecialchars($pt->nombre); ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="sig-field">
                                <label class="sig-field__label">N° Estudiantes</label>
                                <input type="number" name="num_estudiantes" id="of_est" class="sig-input"
                                       value="<?php echo (int)$data['total_participantes']; ?>" min="0" oninput="actualizar()">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sig-field">
                                <label class="sig-field__label">N° Adultos</label>
                                <input type="number" name="num_adultos" id="of_adu" class="sig-input"
                                       value="0" min="0" oninput="actualizar()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer" style="padding:var(--sp-4);">
                    <button type="submit" class="btn-sig btn-sig--primary w-100"
                            <?php if ($faltaConfig || $faltaRuta) echo 'disabled'; ?>>
                        <i class="bi bi-file-earmark-arrow-down"></i>
                        Generar Oficio Oficial
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Vista previa -->
    <div class="col-lg-8">
        <div style="font-size:11px; color:var(--text-tertiary); margin-bottom:var(--sp-3); text-align:center;">
            <i class="bi bi-eye"></i> Vista previa — el N° de oficio se asigna al generar
        </div>
        <div style="background:#fff; border:1px solid #d1d5db; border-radius:4px; box-shadow:0 2px 14px rgba(0,0,0,.08);
                    padding:40px 48px 36px;
                    font-family:'Times New Roman',Times,Georgia,serif; font-size:11pt; color:#111; line-height:1.7;">

            <!-- Header 3 columnas -->
            <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo.png" alt="" style="height:68px; width:auto; object-fit:contain; flex-shrink:0;">
                <div style="flex:1; text-align:center; font-family:Arial,sans-serif;">
                    <p style="font-size:9.5pt; font-weight:700; text-transform:uppercase; line-height:1.7; color:#111; letter-spacing:.01em;">
                        República Bolivariana de Venezuela<br>
                        Alcaldía Bolivariana del Municipio Sucre<br>
                        Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)<br>
                        Cumaná, Estado Sucre<br>
                        RIF. G-20008498-7
                    </p>
                </div>
                <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="" style="height:68px; width:auto; object-fit:contain; flex-shrink:0;">
            </div>
            <hr style="border:none; border-top:1px solid #111; margin:6px 0 28px;">

            <p style="text-align:right; margin-bottom:20px;">Cumaná, <?php echo $data['fecha_hoy']; ?></p>

            <div style="margin-bottom:16px;">
                Oficio: <span style="font-style:italic; color:#9ca3af;">[Se asigna al generar]</span>
            </div>

            <div style="margin-bottom:20px; line-height:1.5;">
                Ciudadano:<br>
                <strong id="prev_dest_nombre"><span style="color:#9ca3af; font-style:italic;">Nombre del destinatario</span></strong><br>
                <span id="prev_dest_cargo"><span style="color:#9ca3af; font-style:italic;">Cargo o institución</span></span><br>
                Su Despacho. &ndash;
            </div>

            <div>
                <p style="text-align:justify; text-indent:2em; margin-bottom:14px;">
                    Reciba un cordial saludo de parte de quienes conformamos el equipo de
                    trabajo de <strong>(<?php echo htmlspecialchars($ruta->departamento_nombre ?? 'Turismo'); ?> de IMATUR)</strong>.
                </p>
                <p style="text-align:justify; text-indent:2em; margin-bottom:14px;">
                    Por medio del presente, se le informa que en el marco del impulso y
                    desarrollo de la <strong><?php echo htmlspecialchars($ruta->nombre ?? ''); ?></strong>,
                    el día <strong><?php echo $data['fecha_ruta_esp'] ?? '<span style="color:#9ca3af; font-style:italic;">fecha de visita</span>'; ?></strong>
                    del presente año, a las
                    <strong><?php echo $ruta->hora_visita ? substr($ruta->hora_visita, 0, 5) : '<span style="color:#9ca3af; font-style:italic;">hora</span>'; ?></strong>,
                    se estará visitando
                    <strong><span id="prev_espacio"><?php echo htmlspecialchars($ruta->nombre ?? ''); ?></span></strong>
                    con un grupo aproximado de
                    <strong><span id="prev_est"><?php echo (int)$data['total_participantes']; ?></span> estudiantes</strong>
                    y <strong><span id="prev_adu">0</span> adultos</strong>,
                    a los fines de promover el motor turismo del municipio Sucre; en tal sentido,
                    solicitamos su mayor atención en cuanto a la guiatura que ofrece el personal
                    adscrito a su institución para los visitantes a este importante patrimonio
                    de la ciudad.
                </p>
                <p style="text-align:justify; text-indent:2em; margin-bottom:28px;">
                    Sin más que hacer referencia y agradeciendo de antemano la atención que
                    sirva brindarle a la presente, se despide.
                </p>
            </div>

            <div style="text-align:center; margin-top:28px;">
                <p style="margin-bottom:50px;">Atentamente</p>
                <p style="font-weight:700; text-transform:uppercase; letter-spacing:.04em; font-size:11pt;">
                    <?php echo $v('director_nombre') . ' ' . $v('director_apellido') ?: '<span style="color:#9ca3af; font-style:italic;">[Nombre del director]</span>'; ?>
                </p>
                <p style="font-size:9.5pt; font-family:Arial,sans-serif; line-height:1.5; margin-top:3px;">
                    Resolución N&ordm; <?php echo $v('resolucion_numero') ?: '<span style="color:#9ca3af; font-style:italic;">XX</span>'; ?>
                    de fecha <?php echo $v('resolucion_fecha') ?: '<span style="color:#9ca3af; font-style:italic;">xxxxxx</span>'; ?>, publicada en<br>
                    Gaceta Municipal Extraordinaria N&ordm;
                    <?php echo $v('gaceta_numero') ?: '<span style="color:#9ca3af; font-style:italic;">XX</span>'; ?>
                    de fecha <?php echo $v('gaceta_fecha') ?: '<span style="color:#9ca3af; font-style:italic;">xxxxxx</span>'; ?>
                </p>
            </div>

            <div style="margin-top:36px; padding-top:8px; border-top:1px solid #555;
                        text-align:center; font-family:Arial,sans-serif; font-size:8pt; color:#444; line-height:1.5;">
                Calle Sucre N° 11, San Francísco, Parroquia Santa Inés, Municipio Sucre &mdash; Edo. Sucre<br>
                Telf.: <?php echo $v('telf_institucion') ?: '(0293) 431-4073'; ?>
                &nbsp;&nbsp; Correo: <?php echo $v('correo_institucion') ?: 'imatur.cumana@gmail.com'; ?>
            </div>
        </div>
    </div>

</div><!-- /row -->

<script>
function set(id, val, placeholder) {
    const el = document.getElementById(id);
    if (!el) return;
    el.innerHTML = val && val.trim() ? val.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
                                     : `<span style="color:#9ca3af;font-style:italic;">${placeholder}</span>`;
}
function actualizar() {
    set('prev_dest_nombre', document.getElementById('of_dest_nombre')?.value, 'Nombre del destinatario');
    set('prev_dest_cargo',  document.getElementById('of_dest_cargo')?.value,  'Cargo o institución');
    set('prev_espacio',     document.getElementById('of_espacio')?.value,     'espacio a visitar');
    const est = document.getElementById('of_est');
    const adu = document.getElementById('of_adu');
    document.getElementById('prev_est').textContent = est?.value || '0';
    document.getElementById('prev_adu').textContent = adu?.value || '0';
}
document.addEventListener('DOMContentLoaded', actualizar);
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
