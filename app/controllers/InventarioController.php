<?php
/**
 * Controlador InventarioController
 */
class InventarioController extends Controller {

    public function index() {
        $items = Inventario::all();
        $categorias = Categoria::all();
        $ubicaciones = Ubicacion::all();

        // Filtros server-side por categoría, ubicación, condición y tipo de bien.
        $fCategoria = (int)($_GET['categoria'] ?? 0);
        $fUbicacion = (int)($_GET['ubicacion'] ?? 0);
        $fCondicion = in_array($_GET['condicion'] ?? '', Inventario::CONDICIONES, true) ? $_GET['condicion'] : '';
        $fTipoBien  = in_array($_GET['tipo_bien'] ?? '', Inventario::TIPOS_BIEN, true) ? $_GET['tipo_bien'] : '';

        if ($fCategoria > 0) $items = array_values(array_filter($items, fn($i) => (int)($i->id_categoria ?? 0) === $fCategoria));
        if ($fUbicacion > 0) $items = array_values(array_filter($items, fn($i) => (int)($i->id_ubicacion ?? 0) === $fUbicacion));
        if ($fCondicion !== '') $items = array_values(array_filter($items, fn($i) => ($i->condicion ?? '') === $fCondicion));
        if ($fTipoBien !== '') $items = array_values(array_filter($items, fn($i) => ($i->tipo_bien ?? '') === $fTipoBien));

        $data = [
            'titulo' => 'Gestión de Bienes e Inventario',
            'items' => $items,
            'categorias' => $categorias,
            'ubicaciones' => $ubicaciones,
            'f_categoria' => $fCategoria,
            'f_ubicacion' => $fUbicacion,
            'f_condicion' => $fCondicion,
            'f_tipo_bien' => $fTipoBien,
        ];

        $this->view('inventario/index', $data);
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $_POST = $this->sanitizePost();
            
            $condicion = in_array($_POST['condicion'] ?? '', Inventario::CONDICIONES)
                ? $_POST['condicion'] : Inventario::CONDICION_DEFAULT;

            $tipoBien = in_array($_POST['tipo_bien'] ?? '', Inventario::TIPOS_BIEN, true)
                ? $_POST['tipo_bien'] : Inventario::TIPO_BIEN_DEFAULT;
            $esFungible = ($tipoBien === 'Fungible');

            $codigoBn = trim($_POST['codigo_bn'] ?? '');
            $serial   = trim($_POST['serial'] ?? '');

            // Reglas por tipo de bien (U5):
            //  · Durable: bien inventariable → Código BN obligatorio; cantidad = 1.
            //  · Fungible: consumible → sin Código BN ni serial; se controla por cantidad (≥1).
            if ($esFungible) {
                $codigoBn = '';                 // los consumibles no llevan Código BN
                $serial   = '';                 // ni serial individual
                $cantidad = max(1, (int)($_POST['cantidad'] ?? 1));
            } else {
                $cantidad = 1;                  // los durables se inventarían de uno en uno
                if ($codigoBn === '') {
                    flash('global_msg', 'El Código B.N. es obligatorio para los bienes durables (inventariables).', 'danger');
                    header('Location: ' . URL_ROOT . '/inventario/index');
                    return;
                }
            }

            $data = [
                'id' => isset($_POST['id']) ? (int)$_POST['id'] : null,
                'id_categoria' => (int)$_POST['id_categoria'],
                'id_ubicacion' => (int)$_POST['id_ubicacion'],
                // Vacío → NULL: evita colisión con los índices UNIQUE de codigo_bn/serial
                'codigo_bn' => $codigoBn !== '' ? $codigoBn : null,
                'nombre' => trim($_POST['nombre']),
                'descripcion' => trim($_POST['descripcion']),
                'marca' => trim($_POST['marca']),
                'modelo' => trim($_POST['modelo']),
                'serial' => $serial !== '' ? $serial : null,
                'condicion' => $condicion,
                'observaciones' => trim($_POST['observaciones']),
                'tipo_bien' => $tipoBien,
                'cantidad' => $cantidad,
            ];

            $esEdicion  = !empty($data['id']);
            $excludeId  = $esEdicion ? (int)$data['id'] : null;

            // Unicidad de Código BN (campo UNIQUE en BD)
            if (!empty($data['codigo_bn'])) {
                $dupBN = Inventario::findByCodigoBn($data['codigo_bn'], $excludeId);
                if ($dupBN) {
                    flash('global_msg', 'El Código BN "' . htmlspecialchars($data['codigo_bn']) . '" ya está registrado en otro bien (ID #' . $dupBN . '). Verifica el código antes de continuar.', 'danger');
                    header('Location: ' . URL_ROOT . '/inventario/index');
                    return;
                }
            }

            // Unicidad de Serial (campo UNIQUE en BD)
            if (!empty($data['serial'])) {
                $dupSer = Inventario::findBySerial($data['serial'], $excludeId);
                if ($dupSer) {
                    flash('global_msg', 'El número de serial "' . htmlspecialchars($data['serial']) . '" ya está asignado a otro bien (ID #' . $dupSer . '). Verifica el serial antes de continuar.', 'danger');
                    header('Location: ' . URL_ROOT . '/inventario/index');
                    return;
                }
            }

            $item = new Inventario($data);

            try {
                if ($item->save($this->getUserId())) {
                    $msg = $esEdicion ? "Bienes nacionales actualizados correctamente." : "Nuevo bien registrado exitosamente en el inventario.";
                    flash('global_msg', $msg);
                    header('Location: ' . URL_ROOT . '/inventario/index');
                } else {
                    throw new Exception("No es posible guardar el bien nacional en este momento.");
                }
            } catch (Exception $e) {
                flash('global_msg', 'Fallo en inventario: ' . $e->getMessage(), 'danger');
                header('Location: ' . URL_ROOT . '/inventario/index');
            }
        }
    }

    public function delete($id) {
        try {
            if (Inventario::delete($id, $this->getUserId())) {
                flash('global_msg', 'El bien nacional ha sido movido a la papelera de reciclaje.', 'warning');
            } else {
                throw new Exception("Error al intentar dar de baja el registro.");
            }
        } catch (Exception $e) {
            flash('global_msg', 'Error de BD: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/index');
    }
}
