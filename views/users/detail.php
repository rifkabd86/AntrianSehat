<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div class="d-flex align-items-center gap-3">
        <a class="btn btn-light border" href="<?= BASE_URL ?>/users" aria-label="Kembali ke daftar pengguna"><i class="fas fa-arrow-left"></i></a>
        <div>
            <h4 class="fw-bold text-dark mb-1">Detail Pengguna</h4>
            <p class="text-muted small mb-0">Akun #<?= (int) $user->id ?> · terdaftar <?= SecurityHelper::escape(Helper::formatTanggal($user->created_at, false)) ?></p>
        </div>
    </div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/users/edit/<?= (int) $user->id ?>"><i class="fas fa-pen me-2"></i>Edit Pengguna</a>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <section class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Informasi Akun</h5>
                <dl class="row mb-0">
                    <dt class="col-sm-4 text-muted fw-medium">ID pengguna</dt><dd class="col-sm-8"><?= (int) $user->id ?></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Nama lengkap</dt><dd class="col-sm-8"><?= SecurityHelper::escape($user->nama_lengkap) ?></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Username</dt><dd class="col-sm-8"><?= SecurityHelper::escape($user->username) ?></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Email</dt><dd class="col-sm-8"><?= SecurityHelper::escape($user->email ?? '-') ?></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Nomor telepon</dt><dd class="col-sm-8"><?= SecurityHelper::escape($user->no_telepon ?? '-') ?></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Role</dt><dd class="col-sm-8"><?= SecurityHelper::escape(Helper::getRoleLabel($user->role)) ?></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Status akun</dt>
                    <dd class="col-sm-8"><span class="badge <?= (int) $user->is_active === 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= (int) $user->is_active === 1 ? 'Aktif' : 'Nonaktif' ?></span></dd>
                    <dt class="col-sm-4 text-muted fw-medium">Login terakhir</dt><dd class="col-sm-8"><?= $user->last_login ? SecurityHelper::escape(Helper::formatTanggal($user->last_login)) : 'Belum pernah login' ?></dd>
                </dl>
                <?php if ($patient): ?>
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Profil Pasien</h6>
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted fw-medium">Nomor rekam medis</dt><dd class="col-sm-8"><?= SecurityHelper::escape($patient->no_rekam_medis) ?></dd>
                        <dt class="col-sm-4 text-muted fw-medium">NIK</dt><dd class="col-sm-8"><?= SecurityHelper::escape($patient->nik) ?></dd>
                    </dl>
                <?php endif; ?>
            </div>
        </section>
    </div>

    <div class="col-lg-5">
        <section class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2">Status Akses</h5>
                <p class="small text-muted">Akun nonaktif tidak dapat login. Data historis tetap dipertahankan.</p>
                <?php if ((int) $user->id === (int) $_SESSION['user_id']): ?>
                    <div class="alert alert-info mb-0">Anda tidak dapat menonaktifkan akun administrator yang sedang digunakan.</div>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/users/status/<?= (int) $user->id ?>" method="POST" onsubmit="return confirm('Yakin ingin mengubah status akun ini?')">
                        <?= SecurityHelper::csrfField() ?>
                        <input type="hidden" name="is_active" value="<?= (int) $user->is_active === 1 ? '0' : '1' ?>">
                        <button class="btn <?= (int) $user->is_active === 1 ? 'btn-outline-danger' : 'btn-outline-success' ?>" type="submit">
                            <i class="fas <?= (int) $user->is_active === 1 ? 'fa-user-slash' : 'fa-user-check' ?> me-2"></i>
                            <?= (int) $user->is_active === 1 ? 'Nonaktifkan Akun' : 'Aktifkan Akun' ?>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </section>

        <section class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-2">Reset Password</h5>
                <p class="small text-muted">Buat password sementara minimal 8 karakter dan sampaikan melalui jalur yang aman. Password tidak dicatat di audit log.</p>
                <?php if ((int) $user->id === (int) $_SESSION['user_id']): ?>
                    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/change-password">Ubah password sendiri</a>
                <?php else: ?>
                    <form action="<?= BASE_URL ?>/users/reset-password/<?= (int) $user->id ?>" method="POST">
                        <?= SecurityHelper::csrfField() ?>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="reset_password">Password baru</label>
                            <input class="form-control" id="reset_password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold" for="reset_password_confirm">Konfirmasi password</label>
                            <input class="form-control" id="reset_password_confirm" name="password_confirm" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                        </div>
                        <button class="btn btn-outline-primary" type="submit" onclick="return confirm('Reset password akun ini?')"><i class="fas fa-key me-2"></i>Reset Password</button>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </div>
</div>
