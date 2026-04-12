<?php
/**
 * Controlador RutasController — Gestión de Rutas Turísticas
 */
class RutasController extends Controller {

    public function index() {
        $rutas = Ruta::all();
        $data = [
            'titulo' => 'Gestión de Rutas Turísticas',
            'rutas' => $rutas
        ];
        $this->view('rutas/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'duracion_estimada' => trim($_POST['duracion_estimada']),
                'nivel_dificultad' => $_POST['nivel_dificultad'],
                'estado' => $_POST['estado']
            ];

            $ruta = new Ruta($data);
            if ($ruta->save($this->getUserId())) {
                header('Location: ' . URL_ROOT . '/rutas/index');
            } else {
                die('Error al guardar la ruta');
            }
        }
    }

    /**
     * Ver detalle de ruta con sus puntos
     */
    public function detalle($id) {
        $ruta = Ruta::find($id);
        $puntos = Ruta::getPuntos($id);
        
        // Obtener inventario asignado a la ruta
        $inventario_asignado = RutaInventario::getByRuta($id);
        // Obtener inventario disponible para asignar
        $inventario_disponible = Inventario::all();

        $data = [
            'titulo' => 'Ruta: ' . $ruta->nombre,
            'ruta' => $ruta,
            'puntos' => $puntos,
            'inventario_asignado' => $inventario_asignado,
            'inventario_disponible' => $inventario_disponible
        ];
        $this->view('rutas/detalle', $data);
    }

    /**
     * Guardar punto de ruta
     */
    public function storePunto() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $data = [
                'id' => isset($_POST['punto_id']) ? (int)$_POST['punto_id'] : null,
                'id_ruta' => (int)$_POST['id_ruta'],
                'nombre' => trim($_POST['punto_nombre']),
                'descripcion' => trim($_POST['punto_descripcion']),
                'orden' => (int)$_POST['orden'],
                'latitud' => $_POST['latitud'] ?: null,
                'longitud' => $_POST['longitud'] ?: null
            ];

            $punto = new PuntoRuta($data);
            if ($punto->save($this->getUserId())) {
                header('Location: ' . URL_ROOT . '/rutas/detalle/' . $data['id_ruta']);
            } else {
                die('Error al guardar el punto');
            }
        }
    }

    /**
     * Eliminar punto de ruta
     */
    public function deletePunto($id, $id_ruta) {
        if (PuntoRuta::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
        }
    }

    public function delete($id) {
        if (Ruta::delete($id, $this->getUserId())) {
            header('Location: ' . URL_ROOT . '/rutas/index');
        }
    }

    /**
     * Asignar inventario a ruta
     */
    public function storeInventario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $id_ruta = (int)$_POST['id_ruta'];
            $id_inventario = (int)$_POST['id_inventario'];
            $cantidad = (int)$_POST['cantidad'];
            $observaciones = trim($_POST['observaciones']);

            if (RutaInventario::asignar($id_ruta, $id_inventario, $cantidad, $observaciones, $this->getUserId())) {
                header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
            } else {
                die('Error al asignar el inventario');
            }
        }
    }

    /**
     * Remover inventario de ruta
     */
    public function deleteInventario($id_asignacion, $id_ruta) {
        if (RutaInventario::remover($id_asignacion)) {
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
        }
    }
}
