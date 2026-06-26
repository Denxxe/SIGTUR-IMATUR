<?php
/**
 * BuscarController — búsqueda global desde el header.
 * Accesible por cualquier usuario autenticado (permitido siempre en Router.php).
 * Cada módulo se consulta solo si el rol tiene acceso a él (resultados gated).
 */
class BuscarController extends Controller {

    public function index() {
        $q   = trim($_GET['q'] ?? '');
        $rol = (int)($_SESSION['user_rol'] ?? 0);
        $grupos = [];

        if (mb_strlen($q) >= 2) {
            $db   = new Database();
            $like = '%' . $q . '%';

            // ── Empleados (RRHH / Admin) ──────────────────────────────────────
            if (in_array($rol, [1, 2], true)) {
                $db->query("SELECT e.id, p.nombre, p.apellido, p.cedula, c.nombre AS cargo
                            FROM empleados e
                            INNER JOIN personas p ON e.id_persona = p.id
                            LEFT  JOIN cargos c   ON e.id_cargo   = c.id
                            WHERE e.is_active = TRUE AND p.is_active = TRUE AND e.fecha_egreso IS NULL
                              AND ((p.nombre || ' ' || p.apellido) ILIKE :q OR p.cedula ILIKE :q)
                            ORDER BY p.nombre ASC LIMIT 8");
                $db->bind(':q', $like);
                $items = [];
                foreach ($db->resultSet() as $r) {
                    $items[] = [
                        'texto' => trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                        'sub'   => ($r->cedula ? 'C.I. ' . $r->cedula : '') . ($r->cargo ? ' · ' . $r->cargo : ''),
                        'url'   => URL_ROOT . '/empleados/detalle/' . (int)$r->id,
                    ];
                }
                $grupos[] = ['titulo' => 'Empleados', 'icono' => 'bi-person-badge', 'items' => $items];
            }

            // ── Inventario (Inventario / Admin) ───────────────────────────────
            if (in_array($rol, [1, 4], true)) {
                $db->query("SELECT id, nombre, codigo_bn, marca, modelo
                            FROM inventario
                            WHERE is_active = TRUE
                              AND (nombre ILIKE :q OR codigo_bn ILIKE :q OR serial ILIKE :q OR marca ILIKE :q)
                            ORDER BY nombre ASC LIMIT 8");
                $db->bind(':q', $like);
                $items = [];
                foreach ($db->resultSet() as $r) {
                    $items[] = [
                        'texto' => $r->nombre ?? '—',
                        'sub'   => ($r->codigo_bn ? 'BN ' . $r->codigo_bn : 'Sin código') . trim(' ' . ($r->marca ?? '') . ' ' . ($r->modelo ?? '')),
                        'url'   => URL_ROOT . '/inventario/index',
                    ];
                }
                $grupos[] = ['titulo' => 'Inventario', 'icono' => 'bi-box-seam', 'items' => $items];
            }

            // ── Formación y Turismo (Turismo / Admin) ─────────────────────────
            if (in_array($rol, [1, 3], true)) {
                $db->query("SELECT id, nombre, estado FROM talleres
                            WHERE is_active = TRUE AND nombre ILIKE :q ORDER BY fecha_inicio DESC LIMIT 8");
                $db->bind(':q', $like);
                $items = [];
                foreach ($db->resultSet() as $r) {
                    $items[] = ['texto' => $r->nombre ?? '—', 'sub' => 'Estado: ' . ($r->estado ?? '—'), 'url' => URL_ROOT . '/talleres/index'];
                }
                $grupos[] = ['titulo' => 'Talleres / Actividades', 'icono' => 'bi-mortarboard', 'items' => $items];

                $db->query("SELECT id, nombre, estado FROM rutas
                            WHERE is_active = TRUE AND nombre ILIKE :q ORDER BY nombre ASC LIMIT 8");
                $db->bind(':q', $like);
                $items = [];
                foreach ($db->resultSet() as $r) {
                    $items[] = ['texto' => $r->nombre ?? '—', 'sub' => 'Estado: ' . ($r->estado ?? '—'), 'url' => URL_ROOT . '/rutas/index'];
                }
                $grupos[] = ['titulo' => 'Rutas', 'icono' => 'bi-compass', 'items' => $items];
            }

            // ── Visitantes (RRHH / Recepción / Admin) ─────────────────────────
            if (in_array($rol, [1, 2, 5], true)) {
                $db->query("SELECT vt.id,
                                   COALESCE(p.nombre, vt.nombre)     AS nombre,
                                   COALESCE(p.apellido, vt.apellido) AS apellido,
                                   COALESCE(p.cedula, vt.cedula)     AS cedula
                            FROM visitantes vt
                            LEFT JOIN personas p ON vt.id_persona = p.id
                            WHERE vt.is_active = TRUE
                              AND (COALESCE(p.cedula, vt.cedula) ILIKE :q
                                   OR (COALESCE(p.nombre, vt.nombre) || ' ' || COALESCE(p.apellido, vt.apellido)) ILIKE :q)
                            ORDER BY nombre ASC LIMIT 8");
                $db->bind(':q', $like);
                $items = [];
                foreach ($db->resultSet() as $r) {
                    $items[] = [
                        'texto' => trim(($r->nombre ?? '') . ' ' . ($r->apellido ?? '')),
                        'sub'   => $r->cedula ? 'C.I. ' . $r->cedula : 'Sin cédula',
                        'url'   => URL_ROOT . '/visitantes/index',
                    ];
                }
                $grupos[] = ['titulo' => 'Visitantes', 'icono' => 'bi-person-vcard', 'items' => $items];
            }
        }

        $this->view('buscar/index', ['titulo' => 'Búsqueda', 'q' => $q, 'grupos' => $grupos]);
    }
}
