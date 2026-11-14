<div class="row justify-content-center">
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <div class="d-flex align-items-center justify-content-between mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-dark mb-1">
                        <i class="fas fa-plus-circle text-primary me-2"></i> Tambah Poli Pelayanan
                    </h4>
                    <p class="text-muted small mb-0">Daftarkan unit poliklinik baru untuk layanan antrean Puskesmas</p>
                </div>
                <a href="<?= BASE_URL ?>/poli" class="btn btn-outline-secondary rounded-pill px-3">
                    <i class="fas fa-arrow-left me-1"></i> Kembali
                </a>
            </div>

            <form action="<?= BASE_URL ?>/poli/create" method="POST">
                <?= CsrfMiddleware::field() ?>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Kode Poli <span class="text-danger">*</span></label>
                    <input type="text" name="kode_poli" class="form-control rounded-3 text-uppercase" placeholder="Contoh: UMM, GGI, ANK" maxlength="10" required>
                    <div class="form-text">Kode unik huruf awalan nomor antrean (misal: A, B, UMM).</div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Nama Poli / Unit <span class="text-danger">*</span></label>
                    <input type="text" name="nama_poli" class="form-control rounded-3" placeholder="Contoh: Poli Umum" maxlength="100" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-dark">Deskripsi Layanan</label>
                    <textarea name="deskripsi" class="form-control rounded-3" rows="3" placeholder="Deskripsi ringkas layanan poli"></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Icon FontAwesome</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-stethoscope" id="icon-preview"></i></span>
                            <input type="text" name="icon" id="icon-input" class="form-control" value="fas fa-stethoscope" placeholder="fas fa-stethoscope">
                        </div>
                        <div class="form-text">Contoh: fas fa-tooth, fas fa-baby, fas fa-heart</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold text-dark">Warna Tema Poli</label>
                        <div class="input-group">
                            <input type="color" name="warna" class="form-control form-control-color w-100 rounded-3" value="#2563EB" title="Pilih warna">
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <a href="<?= BASE_URL ?>/poli" class="btn btn-light rounded-pill px-4">Batal</a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-save me-2"></i> Simpan Poli
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

