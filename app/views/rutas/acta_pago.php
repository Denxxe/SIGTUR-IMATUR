<?php
/**
 * Acta de pago en efectivo — T-C (mig. 083).
 *
 * ⚠️ **ES UNA PROPUESTA NUESTRA.** Sobre este documento el cliente dijo:
 * *«el formato nace del momento, **pueden darnos una idea**, pero funciona como
 * respaldo de que el servicio fue pagado»* (R-39). Es el único documento del
 * módulo que diseñamos en vez de replicar, así que se somete a su visto bueno.
 *
 * El criterio para armarlo: lo mínimo que hace de un papel un respaldo válido —
 * quién recibió, de quién, cuánto, por qué concepto y en qué fecha, con las dos
 * firmas. Nada de adornos que después haya que justificar.
 *
 * El monto va **en letras** además de en cifras (`Util::montoALetras()`, la
 * misma que ya usa el documento de donación de Bienes): es lo que hace que un
 * recibo no se pueda alterar con un trazo.
 *
 * Vista standalone: sin `header.php` ni Bootstrap. `@page` sin margen y el aire
 * en el padding, como el resto de los imprimibles.
 */
$pg   = $data['pago'];
$ej   = $data['ejecucion'];
$ruta = $data['ruta'];
$cfg  = $data['config'] ?? [];
$cv   = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$v    = fn($x) => htmlspecialchars((string)$x);
$bs   = fn($n) => number_format((float)$n, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acta de pago <?php echo $v($pg->acta_numero); ?> — IMATUR-SUCRE</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', -apple-system, Arial, sans-serif; font-size: 11pt; color: #111; background: #e5e7eb; }
    .hoja { width: 21.6cm; min-height: 27.9cm; margin: 18px auto; background: #fff; padding: 1.6cm 2cm; box-shadow: 0 2px 16px rgba(0,0,0,.18); }

    .titulo { text-align: center; font-size: 13pt; font-weight: 700; letter-spacing: .12em; margin: 20px 0 4px; }
    .numero { text-align: center; font-family: 'Courier New', monospace; font-size: 11pt; font-weight: 700; margin-bottom: 22px; }

    .cuerpo p { text-align: justify; line-height: 1.85; margin-bottom: 14px; }
    .dato { font-weight: 700; border-bottom: 1px solid #111; padding: 0 4px; }

    table.det { width: 100%; border-collapse: collapse; margin: 18px 0 22px; }
    table.det th, table.det td { border: 1px solid #111; padding: 6px 9px; font-size: 10pt; }
    table.det th { background: #f3f4f6; text-align: left; width: 38%; text-transform: uppercase; font-size: 8.5pt; letter-spacing: .03em; }

    .monto-caja { border: 2px solid #111; padding: 10px 14px; margin: 4px 0 20px; }
    .monto-caja .cifra { font-size: 16pt; font-weight: 800; }
    .monto-caja .letras { font-size: 9.5pt; text-transform: uppercase; margin-top: 3px; }

    .firmas { display: flex; gap: 60px; margin-top: 60px; }
    .firmas div { flex: 1; text-align: center; }
    .firmas .linea { border-top: 1px solid #111; padding-top: 5px; font-size: 9pt; text-transform: uppercase; }
    .firmas .quien { font-size: 8.5pt; color: #444; }

    .pie { margin-top: 30px; font-size: 8pt; color: #555; text-align: center; line-height: 1.6; }

    .aviso-propuesta {
        margin-top: 26px; padding: 8px 12px; border: 1px dashed #b45309;
        background: #fffbeb; color: #92400e; font-size: 8.5pt; line-height: 1.5;
    }

    .barra { position: sticky; top: 0; z-index: 10; background: #16407A; color: #fff; padding: 10px 18px; display: flex; gap: 10px; align-items: center; }
    .barra a, .barra button { background: #fff; color: #16407A; border: none; border-radius: 6px; padding: 7px 16px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; font-family: inherit; }
    .barra .aviso { font-size: 12px; opacity: .9; margin-right: auto; }

    @page { margin: 0; }
    @media print {
        body { background: #fff; }
        .barra, .aviso-propuesta { display: none !important; }
        .hoja { width: auto; min-height: 0; margin: 0; box-shadow: none; padding: 1.5cm 2cm; }
    }
</style>
</head>
<body>

<div class="barra">
    <span class="aviso">Formato propuesto por nosotros — a validar con IMATUR (R-39)</span>
    <button onclick="window.print()">Imprimir</button>
    <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo (int)$ej->id; ?>">Volver</a>
</div>

<div class="hoja">

    <?php $mb = ['alto_logo' => 66, 'tamano' => 10, 'margen_inf' => 0];
          require '../app/views/inc/membrete.php'; ?>

    <div class="titulo">Acta de Pago</div>
    <div class="numero"><?php echo $v($pg->acta_numero); ?></div>

    <div class="cuerpo">
        <p>
            Quien suscribe, en representación del <strong>Instituto Municipal Autónomo de Turismo
            (IMATUR-SUCRE)</strong>, hace constar que en esta misma fecha se recibió de
            <span class="dato"><?php echo $v($pg->pagador_nombre ?: '_____________________________'); ?></span><?php if (!empty($pg->pagador_cedula)): ?>,
            titular de la cédula de identidad N° <span class="dato"><?php echo $v($pg->pagador_cedula); ?></span><?php endif; ?>,
            la cantidad que se detalla a continuación, <strong>en efectivo</strong>, por concepto del
            servicio de recorrido turístico que se indica.
        </p>

        <div class="monto-caja">
            <div class="cifra">Bs <?php echo $bs($pg->monto_bs); ?></div>
            <div class="letras"><?php echo $v(Util::montoALetras((float)$pg->monto_bs)); ?></div>
            <?php if ($pg->monto_usd !== null): ?>
            <div style="font-size:9pt;color:#444;margin-top:4px;">
                Equivalente a <strong>$ <?php echo number_format((float)$pg->monto_usd, 2); ?></strong>
                <?php if ($pg->tasa_aplicada !== null): ?>
                    a la tasa de <?php echo $bs($pg->tasa_aplicada); ?> Bs/$ vigente para la salida.
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>

        <table class="det">
            <tr><th>Recorrido</th><td><?php echo $v($ruta->nombre); ?></td></tr>
            <tr><th>Fecha de la salida</th><td><?php echo date('d/m/Y', strtotime($ej->fecha)); ?></td></tr>
            <tr><th>Grupo o solicitante</th><td><?php echo $v($ej->institucion_nombre ?: $ej->origen); ?></td></tr>
            <?php if (!empty($pg->personas)): ?>
            <tr><th>Personas que cubre</th><td><?php echo (int)$pg->personas; ?></td></tr>
            <?php endif; ?>
            <tr><th>Fecha del pago</th><td><?php echo date('d/m/Y', strtotime($pg->fecha)); ?></td></tr>
            <?php if (!empty($pg->observaciones)): ?>
            <tr><th>Observaciones</th><td><?php echo $v($pg->observaciones); ?></td></tr>
            <?php endif; ?>
        </table>

        <p>
            Se expide la presente acta como <strong>respaldo de que el servicio fue pagado</strong>, en
            Cumaná, a los <?php echo $v(Util::fechaEnLetras($pg->fecha)); ?>.
        </p>
    </div>

    <div class="firmas">
        <div>
            <div class="linea">Recibido por IMATUR</div>
            <div class="quien"><?php echo $cv('director_nombre'); ?></div>
        </div>
        <div>
            <div class="linea">Conforme — quien paga</div>
            <div class="quien"><?php echo $v($pg->pagador_nombre ?: ''); ?></div>
        </div>
    </div>

    <?php if (!empty($cfg['rutas_cuenta_cobro']['valor'])): ?>
    <div class="pie">
        Cuenta de IMATUR para el cobro de rutas: <?php echo $cv('rutas_cuenta_cobro'); ?>
    </div>
    <?php endif; ?>

    <div class="aviso-propuesta">
        <strong>Nota interna (no se imprime).</strong> Este formato es una <strong>propuesta</strong>:
        sobre el acta de pago el cliente dijo «pueden darnos una idea» (R-39). Lleva lo mínimo que
        hace de un papel un respaldo válido — quién recibió, de quién, cuánto (en cifras y en letras),
        por qué concepto y cuándo, con las dos firmas. Si IMATUR pide cambios, se ajusta solo esta
        vista: el correlativo y el registro del pago no se tocan.
    </div>
</div>

</body>
</html>
