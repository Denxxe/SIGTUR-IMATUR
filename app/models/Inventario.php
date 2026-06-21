<?php
/**
 * Clase Inventario: Modelo para la gestión de bienes institucionales
 */
class Inventario extends Model {

    // ── Fuente única de verdad para enums de este módulo ─────────────────────
    const CONDICIONES       = ['Nuevo', 'Bueno', 'Regular', 'Dañado', 'En Reparación'];
    const CONDICION_DEFAULT = 'Bueno';
    /** CSS class por condición (para vistas) */
    const CONDICION_BADGES  = [
        'Nuevo'         => 'sig-badge--success',
        'Bueno'         => 'sig-badge--info',
        'Regular'       => 'sig-badge--warning',
        'Dañado'        => 'sig-badge--danger',
        'En Reparación' => 'sig-badge--warning',
    ];
    // Tipo de bien (mig.044): Durable = inventariable (Código BN); Fungible = consumible (cantidad)
    const TIPOS_BIEN        = ['Durable', 'Fungible'];
    const TIPO_BIEN_DEFAULT = 'Durable';
    const TIPO_BIEN_BADGES  = ['Durable' => 'sig-badge--info', 'Fungible' => 'sig-badge--neutral'];

    private ?int $id;
    private ?int $id_categoria;
    private ?int $id_ubicacion;
    private ?string $codigo_bn;
    private string $nombre;
    private string $descripcion;
    private string $marca;
    private string $modelo;
    private ?string $serial;
    private string $condicion; // 'Nuevo', 'Bueno', 'Regular', 'Dañado'
    private string $observaciones;
    private string $tipo_bien;
    private int $cantidad;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_categoria = $data['id_categoria'] ?? null;
            $this->id_ubicacion = $data['id_ubicacion'] ?? null;
            $this->codigo_bn = $data['codigo_bn'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->marca = $data['marca'] ?? '';
            $this->modelo = $data['modelo'] ?? '';
            $this->serial = $data['serial'] ?? null;
            $this->condicion = $data['condicion'] ?? 'Bueno';
            $this->observaciones = $data['observaciones'] ?? '';
            $this->tipo_bien = in_array($data['tipo_bien'] ?? '', self::TIPOS_BIEN, true) ? $data['tipo_bien'] : self::TIPO_BIEN_DEFAULT;
            $this->cantidad = max(1, (int)($data['cantidad'] ?? 1));
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT i.*, c.nombre as categoria, u.nombre as ubicacion 
                    FROM inventario i
                    INNER JOIN categorias c ON i.id_categoria = c.id
                    INNER JOIN ubicaciones u ON i.id_ubicacion = u.id
                    WHERE i.is_active = TRUE
                    ORDER BY i.nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM inventario WHERE id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Verifica si ya existe un bien con ese codigo_bn (excluyendo el propio registro en edición).
     * Retorna el id existente o null.
     */
    public static function findByCodigoBn(string $codigo, ?int $excludeId = null): ?int {
        if ($codigo === '') return null;
        $db = new Database();
        $db->query("SELECT id FROM inventario WHERE codigo_bn = :codigo AND is_active = TRUE" . ($excludeId ? " AND id <> :excl" : ""));
        $db->bind(':codigo', $codigo);
        if ($excludeId) $db->bind(':excl', $excludeId);
        $row = $db->single();
        return $row ? (int)$row->id : null;
    }

    /**
     * Verifica si ya existe un bien con ese serial (excluyendo el propio registro en edición).
     * Retorna el id existente o null.
     */
    public static function findBySerial(string $serial, ?int $excludeId = null): ?int {
        if ($serial === '') return null;
        $db = new Database();
        $db->query("SELECT id FROM inventario WHERE serial = :serial AND is_active = TRUE" . ($excludeId ? " AND id <> :excl" : ""));
        $db->bind(':serial', $serial);
        if ($excludeId) $db->bind(':excl', $excludeId);
        $row = $db->single();
        return $row ? (int)$row->id : null;
    }

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE inventario SET id_categoria=:id_categoria, id_ubicacion=:id_ubicacion, codigo_bn=:codigo_bn,
                              nombre=:nombre, descripcion=:descripcion, marca=:marca, modelo=:modelo, serial=:serial,
                              condicion=:condicion, observaciones=:observaciones, tipo_bien=:tipo_bien, cantidad=:cantidad,
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO inventario (id_categoria, id_ubicacion, codigo_bn, nombre, descripcion, marca, modelo, serial, condicion, observaciones, tipo_bien, cantidad, created_by)
                              VALUES (:id_categoria, :id_ubicacion, :codigo_bn, :nombre, :descripcion, :marca, :modelo, :serial, :condicion, :observaciones, :tipo_bien, :cantidad, :user_id)");
        }
        $this->db->bind(':id_categoria', $this->id_categoria);
        $this->db->bind(':id_ubicacion', $this->id_ubicacion);
        $this->db->bind(':codigo_bn', $this->codigo_bn);
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':marca', $this->marca);
        $this->db->bind(':modelo', $this->modelo);
        $this->db->bind(':serial', $this->serial);
        $this->db->bind(':condicion', $this->condicion);
        $this->db->bind(':observaciones', $this->observaciones);
        $this->db->bind(':tipo_bien', $this->tipo_bien);
        $this->db->bind(':cantidad', $this->cantidad);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('inventario', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, ['nombre' => $this->nombre, 'codigo_bn' => $this->codigo_bn, 'condicion' => $this->condicion, 'tipo_bien' => $this->tipo_bien], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE inventario SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('inventario', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
