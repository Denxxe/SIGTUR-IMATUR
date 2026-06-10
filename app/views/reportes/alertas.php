<?php require_once '../app/views/inc/header.php';
$alertas = $data['alertas'] ?? [];
$colores = [
    'danger'  => '#DC2626',
    'warning' => '#D97706',
    'info'    => '#2563EB',
];
$totalPend = array_sum(array_map(fn($a) => (int)$a['n'], $alertas));
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">RRHH · Operación</div>
        <h1 class="page__title"><?php echo $data['titulo']; ?></h1>
        <p class="page__subtitle">Pendientes que requieren atención, consolidados en un solo lugar.</p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/reportes/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Volver</a>
    </div>
</div>

<?php if ($totalPend === 0): ?>
<div class="sig-card anim-slide-up" style="border-left:4px solid var(--success-500,#059669);">
    <div class="sig-card__body" style="display:flex;align-items:center;gap:14px;">
        <i class="bi bi-check2-circle" style="font-size:1.8rem;color:var(--success-600,#059669);"></i>
        <div><strong>Todo al día.</strong> No hay pendientes que atender en este momento.</div>
    </div>
</div>
<?php endif; ?>

<div class="rep-grid anim-slide-up">
    <?php foreach ($alertas as $a):
        $n = (int)$a['n'];
        $ok = ($n === 0);
        $color = $ok ? '#059669' : ($colores[$a['sev']] ?? '#64748B');
    ?>
        <a href="<?php echo $a['url']; ?>" class="rep-card" style="border-left:4px solid <?php echo $color; ?>;">
            <span class="rep-card__icon" style="color:<?php echo $color; ?>;background:<?php echo $color; ?>1f;">
                <i class="bi <?php echo $ok ? 'bi-check2' : $a['icono']; ?>"></i>
            </span>
            <span class="rep-card__body">
                <span class="rep-card__title" style="display:flex;align-items:center;gap:8px;">
                    <?php echo htmlspecialchars($a['titulo']); ?>
                    <span class="sig-badge <?php echo $ok ? 'sig-badge--success' : ('sig-badge--' . $a['sev']); ?>"><?php echo $n; ?></span>
                </span>
                <span class="rep-card__desc"><?php echo htmlspecialchars($a['desc']); ?></span>
            </span>
            <i class="bi bi-arrow-right rep-card__arrow"></i>
        </a>
    <?php endforeach; ?>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
