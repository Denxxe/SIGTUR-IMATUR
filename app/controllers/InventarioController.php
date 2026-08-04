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
            // Si la codificación se hace desde una recepción de BM-1, se deja
            // la trazabilidad de en qué formulario vino ese código.
            $idBM1 = (int)($_POST['id_consolidado_bm1'] ?? 0) ?: null;
            Inventario::codificar($id, $partes, trim($_POST['fecha_verificacion'] ?? '') ?: null,
                                  $this->getUserId(), $idBM1);
            $codigo = Inventario::componerCodigo(
                $partes['codigo_grupo'], $partes['codigo_subgrupo'],
                $partes['codigo_seccion'], $partes['nro_orden']
            );
            flash('global_msg', 'Bien codificado como ' . htmlspecialchars($codigo) . '. Pasa a estatus Activo.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        // Volver a donde se estaba trabajando: la pantalla de BM-1 si la
        // codificación salió de una recepción, o el listado si no.
        $volver = !empty($_POST['id_consolidado_bm1'])
            ? '/inventario/consolidados' : '/inventario/index';
        header('Location: ' . URL_ROOT . $volver);
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

    // =====================================================================
    //  Hoja de vida del bien (B-36)
    // =====================================================================

    /**
     * Expediente completo de un bien: datos, código oficial, movimientos,
     * mantenimientos y documentos de respaldo.
     */
    public function detalle($id = 0) {
        $bien = Inventario::find((int)$id);
        if (!$bien) {
            flash('global_msg', 'El bien solicitado no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/inventario/index');
            return;
        }
        $this->view('inventario/detalle', [
            'titulo'         => 'Bien — ' . $bien->nombre,
            'bien'           => $bien,
            'movimientos'    => ActividadInventario::byItem((int)$id),
            'mantenimientos' => Mantenimiento::porBien((int)$id),
            'documentos'     => InventarioDocumento::porBien((int)$id),
            'consolidado'    => !empty($bien->id_consolidado_bm1)
                                    ? ConsolidadoBM1::find((int)$bien->id_consolidado_bm1) : null,
        ]);
    }

    // =====================================================================
    //  Documentos de respaldo (B-16 a B-19)
    // =====================================================================

    public function subirDocumento() {
        $idBien = (int)($_POST['id_inventario'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Inventario::find($idBien)) {
                throw new Exception('Solicitud no válida.');
            }
            $tipo = $_POST['tipo_documento'] ?? '';
            if (!isset(InventarioDocumento::TIPOS[$tipo])) {
                throw new Exception('Selecciona un tipo de documento válido.');
            }
            $archivo = $this->guardarArchivoBien('documento', 'Doc_' . $tipo . '_' . $idBien);
            InventarioDocumento::save([
                'id_inventario'   => $idBien,
                'tipo_documento'  => $tipo,
                'archivo_url'     => $archivo['nombre'],
                'nombre_original' => $archivo['original'],
                'observaciones'   => trim($_POST['observaciones'] ?? ''),
            ], $this->getUserId());
            flash('global_msg', 'Documento adjuntado al bien.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/detalle/' . $idBien);
    }

    public function eliminarDocumento($idDoc = 0) {
        $doc = InventarioDocumento::find((int)$idDoc);
        if (!$doc) {
            flash('global_msg', 'El documento no existe.', 'danger');
            header('Location: ' . URL_ROOT . '/inventario/index');
            return;
        }
        try {
            InventarioDocumento::delete((int)$idDoc, $this->getUserId());
            flash('global_msg', 'Documento eliminado.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/detalle/' . (int)$doc->id_inventario);
    }

    /** Foto del bien (B-21). */
    public function subirFoto() {
        $idBien = (int)($_POST['id_inventario'] ?? 0);
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Inventario::find($idBien)) {
                throw new Exception('Solicitud no válida.');
            }
            $archivo = $this->guardarArchivoBien('foto', 'Foto_' . $idBien, ['jpg', 'jpeg', 'png']);
            $db = new Database();
            $db->query("UPDATE inventario SET foto_url=:f, updated_at=CURRENT_TIMESTAMP, updated_by=:u WHERE id=:id");
            $db->bind(':f',  $archivo['nombre']);
            $db->bind(':u',  $this->getUserId());
            $db->bind(':id', $idBien);
            $db->execute();
            flash('global_msg', 'Foto del bien actualizada.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/detalle/' . $idBien);
    }

    // =====================================================================
    //  Recepción del BM-1 consolidado (§2-bis del plan)
    // =====================================================================

    /** Listado de BM-1 recibidos + bienes pendientes de codificar. */
    public function consolidados() {
        $this->view('inventario/consolidados', [
            'titulo'      => 'Formularios BM-1 recibidos',
            'consolidados'=> ConsolidadoBM1::all(),
            'pendientes'  => Inventario::pendientesCodificacion(),
        ]);
    }

    /** Registra la recepción de un BM-1 (con su archivo escaneado). */
    public function registrarBM1() {
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') throw new Exception('Solicitud no válida.');
            $_POST = $this->sanitizePost();

            $fechaRec = trim($_POST['fecha_recepcion'] ?? '') ?: date('Y-m-d');
            if ($fechaRec > date('Y-m-d')) {
                throw new Exception('La fecha de recepción no puede ser futura.');
            }

            // El archivo es opcional: a veces el BM-1 llega en papel y se
            // escanea después. Lo importante es dejar registrada la recepción.
            $archivo = ['nombre' => null, 'original' => null];
            if (!empty($_FILES['documento']['name'])) {
                $archivo = $this->guardarArchivoBien('documento', 'BM1_' . date('Ymd_His'));
            }

            ConsolidadoBM1::crear([
                'fecha_recepcion' => $fechaRec,
                'fecha_documento' => trim($_POST['fecha_documento'] ?? ''),
                'referencia'      => trim($_POST['referencia'] ?? ''),
                'archivo_url'     => $archivo['nombre'],
                'nombre_original' => $archivo['original'],
                'observaciones'   => trim($_POST['observaciones'] ?? ''),
            ], $this->getUserId());

            flash('global_msg', 'Recepción del BM-1 registrada. Ya puedes codificar los bienes que trae.');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/consolidados');
    }

    public function eliminarBM1($id = 0) {
        try {
            ConsolidadoBM1::delete((int)$id, $this->getUserId());
            flash('global_msg', 'Recepción eliminada. Los bienes ya codificados conservan su código.', 'warning');
        } catch (Exception $e) {
            flash('global_msg', $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/inventario/consolidados');
    }

    // =====================================================================
    //  Helpers
    // =====================================================================

    /**
     * Guarda un archivo del módulo en storage/uploads/bienes/ (fuera del web
     * root). Valida extensión Y MIME real, igual que en RRHH.
     * @return array{nombre:string, original:string}
     */
    private function guardarArchivoBien(string $campo, string $prefijo,
                                        array $permitidas = ['pdf','jpg','jpeg','png']): array {
        if (empty($_FILES[$campo]['name']) || ($_FILES[$campo]['error'] ?? 1) !== UPLOAD_ERR_OK) {
            throw new Exception('Debes seleccionar un archivo válido.');
        }
        if ($_FILES[$campo]['size'] > 5 * 1024 * 1024) {
            throw new Exception('El archivo no puede superar los 5 MB.');
        }
        $original = $_FILES[$campo]['name'];
        $ext = strtolower(pathinfo($original, PATHINFO_EXTENSION));
        if (!in_array($ext, $permitidas, true)) {
            throw new Exception('Formato no permitido. Se admite: ' . strtoupper(implode(', ', $permitidas)) . '.');
        }
        $mimesOk = [
            'pdf'  => ['application/pdf'],
            'jpg'  => ['image/jpeg'], 'jpeg' => ['image/jpeg'],
            'png'  => ['image/png'],
        ];
        $mimeReal = function_exists('mime_content_type') ? @mime_content_type($_FILES[$campo]['tmp_name']) : null;
        if ($mimeReal && !in_array($mimeReal, $mimesOk[$ext] ?? [], true)) {
            throw new Exception('El contenido del archivo no coincide con su extensión.');
        }

        $dir = dirname(dirname(__DIR__)) . '/storage/uploads/bienes/';
        if (!is_dir($dir) && !@mkdir($dir, 0775, true) && !is_dir($dir)) {
            throw new Exception('No se pudo preparar la carpeta de archivos.');
        }
        $nombre = preg_replace('/[^A-Za-z0-9_\-]/', '', $prefijo) . '_' . time() . '.' . $ext;
        if (!move_uploaded_file($_FILES[$campo]['tmp_name'], $dir . $nombre)) {
            throw new Exception('No se pudo guardar el archivo.');
        }
        return ['nombre' => $nombre, 'original' => $original];
    }

    /** Vuelve al listado con un mensaje de error de validación. */
    private function volverConError(string $msg): void {
        flash('global_msg', $msg, 'danger');
        header('Location: ' . URL_ROOT . '/inventario/index');
    }
}
