<div class="audit-log-container">
    <!-- Header Halaman -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">
                <i class="fas fa-clipboard-list text-primary me-2"></i> Audit Log Sistem
            </h4>
            <p class="text-muted small mb-0">Catatan riwayat seluruh aktivitas penting pengguna untuk akuntabilitas sistem</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-semibold">
                <i class="fas fa-lock me-1"></i> Read-Only Administrator
            </span>
        </div>
    </div>

    <!-- 4 Kartu Statistik Audit Log -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Aktivitas -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 card-stat">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                        <i class="fas fa-list-check fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-medium">Total Aktivitas</small>
                        <h3 class="fw-bold text-dark mb-0"><?= number_format((int)($stats['total_aktivitas'] ?? 0)) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Aktivitas Hari Ini -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 card-stat">
                <div class="d-flex align-items-center">
                    <div class="stat-icon bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px;">
                        <i class="fas fa-calendar-day fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-medium">Aktivitas Hari Ini</small>
                        <h3 class="fw-bold text-dark mb-0"><?= number_format((int)($stats['aktivitas_hari_ini'] ?? 0)) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Pengguna Aktif -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 card-stat">
                <div class="d-flex align-items-center">
                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px; background-color: #EDE9FE; color: #7C3AED;">
                        <i class="fas fa-users-gear fs-4"></i>
                    </div>
                    <div>
                        <small class="text-muted d-block fw-medium">Pengguna Beraktivitas</small>
                        <h3 class="fw-bold text-dark mb-0"><?= number_format((int)($stats['total_pengguna'] ?? 0)) ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Aktivitas Terakhir -->
        <div class="col-12 col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 card-stat">
                <div class="d-flex align-items-center">
                    <div class="stat-icon rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 52px; height: 52px; background-color: #FEF3C7; color: #D97706;">
                        <i class="fas fa-clock-rotate-left fs-4"></i>
                    </div>
                    <div class="overflow-hidden">
                        <small class="text-muted d-block fw-medium">Aktivitas Terakhir</small>
                        <?php if (!empty($stats['aktivitas_terakhir'])): ?>
                            <h6 class="fw-bold text-dark mb-0 text-truncate" title="<?= htmlspecialchars($stats['aktivitas_terakhir']->aksi ?? $stats['aktivitas_terakhir']['aksi'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                                <?= htmlspecialchars($stats['aktivitas_terakhir']->aksi ?? $stats['aktivitas_terakhir']['aksi'] ?? '-', ENT_QUOTES, 'UTF-8') ?>
                            </h6>
                            <small class="text-muted d-block text-truncate" style="font-size: 0.75rem;">
                                <?= Helper::formatWaktu($stats['aktivitas_terakhir']->created_at ?? $stats['aktivitas_terakhir']['created_at'] ?? '') ?> oleh <?= htmlspecialchars($stats['aktivitas_terakhir']->username ?? $stats['aktivitas_terakhir']['username'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                            </small>
                        <?php else: ?>
                            <h6 class="fw-bold text-dark mb-0">-</h6>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Panel Pencarian dan Filter -->
    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
        <h6 class="fw-bold text-dark mb-3">
            <i class="fas fa-filter text-primary me-2"></i> Filter & Pencarian Audit Log
        </h6>

        <form action="<?= BASE_URL ?>/audit" method="GET" id="filterForm">
            <div class="row g-3">
                <!-- Pencarian Deskripsi / Kata Kunci -->
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Cari Kata Kunci / Deskripsi</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control border-start-0" name="search" placeholder="Aksi, tabel, atau detail..." value="<?= htmlspecialchars($filters['search'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    </div>
                </div>

                <!-- Dropdown Pengguna -->
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-secondary">Pengguna</label>
                    <select class="form-select" name="username">
                        <option value="">Semua User</option>
                        <?php foreach ($userList as $u): ?>
                            <option value="<?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>" <?= (($filters['username'] ?? '') === $u) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($u, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Dropdown Jenis Aktivitas -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Jenis Aktivitas</label>
                    <select class="form-select" name="aksi">
                        <option value="">Semua Jenis Aktivitas</option>
                        <?php foreach ($aksiTypes as $a): ?>
                            <option value="<?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>" <?= (($filters['aksi'] ?? '') === $a) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($a, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tanggal Mulai -->
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-secondary">Periode Tanggal</label>
                    <div class="input-group">
                        <input type="date" class="form-control" name="date_from" value="<?= htmlspecialchars($filters['date_from'] ?? '', ENT_QUOTES, 'UTF-8') ?>" title="Dari Tanggal">
                        <span class="input-group-text bg-light text-muted">-</span>
                        <input type="date" class="form-control" name="date_to" value="<?= htmlspecialchars($filters['date_to'] ?? '', ENT_QUOTES, 'UTF-8') ?>" title="Sampai Tanggal">
                    </div>
                </div>

                <!-- Tombol Aksi Filter -->
                <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                    <a href="<?= BASE_URL ?>/audit" class="btn btn-light rounded-pill px-3">
                        <i class="fas fa-rotate-left me-1"></i> Reset Filter
                    </a>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-magnifying-glass me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabel Riwayat Aktivitas -->
    <div class="card border-0 shadow-sm rounded-4 p-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold text-dark mb-0">Riwayat Aktivitas Pengguna</h5>
                <small class="text-muted">Menampilkan <?= count($logs) ?> dari total <?= number_format($total) ?> catatan log</small>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr class="text-muted small">
                        <th style="width: 50px;">No</th>
                        <th style="width: 170px;">Tanggal & Waktu</th>
                        <th>Pengguna</th>
                        <th>Jenis Aktivitas</th>
                        <th>Tabel / Modul</th>
                        <th>IP Address</th>
                        <th class="text-center" style="width: 90px;">Status</th>
                        <th class="text-center" style="width: 90px;">Detail</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($logs)): ?>
                        <?php 
                        $no = (($page - 1) * $perPage) + 1;
                        foreach ($logs as $log): 
                            $logId = is_object($log) ? $log->id : $log['id'];
                            $username = is_object($log) ? $log->username : $log['username'];
                            $roleName = is_object($log) ? $log->role : $log['role'];
                            $aksi = is_object($log) ? $log->aksi : $log['aksi'];
                            $tabel = is_object($log) ? ($log->tabel ?? '-') : ($log['tabel'] ?? '-');
                            $ip = is_object($log) ? ($log->ip_address ?? '127.0.0.1') : ($log['ip_address'] ?? '127.0.0.1');
                            $userAgent = is_object($log) ? ($log->user_agent ?? '-') : ($log['user_agent'] ?? '-');
                            $createdAt = is_object($log) ? $log->created_at : $log['created_at'];
                            $recordId = is_object($log) ? ($log->record_id ?? '-') : ($log['record_id'] ?? '-');
                            $dataLama = is_object($log) ? ($log->data_lama ?? null) : ($log['data_lama'] ?? null);
                            $dataBaru = is_object($log) ? ($log->data_baru ?? null) : ($log['data_baru'] ?? null);
                        ?>
                            <tr>
                                <td class="text-muted small fw-medium"><?= $no++ ?></td>
                                <td class="small">
                                    <div class="fw-semibold text-dark"><?= Helper::formatTanggal($createdAt) ?></div>
                                    <div class="text-muted small"><i class="far fa-clock me-1"></i><?= Helper::formatWaktu($createdAt) ?> WIB</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-2 fw-bold" style="width: 32px; height: 32px; font-size: 0.8rem;">
                                            <?= strtoupper(substr($username, 0, 1)) ?>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-dark d-block"><?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;">
                                                <?= Helper::getRoleLabel($roleName) ?>
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <?= Helper::auditActionBadge($aksi) ?>
                                </td>
                                <td class="text-muted small">
                                    <code><?= htmlspecialchars($tabel ?: '-', ENT_QUOTES, 'UTF-8') ?></code>
                                </td>
                                <td class="text-muted small">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-network-wired me-1 text-muted"></i><?= htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-check-circle me-1"></i>Sukses
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 btn-detail-audit"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailAuditModal"
                                            data-id="<?= $logId ?>"
                                            data-user="<?= htmlspecialchars($username, ENT_QUOTES, 'UTF-8') ?>"
                                            data-role="<?= htmlspecialchars(Helper::getRoleLabel($roleName), ENT_QUOTES, 'UTF-8') ?>"
                                            data-aksi="<?= htmlspecialchars($aksi, ENT_QUOTES, 'UTF-8') ?>"
                                            data-tabel="<?= htmlspecialchars($tabel ?: '-', ENT_QUOTES, 'UTF-8') ?>"
                                            data-record="<?= htmlspecialchars((string)$recordId, ENT_QUOTES, 'UTF-8') ?>"
                                            data-ip="<?= htmlspecialchars($ip, ENT_QUOTES, 'UTF-8') ?>"
                                            data-ua="<?= htmlspecialchars($userAgent, ENT_QUOTES, 'UTF-8') ?>"
                                            data-waktu="<?= htmlspecialchars(Helper::formatTanggalWaktu($createdAt), ENT_QUOTES, 'UTF-8') ?>"
                                            data-lama="<?= htmlspecialchars((string)$dataLama, ENT_QUOTES, 'UTF-8') ?>"
                                            data-baru="<?= htmlspecialchars((string)$dataBaru, ENT_QUOTES, 'UTF-8') ?>">
                                        <i class="fas fa-eye me-1"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-clipboard-check fs-1 text-secondary mb-3 d-block"></i>
                                <h6 class="fw-bold mb-1">Tidak Ada Data Audit Log</h6>
                                <p class="small text-muted mb-0">Belum ada riwayat aktivitas yang sesuai dengan filter pencarian.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <?php 
                $queryParams = $filters;
                unset($queryParams['page']);
            ?>
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center mt-4 pt-3 border-top gap-2">
                <span class="small text-muted">
                    Halaman <?= $page ?> dari <?= $totalPages ?> (Total <?= number_format($total) ?> aktivitas)
                </span>
                <nav>
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => max(1, $page - 1)])) ?>">&lsaquo; Sebelumnya</a>
                        </li>
                        
                        <?php 
                        $startPage = max(1, $page - 2);
                        $endPage = min($totalPages, $page + 2);
                        for ($i = $startPage; $i <= $endPage; $i++): 
                        ?>
                            <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                                <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => $i])) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($queryParams, ['page' => min($totalPages, $page + 1)])) ?>">Selanjutnya &rsaquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Detail Aktivitas Audit Log -->
<div class="modal fade" id="detailAuditModal" tabindex="-1" aria-labelledby="detailAuditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="detailAuditModalLabel">Detail Log Aktivitas</h5>
                        <small class="text-muted" id="modal-log-id">Catatan audit riwayat transaksi</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Meta Info Grid -->
                <div class="row g-3 mb-4 bg-light p-3 rounded-3">
                    <div class="col-md-6">
                        <small class="text-muted d-block">Pengguna:</small>
                        <strong class="text-dark" id="modal-user">-</strong> 
                        <span class="badge bg-light text-secondary border ms-1" id="modal-role">-</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Waktu Eksekusi:</small>
                        <strong class="text-dark" id="modal-waktu">-</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Jenis Aksi:</small>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1" id="modal-aksi">-</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Tabel & Record ID:</small>
                        <code class="text-dark" id="modal-tabel-record">-</code>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">IP Address:</small>
                        <code class="text-primary" id="modal-ip">-</code>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">User Agent:</small>
                        <small class="text-muted text-break" id="modal-ua" style="font-size: 0.75rem;">-</small>
                    </div>
                </div>

                <!-- Perbandingan Data Sebelum & Sesudah -->
                <div class="row g-3">
                    <div class="col-md-6">
                        <h6 class="fw-bold text-secondary mb-2 small text-uppercase">
                            <i class="fas fa-clock-rotate-left me-1"></i> Data Sebelum Perubahan
                        </h6>
                        <div class="p-3 bg-light rounded-3 border" style="min-height: 120px; max-height: 250px; overflow-y: auto;">
                            <pre class="mb-0 small text-muted" id="modal-data-lama" style="white-space: pre-wrap; font-size: 0.8rem;">(Tidak ada data sebelum)</pre>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold text-primary mb-2 small text-uppercase">
                            <i class="fas fa-check-double me-1"></i> Data Sesudah Perubahan
                        </h6>
                        <div class="p-3 bg-light rounded-3 border" style="min-height: 120px; max-height: 250px; overflow-y: auto;">
                            <pre class="mb-0 small text-dark" id="modal-data-baru" style="white-space: pre-wrap; font-size: 0.8rem;">(Tidak ada data sesudah)</pre>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light border-0 py-3 px-4">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const detailButtons = document.querySelectorAll('.btn-detail-audit');

    function formatJsonData(raw) {
        if (!raw || raw.trim() === '' || raw === 'null') {
            return '(Kosong / Tidak ada data)';
        }
        try {
            const parsed = JSON.parse(raw);
            // Bersihkan field sensitif
            delete parsed.password;
            delete parsed.token;
            delete parsed.reset_token;
            return JSON.stringify(parsed, null, 2);
        } catch (e) {
            return raw;
        }
    }

    detailButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            document.getElementById('modal-log-id').textContent = 'Log ID: #' + this.getAttribute('data-id');
            document.getElementById('modal-user').textContent = this.getAttribute('data-user');
            document.getElementById('modal-role').textContent = this.getAttribute('data-role');
            document.getElementById('modal-aksi').textContent = this.getAttribute('data-aksi');
            
            const tabel = this.getAttribute('data-tabel');
            const record = this.getAttribute('data-record');
            document.getElementById('modal-tabel-record').textContent = tabel + (record && record !== '-' ? ' (ID: ' + record + ')' : '');

            document.getElementById('modal-ip').textContent = this.getAttribute('data-ip');
            document.getElementById('modal-ua').textContent = this.getAttribute('data-ua') || '-';
            document.getElementById('modal-waktu').textContent = this.getAttribute('data-waktu');

            document.getElementById('modal-data-lama').textContent = formatJsonData(this.getAttribute('data-lama'));
            document.getElementById('modal-data-baru').textContent = formatJsonData(this.getAttribute('data-baru'));
        });
    });
});
</script>
