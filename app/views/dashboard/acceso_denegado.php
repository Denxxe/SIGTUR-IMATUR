<?php require_once '../app/views/inc/header.php'; ?>

<div class="row justify-content-center mt-5">
    <div class="col-md-6">
        <div class="card border-danger shadow-sm">
            <div class="card-body text-center py-5">
                <div class="display-1 text-danger mb-3">🚫</div>
                <h2 class="text-danger fw-bold"><?php echo $data['titulo']; ?></h2>
                <p class="text-muted mt-3"><?php echo $data['mensaje']; ?></p>
                <hr>
                <a href="<?php echo URL_ROOT; ?>/dashboard/index" class="btn btn-primary">
                    <i class="bi bi-house"></i> Volver al Panel Principal
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/inc/footer.php'; ?>
