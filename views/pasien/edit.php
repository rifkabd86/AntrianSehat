<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Edit Data Pasien</h4>
                    <p class="text-muted small mb-0">No. Rekam Medis: <strong class="text-primary"><?= htmlspecialchars($pasien->no_rekam_medis ?? $pasien['no_rekam_medis'], ENT_QUOTES, 'UTF-8') ?></strong></p>
                </div>
                <a href="<?= BASE_URL ?>/pasien/detail/<?= $pasien->id ?? $pasien['id'] ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= BASE_URL ?>/pasien/update/<?= $pasien->id ?? $pasien['id'] ?>" method="POST">
                <?= SecurityHelper::csrfField() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nama_lengkap" class="form-label fw-semibold small">Nama Lengkap *</label>
                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                               value="<?= htmlspecialchars($pasien->nama_lengkap ?? $pasien['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="nik" class="form-label fw-semibold small">NIK (16 Digit) *</label>
                        <input type="text" class="form-control" id="nik" name="nik" maxlength="16" 
                               value="<?= htmlspecialchars($pasien->nik ?? $pasien['nik'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="tempat_lahir" class="form-label fw-semibold small">Tempat Lahir</label>
                        <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                               value="<?= htmlspecialchars($pasien->tempat_lahir ?? $pasien['tempat_lahir'], ENT_QUOTES, 'UTF-8') ?>">
                    </div>

                    <div class="col-md-6">
                        <label for="tanggal_lahir" class="form-label fw-semibold small">Tanggal Lahir *</label>
                        <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                               value="<?= htmlspecialchars($pasien->tanggal_lahir ?? $pasien['tanggal_lahir'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label for="jenis_kelamin" class="form-label fw-semibold small">Jenis Kelamin *</label>
                        <select class="form-select" id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="Laki-laki" <?= (($pasien->jenis_kelamin ?? $pasien['jenis_kelamin']) === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                            <option value="Perempuan" <?= (($pasien->jenis_kelamin ?? $pasien['jenis_kelamin']) === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="golongan_darah" class="form-label fw-semibold small">Golongan Darah</label>
                        <select class="form-select" id="golongan_darah" name="golongan_darah">
                            <option value="-">-</option>
                            <?php foreach (['A', 'B', 'AB', 'O'] as $goldar): ?>
                                <option value="<?= $goldar ?>" <?= (($pasien->golongan_darah ?? $pasien['golongan_darah']) === $goldar) ? 'selected' : '' ?>><?= $goldar ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label for="no_telepon" class="form-label fw-semibold small">Nomor Telepon / HP *</label>
                        <input type="text" class="form-control" id="no_telepon" name="no_telepon" 
                               value="<?= htmlspecialchars($pasien->no_telepon ?? $pasien['no_telepon'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-12">
                        <label for="alamat" class="form-label fw-semibold small">Alamat Domisili</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="3"><?= htmlspecialchars($pasien->alamat ?? $pasien['alamat'], ENT_QUOTES, 'UTF-8') ?></textarea>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/pasien/detail/<?= $pasien->id ?? $pasien['id'] ?>" class="btn btn-outline-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

