<?php
/**
 * Header Bar - ANTRIAN SEHAT
 * Top navigation bar with user info, notifications, and date/time
 */

$currentUser = $_SESSION['user'] ?? [];
$currentRole = $currentUser['role'] ?? '';
$notificationCount = $_SESSION['notification_count'] ?? 0;

// Indonesian day and month names
$hariIndo = [
    'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
    'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
    'Saturday' => 'Sabtu'
];
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
    5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
    9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];

$hari = $hariIndo[date('l')] ?? date('l');
$tanggal = date('j');
$bulan = $bulanIndo[(int)date('n')] ?? date('F');
$tahun = date('Y');
$tanggalLengkap = "{$hari}, {$tanggal} {$bulan} {$tahun}";

// Role labels
$roleLabels = [
    'admin' => 'Administrator',
    'petugas_pendaftaran' => 'Petugas Pendaftaran',
    'petugas_pelayanan' => 'Petugas Pelayanan',
    'pasien' => 'Pasien',
];

// User initials for avatar
$nama = $currentUser['nama_lengkap'] ?? $currentUser['username'] ?? 'U';
$parts = explode(' ', $nama);
$initials = mb_strtoupper(mb_substr($parts[0], 0, 1));
if (isset($parts[1])) {
    $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
}
?>
<!-- Top Header Bar -->
<header class="app-header d-flex align-items-center px-3 px-md-4 py-2 bg-white border-bottom shadow-sm sticky-top">
    <!-- Left: Toggle & Page Title -->
    <div class="d-flex align-items-center flex-grow-1">
        <!-- Mobile Sidebar Toggle -->
        <button class="btn btn-link text-dark p-1 me-2 d-lg-none sidebar-toggle" type="button" id="sidebarToggle" aria-label="Buka navigasi">
            <i class="fas fa-bars fs-5"></i>
        </button>

        <!-- Desktop Sidebar Toggle -->
        <button class="btn btn-link text-dark p-1 me-3 d-none d-lg-inline-flex sidebar-toggle-desktop" type="button" id="sidebarToggleDesktop" aria-label="Ciutkan sidebar" aria-expanded="true">
            <i class="fas fa-bars fs-5"></i>
        </button>

        <!-- Page Title & Breadcrumb -->
        <div>
            <h5 class="mb-0 fw-semibold text-dark page-title">
                <?= htmlspecialchars($title ?? 'Dashboard', ENT_QUOTES, 'UTF-8') ?>
            </h5>
            <?php if (!empty($breadcrumb)): ?>
                <nav aria-label="breadcrumb" class="d-none d-md-block">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item">
                            <a href="<?= BASE_URL ?>/dashboard" class="text-decoration-none text-muted">
                                <i class="fas fa-home"></i>
                            </a>
                        </li>
                        <?php foreach ($breadcrumb as $item): ?>
                            <?php if (!empty($item['url'])): ?>
                                <li class="breadcrumb-item">
                                    <a href="<?= BASE_URL ?>/<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>" class="text-decoration-none text-muted">
                                        <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </ol>
                </nav>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Date, Notifications, Profile -->
    <div class="d-flex align-items-center gap-2 gap-md-3">
        <!-- Date & Time -->
        <div class="d-none d-md-flex align-items-center text-muted me-1">
            <i class="fas fa-calendar-day me-2 text-primary"></i>
            <div>
                <span class="small fw-medium"><?= htmlspecialchars($tanggalLengkap, ENT_QUOTES, 'UTF-8') ?></span>
                <span class="ms-2 small text-primary fw-semibold" id="liveClock">
                    <i class="fas fa-clock me-1"></i><?= date('H:i') ?>
                </span>
            </div>
        </div>

        <!-- Notification Bell -->
        <div class="dropdown">
            <button class="btn btn-light btn-sm position-relative rounded-circle p-2" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi">
                <i class="fas fa-bell fs-6"></i>
                <?php if ($notificationCount > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                        <?= $notificationCount > 99 ? '99+' : (int)$notificationCount ?>
                        <span class="visually-hidden">notifikasi baru</span>
                    </span>
                <?php endif; ?>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 notification-dropdown" aria-labelledby="notificationDropdown" style="min-width: 320px;">
                <li>
                    <div class="dropdown-header d-flex justify-content-between align-items-center py-2">
                        <strong class="fs-6">Notifikasi</strong>
                        <?php if ($notificationCount > 0): ?>
                            <span class="badge bg-primary rounded-pill"><?= (int)$notificationCount ?> baru</span>
                        <?php endif; ?>
                    </div>
                </li>
                <li><hr class="dropdown-divider my-0"></li>
                <li>
                    <div class="text-center text-muted py-4 px-3">
                        <i class="fas fa-bell-slash fs-3 mb-2 d-block text-secondary"></i>
                        <small>Tidak ada notifikasi baru</small>
                    </div>
                </li>
            </ul>
        </div>

        <!-- User Profile Dropdown -->
        <div class="dropdown">
            <button class="btn btn-light btn-sm d-flex align-items-center gap-2 rounded-pill px-2 py-1" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="avatar-circle avatar-xs">
                    <span><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                </div>
                <div class="d-none d-md-block text-start lh-sm">
                    <div class="small fw-semibold text-dark">
                        <?= htmlspecialchars($currentUser['nama_lengkap'] ?? $currentUser['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.7rem;">
                        <?= htmlspecialchars($roleLabels[$currentRole] ?? ucfirst($currentRole), ENT_QUOTES, 'UTF-8') ?>
                    </div>
                </div>
                <i class="fas fa-chevron-down small text-muted d-none d-md-inline"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-1" aria-labelledby="userDropdown" style="min-width: 220px;">
                <li>
                    <div class="dropdown-header">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle avatar-sm">
                                <span><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
                            </div>
                            <div>
                                <div class="fw-semibold text-dark">
                                    <?= htmlspecialchars($currentUser['nama_lengkap'] ?? $currentUser['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
                                </div>
                                <small class="text-muted">
                                    <?= htmlspecialchars($roleLabels[$currentRole] ?? ucfirst($currentRole), ENT_QUOTES, 'UTF-8') ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item py-2" href="<?= BASE_URL ?>/profile">
                        <i class="fas fa-user-circle me-2 text-primary"></i> Profil Saya
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2" href="<?= BASE_URL ?>/auth/change-password">
                        <i class="fas fa-key me-2 text-warning"></i> Ubah Password
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form action="<?= BASE_URL ?>/logout" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar?')">
                        <?= SecurityHelper::csrfField() ?>
                        <button class="dropdown-item py-2 text-danger w-100 text-start" type="submit">
                            <i class="fas fa-right-from-bracket me-2"></i> Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
