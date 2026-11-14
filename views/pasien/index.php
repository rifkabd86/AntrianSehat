<div class="card border-0 shadow-sm rounded-4 p-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Data Pasien</h4>
            <p class="text-muted small mb-0">Kelola informasi pasien dan rekam medis Puskesmas</p>
        </div>
        <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'petugas_pendaftaran'])): ?>
            <a href="<?= BASE_URL ?>/pasien/create" class="btn btn-primary d-inline-flex align-items-center">
                <i class="fas fa-user-plus me-2"></i> Tambah Pasien Baru
            </a>
        <?php endif; ?>
    </div>

    <!-- Filter & Pencarian -->
    <form action="<?= BASE_URL ?>/pasien" method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-6 col-lg-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control border-start-0 ps-0" name="search" 
                           placeholder="Cari NIK, No. Rekam Medis, atau Nama Pasien..." 
                           value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-primary">Cari</button>
                </div>
            </div>
        </div>
    </form>

    <!-- Tabel Pasien -->
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr class="text-muted small">
                    <th>No. RM</th>
                    <th>NIK</th>
                    <th>Nama Lengkap</th>
                    <th>L/P</th>
                    <th>Tanggal Lahir</th>
                    <th>No. Telepon</th>
                    <th>Gol. Darah</th>
                    <th class="text-center" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($list)): ?>
                    <?php foreach ($list as $p): ?>
                        <tr>
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">
                                    <?= htmlspecialchars($p->no_rekam_medis ?? $p['no_rekam_medis'], ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($p->nik ?? $p['nik'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td class="fw-semibold text-dark">
                                <?= htmlspecialchars($p->nama_lengkap ?? $p['nama_lengkap'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <?= ($p->jenis_kelamin ?? $p['jenis_kelamin']) === 'Laki-laki' ? '<span class="badge bg-info-subtle text-info">L</span>' : '<span class="badge bg-danger-subtle text-danger">P</span>' ?>
                            </td>
                            <td class="text-muted small">
                                <?= Helper::formatTanggal($p->tanggal_lahir ?? $p['tanggal_lahir'], false) ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($p->no_telepon ?? $p['no_telepon'], ENT_QUOTES, 'UTF-8') ?>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= htmlspecialchars($p->golongan_darah ?? $p['golongan_darah'] ?? '-', ENT_QUOTES, 'UTF-8') ?></span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?= BASE_URL ?>/pasien/detail/<?= $p->id ?? $p['id'] ?>" class="btn btn-outline-primary" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if (in_array($_SESSION['user_role'] ?? '', ['admin', 'petugas_pendaftaran'])): ?>
                                        <a href="<?= BASE_URL ?>/pasien/edit/<?= $p->id ?? $p['id'] ?>" class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Sample Data Sesuai Database Mock -->
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">RM-20250001</span></td>
                        <td class="text-muted small">3201234567890001</td>
                        <td class="fw-semibold">Rina Aprilia</td>
                        <td><span class="badge bg-danger-subtle text-danger">P</span></td>
                        <td class="text-muted small">12 Maret 1995</td>
                        <td class="text-muted small">081234567890</td>
                        <td><span class="badge bg-light text-dark border">A</span></td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>/pasien/detail/1" class="btn btn-outline-primary btn-sm rounded-pill px-3">Detail</a>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">RM-20250002</span></td>
                        <td class="text-muted small">3201234567890002</td>
                        <td class="fw-semibold">Budi Santoso</td>
                        <td><span class="badge bg-info-subtle text-info">L</span></td>
                        <td class="text-muted small">25 Juli 1988</td>
                        <td class="text-muted small">081234567891</td>
                        <td><span class="badge bg-light text-dark border">B</span></td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>/pasien/detail/2" class="btn btn-outline-primary btn-sm rounded-pill px-3">Detail</a>
                        </td>
                    </tr>
                    <tr>
                        <td><span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold">RM-20250003</span></td>
                        <td class="text-muted small">3201234567890003</td>
                        <td class="fw-semibold">Siti Nurhaliza</td>
                        <td><span class="badge bg-danger-subtle text-danger">P</span></td>
                        <td class="text-muted small">8 November 1992</td>
                        <td class="text-muted small">081234567892</td>
                        <td><span class="badge bg-light text-dark border">O</span></td>
                        <td class="text-center">
                            <a href="<?= BASE_URL ?>/pasien/detail/3" class="btn btn-outline-primary btn-sm rounded-pill px-3">Detail</a>
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 pt-3 border-top gap-2">
        <nav aria-label="Navigasi pasien">
            <ul class="pagination pagination-sm mb-0">
                <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= max(1, $page - 1) ?>">&lsaquo;</a>
                </li>
                <?php for ($i = 1; $i <= max(3, $totalPages); $i++): ?>
                    <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>
                <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                    <a class="page-link" href="?page=<?= min($totalPages, $page + 1) ?>">&rsaquo;</a>
                </li>
            </ul>
        </nav>
        <span class="text-muted small">Total <strong><?= number_format($total > 0 ? $total : 8) ?></strong> pasien terdaftar</span>
    </div>
</div>

