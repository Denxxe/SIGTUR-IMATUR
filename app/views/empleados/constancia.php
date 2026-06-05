<?php
$e   = $data['empleado'];
$co  = $data['constancia'];
$cfg = $data['config'] ?? [];
$g = fn($k) => $cfg[$k]['valor'] ?? '';
$fechaIngreso = !empty($e->fecha_ingreso) ? (function ($f) {
    $m = ['', 'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = strtotime($f); return (int)date('d',$t) . ' de ' . $m[(int)date('n',$t)] . ' de ' . date('Y',$t);
})($e->fecha_ingreso) : '—';
$firmante = trim($g('director_nombre') . ' ' . $g('director_apellido'));
$cargoFirmante = $g('director_cargo') ?: 'Director(a) General';
$v = fn($x) => htmlspecialchars($x ?? '');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Constancia de Trabajo — <?php echo $v($co->numero); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Times New Roman', serif; font-size:12pt; color:#000; background:#fff; }
  .page { width:21cm; min-height:29.7cm; margin:0 auto; padding:2cm 2.2cm; }
  .header { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.3cm; }
  .logo-box { width:70px; height:70px; display:flex; align-items:center; justify-content:center; }
  .header-text { flex:1; text-align:center; font-size:8.5pt; line-height:1.5; text-transform:uppercase; font-weight:bold; }
  .divider { border-top:1.5px solid #000; margin:0.3cm 0; }
  .doc-nro { text-align:right; font-size:11pt; margin:0.4cm 0 1cm; }
  .titulo { text-align:center; font-size:14pt; font-weight:bold; text-transform:uppercase; text-decoration:underline; margin-bottom:1.2cm; letter-spacing:1px; }
  .cuerpo { font-size:12pt; line-height:2; text-align:justify; margin-bottom:1.5cm; }
  .cuerpo strong { font-weight:bold; }
  .cierre { font-size:12pt; line-height:2; text-align:justify; margin-bottom:2.5cm; }
  .firma { text-align:center; margin-top:2cm; }
  .firma-linea { border-top:1px solid #000; width:60%; margin:0 auto 4px; }
  .firma-nombre { font-weight:bold; font-size:12pt; text-transform:uppercase; }
  .firma-cargo { font-size:10pt; text-transform:uppercase; }
  .btn-print { position:fixed; top:12px; right:12px; padding:8px 18px; background:#2563EB; color:#fff; border:none; border-radius:6px; font-size:13px; cursor:pointer; font-family:sans-serif; }
  @media print { .btn-print { display:none; } .page { padding:1.8cm 2cm; } }
</style>
</head>
<body>
<button class="btn-print" onclick="window.print()">&#128438; Imprimir / PDF</button>
<div class="page">

  <div class="header">
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" style="max-width:64px;max-height:64px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
    <div class="header-text">
      REPÚBLICA BOLIVARIANA DE VENEZUELA<br>
      ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE<br>
      INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO (IMATUR-SUCRE)<br>
      CUMANÁ, ESTADO SUCRE — RIF. G-20008498-7
    </div>
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" style="max-width:64px;max-height:64px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
  </div>
  <div class="divider"></div>

  <div class="doc-nro"><strong>N° <?php echo $v($co->numero); ?></strong></div>

  <div class="titulo">Constancia de Trabajo</div>

  <div class="cuerpo">
    Quien suscribe, <strong><?php echo $v($firmante ?: '____________________'); ?></strong>, en su carácter de
    <strong><?php echo $v($cargoFirmante); ?></strong> del Instituto Municipal Autónomo de Turismo del Municipio Sucre
    (IMATUR), por medio de la presente hace constar que el/la ciudadano(a)
    <strong><?php echo $v($e->nombre . ' ' . $e->apellido); ?></strong>, titular de la cédula de identidad
    N° <strong><?php echo $v($e->cedula); ?></strong>, presta sus servicios en esta institución desempeñando el cargo de
    <strong><?php echo $v($e->cargo ?? '—'); ?></strong>, adscrito(a) a <strong><?php echo $v($e->departamento ?? '—'); ?></strong>,
    bajo la modalidad de <strong><?php echo $v($e->tipo_contrato ?? '—'); ?></strong>, desde el
    <strong><?php echo $fechaIngreso; ?></strong>.
  </div>

  <div class="cierre">
    Constancia que se expide a solicitud de la parte interesada, en <?php echo $v($data['fecha_hoy']); ?>.
  </div>

  <div class="firma">
    <div class="firma-linea"></div>
    <div class="firma-nombre"><?php echo $v($firmante ?: '____________________'); ?></div>
    <div class="firma-cargo"><?php echo $v($cargoFirmante); ?></div>
    <?php if ($g('resolucion_numero')): ?>
        <div style="font-size:8pt;margin-top:0.3cm;color:#333;">Resolución N° <?php echo $v($g('resolucion_numero')); ?><?php echo $g('gaceta_numero') ? ' · Gaceta N° ' . $v($g('gaceta_numero')) : ''; ?></div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
