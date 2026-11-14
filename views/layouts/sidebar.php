<?php
/**
 * Sidebar Navigation - ANTRIAN SEHAT
 * Role-based menu rendering
 */

$currentRole = $_SESSION['user']['role'] ?? $_SESSION['user_role'] ?? '';
$currentUser = $_SESSION['user'] ?? [];

if (!function_exists('isMenuActive')) {
    function isMenuActive(string $path): string {
        return Helper::isActiveMenu($path);
    }
}
?>
<!-- Sidebar -->
<aside class="sidebar" id="sidebar">
    <!-- Logo Section -->
    <div class="sidebar-header">
        <a href="<?= BASE_URL ?>/dashboard" class="sidebar-brand text-decoration-none">
            <div class="brand-icon">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div class="brand-text">
                <h5 class="mb-0 fw-bold text-white">ANTRIAN SEHAT</h5>
                <small class="text-white-50">Mudah &bull; Cepat &bull; Sehat</small>
            </div>
        </a>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">
        <ul class="nav flex-column">
            <!-- Dashboard - All Roles -->
            <li class="nav-item">
                <a class="nav-link <?= isMenuActive('dashboard') ?>" href="<?= BASE_URL ?>/dashboard">
                    <i class="fas fa-tachometer-alt nav-icon"></i>
                    <span class="nav-text">Dashboard</span>
                </a>
            </li>

            <?php if ($currentRole === 'admin'): ?>
                <!-- ===== ADMIN MENU ===== -->
                <li class="nav-section-title">
                    <span>PELAYANAN</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('pendaftaran') ?>" href="<?= BASE_URL ?>/pendaftaran">
                        <i class="fas fa-clipboard-list nav-icon"></i>
                        <span class="nav-text">Pendaftaran Pasien</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('antrean') ?>" href="<?= BASE_URL ?>/antrean">
                        <i class="fas fa-users-line nav-icon"></i>
                        <span class="nav-text">Antrean Pasien</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('pasien') ?>" href="<?= BASE_URL ?>/pasien">
                        <i class="fas fa-hospital-user nav-icon"></i>
                        <span class="nav-text">Data Pasien</span>
                    </a>
                </li>

                <li class="nav-section-title">
                    <span>MASTER DATA</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('poli') ?>" href="#poliSubmenu" data-bs-toggle="collapse" role="button" aria-expanded="<?= isMenuActive('poli') || isMenuActive('dokter') || isMenuActive('jadwal') ? 'true' : 'false' ?>">
                        <i class="fas fa-hospital nav-icon"></i>
                        <span class="nav-text">Dokter &amp; Poli</span>
                        <i class="fas fa-chevron-down nav-arrow ms-auto"></i>
                    </a>
                    <div class="collapse <?= isMenuActive('poli') || isMenuActive('dokter') || isMenuActive('jadwal') ? 'show' : '' ?>" id="poliSubmenu">
                        <ul class="nav flex-column submenu">
                            <li class="nav-item">
                                <a class="nav-link <?= isMenuActive('poli') ?>" href="<?= BASE_URL ?>/poli">
                                    <i class="fas fa-circle nav-icon-dot"></i>
                                    <span class="nav-text">Poli</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= isMenuActive('dokter') ?>" href="<?= BASE_URL ?>/dokter">
                                    <i class="fas fa-circle nav-icon-dot"></i>
                                    <span class="nav-text">Dokter</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?= isMenuActive('jadwal') ?>" href="<?= BASE_URL ?>/jadwal">
                                    <i class="fas fa-circle nav-icon-dot"></i>
                                    <span class="nav-text">Jadwal</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </li>

                <li class="nav-section-title">
                    <span>ADMINISTRASI</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('laporan') ?>" href="<?= BASE_URL ?>/laporan">
                        <i class="fas fa-chart-bar nav-icon"></i>
                        <span class="nav-text">Laporan</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('users') ?>" href="<?= BASE_URL ?>/users">
                        <i class="fas fa-users-gear nav-icon"></i>
                        <span class="nav-text">Manajemen User</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('audit') ?>" href="<?= BASE_URL ?>/audit">
                        <i class="fas fa-clipboard-check nav-icon"></i>
                        <span class="nav-text">Audit Log</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('settings') ?>" href="<?= BASE_URL ?>/settings">
                        <i class="fas fa-cog nav-icon"></i>
                        <span class="nav-text">Pengaturan</span>
                    </a>
                </li>

            <?php elseif ($currentRole === 'petugas_pendaftaran'): ?>
                <!-- ===== PETUGAS PENDAFTARAN MENU ===== -->
                <li class="nav-section-title">
                    <span>PELAYANAN</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('pendaftaran') ?>" href="<?= BASE_URL ?>/pendaftaran">
                        <i class="fas fa-clipboard-list nav-icon"></i>
                        <span class="nav-text">Pendaftaran Pasien</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('pasien') ?>" href="<?= BASE_URL ?>/pasien">
                        <i class="fas fa-hospital-user nav-icon"></i>
                        <span class="nav-text">Data Pasien</span>
                    </a>
                </li>

            <?php elseif ($currentRole === 'petugas_pelayanan'): ?>
                <!-- ===== PETUGAS PELAYANAN MENU ===== -->
                <li class="nav-section-title">
                    <span>PELAYANAN</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('antrean') ?>" href="<?= BASE_URL ?>/antrean">
                        <i class="fas fa-users-line nav-icon"></i>
                        <span class="nav-text">Antrean Pasien</span>
                    </a>
                </li>

            <?php elseif ($currentRole === 'pasien'): ?>
                <!-- ===== PASIEN MENU ===== -->
                <li class="nav-section-title">
                    <span>LAYANAN</span>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('pendaftaran') ?>" href="<?= BASE_URL ?>/pendaftaran">
                        <i class="fas fa-clipboard-list nav-icon"></i>
                        <span class="nav-text">Pendaftaran</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('antrean/saya') ?>" href="<?= BASE_URL ?>/antrean/saya">
                        <i class="fas fa-ticket nav-icon"></i>
                        <span class="nav-text">Antrean Saya</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?= isMenuActive('riwayat') ?>" href="<?= BASE_URL ?>/riwayat">
                        <i class="fas fa-clock-rotate-left nav-icon"></i>
                        <span class="nav-text">Riwayat</span>
                    </a>
                </li>
            <?php endif; ?>

            <!-- Divider & Logout - All Roles -->
            <li class="nav-divider">
                <hr class="my-2 border-secondary">
            </li>
            <li class="nav-item">
                <form action="<?= BASE_URL ?>/logout" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin keluar?')">
                    <?= SecurityHelper::csrfField() ?>
                    <button class="nav-link text-danger-light border-0 w-100 text-start bg-transparent" type="submit">
                        <i class="fas fa-right-from-bracket nav-icon"></i>
                        <span class="nav-text">Logout</span>
                    </button>
                </form>
            </li>
        </ul>
    </nav>

    <!-- Sidebar Footer - User Info (compact) -->
    <div class="sidebar-footer">
        <div class="d-flex align-items-center px-3 py-2">
            <div class="avatar-circle avatar-sm me-2">
                <?php
                    $nama = $currentUser['nama_lengkap'] ?? $currentUser['username'] ?? 'U';
                    $initials = '';
                    $parts = explode(' ', $nama);
                    $initials .= mb_strtoupper(mb_substr($parts[0], 0, 1));
                    if (isset($parts[1])) {
                        $initials .= mb_strtoupper(mb_substr($parts[1], 0, 1));
                    }
                ?>
                <span><?= htmlspecialchars($initials, ENT_QUOTES, 'UTF-8') ?></span>
            </div>
            <div class="overflow-hidden">
                <div class="text-white fw-semibold small text-truncate">
                    <?= htmlspecialchars($currentUser['nama_lengkap'] ?? $currentUser['username'] ?? 'User', ENT_QUOTES, 'UTF-8') ?>
                </div>
                <div class="text-white-50 small text-truncate">
                    <?php
                        $roleLabels = [
                            'admin' => 'Administrator',
                            'petugas_pendaftaran' => 'Petugas Pendaftaran',
                            'petugas_pelayanan' => 'Petugas Pelayanan',
                            'pasien' => 'Pasien',
                        ];
                        echo htmlspecialchars($roleLabels[$currentRole] ?? ucfirst($currentRole), ENT_QUOTES, 'UTF-8');
                    ?>
                </div>
            </div>
        </div>
    </div>
</aside>
