<?php
class Visita extends Model {

    public static function getRecientesToday() {
        $db = new Database();
        $db->query("
            SELECT vi.*,
                   COALESCE(p.cedula,   vt.cedula)   AS vis_cedula,
                   COALESCE(p.nombre,   vt.nombre)   AS vis_nombre,
                   COALESCE(p.apellido, vt.apellido) AS vis_apellido,
                   COALESCE(p.correo,   vt.correo)   AS vis_correo,
                   COALESCE(p.telefono, vt.telefono) AS vis_telefono,
                   vt.procedencia,
                   ep.nombre  AS emp_nombre,
                   ep.apellido AS emp_apellido
            FROM   visitas    vi
            JOIN   visitantes vt  ON vi.id_visitante = vt.id
            LEFT JOIN personas  p   ON vt.id_persona   = p.id
            LEFT JOIN empleados e   ON vi.id_empleado  = e.id
            LEFT JOIN personas  ep  ON e.id_persona    = ep.id
            WHERE  vi.is_active = TRUE 
            AND    DATE(vi.hora_entrada) = CURRENT_DATE
            ORDER  BY vi.hora_entrada DESC
            LIMIT  100
        ");
        return $db->resultSet();
    }

    /**
     * Historial de visitas paginado en servidor, con búsqueda por
     * cédula/nombre/procedencia y rango de fechas. ['items'=>[], 'total'=>n].
     */
    public static function paginate(int $pagina, int $porPagina, array $f = []): array {
        $db    = new Database();
        $binds = [];
        $where = "vi.is_active = TRUE";

        if (!empty($f['buscar'])) {
            $where .= " AND (COALESCE(p.cedula, vt.cedula) ILIKE :q
                         OR (COALESCE(p.nombre, vt.nombre)||' '||COALESCE(p.apellido, vt.apellido)) ILIKE :q
                         OR vt.procedencia ILIKE :q)";
            $binds[':q'] = '%' . $f['buscar'] . '%';
        }
        if (!empty($f['fecha_desde'])) { $where .= " AND DATE(vi.hora_entrada) >= :fd"; $binds[':fd'] = $f['fecha_desde']; }
        if (!empty($f['fecha_hasta'])) { $where .= " AND DATE(vi.hora_entrada) <= :fh"; $binds[':fh'] = $f['fecha_hasta']; }

        $base = "FROM visitas vi
                 JOIN visitantes vt ON vi.id_visitante = vt.id
                 LEFT JOIN personas  p  ON vt.id_persona = p.id
                 LEFT JOIN empleados e  ON vi.id_empleado = e.id
                 LEFT JOIN personas  ep ON e.id_persona  = ep.id
                 WHERE {$where}";

        $db->query("SELECT COUNT(*) AS total {$base}");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $total = (int)($db->single()->total ?? 0);

        $offset = ($pagina - 1) * $porPagina;
        $db->query("SELECT vi.*,
                           COALESCE(p.cedula,   vt.cedula)   AS vis_cedula,
                           COALESCE(p.nombre,   vt.nombre)   AS vis_nombre,
                           COALESCE(p.apellido, vt.apellido) AS vis_apellido,
                           COALESCE(p.correo,   vt.correo)   AS vis_correo,
                           COALESCE(p.telefono, vt.telefono) AS vis_telefono,
                           COALESCE(p.genero,   vt.genero)   AS vis_genero,
                           vt.procedencia,
                           ep.nombre AS emp_nombre, ep.apellido AS emp_apellido
                    {$base}
                    ORDER BY vi.hora_entrada DESC
                    LIMIT :lim OFFSET :off");
        foreach ($binds as $k => $v) $db->bind($k, $v);
        $db->bind(':lim', $porPagina);
        $db->bind(':off', $offset);

        return ['items' => $db->resultSet(), 'total' => $total];
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM visitas WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Registra la ENTRADA de un visitante. Recepción solo lleva entradas: no se
     * marca salida (decisión del cliente), así que cada registro es una visita
     * nueva aunque la misma persona venga dos veces el mismo día.
     */
    public static function registrar($data, $userId) {
        $db = new Database();
        $db->query("
            INSERT INTO visitas (id_visitante, id_empleado, motivo, observaciones, created_by)
            VALUES (:id_visitante, :id_empleado, :motivo, :observaciones, :uid)
        ");
        $db->bind(':id_visitante',  $data['id_visitante']);
        $db->bind(':id_empleado',   $data['id_empleado'] ?: null);
        $db->bind(':motivo',        $data['motivo'] ?: null);
        $db->bind(':observaciones', $data['observaciones'] ?: null);
        $db->bind(':uid',           $userId);
        $result = $db->execute();
        self::auditStatic('visitas', 'INSERT', null, null, [
            'id_visitante'  => $data['id_visitante'],
            'id_empleado'   => $data['id_empleado'] ?: null,
            'motivo'        => $data['motivo'] ?: null,
            'observaciones' => $data['observaciones'] ?: null,
        ], $userId);
        return $result;
    }
}
