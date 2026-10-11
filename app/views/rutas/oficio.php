<?php require_once '../app/views/inc/header.php';
$cfg = $data['config'] ?? [];
$v = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$ruta = $data['ruta'];
$ejecucion = $data['ejecucion'] ?? null;   // la SALIDA (mig. 078)
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
$faltaConfig   = empty($cfg['director_nombre']['valor']) || empty($cfg['resolucion_numero']['valor']);
$faltaRuta     = empty($ejecucion->fecha);
$oficiosPrevios = $data['oficiosPrevios'] ?? [];

if (!empty($oficiosPrevios)):
?>
<div class="sig-card anim-slide-up" style="border-left:4px solid #6366F1; margin-bottom:var(--sp-5);">
    <div class="sig-card__body u-p-sp-4-sp-5">
        <p class="u-fw-600 u-mb-sp-3" style="color:#4338CA;">
            <i class="bi bi-info-circle-fill"></i>
            Ya se ha(n) generado <?php echo count($oficiosPrevios); ?> oficio(s) para esta ruta:
        </p>
        <div class="u-fwr-wrap u-gap-sp-2" style="display:flex;">
            <?php foreach ($oficiosPrevios as $op): ?>
            <span class="u-ai-center u-gap-6px u-fs-12px u-fw-600" style="display:inline-flex; padding:4px 12px; background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.2); border-radius:20px; color:#4338CA;">
                <i class="bi bi-envelope-paper"></i>
                <?php echo htmlspecialchars($op->numero); ?>
                <span class="u-fw-400 u-c-x6366F1">— <?php echo htmlspecialchars($op->destinatario_nombre); ?></span>
                <span style="color:#a5b4fc;">(<?php echo $op->fecha ? date('d/m/Y', strtotime($op->fecha)) : ''; ?>)</span>
            </span>
            <?php endforeach; ?>
        </div>
        <p class="u-fs-12px u-c-text-secondary u-mt-sp-2" style="margin-bottom:0;">
            Puede generar uno nuevo si la ruta se ejecutará en otra fecha o si el destinatario es diferente.
        </p>
    </div>
</div>
<?php endif; ?>

<?php if ($faltaConfig || $faltaRuta): ?>
<div class="sig-card anim-slide-up" style="border-left:4px solid var(--warning-500); margin-bottom:var(--sp-6);">
    <div class="sig-card__body u-p-sp-4-sp-5">
        <p class="u-fw-600 u-mb-sp-2" style="color:var(--warning-700);">
            <i class="bi bi-exclamation-triangle"></i> Datos incompletos para generar el oficio
        </p>
        <ul class="u-fs-13px u-c-text-secondary u-pl-sp-5 u-m-0" style="line-height:2;">
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
                <div class="sig-card__title"><i class="bi bi-database u-c-teal-500"></i> Datos del Sistema</div>
            </div>
            <div class="sig-card__body u-p-sp-4-sp-5">
                <div class="u-gap-sp-3 u-fs-13px" style="display:grid;">
                    <div>
                        <span class="u-c-text-tertiary u-fs-11px u-fw-700 u-tt-uppercase">Ruta</span>
                        <div class="u-fw-600 u-c-text-primary"><?php echo htmlspecialchars($ruta->nombre ?? '—'); ?></div>
                    </div>
                    <div>
                        <span class="u-c-text-tertiary u-fs-11px u-fw-700 u-tt-uppercase">Fecha de visita</span>
                        <div class="u-fw-600 u-c-text-primary">
                            <?php echo $ejecucion->fecha ? date('d/m/Y', strtotime($ejecucion->fecha)) : '<span class="u-c-danger-500">No registrada</span>'; ?>
                        </div>
                    </div>
                    <div>
                        <span class="u-c-text-tertiary u-fs-11px u-fw-700 u-tt-uppercase">Hora</span>
                        <div class="u-fw-600 u-c-text-primary">
                            <?php echo !empty($ejecucion->hora) ? substr($ejecucion->hora, 0, 5) : '—'; ?>
                        </div>
                    </div>
                    <div>
                        <span class="u-c-text-tertiary u-fs-11px u-fw-700 u-tt-uppercase">Departamento</span>
                        <div class="u-fw-600 u-c-text-primary"><?php echo htmlspecialchars($ruta->departamento_nombre ?? '—'); ?></div>
                    </div>
                    <div>
                        <span class="u-c-text-tertiary u-fs-11px u-fw-700 u-tt-uppercase">Participantes inscritos</span>
                        <div class="u-fw-600 u-c-text-primary"><?php echo (int)$data['total_participantes']; ?></div>
                    </div>
                    <div>
                        <span class="u-c-text-tertiary u-fs-11px u-fw-700 u-tt-uppercase">Firmante</span>
                        <div class="u-fw-600 u-c-text-primary">
                            <?php echo $v('director_nombre') . ' ' . $v('director_apellido') ?: '<span class="u-c-danger-500">Sin configurar</span>'; ?>
                        </div>
                    </div>
                </div>
                <hr class="u-m-sp-4-0" style="border-color:var(--border-subtle);">
                <p class="u-fs-11px u-c-text-tertiary">
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
                    <div class="sig-card__title"><i class="bi bi-pencil-square u-c-brand-500"></i> Datos del Destinatario</div>
                </div>
                <div class="sig-card__body u-p-sp-4-sp-5">
                    <div class="sig-field">
                        <label class="sig-field__label" for="of_dest_nombre">Nombre completo <span class="req">*</span></label>
                        <input type="text" name="destinatario_nombre" id="of_dest_nombre" class="sig-input"
                               placeholder="Ej: María González" required oninput="actualizar()">
                    </div>
                    <div class="sig-field">
                        <label class="sig-field__label" for="of_dest_cargo">Cargo / Institución</label>
                        <input type="text" name="destinatario_cargo" id="of_dest_cargo" class="sig-input"
                               placeholder="Ej: Directora del Museo de Cumaná" oninput="actualizar()">
                    </div>
                    <div class="sig-field">
                        <label class="sig-field__label" for="of_espacio">Espacio a visitar</label>
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
                                <label class="sig-field__label" for="of_est">N° Estudiantes</label>
                                <input type="number" name="num_estudiantes" id="of_est" class="sig-input"
                                       value="<?php echo (int)$data['total_participantes']; ?>" min="0" oninput="actualizar()">
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="sig-field">
                                <label class="sig-field__label" for="of_adu">N° Adultos</label>
                                <input type="number" name="num_adultos" id="of_adu" class="sig-input"
                                       value="0" min="0" oninput="actualizar()">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="sig-card__footer u-p-sp-4">
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
        <div class="u-fs-11px u-c-text-tertiary u-mb-sp-3 u-ta-center">
            <i class="bi bi-eye"></i> Vista previa — el N° de oficio se asigna al generar
        </div>
        <div style="background:#fff; border:1px solid #d1d5db; border-radius:4px; box-shadow:0 2px 14px rgba(0,0,0,.08);
                    padding:40px 48px 36px;
                    font-family:'Times New Roman',Times,Georgia,serif; font-size:11pt; color:#111; line-height:1.7;">

            <?php require '../app/views/inc/membrete.php'; ?>

            <p class="u-ta-right u-mb-20px">Cumaná, <?php echo $data['fecha_hoy']; ?></p>

            <div style="margin-bottom:16px;">
                Oficio: <span class="u-fst-italic u-c-x9ca3af">[Se asigna al generar]</span>
            </div>

            <div class="u-mb-20px u-lh-1_5">
                Ciudadano:<br>
                <strong id="prev_dest_nombre"><span class="u-c-x9ca3af u-fst-italic">Nombre del destinatario</span></strong><br>
                <span id="prev_dest_cargo"><span class="u-c-x9ca3af u-fst-italic">Cargo o institución</span></span><br>
                Su Despacho. &ndash;
            </div>

            <div>
                <p class="u-ta-justify u-textin-2em u-mb-14px">
                    Reciba un cordial saludo de parte de quienes conformamos el equipo de
                    trabajo de <strong>(<?php echo htmlspecialchars($ruta->departamento_nombre ?? 'Turismo'); ?> de IMATUR)</strong>.
                </p>
                <p class="u-ta-justify u-textin-2em u-mb-14px">
                    Por medio del presente, se le informa que en el marco del impulso y
                    desarrollo de la <strong><?php echo htmlspecialchars($ruta->nombre ?? ''); ?></strong>,
                    el día <strong><?php echo $data['fecha_ruta_esp'] ?? '<span class="u-c-x9ca3af u-fst-italic">fecha de visita</span>'; ?></strong>
                    del presente año, a las
                    <strong><?php echo !empty($ejecucion->hora) ? substr($ejecucion->hora, 0, 5) : '<span class="u-c-x9ca3af u-fst-italic">hora</span>'; ?></strong>,
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
                <p class="u-ta-justify u-textin-2em" style="margin-bottom:28px;">
                    Sin más que hacer referencia y agradeciendo de antemano la atención que
                    sirva brindarle a la presente, se despide.
                </p>
            </div>

            <div class="u-ta-center" style="margin-top:28px;">
                <p style="margin-bottom:50px;">Atentamente</p>
                <p class="u-fw-700 u-tt-uppercase u-ls-_04em" style="font-size:11pt;">
                    <?php echo $v('director_nombre') . ' ' . $v('director_apellido') ?: '<span class="u-c-x9ca3af u-fst-italic">[Nombre del director]</span>'; ?>
                </p>
                <p class="u-lh-1_5 u-mt-3px" style="font-size:9.5pt; font-family:Arial,sans-serif;">
                    Resolución N&ordm; <?php echo $v('resolucion_numero') ?: '<span class="u-c-x9ca3af u-fst-italic">XX</span>'; ?>
                    de fecha <?php echo $v('resolucion_fecha') ?: '<span class="u-c-x9ca3af u-fst-italic">xxxxxx</span>'; ?>, publicada en<br>
                    Gaceta Municipal Extraordinaria N&ordm;
                    <?php echo $v('gaceta_numero') ?: '<span class="u-c-x9ca3af u-fst-italic">XX</span>'; ?>
                    de fecha <?php echo $v('gaceta_fecha') ?: '<span class="u-c-x9ca3af u-fst-italic">xxxxxx</span>'; ?>
                </p>
            </div>

            <div class="u-pt-8px u-ta-center u-lh-1_5" style="margin-top:36px; border-top:1px solid #555; font-family:Arial,sans-serif; font-size:8pt; color:#444;">
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
