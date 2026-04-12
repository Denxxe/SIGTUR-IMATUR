<?php
/**
 * Controlador CargosController
 * Gestiona las operaciones CRUD para la entidad Cargo
 */
class CargosController extends Controller {

    public function __construct() {
        // En un sistema real aquí se verificaría la sesión del usuario
    }

    /**
     * Listado principal de cargos
     */
    public function index() {
        // Obtener todos los cargos usando el método estático del modelo
        $cargos = Cargo::all();

        $data = [
            'titulo' => 'Gestión de Cargos',
            'cargos' => $cargos
        ];

        // Cargar la vista index y pasar los datos
        $this->view('cargos/index', $data);
    }

    /**
     * Procesar el guardado de un nuevo cargo (o edición)
     */
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Sanitizar datos post
            $_POST = filter_input_array(INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

            $id = isset($_POST['id']) ? (int)$_POST['id'] : null;
            
            $data = [
                'id' => $id,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'sueldo_base' => (float)$_POST['sueldo_base'],
                'user_id' => 1 // ID de usuario quemado por ahora hasta tener Auth
            ];

            // Crear instancia del modelo
            $cargo = new Cargo($data);

            if ($cargo->save($data['user_id'])) {
                // Redirigir al listado con mensaje de éxito (pendiente implementar flash messages)
                header('Location: ' . URL_ROOT . '/cargos/index');
            } else {
                die('Algo salió mal al guardar el cargo');
            }
        }
    }

    /**
     * Eliminar un cargo (Borrado lógico)
     */
    public function delete($id) {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            // En una app real, esto debería ser vía POST o DELETE por seguridad
            if (Cargo::delete($id, $this->getUserId())) {
                header('Location: ' . URL_ROOT . '/cargos/index');
            } else {
                die('No se pudo eliminar el registro');
            }
        }
    }
}
