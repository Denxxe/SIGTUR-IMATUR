<?php
/**
 * Controlador EmpleadosController
 */
class EmpleadosController extends Controller {

    public function index() {
        $empleados = Empleado::all();
        // Necesitamos cargos y departamentos para el modal
        $cargos = Cargo::all();
        $departamentos = Departamento::all();

        $data = [
            'titulo' => 'Gestión de Personal (Empleados)',
            'empleados' => $empleados,
            'cargos' => $cargos,
            'departamentos' => $departamentos
        ];

        $this->view('empleados/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_persona' => isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : null,
                'cedula' => trim($_POST['cedula']),
                'nombre' => trim($_POST['nombre']),
                'apellido' => trim($_POST['apellido']),
                'telefono' => trim($_POST['telefono']),
                'correo' => trim($_POST['correo']),
                'genero' => $_POST['genero'],
                'fecha_nacimiento' => $_POST['fecha_nacimiento'],
                'direccion' => trim($_POST['direccion']),
                'id_cargo' => (int)$_POST['id_cargo'],
                'id_departamento' => (int)$_POST['id_departamento'],
                'nro_expediente' => trim($_POST['nro_expediente']),
                'fecha_ingreso' => $_POST['fecha_ingreso']
            ];

            $empleado = new Empleado($data);

            if ($empleado->save(1)) {
                header('Location: ' . URL_ROOT . '/empleados/index');
            } else {
                die('Error al guardar el empleado');
            }
        }
    }

    public function delete($id) {
        if (Empleado::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/empleados/index');
        } else {
            die('Error al eliminar');
        }
    }
}
