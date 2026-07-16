<?php

/**
 * Escritor OOXML (.xlsx) multi-hoja, sin librerías externas (ZipArchive + XML
 * a mano) — mismo mecanismo y paleta de estilos que
 * ReportesController::descargarXlsx(), extendido para registrar varias hojas
 * en un mismo libro. Necesario para reproducir formatos oficiales que llegan
 * en varias hojas (Bono Vacacional: 4 tipos de personal + resumen).
 * Todas las celdas se escriben como texto (inlineStr) para no perder ceros a
 * la izquierda (cédulas, códigos) — misma convención que Reportes.
 */
class XlsxMultiSheet
{
    // Estilos (cellXfs) — mismos índices/semántica que ReportesController::descargarXlsx().
    const S_DEFAULT      = 0;
    const S_INSTITUCIONAL = 1;
    const S_TITULO       = 2;
    const S_META         = 3;
    const S_HEADER       = 4;
    const S_DATA         = 5;
    const S_ZEBRA        = 6;
    const S_TOTAL        = 7;

    /** Hojas ya cerradas: cada una ['nombre','xml','merges','widths']. */
    private array $sheets = [];

    private ?string $nombreActual = null;
    private int $rnum = 0;
    private string $filas = '';
    private array $mergesActual = [];
    private array $anchosActual = [];

    private static function colLetter(int $n): string
    {
        $s = '';
        while ($n > 0) { $m = ($n - 1) % 26; $s = chr(65 + $m) . $s; $n = intdiv($n - 1, 26); }
        return $s;
    }

    private static function esc($v): string
    {
        return htmlspecialchars((string)$v, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /** Abre una hoja nueva (cierra la anterior si había una abierta sin cerrar). */
    public function nuevaHoja(string $nombre): void
    {
        if ($this->nombreActual !== null) $this->cerrarHoja();
        $this->nombreActual = $nombre;
        $this->rnum = 0;
        $this->filas = '';
        $this->mergesActual = [];
        $this->anchosActual = [];
    }

    /** Fila con un único texto fusionado a lo ancho de $ncol columnas (A1:X1). */
    public function filaFusionada(string $texto, int $ncol, int $style = self::S_TITULO): void
    {
        $this->rnum++;
        $lastCol = self::colLetter(max(1, $ncol));
        $this->filas .= '<row r="' . $this->rnum . '"><c r="A' . $this->rnum . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
            . self::esc($texto) . '</t></is></c></row>';
        $this->mergesActual[] = 'A' . $this->rnum . ':' . $lastCol . $this->rnum;
    }

    /** Fila de celdas individuales (mismo estilo para toda la fila). */
    public function filaCeldas(array $celdas, int $style = self::S_DATA): void
    {
        $this->rnum++;
        $c = ''; $i = 0;
        foreach ($celdas as $v) {
            $i++;
            $c .= '<c r="' . self::colLetter($i) . $this->rnum . '" s="' . $style . '" t="inlineStr"><is><t xml:space="preserve">'
                . self::esc($v) . '</t></is></c>';
            $this->anchosActual[$i - 1] = max($this->anchosActual[$i - 1] ?? 8, strlen((string)$v));
        }
        $this->filas .= '<row r="' . $this->rnum . '">' . $c . '</row>';
    }

    /** Fila en blanco (separador visual entre secciones). */
    public function filaVacia(): void
    {
        $this->rnum++;
    }

    /** Membrete institucional estándar (mismo texto que ReportesController). */
    public function membrete(string $titulo, int $ncol, string $metaExtra = ''): void
    {
        $usuario = $_SESSION['user_username'] ?? 'Sistema';
        $this->filaFusionada('REPÚBLICA BOLIVARIANA DE VENEZUELA', $ncol, self::S_INSTITUCIONAL);
        $this->filaFusionada('ALCALDÍA BOLIVARIANA DEL MUNICIPIO SUCRE', $ncol, self::S_INSTITUCIONAL);
        $this->filaFusionada('Instituto Municipal Autónomo de Turismo (IMATUR-SUCRE) — RIF ' . ConfigSistema::rif(), $ncol, self::S_INSTITUCIONAL);
        $this->filaFusionada($titulo, $ncol, self::S_TITULO);
        $this->filaFusionada('Generado por ' . $usuario . ' · ' . date('d/m/Y H:i') . $metaExtra, $ncol, self::S_META);
        $this->filaVacia();
    }

    /** Cierra la hoja actual y la agrega al libro. */
    public function cerrarHoja(): void
    {
        if ($this->nombreActual === null) return;
        $this->sheets[] = [
            'nombre' => $this->sanitizarNombre($this->nombreActual),
            'xml'    => $this->filas,
            'merges' => $this->mergesActual,
            'widths' => $this->anchosActual,
        ];
        $this->nombreActual = null;
    }

    private function sanitizarNombre(string $n): string
    {
        $n = preg_replace('/[\[\]\:\\\\\/\?\*]/', '', $n);
        return mb_substr($n, 0, 31);
    }

    /** Arma el .xlsx con todas las hojas registradas y lo envía como descarga (termina la ejecución). */
    public function descargar(string $filename): void
    {
        $this->cerrarHoja();
        if (empty($this->sheets)) throw new Exception('No hay hojas para exportar.');

        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        $zip->open($tmp, ZipArchive::OVERWRITE);

        $overridesSheets = '';
        $sheetsWb = '';
        $relsWb = '';
        foreach ($this->sheets as $i => $s) {
            $n = $i + 1;
            $cols = '';
            foreach ($s['widths'] as $ci => $w) {
                $cols .= '<col min="' . ($ci + 1) . '" max="' . ($ci + 1) . '" width="' . min(60, max(10, $w + 3)) . '" customWidth="1"/>';
            }
            $mergeXml = $s['merges']
                ? '<mergeCells count="' . count($s['merges']) . '">' . implode('', array_map(fn($m) => '<mergeCell ref="' . $m . '"/>', $s['merges'])) . '</mergeCells>'
                : '';
            $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<cols>' . $cols . '</cols><sheetData>' . $s['xml'] . '</sheetData>' . $mergeXml . '</worksheet>';
            $zip->addFromString('xl/worksheets/sheet' . $n . '.xml', $sheetXml);
            $overridesSheets .= '<Override PartName="/xl/worksheets/sheet' . $n . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
            $sheetsWb .= '<sheet name="' . self::esc($s['nombre']) . '" sheetId="' . $n . '" r:id="rId' . $n . '"/>';
            $relsWb .= '<Relationship Id="rId' . $n . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . $n . '.xml"/>';
        }
        $stylesRid = count($this->sheets) + 1;
        $relsWb .= '<Relationship Id="rId' . $stylesRid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        $zip->addFromString('[Content_Types].xml',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . $overridesSheets
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
            . '<sheets>' . $sheetsWb . '</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels',
            '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $relsWb
            . '</Relationships>');
        $zip->addFromString('xl/styles.xml', $this->stylesXml());
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

    private function stylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<numFmts count="1"><numFmt numFmtId="164" formatCode="@"/></numFmts>'
            . '<fonts count="5">'
            . '<font><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="14"/><color rgb="FF1E3A8A"/><name val="Calibri"/></font>'
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
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            . '<xf numFmtId="0" fontId="1" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'
            . '<xf numFmtId="0" fontId="3" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'
            . '<xf numFmtId="0" fontId="4" fillId="0" borderId="0" applyFont="1" applyAlignment="1"><alignment horizontal="center"/></xf>'
            . '<xf numFmtId="0" fontId="2" fillId="2" borderId="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="1" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="164" fontId="0" fillId="3" borderId="1" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="top" wrapText="1"/></xf>'
            . '<xf numFmtId="0" fontId="1" fillId="3" borderId="1" applyFont="1" applyFill="1" applyBorder="1"/>'
            . '</cellXfs>'
            . '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>'
            . '</styleSheet>';
    }
}
