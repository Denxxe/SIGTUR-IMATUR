<?php
class Ruta extends Model {
    private ?int   $id;
    private string $nombre;
    private string $descripcion;
    private string $duracion_estimada;
    private string $nivel_dificultad;
    private string $estado;
    private ?string $fecha_visita;
    private ?string $hora_visita;
    private ?int    $id_departamento;
    private ?int    $id_facilitador;
    private int     $cupo_maximo;
    private bool    $requiere_formacion;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id                  = $data['id'] ?? null;
            $this->nombre              = $data['nombre'] ?? '';
            $this->descripcion         = $data['descripcion'] ?? '';
            $this->duracion_estimada   = $data['duracion_estimada'] ?? '';
            $this->nivel_dificultad    = $data['nivel_dificultad'] ?? 'Fácil';
            $this->estado              = $data['estado'] ?? 'Activa';
            $this->fecha_visita        = $data['fecha_visita'] ?: null;
            $this->hora_visita         = $data['hora_visita'] ?: null;
            $this->id_departamento     = $data['id_departamento'] ? (int)$data['id_departamento'] : null;
            $this->id_facilitador      = $data['id_facilitador'] ? (int)$data['id_facilitador'] : null;
            $this->cupo_maximo         = isset($data['cupo_maximo']) ? (int)$data['cupo_maximo'] : 20;
            $this->requiere_formacion  = !empty($data['requiere_formacion']);
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           p.nombre AS facilitador_nombre,
                           p.apellido AS facilitador_apellido,
                           (SELECT COUNT(*) FROM puntos_ruta pr
                            WHERE pr.id_ruta = r.id AND pr.is_active = TRUE) AS total_puntos,
                           (SELECT COUNT(*) FROM participantes_ruta prt
                            WHERE prt.id_ruta = r.id AND prt.is_active = TRUE) AS total_participantes
                    FROM rutas r
                    LEFT JOIN departamentos d  ON r.id_departamento = d.id
                    LEFT JOIN empleados     e  ON r.id_facilitador  = e.id
                    LEFT JOIN personas      p  ON e.id_persona      = p.id
                    WHERE r.is_active = TRUE
                    ORDER BY r.nombre ASC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT r.*,
                           d.nombre AS departamento_nombre,
                           p.nombre AS facilitador_nombre,
                           p.apellido AS facilitador_apellido
                    FROM rutas r
                    LEFT JOIN departamentos d ON r.id_departamento = d.id
                    LEFT JOIN empleados     e ON r.id_facilitador  = e.id
                    LEFT JOIN personas      p ON e.id_persona      = p.id
                    WHERE r.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE rutas
                              SET nombre=:nombre, descripcion=:descripcion,
                                  duracion_estimada=:duracion_estimada,
                                  nivel_dificultad=:nivel_dificultad, estado=:estado,
                                  fecha_visita=:fecha_visita, hora_visita=:hora_visita,
                                  id_departamento=:id_departamento,
                                  id_facilitador=:id_facilitador,
                                  cupo_maximo=:cupo_maximo,
                                  requiere_formacion=:requiere_formacion,
                                  updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO rutas
                              (nombre, descripcion, duracion_estimada, nivel_dificultad, estado,
                               fecha_visita, hora_visita, id_departamento, id_facilitador,
                               cupo_maximo, requiere_formacion, created_by)
                              VALUES (:nombre, :descripcion, :duracion_estimada, :nivel_dificultad,
                                      :estado, :fecha_visita, :hora_visita, :id_departamento,
                                      :id_facilitador, :cupo_maximo, :requiere_formacion, :user_id)");
        }
        $this->db->bind(':nombre',             $this->nombre);
        $this->db->bind(':descripcion',        $this->descripcion);
        $this->db->bind(':duracion_estimada',  $this->duracion_estimada);
        $this->db->bind(':nivel_dificultad',   $this->nivel_dificultad);
        $this->db->bind(':estado',             $this->estado);
        $this->db->bind(':fecha_visita',       $this->fecha_visita);
        $this->db->bind(':hora_visita',        $this->hora_visita);
        $this->db->bind(':id_departamento',    $this->id_departamento);
        $this->db->bind(':id_facilitador',     $this->id_facilitador);
        $this->db->bind(':cupo_maximo',        $this->cupo_maximo);
        $this->db->bind(':requiere_formacion', $this->requiere_formacion);
        $this->db->bind(':user_id',            $user_id);
        $result = $this->db->execute();
        $this->audit('rutas', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['nombre' => $this->nombre, 'estado' => $this->estado, 'nivel_dificultad' => $this->nivel_dificultad, 'fecha_visita' => $this->fecha_visita, 'cupo_maximo' => $this->cupo_maximo, 'requiere_formacion' => $this->requiere_formacion], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE rutas SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('rutas', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }

    public static function getPuntos($id_ruta) {
        $db = new Database();
        $db->query("SELECT * FROM puntos_ruta WHERE id_ruta = :id_ruta AND is_active = TRUE ORDER BY orden ASC");
        $db->bind(':id_ruta', $id_ruta);
        return $db->resultSet();
    }

    // ── Participantes ────────────────────────────────────────────────────────

    public static function getParticipantes($id_ruta) {
        $db = new Database();
        $db->query("SELECT prt.*,
                           p.cedula, p.nombre, p.apellido, p.telefono
                    FROM participantes_ruta prt
                    LEFT JOIN personas p ON prt.id_persona = p.id
                    WHERE prt.id_ruta = :id AND prt.is_active = TRUE
                    ORDER BY COALESCE(p.apellido, prt.apellido_libre) ASC");
        $db->bind(':id', $id_ruta);
        return $db->resultSet();
    }

    public static function countParticipantes($id_ruta): int {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM participantes_ruta WHERE id_ruta = :id AND is_active = TRUE");
        $db->bind(':id', $id_ruta);
        $row = $db->single();
        return (int)($row->total ?? 0);
    }

    public static function buscarPersonaPorCedula(string $cedula) {
        $db = new Database();
        $db->query("SELECT p.*, per.cedula, per.nombre, per.apellido, per.telefono
                    FROM personas per
                    LEFT JOIN empleados p ON per.id = p.id_persona
                    WHERE per.cedula = :cedula AND per.is_active = TRUE LIMIT 1");
        $db->bind(':cedula', $cedula);
        $row = $db->single();
        if ($row) return $row;
        // Buscar en personas directamente
        $db->query("SELECT * FROM personas WHERE cedula = :cedula AND is_active = TRUE LIMIT 1");
        $db->bind(':cedula', $cedula);
        return $db->single();
    }

    public static function inscribir(int $id_ruta, int $id_persona, int $user_id) {
        $db = new Database();
        $db->query("SELECT id FROM participantes_ruta WHERE id_ruta=:r AND id_persona=:p AND is_active=TRUE LIMIT 1");
        $db->bind(':r', $id_ruta);
        $db->bind(':p', $id_persona);
        if ($db->single()) {
            throw new Exception('Esta persona ya está inscrita en la ruta.');
        }
        $db->query("INSERT INTO participantes_ruta (id_ruta, id_persona, created_by) VALUES (:r, :p, :u)");
        $db->bind(':r', $id_ruta);
        $db->bind(':p', $id_persona);
        $db->bind(':u', $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_ruta', 'INSERT', null, null, ['id_ruta' => $id_ruta, 'id_persona' => $id_persona], $user_id);
        return $result;
    }

    public static function inscribirLibre(int $id_ruta, array $datos, int $user_id) {
        $db = new Database();
        $db->query("INSERT INTO participantes_ruta (id_ruta, nombre_libre, apellido_libre, cedula_libre, created_by)
                    VALUES (:r, :nom, :ape, :ced, :u)");
        $db->bind(':r',   $id_ruta);
        $db->bind(':nom', $datos['nombre_libre']);
        $db->bind(':ape', $datos['apellido_libre'] ?? null);
        $db->bind(':ced', $datos['cedula_libre'] ?? null);
        $db->bind(':u',   $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_ruta', 'INSERT', null, null, ['id_ruta' => $id_ruta, 'nombre_libre' => $datos['nombre_libre'], 'cedula_libre' => $datos['cedula_libre'] ?? null], $user_id);
        return $result;
    }

    public static function desinscribir(int $id_participante, int $user_id) {
        $db = new Database();
        $db->query("UPDATE participantes_ruta
                    SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:u
                    WHERE id=:id");
        $db->bind(':id', $id_participante);
        $db->bind(':u',  $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_ruta', 'DELETE', $id_participante, null, null, $user_id);
        return $result;
    }

    // ── Oficio emitido ───────────────────────────────────────────────────────

    /**
     * Genera un número correlativo, guarda el registro y devuelve el número asignado.
     */
    public static function crearOficioEmitido(int $id_ruta, array $data, int $user_id): string {
        $numero = ConfigSistema::generarNumeroOficio('ruta');
        $db     = new Database();
        $db->query("INSERT INTO oficios_emitidos
                    (numero, fecha, destinatario_nombre, destinatario_cargo, asunto, id_ruta, created_by)
                    VALUES (:num, CURRENT_DATE, :dest_nom, :dest_car, :asunto, :id_ruta, :uid)");
        $db->bind(':num',      $numero);
        $db->bind(':dest_nom', $data['destinatario_nombre'] ?? '');
        $db->bind(':dest_car', $data['destinatario_cargo']  ?? '');
        $db->bind(':asunto',   $data['asunto']              ?? '');
        $db->bind(':id_ruta',  $id_ruta);
        $db->bind(':uid',      $user_id);
        $db->execute();
        return $numero;
    }

    public static function getUltimoOficio(int $id_ruta): ?object {
        $db = new Database();
        $db->query("SELECT * FROM oficios_emitidos WHERE id_ruta = :id ORDER BY created_at DESC LIMIT 1");
        $db->bind(':id', $id_ruta);
        return $db->single() ?: null;
    }
}
