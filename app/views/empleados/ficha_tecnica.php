<?php
$e = $data['empleado'];
$ff = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : '';
$v  = fn($x) => !empty($x) ? htmlspecialchars($x) : '';
$niveles = ['Primaria', 'Media', 'Diversificada', 'Técnico Medio'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Ficha Técnica — <?php echo $v($e->nombre . ' ' . $e->apellido); ?></title>
<style>
  * { margin:0; padding:0; box-sizing:border-box; }
  body { font-family: 'Times New Roman', serif; font-size: 10.5pt; color:#000; background:#fff; }
  .page { width:21cm; min-height:29.7cm; margin:0 auto; padding:1.4cm 1.8cm; }
  .header { display:flex; align-items:center; justify-content:space-between; margin-bottom:0.3cm; }
  .logo-box { width:64px; height:64px; display:flex; align-items:center; justify-content:center; }
  .header-text { flex:1; text-align:center; font-size:8.5pt; line-height:1.5; text-transform:uppercase; font-weight:bold; }
  .titulo { text-align:center; font-size:13pt; font-weight:bold; text-transform:uppercase; margin:0.3cm 0; }
  .divider { border-top:1.5px solid #000; margin:0.2cm 0; }
  .seccion { background:#e4e4e4; font-weight:bold; text-transform:uppercase; padding:3px 6px; border:1px solid #000; margin-top:0.35cm; font-size:9.5pt; }
  table { width:100%; border-collapse:collapse; }
  .datos td { border:1px solid #000; padding:4px 6px; font-size:10pt; vertical-align:top; }
  .datos td .lbl { font-weight:bold; }
  .grid th { background:#d0d0d0; border:1px solid #000; padding:4px; font-size:9pt; text-align:center; }
  .grid td { border:1px solid #000; padding:4px; font-size:9pt; height:0.7cm; }
  .etapas td { border:1px solid #000; padding:4px 6px; text-align:center; font-size:9pt; }
  .firmas { display:flex; justify-content:space-between; margin-top:1.6cm; }
  .firma { width:45%; text-align:center; border-top:1px solid #000; padding-top:4px; font-size:9pt; }
  .btn-print { position:fixed; top:12px; right:12px; padding:8px 18px; background:#2563EB; color:#fff; border:none; border-radius:6px; font-size:13px; cursor:pointer; font-family:sans-serif; }
  @media print { .btn-print { display:none; } .page { padding:1.1cm 1.5cm; } }
</style>
</head>
<body>
<button class="btn-print" onclick="window.print()">&#128438; Imprimir / PDF</button>
<div class="page">

  <div class="header">
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" style="max-width:60px;max-height:60px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
    <div class="header-text">
      REPÚBLICA BOLIVARIANA DE VENEZUELA<br>
      ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE<br>
      INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO (IMATUR-SUCRE)<br>
      CUMANÁ, ESTADO SUCRE — RIF. G-20008498-7
    </div>
    <div class="logo-box">
      <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" style="max-width:60px;max-height:60px;object-fit:contain;" onerror="this.style.display='none'">
    </div>
  </div>
  <div class="divider"></div>
  <div class="titulo">Ficha Técnica del Trabajador</div>

  <!-- DATOS PERSONALES -->
  <div class="seccion">Datos Personales</div>
  <table class="datos">
    <tr>
      <td colspan="2"><span class="lbl">Nombres y apellidos:</span> <?php echo $v($e->nombre . ' ' . $e->apellido); ?></td>
      <td><span class="lbl">N° Cédula:</span> <?php echo $v($e->cedula); ?></td>
    </tr>
    <tr>
      <td><span class="lbl">Dirección:</span> <?php echo $v($e->direccion); ?></td>
      <td><span class="lbl">Fecha de Nacimiento:</span> <?php echo $ff($e->fecha_nacimiento); ?></td>
      <td><span class="lbl">N° Telef.:</span> <?php echo $v($e->telefono); ?></td>
    </tr>
    <tr>
      <td><span class="lbl">Parroquia:</span> <?php echo $v($e->parroquia); ?></td>
      <td><span class="lbl">Correo electrónico:</span> <?php echo $v($e->correo); ?></td>
      <td><span class="lbl">RIF:</span> <?php echo $v($e->rif); ?></td>
    </tr>
  </table>

  <!-- FORMACIÓN -->
  <div class="seccion">Formación</div>
  <table class="datos">
    <tr>
      <td><span class="lbl">Nivel académico:</span> <?php echo $v($e->nivel_academico); ?></td>
      <td><span class="lbl">Profesión:</span> <?php echo $v($e->profesion); ?></td>
    </tr>
    <tr>
      <td><span class="lbl">Nombre del título:</span> <?php echo $v($e->titulo); ?></td>
      <td><span class="lbl">Fecha de graduación:</span> <?php echo $ff($e->fecha_graduacion); ?></td>
    </tr>
    <tr>
      <td colspan="2"><span class="lbl">Institución:</span> <?php echo $v($e->institucion_academica); ?></td>
    </tr>
  </table>
  <table class="etapas" style="margin-top:-1px;">
    <tr>
      <?php foreach ($niveles as $n): ?>
        <td><span class="lbl"><?php echo $n; ?></span>: <?php echo ($e->nivel_academico === $n) ? 'X' : ''; ?></td>
      <?php endforeach; ?>
    </tr>
  </table>

  <!-- CURSOS REALIZADOS -->
  <div class="seccion">Cursos Realizados</div>
  <table class="grid">
    <tr><th style="width:30%;">Institución</th><th>Curso</th><th style="width:15%;">Inicio</th><th style="width:15%;">Culminación</th></tr>
    <?php if (empty($data['cursos'])): for ($i=0;$i<2;$i++): ?>
      <tr><td></td><td></td><td></td><td></td></tr>
    <?php endfor; else: foreach ($data['cursos'] as $c): ?>
      <tr><td><?php echo $v($c->institucion); ?></td><td><?php echo $v($c->curso); ?></td>
          <td style="text-align:center;"><?php echo $ff($c->fecha_inicio); ?></td>
          <td style="text-align:center;"><?php echo $ff($c->fecha_culminacion); ?></td></tr>
    <?php endforeach; endif; ?>
  </table>

  <!-- CARGA FAMILIAR -->
  <div class="seccion">Carga Familiar</div>
  <table class="grid">
    <tr><th>Nombre y apellido</th><th style="width:18%;">Cédula</th><th style="width:18%;">F. Nacimiento</th><th style="width:18%;">Parentesco</th></tr>
    <?php if (empty($data['familiares'])): for ($i=0;$i<2;$i++): ?>
      <tr><td></td><td></td><td></td><td></td></tr>
    <?php endfor; else: foreach ($data['familiares'] as $f): ?>
      <tr><td><?php echo $v($f->nombre_apellido); ?></td>
          <td style="text-align:center;"><?php echo $v($f->cedula); ?></td>
          <td style="text-align:center;"><?php echo $ff($f->fecha_nacimiento); ?></td>
          <td style="text-align:center;"><?php echo $v($f->parentesco); ?></td></tr>
    <?php endforeach; endif; ?>
  </table>

  <!-- DATOS LABORALES -->
  <div class="seccion">Datos Laborales</div>
  <table class="datos">
    <tr>
      <td><span class="lbl">Cargo:</span> <?php echo $v($e->cargo); ?></td>
      <td><span class="lbl">Área:</span> <?php echo $v($e->departamento); ?></td>
      <td><span class="lbl">Fecha de Ingreso:</span> <?php echo $ff($e->fecha_ingreso); ?></td>
    </tr>
    <tr>
      <td><span class="lbl">Institución:</span> <?php echo $v($e->institucion_origen); ?></td>
      <td colspan="2"><span class="lbl">Tipo de personal:</span> <?php echo $v($e->tipo_contrato); ?><?php echo !empty($e->clasificacion) ? ' — ' . $v($e->clasificacion) : ''; ?><?php echo ($e->es_comision_servicio === true || $e->es_comision_servicio === 't') ? ' (Comisión de servicio)' : ''; ?></td>
    </tr>
  </table>

  <!-- EXPERIENCIA LABORAL -->
  <div class="seccion">Experiencia Laboral</div>
  <table class="grid">
    <tr><th style="width:34%;">Organismo</th><th>Cargo</th><th style="width:15%;">Inicio</th><th style="width:15%;">Culminación</th></tr>
    <?php if (empty($data['experiencia'])): for ($i=0;$i<2;$i++): ?>
      <tr><td></td><td></td><td></td><td></td></tr>
    <?php endfor; else: foreach ($data['experiencia'] as $x): ?>
      <tr><td><?php echo $v($x->organismo); ?></td><td><?php echo $v($x->cargo); ?></td>
          <td style="text-align:center;"><?php echo $ff($x->fecha_inicio); ?></td>
          <td style="text-align:center;"><?php echo $ff($x->fecha_culminacion); ?></td></tr>
    <?php endforeach; endif; ?>
  </table>

  <div class="firmas">
    <div class="firma">Firma del trabajador</div>
    <div class="firma">Firma del director de Talento Humano</div>
  </div>

</div>
</body>
</html>
