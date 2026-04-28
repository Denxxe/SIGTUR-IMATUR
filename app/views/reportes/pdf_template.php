<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title><?php echo $data['titulo'] ?? 'Reporte'; ?> - SIGTUR-IMATUR</title>
    <style>
        /* === RESET & BASE === */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-size: 10px;
            color: #1e293b;
            background: #fff;
            padding: 40px 50px;
            line-height: 1.4;
        }

        /* === ENCABEZADO INSTITUCIONAL === */
        .header-doc {
            border-bottom: 2px solid #3b82f6;
            padding-bottom: 20px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
        }
        .header-doc .logo-area h1 {
            font-size: 24px;
            font-weight: 900;
            color: #1e3a8a;
            letter-spacing: -0.02em;
            margin-bottom: 4px;
        }
        .header-doc .logo-area p {
            color: #64748b;
            font-size: 11px;
            font-weight: 500;
        }
        .header-doc .meta-area {
            text-align: right;
            font-size: 10px;
            color: #64748b;
        }

        /* === TÍTULO DEL REPORTE === */
        .report-title {
            background: #f8fafc;
            border-left: 4px solid #3b82f6;
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 0 4px 4px 0;
        }
        .report-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .report-title p {
            font-size: 11px;
            color: #475569;
        }

        /* === KPIs === */
        .kpi-row {
            display: flex;
            gap: 15px;
            margin-bottom: 25px;
        }
        .kpi-box {
            flex: 1;
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 12px;
            text-align: center;
        }
        .kpi-box .value {
            font-size: 24px;
            font-weight: 800;
            color: #2563eb;
            margin-bottom: 2px;
        }
        .kpi-box .label {
            font-size: 9px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        /* === TABLA === */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        thead th {
            background: #0f172a;
            color: #ffffff;
            padding: 10px 8px;
            text-align: left;
            font-weight: 700;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        tbody td {
            padding: 8px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
            vertical-align: top;
        }
        tbody tr:nth-child(even) {
            background: #fcfdfe;
        }
        .cell-bold {
            font-weight: 700;
            color: #0f172a;
        }

        /* === PIE DE PÁGINA === */
        .footer-doc {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
            font-size: 9px;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }

        /* === BOTONES (ocultos al imprimir) === */
        .no-print {
            margin-bottom: 30px;
            text-align: right;
            padding: 15px;
            background: #f8fafc;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        .no-print button {
            padding: 10px 20px;
            font-family: inherit;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            margin-left: 10px;
        }
        .btn-print {
            background: #2563eb;
            color: #fff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }
        .btn-print:hover { background: #1d4ed8; transform: translateY(-1px); }
        .btn-back {
            background: #fff;
            color: #475569;
            border: 1px solid #d1d5db !important;
        }
        .btn-back:hover { background: #f3f4f6; }

        /* === REGLAS DE IMPRESIÓN === */
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page {
                size: landscape;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body>

<!-- Botones de acción -->
<div class="no-print">
    <button class="btn-back" onclick="window.history.back()">← Regresar al Panel</button>
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir / Guardar PDF</button>
</div>

<!-- Encabezado Institucional -->
<div class="header-doc">
    <div class="logo-area">
        <h1>SIGTUR-IMATUR</h1>
        <p>Sistema Integral de Gestión Turística y Administrativa</p>
        <p>Instituto Municipal de Turismo — Cumaná, Estado Sucre</p>
    </div>
    <div class="meta-area">
        <p><strong>Fecha:</strong> <?php echo date('d/m/Y H:i'); ?></p>
        <p><strong>Usuario:</strong> <?php echo $_SESSION['user_username'] ?? 'Sistema'; ?></p>
        <p style="margin-top:4px; font-weight:700; color:#3b82f6;">DOCUMENTO OFICIAL</p>
    </div>
</div>

<!-- Título del Reporte -->
<div class="report-title">
    <h2><?php echo $data['titulo'] ?? 'Reporte SIGTUR'; ?></h2>
    <p><?php echo ($data['subtitulo'] ?? '') ?: 'Consolidado general de registros del sistema.'; ?></p>
</div>

<!-- KPIs -->
<?php if (!empty($data['kpis'])): ?>
<div class="kpi-row">
    <?php foreach ($data['kpis'] ?? [] as $label => $value): ?>
        <div class="kpi-box">
            <div class="value"><?php echo $value ?? 0; ?></div>
            <div class="label"><?php echo $label ?? 'Dato'; ?></div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Tabla de Datos -->
<table>
    <thead>
        <tr>
            <?php foreach ($data['headers'] ?? [] as $h): ?>
                <th><?php echo $h ?? '-'; ?></th>
            <?php endforeach; ?>
        </tr>
    </thead>
    <tbody>
        <?php if (empty($data['rows'] ?? [])): ?>
            <tr><td colspan="<?php echo count($data['headers'] ?? []); ?>" style="text-align:center; padding:40px; color:#94a3b8; font-style:italic; font-size:12px;">No se encontraron registros para los criterios seleccionados.</td></tr>
        <?php else: ?>
            <?php foreach ($data['rows'] ?? [] as $row): ?>
                <tr>
                    <?php foreach ($row as $cell): ?>
                        <td><?php echo htmlspecialchars($cell ?? ''); ?></td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </tbody>
</table>

<!-- Pie de Página -->
<div class="footer-doc">
    <span>Generado mediante plataforma SIGTUR-IMATUR © <?php echo date('Y'); ?></span>
    <span>Página 1 de 1 — Total registros: <?php echo count($data['rows'] ?? []); ?></span>
</div>

</body>
</html>
