<?php
class PasantesController extends Controller {

    private Pasante $pasanteModel;
    private Empleado $empleadoModel;

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
        $this->pasanteModel  = $this->model('Pasante');
        $this->empleadoModel = $this->model('Empleado');
    }

    public function index() {
        $data = [
            'titulo'   => 'Módulo de Pasantes',
            'pasantes' => $this->pasanteModel->getPasantesConTutor()
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

        $data = [
            'titulo'     => 'Detalle del Pasante',
            'pasante'    => $pasante,
            'documentos' => $this->pasanteModel->getDocumentos($id)
        ];
        $this->view('pasantes/detalle', $data);
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            $cedula    = trim($_POST['cedula']    ?? '');
            $nombre    = trim($_POST['nombre']    ?? '');
            $apellido  = trim($_POST['apellido']  ?? '');
            $userId    = $this->getUserId();

            $pasanteData = [
                'institucion'            => trim($_POST['institucion'] ?? ''),
                'carrera'                => trim($_POST['carrera']     ?? ''),
                'id_tutor_institucional' => !empty($_POST['id_tutor_institucional']) ? (int)$_POST['id_tutor_institucional'] : null,
                'fecha_inicio'           => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
                'fecha_fin'              => !empty($_POST['fecha_fin'])    ? $_POST['fecha_fin']    : null,
                'estado'                 => 'Postulado'
            ];

            try {
                // Reusar persona existente o crear una nueva
                $persona = $this->pasanteModel->findPersonaByCedula($cedula);

                if ($persona) {
                    $idPersona = $persona->id;
                } else {
                    $idPersona = $this->pasanteModel->createPersona(
                        ['cedula' => $cedula, 'nombre' => $nombre, 'apellido' => $apellido],
                        $userId
                    );
                    if (!$idPersona) {
                        throw new Exception("No se pudo registrar los datos personales.");
                    }
                }

                if ($this->pasanteModel->create($idPersona, $pasanteData, $userId)) {
                    flash('global_msg', 'Expediente de pasante creado exitosamente.');
                    header('Location: ' . URL_ROOT . '/pasantes/index');
                } else {
                    throw new Exception("No se pudo registrar el pasante.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error al registrar: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/crear');
            }
            exit;
        }

        $data = [
            'titulo'    => 'Registrar Nuevo Pasante',
            'empleados' => Empleado::all()
        ];
        $this->view('pasantes/crear', $data);
    }

    public function editar($id) {
        $pasante = $this->pasanteModel->getById($id);
        if (!$pasante) {
            flash('global_msg', 'Pasante no encontrado.', 'danger');
            header('Location: ' . URL_ROOT . '/pasantes/index');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();

            $idPersona = (int)($_POST['id_persona'] ?? $pasante->id_persona);
            $userId    = $this->getUserId();

            $personaData = [
                'cedula'   => trim($_POST['cedula']   ?? ''),
                'nombre'   => trim($_POST['nombre']   ?? ''),
                'apellido' => trim($_POST['apellido'] ?? '')
            ];

            $pasanteData = [
                'id'                     => $id,
                'institucion'            => trim($_POST['institucion'] ?? ''),
                'carrera'                => trim($_POST['carrera']     ?? ''),
                'id_tutor_institucional' => !empty($_POST['id_tutor_institucional']) ? (int)$_POST['id_tutor_institucional'] : null,
                'fecha_inicio'           => !empty($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : null,
                'fecha_fin'              => !empty($_POST['fecha_fin'])    ? $_POST['fecha_fin']    : null,
                'estado'                 => $_POST['estado']    ?? 'Postulado',
                'evaluacion'             => trim($_POST['evaluacion'] ?? ''),
                'nota'                   => $_POST['nota'] ?? ''
            ];

            try {
                $this->pasanteModel->updatePersona($idPersona, $personaData, $userId);

                if ($this->pasanteModel->update($pasanteData, $userId)) {
                    flash('global_msg', 'Expediente actualizado correctamente.');
                    header('Location: ' . URL_ROOT . '/pasantes/detalle/' . $id);
                } else {
                    throw new Exception("Error al actualizar el expediente.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/pasantes/editar/' . $id);
            }
            exit;
        }

        $data = [
            'titulo'    => 'Editar Expediente',
            'pasante'   => $pasante,
            'empleados' => Empleado::all()
        ];
        $this->view('pasantes/editar', $data);
    }

    public function eliminar($id) {
        try {
            if ($this->pasanteModel->softDelete($id, $this->getUserId())) {
                flash('global_msg', 'Pasante desactivado y movido a la papelera.', 'warning');
            } else {
                throw new Exception("No se pudo desactivar el pasante.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/pasantes/index');
        exit;
    }

    public function subirDocumento($id_pasante) {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $tipo          = $_POST['tipo_documento'];
            $observaciones = $_POST['observaciones'] ?? '';
            $archivoUrl    = null;

            try {
                if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] === UPLOAD_ERR_OK) {
                    $fileName  = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($_FILES['archivo']['name']));
                    $uploadDir = dirname(dirname(__DIR__)) . '/public/uploads/pasantes/';

                    if (!file_exists($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }

                    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $uploadDir . $fileName)) {
                        $archivoUrl = '/uploads/pasantes/' . $fileName;
                    } else {
                        throw new Exception("Error al mover el archivo al servidor.");
                    }
                }

                $docData = [
                    'id_pasante'     => $id_pasante,
                    'tipo_documento' => $tipo,
                    'entregado'      => true,
                    'archivo_url'    => $archivoUrl,
                    'observaciones'  => $observaciones
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
