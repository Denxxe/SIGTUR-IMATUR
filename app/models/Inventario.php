<?php
/**
 * Clase Inventario: Modelo para la gestión de bienes institucionales
 */
class Inventario extends Model {
    private $id;
    private $id_categoria;
    private $id_ubicacion;
    private $codigo_bn;
    private $nombre;
    private $descripcion;
    private $marca;
    private $modelo;
    private $serial;
    private $condicion; // 'Nuevo', 'Bueno', 'Regular', 'Dañado'
    private $observaciones;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_categoria = $data['id_categoria'] ?? null;
            $this->id_ubicacion = $data['id_ubicacion'] ?? null;
            $this->codigo_bn = $data['codigo_bn'] ?? '';
            $this->nombre = $data['nombre'] ?? '';
            $this->descripcion = $data['descripcion'] ?? '';
            $this->marca = $data['marca'] ?? '';
            $this->modelo = $data['modelo'] ?? '';
            $this->serial = $data['serial'] ?? '';
            $this->condicion = $data['condicion'] ?? 'Bueno';
            $this->observaciones = $data['observaciones'] ?? '';
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

    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $this->db->query("UPDATE inventario SET id_categoria=:id_categoria, id_ubicacion=:id_ubicacion, codigo_bn=:codigo_bn, 
                              nombre=:nombre, descripcion=:descripcion, marca=:marca, modelo=:modelo, serial=:serial, 
                              condicion=:condicion, observaciones=:observaciones, updated_at=CURRENT_TIMESTAMP, updated_by=:user_id 
                              WHERE id=:id");
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO inventario (id_categoria, id_ubicacion, codigo_bn, nombre, descripcion, marca, modelo, serial, condicion, observaciones, created_by) 
                              VALUES (:id_categoria, :id_ubicacion, :codigo_bn, :nombre, :descripcion, :marca, :modelo, :serial, :condicion, :observaciones, :user_id)");
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
        $this->db->bind(':user_id', $user_id);
        $result = $this->db->execute();
        $this->audit('inventario', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? 0, $previos, ['nombre' => $this->nombre, 'codigo_bn' => $this->codigo_bn, 'condicion' => $this->condicion], $user_id);
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
