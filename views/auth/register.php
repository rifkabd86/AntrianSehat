<div class="auth-card-body p-4 p-md-5">
    <div class="mb-4 text-center text-md-start">
        <h3 class="fw-bold text-dark mb-1">Registrasi Pasien Baru</h3>
        <p class="text-muted small">Buat akun untuk mendapatkan nomor antrean dan memantau status secara online</p>
    </div>

    <!-- Alert Flash -->
    <?php if (Helper::hasFlash()): ?>
        <?php $flash = Helper::getFlash(); ?>
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fas <?= $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> me-2"></i>
            <div><?= htmlspecialchars($flash['message'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <form action="<?= BASE_URL ?>/register" method="POST" class="auth-form" novalidate>
        <?= SecurityHelper::csrfField() ?>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
            <i class="fas fa-id-card me-1"></i> Data Identitas Pasien
        </h6>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="nama_lengkap" class="form-label fw-semibold small">Nama Lengkap *</label>
                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" maxlength="100" value="<?= Helper::old('nama_lengkap') ?>" placeholder="Nama sesuai KTP" autocomplete="name" required>
            </div>
            <div class="col-md-6">
                <label for="nik" class="form-label fw-semibold small">NIK (16 Digit) *</label>
                <input type="text" class="form-control" id="nik" name="nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="<?= Helper::old('nik') ?>" placeholder="16 digit NIK" autocomplete="off" required>
            </div>

            <div class="col-md-6">
                <label for="tempat_lahir" class="form-label fw-semibold small">Tempat Lahir</label>
                <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" value="<?= Helper::old('tempat_lahir') ?>" placeholder="Kota kelahiran">
            </div>
            <div class="col-md-6">
                <label for="tanggal_lahir" class="form-label fw-semibold small">Tanggal Lahir *</label>
                <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" value="<?= Helper::old('tanggal_lahir') ?>" required>
            </div>

            <div class="col-md-6">
                <label for="jenis_kelamin" class="form-label fw-semibold small">Jenis Kelamin *</label>
                <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                    <option value="">Pilih Jenis Kelamin</option>
                    <option value="Laki-laki" <?= Helper::old('jenis_kelamin') === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                    <option value="Perempuan" <?= Helper::old('jenis_kelamin') === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                </select>
            </div>
            <div class="col-md-6">
                <label for="golongan_darah" class="form-label fw-semibold small">Golongan Darah</label>
                <select class="form-select" id="golongan_darah" name="golongan_darah">
                    <option value="-">-</option>
                    <option value="A" <?= Helper::old('golongan_darah') === 'A' ? 'selected' : '' ?>>A</option>
                    <option value="B" <?= Helper::old('golongan_darah') === 'B' ? 'selected' : '' ?>>B</option>
                    <option value="AB" <?= Helper::old('golongan_darah') === 'AB' ? 'selected' : '' ?>>AB</option>
                    <option value="O" <?= Helper::old('golongan_darah') === 'O' ? 'selected' : '' ?>>O</option>
                </select>
            </div>

            <div class="col-12">
                <label for="no_telepon" class="form-label fw-semibold small">Nomor HP / WhatsApp *</label>
                <input type="tel" class="form-control" id="no_telepon" name="no_telepon" maxlength="20" value="<?= Helper::old('no_telepon') ?>" placeholder="08xxxxxxxxxx" autocomplete="tel" required>
            </div>

            <div class="col-12">
                <label for="alamat" class="form-label fw-semibold small">Alamat Lengkap</label>
                <textarea class="form-control" id="alamat" name="alamat" rows="2" placeholder="Nama jalan, RT/RW, kelurahan"><?= Helper::old('alamat') ?></textarea>
            </div>
        </div>

        <h6 class="fw-bold text-primary border-bottom pb-2 mb-3 mt-4">
            <i class="fas fa-user-lock me-1"></i> Data Akun Login
        </h6>

        <div class="row g-3">
            <div class="col-md-6">
                <label for="email" class="form-label fw-semibold small">Email *</label>
                <input type="email" class="form-control" id="email" name="email" maxlength="100" value="<?= Helper::old('email') ?>" placeholder="email@contoh.com" autocomplete="email" required>
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label fw-semibold small">Password *</label>
                <input type="password" class="form-control" id="password" name="password" minlength="8" maxlength="72" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
            </div>
            <div class="col-md-6">
                <label for="password_confirm" class="form-label fw-semibold small">Konfirmasi Password *</label>
                <input type="password" class="form-control" id="password_confirm" name="password_confirm" minlength="8" maxlength="72" placeholder="Ulangi password" autocomplete="new-password" required>
            </div>
        </div>

        <div class="form-check mt-3">
            <input class="form-check-input" type="checkbox" value="1" id="terms" name="terms" required>
            <label class="form-check-label small" for="terms">Saya menyetujui ketentuan penggunaan dan pemrosesan data untuk layanan kesehatan.</label>
        </div>

        <div class="d-grid mt-4">
            <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm">
                <i class="fas fa-user-plus me-2"></i> Daftar Sebagai Pasien
            </button>
        </div>

        <div class="text-center mt-3 pt-2 border-top">
            <p class="text-muted small mb-0">
                Sudah memiliki akun? <a href="<?= BASE_URL ?>/login" class="text-primary fw-semibold text-decoration-none">Login Disini</a>
            </p>
        </div>
    </form>
</div>
