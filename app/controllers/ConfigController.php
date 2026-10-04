<?php
class ConfigController extends Controller {


    public function index() {
        $this->requireModulo('ConfigController');
        try {
            $config = ConfigSistema::getAll();
        } catch (Exception $e) {
            $config = [];
            flash('global_msg', 'Error al cargar configuración: ' . $e->getMessage(), 'danger');
        }
        $data = [
            'titulo' => 'Configuración Institucional',
            'config' => $config,
        ];
        $this->view('config/index', $data);
    }

    public function store() {
        $this->requireModulo('ConfigController');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/config/index');
            exit;
        }

        $userId  = $this->getUserId();
        $updated = 0;

        try {
            $config = ConfigSistema::getAll();
            foreach ($config as $clave => $info) {
                if (isset($_POST[$clave])) {
                    $valor = trim($_POST[$clave]);
                    ConfigSistema::set($clave, $valor, $userId);
                    $updated++;
                }
            }
            flash('global_msg', "Configuración actualizada ({$updated} valores guardados).");
        } catch (Exception $e) {
            flash('global_msg', 'Error al guardar configuración: ' . $e->getMessage(), 'danger');
        }

        header('Location: ' . URL_ROOT . '/config/index');
        exit;
    }
}
