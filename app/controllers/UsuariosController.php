<?php
/**
 * Controlador UsuariosController
 */
class UsuariosController extends Controller {

    public function index() {
        $usuarios = Usuario::all();
        $roles    = Rol::all();

        // Solo empleados que aún no tienen cuenta activa
        $db = new Database();
        $db->query("SELECT e.id, p.nombre, p.apellido, p.cedula
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE e.is_active = TRUE AND p.is_active = TRUE
                      AND NOT EXISTS (
                          SELECT 1 FROM usuarios u
                          WHERE u.id_empleado = e.id AND u.is_active = TRUE
                      )
                    ORDER BY p.nombre ASC");
        $empleados_sin_cuenta = $db->resultSet();

        $data = [
            'titulo'               => 'Seguridad: Gestión de Usuarios',
            'usuarios'             => $usuarios,
            'roles'                => $roles,
            'empleados_sin_cuenta' => $empleados_sin_cuenta,
            // Continuidad administrativa: con un solo admin activo, esa cuenta
            // no se puede suspender ni cambiar de rol.
            'admins_activos'       => Usuario::contarAdminsActivos(),
        ];

        $this->view('usuarios/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST = $this->sanitizePost();

        $data = [
            'id'          => isset($_POST['id']) ? (int)$_POST['id'] : null,
            'id_empleado' => !empty($_POST['id_empleado']) ? (int)$_POST['id_empleado'] : null,
            'id_rol'      => (int)$_POST['id_rol'],
            'username'    => trim($_POST['username']),
            'password'    => $_POST['password'],
        ];

        $esNuevo = empty($data['id']);

        if (($err = $this->errorDelegacion($data['id'], $data['id_rol'])) !== null) {
            flash('global_msg', $err, 'danger');
            header('Location: ' . URL_ROOT . '/usuarios/index');
            return;
        }

        if ($esNuevo && empty($data['id_empleado'])) {
            flash('global_msg', 'Debe seleccionar un empleado para crear la cuenta.', 'danger');
            header('Location: ' . URL_ROOT . '/usuarios/index');
            return;
        }
        if ($esNuevo && empty($data['password'])) {
            flash('global_msg', 'La contraseña es obligatoria al crear una cuenta.', 'danger');
            header('Location: ' . URL_ROOT . '/usuarios/index');
            return;
        }
        if (!empty($data['password']) && ($err = Usuario::passwordPolicyError($data['password'])) !== null) {
            flash('global_msg', $err, 'danger');
            header('Location: ' . URL_ROOT . '/usuarios/index');
            return;
        }

        $usuario = new Usuario($data);
        try {
            if ($usuario->save($this->getUserId())) {
                $msg = $esNuevo ? 'Cuenta de acceso creada correctamente.' : 'Credenciales actualizadas correctamente.';
                flash('global_msg', $msg);
            } else {
                throw new Exception('Error interno al guardar el usuario.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo guardar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/usuarios/index');
    }

    public function delete($id) {
        if ((int)$id === (int)$this->getUserId()) {
            flash('global_msg', 'No puedes suspender tu propia cuenta de usuario.', 'danger');
            header('Location: ' . URL_ROOT . '/usuarios/index');
            return;
        }
        if (!$this->esAdmin()) {
            $objetivo = Usuario::find((int)$id);
            if ($objetivo && (int)$objetivo->id_rol === Usuario::ROL_ADMIN) {
                flash('global_msg', 'Solo un Administrador puede suspender una cuenta de Administrador.', 'danger');
                header('Location: ' . URL_ROOT . '/usuarios/index');
                return;
            }
        }
        try {
            if (Usuario::delete($id, $this->getUserId())) {
                flash('global_msg', 'Cuenta suspendida correctamente.', 'warning');
            } else {
                throw new Exception('No se pudo suspender la cuenta.');
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/usuarios/index');
    }

    /**
     * Límites de quien gestiona usuarios por delegación (un rol distinto de
     * Administrador con acceso a este módulo, p. ej. RRHH). Sin esto, ese rol
     * podía crear una cuenta de Administrador, ascenderse a sí mismo o
     * cambiarle la clave al admin: una sola petición y tenía acceso total.
     * Devuelve el mensaje de error, o null si la operación está permitida.
     */
    private function errorDelegacion(?int $id, int $idRol): ?string {
        if ($this->esAdmin()) return null;

        if ($idRol === Usuario::ROL_ADMIN) {
            return 'Solo un Administrador puede asignar el rol de Administrador.';
        }
        if ($id) {
            $actual = Usuario::find($id);
            if ($actual && (int)$actual->id_rol === Usuario::ROL_ADMIN) {
                return 'Solo un Administrador puede modificar una cuenta de Administrador.';
            }
            if ($id === (int)$this->getUserId() && $actual && (int)$actual->id_rol !== $idRol) {
                return 'No puedes cambiar el rol de tu propia cuenta.';
            }
        }
        return null;
    }
}
