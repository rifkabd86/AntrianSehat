<?php
/**
 * ANTRIAN SEHAT - General Helper Class
 * 
 * Kumpulan fungsi bantuan praktis untuk seluruh aplikasi.
 */

class Helper
{
    /**
     * Escape HTML output (XSS protection)
     */
    public static function e(?string $string): string
    {
        return htmlspecialchars($string ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    /**
     * Ambil nilai properti/key secara aman baik dari object maupun array
     */
    public static function val(mixed $data, string $key, mixed $default = null): mixed
    {
        if (is_object($data)) {
            return $data->$key ?? $default;
        }
        if (is_array($data)) {
            return $data[$key] ?? $default;
        }
        return $default;
    }

    /**
     * Redirect ke URL aplikasi
     */
    public static function redirect(string $url): void
    {
        $target = rtrim(BASE_URL, '/') . '/' . ltrim($url, '/');
        header('Location: ' . $target);
        exit;
    }

    /**
     * Set pesan flash ke session
     */
    public static function flash(string $key, string $message, string $type = 'success'): void
    {
        $_SESSION['flash'] = [
            'key' => $key,
            'message' => $message,
            'type' => $type
        ];
    }

    /**
     * Ambil dan hapus flash message
     */
    public static function getFlash(?string $key = null): ?array
    {
        if (!isset($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        if ($key !== null && ($flash['key'] ?? '') !== $key) {
            return null;
        }
        unset($_SESSION['flash']);
        return $flash;
    }

    /**
     * Cek apakah ada flash message
     */
    public static function hasFlash(?string $key = null): bool
    {
        if (!isset($_SESSION['flash'])) {
            return false;
        }
        if ($key !== null) {
            return ($_SESSION['flash']['key'] ?? '') === $key;
        }
        return true;
    }

    /**
     * Simpan input form lama untuk dipulihkan jika validasi gagal
     */
    public static function setOldInput(array $data): void
    {
        $_SESSION['old_input'] = $data;
    }

    /**
     * Ambil nilai lama form
     */
    public static function old(string $key, mixed $default = ''): mixed
    {
        $val = $_SESSION['old_input'][$key] ?? $default;
        return is_string($val) ? self::e($val) : $val;
    }

    /**
     * Bersihkan input form lama
     */
    public static function clearOldInput(): void
    {
        unset($_SESSION['old_input']);
    }

    /**
     * Format tanggal ke Bahasa Indonesia (contoh: "Selasa, 14 Oktober 2025")
     */
    public static function formatTanggal(?string $date, bool $withDay = true): string
    {
        if (!$date || $date === '0000-00-00') {
            return '-';
        }

        $timestamp = strtotime($date);
        if ($timestamp === false) {
            return $date;
        }

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

        $hari = $hariIndo[date('l', $timestamp)] ?? '';
        $tanggal = date('j', $timestamp);
        $bulan = $bulanIndo[(int)date('n', $timestamp)] ?? '';
        $tahun = date('Y', $timestamp);

        if ($withDay) {
            return "{$hari}, {$tanggal} {$bulan} {$tahun}";
        }
        return "{$tanggal} {$bulan} {$tahun}";
    }

    /**
     * Format jam/waktu (contoh: "08:30 WIB")
     */
    public static function formatWaktu(?string $time): string
    {
        if (!$time) {
            return '-';
        }
        $timestamp = strtotime($time);
        return $timestamp ? date('H:i', $timestamp) . ' WIB' : $time;
    }

    /**
     * Format tanggal dan jam
     */
    public static function formatTanggalWaktu(?string $datetime): string
    {
        if (!$datetime) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return $datetime;
        }
        return self::formatTanggal(date('Y-m-d', $timestamp), false) . ' ' . date('H:i', $timestamp) . ' WIB';
    }

    /**
     * Format mata uang Rupiah
     */
    public static function formatRupiah(float|int $amount): string
    {
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }

    /**
     * Generate Nomor Rekam Medis (misal: RM-20250001)
     */
    public static function generateNomorRM(): string
    {
        $year = date('Y');
        $random = mt_rand(1000, 9999);
        return "RM-{$year}{$random}";
    }

    /**
     * Format Nomor Antrean (misal: A012)
     */
    public static function generateNoAntrean(string $kodePoli, int $nomorUrut): string
    {
        $prefix = strtoupper(substr($kodePoli, 0, 1));
        if (empty($prefix)) {
            $prefix = 'A';
        }
        return sprintf('%s%03d', $prefix, $nomorUrut);
    }

    /**
     * Waktu relatif (contoh: "5 menit yang lalu")
     */
    public static function timeAgo(?string $datetime): string
    {
        if (!$datetime) {
            return '-';
        }
        $timestamp = strtotime($datetime);
        if (!$timestamp) {
            return $datetime;
        }

        $diff = time() - $timestamp;

        if ($diff < 60) {
            return 'Baru saja';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return "{$mins} menit yang lalu";
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return "{$hours} jam yang lalu";
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return "{$days} hari yang lalu";
        } else {
            return self::formatTanggal(date('Y-m-d', $timestamp), false);
        }
    }

    /**
     * Potong teks panjang
     */
    public static function excerpt(string $text, int $length = 100): string
    {
        if (mb_strlen($text) <= $length) {
            return $text;
        }
        return mb_substr($text, 0, $length) . '...';
    }

    /**
     * Cek status menu aktif
     */
    public static function isActiveMenu(string $path): string
    {
        $currentUri = trim(parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH), '/');
        $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '') {
            $currentUri = preg_replace('#^' . preg_quote($basePath, '#') . '/?#', '', $currentUri);
        }
        $currentUri = trim($currentUri, '/');
        $path = trim($path, '/');

        if ($path === '' && $currentUri === '') {
            return 'active';
        }
        if ($path !== '' && str_starts_with($currentUri, $path)) {
            return 'active';
        }
        return '';
    }

    /**
     * Label role pengguna dalam bahasa Indonesia
     */
    public static function getRoleLabel(string $role): string
    {
        return match ($role) {
            'admin' => 'Administrator',
            'petugas_pendaftaran' => 'Petugas Pendaftaran',
            'petugas_pelayanan' => 'Petugas Pelayanan',
            'pasien' => 'Pasien',
            default => ucfirst(str_replace('_', ' ', $role))
        };
    }

    /**
     * HTML Badge untuk status antrean
     */
    public static function statusBadge(string $status): string
    {
        $badgeClass = match ($status) {
            'menunggu' => 'badge-menunggu',
            'dipanggil' => 'badge-dipanggil',
            'dilayani' => 'badge-dilayani',
            'selesai' => 'badge-selesai',
            'dilewati' => 'badge-dilewati',
            'dibatalkan' => 'badge-dibatalkan',
            default => 'badge bg-secondary'
        };

        $label = match ($status) {
            'menunggu' => 'Menunggu',
            'dipanggil' => 'Dipanggil',
            'dilayani' => 'Sedang Dilayani',
            'selesai' => 'Selesai',
            'dilewati' => 'Dilewati',
            'dibatalkan' => 'Dibatalkan',
            default => ucfirst($status)
        };

        return sprintf('<span class="status-badge %s">%s</span>', self::e($badgeClass), self::e($label));
    }

    /**
     * Badge visual untuk aksi pada Audit Log sesuai standar sistem
     */
    public static function auditActionBadge(string $aksi): string
    {
        $aLower = strtolower($aksi);

        if (str_contains($aLower, 'login')) {
            $class = 'bg-success-subtle text-success border border-success-subtle';
            $icon = 'fas fa-arrow-right-to-bracket';
        } elseif (str_contains($aLower, 'logout')) {
            $class = 'bg-secondary-subtle text-secondary border border-secondary-subtle';
            $icon = 'fas fa-arrow-right-from-bracket';
        } elseif (str_contains($aLower, 'tambah') || str_contains($aLower, 'create') || str_contains($aLower, 'registrasi') || str_contains($aLower, 'daftar')) {
            $class = 'bg-primary-subtle text-primary border border-primary-subtle';
            $icon = 'fas fa-plus-circle';
        } elseif (str_contains($aLower, 'edit') || str_contains($aLower, 'update') || str_contains($aLower, 'ubah')) {
            $class = 'bg-warning-subtle text-warning-emphasis border border-warning-subtle';
            $icon = 'fas fa-pen-to-square';
        } elseif (str_contains($aLower, 'hapus') || str_contains($aLower, 'delete') || str_contains($aLower, 'batal') || str_contains($aLower, 'gagal')) {
            $class = 'bg-danger-subtle text-danger border border-danger-subtle';
            $icon = 'fas fa-trash-can';
        } elseif (str_contains($aLower, 'status') || str_contains($aLower, 'panggil') || str_contains($aLower, 'layani') || str_contains($aLower, 'lewati')) {
            $class = 'bg-info-subtle text-info-emphasis border border-info-subtle';
            $icon = 'fas fa-arrows-rotate';
        } else {
            $class = 'bg-light text-dark border';
            $icon = 'fas fa-clock-rotate-left';
        }

        return sprintf(
            '<span class="badge %s px-2 py-1 rounded-pill fw-semibold"><i class="%s me-1 small"></i>%s</span>',
            $class,
            $icon,
            htmlspecialchars($aksi, ENT_QUOTES, 'UTF-8')
        );
    }
}

