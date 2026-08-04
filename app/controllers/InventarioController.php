<?php
/**
 * Controlador InventarioController — Bienes institucionales.
 *
 * Fase 1 del replanteamiento (mig. 062), ver docs/PLAN_MODULO_BIENES.md.
 * El módulo dejó de ser un CRUD de bienes: ahora modela el ciclo de vida
 * administrativo que gobierna la Alcaldía.
 *
 * Flujo de codificación (§4.1 del plan):
 *   1. Alta interna     → estatus "En espera de codificación", sin código.
 *   2. Informe/oficio a la Alcaldía (Fase 3).
 *   3. La Alcaldía devuelve el BM-1 con grupo-subgrupo-sección + N° de orden.
 *   4. `codificar()` transcribe esos datos → estatus "Activo".
 */
class InventarioController extends Controller {

    public function index() {
        // Vista: inventario activo (default), desincorporados o pendientes.
        $ver = $_GET['ver'] ?? '';
        if ($ver === 'baja') {
            $items = Inventario::desincorporados();
        } elseif ($ver === 'pendientes') {
            $items = Inventario::pendientesCodificacion();
        } else {
            $ver   = '';
            $items = Inventario::all();
        }

        $categorias  = Categoria::all();
        $ubicaciones = Ubicacion::all();

        // Filtros server-side
        $fCategoria = (int)($_GET['categoria'] ?? 0);
        $fUbicacion = (int)($_GET['ubicacion'] ?? 0);
        $fCondicion = in_array($_GET['condicion'] ?? '', Inventario::CONDICIONES, true) ? $_GET['condicion'] : '';
        $fEstatus   = in_array($_GET['estatus']   ?? '', Inventario::ESTATUS, true)     ? $_GET['estatus']   : '';
        $fOrigen    = in_array($_GET['origen']    ?? '', Inventario::ORIGENES, true)    ? $_GET['origen']    : '';

        if ($fCategoria > 0)    $items = array_values(array_filter($items, fn($i) => (int)($i->id_categoria ?? 0) === $fCategoria));
        if ($fUbicacion > 0)    $items = array_values(array_filter($items, fn($i) => (int)($i->id_ubicacion ?? 0) === $fUbicacion));
        if ($fCondicion !== '') $items = array_values(array_filter($items, fn($i) => ($i->condicion ?? '') === $fCondicion));
        if ($fEstatus   !== '') $items = array_values(array_filter($items, fn($i) => ($i->estatus   ?? '') === $fEstatus));
        if ($fOrigen    !== '') $items = array_values(array_filter($items, fn($i) => ($i->origen    ?? '') === $fOrigen));

        $data = [
            'titulo'      => 'Gestión de Bienes e Inventario',
            'items'       => $items,
            'categorias'  => $categorias,
            'ubicaciones' => $ubicaciones,
            'empleados'   => Empleado::all(),
            'resumen'     => Inventario::resumenPorEstatus(),
            'ver'         => $ver,
            'f_categoria' => $fCategoria,
            'f_ubicacion' => $fUbicacion,
            'f_condicion' => $fCondicion,
            'f_estatus'   => $fEstatus,
            'f_origen'    => $fOrigen,
        ];

        $this->view('inventario/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/inventario/index');
            return;
        }
        $_POST = $this->sanitizePost();

        $esEdicion = !empty($_POST['id']);
        $id        = $esEdicion ? (int)$_POST['id'] : null;

        $nombre = trim($_POST['nombre'] ?? '');
        if ($nombre === '') {
            return $this->volverConError('El nombre del bien es obligatorio.');
        }
        if ((int)($_POST['id_categoria'] ?? 0) <= 0 || (int)($_POST['id_ubicacion'] ?? 0) <= 0) {
            return $this->volverConError('Debes indicar la categoría y la ubicación del bien.');
        }

        $origen  = in_array($_POST['origen'] ?? '', Inventario::ORIGENES, true)
                    ? $_POST['origen'] : Inventario::ORIGEN_DEFAULT;
        $donante = trim($_POST['donante'] ?? '');

        // B-18: la donación se acredita con el nombre de quien dona.
        if ($origen === 'Donación' && $donante === '') {
            return $this->volverConError('Para un bien donado debes indicar quién lo dona (B-18).');
        }
        if ($origen === 'Compra') {
            $donante = '';
        }

        // Garantía: la fecha solo tiene sentido si la garantía existe.
        $tieneGarantia = !empty($_POST['tiene_garantia']);
        $garantiaVence = $tieneGarantia ? trim($_POST['garantia_vence'] ?? '') : '';
        if ($tieneGarantia && $garantiaVence === '') {
            return $this->volverConError('Indica la fecha de vencimiento de la garantía o desmarca la casilla.');
        }

        $costo = trim((string)($_POST['costo_adquisicion'] ?? ''));
        if ($costo !== '' && (!is_numeric($costo) || (float)$costo < 0)) {
            return $this->volverConError('El costo de adquisición debe ser un número válido.');
        }

        $serial = trim($_POST['serial'] ?? '');

        // El código oficial NO se captura al dar de alta: lo asigna la Alcaldía
        // y entra por `codificar()` al recibir el BM-1. En edición se respeta
        // lo que ya tenga el bien.
        $actual = $esEdicion ? Inventario::find($id) : null;
        if ($esEdicion && !$actual) {
            return $this->volverConError('El bien que intentas editar no existe.');
        }

        $estatus = $actual->estatus ?? Inventario::ESTATUS_DEFAULT;
        // Solo se permite cambiar el estatus a mano entre los operativos; la
        // codificación y la baja tienen sus propios flujos.
        $estatusManual = $_POST['estatus'] ?? '';
        $permitidosManual = [Inventario::EST_ACTIVO, Inventario::EST_MANTENIMIENTO,
                             Inventario::EST_EXTRAVIADO, Inventario::EST_ROBADO];
        if ($esEdicion && in_array($estatusManual, $permitidosManual, true)
            && $estatus !== Inventario::EST_SIN_CODIFICAR && $estatus !== Inventario::EST_BAJA) {
            $estatus = $estatusManual;
        }

        $data = [
            'id'            => $id,
            'id_categoria'  => (int)$_POST['id_categoria'],
            'id_ubicacion'  => (int)$_POST['id_ubicacion'],
            'nombre'        => $nombre,
            'descripcion'   => trim($_POST['descripcion'] ?? ''),
            'marca'         => trim($_POST['marca'] ?? ''),
            'modelo'        => trim($_POST['modelo'] ?? ''),
            'serial'        => $serial !== '' ? $serial : null,
            'condicion'     => $_POST['condicion'] ?? Inventario::CONDICION_DEFAULT,
            'estatus'       => $estatus,
            'observaciones' => trim($_POST['observaciones'] ?? ''),
            // Código: se conserva el existente, nunca se teclea en el alta.
            'codigo_grupo'        => $actual->codigo_grupo ?? null,
            'codigo_subgrupo'     => $actual->codigo_subgrupo ?? null,
            'codigo_seccion'      => $actual->codigo_seccion ?? null,
            'nro_orden'           => $actual->nro_orden ?? null,
            'verificado_alcaldia' => !empty($actual->verificado_alcaldia),
            'fecha_verificacion'  => $actual->fecha_verificacion ?? null,
            'origen'            => $origen,
            'donante'           => $donante,
            'costo_adquisicion' => $costo !== '' ? $costo : null,
            'fecha_adquisicion' => trim($_POST['fecha_adquisicion'] ?? '') ?: null,
            'proveedor'         => trim($_POST['proveedor'] ?? '') ?: null,
            'tiene_garantia'    => $tieneGarantia,
            'garantia_vence'    => $garantiaVence ?: null,
            'id_responsable'    => (int)($_POST['id_responsable'] ?? 0) ?: null,
        ];

        if (!empty($data['serial'])) {
            $dup = Inventario::findBySerial($data['serial'], $id);
            if ($dup) {
                return $this->volverConError('El serial "' . htmlspecialchars($data['serial'])
                    . '" ya está asignado a otro bien (ID #' . $dup . ').');
            }
        }

        try {
            $item = new Inventario($data);
            if (!$item->save($this->getUserId())) {
                throw new Exception('No es posible guardar el bien en este momento.');
            }
            flash('global_msg', $esEdicion
                ? 'Bien actualizado correctamente.'
                : 'Bien registrado. Queda "En espera de codificación" hasta que la Alcaldía asigne su N° de orden.');
        } catch (Exception $e) {
            flash('global_msg', 'Fallo en inventario: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/index');
    }

    /**
     * Conciliación del BM-1: transcribe el código que asignó la Alcaldía.
     * Pasa el bien de "En espera de codificación" a "Activo".
     */
    public function codificar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/inventario/index');
            return;
        }
        $_POST = $this->sanitizePost();
        $id = (int)($_POST['id'] ?? 0);

        try {
            if ($id <= 0 || !Inventario::find($id)) {
                throw new Exception('El bien indicado no existe.');
            }
            $partes = [
                'codigo_grupo'    => trim($_POST['codigo_grupo'] ?? ''),
                'codigo_subgrupo' => trim($_POST['codigo_subgrupo'] ?? ''),
                'codigo_seccion'  => trim($_POST['codigo_seccion'] ?? ''),
                'nro_orden'       => trim($_POST['nro_orden'] ?? ''),
            ];
            Inventario::codificar($id, $partes, trim($_POST['fecha_verificacion'] ?? '') ?: null, $this->getUserId());
            $codigo = Inventario::componerCodigo(
                $partes['codigo_grupo'], $partes['codigo_subgrupo'],
                $partes['codigo_seccion'], $partes['nro_orden']
            );
            flash('global_msg', 'Bien codificado como ' . htmlspecialchars($codigo) . '. Pasa a estatus Activo.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/index');
    }

    public function delete($id) {
        try {
            if (Inventario::delete($id, $this->getUserId())) {
                flash('global_msg', 'El bien ha sido movido a la papelera de reciclaje.', 'warning');
            } else {
                throw new Exception('Error al intentar eliminar el registro.');
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error de BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/index');
    }

    /** Vuelve al listado con un mensaje de error de validación. */
    private function volverConError(string $msg): void {
        flash('global_msg', $msg, 'danger');
        header('Location: ' . URL_ROOT . '/inventario/index');
    }
}
