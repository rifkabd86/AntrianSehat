<?php
/**
 * Test All Views Renderer
 */
require_once dirname(__DIR__) . '/config/bootstrap.php';

$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';
$_SESSION['user_nama'] = 'Administrator Puskesmas';
$_SESSION['user_username'] = 'admin';
$_SESSION['user'] = [
    'id' => 1,
    'username' => 'admin',
    'nama_lengkap' => 'Administrator Puskesmas',
    'email' => 'admin@antriansehat.id',
    'role' => 'admin',
];

$view = new View();

$testViews = [
    ['view' => 'auth/login', 'data' => ['title' => 'Login'], 'layout' => 'auth'],
    ['view' => 'auth/register', 'data' => ['title' => 'Registrasi'], 'layout' => 'auth'],
    ['view' => 'auth/forgot_password', 'data' => ['title' => 'Lupa Password'], 'layout' => 'auth'],
    ['view' => 'auth/reset_password', 'data' => ['title' => 'Reset Password', 'token' => 'abc123test'], 'layout' => 'auth'],
    ['view' => 'auth/profile', 'data' => ['title' => 'Profil', 'user' => (object)$_SESSION['user'], 'pasien' => null], 'layout' => 'main'],
    ['view' => 'auth/change_password', 'data' => ['title' => 'Ubah Password'], 'layout' => 'main'],
    ['view' => 'dashboard/index', 'data' => [
        'title' => 'Dashboard',
        'role' => 'admin',
        'stats' => ['total_hari_ini' => 48, 'dalam_antrean' => 16, 'dipanggil' => 4, 'sedang_dilayani' => 4, 'selesai' => 28, 'dibatalkan' => 0],
        'antreanSaatIni' => [],
        'chartData' => ['labels' => ['8 Okt', '9 Okt', '10 Okt', '11 Okt', '12 Okt', '13 Okt', '14 Okt'], 'data' => [35, 42, 28, 48, 38, 52, 48]],
        'chartKunjunganPeriods' => [
            'daily' => ['labels' => ['8 Okt', '9 Okt', '10 Okt'], 'data' => [35, 42, 28]],
            'weekly' => ['labels' => ['Mg 1', 'Mg 2'], 'data' => [120, 140]],
            'monthly' => ['labels' => ['Ags', 'Sep', 'Okt'], 'data' => [450, 520, 610]],
        ],
        'chartDistribution' => [
            'labels' => ['Menunggu', 'Dipanggil', 'Dilayani', 'Selesai', 'Batal'],
            'data' => [16, 4, 4, 28, 0],
            'colors' => ['#F59E0B', '#3B82F6', '#8B5CF6', '#10B981', '#EF4444'],
        ],
        'chartPoli' => [
            'labels' => ['Poli Umum', 'Poli Gigi', 'Poli Anak'],
            'data' => [24, 14, 10],
            'colors' => ['#2563EB', '#10B981', '#F59E0B'],
        ],
        'poliList' => [],
        'totalPasien' => 8,
        'totalPengguna' => 12,
        'totalPetugas' => 5,
        'myQueue' => null,
        'myHistory' => [],
        'recentActivity' => [],
        'patientProfile' => null,
    ], 'layout' => 'main'],
    ['view' => 'pendaftaran/create', 'data' => [
        'title' => 'Pendaftaran Pasien',
        'poliList' => [],
        'dokterList' => [],
        'pasienDefault' => null,
    ], 'layout' => 'main'],
    ['view' => 'pendaftaran/index', 'data' => [
        'title' => 'Pendaftaran Pasien',
        'list' => [],
        'poliList' => [],
        'filters' => ['tanggal' => date('Y-m-d'), 'poli_id' => 0, 'status' => '', 'search' => ''],
        'page' => 1,
        'total' => 0,
        'totalPages' => 1,
    ], 'layout' => 'main'],
    ['view' => 'pendaftaran/tiket', 'data' => [
        'title' => 'Tiket Antrean',
        'data' => (object)[
            'no_antrean' => 'A012',
            'nama_poli' => 'Poli Umum',
            'nama_pasien' => 'Rina Aprilia',
            'no_rekam_medis' => 'RM-20250001',
            'nama_dokter' => 'dr. Budi Santoso',
            'tanggal_kunjungan' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ]
    ], 'layout' => 'main'],
    ['view' => 'antrean/index', 'data' => [
        'title' => 'Daftar Antrian Pasien',
        'list' => [],
        'total' => 48,
        'totalPages' => 5,
        'page' => 1,
        'poliList' => [],
        'filters' => ['search' => '', 'poli_id' => '', 'status' => '', 'tanggal' => date('Y-m-d')],
    ], 'layout' => 'main'],
    ['view' => 'antrean/detail', 'data' => [
        'title' => 'Detail Antrean',
        'data' => (object)[
            'no_antrean' => 'A001',
            'status' => 'menunggu',
            'nama_pasien' => 'Pasien Uji',
            'no_rekam_medis' => 'RM-20260001',
            'nama_poli' => 'Poli Umum',
            'nama_dokter' => 'Dokter Uji',
            'tanggal_antrean' => date('Y-m-d'),
            'created_at' => date('Y-m-d H:i:s'),
        ],
    ], 'layout' => 'main'],
    ['view' => 'antrean/loket', 'data' => [
        'title' => 'Loket Pemanggilan Pasien',
        'poliId' => 1,
        'poliList' => [],
        'activeList' => [],
        'currentlyServing' => null,
        'stats' => ['total_hari_ini' => 48],
    ], 'layout' => 'main'],
    ['view' => 'display/index', 'data' => ['title' => 'Monitor Antrean'], 'layout' => null],
    ['view' => 'pasien/index', 'data' => [
        'title' => 'Data Pasien',
        'list' => [],
        'search' => '',
        'total' => 8,
        'totalPages' => 1,
        'page' => 1,
    ], 'layout' => 'main'],
    ['view' => 'pasien/detail', 'data' => [
        'title' => 'Detail Pasien',
        'pasien' => (object)[
            'id' => 1,
            'nama_lengkap' => 'Rina Aprilia',
            'no_antrean' => 'A012',
            'tanggal_lahir' => '1995-03-12',
            'jenis_kelamin' => 'Perempuan',
            'no_telepon' => '081234567890',
            'nama_poli' => 'Umum',
            'created_at' => date('Y-m-d 08:12:00'),
            'no_rekam_medis' => 'RM-20250001',
        ],
        'riwayat' => [],
    ], 'layout' => 'main'],
    ['view' => 'pasien/riwayat', 'data' => ['title' => 'Riwayat Kunjungan', 'history' => []], 'layout' => 'main'],
    ['view' => 'pasien/create', 'data' => ['title' => 'Tambah Pasien'], 'layout' => 'main'],
    ['view' => 'poli/index', 'data' => ['title' => 'Data Poli', 'list' => []], 'layout' => 'main'],
    ['view' => 'poli/create', 'data' => ['title' => 'Tambah Poli'], 'layout' => 'main'],
    ['view' => 'poli/edit', 'data' => ['title' => 'Edit Poli', 'poli' => (object)['id' => 1, 'kode_poli' => 'UMM', 'nama_poli' => 'Poli Umum', 'deskripsi' => 'Layanan umum', 'icon' => 'fas fa-stethoscope', 'warna' => '#2563EB', 'is_active' => 1]], 'layout' => 'main'],
    ['view' => 'dokter/index', 'data' => ['title' => 'Data Dokter', 'list' => []], 'layout' => 'main'],
    ['view' => 'dokter/create', 'data' => ['title' => 'Tambah Dokter', 'poliList' => []], 'layout' => 'main'],
    ['view' => 'dokter/edit', 'data' => ['title' => 'Edit Dokter', 'dokter' => (object)['id' => 1, 'kode_dokter' => 'DKT01', 'nama_dokter' => 'dr. Budi', 'poli_id' => 1, 'spesialisasi' => 'Umum', 'no_telepon' => '08123456789', 'is_active' => 1], 'poliList' => []], 'layout' => 'main'],
    ['view' => 'dokter/jadwal', 'data' => ['title' => 'Jadwal Dokter', 'dokter' => (object)['id' => 1, 'kode_dokter' => 'DKT01', 'nama_dokter' => 'dr. Budi', 'nama_poli' => 'Poli Umum', 'spesialisasi' => 'Umum', 'no_telepon' => '08123456789'], 'jadwalList' => []], 'layout' => 'main'],
    ['view' => 'jadwal/index', 'data' => ['title' => 'Jadwal Praktik', 'list' => [], 'poliList' => [], 'dokterList' => [], 'filters' => []], 'layout' => 'main'],
    ['view' => 'jadwal/form', 'data' => ['title' => 'Form Jadwal', 'jadwal' => null, 'dokterList' => [], 'poliList' => [], 'selectedDokterId' => 0], 'layout' => 'main'],
    ['view' => 'settings/index', 'data' => ['title' => 'Pengaturan Puskesmas', 'settings' => ['nama_puskesmas' => 'Puskesmas Sehat Sejahtera', 'alamat' => 'Jl. Kesehatan No. 10', 'telepon' => '(021) 555-1234', 'email' => 'info@puskesmassehat.go.id', 'jam_buka' => '08:00', 'jam_tutup' => '15:00', 'running_text' => 'Selamat datang di Puskesmas Sehat', 'estimasi_waktu_layanan' => '10', 'kuota_default' => '30']], 'layout' => 'main'],
    ['view' => 'laporan/index', 'data' => [
        'title' => 'Laporan Kunjungan',
        'rows' => [],
        'filters' => ['start_date' => date('Y-m-01'), 'end_date' => date('Y-m-d'), 'poli_id' => ''],
        'poliList' => [],
        'totalKunjungan' => 48,
        'totalSelesai' => 28,
        'totalBatal' => 0,
    ], 'layout' => 'main'],
    ['view' => 'laporan/cetak', 'data' => [
        'title' => 'Cetak Laporan',
        'rows' => [],
        'startDate' => date('Y-m-01'),
        'endDate' => date('Y-m-d'),
    ], 'layout' => null],
    ['view' => 'audit/index', 'data' => [
        'title' => 'Audit Log',
        'logs' => [],
        'stats' => [
            'total_aktivitas' => 142,
            'aktivitas_hari_ini' => 28,
            'total_pengguna' => 6,
            'aktivitas_terakhir' => (object)['aksi' => 'Login Berhasil', 'username' => 'admin', 'created_at' => date('Y-m-d H:i:s')],
        ],
        'total' => 142,
        'totalPages' => 10,
        'page' => 1,
        'perPage' => 15,
        'filters' => ['search' => '', 'aksi' => '', 'username' => '', 'date_from' => '', 'date_to' => ''],
        'aksiTypes' => ['Login', 'Logout', 'Pendaftaran Kunjungan & Antrean'],
        'userList' => ['admin', 'petugas1', 'petugas2'],
    ], 'layout' => 'main'],
    ['view' => 'notifikasi/index', 'data' => ['title' => 'Notifikasi', 'list' => []], 'layout' => 'main'],
    ['view' => 'users/index', 'data' => [
        'title' => 'Manajemen Pengguna',
        'users' => [],
        'search' => '',
        'role' => '',
        'status' => '',
        'page' => 1,
        'perPage' => 15,
        'total' => 0,
        'totalPages' => 1,
        'roles' => ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'],
    ], 'layout' => 'main'],
    ['view' => 'users/form', 'data' => [
        'title' => 'Tambah Pengguna',
        'user' => null,
        'patient' => null,
        'roles' => ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'],
    ], 'layout' => 'main'],
    ['view' => 'users/detail', 'data' => [
        'title' => 'Detail Pengguna',
        'user' => (object)[
            'id' => 1,
            'nama_lengkap' => 'Administrator Uji',
            'username' => 'admin-uji',
            'email' => 'admin@example.test',
            'no_telepon' => '081234567890',
            'role' => 'admin',
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'last_login' => null,
        ],
        'patient' => null,
        'roles' => ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'],
    ], 'layout' => 'main'],
    ['view' => 'errors/404', 'data' => [], 'layout' => null],
    ['view' => 'errors/403', 'data' => [], 'layout' => null],
];

echo "Testing template rendering for all views...\n";
$successCount = 0;
foreach ($testViews as $tv) {
    try {
        ob_start();
        $view->render($tv['view'], $tv['data'], $tv['layout']);
        $html = ob_get_clean();
        if ($tv['view'] === 'dashboard/index' && (!str_contains($html, '/logout') || !str_contains($html, 'name="csrf_token"'))) {
            throw new RuntimeException('Dashboard is missing the shared navigation logout form.');
        }
        if (strlen($html) > 50) {
            echo " [OK] " . $tv['view'] . " (" . strlen($html) . " bytes)\n";
            $successCount++;
        } else {
            echo " [WARN] " . $tv['view'] . " rendered too small\n";
        }
    } catch (Throwable $e) {
        ob_end_clean();
        echo " [FAIL] " . $tv['view'] . ": " . $e->getMessage() . "\n";
    }
}

echo "\nSummary: {$successCount} of " . count($testViews) . " views rendered successfully!\n";
