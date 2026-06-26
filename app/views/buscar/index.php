<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up">
    <div class="page__title-block">
        <div class="page__eyebrow">Búsqueda global</div>
        <h1 class="page__title">Resultados</h1>
        <?php if (trim($data['q'] ?? '') !== ''): ?>
            <p class="page__subtitle">Coincidencias para «<strong><?php echo htmlspecialchars($data['q']); ?></strong>»</p>
        <?php endif; ?>
    </div>
</div>

<form method="get" action="<?php echo URL_ROOT; ?>/buscar/index" class="anim-slide-up" style="margin-bottom:var(--sp-5);max-width:640px;">
    <div style="display:flex;gap:8px;">
        <input type="text" name="q" value="<?php echo htmlspecialchars($data['q'] ?? ''); ?>"
               class="form-control" placeholder="Empleado, bien, taller, ruta o visitante…" autofocus>
        <button type="submit" class="btn-sig btn-sig--primary"><i class="bi bi-search"></i> Buscar</button>
    </div>
</form>

<?php
$hayResultados = false;
foreach ($data['grupos'] ?? [] as $g) { if (!empty($g['items'])) { $hayResultados = true; break; } }
?>

<?php if (trim($data['q'] ?? '') === ''): ?>
    <div class="sig-card anim-slide-up"><div class="sig-card__body" style="text-align:center;padding:var(--sp-6);color:var(--text-tertiary);">
        <i class="bi bi-search" style="font-size:2rem;display:block;margin-bottom:var(--sp-2);"></i>
        Escribe al menos 2 caracteres para buscar en los módulos a los que tienes acceso.
    </div></div>
<?php elseif (!$hayResultados): ?>
    <div class="sig-card anim-slide-up"><div class="sig-card__body" style="text-align:center;padding:var(--sp-6);color:var(--text-tertiary);">
        <i class="bi bi-emoji-frown" style="font-size:2rem;display:block;margin-bottom:var(--sp-2);"></i>
        Sin coincidencias para «<?php echo htmlspecialchars($data['q']); ?>».
    </div></div>
<?php else: ?>
    <div class="row g-4 anim-slide-up">
        <?php foreach ($data['grupos'] as $g): if (empty($g['items'])) continue; ?>
        <div class="col-md-6">
            <div class="sig-card h-100">
                <div class="sig-card__head">
                    <div class="sig-card__title"><i class="bi <?php echo $g['icono']; ?>"></i> <?php echo htmlspecialchars($g['titulo']); ?> <span style="color:var(--text-tertiary);font-weight:500;">(<?php echo count($g['items']); ?>)</span></div>
                </div>
                <div class="sig-card__body" style="padding:0;">
                    <div class="sig-table-wrap">
                        <table class="sig-table">
                            <tbody>
                                <?php foreach ($g['items'] as $it): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $it['url']; ?>" style="text-decoration:none;color:inherit;display:block;">
                                            <span class="cell-strong"><?php echo htmlspecialchars($it['texto']); ?></span>
                                            <?php if (!empty($it['sub'])): ?>
                                                <div style="font-size:12px;color:var(--text-tertiary);"><?php echo htmlspecialchars($it['sub']); ?></div>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td style="width:36px;text-align:right;">
                                        <a href="<?php echo $it['url']; ?>" class="row-action row-action--view" title="Abrir"><i class="bi bi-box-arrow-up-right"></i> Abrir</a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php require_once '../app/views/inc/footer.php'; ?>
