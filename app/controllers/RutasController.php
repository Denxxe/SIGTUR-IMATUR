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

            $esEdicion = !empty($data['id']);
            $ruta = new Ruta($data);

            try {
                if ($ruta->save($this->getUserId())) {
                    $msg = $esEdicion ? "Información de la ruta actualizada correctamente." : "Nueva ruta turística creada exitosamente.";
                    flash('global_msg', $msg);
                    header('Location: ' . URL_ROOT . '/rutas/index');
                } else {
                    throw new Exception("Error al procesar la ruta turística.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Fallo en Rutas: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/rutas/index');
            }
        }
    }

    public function detalle($id) {
        $ruta = Ruta::find($id);
        $puntos = Ruta::getPuntos($id);
        $inventario_asignado = RutaInventario::getByRuta($id);
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
            try {
                if ($punto->save($this->getUserId())) {
                    flash('global_msg', 'Punto de interés guardado en la ruta.');
                } else {
                    throw new Exception("No se pudo registrar el punto.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error en puntos: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $data['id_ruta']);
        }
    }

    public function deletePunto($id, $id_ruta) {
        try {
            if (PuntoRuta::delete($id, $this->getUserId())) {
                flash('global_msg', 'Punto desactivado correctamente.', 'warning');
            } else {
                throw new Exception("Error al intentar dar de baja el punto.");
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
    }

    public function delete($id) {
        try {
            if (Ruta::delete($id, $this->getUserId())) {
                flash('global_msg', 'Ruta turística movida a la papelera.', 'warning');
            } else {
                throw new Exception("No es posible eliminar la ruta en este momento.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Fallo de BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/rutas/index');
    }

    public function storeInventario() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $id_ruta = (int)$_POST['id_ruta'];
            $id_inventario = (int)$_POST['id_inventario'];
            $cantidad = (int)$_POST['cantidad'];
            $observaciones = trim($_POST['observaciones']);

            try {
                if (RutaInventario::asignar($id_ruta, $id_inventario, $cantidad, $observaciones, $this->getUserId())) {
                    flash('global_msg', 'Equipamiento asignado a la ruta correctamente.');
                } else {
                    throw new Exception("Error al procesar la asignación de recursos.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error en inventario: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/rutas/detalle/' . $id_ruta);
        }
    }

    public function deleteInventario($id_asignacion, $id_ruta) {
        try {
            if (RutaInventario::remover($id_asignacion)) {
                flash('global_msg', 'Asignación removida exitosamente.', 'info');
            } else {
                throw new Exception("No se pudo desvincular el recurso.");
            }
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT). '/rutas/detalle/' . $id_ruta;
    }
}
