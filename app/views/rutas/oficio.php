<?php require_once '../app/views/inc/header.php'; ?>

<style>
/* ── Carta papel ─────────────────────────────────────────────────────── */
.carta-wrap {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    box-shadow: 0 2px 14px rgba(0,0,0,.09);
    padding: 40px 48px 36px;
    font-family: 'Times New Roman', Times, Georgia, serif;
    font-size: 11.5pt;
    color: #111;
    line-height: 1.7;
    max-width: 760px;
}

/* Encabezado 3 columnas */
.carta-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-bottom: 6px;
}
.carta-header img {
    height: 72px;
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
}
.carta-header .ch-text {
    flex: 1;
    text-align: center;
    font-family: Arial, sans-serif;
}
.carta-header .ch-text p {
    font-size: 9.5pt;
    font-weight: 700;
    text-transform: uppercase;
    line-height: 1.7;
    color: #111;
    letter-spacing: 0.01em;
}
.carta-divider {
    border: none;
    border-top: 1px solid #111;
    margin: 8px 0 28px;
}

/* Cuerpo */
.carta-fecha { text-align: right; margin-bottom: 22px; }
.carta-oficio { margin-bottom: 18px; }
.carta-destinatario { margin-bottom: 22px; line-height: 1.5; }
.carta-cuerpo p {
    text-align: justify;
    text-indent: 2em;
    margin-bottom: 14px;
}
.carta-despedida { margin-top: 28px; }
.carta-firma {
    text-align: center;
    margin-top: 60px;
    margin-bottom: 0;
}
.carta-firma .firma-nombre {
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.04em;
    font-size: 11pt;
}
.carta-firma .firma-resolucion {
    font-size: 9.5pt;
    line-height: 1.5;
    margin-top: 3px;
    font-family: Arial, sans-serif;
}

/* Pie de página */
.carta-footer {
    margin-top: 40px;
    padding-top: 8px;
    border-top: 1px solid #555;
    text-align: center;
    font-family: Arial, sans-serif;
    font-size: 8pt;
    color: #444;
    line-height: 1.5;
}

/* Campo vacío (resalte mientras edita) */
.campo { font-style: italic; color: #9ca3af; }

/* ── PRINT ─────────────────────────────────────────────────────────────── */
@media print {
    aside, header, nav,
    .page__head,
    .oficio-form-col,
    .d-print-none { display: none !important; }

    body, .main-content, .page-padding {
        background: white !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    .oficio-preview-col {
        width: 100% !important;
        max-width: 100% !important;
        flex: 0 0 100% !important;
        padding: 0 !important;
    }

    .carta-wrap {
        box-shadow: none !important;
        border: none !important;
        border-radius: 0 !important;
        padding: 0 !important;
        max-width: 100% !important;
        font-size: 11pt;
    }

    @page {
        size: A4 portrait;
        margin: 2cm 2.5cm;
    }
}
</style>

<!-- ── Page head (pantalla) ── -->
<div class="page__head anim-slide-up d-print-none">
    <div class="page__title-block">
        <div class="page__eyebrow">
            <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $data['ruta']->id; ?>" style="color:inherit;text-decoration:none;">
                <?php echo htmlspecialchars($data['ruta']->nombre ?? ''); ?>
            </a> · Generar Oficio
        </div>
        <h1 class="page__title">Oficio de Ruta Turística</h1>
        <p class="page__subtitle">Complete los datos faltantes y genere el oficio oficial en PDF.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $data['ruta']->id; ?>" class="btn-sig btn-sig--ghost">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <button onclick="window.print()" class="btn-sig btn-sig--primary">
            <i class="bi bi-printer"></i> Imprimir / Guardar PDF
        </button>
    </div>
</div>

<!-- ── Layout dos columnas ── -->
<div class="row g-6 anim-slide-up">

    <!-- FORMULARIO -->
    <div class="col-lg-4 oficio-form-col d-print-none">
        <div class="sig-card" style="position:sticky; top:20px;">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pencil-square" style="color:var(--brand-500);"></i> Datos del Oficio</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-5);">

                <p style="font-size:12px; color:var(--text-secondary); margin-bottom:var(--sp-4);">
                    Los campos marcados actualizan la vista previa en tiempo real.
                </p>

                <!-- Datos básicos -->
                <div class="sig-field">
                    <label class="sig-field__label">N° de Oficio</label>
                    <input type="text" id="of_num" class="sig-input" placeholder="Ej: 001/2026" oninput="actualizar()">
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Departamento emisor</label>
                    <select id="of_depto" class="sig-select" onchange="actualizar()">
                        <option value="Turismo">Turismo</option>
                        <option value="Formación">Formación</option>
                        <option value="Administración">Administración</option>
                        <option value="Dirección">Dirección</option>
                    </select>
                </div>

                <hr style="margin:var(--sp-4) 0; border-color:var(--border-subtle);">

                <!-- Destinatario -->
                <p style="font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.05em; margin-bottom:var(--sp-3);">Destinatario</p>
                <div class="sig-field">
                    <label class="sig-field__label">Nombre completo <span class="req">*</span></label>
                    <input type="text" id="of_dest_nombre" class="sig-input" placeholder="Ej: María González" oninput="actualizar()">
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Cargo / Título</label>
                    <input type="text" id="of_dest_cargo" class="sig-input" placeholder="Ej: Directora del Museo de Cumaná" oninput="actualizar()">
                </div>

                <hr style="margin:var(--sp-4) 0; border-color:var(--border-subtle);">

                <!-- Visita -->
                <p style="font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.05em; margin-bottom:var(--sp-3);">Datos de la Visita</p>
                <div class="sig-field">
                    <label class="sig-field__label">Espacio a visitar <span class="req">*</span></label>
                    <input type="text" id="of_destino" class="sig-input" list="list_puntos"
                        value="<?php echo htmlspecialchars($data['ruta']->nombre ?? ''); ?>"
                        oninput="actualizar()">
                    <datalist id="list_puntos">
                        <?php foreach ($data['puntos'] ?? [] as $pt): ?>
                            <option value="<?php echo htmlspecialchars($pt->nombre); ?>">
                        <?php endforeach; ?>
                        <option value="<?php echo htmlspecialchars($data['ruta']->nombre ?? ''); ?>">
                    </datalist>
                </div>
                <div class="row g-3">
                    <div class="col-7">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha de visita <span class="req">*</span></label>
                            <input type="date" id="of_fecha" class="sig-input" oninput="actualizar()">
                        </div>
                    </div>
                    <div class="col-5">
                        <div class="sig-field">
                            <label class="sig-field__label">Hora</label>
                            <input type="time" id="of_hora" class="sig-input" oninput="actualizar()">
                        </div>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">N° Estudiantes</label>
                            <input type="number" id="of_est" class="sig-input" placeholder="0" min="0" value="0" oninput="actualizar()">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">N° Adultos</label>
                            <input type="number" id="of_adu" class="sig-input" placeholder="0" min="0" value="0" oninput="actualizar()">
                        </div>
                    </div>
                </div>

                <hr style="margin:var(--sp-4) 0; border-color:var(--border-subtle);">

                <!-- Firma -->
                <p style="font-size:11px; font-weight:700; color:var(--text-tertiary); text-transform:uppercase; letter-spacing:.05em; margin-bottom:var(--sp-3);">Firma</p>
                <div class="sig-field">
                    <label class="sig-field__label">Nombre del firmante</label>
                    <input type="text" id="of_firmante" class="sig-input" placeholder="Nombre y Apellido" oninput="actualizar()">
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Resolución Nº</label>
                    <input type="text" id="of_res_num" class="sig-input" placeholder="Ej: 15" oninput="actualizar()">
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">Fecha resolución</label>
                            <input type="text" id="of_res_fecha" class="sig-input" placeholder="dd/mm/aaaa" oninput="actualizar()">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="sig-field">
                            <label class="sig-field__label">N° Gaceta</label>
                            <input type="text" id="of_gaceta_num" class="sig-input" placeholder="Ej: 08" oninput="actualizar()">
                        </div>
                    </div>
                </div>
                <div class="sig-field">
                    <label class="sig-field__label">Fecha Gaceta</label>
                    <input type="text" id="of_gaceta_fecha" class="sig-input" placeholder="dd/mm/aaaa" oninput="actualizar()">
                </div>

            </div>
        </div>
    </div>

    <!-- VISTA PREVIA -->
    <div class="col-lg-8 oficio-preview-col">
        <div class="carta-wrap" id="carta-preview">

            <!-- Encabezado institucional -->
            <div class="carta-header">
                <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo.png" alt="Alcaldía de Cumaná">
                <div class="ch-text">
                    <p>
                        República Bolivariana de Venezuela<br>
                        Alcaldía Bolivariana del Municipio Sucre<br>
                        Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)<br>
                        Cumaná, Estado Sucre<br>
                        RIF. G-20008498-7
                    </p>
                </div>
                <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="IMATUR">
            </div>
            <hr class="carta-divider">

            <!-- Fecha y número de oficio -->
            <p class="carta-fecha">
                Cumaná, <span id="prev_fecha_hoy"><?php echo $data['fecha_hoy']; ?></span>
            </p>

            <div class="carta-oficio">
                Oficio: <span id="prev_num"><span class="campo">xxx/xxxx</span></span>
            </div>

            <!-- Destinatario -->
            <div class="carta-destinatario">
                Ciudadano:<br>
                <strong><span id="prev_dest_nombre"><span class="campo">Nombre del destinatario</span></span></strong><br>
                <span id="prev_dest_cargo"><span class="campo">Cargo o título</span></span><br>
                Su Despacho. &ndash;
            </div>

            <!-- Cuerpo -->
            <div class="carta-cuerpo">
                <p>
                    Reciba un cordial saludo de parte de quienes conformamos el equipo de
                    trabajo de <strong>(<span id="prev_depto">Turismo</span> de IMATUR)</strong>.
                </p>

                <p>
                    Por medio del presente, se le informa que en el marco del impulso y
                    desarrollo de la <strong><?php echo htmlspecialchars($data['ruta']->nombre ?? 'Ruta Turística'); ?></strong>,
                    el día <span id="prev_fecha_vis"><span class="campo">xx de xxxx</span></span>
                    del presente año, a las <span id="prev_hora"><span class="campo">xx:xx am</span></span>,
                    se estará visitando <strong><span id="prev_destino"><?php echo htmlspecialchars($data['ruta']->nombre ?? 'espacio a visitar'); ?></span></strong>
                    con un grupo aproximado de <strong><span id="prev_est">0</span> estudiantes</strong>
                    y <strong><span id="prev_adu">0</span> adultos</strong>, a los fines de
                    promover el motor turismo del municipio Sucre; en tal sentido, solicitamos
                    su mayor atención en cuanto a la guiatura que ofrece el personal adscrito a
                    su institución para los visitantes a este importante patrimonio de la ciudad.
                </p>

                <p class="carta-despedida">
                    Sin más que hacer referencia y agradeciendo de antemano la atención que
                    sirva brindarle a la presente, se despide.
                </p>
            </div>

            <!-- Firma -->
            <div class="carta-firma">
                <p style="margin-bottom:50px;">Atentamente</p>
                <p class="firma-nombre">
                    <span id="prev_firmante"><span class="campo">Nombre del Firmante</span></span>
                </p>
                <p class="firma-resolucion">
                    Resolución N&ordm; <span id="prev_res_num"><span class="campo">xx</span></span>
                    de fecha <span id="prev_res_fecha"><span class="campo">xxxxxx</span></span>, publicada en<br>
                    Gaceta Municipal Extraordinaria N&ordm;
                    <span id="prev_gaceta_num"><span class="campo">xx</span></span>
                    de fecha <span id="prev_gaceta_fecha"><span class="campo">xxxxxx</span></span>
                </p>
            </div>

            <!-- Pie -->
            <div class="carta-footer">
                Calle Sucre N° 11, San Francísco, Parroquia Santa Inés, Municipio Sucre&mdash; Edo. Sucre<br>
                Telf.: (0293) 431-4073 &nbsp;&nbsp; Correo electrónico: imatur.cumana@gmail.com
            </div>

        </div><!-- /carta-wrap -->
    </div>

</div><!-- /row -->

<script>
const MESES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
const DIAS  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];

function fechaEsp(iso) {
    if (!iso) return null;
    const [y, m, d] = iso.split('-').map(Number);
    const fecha = new Date(y, m - 1, d);
    return `${DIAS[fecha.getDay()]} ${d} de ${MESES[m - 1]}`;
}

function horaEsp(t) {
    if (!t) return null;
    const [h, min] = t.split(':').map(Number);
    const sufijo = h < 12 ? 'am' : 'pm';
    const h12 = h % 12 || 12;
    return min === 0 ? `${h12}:00 ${sufijo}` : `${h12}:${String(min).padStart(2,'0')} ${sufijo}`;
}

function set(id, val, placeholder) {
    const el = document.getElementById(id);
    if (!el) return;
    if (val && val.trim() !== '') {
        el.innerHTML = escHtml(val);
    } else {
        el.innerHTML = `<span class="campo">${placeholder}</span>`;
    }
}

function escHtml(str) {
    return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function actualizar() {
    set('prev_num',         g('of_num'),          'xxx/xxxx');
    set('prev_dest_nombre', g('of_dest_nombre'),  'Nombre del destinatario');
    set('prev_dest_cargo',  g('of_dest_cargo'),   'Cargo o título');
    set('prev_depto',       g('of_depto'),        'Turismo');
    set('prev_destino',     g('of_destino'),      'espacio a visitar');
    set('prev_firmante',    g('of_firmante'),     'Nombre del Firmante');
    set('prev_res_num',     g('of_res_num'),      'xx');
    set('prev_res_fecha',   g('of_res_fecha'),    'xxxxxx');
    set('prev_gaceta_num',  g('of_gaceta_num'),   'xx');
    set('prev_gaceta_fecha',g('of_gaceta_fecha'), 'xxxxxx');

    // Fecha visita → formato español
    const fv = fechaEsp(g('of_fecha'));
    set('prev_fecha_vis', fv, 'xx de xxxx');

    // Hora → formato 12h
    const hv = horaEsp(g('of_hora'));
    set('prev_hora', hv, 'xx:xx am');

    // Estudiantes / adultos
    const est = document.getElementById('of_est');
    const adu = document.getElementById('of_adu');
    document.getElementById('prev_est').textContent = est ? (est.value || '0') : '0';
    document.getElementById('prev_adu').textContent = adu ? (adu.value || '0') : '0';
}

function g(id) {
    const el = document.getElementById(id);
    return el ? el.value.trim() : '';
}

// Inicializar con valores por defecto al cargar
document.addEventListener('DOMContentLoaded', actualizar);
</script>

<?php require_once '../app/views/inc/footer.php'; ?>
