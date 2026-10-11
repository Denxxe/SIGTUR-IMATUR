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
    <div class="u-gap-8px" style="display:flex;">
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
    <div class="sig-card anim-slide-up"><div class="sig-card__body u-ta-center u-p-sp-6 u-c-text-tertiary">
        <i class="bi bi-search u-fs-2rem u-mb-sp-2" style="display:block;"></i>
        Escribe al menos 2 caracteres para buscar en los módulos a los que tienes acceso.
    </div></div>
<?php elseif (!$hayResultados): ?>
    <div class="sig-card anim-slide-up"><div class="sig-card__body u-ta-center u-p-sp-6 u-c-text-tertiary">
        <i class="bi bi-emoji-frown u-fs-2rem u-mb-sp-2" style="display:block;"></i>
        Sin coincidencias para «<?php echo htmlspecialchars($data['q']); ?>».
    </div></div>
<?php else: ?>
    <div class="row g-4 anim-slide-up">
        <?php foreach ($data['grupos'] as $g): if (empty($g['items'])) continue; ?>
        <div class="col-md-6">
            <div class="sig-card h-100">
                <div class="sig-card__head">
                    <div class="sig-card__title"><i class="bi <?php echo $g['icono']; ?>"></i> <?php echo htmlspecialchars($g['titulo']); ?> <span class="u-c-text-tertiary u-fw-500">(<?php echo count($g['items']); ?>)</span></div>
                </div>
                <div class="sig-card__body u-p-0">
                    <div class="sig-table-wrap">
                        <table class="sig-table">
                            <tbody>
                                <?php foreach ($g['items'] as $it): ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo $it['url']; ?>" style="text-decoration:none;color:inherit;display:block;">
                                            <span class="cell-strong"><?php echo htmlspecialchars($it['texto']); ?></span>
                                            <?php if (!empty($it['sub'])): ?>
                                                <div class="u-fs-12px u-c-text-tertiary"><?php echo htmlspecialchars($it['sub']); ?></div>
                                            <?php endif; ?>
                                        </a>
                                    </td>
                                    <td class="u-ta-right" style="width:36px;">
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
