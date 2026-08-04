<?php
/**
 * Hoja de etiquetas de bienes (R-4 · B-14/B-15) — documento imprimible.
 *
 * La Alcaldía pega su propia etiqueta durante la inspección; ésta es la de
 * control interno: código oficial + QR que lleva a la hoja de vida del bien,
 * para poder inventariar escaneando con el teléfono.
 *
 * Solo se imprimen bienes YA codificados: sin N° de orden no hay qué pegar.
 *
 * Usa el `qrcode.min.js` que ya estaba vendorizado en el proyecto (quedó sin
 * uso tras el carnet), así que funciona sin internet.
 */
$items = $data['items'] ?? [];
$rif   = ConfigSistema::rif();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Etiquetas de bienes — IMATUR</title>
<script src="<?php echo URL_ROOT; ?>/public/assets/libs/qrcode.min.js"></script>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: Arial, Helvetica, sans-serif; background:#eef1f5; color:#1f2733;
         -webkit-print-color-adjust:exact; print-color-adjust:exact; }

  .toolbar { text-align:center; padding:16px; }
  .btn { padding:9px 20px; background:#16407A; color:#fff; border:none; border-radius:6px;
         font-size:14px; cursor:pointer; text-decoration:none; display:inline-block; }
  .btn--ghost { background:#fff; color:#16407A; border:1px solid #c9d4e2; margin-left:8px; }
  .hint { font-size:12px; color:#5b6470; margin-top:8px; }

  .hoja { display:flex; flex-wrap:wrap; gap:4mm; padding:6mm; justify-content:flex-start; }

  /* Etiqueta 62 × 30 mm — tamaño típico de rotuladora / hoja adhesiva */
  .etiqueta {
    width:62mm; height:30mm; background:#fff; border:0.3mm solid #b9c4d2; border-radius:1.5mm;
    padding:2mm 2.5mm; display:flex; gap:2mm; align-items:center; break-inside:avoid;
  }
  .etq-qr { width:22mm; height:22mm; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
  .etq-qr img, .etq-qr canvas { width:22mm !important; height:22mm !important; }
  .etq-datos { flex:1; min-width:0; }
  .etq-inst { font-size:5pt; font-weight:bold; color:#16407A; letter-spacing:.2px; text-transform:uppercase; }
  .etq-rif  { font-size:4.2pt; color:#78838f; }
  .etq-cod  { font-size:10pt; font-weight:900; color:#16407A; font-family:'Courier New',monospace;
              letter-spacing:-0.2pt; margin:0.8mm 0 0.4mm; }
  .etq-nom  { font-size:5.6pt; color:#39424d; line-height:1.15; overflow:hidden;
              display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; }
  .etq-ubi  { font-size:4.6pt; color:#78838f; margin-top:0.5mm; }

  .vacio { text-align:center; padding:40px; color:#5b6470; }

  @page { size:A4; margin:8mm; }
  @media print {
    body { background:#fff; }
    .toolbar { display:none; }
    .hoja { padding:0; gap:3mm; }
    .etiqueta { border:0.3mm dashed #9aa7b5; }
  }
</style>
</head>
<body>
  <div class="toolbar">
    <button class="btn" onclick="window.print()">&#128438; Imprimir etiquetas</button>
    <a class="btn btn--ghost" href="<?php echo URL_ROOT; ?>/inventario/index">Volver al inventario</a>
    <div class="hint">
      <?php echo count($items); ?> etiqueta(s) · 62 × 30 mm.
      El QR abre la hoja de vida del bien. Solo se listan bienes ya codificados por la Alcaldía.
    </div>
  </div>

  <?php if (empty($items)): ?>
    <div class="vacio">
      No hay bienes codificados para etiquetar.<br>
      <small>Un bien recibe su código cuando la Alcaldía devuelve el Formulario BM-1.</small>
    </div>
  <?php else: ?>
    <div class="hoja">
      <?php foreach ($items as $i): ?>
        <div class="etiqueta">
          <div class="etq-qr" data-url="<?php echo URL_ROOT . '/inventario/detalle/' . (int)$i->id; ?>"></div>
          <div class="etq-datos">
            <div class="etq-inst">IMATUR</div>
            <div class="etq-rif">RIF: <?php echo htmlspecialchars($rif); ?></div>
            <div class="etq-cod"><?php echo htmlspecialchars($i->codigo_bn); ?></div>
            <div class="etq-nom"><?php echo htmlspecialchars($i->nombre); ?></div>
            <div class="etq-ubi"><?php echo htmlspecialchars($i->ubicacion ?? ''); ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

<script>
// Genera los QR con la librería vendorizada (sin internet).
document.querySelectorAll('.etq-qr').forEach(function (box) {
    var url = box.getAttribute('data-url');
    if (!url || typeof QRCode === 'undefined') return;
    try {
        new QRCode(box, { text: url, width: 96, height: 96, correctLevel: QRCode.CorrectLevel.M });
    } catch (e) {
        // Si la librería no cargó, la etiqueta sigue sirviendo con el código impreso.
        box.textContent = '';
    }
});
</script>
</body>
</html>
