<div class="row justify-content-center">
    <div class="col-lg-9">
        <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-1">Pengaturan Sistem</h4>
                    <p class="text-muted small mb-0">Kelola identitas Puskesmas, parameter antrean, dan tampilan monitor display</p>
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill">
                    <i class="fas fa-shield-alt me-1"></i> Administrator Only
                </span>
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

            <form action="<?= BASE_URL ?>/settings/update" method="POST">
                <?= SecurityHelper::csrfField() ?>

                <!-- Section 1: Identitas Puskesmas -->
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-hospital me-1"></i> Identitas Fasilitas Kesehatan (Puskesmas)
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="nama_puskesmas" class="form-label fw-semibold small text-dark">Nama Puskesmas *</label>
                        <input type="text" class="form-control" id="nama_puskesmas" name="nama_puskesmas" 
                               value="<?= htmlspecialchars($settings['nama_puskesmas'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="telepon_puskesmas" class="form-label fw-semibold small text-dark">Nomor Telepon Kontak</label>
                        <input type="text" class="form-control" id="telepon_puskesmas" name="telepon_puskesmas" 
                               value="<?= htmlspecialchars($settings['telepon_puskesmas'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="email_puskesmas" class="form-label fw-semibold small text-dark">Email Resmi</label>
                        <input type="email" class="form-control" id="email_puskesmas" name="email_puskesmas" 
                               value="<?= htmlspecialchars($settings['email_puskesmas'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="alamat_puskesmas" class="form-label fw-semibold small text-dark">Alamat Lengkap</label>
                        <input type="text" class="form-control" id="alamat_puskesmas" name="alamat_puskesmas" 
                               value="<?= htmlspecialchars($settings['alamat_puskesmas'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="kepala_puskesmas" class="form-label fw-semibold small text-dark">Nama Kepala Puskesmas</label>
                        <input type="text" class="form-control" id="kepala_puskesmas" name="kepala_puskesmas" 
                               value="<?= htmlspecialchars($settings['kepala_puskesmas'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                    <div class="col-md-6">
                        <label for="nip_kepala" class="form-label fw-semibold small text-dark">NIP Kepala Puskesmas</label>
                        <input type="text" class="form-control" id="nip_kepala" name="nip_kepala" 
                               value="<?= htmlspecialchars($settings['nip_kepala'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <!-- Section 2: Operasional Antrean -->
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-ticket-alt me-1"></i> Parameter Operasional Antrean
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="kuota_harian_default" class="form-label fw-semibold small text-dark">Kuota Default Harian per Poli (Pasien)</label>
                        <input type="number" class="form-control" id="kuota_harian_default" name="kuota_harian_default" min="1" max="500" 
                               value="<?= htmlspecialchars($settings['kuota_harian_default'] ?? '30', ENT_QUOTES, 'UTF-8') ?>">
                        <div class="form-text">Batas kuota otomatis untuk jadwal dokter baru yang ditambahkan.</div>
                    </div>
                </div>

                <!-- Section 3: Konfigurasi Display TV -->
                <h6 class="fw-bold text-primary border-bottom pb-2 mb-3">
                    <i class="fas fa-tv me-1"></i> Layar Display Monitor Antrean
                </h6>
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <label for="running_text_display" class="form-label fw-semibold small text-dark">Teks Berjalan (Running Text Footer)</label>
                        <textarea class="form-control" id="running_text_display" name="running_text_display" rows="3"><?= htmlspecialchars($settings['running_text_display'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                        <div class="form-text">Pengumuman penting atau himbauan untuk pasien di ruang tunggu.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small text-dark d-block">Suara Notifikasi Pemanggilan (Chime & Suara)</label>
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" id="audio_chime_aktif" name="audio_chime_aktif" value="1" 
                                   <?= ($settings['audio_chime_aktif'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label small" for="audio_chime_aktif">Aktifkan efek suara dan text-to-speech saat memanggil antrean</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="fas fa-save me-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

