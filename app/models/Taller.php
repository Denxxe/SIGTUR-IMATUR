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
    private bool    $es_interna;
    private ?string $tipo_ente;
    private ?string $motivo_cancelacion;

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
            $this->es_interna             = !empty($data['es_interna']);
            $this->tipo_ente              = $data['tipo_ente'] ?? null;
            $this->motivo_cancelacion     = $data['motivo_cancelacion'] ?? null;
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT t.*, uf.nombre AS ubicacion, uf.es_sede_propia,
                           p.nombre AS facilitador_nombre, p.apellido AS facilitador_apellido,
                           (SELECT COUNT(*) FROM participantes_taller pt WHERE pt.id_taller = t.id AND pt.is_active = TRUE) AS total_inscritos,
                           (SELECT COUNT(*) FROM taller_evidencias   te WHERE te.id_taller = t.id AND te.is_active = TRUE) AS total_evidencias
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
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE talleres
                              SET nombre=:nombre, descripcion=:descripcion,
                                  fecha_inicio=:fecha_inicio, fecha_fin=:fecha_fin,
                                  hora_inicio=:hora_inicio, hora_fin=:hora_fin,
                                  id_ubicacion_formacion=:id_ubicacion_formacion,
                                  id_facilitador=:id_facilitador, cupo_maximo=:cupo_maximo,
                                  estado=:estado, tipo_actividad=:tipo_actividad,
                                  es_interna=:es_interna, tipo_ente=:tipo_ente,
                                  motivo_cancelacion=:motivo_cancelacion,
                                  updated_at=CURRENT_TIMESTAMP, updated_by=:user_id
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO talleres
                              (nombre, descripcion, fecha_inicio, fecha_fin,
                               hora_inicio, hora_fin, id_ubicacion_formacion,
                               id_facilitador, cupo_maximo, estado, tipo_actividad,
                               es_interna, tipo_ente, motivo_cancelacion, created_by)
                              VALUES
                              (:nombre, :descripcion, :fecha_inicio, :fecha_fin,
                               :hora_inicio, :hora_fin, :id_ubicacion_formacion,
                               :id_facilitador, :cupo_maximo, :estado, :tipo_actividad,
                               :es_interna, :tipo_ente, :motivo_cancelacion, :user_id)");
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
        $this->db->bind(':motivo_cancelacion',     $this->motivo_cancelacion);
        $this->db->bind(':user_id',                $user_id);
        $result = $this->db->execute();
        $this->audit('talleres', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, ['nombre' => $this->nombre, 'estado' => $this->estado, 'tipo_actividad' => $this->tipo_actividad, 'fecha_inicio' => $this->fecha_inicio, 'fecha_fin' => $this->fecha_fin, 'cupo_maximo' => $this->cupo_maximo], $user_id);
        return $result;
    }

    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE talleres SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('talleres', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }

    // ── Participantes ────────────────────────────────────────────────────────

    // LEFT JOIN para soportar participantes sin id_persona (niños/as — RN-F16)
    public static function getParticipantes($id_taller) {
        $db = new Database();
        $db->query("SELECT pt.*,
                           p.cedula, p.nombre, p.apellido, p.telefono,
                           p.correo, p.genero, p.fecha_nacimiento,
                           p.parroquia_id, p.direccion,
                           par.nombre  AS parroquia_nombre,
                           m.nombre    AS municipio_nombre,
                           par2.nombre AS parroquia_libre_nombre,
                           m2.nombre   AS municipio_libre_nombre
                    FROM participantes_taller pt
                    LEFT JOIN personas  p    ON pt.id_persona        = p.id
                    LEFT JOIN parroquia par  ON p.parroquia_id       = par.id
                    LEFT JOIN municipio m    ON par.id_municipio     = m.id
                    LEFT JOIN parroquia par2 ON pt.parroquia_id_libre = par2.id
                    LEFT JOIN municipio m2   ON par2.id_municipio    = m2.id
                    WHERE pt.id_taller = :id_taller AND pt.is_active = TRUE
                    ORDER BY COALESCE(p.apellido, pt.apellido_libre) ASC, pt.id ASC");
        $db->bind(':id_taller', $id_taller);
        return $db->resultSet();
    }

    public static function estaInscrito(int $idTaller, int $idPersona): bool {
        $db = new Database();
        $db->query("SELECT 1 FROM participantes_taller WHERE id_taller = :t AND id_persona = :p AND is_active = TRUE");
        $db->bind(':t', $idTaller);
        $db->bind(':p', $idPersona);
        return (bool)$db->single();
    }

    public static function marcarAsistencia(int $id, bool $asistio, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_taller SET asistio = :a, updated_at = NOW(), updated_by = :u WHERE id = :id AND is_active = TRUE");
        $db->bind(':a', $asistio);
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        $db->execute();
    }

    public static function marcarAsistenciaMasiva(int $idTaller, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_taller SET asistio = TRUE, updated_at = NOW(), updated_by = :u WHERE id_taller = :t AND is_active = TRUE AND asistio = FALSE");
        $db->bind(':u', $userId);
        $db->bind(':t', $idTaller);
        $db->execute();
    }

    public static function getHistorialPersona(int $idPersona): array {
        $db = new Database();
        $db->query("SELECT t.nombre, t.tipo_actividad, t.fecha_inicio, t.estado, pt.asistio,
                           uf.nombre AS ubicacion
                    FROM participantes_taller pt
                    JOIN talleres t ON pt.id_taller = t.id
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    WHERE pt.id_persona = :id AND pt.is_active = TRUE AND t.is_active = TRUE
                    ORDER BY t.fecha_inicio DESC
                    LIMIT 30");
        $db->bind(':id', $idPersona);
        return $db->resultSet() ?: [];
    }

    public static function desinscribir(int $id, $userId): bool {
        $db = new Database();
        $db->query("UPDATE participantes_taller SET is_active = FALSE, deleted_at = NOW(), deleted_by = :u WHERE id = :id");
        $db->bind(':u', $userId);
        $db->bind(':id', $id);
        return $db->execute();
    }

    public static function countParticipantes($id_taller): int {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM participantes_taller WHERE id_taller = :id AND is_active = TRUE");
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
        $result = $db->execute();
        self::auditStatic('participantes_taller', 'INSERT', null, null, ['id_taller' => $id_taller, 'id_persona' => $id_persona, 'es_brigadista' => $esBrigadista], $user_id);
        return $result;
    }

    // Inscribir participante sin cédula — niño/a (RN-F16)
    public static function inscribirLibre($id_taller, array $datos, $user_id = null) {
        $db = new Database();
        $db->query("INSERT INTO participantes_taller
                    (id_taller, nombre_libre, apellido_libre, cedula_libre,
                     nombre_docente, cedula_docente,
                     fecha_nac_libre, genero_libre, parroquia_id_libre, direccion_libre,
                     created_by)
                    VALUES (:id_taller, :nombre, :apellido, :cedula,
                            :nom_doc, :ced_doc,
                            :fecha_nac, :genero, :parroquia, :direccion,
                            :user_id)");
        $db->bind(':id_taller',  $id_taller);
        $db->bind(':nombre',     $datos['nombre_libre']);
        $db->bind(':apellido',   $datos['apellido_libre'] ?? null);
        $db->bind(':cedula',     $datos['cedula_libre'] ?? null);
        $db->bind(':nom_doc',    $datos['nombre_docente'] ?? null);
        $db->bind(':ced_doc',    $datos['cedula_docente'] ?? null);
        $db->bind(':fecha_nac',  $datos['fecha_nac_libre'] ?? null);
        $db->bind(':genero',     $datos['genero_libre'] ?? null);
        $db->bind(':parroquia',  $datos['parroquia_id_libre'] ?? null);
        $db->bind(':direccion',  $datos['direccion_libre'] ?? null);
        $db->bind(':user_id',    $user_id);
        $result = $db->execute();
        self::auditStatic('participantes_taller', 'INSERT', null, null, ['id_taller' => $id_taller, 'nombre_libre' => $datos['nombre_libre'], 'cedula_libre' => $datos['cedula_libre'] ?? null], $user_id);
        return $result;
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
        $db->query("SELECT id, cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento,
                           parroquia_id, direccion
                    FROM personas WHERE cedula = :cedula AND is_active = TRUE");
        $db->bind(':cedula', $cedula);
        return $db->single();
    }

    public static function crearPersona(array $data, $userId): int {
        $db = new Database();
        $db->query("INSERT INTO personas (cedula, nombre, apellido, telefono, correo, genero, fecha_nacimiento, parroquia_id, direccion, created_by)
                    VALUES (:cedula, :nombre, :apellido, :telefono, :correo, :genero, :fecha_nacimiento, :parroquia_id, :direccion, :uid)
                    RETURNING id");
        $db->bind(':cedula',           $data['cedula']);
        $db->bind(':nombre',           $data['nombre']);
        $db->bind(':apellido',         $data['apellido']);
        $db->bind(':telefono',         $data['telefono'] ?? null);
        $db->bind(':correo',           $data['correo'] ?? null);
        $db->bind(':genero',           $data['genero'] ?? null);
        $db->bind(':fecha_nacimiento', $data['fecha_nacimiento'] ?? null);
        $db->bind(':parroquia_id',     $data['parroquia_id'] ?? null);
        $db->bind(':direccion',        $data['direccion'] ?? null);
        $db->bind(':uid',              $userId);
        $row = $db->single();
        if (!$row) throw new Exception('Error al registrar los datos de la persona.');
        self::auditStatic('personas', 'INSERT', (int)$row->id, null, [
            'cedula'   => $data['cedula'],
            'nombre'   => $data['nombre'],
            'apellido' => $data['apellido'],
        ], $userId);
        return (int)$row->id;
    }

    public static function actualizarPersona(int $id, array $campos, $userId): void {
        if (empty($campos)) return;
        $db = new Database();
        $sets = implode(', ', array_map(fn($k) => "$k = :$k", array_keys($campos)));
        $db->query("UPDATE personas SET $sets, updated_at = NOW(), updated_by = :uid WHERE id = :id");
        foreach ($campos as $k => $v) {
            $db->bind(":$k", $v);
        }
        $db->bind(':uid', $userId);
        $db->bind(':id',  $id);
        $db->execute();
    }

    // Devuelve un participante puntual (para edición)
    public static function getParticipante(int $id_pt) {
        $db = new Database();
        $db->query("SELECT pt.*, p.cedula, p.nombre, p.apellido, p.telefono, p.correo,
                           p.genero, p.fecha_nacimiento, p.parroquia_id, p.direccion
                    FROM participantes_taller pt
                    LEFT JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id = :id AND pt.is_active = TRUE");
        $db->bind(':id', $id_pt);
        return $db->single();
    }

    // Actualiza los campos libres de un participante sin cédula (RN-F16)
    public static function actualizarParticipanteLibre(int $id_pt, array $datos, $userId): void {
        $db = new Database();
        $db->query("UPDATE participantes_taller
                    SET nombre_libre=:nom, apellido_libre=:ape, cedula_libre=:ced,
                        nombre_docente=:ndoc, cedula_docente=:cdoc,
                        fecha_nac_libre=:fnac, genero_libre=:gen,
                        parroquia_id_libre=:parr, direccion_libre=:dir,
                        updated_at=NOW(), updated_by=:uid
                    WHERE id=:id AND id_persona IS NULL AND is_active=TRUE");
        $db->bind(':nom',  $datos['nombre_libre']);
        $db->bind(':ape',  $datos['apellido_libre'] ?? null);
        $db->bind(':ced',  $datos['cedula_libre']   ?? null);
        $db->bind(':ndoc', $datos['nombre_docente'] ?? null);
        $db->bind(':cdoc', $datos['cedula_docente'] ?? null);
        $db->bind(':fnac', $datos['fecha_nac_libre'] ?? null);
        $db->bind(':gen',  $datos['genero_libre']    ?? null);
        $db->bind(':parr', $datos['parroquia_id_libre'] ?? null);
        $db->bind(':dir',  $datos['direccion_libre'] ?? null);
        $db->bind(':uid',  $userId);
        $db->bind(':id',   $id_pt);
        $db->execute();
        self::auditStatic('participantes_taller', 'UPDATE', $id_pt, null, ['nombre_libre' => $datos['nombre_libre']], $userId);
    }

    // ── Informe demográfico ──────────────────────────────────────────────────

    // Genera o actualiza el informe demográfico automáticamente desde participantes activos.
    // Preserva lugar_exacto, instituciones_presentes y resumen si ya fueron editados manualmente.
    public static function autoGenerarInforme(int $id_taller): void {
        $db = new Database();

        // Calcular demografía real desde participantes activos
        $db->query("SELECT
                        COUNT(CASE WHEN pt.id_persona IS NOT NULL AND p.genero = 'F' THEN 1 END) AS mujeres,
                        COUNT(CASE WHEN pt.id_persona IS NOT NULL AND p.genero = 'M' THEN 1 END) AS hombres,
                        COUNT(CASE WHEN pt.id_persona IS NULL AND pt.genero_libre = 'F'  THEN 1 END) AS ninas,
                        COUNT(CASE WHEN pt.id_persona IS NULL AND pt.genero_libre = 'M'  THEN 1 END) AS ninos
                    FROM participantes_taller pt
                    LEFT JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id_taller = :id AND pt.is_active = TRUE");
        $db->bind(':id', $id_taller);
        $dem = $db->single();

        $mujeres = (int)($dem->mujeres ?? 0);
        $hombres = (int)($dem->hombres ?? 0);
        $ninas   = (int)($dem->ninas   ?? 0);
        $ninos   = (int)($dem->ninos   ?? 0);
        $total   = $mujeres + $hombres + $ninas + $ninos;

        // Obtener sede del taller para el campo lugar_exacto
        $db->query("SELECT uf.nombre AS sede FROM talleres t
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    WHERE t.id = :id");
        $db->bind(':id', $id_taller);
        $tallerRow = $db->single();

        $existing = self::getInforme($id_taller);

        $dataInforme = [
            'id_taller'               => $id_taller,
            'unidad_estadal'          => $existing->unidad_estadal          ?? 'Sucre',
            'lugar_exacto'            => !empty($existing->lugar_exacto)          ? $existing->lugar_exacto          : ($tallerRow->sede ?? ''),
            'instituciones_presentes' => $existing->instituciones_presentes ?? '',
            'mujeres'                 => $mujeres,
            'hombres'                 => $hombres,
            'ninas'                   => $ninas,
            'ninos'                   => $ninos,
            'resumen_actividad'       => !empty($existing->resumen_actividad)
                ? $existing->resumen_actividad
                : 'Actividad finalizada con ' . $total . ' participante(s) registrado(s). Complete el resumen desde Informe Oficial si es necesario.',
        ];

        self::saveInforme($dataInforme);
    }

    public static function getInforme($id_taller) {
        $db = new Database();
        $db->query("SELECT * FROM taller_informes WHERE id_taller = :id_taller");
        $db->bind(':id_taller', $id_taller);
        return $db->single();
    }

    public static function saveInforme($data) {
        $db     = new Database();
        $inf    = self::getInforme($data['id_taller']);
        $userId = $_SESSION['user_id'] ?? null;
        $op     = $inf ? 'UPDATE' : 'INSERT';
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
            $db->bind(':user_id', $userId);
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
        $result = $db->execute();
        $total  = (int)$data['mujeres'] + (int)$data['hombres'] + (int)$data['ninas'] + (int)$data['ninos'];
        self::auditStatic('taller_informes', $op, (int)$data['id_taller'], $inf ?: null, ['id_taller' => $data['id_taller'], 'mujeres' => $data['mujeres'], 'hombres' => $data['hombres'], 'ninas' => $data['ninas'], 'ninos' => $data['ninos'], 'total_atendidas' => $total], $userId);
        return $result;
    }

    // ── Evidencias ───────────────────────────────────────────────────────────

    public static function countEvidencias(int $id_taller): int {
        $db = new Database();
        $db->query("SELECT COUNT(*) AS total FROM taller_evidencias WHERE id_taller = :id AND is_active = TRUE");
        $db->bind(':id', $id_taller);
        $row = $db->single();
        return (int)($row->total ?? 0);
    }

    public static function getEvidencias(int $id_taller): array {
        $db = new Database();
        $db->query("SELECT * FROM taller_evidencias WHERE id_taller = :id AND is_active = TRUE ORDER BY uploaded_at ASC");
        $db->bind(':id', $id_taller);
        return $db->resultSet() ?: [];
    }

    public static function saveEvidencias(int $id_taller, array $archivos, $userId): void {
        $db = new Database();
        foreach ($archivos as $arch) {
            $db->query("INSERT INTO taller_evidencias (id_taller, archivo, nombre_original, tipo_archivo, uploaded_by)
                        VALUES (:id_taller, :archivo, :nombre_original, :tipo_archivo, :uid)");
            $db->bind(':id_taller',       $id_taller);
            $db->bind(':archivo',         $arch['archivo']);
            $db->bind(':nombre_original', $arch['nombre_original']);
            $db->bind(':tipo_archivo',    $arch['tipo_archivo']);
            $db->bind(':uid',             $userId);
            $db->execute();
        }
        self::auditStatic('taller_evidencias', 'INSERT', $id_taller, null, ['id_taller' => $id_taller, 'total_subidas' => count($archivos)], $userId);
    }

    // Auto-transición Programado → En Curso cuando llega la fecha/hora de inicio
    // Se ejecuta al cargar el índice; solo afecta actividades con al menos 1 participante (RN-F12)
    public static function autoTransicionarProgramados(): void {
        $db = new Database();
        $db->query("UPDATE talleres
                    SET estado = 'En Curso', updated_at = CURRENT_TIMESTAMP
                    WHERE estado = 'Programado'
                      AND is_active = TRUE
                      AND (
                          fecha_inicio < CURRENT_DATE
                          OR (
                              fecha_inicio = CURRENT_DATE
                              AND (hora_inicio IS NULL OR hora_inicio::time <= CURRENT_TIME::time)
                          )
                      )
                      AND (SELECT COUNT(*) FROM participantes_taller pt
                           WHERE pt.id_taller = talleres.id AND pt.is_active = TRUE) > 0");
        $db->execute();
    }

    // Actualiza solo el estado y el motivo (para el cambio rápido de estado desde la tarjeta)
    public static function cambiarEstado(int $id, string $estado, ?string $motivoCancelacion, $userId): bool {
        $db = new Database();
        $db->query("UPDATE talleres
                    SET estado=:estado, motivo_cancelacion=:motivo,
                        updated_at=CURRENT_TIMESTAMP, updated_by=:uid
                    WHERE id=:id");
        $db->bind(':estado', $estado);
        $db->bind(':motivo', $motivoCancelacion);
        $db->bind(':uid',    $userId);
        $db->bind(':id',     $id);
        return $db->execute();
    }
}
