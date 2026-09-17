<?php
/**
 * Acta de Desincorporación — vista imprimible.
 *
 * ⚠️ PROVISIONAL. El formato oficial todavía no lo entregó el cliente (§3.4 del
 * BACKLOG, R-2). Esto NO inventa un formato oficial: usa el membrete
 * institucional compartido y una estructura mínima y defendible —qué bienes,
 * por qué salen, quién los entrega y quién los recibe—, que es lo que el
 * levantamiento describe (B-39 + §2-ter). Cuando llegue el formato real se
 * sustituye SOLO este archivo: la tabla, el flujo y lo ya registrado no se tocan.
 *
 * Estilos en línea a propósito: vista standalone que no carga el CSS del
 * sistema y tiene que sobrevivir a window.print().
 */
$cfg   = $data['config'] ?? [];
$v     = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$acta  = $data['acta'];
$items = $data['items'] ?? [];

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto',
          'septiembre','octubre','noviembre','diciembre'];
$ts       = strtotime($acta->fecha);
$fechaEsp = date('j', $ts) . ' de ' . ucfirst($meses[(int)date('n', $ts) - 1]) . ' de ' . date('Y', $ts);
$firmada  = !empty($acta->fecha_firma);
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Acta de Desincorporación <?php echo htmlspecialchars($acta->numero); ?></title>
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

    .aviso { background: #fffbeb; border-bottom: 1px solid #fde68a; color: #92400e;
             padding: 9px 36px; font-size: 9pt; }

    .page-wrap { max-width: 860px; margin: 28px auto; background: #fff; border-radius: 4px;
                 box-shadow: 0 2px 16px rgba(0,0,0,.10); overflow: hidden; }
    .letter { padding: 40px 52px 44px; font-family: 'Times New Roman', Times, Georgia, serif;
              font-size: 11pt; color: #111; line-height: 1.7; }

    .doc-titulo { text-align: center; font-weight: 700; text-transform: uppercase;
                  letter-spacing: .04em; margin: 16px 0 2px; font-size: 13pt; }
    .doc-numero { text-align: center; font-weight: 700; margin-bottom: 18px; }
    .letter-body p { text-align: justify; text-indent: 2.2em; margin-bottom: 12px; }

    table.bienes { width: 100%; border-collapse: collapse; margin: 16px 0 18px; font-size: 9.5pt; }
    table.bienes th, table.bienes td { border: 1px solid #111; padding: 5px 7px; vertical-align: middle; }
    table.bienes th { text-align: center; font-weight: 700; }
    table.bienes td.c { text-align: center; white-space: nowrap; }
    table.bienes tfoot td { font-weight: 700; }

    .firmas { margin-top: 62px; display: flex; gap: 40px; justify-content: space-between; }
    .firma  { flex: 1; text-align: center; font-size: 10pt; line-height: 1.45; }
    .firma .linea { border-bottom: 1px solid #111; margin-bottom: 6px; height: 54px; }
    .firma .rotulo { font-weight: 700; text-transform: uppercase; }

    .sello-firmada { margin: 14px 0; padding: 8px 12px; border: 1px dashed #15803d;
                     color: #15803d; font-size: 9.5pt; text-align: center; }
    .nota-anulado  { margin: 14px 0; padding: 8px 12px; border: 1px dashed #b91c1c;
                     color: #b91c1c; font-size: 9.5pt; text-align: center; }

    .letter-footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #999;
                     text-align: center; font-size: 8.5pt; color: #444; }

    @media print {
        body { background: #fff; }
        .ctrl-bar, .aviso { display: none; }
        .page-wrap { max-width: none; margin: 0; box-shadow: none; border-radius: 0; }
        .letter { padding: 1.5cm 1.6cm; }
        @page { size: A4 portrait; margin: 0; }
        table.bienes { page-break-inside: auto; }
        table.bienes tr { page-break-inside: avoid; }
    }
</style>
</head>
<body>

<div class="ctrl-bar">
    <span>Acta N° <strong><?php echo htmlspecialchars($acta->numero); ?></strong>
          — <?php echo count($items); ?> bien(es)</span>
    <div class="btns">
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>
<div class="aviso">
    <strong>Hoja provisional.</strong> El formato oficial del acta aún no lo entregó el cliente;
    este documento se sustituirá por el real sin afectar lo registrado. Este aviso no se imprime.
</div>

<div class="page-wrap">
    <div class="letter">

        <?php require '../app/views/inc/membrete.php'; ?>

        <div class="doc-titulo">Acta de Desincorporación de Bienes Muebles</div>
        <div class="doc-numero">N° <?php echo htmlspecialchars($acta->numero); ?></div>

        <?php if (!$acta->is_active): ?>
            <div class="nota-anulado">
                ACTA ANULADA<?php echo !empty($acta->anulado_motivo)
                    ? ' — ' . htmlspecialchars($acta->anulado_motivo) : ''; ?>
            </div>
        <?php endif; ?>

        <div class="letter-body">
            <p>
                En Cumaná, a los <?php echo htmlspecialchars($fechaEsp); ?>, el
                <strong>Instituto Municipal Autónomo de Turismo (IMATUR SUCRE)</strong>, adscrito a la
                Alcaldía del Municipio Sucre del estado Sucre, deja constancia de la
                <strong>desincorporación</strong> de los bienes muebles que se describen a
                continuación, los cuales salen del inventario activo de la institución y quedan a la
                orden de la Alcaldía para su retiro
                <?php if (!empty($acta->motivo)): ?>
                    por el siguiente motivo: <strong><?php echo htmlspecialchars($acta->motivo); ?></strong>.
                <?php else: ?>.
                <?php endif; ?>
            </p>
        </div>

        <table class="bienes">
            <thead>
                <tr>
                    <th style="width:16%;">Código BN</th>
                    <th>Descripción del bien</th>
                    <th style="width:13%;">Condición</th>
                    <th style="width:14%;">Fecha de baja</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($items)): ?>
                    <tr><td colspan="4" class="c">Sin bienes en el acta.</td></tr>
                <?php else: foreach ($items as $it): ?>
                    <tr>
                        <td class="c"><?php echo htmlspecialchars($it->codigo_bn ?? 'S/N'); ?></td>
                        <td><?php echo htmlspecialchars(Inventario::descripcionOficial($it)); ?></td>
                        <td class="c"><?php echo htmlspecialchars($it->condicion ?? '—'); ?></td>
                        <td class="c"><?php echo $it->fecha_baja ? date('d/m/Y', strtotime($it->fecha_baja)) : '—'; ?></td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td class="c"><?php echo count($items); ?></td>
                    <td colspan="3">bien(es) desincorporado(s)</td>
                </tr>
            </tfoot>
        </table>

        <?php if (!empty($acta->observacion)): ?>
            <div class="letter-body"><p><?php echo nl2br(htmlspecialchars($acta->observacion)); ?></p></div>
        <?php endif; ?>

        <?php if ($firmada): ?>
            <div class="sello-firmada">
                Acta recibida y sellada por la Alcaldía el
                <strong><?php echo date('d/m/Y', strtotime($acta->fecha_firma)); ?></strong><?php
                echo !empty($acta->recibido_por) ? ' — ' . htmlspecialchars($acta->recibido_por) : ''; ?>.
                Los bienes figuran como retirados.
            </div>
        <?php endif; ?>

        <div class="firmas">
            <div class="firma">
                <div class="linea"></div>
                <div class="rotulo"><?php echo $v('director_nombre') . ' ' . $v('director_apellido'); ?></div>
                <div><?php echo $v('director_cargo') ?: 'Presidenta'; ?> — IMATUR Sucre</div>
            </div>
            <div class="firma">
                <div class="linea"></div>
                <div class="rotulo">Coordinación de Bienes</div>
                <div>IMATUR Sucre</div>
            </div>
            <div class="firma">
                <div class="linea"></div>
                <div class="rotulo">Recibe conforme</div>
                <div>Alcaldía del Municipio Sucre<br>(firma y sello)</div>
            </div>
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
