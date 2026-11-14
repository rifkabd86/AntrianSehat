<div class="auth-card-body p-4 p-md-5">
    <div class="mb-4 text-center text-md-start">
        <h3 class="fw-bold text-dark mb-1">Selamat Datang di <span class="text-primary">ANTRIANSEHAT</span></h3>
        <p class="text-muted small">Silakan login untuk melanjutkan ke sistem antrean pelayanan</p>
    </div>

    <!-- Alert Flash Standar & Fallback Session -->
    <?php
    $flash = null;
    if (class_exists('Helper') && Helper::hasFlash()) {
        $flash = Helper::getFlash();
    } elseif (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
    } elseif (!empty($_SESSION['debug_login_error'])) {
        $flash = ['type' => 'error', 'message' => $_SESSION['debug_login_error']];
        unset($_SESSION['debug_login_error']);
    }
    ?>
    <?php if (!empty($flash) && is_array($flash)): ?>
        <?php
        $flashType = $flash['type'] ?? 'info';
        $alertClass = ($flashType === 'error' || $flashType === 'danger') ? 'danger' : $flashType;
        $flashMsg = $flash['message'] ?? '';
        ?>
        <?php if ($flashMsg !== ''): ?>
            <div class="alert alert-<?= htmlspecialchars($alertClass, ENT_QUOTES, 'UTF-8') ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                <i class="fas <?= $alertClass === 'danger' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> me-2"></i>
                <div><?= htmlspecialchars((string)$flashMsg, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- Form Login menggunakan BASE_URL agar mengarah tepat ke domain utama -->
    <form action="<?= rtrim(BASE_URL, '/') ?>/login" method="POST" class="auth-form" novalidate>
        <?= CsrfMiddleware::getTokenField() ?>

        <div class="mb-3">
            <label for="username" class="form-label fw-semibold text-secondary small">Email atau username</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-user"></i></span>
                <input type="text" class="form-control bg-light border-start-0 ps-0" id="username" name="username" value="<?= htmlspecialchars((string)Helper::old('username'), ENT_QUOTES, 'UTF-8') ?>" placeholder="nama@email.com atau username" autocomplete="username" required autofocus>
            </div>
        </div>

        <div class="mb-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label for="password" class="form-label fw-semibold text-secondary small mb-0">Password</label>
                <a href="<?= rtrim(BASE_URL, '/') ?>/forgot-password" class="text-primary small text-decoration-none">Lupa password?</a>
            </div>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock"></i></span>
                <input type="password" class="form-control bg-light border-start-0 border-end-0 ps-0" id="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                <button class="btn btn-light border border-start-0 text-muted toggle-password" type="button" onclick="togglePasswordVisibility('password', this)" aria-label="Tampilkan password">
                    <i class="fas fa-eye"></i>
                </button>
            </div>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                <i class="fas fa-right-to-bracket me-2"></i> Login
            </button>
        </div>

        <div class="text-center mt-4 pt-2 border-top">
            <p class="text-muted small mb-0">
                Belum punya akun? <a href="<?= rtrim(BASE_URL, '/') ?>/register" class="text-primary fw-semibold text-decoration-none">Daftar Pasien Baru</a>
            </p>
        </div>
    </form>
</div>

<script>
function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}
</script>