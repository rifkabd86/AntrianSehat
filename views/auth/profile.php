<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 text-center p-4">
            <div class="avatar-lg mx-auto mb-3 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 80px; height: 80px;">
                <?= mb_strtoupper(mb_substr($user->nama_lengkap ?? 'U', 0, 1)) ?>
            </div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($user->nama_lengkap ?? '', ENT_QUOTES, 'UTF-8') ?></h5>
            <p class="text-muted small mb-2">@<?= htmlspecialchars($user->username ?? '', ENT_QUOTES, 'UTF-8') ?></p>
            <div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                    <?= Helper::getRoleLabel($user->role ?? '') ?>
                </span>
                <span class="badge <?= (int) ($user->is_active ?? 0) === 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?> border px-3 py-2 rounded-pill">
                    <?= (int) ($user->is_active ?? 0) === 1 ? 'Aktif' : 'Nonaktif' ?>
                </span>
            </div>
            
            <?php if (!empty($pasien)): ?>
                <hr class="my-4">
                <div class="text-start">
                    <div class="mb-2">
                        <small class="text-muted d-block">Nomor Rekam Medis</small>
                        <span class="fw-bold text-primary fs-5"><?= htmlspecialchars($pasien->no_rekam_medis ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">NIK</small>
                        <span class="fw-medium"><?= htmlspecialchars($pasien->nik ?? '', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                    <div class="mb-2">
                        <small class="text-muted d-block">Golongan Darah</small>
                        <span class="badge bg-danger"><?= htmlspecialchars($pasien->golongan_darah ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h5 class="fw-bold mb-3 text-dark">
                <i class="fas fa-user-edit text-primary me-2"></i> Edit Informasi Akun
            </h5>
            
            <form action="<?= BASE_URL ?>/profile" method="POST">
                <?= SecurityHelper::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="username" class="form-label fw-semibold small">Username</label>
                        <input type="text" class="form-control bg-light" id="username" value="<?= htmlspecialchars($user->username ?? '', ENT_QUOTES, 'UTF-8') ?>" disabled>
                        <small class="text-muted">Username tidak dapat diubah.</small>
                    </div>

                    <div class="col-md-6">
                        <label for="role" class="form-label fw-semibold small">Role Sistem</label>
                        <input type="text" class="form-control bg-light" id="role" value="<?= Helper::getRoleLabel($user->role ?? '') ?>" disabled>
                    </div>

                    <div class="col-md-6">
                        <label for="account_status" class="form-label fw-semibold small">Status Akun</label>
                        <input type="text" class="form-control bg-light" id="account_status" value="<?= (int) ($user->is_active ?? 0) === 1 ? 'Aktif' : 'Nonaktif' ?>" disabled>
                    </div>

                    <div class="col-12">
                        <label for="nama_lengkap" class="form-label fw-semibold small">Nama Lengkap</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user->nama_lengkap ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="email" class="form-label fw-semibold small">Email</label>
                        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user->email ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="no_telepon" class="form-label fw-semibold small">Nomor Telepon / HP</label>
                        <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($user->no_telepon ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/change-password" class="btn btn-outline-secondary">
                        <i class="fas fa-key me-1"></i> Ganti Password
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
