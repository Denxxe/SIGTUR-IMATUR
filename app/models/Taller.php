<?php
/**
 * Clase Taller: Modelo para la tabla talleres (Formación comunitaria)
 */
class Taller extends Model {
    private ?int $id;
    private string $nombre;
    private string $descripcion;
    private ?string $fecha_inicio;
    private ?string $fecha_fin;
    private ?string $hora_inicio;
    private ?string $hora_fin;
    private ?int $id_ubicacion_formacion;
    private ?int $id_facilitador;
    private int $cupo_maximo;
    private string $estado;

    public function __construct(array $data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->fecha_inicio = $data['fecha_inicio'] ?? date('Y-m-d');
            $this->fecha_fin = $data['fecha_fin'] ?? null;
            $this->hora_inicio = $data['hora_inicio'] ?? null;
            $this->hora_fin = $data['hora_fin'] ?? null;
            $this->id_ubicacion_formacion = $data['id_ubicacion_formacion'] ?? null;
            $this->id_facilitador = $data['id_facilitador'] ?? null;
            $this->cupo_maximo = $data['cupo_maximo'] ?? 30;
            $this->estado = $data['estado'] ?? 'Programado';
        }
    }

    public static function all() {
        $db = new Database();
        $db->query("SELECT t.*, uf.nombre as ubicacion, p.nombre as facilitador_nombre, p.apellido as facilitador_apellido,
                           (SELECT COUNT(*) FROM participantes_taller pt WHERE pt.id_taller = t.id) as total_inscritos
                    FROM talleres t
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    LEFT JOIN empleados e ON t.id_facilitador = e.id
                    LEFT JOIN personas p ON e.id_persona = p.id
                    WHERE t.is_active = TRUE
                    ORDER BY t.fecha_inicio DESC");
        return $db->resultSet();
    }

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT t.*, uf.nombre as ubicacion, p.nombre as facilitador_nombre, p.apellido as facilitador_apellido
                    FROM talleres t
                    LEFT JOIN ubicaciones_formacion uf ON t.id_ubicacion_formacion = uf.id
                    LEFT JOIN empleados e ON t.id_facilitador = e.id
                    LEFT JOIN personas p ON e.id_persona = p.id
                    WHERE t.id = :id");
        $db->bind(':id', $id);
        return $db->single();
    }

    public function save($user_id = null) {
        if ($this->id) {
            $this->db->query("UPDATE talleres SET nombre=:nombre, descripcion=:descripcion, fecha_inicio=:fecha_inicio, 
                              fecha_fin=:fecha_fin, hora_inicio=:hora_inicio, hora_fin=:hora_fin,
                              id_ubicacion_formacion=:id_ubicacion_formacion, id_facilitador=:id_facilitador, 
                              cupo_maximo=:cupo_maximo, estado=:estado, 
                              updated_at=CURRENT_TIMESTAMP, updated_by=:user_id WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO talleres (nombre, descripcion, fecha_inicio, fecha_fin, hora_inicio, hora_fin,
                              id_ubicacion_formacion, id_facilitador, cupo_maximo, estado, created_by)
                              VALUES (:nombre, :descripcion, :fecha_inicio, :fecha_fin, :hora_inicio, :hora_fin,
                              :id_ubicacion_formacion, :id_facilitador, :cupo_maximo, :estado, :user_id)");
        }
        $this->db->bind(':nombre', $this->nombre);
        $this->db->bind(':descripcion', $this->descripcion);
        $this->db->bind(':fecha_inicio', $this->fecha_inicio);
        $this->db->bind(':fecha_fin', $this->fecha_fin);
        $this->db->bind(':hora_inicio', $this->hora_inicio);
        $this->db->bind(':hora_fin', $this->hora_fin);
        $this->db->bind(':id_ubicacion_formacion', $this->id_ubicacion_formacion);
        $this->db->bind(':id_facilitador', $this->id_facilitador);
        $this->db->bind(':cupo_maximo', $this->cupo_maximo);
        $this->db->bind(':estado', $this->estado);
        $this->db->bind(':user_id', $user_id);
        return $this->db->execute();
    }

    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE talleres SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:user_id WHERE id=:id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }

    /**
     * Obtener participantes de un taller
     */
    public static function getParticipantes($id_taller) {
        $db = new Database();
        $db->query("SELECT pt.*, p.cedula, p.nombre, p.apellido, p.telefono
                    FROM participantes_taller pt
                    INNER JOIN personas p ON pt.id_persona = p.id
                    WHERE pt.id_taller = :id_taller");
        $db->bind(':id_taller', $id_taller);
        return $db->resultSet();
    }

    /**
     * Inscribir persona en taller
     */
    public static function inscribir($id_taller, $id_persona, $user_id = null) {
        $db = new Database();
        $db->query("INSERT INTO participantes_taller (id_taller, id_persona, created_by) VALUES (:id_taller, :id_persona, :user_id)");
        $db->bind(':id_taller', $id_taller);
        $db->bind(':id_persona', $id_persona);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }

    /**
     * Obtener y guardar el informe general / demográfico del evento
     */
    public static function getInforme($id_taller) {
        $db = new Database();
        $db->query("SELECT * FROM taller_informes WHERE id_taller = :id_taller");
        $db->bind(':id_taller', $id_taller);
        return $db->single();
    }

    public static function saveInforme($data) {
        $db = new Database();
        
        // Verificar si existe el informe
        $inf = self::getInforme($data['id_taller']);
        if ($inf) {
            $db->query("UPDATE taller_informes 
                        SET unidad_estadal=:unidad, lugar_exacto=:lugar, instituciones_presentes=:inst, 
                            mujeres=:m, hombres=:h, ninas=:ni, ninos=:no, total_atendidas=:tot, 
                            resumen_actividad=:res, updated_at=CURRENT_TIMESTAMP
                        WHERE id_taller = :id_taller");
        } else {
            $db->query("INSERT INTO taller_informes 
                        (id_taller, unidad_estadal, lugar_exacto, instituciones_presentes, mujeres, hombres, ninas, ninos, total_atendidas, resumen_actividad, created_by)
                        VALUES (:id_taller, :unidad, :lugar, :inst, :m, :h, :ni, :no, :tot, :res, :user_id)");
            $db->bind(':user_id', $_SESSION['user_id'] ?? null);
        }
        
        $db->bind(':id_taller', $data['id_taller']);
        $db->bind(':unidad', $data['unidad_estadal']);
        $db->bind(':lugar', $data['lugar_exacto']);
        $db->bind(':inst', $data['instituciones_presentes']);
        $db->bind(':m', $data['mujeres']);
        $db->bind(':h', $data['hombres']);
        $db->bind(':ni', $data['ninas']);
        $db->bind(':no', $data['ninos']);
        $db->bind(':tot', (int)$data['mujeres'] + (int)$data['hombres'] + (int)$data['ninas'] + (int)$data['ninos']);
        $db->bind(':res', $data['resumen_actividad']);
        
        return $db->execute();
    }
}
