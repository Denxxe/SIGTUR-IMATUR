<?php
/**
 * DashboardController — Página de inicio con KPIs y gráficas
 */
class DashboardController extends Controller {

    public function index() {
        $db = new Database();

        // KPIs básicos
        $db->query("SELECT COUNT(*) as total FROM empleados WHERE is_active = TRUE");
        $totalEmpleados = $db->single()->total ?? 0;

        $db->query("SELECT COUNT(*) as total FROM inventario WHERE is_active = TRUE");
        $totalInventario = $db->single()->total ?? 0;

        $db->query("SELECT COUNT(*) as total FROM talleres WHERE is_active = TRUE AND estado IN ('Programado','En Curso')");
        $totalTalleres = $db->single()->total ?? 0;

        $db->query("SELECT COUNT(*) as total FROM rutas WHERE is_active = TRUE AND estado = 'Activa'");
        $totalRutas = $db->single()->total ?? 0;

        // Gráfica: Empleados por Departamento
        $db->query("SELECT d.nombre as label, COUNT(e.id) as value
                    FROM departamentos d
                    LEFT JOIN empleados e ON d.id = e.id_departamento AND e.is_active = TRUE
                    WHERE d.is_active = TRUE
                    GROUP BY d.nombre ORDER BY value DESC LIMIT 10");
        $chartEmpleados = $db->resultSet();

        // Gráfica: Inventario por Condición
        $db->query("SELECT condicion as label, COUNT(*) as value 
                    FROM inventario WHERE is_active = TRUE 
                    GROUP BY condicion ORDER BY value DESC");
        $chartInventario = $db->resultSet();

        // Gráfica: Talleres por Estado
        $db->query("SELECT estado as label, COUNT(*) as value 
                    FROM talleres WHERE is_active = TRUE 
                    GROUP BY estado ORDER BY value DESC");
        $chartTalleres = $db->resultSet();

        // Gráfica: Actividad últimos 7 días (asistencias registradas)
        $db->query("SELECT fecha::text as label, COUNT(*) as value
                    FROM asistencias
                    WHERE is_active = TRUE AND fecha >= (CURRENT_DATE - INTERVAL '7 days')
                    GROUP BY fecha ORDER BY fecha ASC");
        $chartAsistencia = $db->resultSet();

        // KPI: Formación completada (talleres finalizados este año)
        $db->query("SELECT COUNT(*) as total FROM talleres WHERE is_active = TRUE AND estado = 'Finalizado' AND EXTRACT(YEAR FROM fecha_inicio) = EXTRACT(YEAR FROM CURRENT_DATE)");
        $formacionCompletada = $db->single()->total ?? 0;

        // KPI: Logs del sistema hoy
        $db->query("SELECT COUNT(*) as total FROM audit_logs WHERE DATE(fecha) = CURRENT_DATE");
        $logsHoy = $db->single()->total ?? 0;

        // Alertas: contratos por vencer en 30 días
        $db->query("SELECT p.nombre, p.apellido, e.fecha_egreso
                    FROM empleados e
                    INNER JOIN personas p ON e.id_persona = p.id
                    WHERE e.is_active = TRUE
                      AND e.tipo_contrato = 'Contratado'
                      AND e.fecha_egreso IS NOT NULL
                      AND e.fecha_egreso BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '30 days')
                    ORDER BY e.fecha_egreso ASC LIMIT 10");
        $alertasContratos = $db->resultSet();

        // Alertas: talleres en curso hoy
        $db->query("SELECT nombre, fecha_inicio FROM talleres WHERE is_active = TRUE AND estado = 'En Curso' LIMIT 5");
        $talleresEnCurso = $db->resultSet();

        // Alertas: pasantes próximos a culminar (fecha_fin en 15 días)
        $db->query("SELECT pp.nombre, pp.apellido, pa.fecha_fin, pa.institucion
                    FROM pasantes pa
                    INNER JOIN personas pp ON pa.id_persona = pp.id
                    WHERE pa.is_active = TRUE AND pa.estado = 'En Curso'
                      AND pa.fecha_fin IS NOT NULL
                      AND pa.fecha_fin BETWEEN CURRENT_DATE AND (CURRENT_DATE + INTERVAL '15 days')
                    ORDER BY pa.fecha_fin ASC LIMIT 10");
        $alertasPasantes = $db->resultSet();

        $data = [
            'titulo' => 'Panel Principal',
            'totalEmpleados' => $totalEmpleados,
            'totalInventario' => $totalInventario,
            'totalTalleres' => $totalTalleres,
            'totalRutas' => $totalRutas,
            'chartEmpleados' => $chartEmpleados,
            'chartInventario' => $chartInventario,
            'chartTalleres' => $chartTalleres,
            'chartAsistencia' => $chartAsistencia,
            'formacionCompletada' => $formacionCompletada,
            'logsHoy'            => $logsHoy,
            'alertasContratos'   => $alertasContratos,
            'talleresEnCurso'    => $talleresEnCurso,
            'alertasPasantes'    => $alertasPasantes,
        ];

        $this->view('dashboard/index', $data);
    }

    public function accesoDenegado() {
        $data = [
            'titulo' => 'Acceso Denegado',
            'mensaje' => 'No tienes permisos suficientes para acceder a este módulo. Contacta al administrador del sistema.'
        ];
        $this->view('dashboard/acceso_denegado', $data);
    }
}
