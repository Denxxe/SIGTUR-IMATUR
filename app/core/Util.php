<?php
/**
 * Utilidades transversales del sistema.
 */
class Util
{
    /**
     * Edad en AÑOS CUMPLIDOS a partir de una fecha de nacimiento.
     * Acepta string 'YYYY-MM-DD' o DateTimeInterface. Devuelve int o null
     * (fecha vacía, inválida o futura). Siempre se calcula respecto a HOY,
     * por lo que se "actualiza" sola cada vez que se consulta.
     *
     * @param string|\DateTimeInterface|null $fecha
     */
    public static function edad($fecha): ?int
    {
        if (empty($fecha)) return null;
        try {
            $nac = $fecha instanceof \DateTimeInterface ? $fecha : new \DateTime((string)$fecha);
            $hoy = new \DateTime('today');
            if ($nac > $hoy) return null; // fecha futura → sin edad válida
            return (int)$nac->diff($hoy)->y;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Edad formateada para mostrar (p. ej. "20 años"); si no aplica, devuelve $vacio.
     *
     * @param string|\DateTimeInterface|null $fecha
     */
    public static function edadTexto($fecha, string $sufijo = ' años', string $vacio = '—'): string
    {
        $e = self::edad($fecha);
        return $e === null ? $vacio : $e . $sufijo;
    }

    // ── Números en letras ────────────────────────────────────────────────────
    // Los documentos jurídicos escriben las cantidades dos veces, en letras y
    // en números ("DIECINUEVE MIL VEINTICINCO BOLÍVARES CON VEINTIOCHO
    // CÉNTIMOS (Bs. 19.025,28)"). Lo exige el documento de donación de bienes
    // (mig. 075) y también la fecha del acto ("a los Dieciocho (18) días del
    // mes de Febrero de Dos Mil Veintiséis").

    private const UNIDADES = ['', 'uno', 'dos', 'tres', 'cuatro', 'cinco', 'seis', 'siete', 'ocho',
        'nueve', 'diez', 'once', 'doce', 'trece', 'catorce', 'quince', 'dieciséis', 'diecisiete',
        'dieciocho', 'diecinueve', 'veinte', 'veintiuno', 'veintidós', 'veintitrés', 'veinticuatro',
        'veinticinco', 'veintiséis', 'veintisiete', 'veintiocho', 'veintinueve'];

    private const DECENAS  = ['', '', '', 'treinta', 'cuarenta', 'cincuenta', 'sesenta', 'setenta',
        'ochenta', 'noventa'];

    private const CENTENAS = ['', 'ciento', 'doscientos', 'trescientos', 'cuatrocientos',
        'quinientos', 'seiscientos', 'setecientos', 'ochocientos', 'novecientos'];

    /** Tramo de 0 a 999 en letras. */
    private static function tramo(int $n): string
    {
        if ($n === 0)   return '';
        if ($n === 100) return 'cien';
        $out = '';
        $c = intdiv($n, 100);
        $r = $n % 100;
        if ($c > 0) $out .= self::CENTENAS[$c];
        if ($r > 0) {
            if ($out !== '') $out .= ' ';
            if ($r < 30) {
                $out .= self::UNIDADES[$r];
            } else {
                $d = intdiv($r, 10);
                $u = $r % 10;
                $out .= self::DECENAS[$d] . ($u > 0 ? ' y ' . self::UNIDADES[$u] : '');
            }
        }
        return $out;
    }

    /**
     * Entero en letras (hasta miles de millones). Devuelve minúsculas sin
     * acentuar el resultado: el llamador decide si va en mayúsculas.
     */
    public static function numeroALetras(int $n): string
    {
        if ($n === 0) return 'cero';
        if ($n < 0)   return 'menos ' . self::numeroALetras(-$n);

        $partes  = [];
        $escalas = [
            1000000000 => ['mil millones', 'mil millones'],
            1000000    => ['un millón', 'millones'],
            1000       => ['mil', 'mil'],
        ];
        foreach ($escalas as $valor => $nombres) {
            if ($n >= $valor) {
                $cant = intdiv($n, $valor);
                $n   %= $valor;
                if ($cant === 1) {
                    $partes[] = $nombres[0];
                } else {
                    $partes[] = self::numeroALetras($cant) . ' ' . $nombres[1];
                }
            }
        }
        if ($n > 0) $partes[] = self::tramo($n);

        // "veintiuno mil" no se dice: es "veintiún mil".
        return str_replace(['uno mil', 'uno millones'], ['un mil', 'un millones'], implode(' ', $partes));
    }

    /**
     * Monto en letras con su moneda, como lo escribe un documento jurídico:
     *   19025.28 → "DIECINUEVE MIL VEINTICINCO BOLÍVARES CON VEINTIOCHO CÉNTIMOS"
     * Los céntimos se omiten cuando son cero.
     */
    public static function montoALetras($monto, string $moneda = 'BOLÍVARES', string $fraccion = 'CÉNTIMOS'): string
    {
        $monto    = round((float)$monto, 2);
        $enteros  = (int)floor($monto);
        $centimos = (int)round(($monto - $enteros) * 100);

        $txt = mb_strtoupper(self::numeroALetras($enteros), 'UTF-8') . ' ' . $moneda;
        if ($centimos > 0) {
            $txt .= ' CON ' . mb_strtoupper(self::numeroALetras($centimos), 'UTF-8') . ' ' . $fraccion;
        }
        return $txt;
    }

    /**
     * Fecha en la redacción de los actos jurídicos:
     *   2026-02-18 → "a los Dieciocho (18) días del mes de Febrero de Dos Mil Veintiséis"
     */
    public static function fechaEnLetras($fecha): string
    {
        $ts = is_numeric($fecha) ? (int)$fecha : strtotime((string)$fecha);
        if (!$ts) return '';
        $meses = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto',
                  'Septiembre','Octubre','Noviembre','Diciembre'];
        $dia  = (int)date('j', $ts);
        $anio = (int)date('Y', $ts);
        $diaTxt  = ucwords(self::numeroALetras($dia));
        $anioTxt = ucwords(self::numeroALetras($anio));
        return 'a los ' . $diaTxt . ' (' . $dia . ') días del mes de '
             . $meses[(int)date('n', $ts) - 1] . ' de ' . $anioTxt . ' (' . $anio . ')';
    }
}
