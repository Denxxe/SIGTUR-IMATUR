<?php
/**
 * Partial imprimible del CARNET institucional — VERTICAL, UNA SOLA CARA (CR80
 * retrato 54×85.6mm). Compartido por empleados y pasantes. Documento standalone.
 * Paleta del logo IMATUR (azul marino / océano / dorado).
 *
 * Espera $carnet (array):
 *   tipo        'TRABAJADOR' | 'PASANTE'
 *   subtipo     'FIJO' | 'CONTRATADO' | ''   (solo trabajadores)
 *   nombre      nombre completo
 *   cedula      C.I.
 *   id_persona  para la URL de la foto (/descarga/foto/{id})
 *   lineas      [['label'=>..,'valor'=>..], ...]  (Cargo·Departamento / Carrera·Institución)
 */
$cv      = fn($x) => htmlspecialchars((string)($x ?? ''));
$logo    = URL_ROOT . '/public/assets/images/Logo_imatur-removebg-preview.png';
$fotoUrl = URL_ROOT . '/descarga/foto/' . (int)($carnet['id_persona'] ?? 0);
$esPasante = ($carnet['tipo'] ?? '') === 'PASANTE';

// Paleta IMATUR
$navy = '#16407A';   // azul marino
$ocean = '#1C6FB0';  // azul océano
$gold = '#F4B41A';   // dorado
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carnet — <?php echo $cv($carnet['nombre'] ?? ''); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, Helvetica, sans-serif; background:#e9edf2; color:#1f2733;
         -webkit-print-color-adjust:exact; print-color-adjust:exact; }

  .toolbar { text-align:center; padding:16px; }
  .btn-print { padding:9px 20px; background:<?php echo $navy; ?>; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; }
  .hint { font-size:12px; color:#5b6470; margin-top:8px; }

  .sheet { display:flex; justify-content:center; padding:0 0 30px; }

  /* Tarjeta CR80 vertical */
  .card {
    width:54mm; height:85.6mm; background:#fff; border-radius:3.5mm; overflow:hidden;
    position:relative; box-shadow:0 3px 14px rgba(0,0,0,.2); border:1px solid #cfd6df;
    display:flex; flex-direction:column;
  }

  /* Cabecera institucional */
  .head {
    background:linear-gradient(135deg, <?php echo $navy; ?> 0%, <?php echo $ocean; ?> 100%);
    color:#fff; text-align:center; padding:2mm 2mm 1.6mm; position:relative; flex-shrink:0;
  }
  .head::after { content:''; display:block; height:0.9mm; background:<?php echo $gold; ?>;
                 position:absolute; left:0; right:0; bottom:0; }
  .head .logo { width:9.5mm; height:9.5mm; background:#fff; border-radius:50%; margin:0 auto 0.8mm;
                display:flex; align-items:center; justify-content:center; box-shadow:0 1px 2px rgba(0,0,0,.25); }
  .head .logo img { width:8mm; height:8mm; object-fit:contain; }
  .head .inst { font-size:4.6pt; line-height:1.25; text-transform:uppercase; font-weight:bold; letter-spacing:.2px; }

  /* Foto + tipo (centrados) */
  .top { text-align:center; padding:2.4mm 0 1.6mm; flex-shrink:0; }
  .photo {
    width:20mm; height:24mm; border:0.6mm solid <?php echo $gold; ?>; border-radius:1.6mm; overflow:hidden;
    background:#eef1f5; position:relative; margin:0 auto 1.6mm; box-shadow:0 1px 3px rgba(0,0,0,.2);
  }
  .photo img { width:100%; height:100%; object-fit:cover; display:block; }
  .photo .ph { position:absolute; inset:0; display:flex; align-items:center; justify-content:center; font-size:14mm; color:#c2cad6; }
  .badge-tipo { display:inline-block; background:<?php echo $esPasante ? $gold : $navy; ?>;
                color:<?php echo $esPasante ? '#3a2c00' : '#fff'; ?>; font-size:6pt; font-weight:bold;
                letter-spacing:.6px; padding:0.8mm 3mm; border-radius:3mm; text-transform:uppercase; }
  .badge-sub { display:block; margin-top:0.8mm; font-size:4.8pt; font-weight:bold; letter-spacing:1px;
               color:<?php echo $ocean; ?>; text-transform:uppercase; }

  /* Nombre */
  .nombre { text-align:center; font-size:8.2pt; font-weight:bold; color:<?php echo $navy; ?>;
            line-height:1.1; text-transform:uppercase; padding:0 3mm; margin-top:1.2mm; }
  .divider { height:0.35mm; background:<?php echo $gold; ?>; width:70%; margin:1.4mm auto; border-radius:1mm; flex-shrink:0; }

  /* Datos (alineados a la izquierda) */
  .info { flex:1; padding:0 4.5mm; text-align:left; }
  .row { margin-bottom:1.5mm; }
  .row .lbl { display:block; font-size:4.6pt; color:#8a93a0; text-transform:uppercase; letter-spacing:.5px; margin-bottom:0.2mm; }
  .row .val { display:block; font-size:7pt; font-weight:bold; color:#1f2733; line-height:1.18; }

  /* Pie */
  .foot { background:<?php echo $navy; ?>; color:#fff; text-align:center; font-size:4.6pt;
          letter-spacing:.6px; text-transform:uppercase; padding:1.2mm 2mm; flex-shrink:0; }

  @page { size:54mm 85.6mm; margin:0; }
  @media print {
    body { background:#fff; }
    .toolbar { display:none; }
    .sheet { padding:0; }
    .card { box-shadow:none; border:none; border-radius:0; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <button class="btn-print" onclick="window.print()">&#128438; Imprimir / PDF</button>
    <div class="hint">Tarjeta vertical (54 × 85.6 mm). En el diálogo de impresión: márgenes "Ninguno" y escala 100%.</div>
  </div>

  <div class="sheet">
    <div class="card">
      <div class="head">
        <div class="logo"><img src="<?php echo $logo; ?>" onerror="this.style.display='none'" alt=""></div>
        <div class="inst">Instituto Municipal<br>Autónomo de Turismo</div>
      </div>

      <div class="top">
        <div class="photo">
          <img src="<?php echo $fotoUrl; ?>" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';" alt="">
          <div class="ph" style="display:none;">&#128100;</div>
        </div>
        <span class="badge-tipo"><?php echo $cv($carnet['tipo'] ?? ''); ?></span>
        <?php if (!empty($carnet['subtipo'])): ?>
          <span class="badge-sub"><?php echo $cv($carnet['subtipo']); ?></span>
        <?php endif; ?>
      </div>

      <div class="nombre"><?php echo $cv($carnet['nombre'] ?? ''); ?></div>
      <div class="divider"></div>

      <div class="info">
        <div class="row">
          <span class="lbl">Cédula de identidad</span>
          <span class="val"><?php echo $cv($carnet['cedula'] ?? '—'); ?></span>
        </div>
        <?php foreach (($carnet['lineas'] ?? []) as $ln): ?>
          <div class="row">
            <span class="lbl"><?php echo $cv($ln['label']); ?></span>
            <span class="val"><?php echo $cv($ln['valor'] ?: '—'); ?></span>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="foot">Credencial Institucional</div>
    </div>
  </div>
</body>
</html>
