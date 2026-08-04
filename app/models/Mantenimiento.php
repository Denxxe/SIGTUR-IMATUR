<?php
/**
 * Mantenimiento — proceso de reparación de un bien (mig. 063).
 *
 * B-33: el mantenimiento lo ejecuta Servicios Generales (o un taller
 * externo) y se lleva registro del proceso, de quién lo hizo y de si el
 * bien volvió. No basta con anotar "salió a mantenimiento": hay una
 * salida, un trabajo y un retorno con resultado.
 *
 * Se abre y se cierra siempre desde `ActividadInventario::registrarMovimiento()`,
 * que es quien gobierna la transacción — por eso `abrir()` y `cerrar()`
 * reciben la conexión en curso en vez de crear la suya.
 *
 * La BD garantiza con un índice único parcial que un bien no pueda tener
 * dos mantenimientos abiertos a la vez.
 */
class Mantenimiento extends Model {

    const RESULTADOS = ['Reparado', 'Sin reparación', 'Irrecuperable'];

    const RESULTADO_BADGES = [
        'Reparado'       => 'sig-badge--success',
        'Sin reparación' => 'sig-badge--warning',
        'Irrecuperable'  => 'sig-badge--danger',
    ];

    /** Mantenimiento abierto de un bien (sin fecha de retorno), o null. */
    public static function abiertoDe(int $idBien) {
        $db = new Database();
        $db->query("SELECT * FROM inventario_mantenimientos
                     WHERE id_inventario = :id AND fecha_retorno IS NULL AND is_active = TRUE
                     ORDER BY id DESC LIMIT 1");
        $db->bind(':id', $idBien);
        return $db->single() ?: null;
    }

    /** Historial de mantenimientos de un bien (para su hoja de vida, B-36). */
    public static function porBien(int $idBien) {
        $db = new Database();
        $db->query("SELECT m.*,
                           TRIM(COALESCE(p.nombre,'') || ' ' || COALESCE(p.apellido,'')) AS encargado
                      FROM inventario_mantenimientos m
                      LEFT JOIN empleados e ON m.id_empleado_encargado = e.id
                      LEFT JOIN personas  p ON e.id_persona = p.id
                     WHERE m.id_inventario = :id AND m.is_active = TRUE
                     ORDER BY m.fecha_salida DESC, m.id DESC");
        $db->bind(':id', $idBien);
        return $db->resultSet();
    }

    /** Todos los mantenimientos en curso — alimenta el panel y las alertas. */
    public static function enCurso() {
        $db = new Database();
        $db->query("SELECT m.*, i.nombre AS bien, i.codigo_bn,
                           TRIM(COALESCE(p.nombre,'') || ' ' || COALESCE(p.apellido,'')) AS encargado
                      FROM inventario_mantenimientos m
                      INNER JOIN inventario i ON m.id_inventario = i.id
                      LEFT  JOIN empleados  e ON m.id_empleado_encargado = e.id
                      LEFT  JOIN personas   p ON e.id_persona = p.id
                     WHERE m.fecha_retorno IS NULL AND m.is_active = TRUE AND i.is_active = TRUE
                     ORDER BY m.fecha_salida ASC");
        return $db->resultSet();
    }

    /**
     * Abre el mantenimiento al registrar la salida.
     * Recibe la conexión de la transacción en curso (no abre otra).
     */
    public static function abrir(Database $db, int $idBien, ?int $idActividad,
                                 string $fecha, array $datos, $user_id = null): void {
        $db->query("INSERT INTO inventario_mantenimientos
                        (id_inventario, id_actividad_salida, fecha_salida,
                         id_empleado_encargado, proveedor_externo, descripcion_falla, created_by)
                    VALUES (:bien, :act, :fecha, :enc, :prov, :falla, :u)");
        $db->bind(':bien',  $idBien);
        $db->bind(':act',   $idActividad ?: null);
        $db->bind(':fecha', $fecha);
        $db->bind(':enc',   (int)($datos['id_empleado_encargado'] ?? 0) ?: null);
        $db->bind(':prov',  trim((string)($datos['proveedor_externo'] ?? '')) ?: null);
        $db->bind(':falla', trim((string)($datos['descripcion_falla'] ?? '')) ?: null);
        $db->bind(':u',     $user_id);
        $db->execute();
    }

    /**
     * Cierra el mantenimiento abierto al registrar el retorno.
     * Recibe la conexión de la transacción en curso.
     */
    public static function cerrar(Database $db, int $idBien, string $fecha,
                                  array $datos, $user_id = null): void {
        $resultado = in_array($datos['resultado'] ?? '', self::RESULTADOS, true)
            ? $datos['resultado'] : 'Reparado';
        $costo = trim((string)($datos['costo'] ?? ''));

        $db->query("UPDATE inventario_mantenimientos
                       SET fecha_retorno = :fecha,
                           trabajo_realizado = :trabajo,
                           costo = :costo,
                           resultado = :resultado,
                           updated_at = CURRENT_TIMESTAMP,
                           updated_by = :u
                     WHERE id_inventario = :bien
                       AND fecha_retorno IS NULL AND is_active = TRUE");
        $db->bind(':fecha',     $fecha);
        $db->bind(':trabajo',   trim((string)($datos['trabajo_realizado'] ?? '')) ?: null);
        $db->bind(':costo',     $costo !== '' ? $costo : null);
        $db->bind(':resultado', $resultado);
        $db->bind(':u',         $user_id);
        $db->bind(':bien',      $idBien);
        $db->execute();
    }
}
