<?php
class Taller extends Model {
    private ?int    $id;
    private string  $nombre;
    private string  $descripcion;
    private ?string $fecha_inicio;
    private ?string $fecha_fin;
    private ?string $hora_inicio;
    private ?string $hora_fin;
    private ?int    $id_ubicacion_formacion;
    private ?int    $id_facilitador;
    private int     $cupo_maximo;
    private string  $estado;
    private string  $tipo_actividad;
    private ?int    $id_oficio;
    private bool    $es_interna;
    private ?string $tipo_ente;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id                     = $data['id'] ?? null;
            $this->nombre                 = $data['nombre'] ?? '';
            $this->descripcion            = $data['descripcion'] ?? '';
            $this->fecha_inicio           = $data['fecha_inicio'] ?? date('Y-m-d');
            $this->fecha_fin              = $data['fecha_fin'] ?? null;
            $this->hora_inicio            = $data['hora_inicio'] ?? null;
            $this->hora_fin               = $data['hora_fin'] ?? null;
            $this->id_ubicacion_formacion = $data['id_ubicacion_formacion'] ?? null;
            $this->id_facilitador         = $data['id_facilitador'] ?? null;
            $this->cupo_maximo            = $data['cupo_maximo'] ?? 30;
            $this->estado                 = $data['estado'] ?? 'Programado';
            $this->tipo_actividad         = $data['tipo_actividad'] ?? 'Taller';
            $this->id_oficio              = $data['id_oficio'] ?? null;
            $this->es_interna             = !empty($data['es_interna']);
            $this->tipo_ente              = $data['tipo_ente'] ?? null;
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT t.*, uf.nombre AS ubicacion, uf.es_sede_propia,
                           p.nombre AS facilitador_nombre, p.apellido AS facilitador_apellido,
                           (SELECT COUNT(*) FROM participantes_taller pt WHERE pt.id_taller = t.id) AS total_inscritos
                    FROM talleres t
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    LEFT JOIN empleados e  ON t.id_facilitador = e.id
                    LEFT JOIN personas p   ON e.id_persona = p.id
                    WHERE t.is_active = TRUE
                    ORDER BY t.fecha_inicio DESC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT t.*, uf.nombre AS ubicacion, uf.es_sede_propia,
                           p.nombre AS facilitador_nombre, p.apellido AS facilitador_apellido
                    FROM talleres t
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    LEFT JOIN empleados e  ON t.id_facilitador = e.id
                    LEFT JOIN personas p   ON e.id_persona = p.id
                    WHERE t.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE talleres
                              SET nombre=:nombre, descripcion=:descripcion,
                                  fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin,
                                  hora_inicio=:hora_inicio, hora_fin=:hora_fin,
                                  id_ubicacion_formacion=:id_ubicacion_formacion,
                                  id_facilitador=:id_facilitador, cupo_maximo=:cupo_maximo,
                                  estado=:estado, tipo_actividad=:tipo_actividad,
                                  es_interna=:es_interna, tipo_ente=:tipo_ente,
                                  updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO talleres
                              (nombre, descripcion, fecha_inicio, fecha_fin,
                               hora_inicio, hora_fin, id_ubicacion_formacion,
                               id_facilitador, cupo_maximo, estado, tipo_actividad,
                               es_interna, tipo_ente, id_oficio, created_by)
                              VALUES
                              (:nombre, :descripcion, :fecha_inicio, :fecha_fin,
                               :hora_inicio, :hora_fin, :id_ubicacion_formacion,
                               :id_facilitador, :cupo_maximo, :estado, :tipo_actividad,
                               :es_interna, :tipo_ente, :id_oficio, :user_id)");
            $this->db->bind(':id_oficio', $this->id_oficio);
        }
        $this->db->bind(':nombre',                 $this->nombre);
        $this->db->bind(':descripcion',            $this->descripcion);
        $this->db->bind(':fecha_inicio',           $this->fecha_inicio);
        $this->db->bind(':fecha_fin',              $this->fecha_fin);
        $this->db->bind(':hora_inicio',            $this->hora_inicio);
        $this->db->bind(':hora_fin',               $this->hora_fin);
        $this->db->bind(':id_ubicacion_formacion', $this->id_ubicacion_formacion);
        $this->db->bind(':id_facilitador',         $this->id_facilitador);
        $this->db->bind(':cupo_maximo',            $this->cupo_maximo);
        $this->db->bind(':estado',                 $this->estado);
        $this->db->bind(':tipo_actividad',         $this->tipo_actividad);
        $this->db->bind(':es_interna',             $this->es_interna);
        $this->db->bind(':tipo_ente',              $this->tipo_ente);
        $this->db->bind(':user_id',                $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE talleres SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }

    // ── Participantes ────────────────────────────────────────────────────────

    // LEFT JOIN para soportar participantes sin id_persona (niños/as — RN-F16)
    public static function getParticipantes($id_taller) {
        $db = new Database();
        $db->query("SELECT pt.*,
                           p.cedula, p.nombre, p.apellido, p.telefono
                    FROM participantes_taller pt
                    LEFT JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id_taller = :id_taller
                    ORDER BY COALESCE(p.apellido, pt.apellido_libre) ASC, pt.id ASC");
        $db->bind(':id_taller', $id_taller);
        return $db->resultSet();
    }

    public static function countParticipantes($id_taller): int {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM participantes_taller WHERE id_taller = :id");
        $db->bind(':id', $id_taller);
        $row = $db->single();
        return (int)($row->total ?? 0);
    }

    // Inscribir persona con cédula registrada en el sistema
    public static function inscribir($id_taller, $id_persona, $user_id = null, bool $esBrigadista = false) {
        $db = new Database();
        $db->query("INSERT INTO participantes_taller (id_taller, id_persona, es_brigadista, created_by)
                    VALUES (:id_taller, :id_persona, :brigadista, :user_id)");
        $db->bind(':id_taller',  $id_taller);
        $db->bind(':id_persona', $id_persona);
        $db->bind(':brigadista', $esBrigadista);
        $db->bind(':user_id',    $user_id);
        return $db->execute();
    }

    // Inscribir participante sin cédula — niño/a (RN-F16)
    public static function inscribirLibre($id_taller, array $datos, $user_id = null) {
        $db = new Database();
        $db->query("INSERT INTO participantes_taller
                    (id_taller, nombre_libre, apellido_libre, cedula_libre,
                     nombre_docente, cedula_docente, created_by)
                    VALUES (:id_taller, :nombre, :apellido, :cedula,
                            :nom_doc, :ced_doc, :user_id)");
        $db->bind(':id_taller', $id_taller);
        $db->bind(':nombre',    $datos['nombre_libre']);
        $db->bind(':apellido',  $datos['apellido_libre'] ?? null);
        $db->bind(':cedula',    $datos['cedula_libre'] ?? null);
        $db->bind(':nom_doc',   $datos['nombre_docente'] ?? null);
        $db->bind(':ced_doc',   $datos['cedula_docente'] ?? null);
        $db->bind(':user_id',   $user_id);
        return $db->execute();
    }

    // Verificar si una persona ha recibido formación previa (para prerequisito de rutas)
    public static function personaRecibioFormacion(int $id_persona): bool {
        $db = new Database();
        $db->query("SELECT 1 FROM participantes_taller pt
                    JOIN talleres t ON pt.id_taller = t.id
                    WHERE pt.id_persona = :id AND pt.asistio = TRUE AND t.is_active = TRUE
                    LIMIT 1");
        $db->bind(':id', $id_persona);
        return (bool)$db->single();
    }

    public static function buscarPersonaPorCedula(string $cedula) {
        $db = new Database();
        $db->query("SELECT id, cedula, nombre, apellido
                    FROM personas WHERE cedula = :cedula AND is_active = TRUE");
        $db->bind(':cedula', $cedula);
        return $db->single();
    }

    // Crear registro de oficio para actividad externa y retornar su ID (RN-F06)
    public static function crearOficio(array $data, $user_id): int {
        $db = new Database();
        $db->query("INSERT INTO oficios (numero, fecha, id_institucion, asunto, created_by)
                    VALUES (:numero, :fecha, :inst, :asunto, :uid)
                    RETURNING id");
        $db->bind(':numero', $data['numero'] ?: null);
        $db->bind(':fecha',  $data['fecha']);
        $db->bind(':inst',   $data['id_institucion']);
        $db->bind(':asunto', $data['asunto'] ?: null);
        $db->bind(':uid',    $user_id);
        $row = $db->single();
        return (int)$row->id;
    }

    // ── Informe demográfico ──────────────────────────────────────────────────

    public static function getInforme($id_taller) {
        $db = new Database();
        $db->query("SELECT * FROM taller_informes WHERE id_taller = :id_taller");
        $db->bind(':id_taller', $id_taller);
        return $db->single();
    }

    public static function saveInforme($data) {
        $db  = new Database();
        $inf = self::getInforme($data['id_taller']);
        if ($inf) {
            $db->query("UPDATE taller_informes
                        SET unidad_estadal=:unidad, lugar_exacto=:lugar,
                            instituciones_presentes=:inst, mujeres=:m, hombres=:h,
                            ninas=:ni, ninos=:no, total_atendidas=:tot,
                            resumen_actividad=:res, updated_at=CURRENT_TIMESTAMP
                        WHERE id_taller = :id_taller");
        } else {
            $db->query("INSERT INTO taller_informes
                        (id_taller, unidad_estadal, lugar_exacto, instituciones_presentes,
                         mujeres, hombres, ninas, ninos, total_atendidas, resumen_actividad, created_by)
                        VALUES (:id_taller, :unidad, :lugar, :inst, :m, :h, :ni, :no, :tot, :res, :user_id)");
            $db->bind(':user_id', $_SESSION['user_id'] ?? null);
        }
        $db->bind(':id_taller', $data['id_taller']);
        $db->bind(':unidad',    $data['unidad_estadal']);
        $db->bind(':lugar',     $data['lugar_exacto']);
        $db->bind(':inst',      $data['instituciones_presentes']);
        $db->bind(':m',         $data['mujeres']);
        $db->bind(':h',         $data['hombres']);
        $db->bind(':ni',        $data['ninas']);
        $db->bind(':no',        $data['ninos']);
        $db->bind(':tot',       (int)$data['mujeres'] + (int)$data['hombres'] + (int)$data['ninas'] + (int)$data['ninos']);
        $db->bind(':res',       $data['resumen_actividad']);
        return $db->execute();
    }
}
