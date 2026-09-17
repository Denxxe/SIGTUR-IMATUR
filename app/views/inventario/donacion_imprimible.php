<?php
/**
 * Documento de donación de un bien — vista imprimible.
 *
 * Réplica del formato entregado por la Directora de Bienes el 2026-09-15:
 * docs/formatos/documento_donacion_bien_2026-02-18.jpg
 *
 * Es un acto jurídico, no un oficio: el donante declara y la Presidenta acepta,
 * en un solo texto corrido, con las cantidades en letras y en números y la
 * fecha escrita en letras. Por eso no lleva número de oficio ni destinatario,
 * y termina en dos bloques de firma (donante con huellas · Presidenta con
 * sello), más el visado del abogado arriba cuando está configurado.
 *
 * Vale como título del bien cuando no hay factura de compra, que es justamente
 * el caso que describe el formato recibido.
 */
$cfg  = $data['config'] ?? [];
$v    = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$bien = $data['bien'];

$fechaActo   = $bien->donacion_fecha ?: ($bien->fecha_adquisicion ?: date('Y-m-d'));
$fechaLetras = Util::fechaEnLetras($fechaActo);
$bs          = fn($n) => number_format((float)$n, 2, ',', '.');

$valorBs  = $bien->costo_adquisicion !== null ? (float)$bien->costo_adquisicion : null;
$valorUsd = $bien->donacion_valor_usd !== null ? (float)$bien->donacion_valor_usd : null;

$nombrePresidenta = trim(($cfg['director_nombre_completo']['valor'] ?? '') ?: (
    ($cfg['director_nombre']['valor'] ?? '') . ' ' . ($cfg['director_apellido']['valor'] ?? '')
));
$visadorNombre = $cfg['abogado_visador_nombre']['valor'] ?? '';
$visadorIpsa   = $cfg['abogado_visador_ipsa']['valor'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Donación — <?php echo htmlspecialchars($bien->nombre); ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', -apple-system, Arial, sans-serif; font-size: 10.5pt;
           color: #1a2535; background: #eef1f6; line-height: 1.55; }

    .ctrl-bar { background: #f0f4ff; border-bottom: 1px solid #c7d2fe; padding: 11px 36px;
                display: flex; justify-content: space-between; align-items: center; }
    .ctrl-bar span { font-size: 9pt; color: #374151; }
    .ctrl-bar .btns { display: flex; gap: 8px; }
    .ctrl-btn { padding: 7px 18px; font-family: inherit; font-size: 9.5pt; font-weight: 600;
                border: none; border-radius: 5px; cursor: pointer; }
    .ctrl-btn--primary { background: #1a56db; color: #fff; }
    .ctrl-btn--ghost   { background: #fff; color: #374151; border: 1px solid #d1d5db !important; }

    .page-wrap { max-width: 820px; margin: 28px auto; background: #fff; border-radius: 4px;
                 box-shadow: 0 2px 16px rgba(0,0,0,.10); overflow: hidden; }
    .letter { padding: 44px 58px 48px; font-family: 'Times New Roman', Times, Georgia, serif;
              font-size: 12pt; color: #111; line-height: 2.0; }

    .visado { margin-bottom: 40px; text-align: center; }
    .visado .linea { border-bottom: 1px solid #111; width: 300px; margin: 44px auto 4px; }
    .visado .rotulo { font-size: 10pt; letter-spacing: .18em; }

    .cuerpo { text-align: justify; text-indent: 2.5em; }
    .cuerpo strong { font-weight: 700; }

    .firmas { margin-top: 70px; display: flex; gap: 40px; justify-content: space-between; }
    .firma  { flex: 1; text-align: center; font-size: 10.5pt; line-height: 1.5; }
    .firma .linea { border-bottom: 1px solid #111; margin-bottom: 6px; height: 56px; }
    .firma .rotulo { font-weight: 700; text-transform: uppercase; }
    .huellas { margin-top: 10px; font-size: 8.5pt; color: #555; }

    .aviso-falta { margin: 0 0 22px; padding: 9px 13px; border: 1px dashed #b45309;
                   background: #fffbeb; color: #92400e; font-family: Arial, sans-serif;
                   font-size: 9.5pt; text-indent: 0; line-height: 1.5; }

    @media print {
        body { background: #fff; }
        .ctrl-bar, .aviso-falta { display: none; }
        .page-wrap { max-width: none; margin: 0; box-shadow: none; border-radius: 0; }
        .letter { padding: 2cm 2.2cm; }
        @page { size: A4 portrait; margin: 0; }
    }
</style>
</head>
<body>

<div class="ctrl-bar">
    <span>Documento de donación — <?php echo htmlspecialchars($bien->nombre); ?></span>
    <div class="btns">
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<div class="page-wrap">
    <div class="letter">

        <?php
        // Datos sin los cuales el documento queda con un hueco. No se bloquea
        // la impresión —a veces se imprime para completar a mano—, pero se
        // avisa en pantalla; el aviso no sale impreso.
        $faltan = [];
        if (empty($bien->donante_estado_civil))  $faltan[] = 'estado civil del donante';
        if (empty($bien->donante_domicilio))     $faltan[] = 'domicilio del donante';
        if (empty($bien->donacion_procedencia))  $faltan[] = 'procedencia del bien (por qué no hay factura)';
        if ($valorBs === null)                   $faltan[] = 'valor estimado en Bs';
        if ($nombrePresidenta === '')            $faltan[] = 'nombre del firmante institucional (Configuración)';
        if (empty($cfg['director_cedula']['valor'])) $faltan[] = 'cédula del firmante (Configuración)';
        if ($faltan):
        ?>
        <div class="aviso-falta">
            <strong>Faltan datos para que el documento quede completo:</strong>
            <?php echo htmlspecialchars(implode(' · ', $faltan)); ?>.
            Este aviso no se imprime.
        </div>
        <?php endif; ?>

        <?php if ($visadorNombre !== ''): ?>
        <div class="visado">
            <div class="linea"></div>
            <div><?php echo htmlspecialchars($visadorNombre); ?></div>
            <div class="rotulo">A B O G A D O</div>
            <?php if ($visadorIpsa !== ''): ?>
                <div style="font-size:10pt;">IPSA N° <?php echo htmlspecialchars($visadorIpsa); ?></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <p class="cuerpo">
            Yo, <strong><?php echo htmlspecialchars(mb_strtoupper($bien->donante, 'UTF-8')); ?></strong>,
            venezolano, titular de la cédula de identidad N°
            <strong><?php echo htmlspecialchars($bien->donante_cedula); ?></strong>,
            mayor de edad, civilmente hábil, de este domicilio<?php
                echo !empty($bien->donante_estado_civil)
                    ? ', de estado civil ' . htmlspecialchars(mb_strtolower($bien->donante_estado_civil, 'UTF-8'))
                    : ''; ?>, por medio del presente instrumento, declaro: Que he tomado la
            determinación, en forma voluntaria, libre, y sin apremio de ninguna especie, en el
            sentido de dar bajo la forma de <strong>DONACIÓN</strong>, de manera auténtica e
            irrevocable, pero además pura y simple, al <strong>Instituto Municipal Autónomo de
            Turismo (IMATUR Sucre)</strong>,
            <strong><?php echo htmlspecialchars(Inventario::descripcionOficial($bien)); ?></strong>.
            <?php if (!empty($bien->donacion_procedencia)): ?>
                <?php echo htmlspecialchars($bien->donacion_procedencia); ?>
            <?php endif; ?>
            A los fines legales respectivos estimo el valor de los indicados objetos en la cantidad de
            <?php if ($valorBs !== null): ?>
                <strong><?php echo htmlspecialchars(Util::montoALetras($valorBs)); ?>
                (Bs. <?php echo $bs($valorBs); ?>)</strong><?php
                if ($valorUsd !== null): ?> que equivalen a
                <?php echo htmlspecialchars(Util::montoALetras($valorUsd, 'DÓLARES AMERICANOS', 'CENTAVOS')); ?>
                (<?php echo $bs($valorUsd); ?> $)<?php endif; ?>.
            <?php else: ?>
                <strong>_______________________________________</strong>.
            <?php endif; ?>
            Y yo, <strong><?php echo htmlspecialchars(mb_strtoupper($nombrePresidenta, 'UTF-8')); ?></strong>,
            titular de la cédula de identidad N°
            <strong><?php echo $v('director_cedula'); ?></strong>, mayor de edad, civilmente hábil,
            de este domicilio, en mi carácter de
            <strong><?php echo htmlspecialchars(mb_strtoupper($v('director_cargo') ?: 'PRESIDENTA', 'UTF-8')); ?>
            de IMATUR Sucre</strong>, designada mediante Resolución N°
            <?php echo $v('resolucion_numero'); ?> de fecha <?php echo $v('resolucion_fecha'); ?>,
            publicada en Gaceta Municipal Extraordinaria N° <?php echo $v('gaceta_numero'); ?>
            de fecha <?php echo $v('gaceta_fecha'); ?>, en uso de las atribuciones que me confiere la
            Ordenanza de Creación de IMATUR Sucre, expreso: Que acepto la <strong>DONACIÓN</strong>
            de los objetos ut supra señalados, que me hace el ciudadano
            <strong><?php echo htmlspecialchars(mb_strtoupper($bien->donante, 'UTF-8')); ?></strong>,
            arriba identificado. En Cumaná, <?php echo htmlspecialchars($fechaLetras); ?>.
        </p>

        <div class="firmas">
            <div class="firma">
                <div class="linea"></div>
                <div class="rotulo"><?php echo htmlspecialchars($bien->donante); ?></div>
                <div>C.I. <?php echo htmlspecialchars($bien->donante_cedula); ?></div>
                <div class="huellas">Huellas dactilares</div>
            </div>
            <div class="firma">
                <div class="linea"></div>
                <div class="rotulo"><?php echo htmlspecialchars($nombrePresidenta); ?></div>
                <div><?php echo $v('director_cargo') ?: 'Presidenta'; ?> de IMATUR Sucre</div>
                <div class="huellas">Sello institucional</div>
            </div>
        </div>

    </div>
</div>

</body>
</html>
