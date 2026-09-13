<?php
/**
 * TasaBcv — consulta la tasa oficial del dólar publicada por el BCV.
 *
 * EL BCV NO TIENE API. Lo que hay es la portada de bcv.org.ve, que trae el
 * valor en un bloque `id="dolar"`. Esta clase lo lee y lo interpreta.
 *
 * POR QUÉ NO SE USA UNA API DE TERCEROS
 * Existen espejos en JSON (ve.dolarapi.com, pydolarve.org…) que son más
 * cómodos, pero: al comprobarlos, uno iba 4 días atrasado (≈1,2 % de
 * diferencia sobre el oficial) y el otro no respondía. Para un instituto
 * público la fuente citable ante una auditoría es el BCV, no un tercero que
 * lo copia.
 *
 * QUÉ DEVUELVE Y QUÉ NO
 * Devuelve una SUGERENCIA para que Talento Humano la confirme. El sistema
 * nunca la guarda solo, y el cálculo de nómina jamás sale a internet: usa la
 * tasa ya confirmada y congelada en `nomina_periodos`.
 *
 * ⚠️ Esto depende del HTML de un sitio ajeno. Si el BCV rediseña su portada,
 * `consultar()` lanza una excepción con un mensaje claro y el usuario sigue
 * cargando la tasa a mano: nunca bloquea la nómina.
 */
class TasaBcv
{
    /** Portada del BCV: es donde publica el tipo de cambio del día. */
    const URL = 'https://www.bcv.org.ve/';

    /** Segundos de espera. Corto a propósito: esto no puede colgar la pantalla. */
    const TIMEOUT = 8;

    /**
     * CA con la que se verifica al BCV. Hace falta porque su servidor está mal
     * configurado: entrega un intermedio que NO es el que firmó su certificado,
     * así que la cadena no cierra. Los navegadores lo disimulan buscando por su
     * cuenta el intermedio que falta (AIA); OpenSSL, que es lo que usa PHP, no.
     * Aportándolo aquí la verificación queda activa y anclada a la CA esperada,
     * en vez de apagarla como hacen los ejemplos que circulan por internet.
     * Ver storage/certs/LEEME.md.
     */
    const CA_BUNDLE = __DIR__ . '/../../storage/certs/bcv-sectigo-ca.pem';

    /**
     * Consulta el BCV y devuelve la tasa con su fecha valor.
     *
     * @return array{tasa: float, fecha_valor: ?string, consultada_at: string}
     * @throws Exception si no hay red o si el HTML dejó de tener el formato esperado.
     */
    public static function consultar(): array
    {
        $html = self::descargar();
        $tasa = self::extraerTasa($html);

        if ($tasa === null) {
            throw new Exception('El BCV respondió, pero no se encontró el tipo de cambio en la página. '
                . 'Es probable que hayan cambiado el sitio: cargue la tasa a mano y avise a soporte.');
        }
        if ($tasa <= 0) {
            throw new Exception('El BCV devolvió un valor no utilizable (' . $tasa . '). Cargue la tasa a mano.');
        }

        return [
            'tasa'          => $tasa,
            'fecha_valor'   => self::extraerFechaValor($html),
            'consultada_at' => date('Y-m-d H:i:s'),
        ];
    }

    /** Descarga la portada. cURL si está disponible; si no, el envoltorio de PHP. */
    private static function descargar(): string
    {
        if (function_exists('curl_init')) {
            $ch = curl_init(self::URL);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => self::TIMEOUT,
                CURLOPT_CONNECTTIMEOUT => self::TIMEOUT,
                // Se VERIFICA el certificado. El de bcv.org.ve es válido; muchos
                // ejemplos que circulan lo desactivan por un problema viejo, y
                // desactivarlo abriría la puerta a que un intermediario nos
                // dicte la tasa con la que se paga la nómina.
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_USERAGENT      => 'SIGTUR-IMATUR/2.0',
            ]);
            // Ancla de confianza propia (ver CA_BUNDLE). Si el archivo no
            // estuviera, se sigue con el almacén del sistema: puede funcionar
            // donde el servidor sí complete la cadena.
            if (is_readable(self::CA_BUNDLE)) {
                curl_setopt($ch, CURLOPT_CAINFO, self::CA_BUNDLE);
            }
            $html = curl_exec($ch);
            $err  = curl_error($ch);
            $codigo = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($html === false || $html === '') {
                // El fallo de certificado tiene causa y arreglo propios, así que
                // se distingue: mandar a "revise su internet" haría perder tiempo.
                if (stripos($err, 'SSL certificate') !== false || stripos($err, 'certificate') !== false) {
                    throw new Exception('No se pudo verificar el certificado del BCV. '
                        . 'Puede que hayan cambiado de autoridad certificadora: hay que actualizar '
                        . 'storage/certs/bcv-sectigo-ca.pem (ver el LEEME de esa carpeta). '
                        . 'Mientras tanto, cargue la tasa a mano. [' . $err . ']');
                }
                throw new Exception('No se pudo conectar con el BCV' . ($err !== '' ? ' (' . $err . ')' : '')
                    . '. Verifique la conexión del servidor a internet o cargue la tasa a mano.');
            }
            if ($codigo !== 200) {
                throw new Exception('El BCV respondió con un estado inesperado (HTTP ' . $codigo . '). Cargue la tasa a mano.');
            }
            return $html;
        }

        $ctx = stream_context_create(['http' => [
            'timeout' => self::TIMEOUT,
            'header'  => "User-Agent: SIGTUR-IMATUR/2.0\r\n",
        ]]);
        $html = @file_get_contents(self::URL, false, $ctx);
        if ($html === false || $html === '') {
            throw new Exception('No se pudo conectar con el BCV. Verifique la conexión del servidor '
                . 'a internet o cargue la tasa a mano.');
        }
        return $html;
    }

    /**
     * Saca el número del bloque `id="dolar"`.
     * El anclaje importa: la portada publica también euro, yuan, lira y rublo
     * con el mismo formato, así que un patrón suelto tomaría la divisa que no es.
     */
    private static function extraerTasa(string $html): ?float
    {
        $pos = strpos($html, 'id="dolar"');
        if ($pos === false) return null;

        // Dentro de ese bloque, el valor va en el primer <strong>.
        $bloque = substr($html, $pos, 1200);
        if (!preg_match('/<strong[^>]*>\s*([\d.,]+)\s*<\/strong>/u', $bloque, $m)) {
            return null;
        }
        return self::aFloat($m[1]);
    }

    /**
     * Fecha valor: el día hábil en que rige la tasa, que NO es necesariamente
     * el día de la consulta (un domingo la página ya muestra la del martes).
     * Se lee del atributo `content`, que viene en ISO, en vez del texto en
     * español ("Martes, 15 Septiembre 2026"), que es frágil de interpretar.
     */
    private static function extraerFechaValor(string $html): ?string
    {
        $pos = stripos($html, 'Fecha Valor');
        if ($pos === false) return null;

        $bloque = substr($html, $pos, 400);
        if (preg_match('/content="(\d{4}-\d{2}-\d{2})/', $bloque, $m)) {
            return $m[1];
        }
        return null;
    }

    /**
     * "842,20670000" o "1.234,56" → float.
     * El BCV usa coma decimal y punto de millares (formato venezolano).
     */
    private static function aFloat(string $txt): float
    {
        $limpio = str_replace('.', '', trim($txt));   // fuera los millares
        $limpio = str_replace(',', '.', $limpio);     // la coma decide el decimal
        return (float)$limpio;
    }
}
