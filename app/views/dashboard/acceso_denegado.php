<?php require_once '../app/views/inc/header.php'; ?>

<div class="page__head anim-slide-up" style="justify-content:center; text-align:center; padding-top:var(--sp-12);">
    <div class="page__title-block" style="align-items:center;">
        <div class="display-1" style="font-size:80px; margin-bottom:var(--sp-4);">🚫</div>
        <h1 class="page__title text-danger" style="font-size:32px;"><?php echo $data['titulo'] ?? 'Acceso Restringido'; ?></h1>
        <p class="page__subtitle" style="max-width:500px; margin:0 auto;"><?php echo $data['mensaje'] ?? 'No posee los permisos necesarios para visualizar este módulo. Si cree que esto es un error, contacte al administrador del sistema.'; ?></p>
        
        <div style="margin-top:var(--sp-8);">
            <a href="<?php echo URL_ROOT; ?>/dashboard/index" class="btn-sig btn-sig--primary" style="background:var(--brand-600);">
                <i class="bi bi-house"></i> Volver al Panel Principal
            </a>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
