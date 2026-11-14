<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3 text-dark">
                <i class="fas fa-lock text-primary me-2"></i> Ubah Password
            </h5>
            <p class="text-muted small mb-4">Gunakan password baru minimal 8 karakter dan jangan gunakan password yang sama dengan layanan lain.</p>

            <form action="<?= BASE_URL ?>/change-password" method="POST">
                <?= SecurityHelper::csrfField() ?>

                <div class="mb-3">
                    <label for="current_password" class="form-label fw-semibold small">Password Saat Ini *</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="current_password" name="current_password" autocomplete="current-password" required autofocus>
                        <button class="btn btn-light border" type="button" data-password-toggle="current_password" aria-label="Tampilkan password saat ini"><i class="fas fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="new_password" class="form-label fw-semibold small">Password Baru *</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="new_password" name="new_password" minlength="8" maxlength="72" placeholder="Minimal 8 karakter" autocomplete="new-password" required>
                        <button class="btn btn-light border" type="button" data-password-toggle="new_password" aria-label="Tampilkan password baru"><i class="fas fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirm_password" class="form-label fw-semibold small">Konfirmasi Password Baru *</label>
                    <div class="input-group">
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" minlength="8" maxlength="72" placeholder="Ulangi password baru" autocomplete="new-password" required>
                        <button class="btn btn-light border" type="button" data-password-toggle="confirm_password" aria-label="Tampilkan konfirmasi password"><i class="fas fa-eye" aria-hidden="true"></i></button>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Perbarui Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
