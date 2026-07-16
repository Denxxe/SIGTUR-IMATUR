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
    /** Alto de destino común a ambos logos (px); el ancho se deriva de su proporción real. */
    const ALTO_PX = 46;

    private static ?array $cache = null;

    private static function logos(): array
    {
        if (self::$cache !== null) return self::$cache;
        $defs = [
            ['archivo' => APP_ROOT . '/public/assets/images/Logo.png', 'nombre' => 'LogoAlcaldia'],
            ['archivo' => APP_ROOT . '/public/assets/images/Logo_imatur-removebg-preview.png', 'nombre' => 'LogoImatur'],
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

    /**
     * Piezas necesarias para insertar el membrete con logos en UNA hoja:
     * los bytes de cada imagen (xl/media), el XML del drawing (posiciona el
     * logo de la Alcaldía en la primera columna y el de IMATUR en la última)
     * y el .rels del drawing. $ncol = cantidad de columnas de esa hoja.
     * Devuelve arrays vacíos si los logos no están disponibles (degrada sin
     * romper el resto del archivo).
     */
    public static function piezasParaHoja(int $ncol): array
    {
        $logos = self::logos();
        if (empty($logos)) return ['media' => [], 'drawingXml' => '', 'relsXml' => ''];

        $colDerecha = max(0, $ncol - 1);
        $posiciones = [0, $colDerecha];
        $anchors = '';
        $rels = '';
        $media = [];

        foreach ($logos as $i => $logo) {
            $rid = $i + 1;
            $col = $posiciones[$i] ?? 0;
            $media['image' . $rid . '.png'] = $logo['bytes'];
            $anchors .= '<xdr:oneCellAnchor>'
                . '<xdr:from><xdr:col>' . $col . '</xdr:col><xdr:colOff>19050</xdr:colOff><xdr:row>0</xdr:row><xdr:rowOff>19050</xdr:rowOff></xdr:from>'
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
