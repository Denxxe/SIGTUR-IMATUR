<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Control de Asistencia — <?php echo htmlspecialchars($data['taller']->nombre ?? ''); ?></title>
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: Arial, sans-serif; font-size:10pt; background:#eef1f6; color:#111; }
    .ctrl-bar { background:#f0f4ff; border-bottom:1px solid #c7d2fe; padding:10px 32px; display:flex; justify-content:space-between; align-items:center; gap:8px; }
    .ctrl-bar span { font-size:9pt; color:#374151; }
    .ctrl-btn { padding:6px 16px; font-family:inherit; font-size:9pt; font-weight:600; border:none; border-radius:5px; cursor:pointer; }
    .ctrl-btn--primary { background:#1a56db; color:#fff; }
    .ctrl-btn--ghost   { background:#fff; color:#374151; border:1px solid #d1d5db !important; margin-right:6px; }

    .page-wrap { max-width:800px; margin:24px auto 32px; background:#fff; border-radius:4px; box-shadow:0 2px 12px rgba(0,0,0,.10); padding:28px 36px 36px; }

    /* Encabezado con logo */
    .header-box { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:10px; }
    .header-title { font-size:10.5pt; font-weight:700; text-transform:uppercase; text-align:center; flex:1; padding:4px 0; letter-spacing:.04em; }
    .logo-box { width:90px; text-align:center; flex-shrink:0; }
    .logo-box img { max-width:80px; max-height:50px; object-fit:contain; }
    .logo-label { font-size:7pt; font-weight:700; text-align:center; margin-top:2px; }

    /* Campos del encabezado */
    .meta-table { width:100%; border-collapse:collapse; margin-bottom:10px; }
    .meta-table td { border:1px solid #555; padding:4px 8px; font-size:9.5pt; }
    .meta-table td.lbl { font-weight:700; width:120px; background:#f8fafc; white-space:nowrap; }

    /* Tabla de participantes */
    table.asist { width:100%; border-collapse:collapse; margin-top:6px; }
    table.asist thead th { background:#dde3ec; font-size:8pt; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:5px 4px; border:1px solid #333; text-align:center; }
    table.asist tbody td { border:1px solid #666; padding:3px 4px; height:20px; font-size:8.5pt; vertical-align:middle; }
    td.n { text-align:center; color:#555; font-size:8pt; width:26px; }
    td.cedula   { width:88px; }
    td.nombre   { width:160px; }
    td.edad     { width:38px; text-align:center; }
    td.sexo     { width:38px; text-align:center; }
    td.inst     { }
    td.correo   { }
    td.telf     { width:90px; }

    .hoja-lbl { font-size:8pt; color:#777; text-align:right; margin-bottom:3px; }

    @media print {
        body { background:#fff; }
        .ctrl-bar { display:none !important; }
        .page-wrap { margin:0; box-shadow:none; padding:16px 22px 22px; max-width:100%; border-radius:0; page-break-after:always; }
        .page-wrap:last-child { page-break-after:auto; }
        @page { size:A4 portrait; margin:1cm 1.2cm; }
    }
</style>
</head>
<body>

<div class="ctrl-bar">
    <span>Control de Asistencia (Externa) — <strong><?php echo htmlspecialchars($data['taller']->nombre ?? ''); ?></strong></span>
    <div>
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<?php
$t   = $data['taller'];
$pts = $data['participantes'] ?? [];

function edadExt(?string $fNac): string {
    if (!$fNac) return '';
    $nac = new DateTime($fNac);
    $hoy = new DateTime();
    return (string)$nac->diff($hoy)->y;
}

// Dividir en hojas de 35 filas como el formato original
$FILAS_POR_HOJA = 35;
$hojas = array_chunk($pts, $FILAS_POR_HOJA);
if (empty($hojas)) $hojas = [[]]; // al menos una hoja vacía
$totalHojas = count($hojas);
$offset = 0;
?>

<?php foreach ($hojas as $idx => $hoja):
    $hojaNum = $idx + 1; ?>
<div class="page-wrap">
    <?php if ($totalHojas > 1): ?>
    <div class="hoja-lbl">Hoja <?php echo $hojaNum; ?> / <?php echo $totalHojas; ?></div>
    <?php endif; ?>

    <!-- Encabezado -->
    <div class="header-box">
        <table class="meta-table" style="flex:1;margin-right:12px;">
            <tr><td colspan="2" style="text-align:center;font-weight:700;font-size:11pt;letter-spacing:.05em;background:#e8edf5;">CONTROL DE ASISTENCIA</td></tr>
            <tr>
                <td class="lbl">FECHA:</td>
                <td><?php echo $t->fecha_inicio ? date('d/m/Y', strtotime($t->fecha_inicio)) : ''; ?></td>
            </tr>
            <tr>
                <td class="lbl">RESPONSABLE:</td>
                <td><?php echo htmlspecialchars(trim(($t->facilitador_nombre ?? '') . ' ' . ($t->facilitador_apellido ?? ''))); ?></td>
            </tr>
            <tr>
                <td class="lbl">LUGAR:</td>
                <td><?php echo htmlspecialchars($t->ubicacion ?? ''); ?></td>
            </tr>
            <tr>
                <td class="lbl">ACTIVIDAD:</td>
                <td><?php echo htmlspecialchars($t->nombre ?? ''); ?></td>
            </tr>
        </table>
        <div class="logo-box">
            <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="IMATUR"
                 onerror="this.style.display='none'">
            <div class="logo-label">IMATUR</div>
        </div>
    </div>

    <!-- Tabla de participantes -->
    <table class="asist">
        <thead>
            <tr>
                <th style="width:26px;">Nº</th>
                <th class="cedula">Nº de Cédula</th>
                <th class="nombre">Nombre y Apellido</th>
                <th class="edad">Edad</th>
                <th class="sexo">Sexo</th>
                <th class="inst">Institución / Empresa</th>
                <th class="correo">Correo Electrónico</th>
                <th class="telf">Teléfono</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $filaActual = 0;
            foreach ($hoja as $p):
                $filaActual++;
                $nro     = $offset + $filaActual;
                $esLibre = empty($p->id_persona);
                $cedula  = $esLibre ? ($p->cedula_libre  ?? '') : ($p->cedula ?? '');
                $nombre  = $esLibre ? trim(($p->nombre_libre ?? '') . ' ' . ($p->apellido_libre ?? '')) : trim(($p->nombre ?? '') . ' ' . ($p->apellido ?? ''));
                $edad    = $esLibre ? edadExt($p->fecha_nac_libre ?? null) : edadExt($p->fecha_nacimiento ?? null);
                $sexo    = $esLibre ? ($p->genero_libre ?? '') : ($p->genero ?? '');
                $sexoLbl = $sexo === 'M' ? 'M' : ($sexo === 'F' ? 'F' : '');
                $correo  = $esLibre ? '' : ($p->correo ?? '');
                $telefono= $esLibre ? '' : ($p->telefono ?? '');
                // Institución: no hay en el modelo de participante; usar tipo_ente del taller como referencia
                $inst    = $esLibre ? ($t->tipo_ente ?? '') : ($t->tipo_ente ?? '');
            ?>
            <tr>
                <td class="n"><?php echo $nro; ?></td>
                <td class="cedula"><?php echo htmlspecialchars($cedula); ?></td>
                <td class="nombre"><?php echo htmlspecialchars($nombre); ?></td>
                <td class="edad"><?php echo htmlspecialchars($edad); ?></td>
                <td class="sexo"><?php echo htmlspecialchars($sexoLbl); ?></td>
                <td class="inst"><?php echo htmlspecialchars($inst); ?></td>
                <td class="correo"><?php echo htmlspecialchars($correo); ?></td>
                <td class="telf"><?php echo htmlspecialchars($telefono); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php
            // Rellenar filas vacías hasta completar 35 por hoja
            $faltantes = $FILAS_POR_HOJA - count($hoja);
            for ($r = 0; $r < $faltantes; $r++):
                $nro = $offset + count($hoja) + $r + 1;
            ?>
            <tr>
                <td class="n"><?php echo $nro; ?></td>
                <td class="cedula"></td>
                <td class="nombre"></td>
                <td class="edad"></td>
                <td class="sexo"></td>
                <td class="inst"></td>
                <td class="correo"></td>
                <td class="telf"></td>
            </tr>
            <?php endfor; ?>
        </tbody>
    </table>
</div>
<?php $offset += count($hoja); endforeach; ?>

</body>
</html>
