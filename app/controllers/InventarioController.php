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
            
            $condicionesValidas = ['Nuevo', 'Bueno', 'Regular', 'Dañado', 'En Reparación'];
            $condicion = in_array($_POST['condicion'] ?? '', $condicionesValidas) ? $_POST['condicion'] : 'Bueno';

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
                'condicion' => $condicion,
                'observaciones' => trim($_POST['observaciones'])
            ];

            $esEdicion  = !empty($data['id']);
            $excludeId  = $esEdicion ? (int)$data['id'] : null;

            // Unicidad de Código BN (campo UNIQUE en BD)
            if ($data['codigo_bn'] !== '') {
                $dupBN = Inventario::findByCodigoBn($data['codigo_bn'], $excludeId);
                if ($dupBN) {
                    flash('global_msg', 'El Código BN "' . htmlspecialchars($data['codigo_bn']) . '" ya está registrado en otro bien (ID #' . $dupBN . '). Verifica el código antes de continuar.', 'danger');
                    header('Location: ' . URL_ROOT . '/inventario/index');
                    return;
                }
            }

            // Unicidad de Serial (campo UNIQUE en BD)
            if ($data['serial'] !== '') {
                $dupSer = Inventario::findBySerial($data['serial'], $excludeId);
                if ($dupSer) {
                    flash('global_msg', 'El número de serial "' . htmlspecialchars($data['serial']) . '" ya está asignado a otro bien (ID #' . $dupSer . '). Verifica el serial antes de continuar.', 'danger');
                    header('Location: ' . URL_ROOT . '/inventario/index');
                    return;
                }
            }

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
