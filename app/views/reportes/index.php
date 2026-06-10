<?php
require_once '../app/views/inc/header.php';
$rol = (int)($_SESSION['user_rol'] ?? 0);

// Catálogo de reportes agrupados por área. Cada reporte: [título, descripción, ruta, ícono, color]
$secciones = [
    [
        'titulo' => 'Recursos Humanos', 'icono' => 'bi-people-fill', 'roles' => [1, 2],
        'reportes' => [
            ['Reporte de Asistencia', 'Historial de asistencia del personal con filtros por fecha.', 'reportes/asistencia', 'bi-clipboard2-check', '#2563EB'],
            ['Reporte de Visitantes', 'Registro de visitas institucionales por fecha y motivo.', 'reportes/visitantes', 'bi-person-vcard', '#7C3AED'],
            ['Permisos y Reposos', 'Permisos y reposos por tipo, estado y período, con duración.', 'reportes/permisos', 'bi-calendar2-week', '#0891B2'],
            ['Comisión de Servicio', 'Personal de Alcaldía o Gobernación, con su tiempo de servicio.', 'reportes/comisionServicio', 'bi-arrow-left-right', '#D97706'],
        ],
    ],
    [
        'titulo' => 'Formación y Turismo', 'icono' => 'bi-mortarboard-fill', 'roles' => [1, 3],
        'reportes' => [
            ['Reporte de Talleres', 'Estadísticas de talleres, participantes e instructores.', 'reportes/talleres', 'bi-mortarboard', '#7C3AED'],
            ['Reporte de Rutas', 'Estado de rutas turísticas y equipamiento asignado.', 'reportes/rutas', 'bi-map', '#0D9488'],
            ['Reporte de Pasantes', 'Control de practicantes, tutores y documentos.', 'reportes/pasantes', 'bi-person-badge', '#2563EB'],
            ['Posibles duplicados', 'Detecta participantes repetidos para depurar registros basura.', 'reportes/duplicados', 'bi-people', '#DC2626'],
        ],
    ],
    [
        'titulo' => 'Inventario', 'icono' => 'bi-box-seam-fill', 'roles' => [1, 4],
        'reportes' => [
            ['Reporte de Inventario', 'Control patrimonial de bienes por condición y categoría.', 'reportes/inventario', 'bi-box-seam', '#0D9488'],
            ['Bienes Dados de Baja', 'Historial de bienes desincorporados del inventario activo.', 'reportes/bajasInventario', 'bi-trash3', '#64748B'],
        ],
    ],
    [
        'titulo' => 'Indicadores de Gestión', 'icono' => 'bi-graph-up-arrow', 'roles' => [1, 2, 3, 4],
        'reportes' => [
            ['Indicadores de Gestión', 'KPIs globales: personal, formación, turismo e inventario, con tendencias.', 'reportes/indicadores', 'bi-bar-chart-line', '#059669'],
        ],
    ],
];
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Análisis · Reportes</div>
        <h1 class="page__title"><?php echo $data['titulo'] ?? 'Centro de Reportes e Indicadores'; ?></h1>
        <p class="page__subtitle">Elige un reporte para generarlo, filtrarlo y exportarlo.</p>
    </div>
</div>

<div class="anim-slide-up">
<?php foreach ($secciones as $sec): ?>
    <?php if (!array_intersect([$rol], $sec['roles'])) continue; ?>
    <div class="rep-section-title"><i class="bi <?php echo $sec['icono']; ?>"></i> <?php echo htmlspecialchars($sec['titulo']); ?></div>
    <div class="rep-grid">
        <?php foreach ($sec['reportes'] as [$titulo, $desc, $ruta, $icono, $color]): ?>
            <a href="<?php echo URL_ROOT; ?>/<?php echo $ruta; ?>" class="rep-card">
                <span class="rep-card__icon" style="color:<?php echo $color; ?>;background:<?php echo $color; ?>1f;">
                    <i class="bi <?php echo $icono; ?>"></i>
                </span>
                <span class="rep-card__body">
                    <span class="rep-card__title"><?php echo htmlspecialchars($titulo); ?></span>
                    <span class="rep-card__desc"><?php echo htmlspecialchars($desc); ?></span>
                </span>
                <i class="bi bi-arrow-right rep-card__arrow"></i>
            </a>
        <?php endforeach; ?>
    </div>
<?php endforeach; ?>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
