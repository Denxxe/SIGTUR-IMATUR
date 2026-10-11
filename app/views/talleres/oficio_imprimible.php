<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', -apple-system, Arial, sans-serif;
            font-size: 10.5pt;
            color: #1a2535;
            background: #eef1f6;
            line-height: 1.55;
        }

        /* ── CTRL BAR (screen only) ───────────────────────── */
        .ctrl-bar {
            background: #f0f4ff;
            border-bottom: 1px solid #c7d2fe;
            padding: 11px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .ctrl-bar span { font-size: 9pt; color: #374151; }
        .ctrl-bar .btns { display: flex; gap: 8px; }
        .ctrl-btn {
            padding: 7px 18px;
            font-family: inherit;
            font-size: 9.5pt;
            font-weight: 600;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background .15s;
        }
        .ctrl-btn--primary { background: #1a56db; color: #fff; }
        .ctrl-btn--primary:hover { background: #1648c0; }
        .ctrl-btn--ghost { background: #fff; color: #374151; border: 1px solid #d1d5db !important; }
        .ctrl-btn--ghost:hover { background: #f9fafb; }

        /* ── LETTER WRAPPER ───────────────────────────────── */
        .page-wrap {
            max-width: 820px;
            margin: 28px auto;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 16px rgba(0,0,0,.10);
            overflow: hidden;
        }

        /* ── LETTER BODY ──────────────────────────────────── */
        .letter {
            padding: 40px 52px 44px;
            font-family: 'Times New Roman', Times, Georgia, serif;
            font-size: 11pt;
            color: #111;
            line-height: 1.7;
        }

        .letter-header {
            display: flex;
            align-items: center;
            gap: 14px;
            margin-bottom: 8px;
        }
        .letter-header img {
            height: 68px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }
        .letter-header-text {
            flex: 1;
            text-align: center;
            font-family: Arial, sans-serif;
        }
        .letter-header-text p {
            font-size: 9.5pt;
            font-weight: 700;
            text-transform: uppercase;
            line-height: 1.7;
            color: #111;
            letter-spacing: .01em;
            margin: 0;
        }
        .letter-divider {
            border: none;
            border-top: 1.5px solid #111;
            margin: 8px 0 30px;
        }

        .letter-date { text-align: right; margin-bottom: 22px; }

        .letter-oficio { margin-bottom: 18px; }
        .letter-oficio span { font-weight: 700; }

        .letter-recipient { margin-bottom: 22px; line-height: 1.5; }
        .letter-recipient strong { display: block; }

        .letter-body p {
            text-align: justify;
            text-indent: 2em;
            margin-bottom: 16px;
        }

        .letter-signature {
            text-align: center;
            margin-top: 32px;
        }
        .letter-signature .farewell { margin-bottom: 54px; }
        .letter-signature .name {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .04em;
            font-size: 11pt;
            margin-bottom: 4px;
        }
        .letter-signature .resolution {
            font-size: 9.5pt;
            font-family: Arial, sans-serif;
            line-height: 1.5;
            color: #222;
        }

        .letter-footer {
            margin-top: 40px;
            padding-top: 8px;
            border-top: 1px solid #555;
            text-align: center;
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #444;
            line-height: 1.5;
        }

        /* ── PRINT ────────────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .ctrl-bar { display: none !important; }
            .page-wrap {
                max-width: 100%;
                margin: 0;
                box-shadow: none;
                border-radius: 0;
            }
            /* El margen de la hoja lo da este padding y NO @page: con margen de
               página, Chrome usa esa franja para su propio encabezado
               (fecha · título · URL). En un oficio eso no puede salir. */
            .letter { padding: 1.5cm 1.8cm; }
            @page { size: A4 portrait; margin: 0; }
        }
    </style>
</head>
<body>

<?php
$cfg = $data['config'] ?? [];
$v = fn(string $k) => htmlspecialchars($cfg[$k]['valor'] ?? '');
$of     = $data['oficio'];
$taller = $data['taller'];
// Párrafos del texto guardado (separados por línea en blanco); se imprime escapado.
$parrafos = array_filter(array_map('trim', preg_split('/\n\s*\n/', str_replace("\r\n", "\n", (string)$of->cuerpo))));
?>

<!-- Control bar (hidden on print) -->
<div class="ctrl-bar">
    <span>Oficio N° <strong><?php echo htmlspecialchars($of->numero); ?></strong> — <?php echo htmlspecialchars($taller->nombre ?? ''); ?></span>
    <div class="btns">
        <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Volver</button>
        <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">🖨 Imprimir</button>
    </div>
</div>

<div class="page-wrap">
    <div class="letter">

        <!-- Institutional header -->
        <?php require '../app/views/inc/membrete.php'; ?>

        <!-- Date -->
        <p class="letter-date">Cumaná, <?php echo htmlspecialchars($data['fecha_txt']); ?></p>

        <!-- Oficio number -->
        <div class="letter-oficio">
            Oficio N°: <span><?php echo htmlspecialchars($of->numero); ?></span>
        </div>

        <!-- Recipient -->
        <div class="letter-recipient">
            Ciudadano:<br>
            <strong><?php echo htmlspecialchars($of->destinatario_nombre); ?></strong>
            <?php if (!empty($of->destinatario_cargo)): ?>
            <?php echo htmlspecialchars($of->destinatario_cargo); ?><br>
            <?php endif; ?>
            Su Despacho. &ndash;
        </div>

        <!-- Body -->
        <div class="letter-body">
            <?php foreach ($parrafos as $p): ?>
                <p><?php echo nl2br(htmlspecialchars($p)); ?></p>
            <?php endforeach; ?>
        </div>

        <!-- Signature -->
        <div class="letter-signature">
            <p class="farewell">Atentamente</p>
            <p class="name">
                <?php echo $v('director_nombre') . ' ' . $v('director_apellido'); ?>
            </p>
            <p class="resolution">
                Resolución N&ordm; <?php echo $v('resolucion_numero'); ?>
                de fecha <?php echo $v('resolucion_fecha'); ?>, publicada en<br>
                Gaceta Municipal Extraordinaria N&ordm;
                <?php echo $v('gaceta_numero'); ?>
                de fecha <?php echo $v('gaceta_fecha'); ?>
            </p>
        </div>

        <!-- Footer -->
        <div class="letter-footer">
            Calle Sucre N° 11, San Francísco, Parroquia Santa Inés, Municipio Sucre &mdash; Edo. Sucre<br>
            Telf.: <?php echo $v('telf_institucion') ?: '(0293) 431-4073'; ?>
            &nbsp;&nbsp; Correo: <?php echo $v('correo_institucion') ?: 'imatur.cumana@gmail.com'; ?>
        </div>

    </div>
</div>

</body>
</html>
