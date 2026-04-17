<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $data['titulo']; ?> - SIGTUR-IMATUR</title>
    <style>
        /* === RESET & BASE === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            font-size: 11px;
            color: #1e293b;
            background: #fff;
            padding: 20px 30px;
        }

        /* === ENCABEZADO INSTITUCIONAL === */
        .header-doc {
            border-bottom: 3px solid #1a73e8;
            padding-bottom: 15px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .header-doc .logo-area h1 {
            font-size: 20px;
            color: #1a73e8;
            letter-spacing: 1px;
        }
        .header-doc .logo-area p {
            color: #64748b;
            font-size: 10px;
            margin-top: 2px;
        }
        .header-doc .meta-area {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }

        /* === TÍTULO DEL REPORTE === */
        .report-title {
            background: #f1f5f9;
            border-left: 5px solid #1a73e8;
            padding: 10px 15px;
            margin-bottom: 15px;
        }
        .report-title h2 {
            font-size: 16px;
            color: #1e293b;
        }
        .report-title p {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }

        /* === KPIs === */
        .kpi-row {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        .kpi-box {
            flex: 1;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 10px;
            text-align: center;
        }
        .kpi-box .value {
            font-size: 22px;
            font-weight: 700;
            color: #1a73e8;
        }
        .kpi-box .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: .5px;
            margin-top: 2px;
        }

        /* === TABLA === */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
        }
        thead th {
            background: #1e293b;
            color: #fff;
            padding: 8px 6px;
            text-align: left;
            font-weight: 600;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }
        tbody td {
            padding: 6px;
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        /* === PIE DE PÁGINA === */
        .footer-doc {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* === BOTONES (ocultos al imprimir) === */
        .no-print {
            margin-bottom: 20px;
            text-align: right;
        }
        .no-print button {
            padding: 10px 25px;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            margin-left: 8px;
        }
        .btn-print {
            background: #1a73e8;
            color: #fff;
        }
        .btn-print:hover { background: #1557b0; }
        .btn-back {
            background: #e2e8f0;
            color: #475569;
        }
        .btn-back:hover { background: #cbd5e1; }

        /* === REGLAS DE IMPRESIÓN === */
        @media print {
            .no-print { display: none !important; }
            body { padding: 10px; }
            @page {
                size: landscape;
                margin: 1cm;
            }
        }
    </style>
</head>
<body>

<!-- Botones de acción (NO se imprimen) -->
<div class="no-print">
    <button class="btn-back" onclick="window.history.back()">← Volver al Reporte</button>
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar como PDF</button>
</div>

<!-- Encabezado Institucional -->
<div class="header-doc">
    <div class="logo-area">
        <h1>SIGTUR-IMATUR</h1>
        <p>Sistema Integral de Gestión Turística y Administrativa</p>
        <p>Instituto Municipal de Turismo</p>
    </div>
    <div class="meta-area">
        <p><strong>Fecha de generación:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        <p><strong>Generado por:</strong> <?php echo $_SESSION['user_username'] ?? 'Sistema'; ?></p>
        <p>Documento oficial</p>
    </div>
</div>

<!-- Título del Reporte -->
<div class="report-title">
    <h2><?php echo $data['titulo']; ?></h2>
    <p><?php echo $data['subtitulo']; ?></p>
</div>

<!-- KPIs -->
<?php if (!empty($data['kpis'])): ?>
<div class="kpi-row">
    <?php foreach ($data['kpis'] as $label => $value): ?>
        <div class="kpi-box">
            <div class="value"><?php echo $value; ?></div>
            <div class="label"><?php echo $label; ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tabla de Datos -->
<table>
    <thead>
        <tr>
            <?php foreach ($data['headers'] as $h): ?>
                <th><?php echo $h; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data['rows'])): ?>
            <tr><td colspan="<?php echo count($data['headers']); ?>" style="text-align:center; padding:20px; color:#94a3b8;">Sin datos disponibles para este reporte.</td></tr>
        <?php else: ?>
            <?php foreach ($data['rows'] as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo htmlspecialchars($cell); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pie de Página -->
<div class="footer-doc">
    <span>SIGTUR-IMATUR © <?php echo date('Y'); ?> — Documento generado automáticamente</span>
    <span>Total de registros: <?php echo count($data['rows']); ?></span>
</div>

</body>
</html>
