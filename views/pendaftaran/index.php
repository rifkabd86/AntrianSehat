<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Pendaftaran Pasien</h4>
            <p class="text-muted small mb-0">Daftar kunjungan pasien dan nomor antrean.</p>
        </div>
        <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Pendaftaran Baru
        </a>
    </div>

    <form action="<?= BASE_URL ?>/pendaftaran" method="GET" class="mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold" for="search">Pencarian</label>
                <input class="form-control" id="search" name="search" type="search" maxlength="100"
                    placeholder="Nama, nomor rekam medis, atau antrean"
                    value="<?= SecurityHelper::escape($filters['search'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold" for="tanggal">Tanggal kunjungan</label>
                <input class="form-control" id="tanggal" name="tanggal" type="date"
                    value="<?= SecurityHelper::escape($filters['tanggal'] ?? '') ?>">
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold" for="poli_id">Poli</label>
                <select class="form-select" id="poli_id" name="poli_id">
                    <option value="0">Semua poli</option>
                    <?php foreach ($poliList as $poli): ?>
                        <option value="<?= (int) $poli->id ?>" <?= (int) ($filters['poli_id'] ?? 0) === (int) $poli->id ? 'selected' : '' ?>>
                            <?= SecurityHelper::escape($poli->nama_poli) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold" for="status">Status</label>
                <select class="form-select" id="status" name="status">
                    <option value="">Semua status</option>
                    <option value="confirmed" <?= ($filters['status'] ?? '') === 'confirmed' ? 'selected' : '' ?>>Terkonfirmasi</option>
                    <option value="cancelled" <?= ($filters['status'] ?? '') === 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>
            <div class="col-6 col-md-1 d-grid">
                <button class="btn btn-outline-primary" type="submit" aria-label="Terapkan filter">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="small text-muted">
                    <th>No. antrean</th>
                    <th>Pasien</th>
                    <th>Poli / Dokter</th>
                    <th>Tanggal</th>
                    <th>Status</th>
                    <th class="text-end">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($list !== []): ?>
                    <?php foreach ($list as $item): ?>
                        <tr>
                            <td>
                                <?php if (!empty($item->no_antrean)): ?>
                                    <span class="badge bg-primary-subtle text-primary"><?= SecurityHelper::escape($item->no_antrean) ?></span>
                                <?php else: ?>
                                    <span class="text-muted">Belum tersedia</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="fw-semibold"><?= SecurityHelper::escape($item->nama_pasien) ?></span>
                                <small class="d-block text-muted"><?= SecurityHelper::escape($item->no_rekam_medis) ?></small>
                            </td>
                            <td>
                                <span><?= SecurityHelper::escape($item->nama_poli) ?></span>
                                <small class="d-block text-muted"><?= SecurityHelper::escape($item->nama_dokter) ?></small>
                            </td>
                            <td><?= Helper::formatTanggal($item->tanggal_kunjungan, false) ?></td>
                            <td>
                                <?php if ($item->status === 'cancelled'): ?>
                                    <span class="badge bg-danger-subtle text-danger">Dibatalkan</span>
                                <?php else: ?>
                                    <span class="badge bg-success-subtle text-success">Terkonfirmasi</span>
                                <?php endif; ?>
                                <?php if (!empty($item->status_antrean)): ?>
                                    <small class="d-block mt-1"><?= Helper::statusBadge($item->status_antrean) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (!empty($item->no_antrean)): ?>
                                    <a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/antrean/detail/<?= (int) $item->id_antrean ?>" aria-label="Lihat detail antrean">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="text-center text-muted py-4" colspan="6">Belum ada pendaftaran untuk filter yang dipilih.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 mt-4 pt-3 border-top">
        <small class="text-muted">Menampilkan <?= count($list) ?> dari <?= (int) $total ?> pendaftaran</small>
        <?php if ($totalPages > 1): ?>
            <?php $query = $filters; ?>
            <nav aria-label="Halaman daftar pendaftaran">
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= SecurityHelper::escape(http_build_query($query + ['page' => max(1, $page - 1)])) ?>" aria-label="Sebelumnya">&lsaquo;</a>
                    </li>
                    <li class="page-item disabled"><span class="page-link"><?= (int) $page ?> / <?= (int) $totalPages ?></span></li>
                    <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
                        <a class="page-link" href="?<?= SecurityHelper::escape(http_build_query($query + ['page' => min($totalPages, $page + 1)])) ?>" aria-label="Berikutnya">&rsaquo;</a>
                    </li>
                </ul>
            </nav>
        <?php endif; ?>
    </div>
</div>
