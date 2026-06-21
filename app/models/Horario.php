<?php
/**
 * Clase Horario: catálogo de horarios/turnos asignables a empleados.
 * Incluye las modalidades institucionales (Estándar, OAC Matutino/Vespertino,
 * Servicios Generales) y horarios personalizados (p. ej. ajustes por estudio/discapacidad).
 */
class Horario extends Model {
    // Patrones de días laborales (selección controlada, no texto libre).
    // clave = valor almacenado/mostrado · valor = etiqueta en el formulario.
    const DIAS_OPCIONES = [
        'L-V'                => 'Lunes a Viernes (L-V)',
        'L-S'                => 'Lunes a Sábado (L-S)',
        'L-D'                => 'Todos los días (L-D)',
        'L-V (rotación A/B)' => 'L-V con rotación A/B (días alternos)',
        'Días intercalados'  => 'Días intercalados',
    ];
    const DIAS_DEFAULT = 'L-V';

    private ?int $id;
    private string $nombre;
    private ?string $hora_entrada;
    private ?string $hora_salida;
    private ?string $dias_laborales;
    private ?string $descripcion;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->hora_entrada = !empty($data['hora_entrada']) ? $data['hora_entrada'] : null;
            $this->hora_salida = !empty($data['hora_salida']) ? $data['hora_salida'] : null;
            // Solo se acepta un patrón de la lista; cualquier otro valor cae al default.
            $this->dias_laborales = in_array($data['dias_laborales'] ?? '', array_keys(self::DIAS_OPCIONES), true)
                ? $data['dias_laborales'] : self::DIAS_DEFAULT;
            $this->descripcion = !empty($data['descripcion']) ? $data['descripcion'] : null;
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT * FROM horarios WHERE is_active = TRUE ORDER BY nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT * FROM horarios WHERE id = :id AND is_active = TRUE");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE horarios SET
                                nombre = :nombre, hora_entrada = :hora_entrada, hora_salida = :hora_salida,
                                dias_laborales = :dias_laborales, descripcion = :descripcion,
                                updated_at = CURRENT_TIMESTAMP, updated_by = :user_id
                              WHERE id = :id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO horarios (nombre, hora_entrada, hora_salida, dias_laborales, descripcion, created_by)
                              VALUES (:nombre, :hora_entrada, :hora_salida, :dias_laborales, :descripcion, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':hora_entrada', $this->hora_entrada);
        $this->db->bind(':hora_salida', $this->hora_salida);
        $this->db->bind(':dias_laborales', $this->dias_laborales);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('horarios', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, ['nombre' => $this->nombre], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE horarios SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('horarios', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
