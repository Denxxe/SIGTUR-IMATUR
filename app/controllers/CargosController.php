<?php
/**
 * Controlador CargosController
 * Gestiona las operaciones CRUD para la entidad Cargo
 */
class CargosController extends Controller {

    public function index() {
        $cargos = Cargo::all();
        $data = [
            'titulo' => 'Gestión de Cargos',
            'cargos' => $cargos
        ];
        $this->view('cargos/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'sueldo_base' => (float)$_POST['sueldo_base']
            ];

            $esEdicion = !empty($id);
            $cargo = new Cargo($data);

            try {
                if ($cargo->save($this->getUserId())) {
                    $msg = $esEdicion ? "Información del cargo institucional actualizada." : "Nuevo cargo registrado exitosamente.";
                    flash('global_msg', $msg);
                } else {
                    throw new Exception("Error al intentar guardar el cargo.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'No se pudo procesar: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/cargos/index');
        }
    }

    public function delete($id) {
        try {
            if (Cargo::delete($id, $this->getUserId())) {
                flash('global_msg', 'El cargo ha sido eliminado (enviado a papelera).', 'warning');
            } else {
                throw new Exception("Error al eliminar el registro.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'BD Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/cargos/index');
    }
}
