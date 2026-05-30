<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Asistencia — <?php echo htmlspecialchars($data['taller']->nombre ?? ''); ?></title>
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

        /* Campos de encabezado */
        .fields { margin-bottom: 18px; }
        .fields .field-row { display: flex; align-items: baseline; gap: 6px; margin-bottom: 6px; font-size: 10pt; font-weight: 700; }
        .fields .field-row .line { flex: 1; border-bottom: 1px solid #333; min-width: 200px; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; margin-top: 4px; }
        thead th { background: #f1f5f9; font-size: 8.5pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; padding: 6px 8px; border: 1px solid #555; text-align: center; }
        th.col-n     { width: 6%; }
        th.col-name  { width: 44%; text-align: left; }
        th.col-id    { width: 22%; }
        th.col-firma { width: 28%; }
        tbody td { border: 1px solid #888; padding: 4px 8px; height: 22px; font-size: 9pt; vertical-align: middle; }
        td.n { text-align: center; color: #444; font-size: 8pt; }

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
$taller = $data['taller'];
$participantes = $data['participantes'] ?? [];
$totalFilas = 40;
$fechaFormato = !empty($taller->fecha_inicio) ? date('d/m/Y', strtotime($taller->fecha_inicio)) : '';
?>

<div class="ctrl-bar">
    <span>Lista de Asistencia — <strong><?php echo htmlspecialchars($taller->nombre ?? ''); ?></strong></span>
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
                RIF. G-20008498-7
            </p>
        </div>
        <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="IMATUR">
    </div>
    <hr class="divider">

    <div class="fields">
        <div class="field-row">
            <span>FECHA:</span>
            <span class="line">&nbsp;<?php echo htmlspecialchars($fechaFormato); ?></span>
        </div>
        <div class="field-row">
            <span>ACTIVIDAD:</span>
            <span class="line">&nbsp;<?php echo htmlspecialchars($taller->nombre ?? ''); ?></span>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th class="col-n">N°</th>
                <th class="col-name">NOMBRE Y APELLIDO</th>
                <th class="col-id">CÉDULA</th>
                <th class="col-firma">FIRMA</th>
            </tr>
        </thead>
        <tbody>
            <?php for ($i = 1; $i <= $totalFilas; $i++):
                $p = $participantes[$i - 1] ?? null;
                if ($p) {
                    $esLibre  = empty($p->id_persona);
                    $nombre   = $esLibre
                        ? trim(($p->nombre_libre ?? '') . ' ' . ($p->apellido_libre ?? ''))
                        : trim(($p->nombre ?? '') . ' ' . ($p->apellido ?? ''));
                    $cedula   = $esLibre ? ($p->cedula_libre ?? '') : ($p->cedula ?? '');
                    $docente  = ($esLibre && !empty($p->nombre_docente)) ? $p->nombre_docente : '';
                } else {
                    $nombre = ''; $cedula = ''; $docente = '';
                }
            ?>
                <tr>
                    <td class="n"><?php echo $i; ?></td>
                    <td>
                        <?php echo htmlspecialchars($nombre); ?>
                        <?php if ($docente): ?>
                            <br><span style="font-size:7.5pt; color:#555; font-style:italic;">Docente: <?php echo htmlspecialchars($docente); ?></span>
                        <?php endif; ?>
                    </td>
                    <td style="text-align:center;"><?php echo htmlspecialchars($cedula); ?></td>
                    <td></td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

</div>
</body>
</html>
