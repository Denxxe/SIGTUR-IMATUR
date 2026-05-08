<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($data['titulo'] ?? 'Reporte'); ?> — IMATUR-SUCRE</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', -apple-system, BlinkMacSystemFont, Arial, sans-serif;
            font-size: 10.5pt;
            color: #1a2535;
            background: #eef1f6;
            line-height: 1.55;
        }

        /* ── WRAPPER ─────────────────────────────────────────────────── */
        .page-wrap {
            max-width: 980px;
            margin: 28px auto;
            background: #fff;
            border-radius: 4px;
            box-shadow: 0 2px 16px rgba(0,0,0,.10);
            overflow: hidden;
        }

        /* ── BARRA DE CONTROLES (pantalla) ───────────────────────────── */
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

        /* ── CABECERA INSTITUCIONAL ───────────────────────────────────── */
        .inst-header {
            padding: 20px 36px 16px;
            background: #fff;
            display: flex;
            align-items: center;
            gap: 20px;
            border-bottom: 3px solid #1a56db;
        }
        .inst-header img {
            height: 68px;
            width: auto;
            object-fit: contain;
            flex-shrink: 0;
        }
        .inst-header .inst-text {
            flex: 1;
            text-align: center;
        }
        .inst-header .inst-text h2 {
            font-size: 9.5pt;
            font-weight: 700;
            color: #111827;
            line-height: 1.65;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .inst-header .inst-text .rif {
            font-size: 9pt;
            font-weight: 600;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── BARRA TÍTULO DEL REPORTE ─────────────────────────────────── */
        .report-bar {
            background: #0f172a;
            padding: 15px 36px;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        .report-bar .rb-title {
            font-size: 13.5pt;
            font-weight: 700;
            color: #f1f5f9;
            letter-spacing: -0.01em;
        }
        .report-bar .rb-sub {
            font-size: 9pt;
            color: #7dd3fc;
            margin-top: 3px;
        }
        .report-bar .rb-meta {
            text-align: right;
            font-size: 8.5pt;
            color: #94a3b8;
            line-height: 1.7;
        }
        .report-bar .rb-meta strong { color: #cbd5e1; }
        .rb-badge {
            display: inline-block;
            margin-top: 5px;
            background: #1e40af;
            color: #bfdbfe;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            padding: 2px 8px;
            border-radius: 3px;
        }

        /* ── KPI STRIP ───────────────────────────────────────────────── */
        .kpi-strip {
            display: flex;
            border-bottom: 1px solid #e5e7eb;
            background: #fafbfd;
        }
        .kpi-item {
            flex: 1;
            padding: 14px 12px;
            text-align: center;
            border-right: 1px solid #e5e7eb;
        }
        .kpi-item:last-child { border-right: none; }
        .kpi-item .kv {
            font-size: 22pt;
            font-weight: 800;
            color: #1a56db;
            line-height: 1.1;
        }
        .kpi-item .kl {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── TABLA ───────────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
        }
        thead th {
            background: #f1f5f9;
            color: #374151;
            padding: 9px 14px;
            text-align: left;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            border-bottom: 2px solid #1a56db;
        }
        tbody td {
            padding: 8px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 10pt;
            color: #374151;
            vertical-align: top;
        }
        tbody tr:nth-child(even) td { background: #f8fafc; }
        tbody tr:last-child td { border-bottom: none; }
        .td-first { font-weight: 700; color: #111827; }
        .td-empty {
            text-align: center;
            padding: 40px 14px;
            color: #9ca3af;
            font-style: italic;
        }

        /* ── PIE INSTITUCIONAL ────────────────────────────────────────── */
        .inst-footer {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            padding: 10px 36px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .inst-footer .address {
            font-size: 7.5pt;
            color: #6b7280;
        }
        .inst-footer .generated {
            font-size: 8pt;
            color: #9ca3af;
        }
        .inst-footer .generated strong { color: #6b7280; }

        /* ── PRINT ────────────────────────────────────────────────────── */
        @media print {
            body { background: #fff; }
            .ctrl-bar { display: none !important; }
            .page-wrap {
                margin: 0;
                box-shadow: none;
                border-radius: 0;
                max-width: 100%;
            }
            @page {
                size: landscape;
                margin: 1.2cm 1.4cm;
            }
        }
    </style>
</head>
<body>

<div class="page-wrap">

    <!-- ── Controles de pantalla ── -->
    <div class="ctrl-bar">
        <span>
            <strong><?php echo htmlspecialchars($data['titulo'] ?? 'Reporte'); ?></strong>
            &nbsp;·&nbsp; Documento listo para imprimir o guardar como PDF
        </span>
        <div class="btns">
            <button class="ctrl-btn ctrl-btn--ghost" onclick="window.history.back()">← Regresar</button>
            <button class="ctrl-btn ctrl-btn--primary" onclick="window.print()">&#x1F5A8;&nbsp; Imprimir / Guardar PDF</button>
        </div>
    </div>

    <!-- ── Cabecera institucional ── -->
    <div class="inst-header">
        <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo.png" alt="Alcaldía de Cumaná">
        <div class="inst-text">
            <h2>
                República Bolivariana de Venezuela<br>
                Alcaldía Bolivariana del Municipio Sucre<br>
                Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE)<br>
                Cumaná, Estado Sucre
            </h2>
            <div class="rif">RIF. G-20008498-7</div>
        </div>
        <img src="<?php echo URL_ROOT; ?>/public/assets/images/Logo_imatur-removebg-preview.png" alt="IMATUR">
    </div>

    <!-- ── Barra título ── -->
    <div class="report-bar">
        <div>
            <div class="rb-title"><?php echo htmlspecialchars($data['titulo'] ?? 'Reporte'); ?></div>
            <div class="rb-sub"><?php echo htmlspecialchars(($data['subtitulo'] ?? '') ?: 'Consolidado general de registros del sistema.'); ?></div>
        </div>
        <div class="rb-meta">
            <div><strong>Fecha:</strong> <?php echo date('d/m/Y'); ?> &nbsp;<strong>Hora:</strong> <?php echo date('H:i'); ?></div>
            <div><strong>Usuario:</strong> <?php echo htmlspecialchars($_SESSION['user_username'] ?? 'Sistema'); ?></div>
            <div><span class="rb-badge">Documento Oficial</span></div>
        </div>
    </div>

    <!-- ── KPIs ── -->
    <?php if (!empty($data['kpis'])): ?>
    <div class="kpi-strip">
        <?php foreach ($data['kpis'] as $label => $value): ?>
        <div class="kpi-item">
            <div class="kv"><?php echo htmlspecialchars((string)($value ?? '0')); ?></div>
            <div class="kl"><?php echo htmlspecialchars($label); ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- ── Tabla ── -->
    <table>
        <thead>
            <tr>
                <?php foreach ($data['headers'] ?? [] as $h): ?>
                    <th><?php echo htmlspecialchars($h ?? '—'); ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($data['rows'] ?? [])): ?>
                <tr>
                    <td colspan="<?php echo count($data['headers'] ?? []); ?>" class="td-empty">
                        No se encontraron registros para los criterios seleccionados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($data['rows'] as $i => $row): ?>
                    <tr>
                        <?php foreach ($row as $j => $cell): ?>
                            <td <?php if ($j === 0) echo 'class="td-first"'; ?>>
                                <?php echo htmlspecialchars((string)($cell ?? '')); ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ── Pie institucional ── -->
    <div class="inst-footer">
        <div class="address">
            Calle Sucre N° 11, San Francisco, Parroquia Santa Inés, Municipio Sucre — Edo. Sucre
        </div>
        <div class="generated">
            Generado por SIGTUR-IMATUR © <?php echo date('Y'); ?>
            &nbsp;·&nbsp; Total registros: <strong><?php echo count($data['rows'] ?? []); ?></strong>
        </div>
    </div>

</div>
</body>
</html>
