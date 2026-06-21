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
