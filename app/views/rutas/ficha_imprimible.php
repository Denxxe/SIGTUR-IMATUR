<?php
/**
 * Ficha Institucional — imprimible (T-G, mig. 080).
 *
 * Réplica del formato que IMATUR llena a mano
 * (`docs/formatos/rutas_ficha_institucional_IMATUR.jpeg`): mismas casillas, mismo
 * orden, mismos rótulos. Los dos bloques van lado a lado como en el papel, con
 * los renglones en blanco que sobren, para que se pueda completar a mano si hace
 * falta — así funciona hoy y así lo reconoce quien lo recibe.
 *
 * Vista **standalone**: no incluye `header.php`, así que no hay Bootstrap. Todo
 * el estilo va aquí. `@page` sin margen (convención del proyecto): el aire lo
 * pone el padding de la hoja, para que Chrome no imprima su URL en el borde.
 */
$ruta   = $data['ruta'];
$ejec   = $data['ejecucion'];
$ficha  = $data['ficha'];
$grupos = $data['grupos'] ?? [];
$tot    = $data['totales'];
$v      = fn($x) => htmlspecialchars((string)$x);

$instituciones = array_values(array_filter($grupos, fn($g) => $g->tipo === RutaFicha::TIPO_INSTITUCION));
$apoyos        = array_values(array_filter($grupos, fn($g) => $g->tipo === RutaFicha::TIPO_APOYO));

// El papel trae 8 renglones a la izquierda y 4 de apoyo a la derecha. Se
// conservan en blanco: es parte del formato, no relleno.
$FILAS_INST  = max(8, count($instituciones));
$FILAS_APOYO = max(4, count($apoyos));

$edades = function ($g): string {
    $min = $g->edad_min ?? null; $max = $g->edad_max ?? null;
    if ($min === null && $max === null) return '';
    if ($min !== null && $max !== null) return "{$min} a {$max} años";
    return $min !== null ? "desde {$min} años" : "hasta {$max} años";
};
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ficha Institucional — <?php echo $v($ruta->nombre); ?></title>
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
        font-family: 'Segoe UI', -apple-system, Arial, sans-serif;
        font-size: 10.5pt; color: #111; background: #e5e7eb;
    }
    .hoja {
        width: 21.6cm; min-height: 27.9cm; margin: 18px auto; background: #fff;
        padding: 1.6cm 1.8cm; box-shadow: 0 2px 16px rgba(0,0,0,.18);
    }
    .titulo {
        text-align: center; font-size: 11pt; font-weight: 700;
        letter-spacing: .14em; margin: 14px 0 18px;
    }
    /* Cabecera: rótulo + línea de puntos, como en el papel */
    .campo { display: flex; align-items: flex-end; gap: 6px; margin-bottom: 7px; font-size: 10pt; }
    .campo__rotulo { font-weight: 700; text-transform: uppercase; white-space: nowrap; letter-spacing: .02em; }
    .campo__valor {
        flex: 1; border-bottom: 1px solid #111; min-height: 15px;
        padding: 0 4px 1px; font-family: Georgia, 'Times New Roman', serif;
    }
    .campo--corto .campo__valor { flex: 0 0 4.6cm; }

    .bloques { display: flex; gap: 10px; margin-top: 14px; align-items: flex-start; }
    .bloques > div { flex: 1; }

    table.f { width: 100%; border-collapse: collapse; }
    table.f th, table.f td { border: 1px solid #111; padding: 3px 5px; font-size: 9pt; height: 22px; }
    table.f th {
        text-align: center; font-weight: 700; text-transform: uppercase;
        font-size: 8pt; letter-spacing: .03em; background: #fff;
    }
    table.f td.n { text-align: center; font-family: Georgia, serif; }
    .apoyo-head {
        background: #dff0d0; text-align: center; font-weight: 700;
        text-transform: uppercase; font-size: 8pt; letter-spacing: .03em;
    }
    .total-row td { font-weight: 800; }
    .total-row td.lbl { text-align: right; text-transform: uppercase; letter-spacing: .04em; }

    .notas { margin-top: 16px; font-size: 9pt; }
    .notas h4 { font-size: 8.5pt; text-transform: uppercase; letter-spacing: .05em; margin-bottom: 3px; }
    .notas p { border-bottom: 1px solid #ccc; min-height: 15px; padding-bottom: 2px; margin-bottom: 8px; }

    .firmas { display: flex; gap: 60px; margin-top: 46px; }
    .firmas div { flex: 1; text-align: center; }
    .firmas .linea { border-top: 1px solid #111; padding-top: 4px; font-size: 8.5pt; text-transform: uppercase; }

    .pie-emision { margin-top: 26px; font-size: 7.5pt; color: #666; text-align: center; }

    .barra {
        position: sticky; top: 0; z-index: 10; background: #16407A; color: #fff;
        padding: 10px 18px; display: flex; gap: 10px; align-items: center; justify-content: center;
    }
    .barra a, .barra button {
        background: #fff; color: #16407A; border: none; border-radius: 6px;
        padding: 7px 16px; font-size: 13px; font-weight: 700; cursor: pointer;
        text-decoration: none; font-family: inherit;
    }
    .barra .aviso { font-size: 12px; opacity: .9; margin-right: auto; }

    @page { margin: 0; }
    @media print {
        body { background: #fff; }
        .barra { display: none !important; }
        .hoja { width: auto; min-height: 0; margin: 0; box-shadow: none; padding: 1.4cm 1.6cm; }
    }
</style>
</head>
<body>

<div class="barra">
    <span class="aviso">
        <?php echo $ficha->estado === RutaFicha::EST_CERRADA
            ? 'Ficha cerrada' . (!empty($ficha->fecha_cierre) ? ' el ' . date('d/m/Y', strtotime($ficha->fecha_cierre)) : '')
            : 'BORRADOR — la ficha todavía se puede editar'; ?>
    </span>
    <button onclick="window.print()">Imprimir</button>
    <a href="<?php echo URL_ROOT; ?>/rutas/informe/<?php echo (int)$ejec->id; ?>">Volver</a>
</div>

<div class="hoja">

    <?php $mb = ['alto_logo' => 62, 'tamano' => 9.5, 'margen_inf' => 0];
          require '../app/views/inc/membrete.php'; ?>

    <div class="titulo">Ficha Institucional</div>

    <div class="campo">
        <span class="campo__rotulo">Recorrido:</span>
        <span class="campo__valor"><?php echo $v($ruta->nombre); ?></span>
        <span class="campo__rotulo" style="margin-left:14px;">Fecha:</span>
        <span class="campo__valor" style="flex:0 0 3.2cm;"><?php echo date('d-m-Y', strtotime($ejec->fecha)); ?></span>
    </div>
    <div class="campo">
        <span class="campo__rotulo">Encargado:</span>
        <span class="campo__valor"><?php echo $v($data['encargado'] ?? ''); ?></span>
    </div>
    <div class="campo">
        <span class="campo__rotulo">Colegio ó Institución:</span>
        <span class="campo__valor"><?php echo $v($ejec->institucion_nombre ?? ''); ?></span>
    </div>
    <div class="campo">
        <span class="campo__rotulo">Responsable:</span>
        <span class="campo__valor"><?php echo $v($ficha->responsable_nombre ?? ''); ?></span>
    </div>

    <div class="bloques">
        <!-- Bloque izquierdo: instituciones y su conteo de niños -->
        <div>
            <table class="f">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:42%;">Institución</th>
                        <th colspan="2">Niños</th>
                        <th rowspan="2" style="width:22%;">Edades</th>
                        <th rowspan="2" style="width:15%;">Total A.</th>
                    </tr>
                    <tr><th style="width:11%;">F</th><th style="width:11%;">M</th></tr>
                </thead>
                <tbody>
                <?php for ($i = 0; $i < $FILAS_INST; $i++):
                    $g = $instituciones[$i] ?? null; ?>
                    <tr>
                        <td><?php echo $g ? $v($g->nombre) : ''; ?></td>
                        <td class="n"><?php echo $g && (int)$g->femenino  ? (int)$g->femenino  : ''; ?></td>
                        <td class="n"><?php echo $g && (int)$g->masculino ? (int)$g->masculino : ''; ?></td>
                        <td class="n" style="font-size:8pt;"><?php echo $g ? $v($edades($g)) : ''; ?></td>
                        <td class="n"><?php echo $g ? ((int)$g->femenino + (int)$g->masculino) ?: '' : ''; ?></td>
                    </tr>
                <?php endfor; ?>
                </tbody>
            </table>
        </div>

        <!-- Bloque derecho: acompañantes e instituciones de apoyo -->
        <div>
            <table class="f">
                <thead>
                    <tr>
                        <th rowspan="2" style="width:48%;">Acompañantes</th>
                        <th colspan="2">Sexo</th>
                        <th rowspan="2" style="width:20%;">Total A.</th>
                    </tr>
                    <tr><th style="width:16%;">F</th><th style="width:16%;">M</th></tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Docentes</td>
                        <td class="n"><?php echo (int)$ficha->docentes_f ?: ''; ?></td>
                        <td class="n"><?php echo (int)$ficha->docentes_m ?: ''; ?></td>
                        <td class="n"><?php echo ((int)$ficha->docentes_f + (int)$ficha->docentes_m) ?: ''; ?></td>
                    </tr>
                    <tr>
                        <td>Representantes</td>
                        <td class="n"><?php echo (int)$ficha->representantes_f ?: ''; ?></td>
                        <td class="n"><?php echo (int)$ficha->representantes_m ?: ''; ?></td>
                        <td class="n"><?php echo ((int)$ficha->representantes_f + (int)$ficha->representantes_m) ?: ''; ?></td>
                    </tr>
                    <tr><td colspan="4" class="apoyo-head">Instituciones de apoyo</td></tr>
                <?php for ($i = 0; $i < $FILAS_APOYO; $i++):
                    $g = $apoyos[$i] ?? null; ?>
                    <tr>
                        <td><?php echo $g ? $v($g->nombre) : ''; ?></td>
                        <td class="n"><?php echo $g && (int)$g->femenino  ? (int)$g->femenino  : ''; ?></td>
                        <td class="n"><?php echo $g && (int)$g->masculino ? (int)$g->masculino : ''; ?></td>
                        <td class="n"><?php echo $g ? ((int)$g->femenino + (int)$g->masculino) ?: '' : ''; ?></td>
                    </tr>
                <?php endfor; ?>
                    <tr class="total-row">
                        <td colspan="3" class="lbl">Total</td>
                        <td class="n"><?php echo (int)$tot['total']; ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <?php if (!empty($ficha->resumen_visita) || !empty($ficha->observaciones)): ?>
    <div class="notas">
        <?php if (!empty($ficha->resumen_visita)): ?>
            <h4>Reseña de la visita</h4>
            <p><?php echo nl2br($v($ficha->resumen_visita)); ?></p>
        <?php endif; ?>
        <?php if (!empty($ficha->observaciones)): ?>
            <h4>Observaciones</h4>
            <p><?php echo nl2br($v($ficha->observaciones)); ?></p>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <div class="firmas">
        <div><div class="linea">Encargado del recorrido</div></div>
        <div><div class="linea">Responsable del grupo</div></div>
    </div>

    <div class="pie-emision">
        Emitido en Cumaná el <?php echo Util::fechaEnLetras(date('Y-m-d')); ?>
        · <?php echo $v($ruta->nombre); ?> — salida del <?php echo date('d/m/Y', strtotime($ejec->fecha)); ?>
    </div>
</div>

</body>
</html>
