<div class="dashboard-container">
    <style>
    .statistic-cards {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 18px !important;
        width: 100% !important;
    }
    .statistic-card {
        width: 100% !important;
        min-width: 0 !important;
        box-sizing: border-box !important;
        border-radius: 16px !important;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .statistic-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
    }
    @media (max-width: 576px) {
        .statistic-cards {
            gap: 12px !important;
        }
        .statistic-card {
            padding: 1.1rem !important;
        }
        .statistic-card h2 {
            font-size: 1.7rem !important;
        }
    }
    #kunjunganPeriodGroup .btn {
        border: none;
        color: #64748B;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    #kunjunganPeriodGroup .btn.active {
        background-color: var(--primary, #2563EB) !important;
        color: #FFFFFF !important;
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.25);
    }
    #kunjunganPeriodGroup .btn:hover:not(.active) {
        background-color: #E2E8F0;
        color: #1E293B;
    }
    </style>

    <!-- Header Dashboard & Quick Actions -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold text-dark mb-1">Dashboard</h4>
            <p class="text-muted small mb-0">Ringkasan informasi pelayanan ANTRIAN SEHAT</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <?php if (in_array($role, ['admin', 'petugas_pendaftaran', 'pasien'])): ?>
                <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-primary d-inline-flex align-items-center rounded-pill shadow-sm px-3">
                    <i class="fas fa-ticket-alt me-2"></i> Pendaftaran Pasien
                </a>
            <?php endif; ?>
            
            <?php if (in_array($role, ['admin', 'petugas_pelayanan'])): ?>
                <a href="<?= BASE_URL ?>/antrean/loket" class="btn btn-success d-inline-flex align-items-center rounded-pill shadow-sm px-3">
                    <i class="fas fa-bullhorn me-2"></i> Loket Pemanggilan
                </a>
            <?php endif; ?>

            <a href="<?= BASE_URL ?>/display" target="_blank" class="btn btn-outline-secondary d-inline-flex align-items-center rounded-pill shadow-sm px-3">
                <i class="fas fa-tv me-2"></i> Layar Antrean TV
            </a>
        </div>
    </div>

    <!-- KHUSUS PASIEN: Kartu Antrean Aktif Saya -->
    <?php if ($role === 'pasien'): ?>
        <?php if ($patientProfile): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 p-3 bg-white">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center me-3 fw-bold" style="width: 48px; height: 48px;">
                            <i class="fas fa-user-circle fs-3"></i>
                        </div>
                        <div>
                            <small class="text-muted d-block">Pasien Terdaftar</small>
                            <h6 class="fw-bold text-dark mb-0"><?= SecurityHelper::escape($patientProfile->nama_lengkap) ?></h6>
                        </div>
                    </div>
                    <div class="d-flex gap-4">
                        <div>
                            <small class="text-muted d-block">Nomor Rekam Medis</small>
                            <span class="badge bg-light text-dark border fw-bold"><?= SecurityHelper::escape($patientProfile->no_rekam_medis) ?></span>
                        </div>
                        <div>
                            <small class="text-muted d-block">NIK</small>
                            <span class="text-secondary fw-semibold"><?= SecurityHelper::escape($patientProfile->nik) ?></span>
                        </div>
                    </div>
                    <a href="<?= BASE_URL ?>/profile" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                        <i class="fas fa-user-pen me-1"></i> Kelola Profil
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($myQueue)): ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-primary text-white overflow-hidden position-relative">
                <div class="card-body p-4 position-relative z-1">
                    <div class="row align-items-center g-3">
                        <div class="col-md-3 text-center text-md-start border-end border-white-50">
                            <small class="text-white-50 text-uppercase tracking-wider d-block">Nomor Antrean Anda</small>
                            <h1 class="display-4 fw-bold mb-0 text-white"><?= htmlspecialchars($myQueue->no_antrean ?? $myQueue['no_antrean'], ENT_QUOTES, 'UTF-8') ?></h1>
                            <span class="badge bg-white text-primary fw-bold px-3 py-1 rounded-pill mt-2">
                                <?= ucfirst($myQueue->status ?? $myQueue['status']) ?>
                            </span>
                        </div>
                        <div class="col-md-6">
                            <h5 class="fw-bold mb-1"><?= htmlspecialchars($myQueue->nama_poli ?? $myQueue['nama_poli'], ENT_QUOTES, 'UTF-8') ?></h5>
                            <p class="mb-2 text-white-50">
                                <i class="fas fa-user-doctor me-1"></i> <?= htmlspecialchars($myQueue->nama_dokter ?? $myQueue['nama_dokter'], ENT_QUOTES, 'UTF-8') ?>
                            </p>
                            <div class="d-flex flex-wrap gap-3 small text-white-50">
                                <span><i class="fas fa-calendar-day me-1"></i> <?= Helper::formatTanggal($myQueue->tanggal_antrean ?? $myQueue['tanggal_antrean']) ?></span>
                                <span><i class="fas fa-clock me-1"></i> <?= Helper::formatWaktu($myQueue->created_at ?? $myQueue['created_at']) ?></span>
                            </div>
                        </div>
                        <div class="col-md-3 text-center text-md-end">
                            <div class="bg-white bg-opacity-25 rounded-3 p-3 text-center">
                                <small class="d-block text-white-50 mb-1">Estimasi Waktu Tunggu</small>
                                <span class="fs-4 fw-bold">~15 Menit</span>
                                <small class="d-block text-white-50 mt-1">Harap bersiap di dekat poli</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-light p-4 text-center">
                <div class="py-2">
                    <i class="fas fa-ticket-alt text-primary fs-1 mb-2"></i>
                    <h5 class="fw-bold mb-1">Anda Belum Mengambil Nomor Antrean Hari Ini</h5>
                    <p class="text-muted small mb-3">Daftarkan kunjungan Anda ke poli tujuan untuk mendapatkan nomor antrean dan estimasi waktu pelayanan.</p>
                    <a href="<?= BASE_URL ?>/pendaftaran/create" class="btn btn-primary rounded-pill px-4">
                        <i class="fas fa-calendar-plus me-1"></i> Ambil Nomor Antrean Sekarang
                    </a>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <!-- 4 KARTU STATISTIK UTAMA (GRID 2x2 BERWARNA SESUAI DESAIN ANTRIAN SEHAT) -->
    <div class="statistic-cards mb-4" style="display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 18px !important; width: 100% !important;">
        <!-- BARIS PERTAMA: Total Pengguna | Total Pasien -->
        <div class="card border-0 shadow-sm rounded-4 p-4 statistic-card" style="width: 100% !important; min-width: 0 !important; box-sizing: border-box !important; background-color: #EEF4FF; border: 1px solid #E0E7FF !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <i class="fas fa-users fs-4" style="color: #2563EB;"></i>
                <span class="fw-bold small" style="color: #2563EB;">Pengguna</span>
            </div>
            <h2 class="fw-bold mb-1" style="color: #1D4ED8; font-size: 2.3rem;"><?= number_format((int)($totalPengguna ?? 0)) ?></h2>
            <div class="text-secondary small fw-medium">Total Pengguna</div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 statistic-card" style="width: 100% !important; min-width: 0 !important; box-sizing: border-box !important; background-color: #ECFDF5; border: 1px solid #D1FAE5 !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <i class="fas fa-user fs-4" style="color: #10B981;"></i>
                <span class="fw-bold small" style="color: #047857;">Pasien</span>
            </div>
            <h2 class="fw-bold mb-1" style="color: #047857; font-size: 2.3rem;"><?= number_format((int)($totalPasien ?? 0)) ?></h2>
            <div class="text-secondary small fw-medium">Total Pasien</div>
        </div>

        <!-- BARIS KEDUA: Total Petugas | Antrean Menunggu -->
        <div class="card border-0 shadow-sm rounded-4 p-4 statistic-card" style="width: 100% !important; min-width: 0 !important; box-sizing: border-box !important; background-color: #F5F3FF; border: 1px solid #EDE9FE !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <i class="fas fa-stethoscope fs-4" style="color: #7C3AED;"></i>
                <span class="fw-bold small" style="color: #7C3AED;">Petugas</span>
            </div>
            <h2 class="fw-bold mb-1" style="color: #6D28D9; font-size: 2.3rem;"><?= number_format((int)($totalPetugas ?? 0)) ?></h2>
            <div class="text-secondary small fw-medium">Total Petugas</div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 p-4 statistic-card" style="width: 100% !important; min-width: 0 !important; box-sizing: border-box !important; background-color: #FFFBEB; border: 1px solid #FEF3C7 !important;">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <i class="far fa-clock fs-4" style="color: #D97706;"></i>
                <span class="fw-bold small" style="color: #D97706;">Antrean</span>
            </div>
            <h2 class="fw-bold mb-1" style="color: #B45309; font-size: 2.3rem;"><?= number_format((int)($stats['dalam_antrean'] ?? 0)) ?></h2>
            <div class="text-secondary small fw-medium">Antrean Menunggu</div>
        </div>
    </div>

    <!-- GRAFIK KUNJUNGAN PASIEN (FULL WIDTH - BACKGROUND PUTIH BERSIH) -->
    <?php if ($role !== 'pasien'): ?>
        <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-2">
                <div>
                    <h5 class="fw-bold text-dark mb-1">Grafik Kunjungan Pasien</h5>
                    <p class="text-muted small mb-0">Visualisasi kunjungan harian, mingguan, atau bulanan</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge rounded-pill px-3 py-1 fw-semibold" style="background-color: #D1FAE5; color: #047857; font-size: 0.8rem;">
                        <i class="fas fa-circle me-1" style="font-size: 0.45rem;"></i> Grafik utama
                    </span>
                    <div class="btn-group btn-group-sm rounded-pill p-1 bg-light border ms-1" role="group" id="kunjunganPeriodGroup">
                        <button type="button" class="btn btn-sm rounded-pill active px-3" data-period="daily">Harian</button>
                        <button type="button" class="btn btn-sm rounded-pill px-3" data-period="weekly">Mingguan</button>
                        <button type="button" class="btn btn-sm rounded-pill px-3" data-period="monthly">Bulanan</button>
                    </div>
                </div>
            </div>

            <div style="height: 330px; position: relative;">
                <canvas id="kunjunganMainChart"></canvas>
            </div>
        </div>

        <!-- 2 GRAFIK BERDAMPINGAN: DISTRIBUSI ANTREAN & KUNJUNGAN PER POLI (BACKGROUND PUTIH) -->
        <div class="row g-4 mb-4">
            <!-- 1. Distribusi Antrean (Kiri) -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Distribusi Antrean</h5>
                    </div>

                    <!-- Legenda dengan Lingkaran Warna -->
                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-2" style="width: 10px; height: 10px; background-color: #3B82F6;"></span>
                                <span class="text-secondary fw-medium">Menunggu</span>
                            </div>
                            <strong class="text-dark"><?= (int)($stats['dalam_antrean'] ?? 0) ?></strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-2" style="width: 10px; height: 10px; background-color: #10B981;"></span>
                                <span class="text-secondary fw-medium">Selesai</span>
                            </div>
                            <strong class="text-dark"><?= (int)($stats['selesai'] ?? 0) ?></strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-2" style="width: 10px; height: 10px; background-color: #F59E0B;"></span>
                                <span class="text-secondary fw-medium">Dipanggil</span>
                            </div>
                            <strong class="text-dark"><?= (int)($stats['dipanggil'] ?? 0) ?></strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-2" style="width: 10px; height: 10px; background-color: #8B5CF6;"></span>
                                <span class="text-secondary fw-medium">Sedang Dilayani</span>
                            </div>
                            <strong class="text-dark"><?= (int)($stats['sedang_dilayani'] ?? 0) ?></strong>
                        </div>
                        <div class="d-flex align-items-center justify-content-between small">
                            <div class="d-flex align-items-center">
                                <span class="rounded-circle me-2" style="width: 10px; height: 10px; background-color: #EF4444;"></span>
                                <span class="text-secondary fw-medium">Dibatalkan</span>
                            </div>
                            <strong class="text-dark"><?= (int)($stats['dibatalkan'] ?? 0) ?></strong>
                        </div>
                    </div>

                    <!-- Doughnut Chart Mini -->
                    <div class="d-flex justify-content-center align-items-center mt-auto" style="height: 180px; position: relative;">
                        <canvas id="antreanDoughnutChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- 2. Kunjungan per Poli (Kanan - Horizontal Rounded Bars) -->
            <div class="col-12 col-md-6">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="fw-bold text-dark mb-0">Kunjungan per Poli</h5>
                    </div>

                    <!-- Horizontal Rounded Bars -->
                    <div class="d-flex flex-column justify-content-center h-100" style="min-height: 250px;">
                        <?php 
                        $palette = ['#3B82F6', '#10B981', '#F59E0B', '#8B5CF6', '#EF4444', '#06B6D4'];
                        $idx = 0;
                        $maxVisits = 1;
                        if (!empty($poliList)) {
                            foreach ($poliList as $pl) {
                                $v = (int)($pl->antrean_hari_ini ?? 0);
                                if ($v > $maxVisits) $maxVisits = $v;
                            }
                        }
                        ?>
                        <?php if (!empty($poliList)): ?>
                            <?php foreach ($poliList as $p): ?>
                                <?php 
                                    $color = $palette[$idx % count($palette)];
                                    $val = (int)($p->antrean_hari_ini ?? 0);
                                    $pct = max(10, min(100, round(($val / $maxVisits) * 100)));
                                    $idx++;
                                ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <span class="small fw-semibold text-dark"><?= SecurityHelper::escape($p->nama_poli ?? $p['nama_poli']) ?></span>
                                        <span class="small text-secondary fw-bold"><?= $val ?> Pasien</span>
                                    </div>
                                    <div class="progress rounded-pill" style="height: 12px; background-color: #F1F5F9;">
                                        <div class="progress-bar rounded-pill" role="progressbar" 
                                             style="width: <?= $pct ?>%; background-color: <?= $color ?>;" 
                                             aria-valuenow="<?= $val ?>" aria-valuemin="0" aria-valuemax="<?= $maxVisits ?>">
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted small text-center my-auto">Belum ada data kunjungan poli hari ini.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFORMASI TAMBAHAN: ANTREAN TERBARU & AKTIVITAS -->
        <div class="row g-4">
            <!-- Antrean Hari Ini -->
            <div class="col-12 col-lg-7">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Antrean Hari Ini</h5>
                            <small class="text-muted">Daftar status pemanggilan pasien yang sedang berjalan</small>
                        </div>
                        <a href="<?= BASE_URL ?>/antrean" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            Lihat Semua <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr class="text-muted small">
                                    <th style="width: 45px;">No</th>
                                    <th>No. Antrean</th>
                                    <th>Nama Pasien</th>
                                    <th>Poli</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($antreanSaatIni)): ?>
                                    <?php $no = 1; foreach ($antreanSaatIni as $a): ?>
                                        <tr>
                                            <td class="text-muted small"><?= $no++ ?></td>
                                            <td>
                                                <span class="badge bg-primary fs-6 fw-bold px-2 py-1">
                                                    <?= SecurityHelper::escape($a->no_antrean ?? $a['no_antrean']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark d-block"><?= SecurityHelper::escape($a->nama_pasien ?? $a['nama_pasien']) ?></span>
                                                <small class="text-muted"><?= SecurityHelper::escape($a->no_rekam_medis ?? $a['no_rekam_medis'] ?? '') ?></small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border"><?= SecurityHelper::escape($a->nama_poli ?? $a['nama_poli']) ?></span>
                                            </td>
                                            <td class="text-center">
                                                <?= Helper::statusBadge($a->status ?? $a['status']) ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            Belum ada data antrean untuk hari ini.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Aktivitas Terbaru -->
            <div class="col-12 col-lg-5">
                <div class="card border-0 shadow-sm rounded-4 h-100 p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Aktivitas Terbaru</h5>
                            <small class="text-muted">Catatan audit log sistem terkini</small>
                        </div>
                        <?php if ($role === 'admin'): ?>
                            <a href="<?= BASE_URL ?>/audit" class="btn btn-sm btn-outline-secondary rounded-pill px-3">Audit Log</a>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($recentActivity)): ?>
                        <div class="list-group list-group-flush border-0">
                            <?php foreach ($recentActivity as $act): ?>
                                <div class="list-group-item d-flex align-items-center px-0 py-2 border-light">
                                    <div class="rounded-circle bg-light text-primary d-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width: 36px; height: 36px;">
                                        <i class="fas fa-bolt fs-6"></i>
                                    </div>
                                    <div class="flex-grow-1 overflow-hidden">
                                        <span class="fw-semibold text-dark text-truncate d-block small"><?= SecurityHelper::escape($act->aksi ?? $act['aksi']) ?></span>
                                        <small class="text-muted" style="font-size: 0.75rem;">
                                            <?= SecurityHelper::escape($act->username ?? $act['username']) ?> &bull; <?= Helper::formatWaktu($act->created_at ?? $act['created_at']) ?>
                                        </small>
                                    </div>
                                    <span class="badge bg-light text-secondary border ms-2" style="font-size: 0.7rem;">
                                        <?= Helper::getRoleLabel($act->role ?? $act['role'] ?? '') ?>
                                    </span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-0 py-3 text-center">Belum ada aktivitas tercatat.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <!-- KHUSUS PASIEN: Riwayat Kunjungan -->
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">Riwayat Kunjungan Saya</h5>
                <a href="<?= BASE_URL ?>/riwayat" class="btn btn-sm btn-outline-primary rounded-pill px-3">Lihat Semua</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="text-muted small">
                            <th>Tanggal</th>
                            <th>No. Antrean</th>
                            <th>Poli Tujuan</th>
                            <th>Dokter</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($myHistory)): ?>
                            <?php foreach ($myHistory as $visit): ?>
                                <tr>
                                    <td><?= SecurityHelper::escape(Helper::formatTanggal($visit->tanggal_antrean)) ?></td>
                                    <td><span class="badge bg-primary px-2 py-1"><?= SecurityHelper::escape($visit->no_antrean) ?></span></td>
                                    <td><?= SecurityHelper::escape($visit->nama_poli) ?></td>
                                    <td><?= SecurityHelper::escape($visit->nama_dokter) ?></td>
                                    <td class="text-center"><?= Helper::statusBadge($visit->status) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat kunjungan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- INISIALISASI CHART.JS DENGAN TEMA PUTIH BERSIH -->
<?php if ($role !== 'pasien'): ?>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. DATA GRAFIK KUNJUNGAN PASIEN
    const kunjunganDatasets = <?= json_encode($chartKunjunganPeriods ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

    const ctxMain = document.getElementById('kunjunganMainChart');
    let mainChart = null;

    if (ctxMain) {
        const chartCtx = ctxMain.getContext('2d');
        const gradient = chartCtx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.40)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0.02)');

        const initialData = kunjunganDatasets.daily || { labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'], data: [0, 0, 0, 0, 0, 0, 0] };

        mainChart = new Chart(ctxMain, {
            type: 'line',
            data: {
                labels: initialData.labels,
                datasets: [{
                    label: 'Jumlah Kunjungan Pasien',
                    data: initialData.data,
                    borderColor: '#3B82F6',
                    borderWidth: 3.5,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.25,
                    pointBackgroundColor: '#3B82F6',
                    pointBorderColor: '#FFFFFF',
                    pointBorderWidth: 2.5,
                    pointRadius: 6,
                    pointHoverRadius: 8,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { family: 'Inter', size: 12 },
                        bodyFont: { family: 'Inter', size: 13, weight: 'bold' },
                        callbacks: {
                            label: function(context) {
                                return context.parsed.y + ' Pasien';
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { 
                            color: '#E2E8F0', 
                            borderDash: [5, 5],
                            drawBorder: false 
                        },
                        ticks: { color: '#64748B', font: { family: 'Inter', size: 11 }, precision: 0 }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#64748B', font: { family: 'Inter', size: 12, weight: '500' } }
                    }
                }
            }
        });

        // Filter Switcher Buttons (Harian, Mingguan, Bulanan)
        const periodButtons = document.querySelectorAll('#kunjunganPeriodGroup button');
        periodButtons.forEach(btn => {
            btn.addEventListener('click', function() {
                periodButtons.forEach(b => b.classList.remove('active', 'btn-primary'));
                this.classList.add('active');

                const period = this.getAttribute('data-period');
                const target = kunjunganDatasets[period];
                if (target && mainChart) {
                    mainChart.data.labels = target.labels;
                    mainChart.data.datasets[0].data = target.data;
                    mainChart.update();
                }
            });
        });
    }

    // 2. GRAFIK DISTRIBUSI STATUS ANTREAN (Doughnut Chart)
    const ctxDoughnut = document.getElementById('antreanDoughnutChart');
    if (ctxDoughnut) {
        const distData = <?= json_encode($chartDistribution ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;

        new Chart(ctxDoughnut, {
            type: 'doughnut',
            data: {
                labels: distData.labels || ['Menunggu', 'Dipanggil', 'Dilayani', 'Selesai', 'Batal'],
                datasets: [{
                    data: distData.data || [0, 0, 0, 0, 0],
                    backgroundColor: ['#3B82F6', '#F59E0B', '#8B5CF6', '#10B981', '#EF4444'],
                    borderWidth: 2,
                    borderColor: '#FFFFFF',
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#0F172A',
                        padding: 10,
                        cornerRadius: 8,
                        titleFont: { family: 'Inter', size: 11 },
                        bodyFont: { family: 'Inter', size: 12, weight: 'bold' }
                    }
                }
            }
        });
    }
});
</script>
<?php endif; ?>
