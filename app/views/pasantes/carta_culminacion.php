<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carta de Culminación — <?php echo htmlspecialchars($data['pasante']->nombre . ' ' . $data['pasante']->apellido); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', -apple-system, Arial, sans-serif; font-size: 10.5pt; color: #1a2535; background: #eef1f6; line-height: 1.55; }
        .ctrl-bar { background: #f0f4ff; border-bottom: 1px solid #c7d2fe; padding: 11px 36px; display: flex; justify-content: space-between; align-items: center; }
        .ctrl-bar span { font-size: 9pt; color: #374151; }
        .ctrl-bar .btns { display: flex; gap: 8px; }
        .ctrl-btn { padding: 7px 18px; font-family: inherit; font-size: 9.5pt; font-weight: 600; border: none; border-radius: 5px; cursor: pointer; transition: background .15s; }
        .ctrl-btn--primary { background: #1a56db; color: #fff; }
        .ctrl-btn--primary:hover { background: #1648c0; }
        .ctrl-btn--ghost { background: #fff; color: #374151; border: 1px solid #d1d5db !important; }
        .ctrl-btn--ghost:hover { background: #f9fafb; }
        .page-wrap { max-width: 820px; margin: 28px auto; background: #fff; border-radius: 4px; box-shadow: 0 2px 16px rgba(0,0,0,.10); overflow: hidden; }
        .letter { padding: 40px 52px 44px; font-family: 'Times New Roman', Times, Georgia, serif; font-size: 11pt; color: #111; line-height: 1.7; }
        .letter-header { display: flex; align-items: center; gap: 14px; margin-bottom: 8px; }
        .letter-header img { height: 68px; width: auto; object-fit: contain; flex-shrink: 0; }
        .letter-header-text { flex: 1; text-align: center; font-family: Arial, sans-serif; }
        .letter-header-text p { font-size: 9.5pt; font-weight: 700; text-transform: uppercase; line-height: 1.7; color: #111; letter-spacing: .01em; margin: 0; }
        .letter-divider { border: none; border-top: 1.5px solid #111; margin: 8px 0 30px; }
        .letter-date { text-align: right; margin-bottom: 22px; }
        .letter-body p { text-align: justify; text-indent: 2em; margin-bottom: 16px; }
        .letter-signature { text-align: center; margin-top: 32px; }
        .letter-signature .farewell { margin-bottom: 54px; }
        .letter-signature .name { font-weight: 700; text-transform: uppercase; letter-spacing: .04em; font-size: 11pt; margin-bottom: 4px; }
        .letter-signature .resolution { font-size: 9.5pt; font-family: Arial, sans-serif; line-height: 1.5; color: #222; }
        .letter-footer { margin-top: 40px; padding-top: 8px; border-top: 1px solid #555; text-align: center; font-family: Arial, sans-serif; font-size: 8pt; color: #444; line-height: 1.5; }
        @media print {
            body { background: #fff; }
            .ctrl-bar { display: none !important; }
            .page-wrap { max-width: 100%; margin: 0; box-shadow: none; border-radius: 0; }
            .letter { padding: 28px 36px 32px; }
            @page { size: A4 portrait; margin: 1.5cm 1.8cm; }
        }
    </style>
</head>
<body>
<?php
$cfg = $data['config'] ?? [];
$v = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$p = $data['pasante'];

$horas = '';
if (!empty($p->fecha_inicio) && !empty($p->fecha_fin)) {
    $dias = (int)((strtotime($p->fecha_fin) - strtotime($p->fecha_inicio)) / 86400) + 1;
    $horas = $dias . ' días';
}
?>

<div class="ctrl-bar">
    <span>Carta de Culminación — <strong><?php echo htmlspecialchars($p->nombre . ' ' . $p->apellido); ?></strong></span>
    <div class="btns">
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">Imprimir</button>
    </div>
</div>

<div class="page-wrap">
    <div class="letter">

        <div class="letter-header">
            <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo.png" alt="Alcaldía">
            <div class="letter-header-text">
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
        <hr class="letter-divider">

        <p class="letter-date">Cumaná, <?php echo htmlspecialchars($data['fecha_hoy']); ?></p>

        <div class="letter-body">
            <p>
                Quienes suscriben, en representación del
                <strong>Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)</strong>,
                por medio de la presente hacen constar que el/la ciudadano/a
                <strong><?php echo htmlspecialchars(strtoupper($p->nombre . ' ' . $p->apellido)); ?></strong>,
                titular de la Cédula de Identidad N° <strong><?php echo htmlspecialchars($p->cedula); ?></strong>,
                cursante de la carrera de <strong><?php echo htmlspecialchars($p->carrera); ?></strong>
                en la institución <strong><?php echo htmlspecialchars($p->institucion); ?></strong>,
                realizó sus Pasantías Profesionales en esta institución.
            </p>
            <?php if (!empty($p->fecha_inicio) && !empty($p->fecha_fin)): ?>
            <p>
                El período de pasantías comprendió desde el
                <strong><?php echo date('d/m/Y', strtotime($p->fecha_inicio)); ?></strong>
                hasta el
                <strong><?php echo date('d/m/Y', strtotime($p->fecha_fin)); ?></strong>
                (<?php echo $horas; ?>), cumpliendo satisfactoriamente con las actividades asignadas
                por el departamento correspondiente.
            </p>
            <?php endif; ?>
            <?php if (!empty($p->evaluacion)): ?>
            <p>
                Evaluación obtenida: <strong><?php echo htmlspecialchars($p->evaluacion); ?></strong><?php if (!empty($p->nota)): ?>, con calificación de <strong><?php echo htmlspecialchars($p->nota); ?></strong><?php endif; ?>.
            </p>
            <?php endif; ?>
            <p>
                La presente constancia se expide a solicitud de la parte interesada, en
                Cumaná, a los <?php echo htmlspecialchars($data['fecha_hoy']); ?>.
            </p>
        </div>

        <div class="letter-signature">
            <p class="farewell">Atentamente</p>
            <p class="name"><?php echo $v('director_nombre') . ' ' . $v('director_apellido'); ?></p>
            <p class="resolution">
                <?php echo htmlspecialchars($v('director_cargo') ?: 'Director General'); ?><br>
                Resolución N° <?php echo $v('resolucion_numero'); ?>
                de fecha <?php echo $v('resolucion_fecha'); ?>, publicada en<br>
                Gaceta Municipal Extraordinaria N° <?php echo $v('gaceta_numero'); ?>
                de fecha <?php echo $v('gaceta_fecha'); ?>
            </p>
        </div>

        <div class="letter-footer">
            Calle Sucre N° 11, San Francísco, Parroquia Santa Inés, Municipio Sucre &mdash; Edo. Sucre<br>
            Telf.: <?php echo $v('telf_institucion') ?: '(0293) 431-4073'; ?>
            &nbsp;&nbsp; Correo: <?php echo $v('correo_institucion') ?: 'imatur.cumana@gmail.com'; ?>
        </div>

    </div>
</div>

</body>
</html>
