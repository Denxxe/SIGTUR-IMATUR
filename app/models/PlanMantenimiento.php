<?php
/**
 * PlanMantenimiento — mantenimiento preventivo programado (mig. 065).
 *
 * B-56: el cliente pidió que el sistema avise cuándo toca el mantenimiento
 * de aires acondicionados, impresoras y computadoras.
 *
 * No confundir con `Mantenimiento` (mig. 063), que registra la reparación
 * que YA ocurrió. Este modelo es el calendario: cada cuántos meses toca y
 * cuándo es la próxima vez.
 */
class PlanMantenimiento extends Model {

    /** Frecuencias sugeridas, en meses. */
    const FRECUENCIAS = [3 => 'Trimestral', 6 => 'Semestral', 12 => 'Anual'];

    public static function porBien(int $idBien) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_mantenimiento_plan
                     WHERE id_inventario = :id AND is_active = TRUE LIMIT 1");
        $db->bind(':id', $idBien);
        return $db->single() ?: null;
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT p.*, i.nombre AS bien, i.codigo_bn, u.nombre AS ubicacion,
                           (p.proxima_fecha - CURRENT_DATE) AS dias_restantes
                      FROM inventario_mantenimiento_plan p
                      INNER JOIN inventario  i ON p.id_inventario = i.id
                      LEFT  JOIN ubicaciones u ON i.id_ubicacion = u.id
                     WHERE p.is_active = TRUE AND i.is_active = TRUE
                       AND i.estatus <> :baja
                     ORDER BY p.proxima_fecha ASC");
        $db->bind(':baja', Inventario::EST_BAJA);
        return $db->resultSet();
    }

    /** Planes cuyo mantenimiento toca dentro de $dias (o ya venció). */
    public static function proximos(int $dias) {
        $db = new Database();
        $db->query("SELECT p.id, p.id_inventario, p.proxima_fecha, i.nombre AS bien
                      FROM inventario_mantenimiento_plan p
                      INNER JOIN inventario i ON p.id_inventario = i.id
                     WHERE p.is_active = TRUE AND i.is_active = TRUE
                       AND i.estatus <> :baja
                       AND p.proxima_fecha <= CURRENT_DATE + (:d || ' days')::interval
                     ORDER BY p.proxima_fecha ASC");
        $db->bind(':baja', Inventario::EST_BAJA);
        $db->bind(':d', $dias);
        return $db->resultSet();
    }

    /** Crea o actualiza el plan de un bien (uno solo por bien). */
    public static function guardar(array $datos, $user_id = null): bool {
        $idBien = (int)($datos['id_inventario'] ?? 0);
        $frec   = (int)($datos['frecuencia_meses'] ?? 6);
        if ($frec < 1 || $frec > 60) {
            throw new Exception('La frecuencia debe estar entre 1 y 60 meses.');
        }
        $proxima = trim((string)($datos['proxima_fecha'] ?? ''));
        if ($proxima === '') {
            throw new Exception('Indica la fecha del próximo mantenimiento.');
        }

        $db = new Database();
        $existente = self::porBien($idBien);
        if ($existente) {
            $db->query("UPDATE inventario_mantenimiento_plan
                           SET frecuencia_meses = :f, proxima_fecha = :p, descripcion = :d,
                               updated_at = CURRENT_TIMESTAMP, updated_by = :u
                         WHERE id = :id");
            $db->bind(':id', (int)$existente->id);
        } else {
            $db->query("INSERT INTO inventario_mantenimiento_plan
                            (id_inventario, frecuencia_meses, proxima_fecha, descripcion, created_by)
                        VALUES (:bien, :f, :p, :d, :u)");
            $db->bind(':bien', $idBien);
        }
        $db->bind(':f', $frec);
        $db->bind(':p', $proxima);
        $db->bind(':d', trim((string)($datos['descripcion'] ?? '')) ?: null);
        $db->bind(':u', $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_mantenimiento_plan', $existente ? 'UPDATE' : 'INSERT',
            $existente->id ?? null, $existente, $datos, $user_id);
        return $ok;
    }

    /**
     * Corre la fecha al siguiente ciclo. Se llama cuando el bien vuelve de
     * un mantenimiento, para que el calendario no se quede atrás.
     */
    public static function marcarRealizado(int $idBien, string $fecha, $user_id = null): void {
        $plan = self::porBien($idBien);
        if (!$plan) return;
        $db = new Database();
        $db->query("UPDATE inventario_mantenimiento_plan
                       SET ultima_fecha = :f,
                           proxima_fecha = (:f::date + (frecuencia_meses || ' months')::interval)::date,
                           updated_at = CURRENT_TIMESTAMP, updated_by = :u
                     WHERE id = :id");
        $db->bind(':f',  $fecha);
        $db->bind(':u',  $user_id);
        $db->bind(':id', (int)$plan->id);
        $db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE inventario_mantenimiento_plan SET is_active = FALSE,
                        deleted_at = CURRENT_TIMESTAMP, deleted_by = :u WHERE id = :id");
        $db->bind(':id', (int)$id);
        $db->bind(':u',  $user_id);
        $ok = $db->execute();
        self::auditStatic('inventario_mantenimiento_plan', 'DELETE', $id, null, null, $user_id);
        return $ok;
    }
}
