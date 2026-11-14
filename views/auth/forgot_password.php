<div class="auth-card-body p-4 p-md-5">
    <div class="mb-4 text-center text-md-start">
        <a href="<?= BASE_URL ?>/login" class="text-decoration-none text-muted small mb-2 d-inline-block">
            <i class="fas fa-arrow-left me-1"></i> Kembali ke Login
        </a>
        <h3 class="fw-bold text-dark mb-1">Lupa Password</h3>
        <p class="text-muted small">Masukkan username atau email Anda untuk mendapatkan instruksi pemulihan kata sandi.</p>
    </div>

    <!-- Alert Flash -->
    <?php if (Helper::hasFlash()): ?>
        <?php $flash = Helper::getFlash(); ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas <?= $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> me-2"></i>
            <div><?= $flash['message'] ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/forgot-password" method="POST">
        <?= SecurityHelper::csrfField() ?>

        <div class="mb-4">
            <label for="identity" class="form-label fw-semibold text-secondary small">Username atau Email</label>
            <div class="input-group">
                <span class="input-group-text bg-light text-muted"><i class="fas fa-envelope"></i></span>
                <input type="text" class="form-control" id="identity" name="identity" placeholder="Masukkan username atau email" required autofocus>
            </div>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                <i class="fas fa-paper-plane me-2"></i> Kirim Permintaan Reset
            </button>
        </div>
    </form>
</div>

