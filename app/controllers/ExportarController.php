<?php
require_once __DIR__ . '/reportes/ReportesExportTrait.php';

/**
 * ExportarController — convierte un listado de la pantalla en un .xlsx REAL.
 *
 * POR QUÉ EXISTE
 * El botón Excel de los listados generaba un HTML con extensión `.xls` desde el
 * navegador. Excel lo abre, pero **no dibuja las imágenes en `data:` URI**: el
 * membrete salía con dos recuadros rotos donde van los logos, y las celdas que
 * los contenían estrechaban la primera y la última columna. Aquí el archivo lo
 * escribe el servidor con el mismo generador de los reportes de Análisis
 * (`ReportesExportTrait`), que produce OOXML de verdad y ancla los logos como
 * imagen (`XlsxLogos`).
 *
 * QUÉ RECIBE
 * Las filas **ya filtradas y paginadas** que el usuario está viendo: el
 * navegador manda lo que hay en la tabla, no un identificador de consulta. Así
 * el Excel respeta el buscador y los filtros del listado sin que el servidor
 * tenga que reconstruir la consulta de cada módulo.
 *
 * SOBRE EL ACCESO
 * Va en `$accesoSiempre` del Router (como Perfil, Búsqueda y Descarga): no
 * expone datos nuevos —solo da formato a lo que el usuario ya tiene en
 * pantalla, y cada módulo sigue protegido por su propio RBAC—, pero **exige
 * sesión iniciada**, que es lo que el Router garantiza antes de despachar.
 */
class ExportarController extends Controller {

    use ReportesExportTrait;

    /** Cotas para que un POST manipulado no reviente la memoria del servidor. */
    const MAX_FILAS    = 20000;
    const MAX_COLUMNAS = 60;
    const MAX_CELDA    = 2000;   // caracteres

    /**
     * Recibe {titulo, headers[], rows[][]} y devuelve el .xlsx.
     * Responde siempre por POST: el volumen de una tabla completa no cabe en
     * una URL, y así el listado no queda expuesto en el historial del navegador.
     */
    public function tabla() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/dashboard/index');
            return;
        }

        try {
            $titulo  = trim((string)($_POST['titulo'] ?? ''));
            if ($titulo === '') $titulo = 'Listado';
            if (mb_strlen($titulo) > 120) $titulo = mb_substr($titulo, 0, 120);

            $headers = $this->decodificar($_POST['headers'] ?? '');
            $filas   = $this->decodificar($_POST['rows'] ?? '');

            if (!$headers) throw new Exception('El listado no trae encabezados.');
            if (count($headers) > self::MAX_COLUMNAS) {
                throw new Exception('El listado tiene demasiadas columnas para exportar.');
            }
            if (count($filas) > self::MAX_FILAS) {
                throw new Exception('El listado supera las ' . self::MAX_FILAS . ' filas. Use los filtros para acotarlo.');
            }

            $ncol    = count($headers);
            $headers = array_map([$this, 'limpiarCelda'], $headers);

            // Toda fila se recorta o se rellena a la anchura del encabezado: una
            // fila más larga desalinearía las columnas en Excel sin avisar.
            $normalizadas = [];
            foreach ($filas as $fila) {
                $fila = array_map([$this, 'limpiarCelda'], array_values((array)$fila));
                $normalizadas[] = array_pad(array_slice($fila, 0, $ncol), $ncol, '');
            }

            // exportCsv() emite el .xlsx y termina la petición.
            $this->exportCsv($this->slug($titulo), $headers, $normalizadas, $titulo);

        } catch (Exception $e) {
            flash('global_msg', 'No se pudo exportar: ' . $e->getMessage(), 'danger');
            header('Location: ' . ($_SERVER['HTTP_REFERER'] ?? URL_ROOT . '/dashboard/index'));
        }
    }

    /**
     * El navegador manda headers y filas como JSON en un campo del formulario
     * (un `rows[][]` nativo se vuelve ilegible con tablas grandes y depende del
     * `max_input_vars` de PHP, que corta en silencio a las 1000 entradas).
     */
    private function decodificar($crudo): array {
        if (is_array($crudo)) return $crudo;
        $datos = json_decode((string)$crudo, true);
        return is_array($datos) ? $datos : [];
    }

    /** Texto plano, acotado; nunca una fórmula. */
    private function limpiarCelda($v): string {
        $v = trim((string)(is_scalar($v) ? $v : ''));
        if (mb_strlen($v) > self::MAX_CELDA) $v = mb_substr($v, 0, self::MAX_CELDA);
        // Excel interpreta como fórmula toda celda que empiece por = + - @.
        // El generador las escribe como texto, pero el prefijo evita que otras
        // hojas de cálculo las ejecuten al abrir el archivo.
        if ($v !== '' && strpos("=+-@", $v[0]) !== false) $v = "'" . $v;
        return $v;
    }

    /** Nombre de archivo a partir del título. */
    private function slug(string $s): string {
        $s = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s) ?: $s;
        $s = strtolower(preg_replace('/[^A-Za-z0-9]+/', '_', $s));
        return trim($s, '_') ?: 'listado';
    }
}
