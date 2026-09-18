<?php
/**
 * Oficio de permiso a una institución custodia — imprimible (T-H, mig. 081).
 *
 * ⚠️ **PROVISIONAL.** El cliente no ha entregado el formato de este oficio
 * (R-20 describió el trámite, no el papel). Esto es una carta institucional
 * construida con el membrete estándar y el cuerpo que se desprende de lo que
 * el cliente contó: a quién se dirige, qué se pide y qué salidas cubre.
 *
 * Cuando llegue el formato oficial se sustituye **solo este archivo**: la tabla,
 * el correlativo, los estados y lo registrado no cambian. Mismo criterio que con
 * el Acta de Desincorporación de Bienes (mig. 077).
 *
 * Vista standalone: sin `header.php`, sin Bootstrap. `@page` sin margen, con el
 * aire en el padding de la hoja, como el resto de los imprimibles.
 */
$p    = $data['permiso'];
$sals = $data['salidas'] ?? [];
$cfg  = $data['config'] ?? [];
$cv   = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$v    = fn($x) => htmlspecialchars((string)$x);

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio',
          'agosto','septiembre','octubre','noviembre','diciembre'];
$ts    = strtotime($p->fecha);
$dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$fechaLarga = date('j', $ts) . ' de ' . $meses[(int)date('n', $ts) - 1] . ' de ' . date('Y', $ts);
$rango = date('j', strtotime($p->semana_desde)) . ' de ' . $meses[(int)date('n', strtotime($p->semana_desde)) - 1]
       . ' al ' . date('j', strtotime($p->semana_hasta)) . ' de ' . $meses[(int)date('n', strtotime($p->semana_hasta)) - 1]
       . ' de ' . date('Y', strtotime($p->semana_hasta));
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Permiso <?php echo $v($p->numero); ?> — IMATUR-SUCRE</title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Segoe UI', -apple-system, Arial, sans-serif; font-size: 11pt; color: #111; background: #e5e7eb; }
    .hoja { width: 21.6cm; min-height: 27.9cm; margin: 18px auto; background: #fff; padding: 1.6cm 2cm; box-shadow: 0 2px 16px rgba(0,0,0,.18); }

    .meta { text-align: right; font-size: 10pt; margin: 18px 0 4px; }
    .meta strong { font-family: 'Courier New', monospace; }
    .fecha { text-align: right; font-size: 10.5pt; margin-bottom: 26px; }

    .dest { margin-bottom: 22px; line-height: 1.5; }
    .dest .nombre { font-weight: 700; text-transform: uppercase; }
    .dest .cargo  { font-size: 10pt; }
    .dest .inst   { font-weight: 700; }

    .cuerpo p { text-align: justify; line-height: 1.7; margin-bottom: 14px; }

    table.sal { width: 100%; border-collapse: collapse; margin: 16px 0 20px; }
    table.sal th, table.sal td { border: 1px solid #111; padding: 5px 7px; font-size: 9.5pt; }
    table.sal th { background: #f3f4f6; text-transform: uppercase; font-size: 8.5pt; letter-spacing: .03em; }

    .firma { margin-top: 56px; text-align: center; }
    .firma .linea { border-top: 1px solid #111; width: 8cm; margin: 0 auto; padding-top: 5px; font-size: 10pt; }
    .firma .cargo { font-size: 9.5pt; color: #333; }

    .aviso-borrador {
        margin-top: 30px; padding: 8px 12px; border: 1px dashed #b45309;
        background: #fffbeb; color: #92400e; font-size: 8.5pt; line-height: 1.5;
    }

    .barra { position: sticky; top: 0; z-index: 10; background: #16407A; color: #fff; padding: 10px 18px; display: flex; gap: 10px; align-items: center; }
    .barra a, .barra button { background: #fff; color: #16407A; border: none; border-radius: 6px; padding: 7px 16px; font-size: 13px; font-weight: 700; cursor: pointer; text-decoration: none; font-family: inherit; }
    .barra .aviso { font-size: 12px; opacity: .9; margin-right: auto; }

    @page { margin: 0; }
    @media print {
        body { background: #fff; }
        .barra, .aviso-borrador { display: none !important; }
        .hoja { width: auto; min-height: 0; margin: 0; box-shadow: none; padding: 1.5cm 2cm; }
    }
</style>
</head>
<body>

<div class="barra">
    <span class="aviso">Formato provisional — el oficial no ha llegado</span>
    <button onclick="window.print()">Imprimir</button>
    <a href="<?php echo URL_ROOT; ?>/rutas/permiso/<?php echo (int)$p->id; ?>">Volver</a>
</div>

<div class="hoja">

    <?php $mb = ['alto_logo' => 66, 'tamano' => 10, 'margen_inf' => 0];
          require '../app/views/inc/membrete.php'; ?>

    <div class="meta">Oficio N° <strong><?php echo $v($p->numero); ?></strong></div>
    <div class="fecha">Cumaná, <?php echo $fechaLarga; ?></div>

    <div class="dest">
        <?php if (!empty($p->destinatario_nombre)): ?>
            <div class="nombre"><?php echo $v($p->destinatario_nombre); ?></div>
        <?php endif; ?>
        <?php if (!empty($p->destinatario_cargo)): ?>
            <div class="cargo"><?php echo $v($p->destinatario_cargo); ?></div>
        <?php endif; ?>
        <div class="inst"><?php echo $v($p->institucion); ?></div>
        <div style="font-size:10pt;">Su Despacho.—</div>
    </div>

    <div class="cuerpo">
        <p>
            Reciba un cordial saludo institucional de parte del <strong>Instituto Municipal Autónomo
            de Turismo (IMATUR-SUCRE)</strong>, en ocasión de dirigirme a usted para
            <strong>solicitar el permiso de acceso</strong> a las instalaciones bajo su custodia,
            en el marco de los recorridos turísticos que esta institución tiene programados
            durante la semana del <strong><?php echo $rango; ?></strong>.
        </p>

        <?php if (!empty($sals)): ?>
        <p>Las salidas para las cuales se solicita el acceso son las siguientes:</p>

        <table class="sal">
            <thead>
                <tr>
                    <th style="width:18%;">Fecha</th>
                    <th style="width:12%;">Hora</th>
                    <th>Recorrido</th>
                    <th style="width:30%;">Grupo</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($sals as $s):
                $t = strtotime($s->fecha); ?>
                <tr>
                    <td><?php echo $dias[(int)date('w', $t)] . ' ' . date('d/m/Y', $t); ?></td>
                    <td><?php echo !empty($s->hora) ? substr($s->hora, 0, 5) : '—'; ?></td>
                    <td><?php echo $v($s->ruta_nombre); ?></td>
                    <td><?php echo $v($s->institucion_nombre ?: $s->origen); ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>

        <?php if (!empty($p->observaciones) && $p->estado === PermisoRuta::EST_ESPERA): ?>
        <p><?php echo nl2br($v($p->observaciones)); ?></p>
        <?php endif; ?>

        <p>
            Agradecemos de antemano la receptividad y el apoyo que su institución ha brindado a la
            promoción del patrimonio histórico y turístico de nuestro municipio, y quedamos atentos
            a su respuesta.
        </p>
    </div>

    <div class="firma">
        <div class="linea"><?php echo $cv('director_nombre') ?: $v($p->responsable_nombre); ?></div>
        <div class="cargo">
            <?php echo $v($p->responsable_cargo ?: ($cfg['director_cargo']['valor'] ?? 'Presidenta')); ?><br>
            IMATUR-SUCRE
        </div>
    </div>

    <div class="aviso-borrador">
        <strong>Nota interna (no se imprime).</strong> Este formato es <strong>provisional</strong>:
        el cliente describió el trámite (R-20) pero no ha entregado el formato del oficio. Cuando
        llegue se reemplaza únicamente esta vista — el número, el flujo, los estados y las salidas
        cubiertas quedan como están.
    </div>
</div>

</body>
</html>
