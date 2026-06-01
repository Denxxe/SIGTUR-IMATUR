<?php
/**
 * Controlador EmpleadosController
 */
class EmpleadosController extends Controller {

    public function index() {
        $empleados     = Empleado::all();
        $cargos        = Cargo::all();
        $departamentos = Departamento::all();

        $db = new Database();
        $db->query("SELECT id, nombre, hora_entrada, hora_salida FROM horarios WHERE is_active = TRUE ORDER BY nombre ASC");
        $horarios = $db->resultSet();

        $data = [
            'titulo'       => 'Gestión de Personal (Empleados)',
            'empleados'    => $empleados,
            'cargos'       => $cargos,
            'departamentos'=> $departamentos,
            'horarios'     => $horarios,
        ];

        $this->view('empleados/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            $tiposContrato = ['Fijo', 'Contratado', 'Suplente', 'Comisión de Servicio'];
            $tipoContrato  = in_array($_POST['tipo_contrato'] ?? '', $tiposContrato)
                             ? $_POST['tipo_contrato'] : 'Fijo';

            $data = [
                'id'             => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_persona'     => isset($_POST['id_persona']) ? (int)$_POST['id_persona'] : null,
                'cedula'         => trim($_POST['cedula']),
                'nombre'         => trim($_POST['nombre']),
                'apellido'       => trim($_POST['apellido']),
                'telefono'       => trim($_POST['telefono']),
                'correo'         => trim($_POST['correo']),
                'genero'         => $_POST['genero'],
                'fecha_nacimiento'=> $_POST['fecha_nacimiento'],
                'direccion'      => trim($_POST['direccion']),
                'id_cargo'       => (int)$_POST['id_cargo'],
                'id_departamento'=> (int)$_POST['id_departamento'],
                'nro_expediente' => trim($_POST['nro_expediente']),
                'fecha_ingreso'  => $_POST['fecha_ingreso'],
                'tipo_contrato'  => $tipoContrato,
                'fecha_egreso'   => !empty($_POST['fecha_egreso']) ? $_POST['fecha_egreso'] : null,
                'id_horario'     => !empty($_POST['id_horario']) ? (int)$_POST['id_horario'] : null,
            ];

            // Validación de correo electrónico
            if (!empty($data['correo']) && !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
                flash('global_msg', 'El correo electrónico "' . htmlspecialchars($data['correo']) . '" no es válido. Por favor verifica el formato (ejemplo: nombre@dominio.com).', 'danger');
                header('Location: ' . URL_ROOT . '/empleados/index');
                return;
            }

            $esEdicion = !empty($data['id']);
            $empleado = new Empleado($data);

            try {
                if ($empleado->save($this->getUserId())) {
                    $msg = $esEdicion ? "Datos de empleado actualizados correctamente." : "Nuevo empleado registrado exitosamente en el sistema.";
                    flash('global_msg', $msg);
                    header('Location: ' . URL_ROOT . '/empleados/index');
                } else {
                    throw new Exception("Error interno al procesar el registro del empleado.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'No se pudo guardar la información: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/empleados/index');
            }
        }
    }

    public function delete($id) {
        try {
            if (Empleado::delete($id, $this->getUserId())) {
                flash('global_msg', 'El expediente del empleado ha sido movido a la papelera.', 'warning');
            } else {
                throw new Exception("No pudimos eliminar el registro en este momento.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Fallo en la eliminación: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/empleados/index');
    }
}
