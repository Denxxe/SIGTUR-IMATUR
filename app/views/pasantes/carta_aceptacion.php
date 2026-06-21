<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Carta de Aceptación — <?php echo htmlspecialchars($data['ref']->oficio_aceptacion ?? ''); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'Times New Roman', serif; font-size: 11pt; color: #000; background: #fff; padding: 0; }
  .page { width: 21cm; min-height: 29.7cm; margin: 0 auto; padding: 1.8cm 2cm 2cm 2cm; }

  /* Encabezado institucional */
  .header { display:flex; align-items:center; justify-content:space-between; margin-bottom: 0.4cm; }
  .header-logos { display:flex; align-items:center; gap:12px; }
  .logo-box { width:70px; height:70px; border:1px solid #ccc; display:flex; align-items:center; justify-content:center; font-size:8pt; color:#666; text-align:center; }
  .header-text { flex:1; text-align:center; font-size:8.5pt; line-height:1.5; text-transform:uppercase; font-weight:bold; }
  .divider { border-top:1.5px solid #000; margin:0.3cm 0; }
  .divider-thin { border-top:1px solid #000; margin:0.2cm 0; }

  /* Datos del oficio */
  .oficio-row { display:flex; justify-content:space-between; margin-bottom:0.5cm; margin-top:0.3cm; }
  .oficio-nro { font-size:11pt; }
  .oficio-fecha { font-size:11pt; }

  /* Destinatario */
  .destinatario { margin-bottom:0.5cm; font-size:11pt; line-height:1.6; }

  /* Cuerpo */
  .cuerpo { font-size:11pt; line-height:1.7; text-align:justify; margin-bottom:0.5cm; }

  /* Tabla de pasantes */
  .tabla-pasantes { width:100%; border-collapse:collapse; margin:0.4cm 0; font-size:10pt; }
  .tabla-pasantes th { background:#d0d0d0; font-weight:bold; text-align:center; padding:6px 4px; border:1px solid #000; }
  .tabla-pasantes td { padding:5px 4px; border:1px solid #000; vertical-align:top; text-align:center; }

  /* Fechas */
  .fechas { font-size:11pt; line-height:2; margin-bottom:0.5cm; }
  .fechas strong { font-weight:bold; }

  /* Cierre */
  .cierre { font-size:11pt; line-height:1.7; text-align:justify; margin-bottom:1.5cm; }

  /* Firma */
  .firma { text-align:center; }
  .firma-nombre { font-weight:bold; font-size:12pt; text-transform:uppercase; margin-top:1.2cm; }
  .firma-cargo  { font-size:10pt; text-transform:uppercase; }
  .firma-res    { font-size:8pt; margin-top:0.3cm; color:#333; }

  /* Pie */
  .footer { margin-top:1cm; font-size:7.5pt; text-align:center; border-top:1px solid #000; padding-top:0.2cm; }

  /* Botón de impresión (se oculta al imprimir) */
  .btn-print { position:fixed; top:12px; right:12px; padding:8px 18px; background:#2563EB; color:#fff; border:none; border-radius:6px; font-size:13px; cursor:pointer; font-family:sans-serif; }
  @media print {
    .btn-print { display:none; }
    .page { padding: 1.4cm 1.8cm 1.8cm 1.8cm; }
  }
</style>
</head>
<body>

<button class="btn-print" onclick="window.print()">&#128438; Imprimir / PDF</button>

<div class="page">

  <!-- ── Encabezado institucional ── -->
  <div class="header">
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png"
           alt="Logo" style="max-width:60px;max-height:60px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
    <div class="header-text">
      REPÚBLICA BOLIVARIANA DE VENEZUELA<br>
      ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE<br>
      INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO (IMATUR-SUCRE)<br>
      CUMANÁ, ESTADO SUCRE<br>
      RIF. <?php echo htmlspecialchars(ConfigSistema::rif()); ?>
    </div>
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png"
           alt="IMATUR" style="max-width:60px;max-height:60px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
  </div>
  <div class="divider"></div>

  <!-- ── Número de oficio y fecha ── -->
  <?php
  $cfg    = $data['config']  ?? [];
  $ref    = $data['ref'];
  $grupo  = $data['grupo']   ?? [$ref];
  $oficio = $ref->oficio_aceptacion ?? '';
  $partes = explode('/', $oficio);
  $nroDisplay = (isset($partes[0]) ? ltrim(str_replace('PAST-', '', $partes[0]), '0') : '') . '/' . ($partes[1] ?? date('Y'));
  ?>
  <div class="oficio-row">
    <span class="oficio-nro"><strong>Oficio Nro.&nbsp;&nbsp;<?php echo htmlspecialchars($nroDisplay); ?></strong></span>
    <span class="oficio-fecha">Cumaná, <?php echo $data['fecha_hoy']; ?></span>
  </div>

  <!-- ── Destinatario ── -->
  <div class="destinatario">
    <?php if (!empty($ref->tutor_externo)): ?>
      <?php echo nl2br(htmlspecialchars($ref->tutor_externo)); ?><br>
    <?php else: ?>
      Ciudadano(a) Responsable de Gestión de Proyecto<br>
    <?php endif; ?>
    <strong><?php echo htmlspecialchars($ref->institucion ?? ''); ?></strong>
  </div>

  <!-- ── Saludo y cuerpo ── -->
  <div class="cuerpo">
    <?php
    // Saludo con nombre si hay tutor_externo
    $destNombre = !empty($ref->tutor_externo)
        ? explode(',', $ref->tutor_externo)[0]
        : 'estimado(a) ciudadano(a)';
    ?>
    Me dirijo a usted, <?php echo htmlspecialchars($destNombre); ?>, para notificar la aceptación de
    los estudiantes que solicitaron realizar sus Pasantías Profesionales o Prácticas Laborales en
    nuestra institución. Los estudiantes han sido seleccionados y se integrarán al equipo de
    trabajo de la siguiente manera:
  </div>

  <!-- ── Tabla de pasantes ── -->
  <table class="tabla-pasantes">
    <thead>
      <tr>
        <th>NOMBRE DEL<br>(LA) PASANTE</th>
        <th>CÉDULA</th>
        <th>CARRERA</th>
        <th>ÁREA<br>ASIGNADA</th>
        <th>TUTOR<br>INSTITUCIONAL</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($grupo as $p): ?>
      <tr>
        <td style="text-align:left;">
          <?php echo htmlspecialchars(trim(($p->apellido ?? '') . ', ' . ($p->nombre ?? ''))); ?>
        </td>
        <td><?php echo htmlspecialchars($p->cedula ?? '—'); ?></td>
        <td><?php echo htmlspecialchars($p->carrera ?? '—'); ?></td>
        <td style="text-align:left;"><?php echo htmlspecialchars($p->tutor_departamento ?? '—'); ?></td>
        <td style="text-align:left;"><?php echo htmlspecialchars($p->tutor_nombre ?? '—'); ?>.</td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <!-- ── Fechas ── -->
  <div class="fechas">
    <?php
    function fmtFechaLetter(?string $fecha): string {
        if (!$fecha) return '—';
        $d = new DateTime($fecha);
        $meses = ['', 'enero','febrero','marzo','abril','mayo','junio',
                  'julio','agosto','septiembre','octubre','noviembre','diciembre'];
        return $d->format('d') . '/' . $d->format('m') . '/' . $d->format('Y');
    }
    ?>
    <strong>Fecha de Inicio:</strong> <?php echo fmtFechaLetter($ref->fecha_inicio ?? null); ?><br>
    <strong>Fecha de Culminación Estimada:</strong> <?php echo fmtFechaLetter($ref->fecha_fin ?? null); ?><br>
    <?php if ($data['semanas'] !== null): ?>
    <strong>Duración Total:</strong> <?php echo $data['semanas']; ?> semanas.
    <?php endif; ?>
  </div>

  <!-- ── Cierre ── -->
  <div class="cierre">
    El pasante desarrollará actividades alineadas con los objetivos de su programa académico
    y contribuirán a los proyectos del área asignada, con el compromiso de recibir la guía
    y el acompañamiento profesional adecuado.
    <br><br>
    Sin más que agregar, agradecemos el interés demostrado por nuestra institución.
  </div>

  <!-- ── Firma ── -->
  <?php
  $dirNombre = trim((($cfg['director_nombre']['valor'] ?? '') . ' ' . ($cfg['director_apellido']['valor'] ?? '')));
  $dirCargo  = $cfg['director_cargo']['valor'] ?? 'Director(a)';
  $resNum    = $cfg['resolucion_numero']['valor'] ?? '';
  $resFecha  = $cfg['resolucion_fecha']['valor']  ?? '';
  $gacNum    = $cfg['gaceta_numero']['valor']     ?? '';
  $gacFecha  = $cfg['gaceta_fecha']['valor']      ?? '';
  ?>
  <div style="text-align:center; margin-top:0.6cm;">
    <p style="font-size:11pt;">Atentamente,</p>
    <div class="firma">
      <div class="firma-nombre"><?php echo htmlspecialchars($dirNombre); ?></div>
      <div class="firma-cargo"><?php echo htmlspecialchars($dirCargo); ?></div>
      <?php if ($resNum && $resFecha): ?>
      <div class="firma-res">
        Resolución N° <?php echo htmlspecialchars($resNum); ?> de fecha <?php echo htmlspecialchars($resFecha); ?>,
        publicada en Gaceta Municipal <?php if ($gacNum): ?>N° <?php echo htmlspecialchars($gacNum); ?><?php endif; ?>
        <?php if ($gacFecha): ?>de fecha <?php echo htmlspecialchars($gacFecha); ?><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ── Pie de página ── -->
  <div class="footer">
    CALLE SUCRE N° 11, SAN FRANCISCO, PARROQUIA SANTA INÉS, MUNICIPIO SUCRE-EDO. SUCRE<br>
    Telf.: <?php echo htmlspecialchars($cfg['telf_institucion']['valor'] ?? ''); ?>&nbsp;&nbsp;&nbsp;
    Correo: <?php echo htmlspecialchars($cfg['correo_institucion']['valor'] ?? ''); ?>
  </div>

</div><!-- /.page -->
</body>
</html>
