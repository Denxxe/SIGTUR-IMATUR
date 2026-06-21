<?php
/**
 * Clase Departamento: Modelo para la tabla departamentos (jerárquica desde mig.027)
 */
class Departamento extends Model {
    // Tipos de unidad organizativa (fuente única — patrón H-07)
    const TIPOS_UNIDAD = ['Presidencia', 'Junta Directiva', 'Dirección', 'Coordinación', 'Oficina', 'Unidad'];
    // Orden jerárquico para listados
    const ORDEN_TIPO = [
        'Presidencia' => 1, 'Junta Directiva' => 2, 'Dirección' => 3,
        'Oficina' => 4, 'Coordinación' => 5, 'Unidad' => 6,
    ];

    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private ?int $id_padre;
    private ?string $tipo_unidad;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->id_padre = !empty($data['id_padre']) ? (int)$data['id_padre'] : null;
            $this->tipo_unidad = !empty($data['tipo_unidad']) ? $data['tipo_unidad'] : null;
        }
    }

    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }

    /** Listado con nombre del padre y tipo, ordenado jerárquicamente. */
    public static function all() {
        $db = new Database();
        $db->query("SELECT d.*, pa.nombre AS padre
                    FROM departamentos d
                    LEFT JOIN departamentos pa ON d.id_padre = pa.id
                    WHERE d.is_active = TRUE
                    ORDER BY COALESCE(d.id_padre, d.id), d.id");
        return $db->resultSet();
    }

    /**
     * Lista JERÁRQUICA (recorrido en profundidad): cada unidad seguida de sus
     * subunidades, de mayor a menor nivel. Agrega `->nivel` (profundidad, 0=raíz)
     * para indentar en la UI. Las huérfanas (padre inactivo) se tratan como raíces.
     */
    public static function arbol() {
        $rows = self::all();
        $byId = [];
        foreach ($rows as $r) { $r->nivel = 0; $byId[$r->id] = $r; }

        $hijos = [];
        $raices = [];
        foreach ($rows as $r) {
            if (!empty($r->id_padre) && isset($byId[$r->id_padre])) {
                $hijos[$r->id_padre][] = $r;
            } else {
                $raices[] = $r;
            }
        }
        $orden = self::ORDEN_TIPO;
        $cmp = function ($a, $b) use ($orden) {
            $oa = $orden[$a->tipo_unidad] ?? 9;
            $ob = $orden[$b->tipo_unidad] ?? 9;
            if ($oa !== $ob) return $oa <=> $ob;
            return strcasecmp($a->nombre, $b->nombre);
        };
        usort($raices, $cmp);
        foreach ($hijos as &$lst) { usort($lst, $cmp); }
        unset($lst);

        $out = [];
        $walk = function ($nodo, $nivel) use (&$walk, &$out, &$hijos) {
            $nodo->nivel = $nivel;
            $out[] = $nodo;
            foreach ($hijos[$nodo->id] ?? [] as $h) { $walk($h, $nivel + 1); }
        };
        foreach ($raices as $r) { $walk($r, 0); }
        return $out;
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT d.*, pa.nombre AS padre
                    FROM departamentos d
                    LEFT JOIN departamentos pa ON d.id_padre = pa.id
                    WHERE d.id = :id AND d.is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        $previos = null;
        // Evitar que una unidad sea su propio padre
        if ($this->id && $this->id_padre === $this->id) {
            $this->id_padre = null;
        }
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE departamentos SET
                                nombre = :nombre,
                                descripcion = :descripcion,
                                id_padre = :id_padre,
                                tipo_unidad = :tipo_unidad,
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO departamentos (nombre, descripcion, id_padre, tipo_unidad, created_by)
                              VALUES (:nombre, :descripcion, :id_padre, :tipo_unidad, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':id_padre', $this->id_padre);
        $this->db->bind(':tipo_unidad', $this->tipo_unidad);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('departamentos', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, ['nombre' => $this->nombre, 'tipo_unidad' => $this->tipo_unidad], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE departamentos SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('departamentos', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
