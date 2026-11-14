<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="fas fa-user-doctor text-primary me-2"></i> Edit Data Dokter
                    </h4>
                    <p class="text-muted small mb-0">Perbarui profil tenaga medis dan unit penempatan</p>
                </div>
                <a href="<?= BASE_URL ?>/dokter" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= BASE_URL ?>/dokter/edit/<?= $dokter->id ?? $dokter['id'] ?>" method="POST">
                <?= CsrfMiddleware::field() ?>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Kode Dokter</label>
                        <input type="text" name="kode_dokter" class="form-control rounded-3 text-uppercase" 
                               value="<?= htmlspecialchars($dokter->kode_dokter ?? $dokter['kode_dokter'], ENT_QUOTES, 'UTF-8') ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Poli / Unit Layanan <span class="text-danger">*</span></label>
                        <select name="poli_id" class="form-select rounded-3" required>
                            <option value="">-- Pilih Poli --</option>
                            <?php foreach ($poliList as $p): ?>
                                <?php 
                                    $pId = $p->id ?? $p['id'];
                                    $curPoli = $dokter->poli_id ?? $dokter['poli_id'];
                                ?>
                                <option value="<?= $pId ?>" <?= (int)$curPoli === (int)$pId ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label fw-semibold text-dark">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                        <input type="text" name="nama_dokter" class="form-control rounded-3" 
                               value="<?= htmlspecialchars($dokter->nama_dokter ?? $dokter['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Spesialisasi</label>
                        <input type="text" name="spesialisasi" class="form-control rounded-3" 
                               value="<?= htmlspecialchars($dokter->spesialisasi ?? $dokter['spesialisasi'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="100">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Nomor Telepon / WhatsApp</label>
                        <input type="tel" name="no_telepon" class="form-control rounded-3" 
                               value="<?= htmlspecialchars($dokter->no_telepon ?? $dokter['no_telepon'] ?? '', ENT_QUOTES, 'UTF-8') ?>" maxlength="20">
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch mt-2">
                            <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                                   <?= (!isset($dokter->is_active) || $dokter->is_active == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label fw-semibold text-dark ms-2" for="is_active">Dokter Aktif Melayani</label>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/dokter" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-2"></i> Perbarui Dokter
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

