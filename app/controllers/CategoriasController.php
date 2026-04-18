<?php
/**
 * Controlador CategoriasController
 */
class CategoriasController extends Controller {

    public function index() {
        $categorias = Categoria::all();
        $data = [
            'titulo' => 'Configuración: Categorías de Inventario',
            'categorias' => $categorias
        ];
        $this->view('categorias/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'])
            ];

            $esEdicion = !empty($data['id']);
            $cat = new Categoria($data);

            try {
                if ($cat->save($this->getUserId())) {
                    $msg = $esEdicion ? "Categoría de inventario actualizada." : "Nueva categoría registrada exitosamente.";
                    flash('global_msg', $msg);
                } else {
                    throw new Exception("No se pudo completar el registro de la categoría.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Fallo de configuración: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/categorias/index');
        }
    }

    public function delete($id) {
        try {
            if (Categoria::delete($id, $this->getUserId())) {
                flash('global_msg', 'Categoría eliminada de la vista activa.', 'warning');
            } else {
                throw new Exception("Error al intentar dar de baja la categoría.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error de BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/categorias/index');
    }
}
