<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Laporan Kunjungan & Pelayanan</h4>
            <p class="text-muted small mb-0">Rekapitulasi aktivitas pendaftaran dan antrean pasien Puskesmas</p>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= BASE_URL ?>/laporan/cetak?start_date=<?= $filters['start_date'] ?>&end_date=<?= $filters['end_date'] ?>&poli_id=<?= $filters['poli_id'] ?>" target="_blank" class="btn btn-outline-primary d-inline-flex align-items-center">
                <i class="fas fa-print me-2"></i> Cetak Laporan
            </a>
        </div>
    </div>

    <!-- Filter Periode & Poli -->
    <form action="<?= BASE_URL ?>/laporan" method="GET" class="card bg-light border-0 rounded-4 p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
                <input type="date" class="form-control" name="start_date" value="<?= $filters['start_date'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
                <input type="date" class="form-control" name="end_date" value="<?= $filters['end_date'] ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-semibold text-secondary">Poli Tujuan</label>
                <select class="form-select" name="poli_id">
                    <option value="">Semua Poli</option>
                    <?php foreach ($poliList as $p): ?>
                        <option value="<?= $p->id ?? $p['id'] ?>" <?= ($filters['poli_id'] == ($p->id ?? $p['id'])) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i> Terapkan Filter
                </button>
            </div>
        </div>
    </form>

    <!-- Ringkasan Statistik Laporan -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-primary text-white">
                <small class="text-white-50">Total Kunjungan Pasien</small>
                <h3 class="fw-bold mb-0"><?= number_format($totalKunjungan) ?></h3>
                <small class="text-white-50">Dalam periode terpilih</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-success text-white">
                <small class="text-white-50">Pasien Selesai Dilayani</small>
                <h3 class="fw-bold mb-0"><?= number_format($totalSelesai) ?></h3>
                <small class="text-white-50">Tuntas diperiksa dokter</small>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-secondary text-white">
                <small class="text-white-50">Antrean Dibatalkan / Dilewati</small>
                <h3 class="fw-bold mb-0"><?= number_format($totalBatal) ?></h3>
                <small class="text-white-50">Tidak hadir / batal</small>
            </div>
        </div>
    </div>

    <!-- Tabel Data Laporan -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small">
                    <th>Tanggal</th>
                    <th>No. Antrian</th>
                    <th>No. RM</th>
                    <th>Nama Pasien</th>
                    <th>Poli</th>
                    <th>Dokter</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($rows)): ?>
                    <?php foreach ($rows as $row): ?>
                        <tr>
                            <td class="text-muted small">
                                <?= Helper::formatTanggal($row->tanggal_antrean ?? $row['tanggal_antrean'], false) ?>
                            </td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                    <?= htmlspecialchars($row->no_antrean ?? $row['no_antrean'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($row->no_rekam_medis ?? $row['no_rekam_medis'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="fw-semibold text-dark">
                                <?= htmlspecialchars($row->nama_pasien ?? $row['nama_pasien'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($row->nama_poli ?? $row['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($row->nama_dokter ?? $row['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?= Helper::statusBadge($row->status ?? $row['status']) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Tidak ada data kunjungan pada periode tanggal ini.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

