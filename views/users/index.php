<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Manajemen Pengguna</h4>
        <p class="text-muted small mb-0">Kelola akun, role, dan status akses pengguna.</p>
    </div>
    <a class="btn btn-primary" href="<?= BASE_URL ?>/users/create"><i class="fas fa-user-plus me-2"></i>Tambah Pengguna</a>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body">
        <form class="row g-2 align-items-end mb-3" method="GET" action="<?= BASE_URL ?>/users">
            <div class="col-12 col-lg-5">
                <label class="form-label small fw-semibold" for="userSearch">Cari pengguna</label>
                <input class="form-control" id="userSearch" type="search" name="search" value="<?= SecurityHelper::escape($search) ?>" placeholder="Nama, email, atau username">
            </div>
            <div class="col-6 col-lg-3">
                <label class="form-label small fw-semibold" for="userRole">Role</label>
                <select class="form-select" id="userRole" name="role">
                    <option value="">Semua role</option>
                    <?php foreach ($roles as $roleOption): ?>
                        <option value="<?= SecurityHelper::escape($roleOption) ?>" <?= $role === $roleOption ? 'selected' : '' ?>><?= SecurityHelper::escape(Helper::getRoleLabel($roleOption)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-lg-2">
                <label class="form-label small fw-semibold" for="userStatus">Status</label>
                <select class="form-select" id="userStatus" name="status">
                    <option value="">Semua status</option>
                    <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Nonaktif</option>
                </select>
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" type="submit"><i class="fas fa-search me-1"></i>Filter</button>
                <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/users" aria-label="Hapus filter"><i class="fas fa-rotate-left"></i></a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Tanggal Daftar</th>
                        <th><span class="visually-hidden">Aksi</span></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($users === []): ?>
                        <tr><td class="text-center text-muted py-4" colspan="7">Tidak ada pengguna yang cocok dengan filter.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $account): ?>
                            <tr>
                                <td><?= (int) $account->id ?></td>
                                <td>
                                    <strong><?= SecurityHelper::escape($account->nama_lengkap) ?></strong>
                                    <small class="d-block text-muted">@<?= SecurityHelper::escape($account->username) ?></small>
                                </td>
                                <td><?= SecurityHelper::escape($account->email ?? '-') ?></td>
                                <td><?= SecurityHelper::escape(Helper::getRoleLabel($account->role)) ?></td>
                                <td><span class="badge <?= (int) $account->is_active === 1 ? 'bg-success-subtle text-success' : 'bg-secondary-subtle text-secondary' ?>"><?= (int) $account->is_active === 1 ? 'Aktif' : 'Nonaktif' ?></span></td>
                                <td><?= SecurityHelper::escape(Helper::formatTanggal($account->created_at, false)) ?></td>
                                <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="<?= BASE_URL ?>/users/detail/<?= (int) $account->id ?>">Detail</a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mt-3">
            <small class="text-muted">Menampilkan <?= count($users) ?> dari <?= (int) $total ?> pengguna.</small>
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Navigasi halaman pengguna">
                    <ul class="pagination pagination-sm mb-0">
                        <?php for ($pageNumber = 1; $pageNumber <= $totalPages; $pageNumber++): ?>
                            <?php $query = http_build_query(['search' => $search, 'role' => $role, 'status' => $status, 'page' => $pageNumber]); ?>
                            <li class="page-item <?= $page === $pageNumber ? 'active' : '' ?>">
                                <a class="page-link" href="<?= BASE_URL ?>/users?<?= SecurityHelper::escape($query) ?>"><?= $pageNumber ?></a>
                            </li>
                        <?php endfor; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>
