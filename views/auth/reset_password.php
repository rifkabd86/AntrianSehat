<div class="auth-card-body p-4 p-md-5">
    <div class="mb-4 text-center text-md-start">
        <h3 class="fw-bold text-dark mb-1">Setel Ulang Password</h3>
        <p class="text-muted small">Buat password baru untuk akun Anda.</p>
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

    <form action="<?= BASE_URL ?>/reset-password?token=<?= htmlspecialchars($token ?? '', ENT_QUOTES, 'UTF-8') ?>" method="POST">
        <?= SecurityHelper::csrfField() ?>

        <div class="mb-3">
            <label for="password" class="form-label fw-semibold small">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required autofocus>
        </div>

        <div class="mb-4">
            <label for="password_confirm" class="form-label fw-semibold small">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" placeholder="Ulangi password baru" required>
        </div>

        <div class="d-grid">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                <i class="fas fa-check-circle me-2"></i> Simpan Password Baru
            </button>
        </div>
    </form>
</div>

