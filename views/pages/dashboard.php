<?php
$dashboardRoles = [
    'admin' => 'Administrator',
    'registration' => 'Petugas Pendaftaran',
    'service' => 'Petugas Pelayanan',
    'patient' => 'Pasien',
];
$roleAliases = [
    'admin' => 'admin',
    'petugas_pendaftaran' => 'registration',
    'petugas_pelayanan' => 'service',
    'pasien' => 'patient',
];
$sessionRole = $role ?? null;
$hasAuthenticatedRole = $sessionRole !== null && isset($roleAliases[$sessionRole]);
$activeRole = $hasAuthenticatedRole ? $roleAliases[$sessionRole] : ($previewRole ?? $_GET['role'] ?? 'admin');
if (!isset($dashboardRoles[$activeRole])) {
    $activeRole = 'admin';
}
$activeRoleLabel = $dashboardRoles[$activeRole];
$dashboardStats = $stats ?? [];
$patientTotal = (int) ($totalPasien ?? 2486);
$todayTotal = (int) ($dashboardStats['total_hari_ini'] ?? 86);
$waitingTotal = (int) ($dashboardStats['dalam_antrean'] ?? 18);
$servingTotal = (int) ($dashboardStats['sedang_dilayani'] ?? 12);
$completedTotal = (int) ($dashboardStats['selesai'] ?? 56);
$readValue = static function ($record, string $key, $fallback = '') {
    if (is_array($record)) {
        return $record[$key] ?? $fallback;
    }
    if (is_object($record)) {
        return $record->$key ?? $fallback;
    }
    return $fallback;
};
?>

<div class="dashboard-page" data-dashboard data-active-role="<?= SecurityHelper::escape($activeRole) ?>">
    <aside class="app-sidebar" id="appSidebar" aria-label="Navigasi utama">
        <div class="sidebar-topline">
            <a class="brand-lockup brand-lockup-sidebar" href="<?= SecurityHelper::escape(UrlHelper::to()) ?>">
                <span class="brand-symbol"><i class="bi bi-heart-pulse-fill" aria-hidden="true"></i></span>
                <span class="brand-copy"><strong>ANTRIAN SEHAT</strong><small>LAYANAN PUSKESMAS</small></span>
            </a>
            <button type="button" class="icon-button sidebar-collapse" data-sidebar-collapse aria-label="Ciutkan sidebar" title="Ciutkan sidebar"><i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i></button>
        </div>

        <div class="sidebar-section-label">MENU UTAMA</div>
        <nav class="sidebar-navigation">
            <a class="sidebar-link is-active" href="?role=<?= SecurityHelper::escape($activeRole) ?>"><i class="bi bi-grid-1x2" aria-hidden="true"></i><span>Dashboard</span></a>
            <a class="sidebar-link" href="#pasien" data-module="patients"><i class="bi bi-people" aria-hidden="true"></i><span>Data Pasien</span></a>
            <a class="sidebar-link" href="#pendaftaran" data-module="registration"><i class="bi bi-clipboard2-plus" aria-hidden="true"></i><span>Pendaftaran</span><span class="sidebar-count">12</span></a>
            <a class="sidebar-link" href="#antrean" data-module="queue"><i class="bi bi-list-ol" aria-hidden="true"></i><span>Antrean</span></a>
            <a class="sidebar-link" href="#poli" data-module="clinics"><i class="bi bi-hospital" aria-hidden="true"></i><span>Data Poli</span></a>
            <a class="sidebar-link" href="#dokter" data-module="doctors"><i class="bi bi-person-vcard" aria-hidden="true"></i><span>Data Dokter</span></a>
            <a class="sidebar-link" href="#riwayat" data-module="history"><i class="bi bi-clock-history" aria-hidden="true"></i><span>Riwayat Kunjungan</span></a>
            <a class="sidebar-link" href="#laporan" data-module="reports"><i class="bi bi-bar-chart-line" aria-hidden="true"></i><span>Laporan</span></a>
        </nav>

        <div class="sidebar-section-label sidebar-section-spaced">LAINNYA</div>
        <nav class="sidebar-navigation">
            <a class="sidebar-link" href="#notifikasi" data-module="notifications"><i class="bi bi-bell" aria-hidden="true"></i><span>Notifikasi</span><span class="sidebar-unread" aria-label="3 notifikasi belum dibaca"></span></a>
            <a class="sidebar-link" href="#pengaturan" data-module="settings"><i class="bi bi-gear" aria-hidden="true"></i><span>Pengaturan</span></a>
        </nav>

        <div class="sidebar-bottom">
            <div class="clinic-status"><span class="status-pulse"></span><span><strong>Layanan aktif</strong><small>Puskesmas Sehat Sentosa</small></span></div>
            <button class="sidebar-user" type="button" data-module="profile">
                <span class="avatar avatar-sidebar">NP</span>
                <span class="sidebar-user-copy"><strong>Nadia Putri</strong><small><?= SecurityHelper::escape($activeRoleLabel) ?></small></span>
                <i class="bi bi-three-dots" aria-hidden="true"></i>
            </button>
        </div>
    </aside>

    <div class="dashboard-main">
        <header class="topbar">
            <div class="topbar-left">
                <button type="button" class="icon-button mobile-menu-button" data-sidebar-open aria-label="Buka navigasi"><i class="bi bi-list" aria-hidden="true"></i></button>
                <div class="breadcrumb-line"><span>ANTRIAN SEHAT</span><i class="bi bi-chevron-right" aria-hidden="true"></i><strong>Dashboard</strong></div>
            </div>
            <div class="topbar-actions">
                <label class="topbar-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Cari pasien, antrean..." aria-label="Cari pasien atau antrean" data-table-search><kbd>⌘ K</kbd></label>
                <div class="dropdown">
                    <button class="icon-button notification-button" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi"><i class="bi bi-bell" aria-hidden="true"></i><span class="notification-dot"></span></button>
                    <div class="dropdown-menu dropdown-menu-end notification-menu">
                        <div class="dropdown-heading"><strong>Notifikasi</strong><span>3 baru</span></div>
                        <a class="notification-item" href="#notifikasi" data-module="registration"><span class="notification-icon notification-blue"><i class="bi bi-person-plus" aria-hidden="true"></i></span><span><strong>Pendaftaran baru</strong><small>4 pasien mendaftar hari ini</small></span><time>09.42</time></a>
                        <a class="notification-item" href="#notifikasi" data-module="history"><span class="notification-icon notification-green"><i class="bi bi-check2-circle" aria-hidden="true"></i></span><span><strong>Kunjungan selesai</strong><small>Poli Umum · 8 menit lalu</small></span><time>09.31</time></a>
                        <a class="notification-all" href="#notifikasi" data-module="notifications">Lihat semua notifikasi <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
                <span class="topbar-date"><i class="bi bi-calendar3" aria-hidden="true"></i><span data-current-date></span></span>
                <div class="dropdown profile-dropdown">
                    <button class="profile-trigger" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <span class="avatar">NP</span><span class="profile-trigger-copy"><strong>Nadia Putri</strong><small><?= SecurityHelper::escape($activeRoleLabel) ?></small></span><i class="bi bi-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <?php if (!$hasAuthenticatedRole): ?>
                        <span class="dropdown-user-label">PRATINJAU DASHBOARD</span>
                        <?php foreach ($dashboardRoles as $roleKey => $roleName): ?>
                            <a class="dropdown-item <?= $activeRole === $roleKey ? 'active' : '' ?>" href="?role=<?= SecurityHelper::escape($roleKey) ?>"><?= SecurityHelper::escape($roleName) ?></a>
                        <?php endforeach; ?>
                        <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="<?= SecurityHelper::escape(UrlHelper::to('login')) ?>"><i class="bi bi-box-arrow-in-right me-2" aria-hidden="true"></i>Ke halaman masuk</a>
                        <?php else: ?>
                            <span class="dropdown-user-label">AKUN SAYA</span>
                            <a class="dropdown-item" href="#profil" data-module="profile"><i class="bi bi-person-circle me-2" aria-hidden="true"></i>Profil saya</a>
                            <form method="post" action="<?= SecurityHelper::escape(UrlHelper::to('logout')) ?>">
                                <?= SecurityHelper::csrfField() ?>
                                <button class="dropdown-item w-100 text-start" type="submit"><i class="bi bi-box-arrow-right me-2" aria-hidden="true"></i>Keluar</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <main class="dashboard-content">
            <div class="dashboard-heading">
                <div><span class="eyebrow">RINGKASAN HARIAN</span><h1>Dashboard</h1><p>Pantau aktivitas dan pelayanan Puskesmas hari ini.</p></div>
                <div class="heading-actions"><span class="prototype-badge"><i class="bi bi-eye" aria-hidden="true"></i> Pratinjau UI</span><button type="button" class="btn btn-primary" data-open-registration><i class="bi bi-plus-lg" aria-hidden="true"></i><span>Pendaftaran Baru</span></button></div>
            </div>

            <?php if (!$hasAuthenticatedRole): ?>
            <div class="role-tabs" role="tablist" aria-label="Pilih tampilan dashboard">
                <?php foreach ($dashboardRoles as $roleKey => $roleName): ?>
                    <a class="role-tab <?= $activeRole === $roleKey ? 'active' : '' ?>" role="tab" aria-selected="<?= $activeRole === $roleKey ? 'true' : 'false' ?>" href="?role=<?= SecurityHelper::escape($roleKey) ?>"><?= SecurityHelper::escape($roleName) ?></a>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <section class="role-panel <?= $activeRole === 'admin' ? 'is-visible' : '' ?>" data-role-panel="admin" <?= $activeRole === 'admin' ? '' : 'hidden' ?> aria-label="Dashboard Administrator">
                <div class="stat-grid stat-grid-five">
                    <article class="stat-card"><div class="stat-card-top"><span>Total Pasien</span><span class="stat-icon stat-icon-blue"><i class="bi bi-people" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($patientTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="trend-up"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> 8,2%</span><span>dari bulan lalu</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Pendaftaran Hari Ini</span><span class="stat-icon stat-icon-violet"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($todayTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="trend-up"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> 12,5%</span><span>dari kemarin</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Antrean Menunggu</span><span class="stat-icon stat-icon-amber"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($waitingTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="stat-neutral">6 poli aktif</span><span>per saat ini</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Sedang Dilayani</span><span class="stat-icon stat-icon-cyan"><i class="bi bi-activity" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($servingTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="stat-neutral">4 dokter</span><span>sedang praktik</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Pasien Selesai</span><span class="stat-icon stat-icon-green"><i class="bi bi-check2-circle" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($completedTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="trend-up"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> 92%</span><span>dari pendaftaran</span></div></article>
                </div>

                <div class="analytics-grid">
                    <article class="surface-card chart-card">
                        <div class="surface-heading"><div><h2>Tren kunjungan pasien</h2><p>Jumlah kunjungan selama 7 hari terakhir</p></div><button class="select-button" type="button" data-chart-range>7 hari <i class="bi bi-chevron-down" aria-hidden="true"></i></button></div>
                        <div class="chart-summary"><strong>428</strong><span>kunjungan</span><span class="trend-up"><i class="bi bi-arrow-up-right" aria-hidden="true"></i> 6,4%</span><small>vs. minggu lalu</small></div>
                        <div class="chart-wrap"><canvas data-chart="visits" aria-label="Grafik tren kunjungan pasien tujuh hari terakhir"></canvas></div>
                    </article>
                    <article class="surface-card chart-card poli-chart-card">
                        <div class="surface-heading"><div><h2>Kunjungan berdasarkan poli</h2><p>Distribusi hari ini</p></div><button class="icon-button small-icon-button" type="button" aria-label="Lihat rincian poli" data-module="clinics"><i class="bi bi-three-dots" aria-hidden="true"></i></button></div>
                        <div class="poli-chart-layout"><div class="doughnut-wrap"><canvas data-chart="poli" aria-label="Grafik distribusi kunjungan tiap poli"></canvas><div class="doughnut-center"><strong>86</strong><small>kunjungan</small></div></div><div class="chart-legend"><div><span class="legend-dot dot-blue"></span><span>Umum</span><strong>38</strong></div><div><span class="legend-dot dot-teal"></span><span>Gigi</span><strong>19</strong></div><div><span class="legend-dot dot-amber"></span><span>Anak</span><strong>16</strong></div><div><span class="legend-dot dot-violet"></span><span>KIA & lainnya</span><strong>13</strong></div></div></div>
                    </article>
                </div>

                <div class="content-grid admin-bottom-grid">
                    <article class="surface-card table-card">
                        <div class="surface-heading"><div><h2>Antrean terbaru</h2><p>Perubahan antrean hari ini</p></div><a class="text-link" href="#antrean" data-module="queue">Lihat semua <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div>
                        <div class="table-responsive"><table class="table app-table" data-search-table><thead><tr><th>NO. ANTREAN</th><th>PASIEN</th><th>POLI</th><th>WAKTU</th><th>STATUS</th></tr></thead><tbody>
                            <tr><td><span class="queue-code">U-056</span></td><td><span class="table-person"><span class="avatar avatar-soft-blue">RA</span><span><strong>Rina Amelia</strong><small>RM-002184</small></span></span></td><td>Poli Umum</td><td>09.42</td><td><span class="status-pill status-waiting">Menunggu</span></td></tr>
                            <tr><td><span class="queue-code">G-019</span></td><td><span class="table-person"><span class="avatar avatar-soft-teal">DS</span><span><strong>Dimas Saputra</strong><small>RM-001967</small></span></span></td><td>Poli Gigi</td><td>09.38</td><td><span class="status-pill status-serving">Dilayani</span></td></tr>
                            <tr><td><span class="queue-code">A-014</span></td><td><span class="table-person"><span class="avatar avatar-soft-amber">NP</span><span><strong>Nadia Putri</strong><small>RM-002403</small></span></span></td><td>Poli Anak</td><td>09.31</td><td><span class="status-pill status-done">Selesai</span></td></tr>
                            <tr><td><span class="queue-code">K-009</span></td><td><span class="table-person"><span class="avatar avatar-soft-violet">SA</span><span><strong>Siti Aisyah</strong><small>RM-001825</small></span></span></td><td>Poli KIA</td><td>09.24</td><td><span class="status-pill status-waiting">Menunggu</span></td></tr>
                        </tbody></table></div>
                    </article>
                    <article class="surface-card activity-card">
                        <div class="surface-heading"><div><h2>Aktivitas terbaru</h2><p>Aktivitas pengguna</p></div><button class="icon-button small-icon-button" type="button" aria-label="Lihat riwayat kunjungan" data-module="history"><i class="bi bi-three-dots" aria-hidden="true"></i></button></div>
                        <div class="activity-list">
                            <div class="activity-item"><span class="activity-icon activity-blue"><i class="bi bi-person-plus" aria-hidden="true"></i></span><div><strong>Pasien baru didaftarkan</strong><p>Rina Amelia · oleh Siti Rahma</p><time>09.42</time></div></div>
                            <div class="activity-item"><span class="activity-icon activity-green"><i class="bi bi-check2" aria-hidden="true"></i></span><div><strong>Kunjungan diselesaikan</strong><p>Poli Umum · dr. Budi Santoso</p><time>09.31</time></div></div>
                            <div class="activity-item"><span class="activity-icon activity-amber"><i class="bi bi-megaphone" aria-hidden="true"></i></span><div><strong>Antrean dipanggil</strong><p>G-019 · Poli Gigi</p><time>09.24</time></div></div>
                        </div>
                    </article>
                </div>
            </section>

            <section class="role-panel <?= $activeRole === 'registration' ? 'is-visible' : '' ?>" data-role-panel="registration" <?= $activeRole === 'registration' ? '' : 'hidden' ?> aria-label="Dashboard Petugas Pendaftaran">
                <div class="role-context"><span class="role-context-icon"><i class="bi bi-clipboard2-plus" aria-hidden="true"></i></span><div><strong>Meja Pendaftaran</strong><span>Loket 01 <i class="status-pulse"></i> Buka</span></div><a href="#pendaftaran" class="btn btn-primary ms-auto" data-open-registration><i class="bi bi-plus-lg" aria-hidden="true"></i> Pendaftaran Baru</a></div>
                <div class="stat-grid stat-grid-four">
                    <article class="stat-card"><div class="stat-card-top"><span>Total Pasien Terdaftar</span><span class="stat-icon stat-icon-blue"><i class="bi bi-people" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($patientTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="trend-up">+24</span><span>bulan ini</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Pendaftaran Hari Ini</span><span class="stat-icon stat-icon-violet"><i class="bi bi-journal-check" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($todayTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="stat-neutral">Kuota terpakai 72%</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Pasien Baru</span><span class="stat-icon stat-icon-cyan"><i class="bi bi-person-add" aria-hidden="true"></i></span></div><div class="stat-value">14</div><div class="stat-foot"><span class="stat-neutral">16% dari hari ini</span></div></article>
                    <article class="stat-card"><div class="stat-card-top"><span>Antrean Menunggu</span><span class="stat-icon stat-icon-amber"><i class="bi bi-hourglass" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($waitingTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="stat-neutral">Di seluruh poli</span></div></article>
                </div>
                <article class="surface-card table-card section-gap">
                    <div class="surface-heading"><div><h2>Pendaftaran terbaru</h2><p>Daftar pasien yang mendaftar hari ini</p></div><div class="table-tools"><label class="table-search"><i class="bi bi-search" aria-hidden="true"></i><input type="search" placeholder="Cari pasien..." aria-label="Cari pendaftaran" data-table-search></label><button class="select-button" type="button" data-date-filter>Hari ini <i class="bi bi-chevron-down" aria-hidden="true"></i></button></div></div>
                    <div class="table-responsive"><table class="table app-table" data-search-table><thead><tr><th>PASIEN</th><th>NOMOR RM</th><th>POLI / DOKTER</th><th>JAM DAFTAR</th><th>NO. ANTREAN</th><th>STATUS</th><th></th></tr></thead><tbody>
                        <tr><td><span class="table-person"><span class="avatar avatar-soft-blue">RA</span><span><strong>Rina Amelia</strong><small>Perempuan · 29 th</small></span></span></td><td>RM-002184</td><td><strong>Poli Umum</strong><small class="table-subline">dr. Budi Santoso</small></td><td>09.42</td><td><span class="queue-code">U-056</span></td><td><span class="status-pill status-waiting">Menunggu</span></td><td><button class="icon-button small-icon-button" aria-label="Aksi Rina Amelia" data-toast="Aksi pendaftaran belum terhubung."><i class="bi bi-three-dots" aria-hidden="true"></i></button></td></tr>
                        <tr><td><span class="table-person"><span class="avatar avatar-soft-teal">DS</span><span><strong>Dimas Saputra</strong><small>Laki-laki · 42 th</small></span></span></td><td>RM-001967</td><td><strong>Poli Gigi</strong><small class="table-subline">drg. Dewi Lestari</small></td><td>09.38</td><td><span class="queue-code queue-teal">G-019</span></td><td><span class="status-pill status-serving">Dilayani</span></td><td><button class="icon-button small-icon-button" aria-label="Aksi Dimas Saputra" data-toast="Aksi pendaftaran belum terhubung."><i class="bi bi-three-dots" aria-hidden="true"></i></button></td></tr>
                        <tr><td><span class="table-person"><span class="avatar avatar-soft-amber">NP</span><span><strong>Nadia Putri</strong><small>Perempuan · 8 th</small></span></span></td><td>RM-002403</td><td><strong>Poli Anak</strong><small class="table-subline">dr. Andi Pratama</small></td><td>09.31</td><td><span class="queue-code queue-amber">A-014</span></td><td><span class="status-pill status-done">Selesai</span></td><td><button class="icon-button small-icon-button" aria-label="Aksi Nadia Putri" data-toast="Aksi pendaftaran belum terhubung."><i class="bi bi-three-dots" aria-hidden="true"></i></button></td></tr>
                        <tr><td><span class="table-person"><span class="avatar avatar-soft-violet">SA</span><span><strong>Siti Aisyah</strong><small>Perempuan · 34 th</small></span></span></td><td>RM-001825</td><td><strong>Poli KIA</strong><small class="table-subline">dr. Nurhayati</small></td><td>09.24</td><td><span class="queue-code queue-violet">K-009</span></td><td><span class="status-pill status-waiting">Menunggu</span></td><td><button class="icon-button small-icon-button" aria-label="Aksi Siti Aisyah" data-toast="Aksi pendaftaran belum terhubung."><i class="bi bi-three-dots" aria-hidden="true"></i></button></td></tr>
                    </tbody></table></div>
                    <div class="table-pagination"><span data-pagination-label>Menampilkan 4 dari 86 pendaftaran</span><div><button class="pagination-button" aria-label="Halaman sebelumnya" disabled><i class="bi bi-chevron-left" aria-hidden="true"></i></button><button class="pagination-button" aria-label="Halaman berikutnya" data-pagination-next><i class="bi bi-chevron-right" aria-hidden="true"></i></button></div></div>
                </article>
            </section>

            <section class="role-panel <?= $activeRole === 'service' ? 'is-visible' : '' ?>" data-role-panel="service" <?= $activeRole === 'service' ? '' : 'hidden' ?> aria-label="Dashboard Petugas Pelayanan">
                <div class="service-toolbar"><div class="service-poli"><span class="stat-icon stat-icon-blue"><i class="bi bi-hospital" aria-hidden="true"></i></span><span><strong>Poli Umum</strong><small>dr. Budi Santoso · 08.00–12.00</small></span><button class="select-button" type="button" data-toast="Daftar poli belum terhubung.">Ganti poli <i class="bi bi-chevron-down" aria-hidden="true"></i></button></div><span class="service-live"><span class="status-pulse"></span> Pelayanan berlangsung</span></div>
                <div class="service-grid">
                    <article class="surface-card current-queue-card"><div class="current-queue-heading"><div><span class="eyebrow">SEDANG DIPANGGIL</span><span class="live-label"><i class="bi bi-broadcast" aria-hidden="true"></i> LIVE</span></div><button class="icon-button small-icon-button" type="button" aria-label="Opsi antrean" data-toast="Opsi antrean"><i class="bi bi-three-dots" aria-hidden="true"></i></button></div><div class="queue-number-display" data-current-queue>U-024</div><div class="current-patient"><span class="avatar avatar-large avatar-soft-blue">BW</span><div><strong>Budi Wicaksono</strong><span>RM-001742 · Laki-laki, 51 th</span><small><i class="bi bi-clock" aria-hidden="true"></i> Terdaftar pukul 09.16</small></div></div><div class="queue-actions"><button class="btn btn-primary" type="button" data-call-next><i class="bi bi-megaphone" aria-hidden="true"></i> Panggil berikutnya</button><button class="btn btn-outline-secondary" type="button" data-call-repeat><i class="bi bi-arrow-repeat" aria-hidden="true"></i><span>Panggil ulang</span></button><button class="btn btn-outline-success" type="button" data-call-finish><i class="bi bi-check2" aria-hidden="true"></i><span>Selesaikan</span></button></div></article>
                    <div class="service-stats"><article class="stat-card"><div class="stat-card-top"><span>Sedang Dilayani</span><span class="stat-icon stat-icon-cyan"><i class="bi bi-person-check" aria-hidden="true"></i></span></div><div class="stat-value"><?= number_format($servingTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="stat-neutral">Ruang pemeriksaan 02</span></div></article><article class="stat-card"><div class="stat-card-top"><span>Menunggu</span><span class="stat-icon stat-icon-amber"><i class="bi bi-hourglass-split" aria-hidden="true"></i></span></div><div class="stat-value" data-waiting-count><?= number_format($waitingTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="stat-neutral">Estimasi 24 menit</span></div></article><article class="stat-card"><div class="stat-card-top"><span>Selesai Hari Ini</span><span class="stat-icon stat-icon-green"><i class="bi bi-check2-circle" aria-hidden="true"></i></span></div><div class="stat-value" data-completed-count><?= number_format($completedTotal, 0, ',', '.') ?></div><div class="stat-foot"><span class="trend-up">+3</span><span>dari kemarin</span></div></article></div>
                </div>
                <article class="surface-card table-card section-gap"><div class="surface-heading"><div><h2>Antrean berikutnya</h2><p>Urutan pasien pada Poli Umum</p></div><button class="select-button" type="button" data-toast="Filter status belum terhubung.">Semua status <i class="bi bi-chevron-down" aria-hidden="true"></i></button></div><div class="table-responsive"><table class="table app-table" data-search-table><thead><tr><th>URUTAN</th><th>PASIEN</th><th>JAM DAFTAR</th><th>KELUHAN</th><th>STATUS</th></tr></thead><tbody data-queue-list><tr><td><span class="queue-code">U-025</span></td><td><span class="table-person"><span class="avatar avatar-soft-teal">SA</span><span><strong>Siti Aminah</strong><small>RM-002019</small></span></span></td><td>09.19</td><td>Pusing sejak kemarin</td><td><span class="status-pill status-waiting">Menunggu</span></td></tr><tr><td><span class="queue-code">U-026</span></td><td><span class="table-person"><span class="avatar avatar-soft-amber">AR</span><span><strong>Andi Ramadhan</strong><small>RM-001508</small></span></span></td><td>09.22</td><td>Kontrol rutin</td><td><span class="status-pill status-waiting">Menunggu</span></td></tr><tr><td><span class="queue-code">U-027</span></td><td><span class="table-person"><span class="avatar avatar-soft-violet">ML</span><span><strong>Maya Lestari</strong><small>RM-002144</small></span></span></td><td>09.27</td><td>Batuk dan demam</td><td><span class="status-pill status-waiting">Menunggu</span></td></tr></tbody></table></div></article>
            </section>

            <section class="role-panel <?= $activeRole === 'patient' ? 'is-visible' : '' ?>" data-role-panel="patient" <?= $activeRole === 'patient' ? '' : 'hidden' ?> aria-label="Dashboard Pasien">
                <div class="patient-welcome"><div><span class="eyebrow" data-patient-date></span><h2>Selamat pagi, Nadia <span aria-hidden="true">☀</span></h2><p>Semoga hari Anda menyenangkan. Ini ringkasan layanan kesehatan Anda.</p></div><button type="button" class="btn btn-primary" data-open-registration><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat pendaftaran</button></div>
                <?php if ($hasAuthenticatedRole && empty($myQueue)): ?>
                    <article class="surface-card patient-empty-state">
                        <span class="stat-icon stat-icon-blue"><i class="bi bi-ticket-perforated" aria-hidden="true"></i></span>
                        <h2>Belum ada antrean aktif</h2>
                        <p>Buat pendaftaran kunjungan untuk melihat nomor antrean dan perkiraan waktu layanan di sini.</p>
                        <button class="btn btn-primary" type="button" data-open-registration><i class="bi bi-plus-lg" aria-hidden="true"></i> Buat pendaftaran</button>
                    </article>
                <?php else: ?>
                    <?php
                    $queueStatus = strtolower((string) $readValue($myQueue ?? null, 'status', 'menunggu'));
                    $queueStatusClass = match ($queueStatus) {
                        'dipanggil', 'dilayani' => 'status-serving',
                        'selesai' => 'status-done',
                        default => 'status-waiting',
                    };
                    ?>
                    <div class="patient-grid">
                        <article class="patient-queue-card">
                            <div class="patient-queue-top"><span class="patient-queue-label"><i class="bi bi-ticket-perforated" aria-hidden="true"></i> ANTREAN HARI INI</span><span class="status-pill <?= $queueStatusClass ?>"><?= SecurityHelper::escape(ucfirst($queueStatus)) ?></span></div>
                            <div class="patient-queue-main"><div><span class="patient-queue-number"><?= SecurityHelper::escape($readValue($myQueue ?? null, 'no_antrean', 'A-014')) ?></span><span class="patient-queue-poli"><?= SecurityHelper::escape($readValue($myQueue ?? null, 'nama_poli', 'Poli Anak')) ?></span></div><div class="queue-position"><strong>3</strong><span>pasien<br>di depan Anda</span></div></div>
                            <div class="queue-progress"><div class="queue-progress-track"><span style="width: 68%"></span></div><div class="queue-progress-label"><span>Antrean berjalan</span><span>Estimasi ± 12 menit</span></div></div>
                            <div class="patient-queue-doctor"><span class="doctor-avatar"><i class="bi bi-person-fill" aria-hidden="true"></i></span><span><strong><?= SecurityHelper::escape($readValue($myQueue ?? null, 'nama_dokter', 'dr. Andi Pratama')) ?></strong><small><?= SecurityHelper::escape($readValue($myQueue ?? null, 'tanggal_antrean', 'Rabu, 5 Nov')) ?> · 08.00–12.00</small></span><button class="icon-button small-icon-button ms-auto" type="button" aria-label="Detail jadwal" data-module="history"><i class="bi bi-arrow-up-right" aria-hidden="true"></i></button></div>
                        </article>
                        <article class="surface-card patient-next-card"><div class="surface-heading"><div><h2>Jadwal berikutnya</h2><p>Kontrol yang akan datang</p></div><span class="calendar-icon"><i class="bi bi-calendar2-heart" aria-hidden="true"></i></span></div><div class="appointment-date"><strong>12</strong><span>NOV<br><small>KAMIS</small></span></div><div class="appointment-info"><strong>Poli Gigi</strong><span>drg. Dewi Lestari</span><small><i class="bi bi-clock" aria-hidden="true"></i> 09.30 WIB</small></div><button class="btn btn-outline-primary btn-sm appointment-detail" type="button" data-module="history">Lihat detail <i class="bi bi-arrow-right" aria-hidden="true"></i></button></article>
                    </div>
                <?php endif; ?>
                <div class="patient-stat-grid"><article class="patient-mini-stat"><span class="mini-stat-icon mini-blue"><i class="bi bi-clipboard2-check" aria-hidden="true"></i></span><span><strong>2</strong><small>Kunjungan tahun ini</small></span></article><article class="patient-mini-stat"><span class="mini-stat-icon mini-green"><i class="bi bi-heart-pulse" aria-hidden="true"></i></span><span><strong>1</strong><small>Jadwal mendatang</small></span></article><article class="patient-mini-stat"><span class="mini-stat-icon mini-violet"><i class="bi bi-file-earmark-medical" aria-hidden="true"></i></span><span><strong>3</strong><small>Riwayat kunjungan</small></span></article></div>
                <article class="surface-card table-card patient-history-card"><div class="surface-heading"><div><h2>Riwayat kunjungan</h2><p>Catatan kunjungan terakhir Anda</p></div><a href="#riwayat" class="text-link" data-nav-message="Riwayat Kunjungan">Lihat semua <i class="bi bi-arrow-right" aria-hidden="true"></i></a></div><div class="table-responsive"><table class="table app-table" data-search-table><thead><tr><th>TANGGAL</th><th>LAYANAN</th><th>DOKTER</th><th>NO. ANTREAN</th><th>STATUS</th></tr></thead><tbody><tr><td><strong>5 Nov 2026</strong><small class="table-subline">Hari ini</small></td><td>Poli Anak</td><td>dr. Andi Pratama</td><td><span class="queue-code queue-amber">A-014</span></td><td><span class="status-pill status-waiting">Menunggu</span></td></tr><tr><td><strong>18 Sep 2026</strong><small class="table-subline">Jumat</small></td><td>Poli Umum</td><td>dr. Budi Santoso</td><td><span class="queue-code">U-031</span></td><td><span class="status-pill status-done">Selesai</span></td></tr><tr><td><strong>2 Jul 2026</strong><small class="table-subline">Kamis</small></td><td>Poli Gigi</td><td>drg. Dewi Lestari</td><td><span class="queue-code queue-teal">G-012</span></td><td><span class="status-pill status-done">Selesai</span></td></tr></tbody></table></div></article>
            </section>

            <footer class="dashboard-footer"><span>© <?= date('Y') ?> ANTRIAN SEHAT</span><span><?= $hasAuthenticatedRole ? 'Ringkasan pelayanan' : 'Data pada tampilan ini adalah pratinjau antarmuka.' ?></span></footer>
        </main>
    </div>
    <div class="sidebar-scrim" data-sidebar-close></div>
    <div class="toast-stack" aria-live="polite" aria-atomic="true" data-toast-stack></div>

    <div class="modal fade" id="moduleModal" tabindex="-1" aria-labelledby="moduleModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content registration-modal">
                <div class="modal-header"><div><span class="eyebrow">ANTRIAN SEHAT</span><h2 class="modal-title" id="moduleModalTitle" data-module-title></h2></div><button type="button" class="icon-button" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                <div class="modal-body" data-module-body></div>
                <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Tutup</button><button type="button" class="btn btn-primary" data-module-action>Unduh laporan</button></div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content registration-modal">
                <div class="modal-header"><div><span class="eyebrow">PENDAFTARAN KUNJUNGAN</span><h2 class="modal-title" id="registrationModalLabel">Pendaftaran baru</h2></div><button type="button" class="icon-button" data-bs-dismiss="modal" aria-label="Tutup"><i class="bi bi-x-lg" aria-hidden="true"></i></button></div>
                <form data-demo-form><div class="modal-body"><label class="form-label" for="modal-poli">Pilih poli</label><select class="form-select" id="modal-poli" required><option value="">Pilih poli tujuan</option><option>Poli Umum</option><option>Poli Gigi</option><option>Poli Anak</option><option>Poli KIA</option></select><label class="form-label mt-3" for="modal-date">Tanggal kunjungan</label><input class="form-control" id="modal-date" type="date" required><label class="form-label mt-3" for="modal-complaint">Keluhan singkat</label><textarea class="form-control" id="modal-complaint" rows="3" placeholder="Jelaskan keluhan utama"></textarea></div><div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary">Lanjutkan <i class="bi bi-arrow-right" aria-hidden="true"></i></button></div></form>
            </div>
        </div>
    </div>
</div>