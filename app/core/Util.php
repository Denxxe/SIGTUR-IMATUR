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
}
