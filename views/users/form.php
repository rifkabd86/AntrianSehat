<?php
$isEditing = $user !== null;
$formAction = $isEditing ? BASE_URL . '/users/update/' . (int) $user->id : BASE_URL . '/users/store';
$selectedRole = Helper::old('role', $user->role ?? 'petugas_pendaftaran');
?>
<div class="d-flex align-items-center gap-3 mb-4">
    <a class="btn btn-light border" href="<?= BASE_URL ?>/users" aria-label="Kembali ke daftar pengguna"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold text-dark mb-1"><?= $isEditing ? 'Edit Pengguna' : 'Tambah Pengguna' ?></h4>
        <p class="text-muted small mb-0"><?= $isEditing ? 'Perbarui data dasar dan role akun.' : 'Buat akun baru dengan role yang sesuai.' ?></p>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="<?= SecurityHelper::escape($formAction) ?>" method="POST" class="row g-3">
            <?= SecurityHelper::csrfField() ?>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="nama_lengkap">Nama lengkap *</label>
                <input class="form-control" id="nama_lengkap" name="nama_lengkap" maxlength="100" value="<?= Helper::old('nama_lengkap', $user->nama_lengkap ?? '') ?>" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="username">Username *</label>
                <input class="form-control" id="username" name="username" maxlength="50" pattern="[A-Za-z0-9_.-]{3,50}" value="<?= Helper::old('username', $user->username ?? '') ?>" autocomplete="username" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="email">Email *</label>
                <input class="form-control" id="email" name="email" type="email" maxlength="100" value="<?= Helper::old('email', $user->email ?? '') ?>" autocomplete="email" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="no_telepon">Nomor telepon</label>
                <input class="form-control" id="no_telepon" name="no_telepon" type="tel" maxlength="20" value="<?= Helper::old('no_telepon', $user->no_telepon ?? '') ?>" autocomplete="tel">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold" for="role">Role *</label>
                <select class="form-select" id="role" name="role" required>
                    <?php foreach ($roles as $roleOption): ?>
                        <option value="<?= SecurityHelper::escape($roleOption) ?>" <?= $selectedRole === $roleOption ? 'selected' : '' ?>><?= SecurityHelper::escape(Helper::getRoleLabel($roleOption)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <?php if (!$isEditing): ?>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="password">Password sementara *</label>
                    <input class="form-control" id="password" name="password" type="password" minlength="8" maxlength="72" autocomplete="new-password" required>
                    <small class="form-text">Minimal 8 karakter. Sampaikan secara aman kepada pengguna.</small>
                </div>
            <?php endif; ?>

            <div class="col-12">
                <div class="p-3 rounded-3 bg-light border">
                    <h6 class="fw-bold mb-1">Data pasien (diperlukan jika role pasien)</h6>
                    <p class="small text-muted mb-3">Data ini membentuk profil pasien dan nomor rekam medis yang terhubung dengan akun.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small" for="nik">NIK</label>
                            <input class="form-control" id="nik" name="nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="<?= Helper::old('nik', $patient->nik ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="tempat_lahir">Tempat lahir</label>
                            <input class="form-control" id="tempat_lahir" name="tempat_lahir" maxlength="100" value="<?= Helper::old('tempat_lahir', $patient->tempat_lahir ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="tanggal_lahir">Tanggal lahir</label>
                            <input class="form-control" id="tanggal_lahir" name="tanggal_lahir" type="date" value="<?= Helper::old('tanggal_lahir', $patient->tanggal_lahir ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="jenis_kelamin">Jenis kelamin</label>
                            <?php $gender = Helper::old('jenis_kelamin', $patient->jenis_kelamin ?? ''); ?>
                            <select class="form-select" id="jenis_kelamin" name="jenis_kelamin">
                                <option value="">Pilih jenis kelamin</option>
                                <option value="Laki-laki" <?= $gender === 'Laki-laki' ? 'selected' : '' ?>>Laki-laki</option>
                                <option value="Perempuan" <?= $gender === 'Perempuan' ? 'selected' : '' ?>>Perempuan</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="golongan_darah">Golongan darah</label>
                            <?php $bloodType = Helper::old('golongan_darah', $patient->golongan_darah ?? '-'); ?>
                            <select class="form-select" id="golongan_darah" name="golongan_darah">
                                <?php foreach (['-' => 'Tidak diketahui', 'A' => 'A', 'B' => 'B', 'AB' => 'AB', 'O' => 'O'] as $value => $label): ?>
                                    <option value="<?= $value ?>" <?= $bloodType === $value ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small" for="alamat">Alamat</label>
                            <input class="form-control" id="alamat" name="alamat" value="<?= Helper::old('alamat', $patient->alamat ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2 pt-2">
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/users">Batal</a>
                <button class="btn btn-primary" type="submit"><i class="fas fa-save me-2"></i><?= $isEditing ? 'Simpan Perubahan' : 'Buat Pengguna' ?></button>
            </div>
        </form>
    </div>
</div>
