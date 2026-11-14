<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="fas fa-edit text-primary me-2"></i> Edit Data Poli
                    </h4>
                    <p class="text-muted small mb-0">Perbarui informasi dan tampilan unit poliklinik</p>
                </div>
                <a href="<?= BASE_URL ?>/poli" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= BASE_URL ?>/poli/edit/<?= $poli->id ?? $poli['id'] ?>" method="POST">
                <?= CsrfMiddleware::field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Kode Poli</label>
                    <input type="text" class="form-control rounded-3 bg-light text-uppercase fw-bold" 
                           value="<?= htmlspecialchars($poli->kode_poli ?? $poli['kode_poli'], ENT_QUOTES, 'UTF-8') ?>" readonly disabled>
                    <div class="form-text">Kode poli bersifat permanen untuk menjaga konsistensi data antrean historis.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Nama Poli / Unit <span class="text-danger">*</span></label>
                    <input type="text" name="nama_poli" class="form-control rounded-3" 
                           value="<?= htmlspecialchars($poli->nama_poli ?? $poli['nama_poli'], ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Deskripsi Layanan</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="3"><?= htmlspecialchars($poli->deskripsi ?? $poli['deskripsi'] ?? '', ENT_QUOTES, 'UTF-8') ?></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Icon FontAwesome</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="<?= htmlspecialchars($poli->icon ?? $poli['icon'] ?? 'fas fa-stethoscope', ENT_QUOTES, 'UTF-8') ?>" id="icon-preview"></i></span>
                            <input type="text" name="icon" id="icon-input" class="form-control" 
                                   value="<?= htmlspecialchars($poli->icon ?? $poli['icon'] ?? 'fas fa-stethoscope', ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Warna Tema Poli</label>
                        <div class="input-group">
                            <input type="color" name="warna" class="form-control form-control-color w-100 rounded-3" 
                                   value="<?= htmlspecialchars($poli->warna ?? $poli['warna'] ?? '#2563EB', ENT_QUOTES, 'UTF-8') ?>" title="Pilih warna">
                        </div>
                    </div>
                </div>

                <div class="form-check form-switch mb-4">
                    <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" 
                           <?= (!isset($poli->is_active) || $poli->is_active == 1) ? 'checked' : '' ?>>
                    <label class="form-check-label fw-semibold text-dark ms-2" for="is_active">Status Poli Aktif</label>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/poli" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-2"></i> Perbarui Poli
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const iconInput = document.getElementById('icon-input');
    const iconPreview = document.getElementById('icon-preview');
    if (iconInput && iconPreview) {
        iconInput.addEventListener('input', function() {
            iconPreview.className = this.value || 'fas fa-stethoscope';
        });
    }
});
</script>

