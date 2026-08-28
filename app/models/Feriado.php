<?php
/**
 * Feriado — calendario de días no hábiles para el cálculo de vacaciones (3A).
 * recurrente = TRUE → se repite cada año en el mismo mes/día (feriados fijos).
 * recurrente = FALSE → fecha puntual de ese año (movibles: Carnaval, Semana Santa).
 */
class Feriado extends Model {

    public static function all(): array {
        $db = new Database();
        $db->query("SELECT * FROM feriados WHERE is_active = TRUE
                    ORDER BY EXTRACT(MONTH FROM fecha), EXTRACT(DAY FROM fecha)");
        return $db->resultSet();
    }

    public static function find(int $id) {
        $db = new Database();
        $db->query("SELECT * FROM feriados WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /** Conjuntos de feriados para búsqueda rápida: ['md'=>set 'm-d', 'ymd'=>set 'Y-m-d']. */
    public static function lookup(): array {
        $md = []; $ymd = [];
        foreach (self::all() as $f) {
            $d = new \DateTime($f->fecha);
            if ($f->recurrente) $md[$d->format('m-d')] = true;
            else                $ymd[$d->format('Y-m-d')] = true;
        }
        return ['md' => $md, 'ymd' => $ymd];
    }

    // ── Feriados movibles: se calculan, no se cargan a mano ──────────────────
    //
    // Carnaval y Semana Santa dependen del Domingo de Resurrección, así que
    // cambian de fecha cada año. La mig. 071 los cargó para 2026-2028; sin un
    // generador, alguien tiene que acordarse de agregarlos antes de cada año
    // nuevo, y si no lo hace el conteo de días hábiles de vacaciones vuelve a
    // fallar **en silencio** (descuenta días que no corresponden, sin error
    // visible). Esto lo vuelve un cálculo en vez de una tarea de mantenimiento.

    /** Días de Carnaval y Semana Santa, como desplazamiento respecto de Pascua. */
    private const OFFSETS_MOVIBLES = [
        -48 => 'Lunes de Carnaval',
        -47 => 'Martes de Carnaval',
        -3  => 'Jueves Santo',
        -2  => 'Viernes Santo',
    ];

    /**
     * Domingo de Resurrección de un año, en 'Y-m-d'.
     *
     * Algoritmo Gregoriano anónimo (Meeus/Jones/Butcher). Se implementa a mano
     * en vez de usar `easter_date()` porque esa función vive en la extensión
     * `calendar`, que no siempre está habilitada; el resultado es el mismo
     * (se verificó contra ella al preparar la mig. 071).
     */
    public static function pascua(int $anio): string {
        $a = $anio % 19;
        $b = intdiv($anio, 100);
        $c = $anio % 100;
        $d = intdiv($b, 4);
        $e = $b % 4;
        $f = intdiv($b + 8, 25);
        $g = intdiv($b - $f + 1, 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = intdiv($c, 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = intdiv($a + 11 * $h + 22 * $l, 451);
        $mes = intdiv($h + $l - 7 * $m + 114, 31);
        $dia = (($h + $l - 7 * $m + 114) % 31) + 1;
        return sprintf('%04d-%02d-%02d', $anio, $mes, $dia);
    }

    /**
     * Los 4 feriados movibles de un año: ['Y-m-d' => nombre], ordenados por fecha.
     * Función pura: no toca la base de datos, así que se puede probar sola.
     */
    public static function movibles(int $anio): array {
        $pascua = new \DateTimeImmutable(self::pascua($anio));
        $out = [];
        foreach (self::OFFSETS_MOVIBLES as $offset => $nombre) {
            $dia = $pascua->modify(($offset >= 0 ? '+' : '') . $offset . ' days');
            $out[$dia->format('Y-m-d')] = $nombre;
        }
        ksort($out);
        return $out;
    }

    /**
     * Carga los feriados movibles que le falten a un año.
     *
     * Idempotente y **respetuosa de las bajas**: si ya existe una fila para esa
     * fecha se omite, incluso si está desactivada. Así, regenerar no resucita un
     * feriado que alguien quitó a propósito (p. ej. si el Ejecutivo no decreta
     * el lunes de Carnaval algún año).
     *
     * @return array{creados:int, existentes:int, fechas:array<string,string>}
     */
    public static function generarAnio(int $anio, $userId = null): array {
        $movibles   = self::movibles($anio);
        $creados    = 0;
        $existentes = 0;
        $db = new Database();

        foreach ($movibles as $fecha => $nombre) {
            $db->query("SELECT COUNT(*) AS n FROM feriados WHERE fecha = :f");
            $db->bind(':f', $fecha);
            if ((int)($db->single()->n ?? 0) > 0) { $existentes++; continue; }

            self::crear($fecha, $nombre, false, $userId);
            $creados++;
        }

        return ['creados' => $creados, 'existentes' => $existentes, 'fechas' => $movibles];
    }

    /**
     * Años (desde el actual, mirando `$horizonte` hacia adelante) que todavía no
     * tienen sus feriados movibles cargados. Alimenta el aviso de la pantalla:
     * el problema de este dato es que su ausencia no se nota, así que hay que
     * decirlo antes de que alguien registre vacaciones mal contadas.
     */
    public static function aniosSinMovibles(int $horizonte = 3): array {
        $db = new Database();
        $db->query("SELECT EXTRACT(YEAR FROM fecha)::int AS anio, COUNT(*) AS n
                    FROM feriados
                    WHERE is_active = TRUE AND recurrente = FALSE
                    GROUP BY 1");
        $cargados = [];
        foreach ($db->resultSet() as $r) $cargados[(int)$r->anio] = (int)$r->n;

        $faltan  = [];
        $desde   = (int)date('Y');
        for ($a = $desde; $a < $desde + $horizonte; $a++) {
            if (($cargados[$a] ?? 0) < count(self::OFFSETS_MOVIBLES)) $faltan[] = $a;
        }
        return $faltan;
    }

    public static function crear(string $fecha, string $nombre, bool $recurrente, $userId = null): bool {
        $db = new Database();
        $db->query("INSERT INTO feriados (fecha, nombre, recurrente, created_by)
                    VALUES (:fecha, :nombre, :rec, :uid)");
        $db->bind(':fecha', $fecha);
        $db->bind(':nombre', $nombre);
        $db->bind(':rec', $recurrente, \PDO::PARAM_BOOL);
        $db->bind(':uid', $userId);
        $ok = $db->execute();
        self::auditStatic('feriados', 'INSERT', 0, null, ['fecha' => $fecha, 'nombre' => $nombre], $userId);
        return $ok;
    }

    public static function eliminar(int $id, $userId = null): bool {
        $previo = self::find($id);
        $db = new Database();
        $db->query("UPDATE feriados SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :uid WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':uid', $userId);
        $ok = $db->execute();
        self::auditStatic('feriados', 'DELETE', $id, $previo, null, $userId);
        return $ok;
    }
}
