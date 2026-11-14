<div class="d-flex align-items-center gap-3 mb-4">
    <a class="btn btn-light border" href="<?= BASE_URL ?>/antrean" aria-label="Kembali ke antrean"><i class="fas fa-arrow-left"></i></a>
    <div>
        <h4 class="fw-bold text-dark mb-1">Detail Antrean</h4>
        <p class="text-muted small mb-0">Informasi pendaftaran dan status pelayanan.</p>
    </div>
</div>
<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3">
            <div>
                <small class="text-muted text-uppercase">Nomor antrean</small>
                <h2 class="fw-bold text-primary mb-0"><?= SecurityHelper::escape($data->no_antrean) ?></h2>
            </div>
            <div class="text-sm-end">
                <small class="text-muted text-uppercase">Status</small>
                <div><?= Helper::statusBadge($data->status) ?></div>
            </div>
        </div>
        <hr>
        <dl class="row mb-0">
            <dt class="col-sm-4 text-muted fw-medium">Pasien</dt><dd class="col-sm-8"><?= SecurityHelper::escape($data->nama_pasien) ?></dd>
            <dt class="col-sm-4 text-muted fw-medium">Nomor rekam medis</dt><dd class="col-sm-8"><?= SecurityHelper::escape($data->no_rekam_medis) ?></dd>
            <dt class="col-sm-4 text-muted fw-medium">Poli</dt><dd class="col-sm-8"><?= SecurityHelper::escape($data->nama_poli) ?></dd>
            <dt class="col-sm-4 text-muted fw-medium">Dokter</dt><dd class="col-sm-8"><?= SecurityHelper::escape($data->nama_dokter) ?></dd>
            <dt class="col-sm-4 text-muted fw-medium">Tanggal kunjungan</dt><dd class="col-sm-8"><?= SecurityHelper::escape(Helper::formatTanggal($data->tanggal_antrean)) ?></dd>
            <dt class="col-sm-4 text-muted fw-medium">Waktu daftar</dt><dd class="col-sm-8"><?= SecurityHelper::escape(Helper::formatWaktu($data->created_at)) ?></dd>
        </dl>
    </div>
</div>
