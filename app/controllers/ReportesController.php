<?php
/**
 * ReportesController — Módulo de Reportes e Indicadores (RF27-RF31)
 * Incluye exportación a CSV (Excel) y PDF (HTML imprimible)
 *
 * ── Organización ────────────────────────────────────────────────────────────
 * El controlador reunía 101 métodos en 3.405 líneas. Se repartió por área en
 * traits bajo `reportes/`, dejando aquí solo lo transversal: el índice y los
 * tres helpers que todos los reportes comparten (`requireRoles`, `qsFiltros`,
 * `renderReporte`).
 *
 * La separación es **puramente organizativa**: los traits se componen en esta
 * misma clase, así que `$this`, los métodos privados y las firmas públicas son
 * idénticos a antes. Ninguna URL ni llamada cambió.
 *
 * Para agregar un reporte, hay que ubicarlo en el trait de su área:
 *
 *   reportes/ReportesRrhhTrait.php         personal, asistencia, permisos, disciplina, vacaciones
 *   reportes/ReportesFormacionTrait.php    talleres, cobertura, dossier, pasantes, trimestral
 *   reportes/ReportesTurismoTrait.php      rutas, participación, ejecuciones
 *   reportes/ReportesInventarioTrait.php   bienes, kardex, asignaciones, bajas
 *   reportes/ReportesRecepcionTrait.php    visitantes, visitas y estadísticas
 *   reportes/ReportesSistemaTrait.php      alertas, auditoría, accesos, duplicados
 *   reportes/ReportesIndicadoresTrait.php  indicadores de gestión (CMI)
 *   reportes/ReportesExportTrait.php       helpers de exportación (CSV/XLSX/PDF)
 *
 * Se incluyen con `require_once` en vez de dejarlos al autocargador porque éste
 * (`public/index.php`) solo mira rutas planas y no entra en subdirectorios.
 */

require_once __DIR__ . '/reportes/ReportesRrhhTrait.php';
require_once __DIR__ . '/reportes/ReportesFormacionTrait.php';
require_once __DIR__ . '/reportes/ReportesTurismoTrait.php';
require_once __DIR__ . '/reportes/ReportesInventarioTrait.php';
require_once __DIR__ . '/reportes/ReportesRecepcionTrait.php';
require_once __DIR__ . '/reportes/ReportesSistemaTrait.php';
require_once __DIR__ . '/reportes/ReportesIndicadoresTrait.php';
require_once __DIR__ . '/reportes/ReportesExportTrait.php';

class ReportesController extends Controller {

    use ReportesRrhhTrait;
    use ReportesFormacionTrait;
    use ReportesTurismoTrait;
    use ReportesInventarioTrait;
    use ReportesRecepcionTrait;
    use ReportesSistemaTrait;
    use ReportesIndicadoresTrait;
    use ReportesExportTrait;

    public function index() {
        $data = [
            'titulo' => 'Centro de Reportes e Indicadores'
        ];
        $this->view('reportes/index', $data);
    }

    private function requireRoles(array $roles) {
        $rol = (int)($_SESSION['user_rol'] ?? 0);
        if (!in_array($rol, $roles)) {
            flash('global_msg', 'No tienes permiso para acceder a este reporte.', 'danger');
            header('Location: ' . URL_ROOT . '/reportes/index');
            exit;
        }
    }

    /**
     * Query string con los filtros actuales para los enlaces de exportación,
     * EXCLUYENDO la clave 'url' del enrutador (.htaccess usa ?url=… con QSA;
     * si se arrastra, el enlace volvería al reporte en vez de exportar).
     */
    private function qsFiltros(): string {
        $q = $_GET;
        unset($q['url']);
        return http_build_query($q);
    }

    /**
     * Renderiza un reporte tabular: HTML normal, o PDF con membrete institucional
     * (misma plantilla/estilo que el Excel) cuando ?formato=pdf. Reutiliza
     * reportes/pdf_template aplanando las celdas con HTML (badges) a texto.
     */
    private function renderReporte(array $data) {
        if (($_GET['formato'] ?? '') === 'pdf') {
            $rows = [];
            foreach ($data['filas'] ?? [] as $f) {
                $r = [];
                foreach ($f as $c) {
                    $r[] = is_array($c)
                        ? trim(html_entity_decode(strip_tags($c['raw'] ?? ''), ENT_QUOTES, 'UTF-8'))
                        : (string)$c;
                }
                $rows[] = $r;
            }
            $this->exportPdf($data['titulo'] ?? 'Reporte', $data['subtitulo'] ?? '', $data['columnas'] ?? [], $rows, $data['resumen'] ?? []);
            return;
        }
        $this->view('reportes/tabla', $data);
    }
}
