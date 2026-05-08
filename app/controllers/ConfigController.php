<?php
class ConfigController extends Controller {

    private function requireRoles(array $roles) {
        $rol = (int)($_SESSION['user_rol'] ?? 0);
        if (!in_array($rol, $roles)) {
            flash('global_msg', 'No tienes permiso para acceder a esta sección.', 'danger');
            header('Location: ' . URL_ROOT . '/dashboard/index');
            exit;
        }
    }

    public function index() {
        $this->requireRoles([1, 2]);
        $config = ConfigSistema::getAll();
        $data = [
            'titulo' => 'Configuración Institucional',
            'config' => $config,
        ];
        $this->view('config/index', $data);
    }

    public function store() {
        $this->requireRoles([1, 2]);
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . URL_ROOT . '/config/index');
            exit;
        }

        $userId   = $this->getUserId();
        $updated  = 0;
        $config   = ConfigSistema::getAll();

        foreach ($config as $clave => $info) {
            if (isset($_POST[$clave])) {
                $valor = trim($_POST[$clave]);
                ConfigSistema::set($clave, $valor, $userId);
                $updated++;
            }
        }

        flash('global_msg', "Configuración actualizada ({$updated} valores guardados).");
        header('Location: ' . URL_ROOT . '/config/index');
        exit;
    }
}
