<?php
/**
 * Controlador DepartamentosController
 */
class DepartamentosController extends Controller {

    public function index() {
        $departamentos = Departamento::arbol(); // orden jerárquico (árbol) con ->nivel
        $data = [
            'titulo' => 'Estructura Organizativa (Departamentos)',
            'departamentos' => $departamentos
        ];
        $this->view('departamentos/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            $tipoUnidad = in_array($_POST['tipo_unidad'] ?? '', Departamento::TIPOS_UNIDAD, true)
                          ? $_POST['tipo_unidad'] : null;
            $idPadre = !empty($_POST['id_padre']) ? (int)$_POST['id_padre'] : null;
            // Una unidad no puede ser su propio padre
            if ($id && $idPadre === $id) { $idPadre = null; }
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'tipo_unidad' => $tipoUnidad,
                'id_padre' => $idPadre
            ];

            $esEdicion = !empty($id);
            $dpto = new Departamento($data);

            try {
                if ($dpto->save($this->getUserId())) {
                    $msg = $esEdicion ? "Departamento institucional actualizado." : "Nuevo departamento administrativo registrado.";
                    flash('global_msg', $msg);
                } else {
                    throw new Exception("Error al intentar procesar el departamento.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'No se pudo guardar: ' . $e->getMessage(), 'danger');
            }
            header('Location: ' . URL_ROOT . '/departamentos/index');
        }
    }

    public function delete($id) {
        try {
            if (Departamento::delete($id, $this->getUserId())) {
                flash('global_msg', 'Departamento desactivado y movido a la papelera.', 'warning');
            } else {
                throw new Exception("El registro no pudo eliminarse.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Fallo en la BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/departamentos/index');
    }
}
