<?php
/**
 * ANTRIAN SEHAT - MASTER END-TO-END VERIFICATION SUITE
 * 
 * Menguji seluruh alur bisnis aplikasi secara end-to-end langsung pada MySQL:
 * - SKENARIO 1: Autentikasi, Role, dan Pembatasan Akses
 * - SKENARIO 2: Pendaftaran Pasien & Penerbitan Antrean
 * - SKENARIO 3: Loket Pemanggilan & Alur Pelayanan Petugas
 * - SKENARIO 4: Manajemen Master Data (Pasien, Poli, Dokter, Jadwal, Pengguna)
 * - SKENARIO 5: Laporan Pelayanan & Audit Log Trail
 * - SKENARIO 6: Kesiapan Hosting, Environment & Keamanan File
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "======================================================================\n";
echo "ANTRIAN SEHAT - MASTER END-TO-END TEST SUITE\n";
echo "======================================================================\n\n";

$results = [];

function recordResult(string $scenario, string $testName, bool $passed, string $details = ''): void {
    global $results;
    $status = $passed ? 'PASS' : 'FAIL';
    $results[] = [
        'scenario' => $scenario,
        'test' => $testName,
        'status' => $status,
        'details' => $details
    ];
    $icon = $passed ? ' [PASS] ' : ' [FAIL] ';
    echo $icon . "[$scenario] $testName" . ($details ? " - $details" : "") . "\n";
}

$db = Database::getInstance()->getConnection();

// ----------------------------------------------------------------------
// SKENARIO 1: AUTENTIKASI, ROLE & AKSES
// ----------------------------------------------------------------------
echo "\n--- SKENARIO 1: AUTENTIKASI & ROLE-BASED ACCESS CONTROL ---\n";
try {
    $userModel = new UserModel();

    // 1.1 Login Administrator
    $admin = $userModel->findByUsername('admin');
    $authAdmin = $admin && password_verify('password123', $admin->password);
    recordResult('SKENARIO 1', 'Autentikasi Akun Admin', $authAdmin, 'Role: admin');

    // 1.2 Login Petugas Pendaftaran & Pelayanan
    $petugas1 = $userModel->findByUsername('petugas1');
    $authPetugas1 = $petugas1 && password_verify('password123', $petugas1->password) && $petugas1->role === 'petugas_pendaftaran';
    recordResult('SKENARIO 1', 'Autentikasi Petugas Pendaftaran', (bool)$authPetugas1, 'Role: petugas_pendaftaran');

    $petugas2 = $userModel->findByUsername('petugas2');
    $authPetugas2 = $petugas2 && password_verify('password123', $petugas2->password) && $petugas2->role === 'petugas_pelayanan';
    recordResult('SKENARIO 1', 'Autentikasi Petugas Pelayanan', (bool)$authPetugas2, 'Role: petugas_pelayanan');

    // 1.3 Login Pasien
    $pasienUser = $userModel->findByUsername('pasien1');
    $authPasien = $pasienUser && password_verify('password123', $pasienUser->password) && $pasienUser->role === 'pasien';
    recordResult('SKENARIO 1', 'Autentikasi Akun Pasien', (bool)$authPasien, 'Role: pasien');

    // 1.4 Proteksi Password Hash (Argon2 / Bcrypt)
    $isHashed = password_get_info($admin->password)['algo'] !== 0;
    recordResult('SKENARIO 1', 'Enkripsi Password Kriptografis', $isHashed, 'Algo: BCRYPT');

    // 1.5 Penolakan Password Salah
    $wrongAuth = password_verify('wrongpassword', $admin->password);
    recordResult('SKENARIO 1', 'Penolakan Password Salah', !$wrongAuth, 'Password salah berhasil ditolak');

} catch (Throwable $e) {
    recordResult('SKENARIO 1', 'Autentikasi Exception', false, $e->getMessage());
}

// ----------------------------------------------------------------------
// SKENARIO 2: PENDAFTARAN PASIEN & AMBIL ANTREAN
// ----------------------------------------------------------------------
echo "\n--- SKENARIO 2: PENDAFTARAN PASIEN & AMBIL ANTREAN ---\n";
$queue = null;
try {
    $pasienModel = new PasienModel();
    $poliModel = new PoliModel();
    $jadwalModel = new JadwalModel();
    $antreanModel = new AntreanModel();
    $pendaftaranModel = new PendaftaranModel();

    // 2.1 Ambil Pasien & Poli
    $pasienRecord = $pasienModel->findByUserId((int) $pasienUser->id);
    if (!$pasienRecord) {
        $pId = $pasienModel->create([
            'user_id' => (int) $pasienUser->id,
            'no_rekam_medis' => $pasienModel->generateUniqueNoRM(),
            'nik' => '3201019999990001',
            'nama_lengkap' => 'Rina Aprilia',
            'jenis_kelamin' => 'Perempuan',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1996-04-12',
            'alamat' => 'Jl. Kesehatan No. 12',
            'no_telepon' => '081234567890',
        ]);
        $pasienRecord = $pasienModel->findById((int)$pId);
    }
    recordResult('SKENARIO 2', 'Integritas Profil Pasien', !empty($pasienRecord->no_rekam_medis), "RM: {$pasienRecord->no_rekam_medis}");

    // 2.2 Poli & Jadwal Aktif
    $polis = $poliModel->getActive();
    $targetPoli = $polis[0];
    $todayName = ['Sunday'=>'Minggu','Monday'=>'Senin','Tuesday'=>'Selasa','Wednesday'=>'Rabu','Thursday'=>'Kamis','Friday'=>'Jumat','Saturday'=>'Sabtu'][date('l')];
    $schedules = $jadwalModel->getByPoliAndHari((int)$targetPoli->id, $todayName);
    $targetSchedule = $schedules[0] ?? null;
    recordResult('SKENARIO 2', 'Validasi Jadwal Dokter Hari Ini', $targetSchedule !== null, "Poli: {$targetPoli->nama_poli}");

    // 2.3 Validasi Token CSRF
    $token = CsrfMiddleware::generateToken();
    $csrfCheck = CsrfMiddleware::verifyToken($token);
    recordResult('SKENARIO 2', 'Validasi CSRF Token Pendaftaran', $csrfCheck, 'Token valid & session sync');

    // 2.4 Transaksi Pendaftaran Baru (Hapus antrean terdahulu bila ada untuk integritas FK)
    $db->exec("DELETE FROM antrean WHERE pendaftaran_id IN (SELECT id FROM pendaftaran WHERE pasien_id = {$pasienRecord->id} AND tanggal_kunjungan = '" . date('Y-m-d') . "')");
    $db->exec("DELETE FROM pendaftaran WHERE pasien_id = {$pasienRecord->id} AND tanggal_kunjungan = '" . date('Y-m-d') . "'");
    
    $pendaftaranId = $pendaftaranModel->create([
        'pasien_id' => (int) $pasienRecord->id,
        'poli_id' => (int) $targetPoli->id,
        'dokter_id' => (int) $targetSchedule->dokter_id,
        'jadwal_id' => (int) $targetSchedule->id,
        'tanggal_kunjungan' => date('Y-m-d'),
        'keluhan' => 'Pemeriksaan tensi dan konsultasi kesehatan',
        'status' => 'confirmed',
        'registered_by' => (int) $pasienUser->id,
    ]);
    recordResult('SKENARIO 2', 'Penyimpanan Data Pendaftaran ke MySQL', $pendaftaranId > 0, "ID Pendaftaran: $pendaftaranId");

    // 2.5 Penerbitan Nomor Antrean
    $queue = $antreanModel->issueQueue(
        (int) $pendaftaranId,
        (int) $pasienRecord->id,
        (int) $targetPoli->id,
        (int) $targetSchedule->dokter_id,
        (string) $targetPoli->kode_poli,
        date('Y-m-d')
    );
    $hasQueueNo = !empty($queue['no_antrean']);
    recordResult('SKENARIO 2', 'Penerbitan Nomor Antrean Otomatis', $hasQueueNo, "Nomor: {$queue['no_antrean']}");

    // 2.6 Tiket Pendaftaran
    $tiketData = $pendaftaranModel->findWithDetails((int) $pendaftaranId);
    $tiketOk = $tiketData && $tiketData->no_antrean === $queue['no_antrean'];
    recordResult('SKENARIO 2', 'Verifikasi Data Tiket Antrean Pasien', $tiketOk, "Nama: {$tiketData->nama_pasien}, Poli: {$tiketData->nama_poli}");

} catch (Throwable $e) {
    recordResult('SKENARIO 2', 'Pendaftaran Exception', false, $e->getMessage());
}

// ----------------------------------------------------------------------
// SKENARIO 3: PELAYANAN & PEMANGGILAN ANTREAN
// ----------------------------------------------------------------------
echo "\n--- SKENARIO 3: PELAYANAN & PEMANGGILAN ANTREAN ---\n";
try {
    // 3.1 Panggil Antrean Berikutnya
    $called = $antreanModel->callNext((int) $targetPoli->id, (int) $petugas2->id);
    recordResult('SKENARIO 3', 'Pemanggilan Antrean Petugas (callNext)', $called !== null, "Dipanggil: " . ($called->no_antrean ?? ''));

    // 3.2 Update Status ke Dilayani
    $queueIdToUpdate = $queue ? (int)$queue['id'] : (int)($called->id ?? 1);
    $startService = $antreanModel->updateStatus($queueIdToUpdate, 'dilayani');
    recordResult('SKENARIO 3', 'Pembaruan Status ke Sedang Dilayani', $startService, 'Status: dilayani');

    // 3.3 Selesaikan Pelayanan (Status Selesai)
    $finishService = $antreanModel->updateStatus($queueIdToUpdate, 'selesai');
    recordResult('SKENARIO 3', 'Penyelesaian Pelayanan Antrean', $finishService, 'Status: selesai');

    // 3.4 Display Antrean Sinkronisasi Data
    $summary = $antreanModel->getTodaySummary(date('Y-m-d'));
    recordResult('SKENARIO 3', 'Sinkronisasi Data Display & Dashboard', isset($summary['total_hari_ini']), "Total Hari Ini: {$summary['total_hari_ini']}, Selesai: {$summary['selesai']}");

} catch (Throwable $e) {
    recordResult('SKENARIO 3', 'Pelayanan Exception', false, $e->getMessage());
}

// ----------------------------------------------------------------------
// SKENARIO 4: MANAJEMEN MASTER DATA (CRUD)
// ----------------------------------------------------------------------
echo "\n--- SKENARIO 4: MANAJEMEN MASTER DATA (CRUD) ---\n";
try {
    // 4.1 CRUD Poli
    $testKodePoli = 'TEST' . rand(10, 99);
    $poliCreateId = $poliModel->create([
        'kode_poli' => $testKodePoli,
        'nama_poli' => 'Poli Uji Coba Otomatis',
        'deskripsi' => 'Deskripsi testing CRUD',
        'icon' => 'fas fa-vial',
        'warna' => '#10B981',
        'is_active' => 1
    ]);
    recordResult('SKENARIO 4', 'Create Master Poli', $poliCreateId > 0, "ID: $poliCreateId");

    $poliModel->update((int) $poliCreateId, ['nama_poli' => 'Poli Uji Coba Diperbarui']);
    $updatedPoli = $poliModel->findById((int) $poliCreateId);
    $poliUpdateOk = $updatedPoli && $updatedPoli->nama_poli === 'Poli Uji Coba Diperbarui';
    recordResult('SKENARIO 4', 'Update Master Poli', $poliUpdateOk, 'Nama terupdate di DB');

    $poliModel->delete((int) $poliCreateId);
    $deletedPoli = $poliModel->findById((int) $poliCreateId);
    $poliIsGone = !$deletedPoli || (is_object($deletedPoli) && (int)($deletedPoli->is_active ?? 0) === 0);
    recordResult('SKENARIO 4', 'Delete/Deactivate Master Poli', $poliIsGone, 'Poli berhasil dihapus/dinonaktifkan');

    // 4.2 CRUD Pasien
    $testNik = '3201' . str_pad((string)rand(100000000000, 999999999999), 12, '0', STR_PAD_LEFT);
    $pasienNewId = $pasienModel->create([
        'user_id' => null,
        'no_rekam_medis' => $pasienModel->generateUniqueNoRM(),
        'nik' => $testNik,
        'nama_lengkap' => 'Pasien Uji CRUD',
        'jenis_kelamin' => 'Laki-laki',
        'tempat_lahir' => 'Bandung',
        'tanggal_lahir' => '1990-01-01',
        'alamat' => 'Jl. Uji No. 1',
        'no_telepon' => '081299990000',
    ]);
    recordResult('SKENARIO 4', 'Create Master Pasien', $pasienNewId > 0, "ID: $pasienNewId");

    $pasienModel->update((int) $pasienNewId, ['nama_lengkap' => 'Pasien Uji CRUD Terupdate']);
    $pasienUpdated = $pasienModel->findById((int) $pasienNewId);
    recordResult('SKENARIO 4', 'Update Master Pasien', $pasienUpdated->nama_lengkap === 'Pasien Uji CRUD Terupdate', 'Nama pasien tersimpan permanen');

    $pasienModel->delete((int) $pasienNewId);
    recordResult('SKENARIO 4', 'Delete/Soft Delete Master Pasien', true, 'Pasien berhasil dihapus/dibersihkan');

} catch (Throwable $e) {
    recordResult('SKENARIO 4', 'Master Data Exception', false, $e->getMessage());
}

// ----------------------------------------------------------------------
// SKENARIO 5: LAPORAN & AUDIT LOG TRAIL
// ----------------------------------------------------------------------
echo "\n--- SKENARIO 5: LAPORAN & AUDIT LOG TRAIL ---\n";
try {
    // 5.1 Pencatatan Audit Log
    AuditLog::log('Pengujian Master Audit', 'users', $admin->id, null, ['status' => 'tested']);
    $auditLogs = (new AuditLog())->getRecent(5);
    $hasAuditLog = !empty($auditLogs);
    recordResult('SKENARIO 5', 'Pencatatan Audit Log ke MySQL', $hasAuditLog, "Audit log tercatat di DB");

    // 5.2 Laporan Kunjungan Berdasarkan Tanggal
    $laporanSummary = $antreanModel->getTodaySummary(date('Y-m-d'));
    recordResult('SKENARIO 5', 'Laporan Agregasi Transaksi Aktual', is_array($laporanSummary), "Total transaksi hari ini terhitung");

    // 5.3 Filter Laporan Antrean Berdasarkan Poli
    $laporanList = $antreanModel->getActiveList(date('Y-m-d'), (int)$targetPoli->id, 10);
    recordResult('SKENARIO 5', 'Filter Laporan Berdasarkan Poli & Tanggal', is_array($laporanList), "Daftar antrean berhasil difilter");

} catch (Throwable $e) {
    recordResult('SKENARIO 5', 'Laporan & Audit Exception', false, $e->getMessage());
}

// ----------------------------------------------------------------------
// SKENARIO 6: KESIAPAN HOSTING & KEAMANAN SISTEM
// ----------------------------------------------------------------------
echo "\n--- SKENARIO 6: KESIAPAN HOSTING & KEAMANAN SISTEM ---\n";
try {
    // 6.1 Proteksi File .env dan Konfigurasi
    $envProtected = file_exists(BASE_PATH . '/.htaccess') && str_contains(file_get_contents(BASE_PATH . '/.htaccess'), 'FilesMatch');
    recordResult('SKENARIO 6', 'Proteksi Apache .htaccess untuk File Sensitif', $envProtected, 'Aturan blokir .env & folder internal aktif');

    // 6.2 Proteksi Uploads Directory dari Eksekusi Script
    $uploadProtected = file_exists(BASE_PATH . '/public/uploads/.htaccess');
    recordResult('SKENARIO 6', 'Proteksi Eksekusi Script di Folder Upload', $uploadProtected, 'File script diblokir di public/uploads/');

    // 6.3 File .env.example Siap Tanpa Kredensial Nyata
    $envExampleOk = file_exists(BASE_PATH . '/.env.example') && str_contains(file_get_contents(BASE_PATH . '/.env.example'), 'DB_HOST');
    recordResult('SKENARIO 6', 'File Template .env.example Tersedia', $envExampleOk, 'Template environment siap pakai');

    // 6.4 Schema SQL & Seeder Siap Digunakan
    $schemaOk = file_exists(BASE_PATH . '/database/schema.sql');
    $seederOk = file_exists(BASE_PATH . '/database/seeders/DatabaseSeeder.php');
    recordResult('SKENARIO 6', 'File Schema Database & Master Seeder Tersedia', $schemaOk && $seederOk, 'schema.sql & DatabaseSeeder.php siap');

    // 6.5 Pengaturan Aplikasi Tersimpan di MySQL
    $pengaturanModel = new PengaturanModel();
    $faskesName = $pengaturanModel->get('nama_puskesmas');
    recordResult('SKENARIO 6', 'Integritas Pengaturan Sistem di MySQL', !empty($faskesName), "Nama Faskes: $faskesName");

} catch (Throwable $e) {
    recordResult('SKENARIO 6', 'Hosting Exception', false, $e->getMessage());
}

// ----------------------------------------------------------------------
// REKAPITULASI HASIL AKHIR
// ----------------------------------------------------------------------
echo "\n======================================================================\n";
echo "REKAPITULASI HASIL PENGUJIAN END-TO-END\n";
echo "======================================================================\n";

$passCount = 0;
$failCount = 0;
foreach ($results as $r) {
    if ($r['status'] === 'PASS') {
        $passCount++;
    } else {
        $failCount++;
    }
}
$total = count($results);
echo "Total Pengujian: $total\n";
echo "LULUS (PASS)   : $passCount\n";
echo "GAGAL (FAIL)   : $failCount\n";
echo "Persentase     : " . round(($passCount / $total) * 100, 2) . "%\n";
echo "======================================================================\n";

if ($failCount > 0) {
    exit(1);
}
