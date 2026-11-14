<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                        <?= !empty($jadwal) ? 'Edit Jadwal Praktik' : 'Tambah Jadwal Praktik' ?>
                    </h4>
                    <p class="text-muted small mb-0">Atur hari, jam pelayanan, kuota pasien, dan penempatan poli dokter</p>
                </div>
                <a href="<?= BASE_URL ?>/jadwal" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= BASE_URL ?>/jadwal/<?= !empty($jadwal) ? 'update/' . ($jadwal->id ?? $jadwal['id']) : 'store' ?>" method="POST">
                <?= CsrfMiddleware::field() ?>

                <div class="row g-3">
                    <!-- Dokter -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Dokter <span class="text-danger">*</span></label>
                        <select name="dokter_id" id="dokter_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Dokter --</option>
                            <?php foreach ($dokterList as $d): ?>
                                <?php 
                                    $dId = $d->id ?? $d['id'];
                                    $curDokter = $jadwal->dokter_id ?? $jadwal['dokter_id'] ?? $selectedDokterId ?? 0;
                                ?>
                                <option value="<?= $dId ?>" data-poli="<?= $d->poli_id ?? $d['poli_id'] ?>" <?= (int)$curDokter === (int)$dId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($d->nama_dokter ?? $d['nama_dokter'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($d->nama_poli ?? $d['nama_poli'], ENT_QUOTES, 'UTF-8') ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Poli -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Poli / Unit Pelayanan <span class="text-danger">*</span></label>
                        <select name="poli_id" id="poli_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Poli --</option>
                            <?php foreach ($poliList as $p): ?>
                                <?php 
                                    $pId = $p->id ?? $p['id'];
                                    $curPoli = $jadwal->poli_id ?? $jadwal['poli_id'] ?? 0;
                                ?>
                                <option value="<?= $pId ?>" <?= (int)$curPoli === (int)$pId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Hari Praktik -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Hari Praktik <span class="text-danger">*</span></label>
                        <?php 
                            $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
                            $curDay = $jadwal->hari ?? $jadwal['hari'] ?? 'Senin';
                        ?>
                        <select name="hari" class="form-select rounded-3" required>
                            <?php foreach ($days as $day): ?>
                                <option value="<?= $day ?>" <?= $curDay === $day ? 'selected' : '' ?>><?= $day ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jam Mulai -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_mulai" class="form-control rounded-3" 
                               value="<?= htmlspecialchars(substr($jadwal->jam_mulai ?? $jadwal['jam_mulai'] ?? '08:00', 0, 5), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- Jam Selesai -->
                    <div class="col-md-4">
                        <label class="form-label fw-semibold text-dark">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="jam_selesai" class="form-control rounded-3" 
                               value="<?= htmlspecialchars(substr($jadwal->jam_selesai ?? $jadwal['jam_selesai'] ?? '12:00', 0, 5), ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <!-- Kuota Pasien -->
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Kuota Pasien <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="kuota" class="form-control rounded-start-3" min="1" max="500" 
                                   value="<?= (int)($jadwal->kuota ?? $jadwal['kuota'] ?? 30) ?>" required>
                            <span class="input-group-text bg-light text-muted">Pasien/hari</span>
                        </div>
                        <div class="form-text">Batas maksimal kuota antrean yang dapat mendaftar pada sesi jadwal ini.</div>
                    </div>

                    <?php if (!empty($jadwal)): ?>
                        <!-- Status Aktif -->
                        <div class="col-md-6 d-flex align-items-center">
                            <div class="form-check form-switch mt-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                                       <?= (!isset($jadwal->is_active) || $jadwal->is_active == 1) ? 'checked' : '' ?>>
                                <label class="form-check-label fw-semibold text-dark ms-2" for="is_active">Status Jadwal Aktif</label>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/jadwal" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-2"></i> Simpan Jadwal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const dokterSelect = document.getElementById('dokter_id');
    const poliSelect = document.getElementById('poli_id');

    if (dokterSelect && poliSelect) {
        dokterSelect.addEventListener('change', function() {
            const selected = this.options[this.selectedIndex];
            const poliId = selected.getAttribute('data-poli');
            if (poliId && !poliSelect.value) {
                poliSelect.value = poliId;
            }
        });
    }
});
</script>

