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
            if ($ruta->save(1)) {
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

        $data = [
            'titulo' => 'Ruta: ' . $ruta->nombre,
            'ruta' => $ruta,
            'puntos' => $puntos
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
            if ($punto->save(1)) {
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
        if (PuntoRuta::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
        }
    }

    public function delete($id) {
        if (Ruta::delete($id, 1)) {
            header('Location: ' . URL_ROOT . '/rutas/index');
        }
    }
}
