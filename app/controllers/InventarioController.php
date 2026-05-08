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
            $_POST = $this->sanitizePost();
            
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

            $esEdicion = !empty($data['id']);
            $item = new Inventario($data);

            try {
                if ($item->save($this->getUserId())) {
                    $msg = $esEdicion ? "Bienes nacionales actualizados correctamente." : "Nuevo bien registrado exitosamente en el inventario.";
                    flash('global_msg', $msg);
                    header('Location: ' . URL_ROOT . '/inventario/index');
                } else {
                    throw new Exception("No es posible guardar el bien nacional en este momento.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Fallo en inventario: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/inventario/index');
            }
        }
    }

    public function delete($id) {
        try {
            if (Inventario::delete($id, $this->getUserId())) {
                flash('global_msg', 'El bien nacional ha sido movido a la papelera de reciclaje.', 'warning');
            } else {
                throw new Exception("Error al intentar dar de baja el registro.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error de BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/index');
    }
}
