<?php
/**
 * Controlador PermisosController — permisos y reposos laborales (R-8).
 */
class PermisosController extends Controller {

    public function index() {
        $filtros = [
            'estado'    => $_GET['estado']    ?? '',
            'categoria' => $_GET['categoria'] ?? '',
        ];
        $data = [
            'titulo'    => 'Permisos y Reposos',
            'permisos'  => PermisoLaboral::all($filtros),
            'empleados' => Empleado::all(),
            'filtros'   => $filtros,
        ];
        $this->view('permisos/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
        $_POST = $this->sanitizePost();
        try {
            PermisoLaboral::save($_POST, $this->getUserId());
            flash('global_msg', 'Permiso/reposo registrado (estado: Pendiente).');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo registrar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/permisos/index');
    }

    public function aprobar($id) {
        $this->cambiar($id, 'Aprobado', 'Permiso aprobado.');
    }

    public function rechazar($id) {
        $this->cambiar($id, 'Rechazado', 'Permiso rechazado.', 'warning');
    }

    public function anular($id) {
        $this->cambiar($id, 'Anulado', 'Permiso anulado.', 'warning');
    }

    private function cambiar($id, $estado, $msg, $tipo = 'success') {
        try {
            PermisoLaboral::cambiarEstado($id, $estado, $this->getUserId());
            flash('global_msg', $msg, $tipo);
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo actualizar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/permisos/index');
    }

    public function delete($id) {
        try {
            PermisoLaboral::delete($id, $this->getUserId());
            flash('global_msg', 'Registro movido a la papelera.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', 'No se pudo eliminar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/permisos/index');
    }
}
