<div class="row g-4">
    <!-- Kolom Kiri: Form Data Pasien (Sesuai Mockup) -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="mb-4">
                <h4 class="fw-bold text-dark mb-1">Pendaftaran Pasien</h4>
                <p class="text-muted small">Input data pasien untuk mendapatkan nomor antrian</p>
            </div>

            <!-- Flash Alert -->
            <?php if (Helper::hasFlash()): ?>
                <?php $flash = Helper::getFlash(); ?>
                <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
                    <i class="fas <?= $flash['type'] === 'error' ? 'fa-circle-exclamation' : 'fa-circle-check' ?> me-2"></i>
                    <div><?= SecurityHelper::escape($flash['message']) ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="<?= BASE_URL ?>/pendaftaran/store" method="POST" id="formPendaftaran">
                <?= SecurityHelper::csrfField() ?>

                <h6 class="fw-bold text-secondary mb-3 small text-uppercase tracking-wider">
                    Data Pasien
                </h6>
                <?php if (($_SESSION['user_role'] ?? '') !== 'pasien'): ?>
                    <div class="mb-3">
                        <label for="nik" class="form-label fw-semibold small text-dark">NIK *</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="nik" name="nik"
                               inputmode="numeric" pattern="[0-9]{16}" maxlength="16" minlength="16"
                               value="<?= Helper::old('nik') ?>" required>
                        <div class="form-text">Masukkan NIK yang benar untuk mencari atau membuat data pasien.</div>
                    </div>
                <?php endif; ?>

                <!-- Nama Lengkap -->
                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label fw-semibold small text-dark">Nama Lengkap *</label>
                    <input type="text" class="form-control form-control-lg fs-6" id="nama_lengkap" name="nama_lengkap" 
                           placeholder="Masukkan nama lengkap" 
                           value="<?= Helper::old('nama_lengkap', $pasienDefault->nama_lengkap ?? '') ?>" required
                           <?= $pasienDefault ? 'readonly' : '' ?>>
                </div>
                <?php if (!$pasienDefault): ?>
                    <div class="mb-3">
                        <label for="tempat_lahir" class="form-label fw-semibold small text-dark">Tempat Lahir *</label>
                        <input type="text" class="form-control form-control-lg fs-6" id="tempat_lahir" name="tempat_lahir"
                               value="<?= Helper::old('tempat_lahir') ?>" maxlength="100" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label fw-semibold small text-dark">Alamat</label>
                        <textarea class="form-control" id="alamat" name="alamat" rows="2"><?= Helper::old('alamat') ?></textarea>
                    </div>
                <?php endif; ?>

                <!-- Tanggal Lahir -->
                <div class="mb-3">
                    <label for="tanggal_lahir" class="form-label fw-semibold small text-dark">Tanggal Lahir *</label>
                    <div class="input-group">
                        <input type="date" class="form-control form-control-lg fs-6" id="tanggal_lahir" name="tanggal_lahir" 
                               value="<?= Helper::old('tanggal_lahir', $pasienDefault->tanggal_lahir ?? '') ?>" required
                               <?= $pasienDefault ? 'readonly' : '' ?>>
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar"></i></span>
                    </div>
                </div>

                <!-- Jenis Kelamin -->
                <div class="mb-3">
                    <label for="jenis_kelamin" class="form-label fw-semibold small text-dark">Jenis Kelamin *</label>
                    <select class="form-select form-select-lg fs-6" id="jenis_kelamin" name="jenis_kelamin" required
                            <?= $pasienDefault ? 'disabled' : '' ?>>
                        <option value="">Pilih jenis kelamin</option>
                        <option value="Laki-laki" <?= (Helper::old('jenis_kelamin', $pasienDefault->jenis_kelamin ?? '') === 'Laki-laki') ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="Perempuan" <?= (Helper::old('jenis_kelamin', $pasienDefault->jenis_kelamin ?? '') === 'Perempuan') ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                </div>

                <!-- Nomor HP -->
                <div class="mb-3">
                    <label for="no_telepon" class="form-label fw-semibold small text-dark">Nomor HP *</label>
                    <input type="text" class="form-control form-control-lg fs-6" id="no_telepon" name="no_telepon" 
                           placeholder="08xxxxxxxxxx" 
                           value="<?= Helper::old('no_telepon', $pasienDefault->no_telepon ?? '') ?>" required
                           <?= $pasienDefault ? 'readonly' : '' ?>>
                </div>

                <!-- Tanggal Kunjungan -->
                <div class="mb-3">
                    <label for="tanggal_kunjungan" class="form-label fw-semibold small text-dark">Tanggal Kunjungan *</label>
                    <div class="input-group">
                        <input type="date" class="form-control form-control-lg fs-6" id="tanggal_kunjungan" name="tanggal_kunjungan" 
                               min="<?= date('Y-m-d') ?>" value="<?= Helper::old('tanggal_kunjungan', date('Y-m-d')) ?>" required>
                        <span class="input-group-text bg-light text-muted"><i class="fas fa-calendar-day"></i></span>
                    </div>
                </div>

                <!-- Poli Tujuan -->
                <div class="mb-3">
                    <label for="poli_id" class="form-label fw-semibold small text-dark">Poli Tujuan *</label>
                    <select class="form-select form-select-lg fs-6" id="poli_id" name="poli_id" required>
                        <option value="">Pilih poli</option>
                        <?php if (!empty($poliList)): ?>
                            <?php foreach ($poliList as $poli): ?>
                                <option value="<?= $poli->id ?? $poli['id'] ?>" <?= (Helper::old('poli_id') == ($poli->id ?? $poli['id'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($poli->nama_poli ?? $poli['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <!-- Dokter Pelayanan (Opsional/Sesuai Jadwal) -->
                <div class="mb-4">
                    <label for="dokter_id" class="form-label fw-semibold small text-dark">Dokter Pemeriksa</label>
                    <select class="form-select form-select-lg fs-6" id="dokter_id" name="dokter_id">
                        <option value="">-- Otomatis Sesuai Jadwal Dokter Jaga --</option>
                        <?php if (!empty($dokterList)): ?>
                            <?php foreach ($dokterList as $d): ?>
                                <option value="<?= $d->id ?? $d['id'] ?>" data-poli="<?= $d->poli_id ?? $d['poli_id'] ?>" <?= (Helper::old('dokter_id') == ($d->id ?? $d['id'])) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d->nama_dokter ?? $d['nama_dokter'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($d->nama_poli ?? $d['nama_poli'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                    <div class="form-text">Biarkan otomatis jika ingin diarahkan ke dokter yang sedang bertugas hari ini.</div>
                </div>

                <!-- Keluhan Opsional -->
                <div class="mb-4">
                    <label for="keluhan" class="form-label fw-semibold small text-dark">Keluhan Utama (Opsional)</label>
                    <textarea class="form-control" id="keluhan" name="keluhan" rows="2" placeholder="Tuliskan keluhan atau gejala yang dirasakan"><?= Helper::old('keluhan') ?></textarea>
                </div>

                <!-- Tombol Submit (Sesuai Mockup) -->
                <div class="d-grid mt-4 pt-2">
                    <button type="submit" class="btn btn-primary btn-lg fw-semibold shadow-sm py-3">
                        <i class="fas fa-ticket-alt me-2"></i> Daftar & Ambil Antrian
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Estimasi Waktu Tunggu & Poli yang Tersedia (Sesuai Mockup) -->
    <div class="col-lg-5">
        <!-- Card 1: Estimasi Waktu Tunggu -->
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4" style="background-color: #F8FAFC;">
            <div class="d-flex align-items-start">
                <div class="stat-icon bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fas fa-clock fs-5"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-2">Estimasi Waktu Tunggu</h6>
                    <p class="text-muted small mb-0 lh-base">
                        Setelah mendaftar, pasien akan mendapatkan nomor antrian dan estimasi waktu tunggu untuk poli yang dipilih.
                    </p>
                </div>
            </div>
        </div>

        <!-- Card 2: Poli yang Tersedia -->
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h6 class="fw-bold text-dark mb-3">Poli yang Tersedia</h6>
            
            <div class="list-group list-group-flush border-0">
                <?php foreach ($poliList as $poli): ?>
                    <div class="list-group-item d-flex align-items-center px-0 py-3 border-light">
                        <div class="text-primary me-3 fs-5" style="width: 32px;"><i class="<?= SecurityHelper::escape($poli->icon ?? 'fas fa-stethoscope') ?>"></i></div>
                        <div class="flex-grow-1">
                            <span class="fw-semibold text-dark d-block"><?= SecurityHelper::escape($poli->nama_poli) ?></span>
                            <small class="text-muted"><?= SecurityHelper::escape($poli->deskripsi ?? 'Layanan tersedia') ?></small>
                        </div>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1">Buka</span>
                    </div>
                <?php endforeach; ?>
                <?php if (!$poliList): ?>
                    <p class="text-muted small mb-0">Belum ada poli yang tersedia.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const poliSelect = document.getElementById('poli_id');
    const dokterSelect = document.getElementById('dokter_id');

    if (poliSelect && dokterSelect) {
        function filterDokter() {
            const selectedPoli = poliSelect.value;
            const options = dokterSelect.querySelectorAll('option');

            options.forEach(opt => {
                if (!opt.value) {
                    opt.style.display = '';
                    return;
                }
                const docPoli = opt.getAttribute('data-poli');
                if (!selectedPoli || docPoli === selectedPoli) {
                    opt.style.display = '';
                } else {
                    opt.style.display = 'none';
                    if (opt.selected) {
                        dokterSelect.value = '';
                    }
                }
            });
        }

        poliSelect.addEventListener('change', filterDokter);
        filterDokter();
    }
});
</script>
