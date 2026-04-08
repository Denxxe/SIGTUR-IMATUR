<?php
/**
 * Clase Usuario: Modelo para la tabla usuarios
 */
class Usuario extends Model {
    private $id;
    private $id_empleado;
    private $id_rol;
    private $username;
    private $password;
    private $is_active;

    public function __construct($data = []) {
        parent::__construct();
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->id_empleado = $data['id_empleado'] ?? null;
            $this->id_rol = $data['id_rol'] ?? null;
            $this->username = $data['username'] ?? '';
            $this->password = $data['password'] ?? '';
            $this->is_active = $data['is_active'] ?? true;
        }
    }

    // --- Getters ---
    public function getId() { return $this->id; }
    public function getUsername() { return $this->username; }

    /**
     * Obtener todos los usuarios con sus roles y nombres de empleados
     */
    public static function all() {
        $db = new Database();
        $db->query("SELECT u.*, r.nombre as rol, p.nombre, p.apellido 
                    FROM usuarios u
                    INNER JOIN roles r ON u.id_rol = r.id
                    INNER JOIN empleados e ON u.id_empleado = e.id
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE u.is_active = TRUE");
        return $db->resultSet();
    }

    /**
     * Buscar un usuario por username (para Login)
     */
    public static function findByUsername($username) {
        $db = new Database();
        $db->query("SELECT * FROM usuarios WHERE username = :username AND is_active = TRUE");
        $db->bind(':username', $username);
        return $db->single();
    }

    /**
     * Guardar registro
     */
    public function save($user_id = null) {
        if ($this->id) {
            // Update
            $sql = "UPDATE usuarios SET id_rol = :id_rol, username = :username";
            // Solo actualizar password si se proporcionó uno nuevo
            if (!empty($this->password)) {
                $sql .= ", password = :password";
            }
            $sql .= ", updated_at = CURRENT_TIMESTAMP, updated_by = :user_id WHERE id = :id";
            
            $this->db->query($sql);
            $this->db->bind(':id', $this->id);
        } else {
            // Insert
            $this->db->query("INSERT INTO usuarios (id_empleado, id_rol, username, password, created_by) 
                              VALUES (:id_empleado, :id_rol, :username, :password, :user_id)");
            $this->db->bind(':id_empleado', $this->id_empleado);
        }

        $this->db->bind(':id_rol', $this->id_rol);
        $this->db->bind(':username', $this->username);
        $this->db->bind(':user_id', $user_id);

        if (!empty($this->password) || !$this->id) {
            // Hashear password antes de guardar
            $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
            $this->db->bind(':password', $hashed_password);
        }

        return $this->db->execute();
    }

    /**
     * Borrado lógico
     */
    public static function delete($id, $user_id = null) {
        $db = new Database();
        $db->query("UPDATE usuarios SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        return $db->execute();
    }
}
