<div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Riwayat Kunjungan</h4>
        <p class="text-muted small mb-0">Riwayat kunjungan yang terhubung dengan akun Anda.</p>
    </div>
    <a class="btn btn-outline-primary" href="<?= BASE_URL ?>/antrean/saya"><i class="fas fa-ticket me-2"></i>Antrean Saya</a>
</div>
<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr><th>Tanggal</th><th>Nomor antrean</th><th>Poli</th><th>Dokter</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php if ($history === []): ?>
                    <tr><td class="text-center text-muted py-4" colspan="5">Belum ada riwayat kunjungan.</td></tr>
                <?php else: ?>
                    <?php foreach ($history as $item): ?>
                        <tr>
                            <td><?= SecurityHelper::escape(Helper::formatTanggal($item->tanggal_antrean)) ?></td>
                            <td><strong><?= SecurityHelper::escape($item->no_antrean) ?></strong></td>
                            <td><?= SecurityHelper::escape($item->nama_poli) ?></td>
                            <td><?= SecurityHelper::escape($item->nama_dokter) ?></td>
                            <td><?= Helper::statusBadge($item->status) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
