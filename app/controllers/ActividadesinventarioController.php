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
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_inventario' => (int)$_POST['id_inventario'],
                'tipo_movimiento' => trim($_POST['tipo_movimiento']),
                'descripcion' => trim($_POST['descripcion']),
                'fecha' => $_POST['fecha'] ?: date('Y-m-d'),
                'id_empleado_responsable' => !empty($_POST['id_empleado_responsable']) ? (int)$_POST['id_empleado_responsable'] : null
            ];

            $actividad = new ActividadInventario($data);
            if ($actividad->save($this->getUserId())) { // Cambiaremos el 1 al user_id de Auth
                
                // Si es necesario dar de baja o reparar, opcionalmente actualizar condicion de inventario.
                // Como plus, interceptar cambios crudos (ej. Si tipo_movimiento es "Baja", Inventory status => "Inservible")
                
                header('Location: ' . URL_ROOT . '/actividadesinventario/index');
            } else {
                die('Error al registrar movimiento del bien.');
            }
        }
    }

    public function delete($id) {
        if (ActividadInventario::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/actividadesinventario/index');
        }
    }
}
