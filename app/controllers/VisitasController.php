<?php
class VisitasController extends Controller {

    public function __construct() {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . URL_ROOT . '/auth/login');
            exit;
        }
    }

    public function index() {
        header('Location: ' . URL_ROOT . '/visitantes/index');
        exit;
    }
}
