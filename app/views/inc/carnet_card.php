<?php
/**
 * Partial imprimible del CARNET institucional — VERTICAL, UNA SOLA CARA
 * (CR80 retrato, 54 × 85.6 mm). Compartido por empleados y pasantes.
 * Documento standalone (no usa header.php).
 *
 * Reproduce el carnet físico vigente de IMATUR (modelo entregado por el
 * cliente el 2026-08-04): logo de la Alcaldía arriba a la izquierda,
 * palabra IMATUR grande a la derecha con el RIF debajo, unidad de
 * adscripción en vertical sobre el margen izquierdo, foto circular con
 * aro dorado al centro, apellidos/nombres y cédula alineados a la
 * derecha, y bloque de contacto institucional al pie.
 *
 * Espera $carnet (array):
 *   tipo        'TRABAJADOR' | 'PASANTE'
 *   subtipo     'FIJO' | 'CONTRATADO' | ''   (solo trabajadores)
 *   nombre      nombres  (línea 2 del bloque de identidad)
 *   apellido    apellidos (línea 1, la que va arriba)
 *   cedula      C.I. (solo dígitos; aquí se formatea con puntos)
 *   id_persona  para la URL de la foto (/descarga/foto/{id})
 *   vertical    texto lateral: departamento (trabajador) / institución (pasante)
 *
 * Los datos institucionales (RIF, teléfono, correo, dirección, lema) se
 * leen de `configuracion_sistema` — editables en /config, nunca fijos aquí.
 *
 * FONDO: el arte original del carnet (degradado + marca de agua + foto de
 * Cumaná al pie) todavía no lo tenemos. Mientras tanto se aproxima con
 * CSS. Para incorporarlo cuando llegue, basta con dejar el archivo en
 * public/assets/images/carnet_fondo.png — se detecta solo y sustituye el
 * degradado (ver $fondo más abajo).
 */
$cv = fn($x) => htmlspecialchars((string)($x ?? ''));

// ── Datos institucionales (fuente única: configuracion_sistema) ──────
$rif       = ConfigSistema::rif();
$telefono  = ConfigSistema::get('telf_institucion');
$correo    = ConfigSistema::get('correo_institucion');
$direccion = ConfigSistema::get('direccion_institucion');
$lema      = ConfigSistema::get('lema_institucion');

$logoAlcaldia = URL_ROOT . '/public/assets/images/Logo.png';
$fotoUrl      = URL_ROOT . '/descarga/foto/' . (int)($carnet['id_persona'] ?? 0);
$esPasante    = ($carnet['tipo'] ?? '') === 'PASANTE';

// Fondo institucional opcional: si el arte existe, se usa; si no, degradado CSS.
$fondoFs  = dirname(__DIR__, 3) . '/public/assets/images/carnet_fondo.png';
$fondo    = is_file($fondoFs) ? URL_ROOT . '/public/assets/images/carnet_fondo.png' : null;

// Cédula con separador de miles: 28134290 -> 28.134.290
// Se agrupa sobre la CADENA, no con number_format((int)…): el cast a entero
// descartaría los ceros a la izquierda ("00123456" -> "123.456").
$cedRaw = preg_replace('/\D/', '', (string)($carnet['cedula'] ?? ''));
$cedula = $cedRaw !== ''
    ? strrev(implode('.', str_split(strrev($cedRaw), 3)))
    : '—';

// El texto vertical se parte para dar el mismo ritmo tipográfico del
// carnet físico: la última palabra larga va grande y el resto pequeño.
$vertical = trim((string)($carnet['vertical'] ?? ''));

// Paleta tomada del carnet físico
$azul      = '#1B5FAE';  // azul del texto principal
$azulOsc   = '#123F78';  // azul profundo (IMATUR)
$gris      = '#6E7B87';  // gris de las etiquetas
$dorado    = '#D9A441';  // aro de la foto
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carnet — <?php echo $cv(trim(($carnet['nombre'] ?? '') . ' ' . ($carnet['apellido'] ?? ''))); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, Helvetica, sans-serif; background:#e9edf2; color:#1f2733;
         -webkit-print-color-adjust:exact; print-color-adjust:exact; }

  .toolbar { text-align:center; padding:16px; }
  .btn-print { padding:9px 20px; background:<?php echo $azulOsc; ?>; color:#fff; border:none;
               border-radius:6px; font-size:14px; cursor:pointer; }
  .hint { font-size:12px; color:#5b6470; margin-top:8px; }

  .sheet { display:flex; justify-content:center; padding:0 0 30px; }

  /* ── Tarjeta CR80 vertical ─────────────────────────────────────── */
  .card {
    width:54mm; height:85.6mm; overflow:hidden; position:relative;
    box-shadow:0 3px 14px rgba(0,0,0,.2); border:1px solid #cfd6df;
<?php if ($fondo): ?>
    background:#fff url('<?php echo $fondo; ?>') center/cover no-repeat;
<?php else: ?>
    /* Aproximación del fondo mientras no tengamos el arte original */
    background:
      radial-gradient(ellipse at 62% 30%, rgba(255,255,255,.95) 0%, rgba(255,255,255,0) 55%),
      linear-gradient(160deg, #eef2f7 0%, #dce6f2 42%, #c9d9ec 72%, #b9cde3 100%);
<?php endif; ?>
  }

  /* Franja inferior: en el carnet físico es una foto de Cumaná difuminada */
<?php if (!$fondo): ?>
  .card::after {
    content:''; position:absolute; left:0; right:0; bottom:0; height:14mm;
    background:linear-gradient(180deg, rgba(150,175,200,0) 0%, rgba(120,150,180,.45) 45%, rgba(85,115,150,.70) 100%);
  }
<?php endif; ?>

  .capa { position:absolute; inset:0; z-index:2; }

  /* ── Cabecera ──────────────────────────────────────────────────── */
  .logo-alc { position:absolute; top:2.2mm; left:2.4mm; width:13mm; height:auto; }

  .marca { position:absolute; top:3.2mm; right:2.6mm; text-align:right; }
  .marca .imatur {
    font-size:19pt; font-weight:900; letter-spacing:-0.3pt; line-height:1;
    color:<?php echo $azul; ?>;
    -webkit-text-stroke:0.45mm #fff; paint-order:stroke fill;
    text-shadow:0 0.3mm 0.5mm rgba(0,0,0,.18);
  }
  .marca .rif { font-size:4.4pt; color:<?php echo $gris; ?>; font-style:italic;
                letter-spacing:.2px; margin-top:0.5mm; }

  /* ── Texto vertical (unidad de adscripción) ────────────────────── */
  .vertical {
    position:absolute; left:0; top:14mm; height:46mm; width:13mm;
    display:flex; align-items:center; justify-content:center;
  }
  .vertical span {
    transform:rotate(-90deg); transform-origin:center; white-space:nowrap;
    font-size:<?php echo mb_strlen($vertical) > 42 ? '5.1' : (mb_strlen($vertical) > 30 ? '6' : '7'); ?>pt;
    font-weight:800; letter-spacing:.2px; text-transform:uppercase;
    color:<?php echo $azul; ?>;
    -webkit-text-stroke:0.16mm #fff; paint-order:stroke fill;
  }

  /* ── Foto circular con aro dorado ──────────────────────────────── */
  .foto {
    position:absolute; top:16.5mm; left:50%; margin-left:-6mm;
    width:27mm; height:27mm; border-radius:50%; overflow:hidden;
    border:0.7mm solid <?php echo $dorado; ?>; background:#f4f6f9;
    box-shadow:0 1mm 2mm rgba(0,0,0,.22);
  }
  .foto img { width:100%; height:100%; object-fit:cover; display:block; }
  .foto .ph { position:absolute; inset:0; display:flex; align-items:center;
              justify-content:center; font-size:14mm; color:#c2cad6; }

  /* ── Identidad ─────────────────────────────────────────────────── */
  .identidad { position:absolute; top:45.5mm; right:2.8mm; left:13mm; text-align:right; }
  .identidad .ape, .identidad .nom {
    font-size:<?php echo 9.5; ?>pt; font-weight:900; line-height:1.08;
    text-transform:uppercase; color:<?php echo $azul; ?>;
    -webkit-text-stroke:0.28mm #fff; paint-order:stroke fill;
    text-shadow:0 0.25mm 0.4mm rgba(0,0,0,.15);
    word-break:break-word;
  }
  .identidad .ci { font-size:6.4pt; font-weight:bold; color:<?php echo $dorado; ?>;
                   letter-spacing:.3px; margin-top:0.9mm; }

  /* Tipo de credencial (TRABAJADOR / PASANTE + FIJO / CONTRATADO) */
  .tipo { margin-top:1.2mm; display:flex; gap:1mm; justify-content:flex-end; align-items:center; }
  .tipo .b1 {
    background:<?php echo $esPasante ? $dorado : $azulOsc; ?>;
    color:<?php echo $esPasante ? '#3a2c00' : '#fff'; ?>;
    font-size:4.9pt; font-weight:bold; letter-spacing:.6px; text-transform:uppercase;
    padding:0.6mm 2.2mm; border-radius:3mm;
  }
  .tipo .b2 {
    border:0.22mm solid <?php echo $azul; ?>; color:<?php echo $azul; ?>; background:rgba(255,255,255,.75);
    font-size:4.9pt; font-weight:bold; letter-spacing:.6px; text-transform:uppercase;
    padding:0.5mm 2mm; border-radius:3mm;
  }

  /* ── Contacto institucional ────────────────────────────────────── */
  .contacto { position:absolute; right:2.8mm; bottom:6.4mm; left:9mm; text-align:right; }
  .cline { display:flex; align-items:flex-start; justify-content:flex-end; gap:1.1mm; margin-bottom:0.9mm; }
  .cline .ico {
    flex-shrink:0; width:2.9mm; height:2.9mm; border-radius:50%; background:<?php echo $azul; ?>;
    color:#fff; font-size:3.6pt; line-height:2.9mm; text-align:center; font-weight:bold;
    margin-top:0.15mm;
  }
  .cline .txt { font-size:4.6pt; font-weight:bold; color:<?php echo $azulOsc; ?>; line-height:1.25; }

  /* ── Lema ──────────────────────────────────────────────────────── */
  .lema {
    position:absolute; left:0; right:0; bottom:1.6mm; text-align:center;
    font-size:6.2pt; font-style:italic; color:#fff; letter-spacing:.3px;
    text-shadow:0 0.2mm 0.6mm rgba(0,0,0,.45);
  }

  @page { size:54mm 85.6mm; margin:0; }
  @media print {
    body { background:#fff; }
    .toolbar { display:none; }
    .sheet { padding:0; }
    .card { box-shadow:none; border:none; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <button class="btn-print" onclick="window.print()">&#128438; Imprimir / PDF</button>
    <div class="hint">Tarjeta vertical (54 × 85.6 mm). En el diálogo de impresión: márgenes «Ninguno» y escala 100%.</div>
  </div>

  <div class="sheet">
    <div class="card">
      <div class="capa">

        <img class="logo-alc" src="<?php echo $logoAlcaldia; ?>" onerror="this.style.display='none'" alt="">

        <div class="marca">
          <div class="imatur">IMATUR</div>
          <div class="rif">RIF: <?php echo $cv($rif); ?></div>
        </div>

        <?php if ($vertical !== ''): ?>
          <div class="vertical"><span><?php echo $cv($vertical); ?></span></div>
        <?php endif; ?>

        <div class="foto">
          <img src="<?php echo $fotoUrl; ?>"
               onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="">
          <div class="ph" style="display:none;">&#128100;</div>
        </div>

        <div class="identidad">
          <div class="ape"><?php echo $cv($carnet['apellido'] ?? ''); ?></div>
          <div class="nom"><?php echo $cv($carnet['nombre'] ?? ''); ?></div>
          <div class="ci">C.I: <?php echo $cv($cedula); ?></div>
          <div class="tipo">
            <?php if (!empty($carnet['subtipo'])): ?>
              <span class="b2"><?php echo $cv($carnet['subtipo']); ?></span>
            <?php endif; ?>
            <span class="b1"><?php echo $cv($carnet['tipo'] ?? ''); ?></span>
          </div>
        </div>

        <div class="contacto">
          <?php if ($telefono !== ''): ?>
            <div class="cline"><span class="ico">&#9742;</span><span class="txt"><?php echo $cv($telefono); ?></span></div>
          <?php endif; ?>
          <?php if ($correo !== ''): ?>
            <div class="cline"><span class="ico">&#64;</span><span class="txt"><?php echo $cv($correo); ?></span></div>
          <?php endif; ?>
          <?php if ($direccion !== ''): ?>
            <div class="cline"><span class="ico">&#9679;</span><span class="txt"><?php echo $cv($direccion); ?></span></div>
          <?php endif; ?>
        </div>

        <?php if ($lema !== ''): ?>
          <div class="lema"><?php echo $cv($lema); ?></div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</body>
</html>
