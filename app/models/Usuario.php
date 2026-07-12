<?php
/**
 * Clase Usuario: Modelo para la tabla usuarios
 */
class Usuario extends Model {
    // ── Política de seguridad del login (mig. 051) ───────────────────────────
    const MAX_INTENTOS     = 5;    // intentos fallidos consecutivos antes de bloquear
    const BLOQUEO_MINUTOS  = 15;   // duración del bloqueo temporal
    const PASSWORD_MIN     = 8;    // longitud mínima de contraseña

    private ?int $id;
    private ?int $id_empleado;
    private ?int $id_rol;
    private string $username;
    private string $password;
    private bool $is_active;

    public function __construct(array $data = []) {
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

    public static function find($id) {
        $db = new Database();
        $db->query("SELECT u.id, u.id_empleado, u.id_rol, u.username, u.is_active FROM usuarios u WHERE u.id = :id");
        $db->bind(':id', $id);
        return $db->single();
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
     * Buscar un usuario activo por username o por el correo de su persona
     * asociada (recuperación de contraseña). Devuelve null si no hay
     * coincidencia única (no existe, o el correo es ambiguo entre 2+ cuentas)
     * — nunca revela cuál de los dos casos ocurrió (anti-enumeración).
     */
    public static function findByUsernameOrEmail(string $identificador) {
        $db = new Database();
        $db->query("SELECT u.*, p.correo
                    FROM usuarios u
                    INNER JOIN empleados e ON u.id_empleado = e.id
                    INNER JOIN personas p  ON e.id_persona  = p.id
                    WHERE u.is_active = TRUE AND (u.username = :id OR p.correo = :id)
                    LIMIT 2");
        $db->bind(':id', trim($identificador));
        $rows = $db->resultSet();
        return count($rows) === 1 ? $rows[0] : null;
    }

    /**
     * Guardar registro
     */
    public function save($user_id = null) {
        $previos = null;
        if ($this->id) {
            $previos = self::find($this->id);
            $sql = "UPDATE usuarios SET id_rol = :id_rol, username = :username";
            if (!empty($this->password)) {
                $sql .= ", password = :password";
            }
            $sql .= ", updated_at = CURRENT_TIMESTAMP, updated_by = :user_id WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':id', $this->id);
        } else {
            $this->db->query("INSERT INTO usuarios (id_empleado, id_rol, username, password, created_by)
                              VALUES (:id_empleado, :id_rol, :username, :password, :user_id)");
            $this->db->bind(':id_empleado', $this->id_empleado);
        }

        $this->db->bind(':id_rol', $this->id_rol);
        $this->db->bind(':username', $this->username);
        $this->db->bind(':user_id', $user_id);

        if (!empty($this->password) || !$this->id) {
            $hashed_password = password_hash($this->password, PASSWORD_BCRYPT);
            $this->db->bind(':password', $hashed_password);
        }

        $result = $this->db->execute();
        $nuevos = ['username' => $this->username, 'id_rol' => $this->id_rol, 'id_empleado' => $this->id_empleado, 'password_changed' => !empty($this->password)];
        $this->audit('usuarios', $this->id ? 'UPDATE' : 'INSERT', $this->id ?? null, $previos, $nuevos, $user_id);
        return $result;
    }

    // ── Seguridad del login (mig. 051) ───────────────────────────────────────

    /**
     * Minutos restantes de bloqueo de la cuenta (0 si no está bloqueada).
     * Recibe la fila del usuario (debe incluir locked_until).
     */
    public static function bloqueoRestante($user): int {
        if (empty($user->locked_until)) return 0;
        $hasta = strtotime($user->locked_until);
        $ahora = time();
        if ($hasta <= $ahora) return 0;
        return (int)ceil(($hasta - $ahora) / 60);
    }

    /**
     * Registra un intento fallido. Si alcanza el máximo, bloquea la cuenta por
     * BLOQUEO_MINUTOS. Devuelve ['bloqueada'=>bool, 'restantes'=>intentos restantes].
     */
    public static function registrarLoginFallido(int $id): array {
        $db = new Database();
        $db->query("UPDATE usuarios
                    SET failed_attempts = failed_attempts + 1,
                        locked_until = CASE
                            WHEN failed_attempts + 1 >= :max
                            THEN NOW() + (:mins || ' minutes')::INTERVAL
                            ELSE locked_until END
                    WHERE id = :id
                    RETURNING failed_attempts, locked_until");
        $db->bind(':max', self::MAX_INTENTOS);
        $db->bind(':mins', self::BLOQUEO_MINUTOS);
        $db->bind(':id', $id);
        $row = $db->single();
        $intentos  = (int)($row->failed_attempts ?? 0);
        $bloqueada = self::bloqueoRestante($row) > 0;
        return ['bloqueada' => $bloqueada, 'restantes' => max(0, self::MAX_INTENTOS - $intentos)];
    }

    /** Reinicia los contadores y marca el último acceso tras un login exitoso. */
    public static function registrarLoginExitoso(int $id): void {
        $db = new Database();
        $db->query("UPDATE usuarios SET failed_attempts = 0, locked_until = NULL, last_login = NOW() WHERE id = :id");
        $db->bind(':id', $id);
        $db->execute();
    }

    /**
     * Actualiza SOLO la contraseña (recuperación por correo). A diferencia de
     * save(), no toca username/id_rol — evita pisarlos con vacío si el llamador
     * no los conoce. Limpia el bloqueo de intentos fallidos (la identidad ya
     * quedó verificada por el enlace de un solo uso).
     */
    public static function actualizarPassword(int $id, string $plainPassword, $userId = null): bool {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE usuarios
                    SET password = :password, failed_attempts = 0, locked_until = NULL,
                        updated_at = CURRENT_TIMESTAMP, updated_by = :user_id
                    WHERE id = :id");
        $db->bind(':password', password_hash($plainPassword, PASSWORD_BCRYPT));
        $db->bind(':user_id', $userId);
        $db->bind(':id', $id);
        $result = $db->execute();
        self::auditStatic('usuarios', 'UPDATE', $id, $previos, ['password_changed' => true], $userId);
        return $result;
    }

    /**
     * Valida la política de contraseñas. Devuelve un mensaje de error o null si es válida.
     * Reglas: mínimo PASSWORD_MIN caracteres, al menos una letra y un número.
     */
    public static function passwordPolicyError(string $pwd): ?string {
        if (mb_strlen($pwd) < self::PASSWORD_MIN) {
            return 'La contraseña debe tener al menos ' . self::PASSWORD_MIN . ' caracteres.';
        }
        if (!preg_match('/[A-Za-zÁÉÍÓÚáéíóúÑñ]/', $pwd) || !preg_match('/[0-9]/', $pwd)) {
            return 'La contraseña debe incluir al menos una letra y un número.';
        }
        return null;
    }

    /**
     * Borrado lógico
     */
    public static function delete($id, $user_id = null) {
        $previos = self::find($id);
        $db = new Database();
        $db->query("UPDATE usuarios SET is_active = FALSE, deleted_at = CURRENT_TIMESTAMP, deleted_by = :user_id WHERE id = :id");
        $db->bind(':id', $id);
        $db->bind(':user_id', $user_id);
        $result = $db->execute();
        self::auditStatic('usuarios', 'DELETE', $id, $previos, null, $user_id);
        return $result;
    }
}
