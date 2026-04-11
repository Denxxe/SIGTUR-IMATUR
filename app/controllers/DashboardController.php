<?php
/**
 * DashboardController — Página de inicio del sistema
 */
class DashboardController extends Controller {

    public function index() {
        // Indicadores rápidos para el panel principal
        $db = new Database();

        // Total Empleados activos
        $db->query("SELECT COUNT(*) as total FROM empleados WHERE is_active = TRUE");
        $totalEmpleados = $db->single()->total ?? 0;

        // Total Items Inventario
        $db->query("SELECT COUNT(*) as total FROM inventario WHERE is_active = TRUE");
        $totalInventario = $db->single()->total ?? 0;

        // Total Talleres programados
        $db->query("SELECT COUNT(*) as total FROM talleres WHERE is_active = TRUE AND estado IN ('Programado','En Curso')");
        $totalTalleres = $db->single()->total ?? 0;

        // Total Rutas activas
        $db->query("SELECT COUNT(*) as total FROM rutas WHERE is_active = TRUE AND estado = 'Activa'");
        $totalRutas = $db->single()->total ?? 0;

        $data = [
            'titulo' => 'Panel Principal',
            'totalEmpleados' => $totalEmpleados,
            'totalInventario' => $totalInventario,
            'totalTalleres' => $totalTalleres,
            'totalRutas' => $totalRutas
        ];

        $this->view('dashboard/index', $data);
    }
}
