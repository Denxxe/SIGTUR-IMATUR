<?php
/**
 * Acta de conteo de inventario — documento imprimible (R-8).
 *
 * Deja constancia del cambio de gestión: qué se contó, qué se halló y qué
 * diferencias hubo. Sigue el patrón de "documento oficial" del sistema
 * (membrete institucional con logos y RIF + window.print()).
 *
 * NOTA: no reproduce ningún formato oficial de la Alcaldía — es un acta
 * interna. Si el cliente entrega un formato propio, se adapta.
 */
$c   = $data['conteo'];
$r   = $data['resumen'];
$dif = $data['detalle'] ?? [];
$cv  = fn($x) => htmlspecialchars((string)($x ?? ''));

$rif       = ConfigSistema::rif();
$direccion = ConfigSistema::get('direccion_institucion');
$logoAlc   = URL_ROOT . '/public/assets/images/Logo.png';
$logoImg   = URL_ROOT . '/public/assets/images/Logo_imatur-removebg-preview.png';

$meses = ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$fechaLarga = function ($f) use ($meses) {
    if (!$f) return '—';
    $t = strtotime($f);
    return date('j', $t) . ' de ' . $meses[(int)date('n', $t)] . ' de ' . date('Y', $t);
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acta de conteo #<?php echo (int)$c->id; ?> — IMATUR</title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family:'Times New Roman', Georgia, serif; background:#e9edf2; color:#1a1a1a;
         -webkit-print-color-adjust:exact; print-color-adjust:exact; }
  .toolbar { text-align:center; padding:16px; }
  .btn { padding:9px 20px; background:#16407A; color:#fff; border:none; border-radius:6px; font-size:14px; cursor:pointer; }
  .hoja { width:21.6cm; min-height:27.9cm; margin:0 auto 30px; background:#fff; padding:2cm 2.2cm;
          box-shadow:0 3px 14px rgba(0,0,0,.15); }

  .membrete { display:flex; align-items:center; justify-content:space-between; gap:12px; }
  .membrete img { height:70px; width:auto; object-fit:contain; }
  .membrete .txt { text-align:center; flex:1; line-height:1.3; }
  .membrete .txt b { display:block; font-size:11pt; text-transform:uppercase; }
  .membrete .txt small { font-size:8.5pt; }

  h1 { text-align:center; font-size:13pt; text-transform:uppercase; margin:22px 0 4px; letter-spacing:.5px; }
  .sub { text-align:center; font-size:9.5pt; color:#444; margin-bottom:20px; }

  p.cuerpo { font-size:11pt; text-align:justify; line-height:1.7; margin-bottom:12px; }

  table { width:100%; border-collapse:collapse; margin:14px 0; font-size:9.5pt; }
  th, td { border:1px solid #666; padding:5px 7px; text-align:left; }
  th { background:#e8eef6; font-size:9pt; text-transform:uppercase; }

  .resumen td { text-align:center; }
  .resumen .n { font-size:15pt; font-weight:bold; }

  .firmas { display:flex; justify-content:space-around; margin-top:70px; gap:40px; }
  .firma { text-align:center; flex:1; }
  .firma .linea { border-top:1px solid #000; margin-bottom:6px; }
  .firma small { font-size:9pt; }

  .pie { margin-top:40px; text-align:center; font-size:8pt; color:#555; border-top:1px solid #ccc; padding-top:8px; }

  @page { size:letter; margin:0; }
  @media print { body { background:#fff; } .toolbar { display:none; } .hoja { box-shadow:none; margin:0; width:auto; } }
</style>
</head>
<body>
  <div class="toolbar"><button class="btn" onclick="window.print()">&#128438; Imprimir / PDF</button></div>

  <div class="hoja">
    <div class="membrete">
      <img src="<?php echo $logoAlc; ?>" onerror="this.style.display='none'" alt="">
      <div class="txt">
        <b>República Bolivariana de Venezuela</b>
        <b>Alcaldía del Municipio Sucre</b>
        <b>Instituto Municipal Autónomo de Turismo</b>
        <small>IMATUR · RIF: <?php echo $cv($rif); ?></small>
        <?php if ($direccion): ?><small><?php echo $cv($direccion); ?></small><?php endif; ?>
      </div>
      <img src="<?php echo $logoImg; ?>" onerror="this.style.display='none'" alt="">
    </div>

    <h1>Acta de Conteo Físico de Bienes</h1>
    <div class="sub">N° <?php echo str_pad((string)(int)$c->id, 4, '0', STR_PAD_LEFT); ?> · <?php echo $cv($c->motivo); ?></div>

    <p class="cuerpo">
      En Cumaná, Municipio Sucre del estado Sucre, a los <?php echo $fechaLarga($c->fecha_cierre ?: $c->fecha_inicio); ?>,
      se deja constancia del <strong>conteo físico de los bienes</strong> del Instituto Municipal Autónomo de
      Turismo (IMATUR), iniciado el <?php echo $fechaLarga($c->fecha_inicio); ?> por motivo de
      <strong><?php echo $cv(mb_strtolower($c->motivo)); ?></strong><?php
        if ($c->responsable) echo ', bajo la responsabilidad de <strong>' . $cv($c->responsable) . '</strong>'; ?>.
    </p>

    <p class="cuerpo">
      Se verificó, bien por bien, su <strong>existencia física, ubicación y condición</strong>, contrastándolas
      con lo registrado en el sistema al momento de iniciar el conteo. El resultado es el siguiente:
    </p>

    <table class="resumen">
      <tr>
        <th>Bienes contados</th><th>Hallados</th><th>No aparecieron</th>
        <th>En otra ubicación</th><th>Cambio de condición</th>
      </tr>
      <tr>
        <td class="n"><?php echo $r['total']; ?></td>
        <td class="n"><?php echo $r['hallados']; ?></td>
        <td class="n"><?php echo $r['faltantes']; ?></td>
        <td class="n"><?php echo $r['movidos']; ?></td>
        <td class="n"><?php echo $r['cambio_condicion']; ?></td>
      </tr>
    </table>

    <?php if (!empty($dif)): ?>
      <p class="cuerpo"><strong>Detalle de las diferencias encontradas:</strong></p>
      <table>
        <thead><tr><th style="width:22%">Código</th><th>Bien</th><th style="width:30%">Diferencia</th></tr></thead>
        <tbody>
        <?php foreach ($dif as $d): ?>
          <?php
          $obs = [];
          if ($d->hallado === false) $obs[] = 'No apareció';
          if ($d->hallado && $d->hallado_ubicacion && (int)$d->hallado_ubicacion !== (int)$d->esperado_ubicacion)
              $obs[] = 'Hallado en ' . ($d->ubic_hallada ?? '?') . ' (registrado en ' . ($d->ubic_esperada ?? '?') . ')';
          if ($d->hallado && $d->hallado_condicion && $d->hallado_condicion !== $d->esperado_condicion)
              $obs[] = 'Condición ' . $d->hallado_condicion . ' (registrada ' . $d->esperado_condicion . ')';
          if ($d->observaciones) $obs[] = $d->observaciones;
          ?>
          <tr>
            <td style="font-family:monospace"><?php echo $cv($d->codigo_bn ?: 'Sin código'); ?></td>
            <td><?php echo $cv($d->bien); ?></td>
            <td><?php echo $cv(implode('. ', $obs)); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p class="cuerpo"><strong>No se encontraron diferencias</strong> entre el registro del sistema y lo hallado físicamente.</p>
    <?php endif; ?>

    <?php if ($c->observaciones): ?>
      <p class="cuerpo"><strong>Observaciones:</strong> <?php echo $cv($c->observaciones); ?></p>
    <?php endif; ?>

    <div class="firmas">
      <div class="firma"><div class="linea"></div><small>Coordinación de Compras,<br>Bienes y Servicios</small></div>
      <div class="firma"><div class="linea"></div><small>Presidencia</small></div>
    </div>

    <div class="pie">
      Documento generado por SIGTUR-IMATUR el <?php echo date('d/m/Y'); ?>.
      Acta interna de control patrimonial.
    </div>
  </div>
</body>
</html>
