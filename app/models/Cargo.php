<?php
/**
 * Clase Cargo: Modelo para la tabla cargos
 */
class Cargo extends Model {
    // Niveles de jerarquía (sucesión de responsabilidad, de mayor a menor) — patrón H-07.
    const NIVELES = ['Presidencia', 'Dirección', 'Coordinación', 'Adscrito'];
    const NIVEL_DEFAULT = 'Adscrito';
    // Orden de la sucesión (1 = mayor responsabilidad) para ordenar/agrupar.
    const ORDEN_NIVEL = ['Presidencia' => 1, 'Dirección' => 2, 'Coordinación' => 3, 'Adscrito' => 4];

    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private ?string $nivel_jerarquico;
    private bool $is_active;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->nivel_jerarquico = !empty($data['nivel_jerarquico']) ? $data['nivel_jerarquico'] : self::NIVEL_DEFAULT;
            $this->is_active = $data['is_active'] ?? true;
        }
    }

    // --- Getters y Setters ---
    public function getId() { return $this->id; }
    public function getNombre() { return $this->nombre; }
    public function setNombre($nombre) { $this->nombre = $nombre; }
    public function getDescripcion() { return $this->descripcion; }
    public function setDescripcion($descripcion) { $this->descripcion = $descripcion; }
    public function getNivel() { return $this->nivel_jerarquico; }

    /**
     * Obtener todos los cargos activos, ordenados por jerarquía (Presidencia→Adscrito) y nombre.
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM cargos WHERE is_active = TRUE
                    ORDER BY CASE nivel_jerarquico
                                WHEN 'Presidencia' THEN 1 WHEN 'Dirección' THEN 2
                                WHEN 'Coordinación' THEN 3 WHEN 'Adscrito' THEN 4 ELSE 5 END,
                             nombre ASC");
        return $db->resultSet();
    }

    /**
     * Buscar un cargo por ID
     */
    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM cargos WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    /**
     * Guardar o actualizar registro
     */
    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE cargos SET
                                nombre = :nombre,
                                descripcion = :descripcion,
                                nivel_jerarquico = :nivel,
                                updated_at = CURRENT_TIMESTAMP,
                                updated_by = :user_id
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO cargos (nombre, descripcion, nivel_jerarquico, created_by)
                              VALUES (:nombre, :descripcion, :nivel, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':nivel', $this->nivel_jerarquico);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('cargos', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, ['nombre' => $this->nombre, 'nivel_jerarquico' => $this->nivel_jerarquico], $user_id);
        return $result;
    }

    /**
     * Borrado lógico (Soft Delete)
     */
    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE cargos SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('cargos', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
