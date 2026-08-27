<?php
/**
 * Clase Ubicacion: Modelo para las ubicaciones físicas del inventario
 */
class Ubicacion extends Model {

    /**
     * Sedes de la institución (B-24). Enum centralizado, patrón H-07.
     * Son dos: la sede administrativa y la oficina del aeropuerto, cuyos bienes
     * también se controlan (esta última es además un departamento propio desde
     * la mig. 067).
     */
    const SEDES        = ['Sede Principal', 'Aeropuerto de Cumaná'];
    const SEDE_DEFAULT = 'Sede Principal';

    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private $id_departamento;   // mapea a la columna "departamento _d" (NOT NULL)
    private string $sede;
    private bool $es_deposito;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->id_departamento = !empty($data['id_departamento']) ? (int)$data['id_departamento'] : null;
            $sede = $data['sede'] ?? self::SEDE_DEFAULT;
            $this->sede = in_array($sede, self::SEDES, true) ? $sede : self::SEDE_DEFAULT;
            $this->es_deposito = !empty($data['es_deposito']);
        }
    }

    public static function all() {
        $db = new Database();
        // "departamento _d" tiene un espacio en el nombre → comillas dobles obligatorias.
        $db->query('SELECT u.id, u.nombre, u.descripcion, u.is_active,
                           u."departamento _d" AS id_departamento,
                           u.sede, u.es_deposito,
                           d.nombre AS departamento_nombre
                    FROM ubicaciones u
                    LEFT JOIN departamentos d ON u."departamento _d" = d.id
                    WHERE u.is_active = TRUE
                    ORDER BY u.es_deposito DESC, u.sede ASC, u.nombre ASC');
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query('SELECT u.id, u.nombre, u.descripcion, u.is_active,
                           u."departamento _d" AS id_departamento,
                           u.sede, u.es_deposito,
                           d.nombre AS departamento_nombre
                    FROM ubicaciones u
                    LEFT JOIN departamentos d ON u."departamento _d" = d.id
                    WHERE u.id = :id');
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query('UPDATE ubicaciones SET nombre=:nombre, descripcion=:descripcion,
                                  "departamento _d"=:id_departamento,
                                  sede=:sede, es_deposito=:es_deposito,
                                  updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id');
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query('INSERT INTO ubicaciones (nombre, descripcion, "departamento _d", sede, es_deposito, created_by)
                              VALUES (:nombre, :descripcion, :id_departamento, :sede, :es_deposito, :user_id)');
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':id_departamento', $this->id_departamento);
        $this->db->bind(':sede', $this->sede);
        $this->db->bind(':es_deposito', $this->es_deposito, \PDO::PARAM_BOOL);
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('ubicaciones', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['nombre' => $this->nombre, 'descripcion' => $this->descripcion, 'id_departamento' => $this->id_departamento, 'sede' => $this->sede, 'es_deposito' => $this->es_deposito], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE ubicaciones SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('ubicaciones', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
