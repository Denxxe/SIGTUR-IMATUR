<?php
/**
 * Oficio de relación de bienes nuevos a la Alcaldía — vista imprimible.
 *
 * Réplica del formato entregado por la Directora de Bienes el 2026-09-15:
 * docs/formatos/oficio_relacion_bienes_nuevos_alcaldia_2026-06-10.jpg
 * (Oficio N° 179/2026, dirigido al Coordinador de Bienes y Materias).
 *
 * El cuerpo y la tabla son los del papel: tres columnas —cantidad, descripción
 * y monto en Bs—. Si el cliente confirma el formato del procedimiento nuevo
 * (PLAN_MODULO_BIENES.md §2-ter), se agrega una columna de código: el dato ya
 * viaja en $items.
 *
 * Estilos en línea a propósito: es una vista standalone que no carga el CSS
 * del sistema y tiene que sobrevivir a window.print().
 */
$cfg   = $data['config'] ?? [];
$v     = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$rel   = $data['relacion'];
$items = $data['items'] ?? [];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto',
          'septiembre','octubre','noviembre','diciembre'];
$ts        = strtotime($rel->fecha);
$fechaEsp  = date('j', $ts) . ' de ' . ucfirst($meses[(int)date('n', $ts) - 1]) . ' de ' . date('Y', $ts);
$montoTotal = 0.0;
foreach ($items as $it) $montoTotal += (float)($it->costo_adquisicion ?? 0);
$bs = fn($n) => number_format((float)$n, 2, ',', '.');
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Oficio <?php echo htmlspecialchars($rel->numero); ?> — Relación de bienes</title>
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
    .letter { padding: 40px 52px 44px; font-family: 'Times New Roman', Times, Georgia, serif;
              font-size: 11pt; color: #111; line-height: 1.7; }

    .letter-oficio    { margin: 18px 0 4px; font-weight: 700; }
    .letter-date      { text-align: right; margin-top: -22px; }
    .letter-recipient { margin: 16px 0 20px; line-height: 1.5; }
    .letter-recipient strong { display: block; }
    .letter-body p    { text-align: justify; text-indent: 2.2em; margin-bottom: 12px; }

    table.bienes { width: 100%; border-collapse: collapse; margin: 18px 0 20px; font-size: 10pt; }
    table.bienes th, table.bienes td { border: 1px solid #111; padding: 6px 8px; vertical-align: middle; }
    table.bienes th { text-align: center; font-weight: 700; }
    table.bienes td.cant  { text-align: center; width: 12%; }
    table.bienes td.monto { text-align: right;  width: 20%; white-space: nowrap; }
    table.bienes td.desc  { text-align: center; }
    table.bienes tfoot td { font-weight: 700; }

    .letter-signature { margin-top: 54px; text-align: center; }
    .letter-signature .farewell { margin-bottom: 52px; }
    .letter-signature .name  { font-weight: 700; text-transform: uppercase; }
    .letter-signature .cargo { font-weight: 700; text-transform: uppercase; }
    .letter-signature .resolution { font-size: 9.5pt; margin-top: 2px; line-height: 1.45; }

    .letter-footer { margin-top: 34px; padding-top: 10px; border-top: 1px solid #999;
                     text-align: center; font-size: 8.5pt; color: #444; }
    .nota-anulado { margin: 14px 0; padding: 8px 12px; border: 1px dashed #b91c1c;
                    color: #b91c1c; font-size: 9.5pt; text-align: center; }

    @media print {
        body { background: #fff; }
        .ctrl-bar { display: none; }
        .page-wrap { max-width: none; margin: 0; box-shadow: none; border-radius: 0; }
        .letter { padding: 1.5cm 1.8cm; }
        @page { size: A4 portrait; margin: 0; }
        table.bienes { page-break-inside: auto; }
        table.bienes tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<div class="ctrl-bar">
    <span>Oficio N° <strong><?php echo htmlspecialchars($rel->numero); ?></strong>
          — <?php echo count($items); ?> bien(es)</span>
    <div class="btns">
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<div class="page-wrap">
    <div class="letter">

        <?php require '../app/views/inc/membrete.php'; ?>

        <div class="letter-oficio">Oficio N° <?php echo htmlspecialchars($rel->numero); ?></div>
        <p class="letter-date">Cumaná, <?php echo htmlspecialchars($fechaEsp); ?></p>

        <div class="letter-recipient">
            Ciudadano:<br>
            <strong><?php echo htmlspecialchars($rel->destinatario_nombre); ?></strong>
            <?php if (!empty($rel->destinatario_cargo)): ?>
                <?php echo htmlspecialchars($rel->destinatario_cargo); ?><br>
            <?php endif; ?>
            <?php if (!empty($rel->destinatario_ente)): ?>
                <?php echo htmlspecialchars($rel->destinatario_ente); ?><br>
            <?php endif; ?>
            Su despacho.&ndash;
        </div>

        <?php if (!$rel->is_active): ?>
            <div class="nota-anulado">
                OFICIO ANULADO<?php echo !empty($rel->anulado_motivo)
                    ? ' — ' . htmlspecialchars($rel->anulado_motivo) : ''; ?>
            </div>
        <?php endif; ?>

        <div class="letter-body">
            <p>
                Reciba de antemano un cordial saludo de parte de quienes conforman el equipo del
                Instituto Municipal Autónomo de Turismo (IMATUR SUCRE), adscrito a la Alcaldía del
                Municipio Sucre del estado Sucre.
            </p>
            <p>
                Sirva la presente para en esta oportunidad hacer de su conocimiento que IMATUR SUCRE
                tal y consta de copias certificadas de facturas de compra, adquirió bienes muebles que
                se describen a continuación en tabla contigua, motivo por el cual se le solicita a la
                Coordinación que dirige, les sean asignados los respectivos códigos, para su inclusión
                en el Formulario BM-1, correspondiente al Inventario de Bienes Muebles que usufructúa
                y custodia IMATUR SUCRE, en estricto cumplimiento de las normas y demás leyes que
                rigen la materia.
            </p>
        </div>

        <table class="bienes">
            <thead>
                <tr>
                    <th>CANTIDAD</th>
                    <th>DESCRIPCIÓN DEL BIEN</th>
                    <th>MONTO EN Bs</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="3" style="text-align:center;">Sin bienes relacionados.</td></tr>
                <?php else: foreach ($items as $it): ?>
                    <tr>
                        <td class="cant">1</td>
                        <td class="desc"><?php echo htmlspecialchars(Inventario::descripcionOficial($it)); ?></td>
                        <td class="monto">
                            <?php echo $it->costo_adquisicion !== null ? $bs($it->costo_adquisicion) : 'S/P'; ?>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <?php if ($montoTotal > 0): ?>
            <tfoot>
                <tr>
                    <td class="cant"><?php echo count($items); ?></td>
                    <td style="text-align:right;">TOTAL</td>
                    <td class="monto"><?php echo $bs($montoTotal); ?></td>
                </tr>
            </tfoot>
            <?php endif; ?>
        </table>

        <?php if (!empty($rel->observacion)): ?>
            <div class="letter-body"><p><?php echo nl2br(htmlspecialchars($rel->observacion)); ?></p></div>
        <?php endif; ?>

        <div class="letter-body">
            <p>
                Agradeciendo de antemano su atención y en espera de una respuesta satisfactoria,
                se suscribe.
            </p>
        </div>

        <div class="letter-signature">
            <p class="farewell">Atentamente,</p>
            <p class="name"><?php echo $v('director_nombre') . ' ' . $v('director_apellido'); ?></p>
            <p class="cargo"><?php echo $v('director_cargo') ?: 'Presidenta'; ?></p>
            <p class="resolution">
                Resolución N&ordm; <?php echo $v('resolucion_numero'); ?>
                de fecha <?php echo $v('resolucion_fecha'); ?>, publicada en<br>
                Gaceta Municipal Extraordinaria N&ordm; <?php echo $v('gaceta_numero'); ?>
                de fecha <?php echo $v('gaceta_fecha'); ?>
            </p>
        </div>

        <div class="letter-footer">
            <?php echo $v('direccion_institucion') ?: 'Calle Sucre N° 11, Parroquia Santa Inés, Municipio Sucre — Edo. Sucre'; ?><br>
            Telf.: <?php echo $v('telf_institucion') ?: '(0293) 431-4073'; ?>
            &nbsp;&nbsp; Correo: <?php echo $v('correo_institucion') ?: 'imatur.cumana@gmail.com'; ?>
        </div>

    </div>
</div>

</body>
</html>
