<?php
/**
 * ActividadesInventarioController — movimientos de bienes.
 *
 * Fase 2 (mig. 063), ver docs/PLAN_MODULO_BIENES.md §4.2/§4.3.
 *
 * Un movimiento cambia el estado del bien, así que toda la lógica vive en
 * `ActividadInventario::registrarMovimiento()` (transaccional). Aquí solo
 * se recogen y validan los datos del formulario.
 */
class ActividadesinventarioController extends Controller {

    public function index() {
        $autorizador = ActividadInventario::autorizador();

        $data = [
            'titulo'      => 'Movimientos de Inventario',
            'actividades' => ActividadInventario::all(),
            // Solo se mueven bienes que siguen en el inventario activo.
            'inventario'  => Inventario::all(),
            'ubicaciones' => Ubicacion::all(),
            'empleados'   => Empleado::all(),
            'enCurso'     => Mantenimiento::enCurso(),
            'autorizador' => $autorizador,
        ];

        $this->view('actividades_inventario/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/actividadesinventario/index');
            return;
        }
        $_POST = $this->sanitizePost();

        try {
            // B-32/B-64: la autorización no la elige el usuario — la resuelve
            // el sistema por cargo + departamento. Si el puesto está vacante,
            // el movimiento no puede registrarse.
            $autorizador = ActividadInventario::autorizador();
            if (!$autorizador) {
                throw new Exception('No hay un responsable de la Coordinación de Bienes registrado '
                    . '(cargo y departamento configurados en Sistema → Configuración). '
                    . 'Sin ese cargo asignado no se pueden autorizar movimientos.');
            }

            $tipo = $_POST['tipo_movimiento'] ?? '';
            if (!in_array($tipo, ActividadInventario::TIPOS_MANUALES, true)) {
                throw new Exception('Selecciona un tipo de movimiento válido.');
            }

            $fecha = trim($_POST['fecha'] ?? '') ?: date('Y-m-d');
            if ($fecha > date('Y-m-d')) {
                throw new Exception('La fecha del movimiento no puede ser futura.');
            }

            $costo = trim((string)($_POST['costo'] ?? ''));
            if ($costo !== '' && (!is_numeric($costo) || (float)$costo < 0)) {
                throw new Exception('El costo del mantenimiento debe ser un número válido.');
            }

            $datos = [
                'id_inventario'           => (int)($_POST['id_inventario'] ?? 0),
                'tipo_movimiento'         => $tipo,
                'fecha'                   => $fecha,
                'descripcion'             => trim($_POST['descripcion'] ?? ''),
                'id_ubicacion_destino'    => (int)($_POST['id_ubicacion_destino'] ?? 0) ?: null,
                'id_empleado_responsable' => (int)($_POST['id_empleado_responsable'] ?? 0) ?: null,
                'autorizado_por'          => (int)$autorizador->id,
                // Mantenimiento
                'id_empleado_encargado'   => (int)($_POST['id_empleado_encargado'] ?? 0) ?: null,
                'proveedor_externo'       => trim($_POST['proveedor_externo'] ?? ''),
                'descripcion_falla'       => trim($_POST['descripcion_falla'] ?? ''),
                'trabajo_realizado'       => trim($_POST['trabajo_realizado'] ?? ''),
                'costo'                   => $costo,
                'resultado'               => $_POST['resultado'] ?? '',
            ];

            ActividadInventario::registrarMovimiento($datos, $this->getUserId());
            flash('global_msg', 'Movimiento registrado y aplicado al bien.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/actividadesinventario/index');
    }

    public function delete($id) {
        try {
            if (ActividadInventario::delete($id, $this->getUserId())) {
                flash('global_msg', 'Movimiento eliminado de la bitácora. '
                    . 'Ojo: esto NO revierte el efecto que tuvo sobre el bien.', 'warning');
            } else {
                throw new Exception('No se pudo eliminar el movimiento.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error al eliminar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/actividadesinventario/index');
    }
}
