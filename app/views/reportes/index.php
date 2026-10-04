<?php
require_once '../app/views/inc/header.php';

// Catálogo de reportes agrupados por área. Cada reporte: [título, descripción, ruta, ícono, color, módulos?].
// Un reporte se ve si el rol tiene alguno de sus módulos (los suyos o, si no trae, los de la sección)
// en Roles y Permisos; la sección se oculta si no le queda ninguno. 'soloAdmin' no es delegable.
$secciones = [
    [
        'titulo' => 'Alertas y pendientes', 'icono' => 'bi-bell-fill', 'modulos' => CentroAlertas::MODULOS,
        'reportes' => [
            ['Centro de Alertas', 'Pendientes por atender: permisos, amonestaciones, expedientes incompletos.', 'reportes/alertas', 'bi-bell', '#D97706'],
        ],
    ],
    [
        'titulo' => 'Recursos Humanos', 'icono' => 'bi-people-fill', 'modulos' => ['EmpleadosController'],
        'reportes' => [
            ['Directorio de Personal', 'Plantilla activa con filtros por área, cargo, clasificación, contrato y origen.', 'reportes/directorio', 'bi-person-lines-fill', '#2563EB'],
            ['Reporte de Asistencia', 'Historial de asistencia del personal con filtros por fecha.', 'reportes/asistencia', 'bi-clipboard2-check', '#2563EB'],
            ['Permisos y Reposos', 'Permisos y reposos por tipo, estado y período, con duración.', 'reportes/permisos', 'bi-calendar2-week', '#0891B2'],
            ['Amonestaciones y Faltas', 'Conteo por empleado y semáforo; quién llega a causa de despido.', 'reportes/amonestaciones', 'bi-flag', '#DC2626'],
            ['Egresos y Rotación', 'Personal desincorporado por motivo y período (renuncias, despidos…).', 'reportes/egresos', 'bi-box-arrow-right', '#D97706'],
            ['Comisión de Servicio', 'Personal de Alcaldía o Gobernación, con su tiempo de servicio.', 'reportes/comisionServicio', 'bi-arrow-left-right', '#D97706'],
            ['Constancias Emitidas', 'Bitácora de constancias de trabajo generadas, con correlativo.', 'reportes/constancias', 'bi-file-earmark-text', '#0891B2'],
            ['Expedientes Incompletos', 'Personal con recaudos obligatorios faltantes en su expediente.', 'reportes/expedientesIncompletos', 'bi-folder-x', '#DC2626'],
            ['Carga Familiar', 'Carga familiar detallada por trabajador, con filtros (parentesco, sexo, edad, estado, N° de familiares).', 'reportes/cargaFamiliar', 'bi-people-fill', '#7C3AED'],
            ['Saldo de Vacaciones', 'Derecho acumulado, días disfrutados y saldo disponible por empleado.', 'reportes/vacacionesSaldo', 'bi-umbrella', '#0891B2'],
        ],
    ],
    [
        'titulo' => 'Recepción', 'icono' => 'bi-door-open-fill', 'modulos' => ['VisitantesController'],
        'reportes' => [
            ['Reporte de Visitantes', 'Registro de visitas institucionales por fecha y motivo.', 'reportes/visitantes', 'bi-person-vcard', '#7C3AED'],
            ['Estadísticas de Visitas', 'Afluencia por mes, visitantes únicos y situación del día.', 'reportes/estadisticasVisitas', 'bi-graph-up', '#7C3AED'],
        ],
    ],
    [
        'titulo' => 'Formación y Turismo', 'icono' => 'bi-mortarboard-fill', 'modulos' => ['TalleresController'],
        'reportes' => [
            ['Reporte de Talleres', 'Estadísticas de talleres, participantes e instructores.', 'reportes/talleres', 'bi-mortarboard', '#7C3AED'],
            ['Informe Trimestral', 'Consolidado de formación por trimestre: actividades, ejecución y personas atendidas.', 'reportes/formacionTrimestral', 'bi-calendar3-range', '#7C3AED'],
            ['Cobertura Comunitaria', 'Participaciones en formación por parroquia (alcance territorial).', 'reportes/coberturaFormacion', 'bi-geo-alt-fill', '#059669'],
            ['Reporte de Rutas', 'Estado de rutas turísticas y equipamiento asignado.', 'reportes/rutas', 'bi-map', '#0D9488', ['RutasController']],
            ['Participación en Rutas', 'Ocupación por ruta (participantes vs cupo) y estado.', 'reportes/participacionRutas', 'bi-people', '#0D9488', ['RutasController']],
            ['Ejecuciones de Ruta', 'Rutas efectivamente ejecutadas (Finalizadas) por fecha, con participantes y atendidos.', 'reportes/ejecucionesRuta', 'bi-flag-fill', '#0D9488', ['RutasController']],
            ['Reporte de Pasantes', 'Control de practicantes, tutores y documentos.', 'reportes/pasantes', 'bi-person-badge', '#2563EB', ['PasantesController']],
            ['Posibles duplicados', 'Detecta participantes repetidos para depurar registros basura.', 'reportes/duplicados', 'bi-people', '#DC2626', ['TalleresController', 'RutasController']],
        ],
    ],
    [
        'titulo' => 'Inventario', 'icono' => 'bi-box-seam-fill', 'modulos' => ['InventarioController'],
        'reportes' => [
            ['Reporte de Inventario', 'Control patrimonial de bienes por condición y categoría.', 'reportes/inventario', 'bi-box-seam', '#0D9488'],
            ['Kardex de Movimientos', 'Entradas, salidas y asignaciones de bienes por período.', 'reportes/kardex', 'bi-arrow-left-right', '#2563EB'],
            ['Bienes Asignados', 'Responsable actual de cada bien según el último movimiento.', 'reportes/bienesAsignados', 'bi-person-check', '#0891B2'],
            ['Bienes Desincorporados', 'Bienes dados de baja: motivo, fecha del acta y si la Alcaldía ya los retiró.', 'reportes/bajasInventario', 'bi-trash3', '#64748B'],
            ['Conteos de Inventario', 'Verificación física por cambio de gestión, con acta imprimible.', 'inventario/conteos', 'bi-clipboard-check', '#7C3AED'],
            ['Mantenimiento Preventivo', 'Calendario de mantenimiento de equipos y avisos de vencimiento.', 'inventario/planMantenimiento', 'bi-tools', '#D97706'],
            ['Etiquetas de Bienes', 'Hoja imprimible con código y QR para pegar en cada bien.', 'inventario/etiquetas', 'bi-upc-scan', '#0891B2'],
            ['Suficiencia de Bienes', 'Si alcanzan los bienes según el personal de cada departamento.', 'inventario/suficiencia', 'bi-clipboard-data', '#DB2777'],
        ],
    ],
    [
        'titulo' => 'Seguridad', 'icono' => 'bi-shield-lock-fill', 'soloAdmin' => true,
        'reportes' => [
            ['Auditoría del Sistema', 'Bitácora de cambios (quién, qué, cuándo) filtrable y exportable.', 'reportes/auditoria', 'bi-clipboard-data', '#64748B'],
            ['Accesos al Sistema', 'Inicios de sesión e intentos fallidos: quién entró, cuándo y desde qué IP.', 'reportes/accesos', 'bi-box-arrow-in-right', '#DC2626'],
        ],
    ],
    [
        'titulo' => 'Indicadores de Gestión', 'icono' => 'bi-graph-up-arrow', 'modulos' => ['EmpleadosController', 'TalleresController', 'RutasController', 'InventarioController'],
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
    <?php
    $visibles = array_filter($sec['reportes'], fn($r) => !empty($sec['soloAdmin'])
        ? sigtur_es_admin()
        : sigtur_puede(...($r[5] ?? $sec['modulos'])));
    if (!$visibles) continue;
    ?>
    <div class="rep-section-title"><i class="bi <?php echo $sec['icono']; ?>"></i> <?php echo htmlspecialchars($sec['titulo']); ?></div>
    <div class="rep-grid">
        <?php foreach ($visibles as [$titulo, $desc, $ruta, $icono, $color]): ?>
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
