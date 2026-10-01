<?php

/**
 * Helper compartido para insertar los logos institucionales reales
 * (Alcaldía + IMATUR) como imágenes en un .xlsx armado a mano (sin
 * librerías) — usado por XlsxMultiSheet y ReportesController::descargarXlsx()
 * para que el membrete de los reportes se vea igual que en constancias/
 * oficios (que sí usan <img>), en vez de solo texto plano.
 */
class XlsxLogos
{
    /**
     * Alto de destino común a ambos logos (px); el ancho se deriva de su
     * proporción real. Ambos logos son verticales (Alcaldía 204×278, IMATUR
     * 451×553), así que a poca altura quedan diminutos: a 46 px medían 34 y
     * 38 px de ancho y se perdían al lado del membrete. El bloque institucional
     * son 5 filas de 18 pt ≈ 120 px, de modo que 100 px lo acompaña sin
     * desbordarlo (quedan ~73 y ~82 px de ancho).
     */
    const ALTO_PX = 100;

    /** Margen (px) entre cada logo y el borde de la tabla. */
    const MARGEN_PX = 6;

    /**
     * Ancho mínimo (px) de la tabla para que el membrete no quede debajo de
     * los logos: la línea más larga («INSTITUTO MUNICIPAL AUTÓNOMO DE TURISMO
     * (IMATUR-SUCRE)», Calibri 11 negrita) mide ~410 px, y a cada lado va un
     * logo de hasta ~82 px más su margen.
     */
    const ANCHO_MIN_TABLA_PX = 620;

    /** Límites del ancho de columna, en caracteres (lo que Excel guarda en `<col width>`). */
    const COL_MIN = 10;
    const COL_MAX = 60;

    private static ?array $cache = null;

    private static function logos(): array
    {
        if (self::$cache !== null) return self::$cache;
        // Rutas desde ConfigSistema (fuente única). Antes apuntaban a mano a
        // `Logo.png` como "Alcaldía", pero ese archivo es el de IMATUR: las
        // exportaciones salían con el mismo logo a los dos lados.
        $defs = [
            ['archivo' => ConfigSistema::rutaLogoAlcaldia(), 'nombre' => 'LogoAlcaldia'],
            ['archivo' => ConfigSistema::rutaLogoImatur(),   'nombre' => 'LogoImatur'],
        ];
        $out = [];
        foreach ($defs as $d) {
            if (!is_readable($d['archivo'])) continue;
            $tam = @getimagesize($d['archivo']);
            if (!$tam) continue;
            [$w, $h] = $tam;
            $anchoPx = (int)round(self::ALTO_PX * ($w / $h));
            $out[] = [
                'nombre' => $d['nombre'],
                'bytes'  => file_get_contents($d['archivo']),
                'cx'     => $anchoPx * 9525,      // EMU (1px @96dpi = 9525 EMU)
                'cy'     => self::ALTO_PX * 9525,
            ];
        }
        return self::$cache = $out;
    }

    /** Píxeles que ocupa en pantalla una columna de $chars de ancho (Calibri 11, 96 dpi). */
    private static function px(float $chars): int
    {
        return (int)round($chars * 7 + 5);
    }

    /**
     * Ancho final de cada columna, en caracteres, a partir del contenido más
     * largo de cada una. Es la ÚNICA fórmula de ancho de los .xlsx con
     * membrete: el `<cols>` de la hoja y la posición de los logos tienen que
     * salir del mismo número, o el logo derecho cae en otro sitio.
     *
     * Si la tabla queda más angosta que ANCHO_MIN_TABLA_PX (pocas columnas o
     * cortas), el sobrante se reparte entre todas, para que los logos no
     * tapen el membrete.
     */
    public static function anchosColumnas(array $largosContenido): array
    {
        $anchos = [];
        foreach (array_values($largosContenido) as $w) {
            $anchos[] = (float)min(self::COL_MAX, max(self::COL_MIN, (int)$w + 3));
        }
        if (empty($anchos)) $anchos = [(float)self::COL_MIN];

        $total = array_sum(array_map([self::class, 'px'], $anchos));
        $falta = self::ANCHO_MIN_TABLA_PX - $total;
        if ($falta > 0) {
            $extra = ceil($falta / 7 / count($anchos) * 10) / 10;   // caracteres por columna, 1 decimal
            $anchos = array_map(fn($w) => $w + $extra, $anchos);
        }
        return $anchos;
    }

    /**
     * Piezas necesarias para insertar el membrete con logos en UNA hoja:
     * los bytes de cada imagen (xl/media), el XML del drawing y el .rels del
     * drawing. $anchos = el resultado de anchosColumnas() para esa hoja: el
     * logo de la Alcaldía va contra el borde izquierdo de la tabla y el de
     * IMATUR contra el borde DERECHO (no al inicio de la última columna, que
     * en una columna ancha lo dejaba en medio, encima del membrete).
     * Devuelve arrays vacíos si los logos no están disponibles (degrada sin
     * romper el resto del archivo).
     */
    public static function piezasParaHoja(array $anchos): array
    {
        $logos = self::logos();
        if (empty($logos)) return ['media' => [], 'drawingXml' => '', 'relsXml' => ''];

        $pxCols = array_map([self::class, 'px'], array_values($anchos) ?: [self::COL_MIN]);
        $totalPx = array_sum($pxCols);

        // Columna y desplazamiento (px) donde cae una coordenada x de la tabla.
        $ubicar = function (int $x) use ($pxCols): array {
            $acum = 0;
            foreach ($pxCols as $i => $w) {
                if ($x < $acum + $w || $i === count($pxCols) - 1) return [$i, max(0, $x - $acum)];
                $acum += $w;
            }
            return [0, 0];
        };

        $anchors = '';
        $rels = '';
        $media = [];

        foreach ($logos as $i => $logo) {
            $rid = $i + 1;
            $anchoLogo = (int)round($logo['cx'] / 9525);
            $x = $i === 0 ? self::MARGEN_PX : max(self::MARGEN_PX, $totalPx - $anchoLogo - self::MARGEN_PX);
            [$col, $off] = $ubicar($x);
            $media['image' . $rid . '.png'] = $logo['bytes'];
            $anchors .= '<xdr:oneCellAnchor>'
                . '<xdr:from><xdr:col>' . $col . '</xdr:col><xdr:colOff>' . ($off * 9525) . '</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>' . (4 * 9525) . '</xdr:rowOff></xdr:from>'
                . '<xdr:ext cx="' . $logo['cx'] . '" cy="' . $logo['cy'] . '"/>'
                . '<xdr:pic>'
                . '<xdr:nvPicPr><xdr:cNvPr id="' . $rid . '" name="' . htmlspecialchars($logo['nombre'], ENT_QUOTES | ENT_XML1, 'UTF-8') . '"/><xdr:cNvPicPr/></xdr:nvPicPr>'
                . '<xdr:blipFill><a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="rId' . $rid . '"/><a:stretch><a:fillRect/></a:stretch></xdr:blipFill>'
                . '<xdr:spPr><a:xfrm><a:off x="0" y="0"/><a:ext cx="' . $logo['cx'] . '" cy="' . $logo['cy'] . '"/></a:xfrm><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>'
                . '</xdr:pic><xdr:clientData/></xdr:oneCellAnchor>';
            $rels .= '<Relationship Id="rId' . $rid . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image' . $rid . '.png"/>';
        }

        $drawingXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            . $anchors . '</xdr:wsDr>';
        $relsXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">' . $rels . '</Relationships>';

        return ['media' => $media, 'drawingXml' => $drawingXml, 'relsXml' => $relsXml];
    }
}
