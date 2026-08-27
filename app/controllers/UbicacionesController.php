<?php
/**
 * Controlador UbicacionesController
 */
class UbicacionesController extends Controller {

    public function index() {
        $ubicaciones = Ubicacion::all();
        $data = [
            'titulo' => 'Configuración: Sedes y Almacenes',
            'ubicaciones'  => $ubicaciones,
            'departamentos' => Departamento::all(),
            'sedes'         => Ubicacion::SEDES,
        ];
        $this->view('ubicaciones/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            // `sede` se valida contra la whitelist Ubicacion::SEDES (el modelo
            // vuelve a filtrarla); `es_deposito` marca el área común de los
            // bienes sin asignar (B-23/B-25), de donde el sistema deriva el
            // responsable vía `bienes_depto_autoriza`.
            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'id_departamento' => isset($_POST['id_departamento']) ? (int)$_POST['id_departamento'] : 0,
                'sede' => trim($_POST['sede'] ?? ''),
                'es_deposito' => !empty($_POST['es_deposito']),
            ];

            $esEdicion = !empty($data['id']);

            // El departamento es obligatorio (la columna es NOT NULL).
            if (empty($data['id_departamento'])) {
                flash('global_msg', 'Debe seleccionar el departamento al que pertenece la ubicación.', 'danger');
                header('Location: ' . URL_ROOT . '/ubicaciones/index');
                return;
            }

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
