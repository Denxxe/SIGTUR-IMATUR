<?php
/**
 * Helpers de exportación compartidos — CSV, XLSX y PDF
 *
 * Parte de `ReportesController`, separada en un trait por tamaño: el controlador
 * llegó a 3.405 líneas y 101 métodos. El trait mantiene `$this`, los métodos
 * privados y la API pública **exactamente iguales** — no hay ningún cambio de
 * comportamiento ni de firma, solo de archivo.
 *
 * No se autocarga: `ReportesController.php` lo incluye con `require_once`.
 */
trait ReportesExportTrait {

    // =========================================================================
    // HELPERS DE EXPORTACIÓN
    // =========================================================================

    /** Índice de columna (1→A, 27→AA) para celdas .xlsx */
    /**
     * "Emitido en Cumaná el 14 de septiembre de 2026 a las 9:31 a. m."
     * Misma redacción que el exportador del lado cliente (sigturExportarTabla),
     * para que el .xlsx y el PDF de un mismo listado no difieran en el pie.
     */
    private function pieEmitido(): string {
        $meses = ['', 'enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                  'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $h   = (int)date('G');
        $suf = $h < 12 ? 'a. m.' : 'p. m.';
        $h12 = $h % 12 ?: 12;
        return 'Emitido en Cumaná el ' . (int)date('j') . ' de ' . $meses[(int)date('n')]
             . ' de ' . date('Y') . ' a las ' . $h12 . ':' . date('i') . ' ' . $suf;
    }

    private function colLetter(int $n): string {
        $s = '';
        while ($n > 0) { $m = ($n - 1) % 26; $s = chr(65 + $m) . $s; $n = intdiv($n - 1, 26); }
        return $s;
    }

    /**
     * Exporta a un archivo .xlsx REAL (Office Open XML) con formato — sin
     * librerías externas (usa ZipArchive). Excel lo abre sin advertencias, con
     * membrete, encabezados en color, bordes, filas alternadas y total.
     * Todas las celdas son texto (preserva cédulas/códigos con ceros).
     * Mantiene el nombre exportCsv para no tocar los llamadores.
     */
    private function exportCsv($filename, $headers, $rows, ?string $tituloExplicito = null) {
        // El título sale del nombre de archivo salvo que el llamador lo indique.
        // Derivarlo del slug pierde tildes y mayúsculas ("Listado De Pasantes
        // Registrados"), así que los listados mandan el suyo tal cual.
        $titulo = $tituloExplicito !== null && trim($tituloExplicito) !== ''
            ? trim($tituloExplicito)
            : ucwords(str_replace('_', ' ', $filename));
        $ncol   = max(1, count($headers));
        [$sheetRows, $merges, $dataStart] = $this->construirHojaMembrete($titulo, $ncol,
            function ($rowMerged, $rowCells) use ($headers, $rows) {
                $rowCells($headers, 4);
                foreach ($rows as $i => $row) $rowCells(array_values((array)$row), ($i % 2 ? 6 : 5));
                $rowMerged('Total de registros: ' . count($rows), 7);
            },
            ' · ' . count($rows) . ' registro(s)'
        );

        // Ancho de columnas según contenido
        $widths = [];
        foreach ($headers as $i => $h) $widths[$i] = strlen((string)$h);
        foreach ($rows as $row) { $j = 0; foreach ((array)$row as $v) { $widths[$j] = max($widths[$j] ?? 8, strlen((string)$v)); $j++; } }

        $this->descargarXlsx($filename, $sheetRows, $merges, $widths, $dataStart);
    }

    /**
     * Exporta un .xlsx con MÚLTIPLES secciones en una sola hoja (cada una con su
     * propio título y su propio encabezado de columnas) — para reportes que no son
     * una tabla plana, como el Dossier de Taller. Comparte el mismo membrete
     * institucional y el mismo empaquetador que exportCsv().
     * $secciones = [['titulo' => .., 'headers' => [...], 'rows' => [...]], ...]
     */
    private function exportCsvSecciones($filename, $tituloReporte, array $secciones) {
        $ncol = max(1, ...array_map(fn($s) => count($s['headers']), $secciones));
        $widths = [];
        [$sheetRows, $merges, $dataStart] = $this->construirHojaMembrete($tituloReporte, $ncol,
            function ($rowMerged, $rowCells) use ($secciones, &$widths) {
                foreach ($secciones as $sec) {
                    $rowMerged($sec['titulo'], 2);
                    $rowCells($sec['headers'], 4);
                    foreach ($sec['headers'] as $i => $h) $widths[$i] = max($widths[$i] ?? 8, strlen((string)$h));
                    foreach ($sec['rows'] as $i => $row) {
                        $rowCells(array_values((array)$row), ($i % 2 ? 6 : 5));
                        $j = 0; foreach ((array)$row as $v) { $widths[$j] = max($widths[$j] ?? 8, strlen((string)$v)); $j++; }
                    }
                }
            }
        );
        $this->descargarXlsx($filename, $sheetRows, $merges, $widths, $dataStart);
    }

    /**
     * Arma el membrete institucional (REPÚBLICA/ALCALDÍA/IMATUR+RIF, título, autor/fecha)
     * y delega el cuerpo de la hoja al callback, que recibe los mismos constructores de
     * fila ($rowMerged para texto fusionado a lo ancho de $ncol columnas, $rowCells para
     * una fila de celdas con estilo) usados por el membrete. Devuelve [sheetRows, merges, dataStart].
     */
    private function construirHojaMembrete(string $titulo, int $ncol, callable $cuerpo, string $metaExtra = ''): array {
        $lastCol = $this->colLetter($ncol);
        $rnum = 0; $sheetRows = ''; $merges = [];
        $esc = fn($v) => htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
        $rowMerged = function (string $texto, int $style, ?float $alto = null) use (&$rnum, &$sheetRows, &$merges, $lastCol, $esc) {
            $rnum++;
            $rowAttrs = $alto !== null ? ' ht="' . $alto . '" customHeight="1"' : '';
            $sheetRows .= '<row r="' . $rnum . '"' . $rowAttrs . '><c r="A' . $rnum . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $esc($texto) . '</t></is></c></row>';
            $merges[] = 'A' . $rnum . ':' . $lastCol . $rnum;
        };
        $rowCells = function (array $cells, int $style) use (&$rnum, &$sheetRows, $esc) {
            $rnum++; $c = ''; $i = 0;
            foreach ($cells as $v) {
                $i++;
                $c .= '<c r="' . $this->colLetter($i) . $rnum . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">' . $esc($v) . '</t></is></c>';
            }
            $sheetRows .= '<row r="' . $rnum . '">' . $c . '</row>';
        };

        $usuario = $_SESSION['user_username'] ?? 'Sistema';
        // Mismas cinco líneas que app/views/inc/membrete.php y que
        // XlsxMultiSheet::membrete(): un solo membrete para todo el sistema.
        $rowMerged('REPÚBLICA BOLIVARIANA DE VENEZUELA', 1, 18);
        $rowMerged('ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE', 1, 18);
        $rowMerged('INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO (IMATUR-SUCRE)', 1, 18);
        $rowMerged('CUMANÁ, ESTADO SUCRE', 1, 18);
        $rowMerged('RIF. ' . ConfigSistema::rif(), 1, 18);
        $rnum++; $sheetRows .= '<row r="' . $rnum . '" ht="6" customHeight="1"/>'; // fila en blanco (aire para los logos)
        $rowMerged($titulo, 2, 24);
        // Mismo pie que el PDF: fecha en palabras y hora sin segundos.
        $rowMerged($this->pieEmitido() . $metaExtra, 3, 16);
        $rowMerged('Generado por ' . $usuario, 3, 14);
        $rnum++; $sheetRows .= '<row r="' . $rnum . '" ht="8" customHeight="1"/>'; // fila en blanco
        $dataStart = $rnum + 1;

        $cuerpo($rowMerged, $rowCells);

        return [$sheetRows, $merges, $dataStart];
    }

    private function descargarXlsx($filename, $sheetRows, array $merges, array $widths, int $dataStart) {
        $cols = '';
        foreach ($widths as $i => $w) { $cols .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . min(60, max(10, $w + 3)) . '" customWidth="1"/>'; }

        $mergeXml = $merges ? '<mergeCells count="' . count($merges) . '">' . implode('', array_map(fn($m) => '<mergeCell ref="' . $m . '"/>', $merges)) . '</mergeCells>' : '';

        // Logos institucionales (Alcaldía + IMATUR) anclados como imagen real.
        $piezas = XlsxLogos::piezasParaHoja(max(1, count($widths)));
        $drawingTag = !empty($piezas['drawingXml']) ? '<drawing r:id="rIdDrawing"/>' : '';

        $sheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheetViews><sheetView workbookViewId="0"><pane ySplit="' . ($dataStart - 1) . '" topLeftCell="A' . $dataStart . '" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<cols>' . $cols . '</cols><sheetData>' . $sheetRows . '</sheetData>' . $mergeXml . $drawingTag . '</worksheet>';

        // ── Estilos ───────────────────────────────────────────────────────────
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="@"/></numFmts>'
            . '<fonts count="5">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="16"/><color rgb="FF1E3A8A"/><name val="Calibri"/></font>'
            . '<font><sz val="9"/><color rgb="FF64748B"/><name val="Calibri"/></font>'
            . '</fonts>'
            . '<fills count="4">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF1E3A8A"/></patternFill></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>'
            . '</fills>'
            . '<borders count="2"><border><left/><right/><top/><bottom/><diagonal/></border>'
            . '<border><left style="thin"><color rgb="FFCBD5E1"/></left><right style="thin"><color rgb="FFCBD5E1"/></right><top style="thin"><color rgb="FFCBD5E1"/></top><bottom style="thin"><color rgb="FFCBD5E1"/></bottom></border></borders>'
            . '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            . '<cellXfs count="8">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'                                                                                  // 0 default
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'              // 1 inst
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'              // 2 título
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'              // 3 meta
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>' // 4 header
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'             // 5 data
            . '<xf numFmtId="164" fontId="0" fillId="3" borderId="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>' // 6 zebra
            . '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'                                        // 7 total
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';

        // ── Empaquetar .xlsx (ZIP) ────────────────────────────────────────────
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);
        if (!empty($piezas['drawingXml'])) {
            foreach ($piezas['media'] as $nombreImg => $bytes) {
                $zip->addFromString('xl/media/' . $nombreImg, $bytes);
            }
            $zip->addFromString('xl/drawings/drawing1.xml', $piezas['drawingXml']);
            $zip->addFromString('xl/drawings/_rels/drawing1.xml.rels', $piezas['relsXml']);
            $zip->addFromString('xl/worksheets/_rels/sheet1.xml.rels',
                '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
                . '<Relationship Id="rIdDrawing" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>'
                . '</Relationships>');
        }
        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . (!empty($piezas['drawingXml']) ? '<Default Extension="png" ContentType="image/png"/>' : '')
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . (!empty($piezas['drawingXml']) ? '<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>' : '')
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>');
        $zip->addFromString('_rels/.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/workbook.xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<sheets><sheet name="Reporte" sheetId="1" r:id="rId1"/></sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>');
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->close();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.xlsx"');
        header('Content-Length: ' . filesize($tmp));
        header('Pragma: no-cache');
        header('Expires: 0');
        readfile($tmp);
        @unlink($tmp);
        exit;
    }

    private function exportPdf($titulo, $subtitulo, $headers, $rows, $kpis = []) {
        $data = [
            'titulo'    => $titulo,
            'subtitulo' => $subtitulo,
            'headers'   => $headers,
            'rows'      => $rows,
            'kpis'      => $kpis,
        ];
        $this->view('reportes/pdf_template', $data);
    }
}
