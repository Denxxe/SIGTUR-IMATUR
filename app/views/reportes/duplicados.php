<?php require_once '../app/views/inc/header.php';
$dupCedula  = $data['dupCedula']  ?? [];
$dupPersona = $data['dupPersona'] ?? [];
$dupLibre   = $data['dupLibre']   ?? [];
$ffecha = fn($f) => !empty($f) ? date('d/m/Y', strtotime($f)) : 's/f';
$totalGrupos = count($dupCedula) + count($dupPersona) + count($dupLibre);
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Análisis · Calidad de datos</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Detección de registros que podrían ser la misma persona, para depurar registros basura en Formación y Turismo.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<div class="sig-card anim-slide-up" style="margin-bottom:20px;border-left:4px solid var(--brand-500,#2563eb);">
    <div class="sig-card__body" style="font-size:13px;color:var(--text-secondary);">
        <strong>Cómo leer este reporte.</strong> El identificador único de una persona es la <strong>cédula</strong>.
        Los participantes <strong>sin cédula</strong> (niños/as de 5–11 años) no tienen una clave única, por lo que el
        sistema <em>no puede</em> decidir con certeza si dos registros son la misma persona: solo señala
        <strong>coincidencias</strong> (mismo nombre, apellido y fecha de nacimiento) para que un humano las revise.
        Para distinguir homónimos reales conviene apoyarse en datos adicionales (representante/docente, parroquia, género).
        <?php if ($totalGrupos === 0): ?>
            <div class="mt-2"><span class="sig-badge sig-badge--success"><i class="bi bi-check2-all"></i> No se detectaron posibles duplicados.</span></div>
        <?php endif; ?>
    </div>
</div>

<!-- 1) Cédulas duplicadas -->
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;"><h5 style="margin:0;"><i class="bi bi-fingerprint"></i> Personas con cédula repetida
        <span class="sig-badge sig-badge--<?php echo empty($dupCedula) ? 'success' : 'danger'; ?>"><?php echo count($dupCedula); ?></span></h5>
        <small style="color:var(--text-tertiary);">Misma cédula (normalizada) en más de un registro de personas. Casi siempre son duplicados a unificar.</small>
    </div>
    <table class="sig-table">
        <thead><tr><th>Cédula</th><th>N°</th><th>Registros</th></tr></thead>
        <tbody>
            <?php if (empty($dupCedula)): ?>
                <tr><td colspan="3" class="sig-table-empty">Sin coincidencias.</td></tr>
            <?php else: foreach ($dupCedula as $g): ?>
                <tr>
                    <td class="cell-strong"><?php echo htmlspecialchars($g->cedula_norm); ?></td>
                    <td><span class="sig-badge sig-badge--danger"><?php echo $g->total; ?></span></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($g->detalle); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- 2) Personas con mismo nombre + fecha de nacimiento -->
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;"><h5 style="margin:0;"><i class="bi bi-people"></i> Personas con mismo nombre y fecha de nacimiento
        <span class="sig-badge sig-badge--<?php echo empty($dupPersona) ? 'success' : 'warning'; ?>"><?php echo count($dupPersona); ?></span></h5>
        <small style="color:var(--text-tertiary);">Coinciden en nombre + apellido + fecha de nacimiento. Revisar: puede ser la misma persona (con/ sin cédula) o un homónimo real.</small>
    </div>
    <table class="sig-table">
        <thead><tr><th>F. Nacimiento</th><th>N°</th><th>Registros</th></tr></thead>
        <tbody>
            <?php if (empty($dupPersona)): ?>
                <tr><td colspan="3" class="sig-table-empty">Sin coincidencias.</td></tr>
            <?php else: foreach ($dupPersona as $g): ?>
                <tr>
                    <td><?php echo $ffecha($g->fnac); ?></td>
                    <td><span class="sig-badge sig-badge--warning"><?php echo $g->total; ?></span></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($g->detalle); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<!-- 3) Participantes sin cédula repetidos -->
<div class="sig-table-wrap anim-slide-up" style="margin-bottom:20px;">
    <div style="padding:12px 16px;"><h5 style="margin:0;"><i class="bi bi-person-badge"></i> Participantes sin cédula repetidos (Formación / Turismo)
        <span class="sig-badge sig-badge--<?php echo empty($dupLibre) ? 'success' : 'warning'; ?>"><?php echo count($dupLibre); ?></span></h5>
        <small style="color:var(--text-tertiary);">Mismo nombre + apellido + fecha de nacimiento <strong>y mismo representante</strong>, entre participantes libre de talleres y rutas. La cédula del representante distingue homónimos: misma persona en varias actividades.</small>
    </div>
    <table class="sig-table">
        <thead><tr><th>F. Nacimiento</th><th>C.I. Representante</th><th>N°</th><th>Apariciones (actividad)</th></tr></thead>
        <tbody>
            <?php if (empty($dupLibre)): ?>
                <tr><td colspan="4" class="sig-table-empty">Sin coincidencias.</td></tr>
            <?php else: foreach ($dupLibre as $g): ?>
                <tr>
                    <td><?php echo $ffecha($g->fnac); ?></td>
                    <td><?php echo !empty($g->ced_rep) ? htmlspecialchars($g->ced_rep) : '<span class="text-muted">—</span>'; ?></td>
                    <td><span class="sig-badge sig-badge--warning"><?php echo $g->total; ?></span></td>
                    <td style="font-size:13px;"><?php echo htmlspecialchars($g->detalle); ?></td>
                </tr>
            <?php endforeach; endif; ?>
        </tbody>
    </table>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
