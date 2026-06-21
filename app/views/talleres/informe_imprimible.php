<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Actividad — <?php echo htmlspecialchars($data['taller']->nombre ?? ''); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10pt; background: #eef1f6; color: #111; }

        .ctrl-bar { background: #f0f4ff; border-bottom: 1px solid #c7d2fe; padding: 10px 32px; display: flex; justify-content: space-between; align-items: center; }
        .ctrl-bar span { font-size: 9pt; color: #374151; }
        .ctrl-btn { padding: 6px 16px; font-family: inherit; font-size: 9pt; font-weight: 600; border: none; border-radius: 5px; cursor: pointer; }
        .ctrl-btn--primary { background: #1a56db; color: #fff; }
        .ctrl-btn--ghost { background: #fff; color: #374151; border: 1px solid #d1d5db !important; margin-right: 6px; }

        .page-wrap { max-width: 760px; margin: 24px auto; background: #fff; border-radius: 4px; box-shadow: 0 2px 12px rgba(0,0,0,.10); padding: 32px 40px 40px; }

        /* Membrete */
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
        .header img { height: 64px; width: auto; object-fit: contain; }
        .header-center { flex: 1; text-align: center; padding: 0 12px; }
        .header-center p { font-size: 8.5pt; font-weight: 700; text-transform: uppercase; line-height: 1.65; margin: 0; }
        .divider { border: none; border-top: 1.5px solid #111; margin: 8px 0 18px; }

        .doc-title { text-align: center; font-size: 13pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 22px; }

        .field-table { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .field-table td { padding: 5px 8px; font-size: 10pt; vertical-align: top; }
        .field-table td.label { font-weight: 700; width: 38%; white-space: nowrap; }
        .field-table td.value { border-bottom: 1px solid #555; }

        .section-title { font-size: 10pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #374151; border-bottom: 1px solid #ccc; padding-bottom: 4px; margin: 18px 0 10px; }

        .demo-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
        .demo-box { border: 1px solid #999; border-radius: 4px; text-align: center; padding: 8px 4px; }
        .demo-box .demo-label { font-size: 8pt; font-weight: 700; text-transform: uppercase; color: #555; }
        .demo-box .demo-value { font-size: 18pt; font-weight: 800; color: #1a56db; }
        .part-table { width: 100%; border-collapse: collapse; margin-top: 8px; font-size: 9pt; }
        .part-table th { background: #f1f5f9; padding: 5px 8px; border: 1px solid #555; font-weight: 700; text-transform: uppercase; font-size: 8pt; text-align: center; }
        .part-table th.left { text-align: left; }
        .part-table td { border: 1px solid #aaa; padding: 4px 8px; vertical-align: middle; }
        .part-table td.c { text-align: center; }
        .demo-total { border: 2px solid #1a56db; border-radius: 4px; padding: 8px 16px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
        .demo-total span { font-weight: 700; font-size: 10pt; }
        .demo-total .total-val { font-size: 22pt; font-weight: 800; color: #1a56db; }

        .resumen-box { border: 1px solid #ccc; border-radius: 4px; padding: 12px 14px; font-size: 10pt; line-height: 1.7; text-align: justify; min-height: 80px; }

        .no-informe { text-align: center; padding: 40px 20px; color: #888; font-size: 12pt; }

        .signature-row { display: flex; justify-content: space-around; margin-top: 60px; }
        .signature-block { text-align: center; width: 240px; }
        .signature-block .sig-line { border-top: 1px solid #333; padding-top: 8px; }
        .signature-block .sig-name { font-weight: 700; font-size: 10pt; }
        .signature-block .sig-role { font-size: 9pt; color: #666; }

        @media print {
            body { background: #fff; }
            .ctrl-bar { display: none !important; }
            .page-wrap { margin: 0; box-shadow: none; padding: 20px 28px 28px; max-width: 100%; border-radius: 0; }
            @page { size: A4 portrait; margin: 1.2cm 1.5cm; }
        }
    </style>
</head>
<body>
<?php
$taller  = $data['taller'];
$informe = $data['informe'] ?? null;
$config  = $data['config'] ?? [];
$fechaFormato = !empty($taller->fecha_inicio) ? date('d/m/Y', strtotime($taller->fecha_inicio)) : '';
?>

<div class="ctrl-bar">
    <span>Reporte de Actividad — <strong><?php echo htmlspecialchars($taller->nombre ?? ''); ?></strong></span>
    <div>
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<div class="page-wrap">

    <div class="header">
        <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo.png" alt="Alcaldía">
        <div class="header-center">
            <p>
                República Bolivariana de Venezuela<br>
                Alcaldía Bolivariana del Municipio Sucre<br>
                Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)<br>
                Cumaná, Estado Sucre<br>
                RIF. <?php echo htmlspecialchars(ConfigSistema::rif()); ?>
            </p>
        </div>
        <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="IMATUR">
    </div>
    <hr class="divider">

    <div class="doc-title">Reporte de Actividad IMATUR-SUCRE</div>

    <?php if (!$informe): ?>
        <div class="no-informe">
            <p>⚠ Informe no completado aún.</p>
            <p style="font-size:10pt;margin-top:8px;">Complete el Reporte Oficial de Actividad desde la página de detalle del taller.</p>
        </div>
    <?php else: ?>

    <table class="field-table">
        <tr>
            <td class="label">Nombre de la Unidad Estadal:</td>
            <td class="value"><?php echo htmlspecialchars($informe->unidad_estadal ?? 'Sucre'); ?></td>
        </tr>
        <tr>
            <td class="label">Nombre de la Actividad:</td>
            <td class="value"><?php echo htmlspecialchars($taller->nombre ?? ''); ?></td>
        </tr>
        <tr>
            <td class="label">Fecha:</td>
            <td class="value"><?php echo htmlspecialchars($fechaFormato); ?></td>
        </tr>
        <tr>
            <td class="label">Hora:</td>
            <td class="value"><?php echo htmlspecialchars($taller->hora_inicio ?? 'N/A'); ?></td>
        </tr>
        <tr>
            <td class="label">Lugar exacto y municipio:</td>
            <td class="value"><?php echo htmlspecialchars($informe->lugar_exacto ?? ($taller->ubicacion ?? '')); ?></td>
        </tr>
        <tr>
            <td class="label">Instituciones o empresas presentes:</td>
            <td class="value"><?php echo htmlspecialchars($informe->instituciones_presentes ?? '—'); ?></td>
        </tr>
    </table>

    <div class="section-title">Demografía de Asistentes</div>

    <div class="demo-grid">
        <div class="demo-box">
            <div class="demo-label">Mujeres</div>
            <div class="demo-value"><?php echo (int)($informe->mujeres ?? 0); ?></div>
        </div>
        <div class="demo-box">
            <div class="demo-label">Hombres</div>
            <div class="demo-value"><?php echo (int)($informe->hombres ?? 0); ?></div>
        </div>
        <div class="demo-box">
            <div class="demo-label">Niñas (5-11)</div>
            <div class="demo-value"><?php echo (int)($informe->ninas ?? 0); ?></div>
        </div>
        <div class="demo-box">
            <div class="demo-label">Niños (5-11)</div>
            <div class="demo-value"><?php echo (int)($informe->ninos ?? 0); ?></div>
        </div>
    </div>
    <div class="demo-total">
        <span>Total personas atendidas:</span>
        <span class="total-val"><?php echo (int)($informe->total_atendidas ?? 0); ?></span>
    </div>

    <div class="section-title">Resumen de la Actividad</div>
    <div class="resumen-box">
        <?php echo nl2br(htmlspecialchars($informe->resumen_actividad ?? '')); ?>
    </div>

    <div class="signature-row">
        <div class="signature-block">
            <div class="sig-line">
                <div class="sig-name"><?php echo htmlspecialchars(trim(($taller->facilitador_nombre ?? '') . ' ' . ($taller->facilitador_apellido ?? ''))); ?></div>
                <div class="sig-role">Facilitador / Responsable</div>
            </div>
        </div>
        <div class="signature-block">
            <div class="sig-line">
                <div class="sig-name"><?php echo htmlspecialchars($config['firmante_nombre']['valor'] ?? 'Director(a) General'); ?></div>
                <div class="sig-role"><?php echo htmlspecialchars($config['firmante_cargo']['valor'] ?? 'Director General'); ?></div>
            </div>
        </div>
    </div>

    <?php if ($informe && !empty($data['participantes'])): ?>
    <div class="section-title" style="margin-top:28px; page-break-before:auto;">
        Listado de Participantes (<?php echo count($data['participantes']); ?> registrados)
    </div>
    <table class="part-table">
        <thead>
            <tr>
                <th class="c" style="width:4%;">N°</th>
                <th class="c" style="width:13%;">Cédula / ID</th>
                <th class="left" style="width:22%;">Nombre Completo</th>
                <th class="c" style="width:7%;">Tipo</th>
                <th class="c" style="width:6%;">Gén.</th>
                <th class="c" style="width:5%;">Edad</th>
                <th class="left" style="width:17%;">Parroquia / Municipio</th>
                <th class="c" style="width:8%;">Asist.</th>
                <th class="left">Docente / Tutor</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($data['participantes'] as $i => $p):
                $esLibre = empty($p->id_persona);
                $nombre  = htmlspecialchars(trim(
                    ($esLibre ? ($p->nombre_libre ?? '') : ($p->nombre ?? '')) . ' ' .
                    ($esLibre ? ($p->apellido_libre ?? '') : ($p->apellido ?? ''))
                ));
                $cedula  = htmlspecialchars($esLibre ? ($p->cedula_libre ?? '—') : ($p->cedula ?? '—'));
                $genSrc  = $esLibre ? ($p->genero_libre ?? '') : ($p->genero ?? '');
                $genero  = ['M' => 'M', 'F' => 'F'][$genSrc] ?? '—';
                $fnac    = $esLibre ? ($p->fecha_nac_libre ?? null) : ($p->fecha_nacimiento ?? null);
                $edad    = Util::edad($fnac) ?? '';
                $parr    = $esLibre ? ($p->parroquia_libre_nombre ?? '') : ($p->parroquia_nombre ?? '');
                $muni    = $esLibre ? ($p->municipio_libre_nombre ?? '') : ($p->municipio_nombre ?? '');
                $ubic    = trim($parr . ($muni ? ' / ' . $muni : ''));
            ?>
            <tr>
                <td class="c"><?php echo $i + 1; ?></td>
                <td class="c"><?php echo $cedula; ?></td>
                <td><?php echo $nombre; ?></td>
                <td class="c" style="font-size:8pt;"><?php echo $esLibre ? 'Niño/a' : 'Adulto'; ?></td>
                <td class="c"><?php echo $genero; ?></td>
                <td class="c"><?php echo $edad !== '' ? $edad : '—'; ?></td>
                <td style="font-size:8pt;"><?php echo $ubic !== '' ? htmlspecialchars($ubic) : '—'; ?></td>
                <td class="c"><?php echo $p->asistio ? '✓' : '—'; ?></td>
                <td style="font-size:8.5pt;"><?php echo !empty($p->nombre_docente) ? htmlspecialchars($p->nombre_docente) : '—'; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <?php endif; ?>

</div>
</body>
</html>
