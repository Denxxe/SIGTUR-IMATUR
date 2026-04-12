<?php
/**
 * Controlador AuditoriaController
 */
class AuditoriaController extends Controller {

    public function index() {
        $logs = AuditLog::all();

        $data = [
            'titulo' => 'Bitácora y Trazabilidad del Sistema',
            'logs' => $logs
        ];

        $this->view('auditoria/index', $data);
    }
}
