<?php
/**
 * Controlador UbicacionesController
 */
class UbicacionesController extends Controller {

    public function index() {
        $ubicaciones = Ubicacion::all();
        $data = [
            'titulo' => 'Configuración: Sedes y Almacenes',
            'ubicaciones' => $ubicaciones
        ];
        $this->view('ubicaciones/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion'])
            ];

            $esEdicion = !empty($data['id']);
            $ubi = new Ubicacion($data);

            try {
                if ($ubi->save($this->getUserId())) {
                    $msg = $esEdicion ? "Ubicación institucional actualizada." : "Nueva sede/almacén registrada con éxito.";
                    flash('global_msg', $msg);
                } else {
                    throw new Exception("Error al procesar la ubicación física.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Fallo de configuración: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/ubicaciones/index');
        }
    }

    public function delete($id) {
        try {
            if (Ubicacion::delete($id, $this->getUserId())) {
                flash('global_msg', 'La ubicación ha sido enviada a la papelera.', 'warning');
            } else {
                throw new Exception("No pudimos eliminar la sede solicitada.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error de BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/ubicaciones/index');
    }
}
