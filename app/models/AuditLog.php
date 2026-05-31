<?php

/**
 * Clase AuditLog: Modelo para visualizar y registrar las bitácoras del sistema.
 */
class AuditLog extends Model
{

    public static function all()
    {
        $db = new Database();
        // actor_name = nombre real de la persona (usuarios → empleados → personas);
        // si no hay persona vinculada, cae al username.
        $db->query("SELECT a.*, u.username,
                           COALESCE(NULLIF(TRIM(COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')), ''), u.username) AS actor_name
                    FROM audit_logs a
                    LEFT JOIN usuarios u   ON a.id_usuario  = u.id
                    LEFT JOIN empleados e  ON u.id_empleado = e.id
                    LEFT JOIN personas per ON e.id_persona  = per.id
                    ORDER BY a.fecha DESC LIMIT 500");
        return $db->resultSet();
    }

    /**
     * Bitácora paginada con filtros server-side.
     * @param int   $pagina    1-based
     * @param int   $porPagina tamaño de página
     * @param array $f         filtros: fecha_inicio, fecha_fin, modulo, operacion, buscar
     * @return array ['items' => [...], 'total' => int]
     */
    public static function paginate(int $pagina, int $porPagina, array $f = []): array
    {
        $db     = new Database();
        $pagina = max(1, $pagina);
        $offset = ($pagina - 1) * $porPagina;

        $joins = "LEFT JOIN usuarios u   ON a.id_usuario  = u.id
                  LEFT JOIN empleados e  ON u.id_empleado = e.id
                  LEFT JOIN personas per ON e.id_persona  = per.id";

        $where = "1=1";
        $binds = [];
        if (!empty($f['fecha_inicio'])) { $where .= " AND a.fecha >= :fi"; $binds[':fi'] = $f['fecha_inicio'] . ' 00:00:00'; }
        if (!empty($f['fecha_fin']))    { $where .= " AND a.fecha <= :ff"; $binds[':ff'] = $f['fecha_fin'] . ' 23:59:59'; }
        if (!empty($f['modulo']))       { $where .= " AND a.tabla_afectada = :mod"; $binds[':mod'] = $f['modulo']; }
        if (!empty($f['operacion']))    { $where .= " AND a.operacion = :op"; $binds[':op'] = $f['operacion']; }
        if (!empty($f['buscar'])) {
            $where .= " AND (a.tabla_afectada ILIKE :q OR u.username ILIKE :q
                        OR (COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')) ILIKE :q)";
            $binds[':q'] = '%' . $f['buscar'] . '%';
        }

        // Total de registros que cumplen el filtro
        $db->query("SELECT COUNT(*) AS total FROM audit_logs a {$joins} WHERE {$where}");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $total = (int)($db->single()->total ?? 0);

        // Página solicitada
        $db->query("SELECT a.*, u.username,
                           COALESCE(NULLIF(TRIM(COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')), ''), u.username) AS actor_name
                    FROM audit_logs a {$joins}
                    WHERE {$where}
                    ORDER BY a.fecha DESC
                    LIMIT :limit OFFSET :offset");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->bind(':limit', (int)$porPagina);
        $db->bind(':offset', (int)$offset);
        $items = $db->resultSet();

        return ['items' => $items, 'total' => $total];
    }

    /** Módulos (tablas) que efectivamente tienen registros en la bitácora — para el filtro. */
    public static function modulosDistintos(): array
    {
        $db = new Database();
        $db->query("SELECT DISTINCT tabla_afectada FROM audit_logs WHERE tabla_afectada IS NOT NULL ORDER BY tabla_afectada");
        return $db->resultSet();
    }

    public static function byTabla($tabla)
    {
        $db = new Database();
        $db->query("SELECT a.*, u.username 
                    FROM audit_logs a
                    LEFT JOIN usuarios u ON a.id_usuario = u.id
                    WHERE a.tabla_afectada = :tabla
                    ORDER BY a.fecha DESC LIMIT 500");
        $db->bind(':tabla', $tabla);
        return $db->resultSet();
    }

    public static function getDeleted($tabla)
    {
        $db = new Database();
        $identificador = "id";
        if ($tabla == 'personas') $identificador = "cedula || ' - ' || nombre || ' ' || apellido";
        if ($tabla == 'inventario') $identificador = "codigo_bn || ' - ' || nombre";
        if (in_array($tabla, ['talleres', 'rutas', 'departamentos', 'cargos', 'categorias', 'ubicaciones', 'ubicaciones_formacion'])) $identificador = "nombre";
        // pasantes ya no tiene cedula/nombre propios (migración 003): se consulta a personas por id_persona
        if ($tabla == 'pasantes') $identificador = "(SELECT pp.cedula || ' - ' || pp.nombre || ' ' || pp.apellido FROM personas pp WHERE pp.id = id_persona)";
        if ($tabla == 'municipio') $identificador = "nombre || ' - ' || codigo_postal";
        if ($tabla == 'parroquia') $identificador = "nombre";

        // Nombre real de quien eliminó: usuarios → empleados → personas.
        // Si no hay persona vinculada, cae al username; si no hay usuario, queda NULL (→ 'Sistema' en la vista).
        // Se califica con el alias 'src' porque 'usuarios' también tiene una columna deleted_by (ambigüedad).
        $deletedByName = "(SELECT COALESCE(NULLIF(TRIM(COALESCE(per.nombre,'') || ' ' || COALESCE(per.apellido,'')), ''), u.username)
                           FROM usuarios u
                           LEFT JOIN empleados e   ON u.id_empleado = e.id
                           LEFT JOIN personas per  ON e.id_persona  = per.id
                           WHERE u.id = src.deleted_by)";

        $sql = "SELECT src.*, ($identificador) as display_name, $deletedByName as deleted_by_name
                FROM $tabla src WHERE src.is_active = FALSE ORDER BY src.deleted_at DESC";
        $db->query($sql);
        return $db->resultSet();
    }

    /**
     * Insertar un log de auditoría.
     * @param string $tabla Tabla afectada.
     * @param string $operacion Tipo de operación (INSERT, UPDATE, DELETE).
     * @param int $record_id ID del registro afectado.
     * @param array $datos_previos Datos antes del cambio.
     * @param array $datos_nuevos Datos después del cambio.
     * @param int $id_usuario Usuario que realiza la acción.
     * @param Database|null $dbInstance Instancia de DB opcional para usar dentro de una transacción.
     */
    public static function log(string $tabla, string $operacion, ?int $record_id, ?array $datos_previos, ?array $datos_nuevos, ?int $id_usuario, $dbInstance = null)
    {
        // Si no se provee instancia, creamos una nueva.
        $db = $dbInstance ? $dbInstance : new Database();

        $db->query("INSERT INTO audit_logs (tabla_afectada, operacion, record_id, datos_previos, datos_nuevos, id_usuario, ip_direccion) 
                    VALUES (:tabla, :operacion, :record_id, :previos, :nuevos, :id_usuario, :ip)");

        $db->bind(':tabla', $tabla);
        $db->bind(':operacion', $operacion);
        $db->bind(':record_id', $record_id);
        $db->bind(':previos', $datos_previos ? json_encode($datos_previos) : null);
        $db->bind(':nuevos', $datos_nuevos ? json_encode($datos_nuevos) : null);
        $db->bind(':id_usuario', $id_usuario);
        $db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1');

        return $db->execute();
    }
}
