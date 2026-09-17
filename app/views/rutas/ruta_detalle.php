<?php require_once '../app/views/inc/header.php';
/**
 * Ficha de un recorrido del CATÁLOGO (mig. 078): sus puntos y el histórico de
 * salidas. La gestión de participantes, asistencia e informe **no** está aquí:
 * es de cada salida, porque es gente distinta cada vez.
 */
$r      = $data['ruta'];
$puntos = $data['puntos'] ?? [];
$salidas = $data['salidas'] ?? [];
$fmt    = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '—';
?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Turismo · Catálogo</div>
        <h1 class="page__title"><?php echo htmlspecialchars($r->nombre); ?></h1>
        <p class="page__subtitle">
            <span class="sig-badge <?php echo Ruta::ESTADO_BADGES[$r->estado] ?? 'sig-badge--neutral'; ?>">
                <?php echo htmlspecialchars($r->estado); ?>
            </span>
            · <?php echo htmlspecialchars($r->tipo_ruta ?: 'General'); ?>
            <?php if (!empty($r->duracion_estimada)): ?> · <?php echo htmlspecialchars($r->duracion_estimada); ?><?php endif; ?>
        </p>
    </div>
    <div class="page__actions">
        <a href="<?php echo URL_ROOT; ?>/rutas/salidas?ruta=<?php echo (int)$r->id; ?>" class="btn-sig btn-sig--primary">
            <i class="bi bi-calendar-event"></i> Ver sus salidas
        </a>
        <a href="<?php echo URL_ROOT; ?>/rutas/index" class="btn-sig btn-sig--ghost"><i class="bi bi-arrow-left"></i> Catálogo</a>
    </div>
</div>

<?php if (!empty($r->descripcion)): ?>
<div class="sig-card anim-slide-up" style="margin-bottom:var(--sp-4);">
    <div class="sig-card__body" style="padding:var(--sp-4);">
        <?php echo nl2br(htmlspecialchars($r->descripcion)); ?>
    </div>
</div>
<?php endif; ?>

<div class="row g-4 anim-slide-up">
    <!-- Puntos del recorrido -->
    <div class="col-lg-7">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-pin-map"></i> Paradas del recorrido (<?php echo count($puntos); ?>)</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <?php if (empty($puntos)): ?>
                    <p style="color:var(--text-tertiary);margin:0;">
                        Este recorrido todavía no tiene paradas cargadas.
                    </p>
                <?php else: ?>
                    <ol style="margin:0;padding-left:var(--sp-5);">
                        <?php foreach ($puntos as $p): ?>
                        <li style="margin-bottom:var(--sp-3);">
                            <strong><?php echo htmlspecialchars($p->nombre); ?></strong>
                            <?php if (!empty($p->descripcion)): ?>
                                <div style="font-size:12px;color:var(--text-secondary);">
                                    <?php echo htmlspecialchars($p->descripcion); ?>
                                </div>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                    <p style="font-size:11px;color:var(--text-tertiary);margin-top:var(--sp-3);margin-bottom:0;">
                        El orden es el <strong>sugerido</strong>: el guía puede variarlo según convenga,
                        sobre todo si hay dos grupos a la vez en la misma ruta.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Histórico de salidas -->
    <div class="col-lg-5">
        <div class="sig-card h-100">
            <div class="sig-card__head">
                <div class="sig-card__title"><i class="bi bi-calendar-event"></i> Salidas (<?php echo count($salidas); ?>)</div>
            </div>
            <div class="sig-card__body" style="padding:var(--sp-4);">
                <?php if (empty($salidas)): ?>
                    <p style="color:var(--text-tertiary);margin:0;">Este recorrido no se ha ejecutado todavía.</p>
                <?php else: ?>
                    <div class="sig-table-wrap">
                        <table class="sig-table">
                            <thead><tr><th>Fecha</th><th>Estado</th><th class="text-center">Grupo</th><th></th></tr></thead>
                            <tbody>
                            <?php foreach ($salidas as $s): ?>
                                <tr>
                                    <td style="white-space:nowrap;"><?php echo $fmt($s->fecha); ?></td>
                                    <td>
                                        <span class="sig-badge <?php echo RutaEjecucion::ESTADO_BADGES[$s->estado] ?? 'sig-badge--neutral'; ?>">
                                            <?php echo htmlspecialchars($s->estado); ?>
                                        </span>
                                    </td>
                                    <td class="text-center"><?php echo (int)$s->total_participantes; ?></td>
                                    <td class="col-actions">
                                        <a href="<?php echo URL_ROOT; ?>/rutas/detalle/<?php echo $s->id; ?>" class="row-action">
                                            <i class="bi bi-folder2-open"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
