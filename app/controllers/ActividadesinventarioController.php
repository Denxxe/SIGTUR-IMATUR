<?php
/**
 * Controlador ActividadesInventarioController
 * Gestiona trazabilidad física, mantenimiento y baja de los bienes institucionales.
 */
class ActividadesinventarioController extends Controller {

    public function index() {
        $actividades = ActividadInventario::all();
        $inventario = Inventario::all();
        $empleados = Empleado::all();

        $data = [
            'titulo' => 'Movimientos de Inventario',
            'actividades' => $actividades,
            'inventario' => $inventario,
            'empleados' => $empleados
        ];

        $this->view('actividades_inventario/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_inventario' => (int)$_POST['id_inventario'],
                'tipo_movimiento' => trim($_POST['tipo_movimiento']),
                'descripcion' => trim($_POST['descripcion']),
                'fecha' => $_POST['fecha'] ?: date('Y-m-d'),
                'id_empleado_responsable' => !empty($_POST['id_empleado_responsable']) ? (int)$_POST['id_empleado_responsable'] : null
            ];

            $esEdicion = !empty($data['id']);
            try {
                $actividad = new ActividadInventario($data);
                if ($actividad->save($this->getUserId())) {
                    flash('global_msg', $esEdicion ? 'Movimiento del bien actualizado correctamente.' : 'Movimiento del bien registrado correctamente.');
                } else {
                    throw new Exception('No se pudo registrar el movimiento del bien.');
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error al registrar el movimiento: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/actividadesinventario/index');
        }
    }

    public function delete($id) {
        try {
            if (ActividadInventario::delete($id, $this->getUserId())) {
                flash('global_msg', 'Movimiento de inventario eliminado.', 'warning');
            } else {
                throw new Exception('No se pudo eliminar el movimiento.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error al eliminar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/actividadesinventario/index');
    }
}
