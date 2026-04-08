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

            $cat = new Categoria($data);
            if ($cat->save(1)) {
                header('Location: ' . URL_ROOT . '/categorias/index');
            } else {
                die('Error al guardar la categoría');
            }
        }
    }

    public function delete($id) {
        if (Categoria::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/categorias/index');
        }
    }
}
