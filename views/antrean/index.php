<div class="card border-0 shadow-sm rounded-4 p-4">
    <!-- Header Title (Sesuai Mockup) -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Daftar Antrian Pasien</h4>
            <p class="text-muted small mb-0">Pantau dan kelola antrian pasien</p>
        </div>
        <div class="d-flex gap-2">
            <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'petugas_pelayanan'])): ?>
                <a href="<?= BASE_URL ?>/antrean/loket" class="btn btn-outline-primary d-inline-flex align-items-center">
                    <i class="fas fa-bullhorn me-1"></i> Buka Loket
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>/display" target="_blank" class="btn btn-outline-secondary d-inline-flex align-items-center">
                <i class="fas fa-tv me-1"></i> Monitor TV
            </a>
            <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-primary d-inline-flex align-items-center">
                <i class="fas fa-plus me-1"></i> Pendaftaran Baru
            </a>
        </div>
    </div>

    <!-- Filter Bar (Sesuai Mockup) -->
    <form action="<?= BASE_URL ?>/antrean" method="GET" class="mb-4">
        <div class="row g-2">
            <!-- Search Input -->
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="search" 
                           placeholder="Cari nama, no. antrian, atau poli..." 
                           value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
            </div>

            <!-- Select Poli -->
            <div class="col-6 col-md-3">
                <select class="form-select" name="poli_id" onchange="this.form.submit()">
                    <option value="">Semua Poli</option>
                    <?php if (!empty($poliList)): ?>
                        <?php foreach ($poliList as $p): ?>
                            <option value="<?= $p->id ?? $p['id'] ?>" <?= ($filters['poli_id'] == ($p->id ?? $p['id'])) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p->nama_poli ?? $p['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="1">Poli Umum</option>
                        <option value="2">Poli Gigi</option>
                        <option value="3">Poli Anak</option>
                        <option value="4">Poli KIA</option>
                        <option value="5">Poli Penyakit Dalam</option>
                    <?php endif; ?>
                </select>
            </div>

            <!-- Select Status -->
            <div class="col-6 col-md-3">
                <select class="form-select" name="status" onchange="this.form.submit()">
                    <option value="">Semua Status</option>
                    <option value="menunggu" <?= ($filters['status'] === 'menunggu') ? 'selected' : '' ?>>Menunggu</option>
                    <option value="dipanggil" <?= ($filters['status'] === 'dipanggil') ? 'selected' : '' ?>>Dipanggil</option>
                    <option value="dilayani" <?= ($filters['status'] === 'dilayani') ? 'selected' : '' ?>>Sedang Dilayani</option>
                    <option value="selesai" <?= ($filters['status'] === 'selesai') ? 'selected' : '' ?>>Selesai</option>
                    <option value="dilewati" <?= ($filters['status'] === 'dilewati') ? 'selected' : '' ?>>Dilewati</option>
                    <option value="dibatalkan" <?= ($filters['status'] === 'dibatalkan') ? 'selected' : '' ?>>Dibatalkan</option>
                </select>
            </div>

            <!-- Submit Filter Button -->
            <div class="col-12 col-md-1">
                <button type="submit" class="btn btn-outline-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <!-- Tabel Antrian Pasien (Sesuai Mockup) -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small">
                    <th>No. Antrian</th>
                    <th>Nama Pasien</th>
                    <th>Poli</th>
                    <th>Waktu Daftar</th>
                    <th>Status</th>
                    <th class="text-center" style="width: 100px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $item): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary fs-6 fw-bold px-2 py-1">
                                    <?= htmlspecialchars($item->no_antrean ?? $item['no_antrean'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">
                                <?= htmlspecialchars($item->nama_pasien ?? $item['nama_pasien'], ENT_QUOTES, 'UTF-8') ?>
                                <small class="text-muted d-block" style="font-size: 0.75rem;">
                                    <?= htmlspecialchars($item->no_rekam_medis ?? $item['no_rekam_medis'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <?= htmlspecialchars($item->nama_poli ?? $item['nama_poli'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-muted">
                                <?= date('H:i', strtotime($item->created_at ?? $item['created_at'])) ?>
                            </td>
                            <td>
                                <?= Helper::statusBadge($item->status ?? $item['status']) ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= BASE_URL ?>/antrean/detail/<?= (int) ($item->id ?? $item['id']) ?>" class="btn btn-primary btn-sm px-3 rounded-pill">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td class="text-center text-muted py-4" colspan="6">Tidak ada data antrean untuk filter yang dipilih.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination Footer (Sesuai Mockup) -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 pt-3 border-top gap-2">
        <nav aria-label="Navigasi antrean">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $page - 1) ?>">&lsaquo;</a>
                </li>
                <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= SecurityHelper::escape(http_build_query([
                            'search' => $filters['search'] ?? '',
                            'poli_id' => $filters['poli_id'] ?? '',
                            'status' => $filters['status'] ?? '',
                            'tanggal' => $filters['tanggal'] ?? '',
                            'page' => $i,
                        ])) ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>">&rsaquo;</a>
                </li>
            </ul>
        </nav>
        <span class="text-muted small">
            Total <strong><?= number_format($total) ?></strong> pasien
        </span>
    </div>
</div>
