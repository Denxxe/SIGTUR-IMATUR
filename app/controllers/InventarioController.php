<?php
/**
 * Controlador InventarioController
 */
class InventarioController extends Controller {

    public function index() {
        $items = Inventario::all();
        $categorias = Categoria::all();
        $ubicaciones = Ubicacion::all();

        $data = [
            'titulo' => 'Gestión de Bienes e Inventario',
            'items' => $items,
            'categorias' => $categorias,
            'ubicaciones' => $ubicaciones
        ];

        $this->view('inventario/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_categoria' => (int)$_POST['id_categoria'],
                'id_ubicacion' => (int)$_POST['id_ubicacion'],
                'codigo_bn' => trim($_POST['codigo_bn']),
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'marca' => trim($_POST['marca']),
                'modelo' => trim($_POST['modelo']),
                'serial' => trim($_POST['serial']),
                'condicion' => $_POST['condicion'],
                'observaciones' => trim($_POST['observaciones'])
            ];

            $item = new Inventario($data);
            if ($item->save(1)) {
                header('Location: ' . URL_ROOT . '/inventario/index');
            } else {
                die('Error al guardar el bien en inventario');
            }
        }
    }

    public function delete($id) {
        if (Inventario::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/inventario/index');
        }
    }
}
