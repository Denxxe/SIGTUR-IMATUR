<?php
/**
 * InstitucionesexternasController — Módulo de Instituciones Externas
 * Gestiona escuelas, liceos, comunidades y entes que participan en rutas y actividades.
 */
class InstitucionesexternasController extends Controller {

    private static array $TIPOS = ['Educativa', 'Comunitaria', 'Pública', 'Privada', 'ONG', 'Otra'];

    public function index() {
        try {
            $db = new Database();
            $db->query("SELECT ie.*,
                               (SELECT COUNT(*) FROM participantes_ruta pr
                                WHERE pr.id_institucion = ie.id AND pr.is_active = TRUE) as total_participantes_rutas,
                               (SELECT COUNT(DISTINCT pr.id_ruta) FROM participantes_ruta pr
                                WHERE pr.id_institucion = ie.id AND pr.is_active = TRUE) as total_rutas
                        FROM instituciones_externas ie
                        WHERE ie.is_active = TRUE
                        ORDER BY ie.nombre ASC");
            $instituciones = $db->resultSet();

            $data = [
                'titulo'        => 'Instituciones Externas',
                'instituciones' => $instituciones,
                'tipos'         => self::$TIPOS,
            ];
            $this->view('instituciones_externas/index', $data);
        } catch (Exception $e) {
            flash('global_msg', 'Error al cargar instituciones: ' . $e->getMessage(), 'danger');
            header('Location: ' . URL_ROOT . '/rutas/index');
        }
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;

        $_POST   = $this->sanitizePost();
        $userId  = $this->getUserId();
        $esEdicion = !empty($_POST['id']);

        $tipo = in_array($_POST['tipo'] ?? '', self::$TIPOS) ? $_POST['tipo'] : 'Educativa';
        $nombre = trim($_POST['nombre'] ?? '');

        if (empty($nombre)) {
            flash('global_msg', 'El nombre de la institución es obligatorio.', 'danger');
            header('Location: ' . URL_ROOT . '/institucionesexternas/index');
            return;
        }

        try {
            $db = new Database();
            if ($esEdicion) {
                $id = (int)$_POST['id'];
                $db->query("UPDATE instituciones_externas
                            SET nombre=:nombre, tipo=:tipo, es_educativa=:es_edu,
                                municipio=:municipio, contacto=:contacto, telefono=:telefono,
                                updated_at=CURRENT_TIMESTAMP, updated_by=:uid
                            WHERE id=:id AND is_active=TRUE");
                $db->bind(':id', $id);
                flash('global_msg', 'Institución actualizada correctamente.');
            } else {
                $db->query("INSERT INTO instituciones_externas
                            (nombre, tipo, es_educativa, municipio, contacto, telefono, created_by)
                            VALUES (:nombre, :tipo, :es_edu, :municipio, :contacto, :telefono, :uid)");
                flash('global_msg', 'Institución registrada exitosamente.');
            }
            $db->bind(':nombre',    $nombre);
            $db->bind(':tipo',      $tipo);
            $db->bind(':es_edu',    ($tipo === 'Educativa') ? 'TRUE' : (!empty($_POST['es_educativa']) ? 'TRUE' : 'FALSE'));
            $db->bind(':municipio', trim($_POST['municipio'] ?? '') ?: null);
            $db->bind(':contacto',  trim($_POST['contacto']  ?? '') ?: null);
            $db->bind(':telefono',  trim($_POST['telefono']  ?? '') ?: null);
            $db->bind(':uid',       $userId);
            $db->execute();

            AuditLog::log('instituciones_externas', $esEdicion ? 'UPDATE' : 'INSERT', $esEdicion ? (int)$_POST['id'] : null, null, ['nombre' => $nombre, 'tipo' => $tipo], $userId);
        } catch (Exception $e) {
            flash('global_msg', 'Error al guardar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/institucionesexternas/index');
    }

    public function delete($id) {
        $userId = $this->getUserId();
        try {
            $db = new Database();
            $db->query("UPDATE instituciones_externas
                        SET is_active=FALSE, deleted_at=CURRENT_TIMESTAMP, deleted_by=:uid
                        WHERE id=:id");
            $db->bind(':id',  (int)$id);
            $db->bind(':uid', $userId);
            $db->execute();
            AuditLog::log('instituciones_externas', 'DELETE', (int)$id, null, null, $userId);
            flash('global_msg', 'Institución eliminada.');
        } catch (Exception $e) {
            flash('global_msg', 'Error al eliminar: ' . $e->getMessage(), 'danger');
        }
        header('Location: ' . URL_ROOT . '/institucionesexternas/index');
    }

    public function listarJson() {
        header('Content-Type: application/json');
        try {
            $db = new Database();
            $db->query("SELECT id, nombre, tipo, municipio FROM instituciones_externas WHERE is_active = TRUE ORDER BY nombre ASC");
            echo json_encode($db->resultSet());
        } catch (Exception $e) {
            echo json_encode([]);
        }
        exit;
    }
}
