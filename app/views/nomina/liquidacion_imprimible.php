<?php
/**
 * Hoja FORMATO de la liquidación del cliente, imprimible. Standalone: no carga
 * el layout ni Bootstrap (estilos en línea). Sin margen en @page — el aire lo da
 * el padding de .page — para que Chrome no imprima su encabezado con la URL.
 */
$l = $data['liq'];
$v = fn($x) => htmlspecialchars((string)($x ?? ''));
$m = fn($x) => number_format((float)$x, 2, ',', '.');
$d = fn($x) => rtrim(rtrim(number_format((float)$x, 2, ',', '.'), '0'), ',');
$fecha = fn($f) => $f ? date('d/m/Y', strtotime($f)) : '';
$diasMes = (float)(Liquidacion::params()['nomina_dias_mes'] ?? 30) ?: 30;
$marca = fn($c) => ($l->condicion === $c) ? 'X' : '&nbsp;&nbsp;';
$salario = [
    'Sueldo Base' => $l->sueldo_base, 'Prima Profesional' => $l->prima_profesional,
    'Prima Responsabilidad' => $l->prima_responsabilidad, 'Prima Antigüedad' => $l->prima_antiguedad,
    'Prima por Hijo' => $l->prima_hijos, 'Bono de Transporte' => $l->bono_transporte,
    'Prima FOND' => $l->prima_fond, 'Prima Discapacidad' => $l->prima_discapacidad,
    'Caja de Ahorro' => $l->caja_ahorro,
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:Arial, Helvetica, sans-serif; font-size:9.5pt; color:#000; background:#fff; }
  .page { width:21cm; margin:0 auto; padding:1.2cm 1.5cm; }
  table { width:100%; border-collapse:collapse; }
  td, th { border:1px solid #000; padding:3px 6px; vertical-align:middle; }
  th { background:#e5e7eb; font-weight:bold; text-transform:uppercase; font-size:8.5pt; }
  .num { text-align:right; font-variant-numeric:tabular-nums; white-space:nowrap; }
  .lbl { font-size:7.5pt; font-weight:bold; text-transform:uppercase; color:#333; border-bottom:none; }
  .val { border-top:none; height:20px; }
  .titulo { text-align:center; font-weight:bold; font-size:12pt; padding:8px; text-transform:uppercase; letter-spacing:.5px; }
  .total td { font-weight:bold; }
  .salario { width:62%; margin-left:auto; margin-bottom:10px; }
  .nro { text-align:right; font-size:9pt; margin-bottom:6px; }
  .firmas { margin-top:30px; }
  .firmas td { border:none; text-align:center; padding-top:40px; }
  .firmas .linea { border-top:1px solid #000; display:inline-block; width:80%; padding-top:3px; font-weight:bold; font-size:8.5pt; }
  .btn-print { position:fixed; top:12px; right:12px; padding:8px 18px; background:#2563EB; color:#fff; border:none; border-radius:6px; font-size:13px; cursor:pointer; }
  .borrador { position:fixed; top:40%; left:0; right:0; text-align:center; font-size:72pt; color:rgba(220,38,38,.12); transform:rotate(-25deg); pointer-events:none; font-weight:bold; }
  @media print { .btn-print { display:none; } @page { size:A4; margin:0; } }
</style>
</head>
<body>
<button class="btn-print" onclick="window.print()">Imprimir / Guardar PDF</button>
<?php if ($l->estado !== 'Cerrada'): ?><div class="borrador"><?php echo $l->estado === 'Anulada' ? 'ANULADA' : 'BORRADOR'; ?></div><?php endif; ?>
<div class="page">
  <?php $mb = ['alto_logo' => 62, 'margen_inf' => '10px', 'dependencia' => 'Dirección de Talento Humano']; require '../app/views/inc/membrete.php'; ?>

  <div class="nro"><?php echo $l->numero ? 'N.º ' . $v($l->numero) : ''; ?></div>

  <table class="salario">
    <tr><th style="text-align:left;">Conceptos</th><th>Monto mensual</th><th>Monto diario</th></tr>
    <?php foreach ($salario as $c => $x): ?>
      <tr><td><?php echo $c; ?></td><td class="num"><?php echo $m($x); ?></td><td class="num"><?php echo $m((float)$x / $diasMes); ?></td></tr>
    <?php endforeach; ?>
    <tr class="total"><td>SUB-TOTAL SALARIO</td><td class="num"><?php echo $m($l->subtotal_mensual); ?></td><td class="num"><?php echo $m($l->salario_normal_diario); ?></td></tr>
    <tr><td>Alic. Bono Vacacional (<?php echo $d($l->dias_alic_vac); ?> días)</td><td></td><td class="num"><?php echo $m($l->alic_vac_diaria); ?></td></tr>
    <tr><td>Alic. Bonif. Fin Año (<?php echo $d($l->dias_alic_fin); ?> días)</td><td></td><td class="num"><?php echo $m($l->alic_fin_diaria); ?></td></tr>
    <tr class="total"><td>TOTAL SALARIO INTEGRAL</td><td></td><td class="num"><?php echo $m($l->salario_integral_diario); ?></td></tr>
  </table>

  <table>
    <tr><td colspan="4" class="titulo">Liquidación de Prestaciones Sociales</td></tr>
    <tr><td colspan="2" class="lbl">Nombres y apellidos</td><td class="lbl">Cédula</td><td class="lbl">Cargo</td></tr>
    <tr><td colspan="2" class="val"><?php echo $v(trim($l->nombre . ' ' . $l->apellido)); ?></td><td class="val"><?php echo $v($l->cedula); ?></td><td class="val"><?php echo $v($l->cargo); ?></td></tr>
    <tr><td colspan="3" class="lbl">Dirección o departamento</td><td class="lbl">Condición laboral</td></tr>
    <tr><td colspan="3" class="val"><?php echo $v($l->departamento); ?></td><td class="val"><?php echo $v($l->condicion); ?></td></tr>
    <tr><td class="lbl">Fecha ingreso</td><td class="lbl">Fecha egreso</td><td colspan="2" class="lbl">Tiempo laboral</td></tr>
    <tr>
      <td class="val"><?php echo $fecha($l->fecha_ingreso); ?></td>
      <td class="val"><?php echo $fecha($l->fecha_egreso); ?></td>
      <td colspan="2" class="val"><?php echo (int)$l->anios; ?> AÑOS &nbsp; <?php echo (int)$l->meses; ?> MESES &nbsp; <?php echo (int)$l->dias; ?> DÍAS</td>
    </tr>
  </table>

  <table style="margin-top:10px;">
    <tr><th style="text-align:left;width:58%;">Conceptos</th><th>Tiempo</th><th>Alícuota</th><th>Total</th></tr>
    <?php foreach (NominaController::conceptosLiquidacion($l) as $c): ?>
      <tr>
        <td><?php echo $v($c['concepto']); ?></td>
        <td class="num"><?php echo $c['tiempo'] === null ? '' : $d($c['tiempo']); ?></td>
        <td class="num"><?php echo $c['alicuota'] === null ? '' : $m($c['alicuota']); ?></td>
        <td class="num"><?php echo $m($c['total']); ?></td>
      </tr>
    <?php endforeach; ?>
    <tr class="total"><td colspan="3" style="text-align:right;">SUB-TOTAL PRESTACIONES SOCIALES</td><td class="num"><?php echo $m($l->subtotal); ?></td></tr>
    <tr class="total"><td colspan="3" style="text-align:right;">TOTAL DE INTERÉS DE MORA</td><td class="num"><?php echo $m($l->interes_mora); ?></td></tr>
    <tr class="total"><td colspan="3" style="text-align:right;">TOTAL DE PRESTACIONES SOCIALES</td><td class="num"><?php echo $m($l->total); ?></td></tr>
  </table>
  <div style="font-size:7.5pt;margin-top:4px;color:#333;">
    Antigüedad según el art. 142 d LOTTT: se paga la mayor entre la garantía depositada (<?php echo $m($l->monto_garantia); ?>)
    y el cálculo del literal c (<?php echo $d($l->dias_retroactivo); ?> días × <?php echo $m($l->salario_integral_diario); ?> = <?php echo $m($l->monto_retroactivo); ?>).
  </div>

  <table class="firmas">
    <tr>
      <td style="width:50%;"><span class="linea">LUGAR Y FECHA</span></td>
      <td><span class="linea">FIRMA DEL TRABAJADOR</span></td>
    </tr>
  </table>
  <table style="margin-top:14px;">
    <tr>
      <td style="text-align:center;">EMPLEADO FIJO ( <?php echo $marca('Empleado Fijo'); ?> )</td>
      <td style="text-align:center;">OBRERO FIJO ( <?php echo $marca('Obrero Fijo'); ?> )</td>
      <td style="text-align:center;">CONTRATADO ( <?php echo $marca('Contratado'); ?> )</td>
    </tr>
  </table>
  <table class="firmas">
    <tr><td><span class="linea" style="width:45%;">Dirección de Talento Humano</span></td></tr>
  </table>
</div>
</body>
</html>
