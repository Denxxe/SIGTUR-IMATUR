<?php
$e   = $data['empleado'];
$co  = $data['constancia'];
$cfg = $data['config'] ?? [];
$g = fn($k) => $cfg[$k]['valor'] ?? '';
$fechaIngreso = !empty($e->fecha_ingreso) ? (function ($f) {
    $m = ['', 'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = strtotime($f); return (int)date('d',$t) . ' de ' . $m[(int)date('n',$t)] . ' de ' . date('Y',$t);
})($e->fecha_ingreso) : '—';
$fmtFecha = function ($f) {
    if (empty($f)) return '—';
    $m = ['', 'enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    $t = strtotime($f); return (int)date('d',$t) . ' de ' . $m[(int)date('n',$t)] . ' de ' . date('Y',$t);
};
$firmante = trim($g('director_nombre') . ' ' . $g('director_apellido'));
$cargoFirmante = $g('director_cargo') ?: 'Director(a) General';
$v = fn($x) => htmlspecialchars($x ?? '');
$egresado       = !empty($data['egresado']);
$tiempoServicio = $data['tiempo_servicio'] ?? '';
$fechaEgreso    = $egresado ? $fmtFecha($e->fecha_egreso) : '';

// Tipo de constancia + datos derivados
$tipo = array_key_exists($co->tipo ?? '', Constancia::TIPOS) ? $co->tipo : 'trabajo';
$tituloConst = Constancia::labelTipo($tipo);
$cargo   = $v($e->cargo ?? '—');
$depto   = $v($e->departamento ?? '—');
$contrato= $v($e->tipo_contrato ?? '—');
$nivel   = $v($e->nivel_cargo ?? '');
$tsTxt   = $tiempoServicio ? (', con un tiempo de servicio de <strong>' . $v($tiempoServicio) . '</strong>') : '';
$horaEnt = !empty($e->hora_entrada) ? substr($e->hora_entrada, 0, 5) : '';
$horaSal = !empty($e->hora_salida) ? substr($e->hora_salida, 0, 5) : '';
$horarioNom = $v($e->horario ?? '');
$grupo   = $v($e->grupo_rotacion ?? '');
$motivoEg= $v($e->motivo_egreso ?? '—');
$presta  = $egresado ? 'prestó sus servicios' : 'presta sus servicios';
$desemp  = $egresado ? 'desempeñó el cargo' : 'desempeña el cargo';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?php echo $v($tituloConst); ?> — <?php echo $v($co->numero); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Times New Roman', serif; font-size:12pt; color:#000; background:#fff; }
  .page { width:21cm; min-height:29.7cm; margin:0 auto; padding:2cm 2.2cm; }
  .header { display:flex; align-items:center; justify-content:space-between; gap:14px; margin-bottom:1cm; }
  .logo-box { width:80px; height:80px; flex-shrink:0; display:flex; align-items:center; justify-content:center; }
  .header-text { flex:1; text-align:center; font-family:Arial, Helvetica, sans-serif; text-transform:uppercase; }
  .header-text .linea1 { font-size:11pt; font-weight:bold; letter-spacing:.4px; line-height:1.6; }
  .header-text .linea2 { font-size:9.5pt; font-weight:600; line-height:1.6; color:#222; }
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
  @media print {
    .btn-print { display:none; }
    .page { min-height:auto; padding:0.6cm 1.5cm; }
    @page { size:A4; margin:1cm 1.5cm; }
  }
</style>
</head>
<body>
<button class="btn-print" onclick="window.print()">&#128438; Imprimir / PDF</button>
<div class="page">

  <div class="header">
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo.png" style="max-width:76px;max-height:76px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
    <div class="header-text">
      <div class="linea1">República Bolivariana de Venezuela<br>Alcaldía Bolivariana del Municipio Sucre</div>
      <div class="linea2">Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)<br>Cumaná, Estado Sucre — RIF. <?php echo htmlspecialchars(ConfigSistema::rif()); ?></div>
    </div>
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" style="max-width:76px;max-height:76px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
  </div>

  <div class="doc-nro"><strong>N° <?php echo $v($co->numero); ?></strong></div>

  <div class="titulo"><?php echo $v($tituloConst); ?></div>

  <div class="cuerpo">
    Quien suscribe, <strong><?php echo $v($firmante ?: '____________________'); ?></strong>, en su carácter de
    <strong><?php echo $v($cargoFirmante); ?></strong> del Instituto Municipal Autónomo de Turismo del Municipio Sucre
    (IMATUR), por medio de la presente hace constar que el/la ciudadano(a)
    <strong><?php echo $v($e->nombre . ' ' . $e->apellido); ?></strong>, titular de la cédula de identidad
    N° <strong><?php echo $v($e->cedula); ?></strong>,
    <?php
    switch ($tipo):
        case 'horario': ?>
            <?php echo $presta; ?> en esta institución desempeñando el cargo de <strong><?php echo $cargo; ?></strong>,
            adscrito(a) a <strong><?php echo $depto; ?></strong>,
            <?php if ($horaEnt && $horaSal): ?>
                cumpliendo un horario de trabajo<?php echo $horarioNom ? ' (<strong>' . $horarioNom . '</strong>)' : ''; ?>
                desde las <strong><?php echo $horaEnt; ?></strong> hasta las <strong><?php echo $horaSal; ?></strong><?php echo $grupo ? ', en el <strong>Grupo ' . $grupo . '</strong>' : ''; ?>.
            <?php else: ?>
                sin un horario formal registrado en el sistema a la fecha de emisión.
            <?php endif; ?>
            <?php break; ?>

        <?php case 'funciones': ?>
            <?php echo $desemp; ?> de <strong><?php echo $cargo; ?></strong><?php echo $nivel ? ' (nivel <strong>' . $nivel . '</strong>)' : ''; ?>,
            adscrito(a) a <strong><?php echo $depto; ?></strong>, cumpliendo las funciones y responsabilidades inherentes a dicho cargo<?php echo $tsTxt; ?>.
            <?php break; ?>

        <?php case 'antiguedad': ?>
            ha prestado sus servicios en esta institución desde el <strong><?php echo $fechaIngreso; ?></strong><?php echo $egresado ? ' hasta el <strong>' . $fechaEgreso . '</strong>' : ''; ?>,
            acumulando un tiempo de servicio de <strong><?php echo $v($tiempoServicio ?: '—'); ?></strong>, desempeñando el cargo de <strong><?php echo $cargo; ?></strong>.
            <?php break; ?>

        <?php case 'egreso': ?>
            prestó sus servicios en esta institución desempeñando el cargo de <strong><?php echo $cargo; ?></strong>,
            adscrito(a) a <strong><?php echo $depto; ?></strong>, desde el <strong><?php echo $fechaIngreso; ?></strong>
            hasta el <strong><?php echo $fechaEgreso; ?></strong>, fecha en la cual cesó en sus funciones por motivo de
            <strong><?php echo $motivoEg; ?></strong><?php echo $tsTxt; ?>.
            <?php break; ?>

        <?php case 'bancaria': ?>
            <?php echo $presta; ?> en esta institución desempeñando el cargo de <strong><?php echo $cargo; ?></strong>,
            adscrito(a) a <strong><?php echo $depto; ?></strong>, bajo la modalidad de <strong><?php echo $contrato; ?></strong>,
            desde el <strong><?php echo $fechaIngreso; ?></strong><?php echo $egresado ? ' hasta el <strong>' . $fechaEgreso . '</strong>' : ''; ?><?php echo $tsTxt; ?>.
            Devenga una remuneración mensual de: <strong>_______________________________</strong>.
            <?php break; ?>

        <?php default: // trabajo ?>
            <?php echo $presta; ?> en esta institución desempeñando el cargo de <strong><?php echo $cargo; ?></strong>,
            adscrito(a) a <strong><?php echo $depto; ?></strong>, bajo la modalidad de <strong><?php echo $contrato; ?></strong>,
            desde el <strong><?php echo $fechaIngreso; ?></strong><?php echo $egresado ? ' hasta el <strong>' . $fechaEgreso . '</strong>' : ''; ?><?php echo $tsTxt; ?>.
    <?php endswitch; ?>
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
<script>
  // El navegador imprime su propio encabezado/pie (fecha + título de la pestaña
  // arriba, URL + página abajo) — no es parte del documento y no se puede
  // suprimir por completo desde la página. Vaciar el título justo antes de
  // imprimir sí quita esa mitad del texto (el navegador lo usa arriba).
  var _tituloOriginal = document.title;
  window.addEventListener('beforeprint', function () { document.title = ' '; });
  window.addEventListener('afterprint', function () { document.title = _tituloOriginal; });
</script>
</body>
</html>
