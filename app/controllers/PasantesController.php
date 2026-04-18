<?php
/**
 * Controlador de Pasantes
 */
class PasantesController extends Controller {

    private $pasanteModel;
    private $empleadoModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
        $this->pasanteModel = $this->model('Pasante');
        $this->empleadoModel = $this->model('Empleado');
    }

    public function index() {
        $pasantes = $this->pasanteModel->getPasantesConTutor();

        $data = [
            'titulo' => 'Módulo de Pasantes',
            'pasantes' => $pasantes
        ];

        $this->view('pasantes/index', $data);
    }

    public function detalle($id) {
        $pasante = $this->pasanteModel->getPasanteUnico($id);
        if (!$pasante) {
            flash('global_msg', 'El pasante solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/index');
            exit;
        }

        $documentos = $this->pasanteModel->getDocumentos($id);

        $data = [
            'titulo' => 'Detalle del Pasante',
            'pasante' => $pasante,
            'documentos' => $documentos
        ];

        $this->view('pasantes/detalle', $data);
    }

    public function subirDocumento($id_pasante) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tipo = $_POST['tipo_documento'];
            $observaciones = $_POST['observaciones'] ?? '';
            $archivoUrl = null;

            try {
                // Manejo de la subida del archivo
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    $fileTmp = $_FILES['archivo']['tmp_name'];
                    $fileName = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['archivo']['name']));
                    
                    $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/pasantes/';
                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    $filePath = $uploadDir . $fileName;

                    if (move_uploaded_file($fileTmp, $filePath)) {
                        $archivoUrl = '/uploads/pasantes/' . $fileName;
                    } else {
                        throw new Exception("Error al mover el archivo al servidor.");
                    }
                }

                $docData = [
                    'id_pasante' => $id_pasante,
                    'tipo_documento' => $tipo,
                    'entregado' => true,
                    'archivo_url' => $archivoUrl,
                    'observaciones' => $observaciones
                ];

                if ($this->pasanteModel->saveDocumento($docData)) {
                    flash('global_msg', 'Documentación del pasante actualizada correctamente.');
                } else {
                    throw new Exception("Fallo al registrar el documento en la base de datos.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error en gestión documental: ' . $e->getMessage(), 'danger');
            }
            
            header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id_pasante);
        }
    }
}
