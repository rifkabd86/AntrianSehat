<?php
/**
 * ANTRIAN SEHAT - Master Database Seeder
 * 
 * Mengisi master data awal aplikasi untuk instalasi baru / hosting produksi:
 * 1. Akun Awal (Admin, Petugas Pendaftaran, Petugas Pelayanan, Akun Pasien Contoh)
 * 2. Data Master Poliklinik
 * 3. Data Master Dokter & Jadwal Praktik
 * 4. Pengaturan Sistem & Identitas Faskes
 * 
 * Penggunaan CLI: php database/seeders/DatabaseSeeder.php
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Seeder hanya dapat dijalankan melalui CLI.');
}

require dirname(__DIR__, 2) . '/config/bootstrap.php';

echo "=====================================================\n";
echo "ANTRIAN SEHAT - DATABASE SEEDER\n";
echo "=====================================================\n";

$db = Database::getInstance()->getConnection();

try {
    $db->beginTransaction();

    // 1. Akun Pengguna Awal
    echo "1. Seeding Akun Pengguna...\n";
    $users = [
        ['admin', 'Administrator Puskesmas', 'admin@antriansehat.id', '081200000001', 'admin'],
        ['petugas1', 'Siti Rahayu, A.Md.RMIK', 'petugas1@antriansehat.id', '081200000002', 'petugas_pendaftaran'],
        ['petugas2', 'Ahmad Fauzi, S.Kep', 'petugas2@antriansehat.id', '081200000003', 'petugas_pelayanan'],
        ['pasien1', 'Rina Aprilia', 'pasien1@antriansehat.id', '081234567890', 'pasien'],
    ];

    $stmtUser = $db->prepare(
        'INSERT INTO users (username, password, nama_lengkap, email, no_telepon, role, is_active, created_at, updated_at)
         VALUES (:username, :password, :name, :email, :phone, :role, 1, NOW(), NOW())
         ON DUPLICATE KEY UPDATE nama_lengkap = VALUES(nama_lengkap), role = VALUES(role)'
    );

    foreach ($users as [$username, $name, $email, $phone, $role]) {
        $stmtUser->execute([
            'username' => $username,
            'password' => password_hash('password123', PASSWORD_DEFAULT),
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => $role,
        ]);
    }
    echo "   [OK] Akun pengguna berhasil dibuat (Password default: password123)\n";

    // 2. Data Master Poli
    echo "2. Seeding Data Master Poli...\n";
    $polis = [
        ['U01', 'Poli Umum', 'Pelayanan pemeriksaan kesehatan umum dan konsultasi medis dasar.', 'fas fa-stethoscope', '#2563EB'],
        ['G01', 'Poli Gigi & Mulut', 'Pemeriksaan kesehatan gigi, pencabutan, pembersihan karang, dan penambalan.', 'fas fa-tooth', '#10B981'],
        ['K01', 'Poli KIA & KB', 'Pelayanan kesehatan ibu, imunisasi balita, dan konsultasi keluarga berencana.', 'fas fa-baby', '#F59E0B'],
        ['L01', 'Poli Lansia', 'Pelayanan khusus kesehatan geriatri dan penyakit degeneratif.', 'fas fa-user-doctor', '#8B5CF6'],
        ['M01', 'Poli Mata', 'Pemeriksaan refraksi mata dan penanganan keluhan penglihatan.', 'fas fa-eye', '#06B6D4'],
    ];

    $stmtPoli = $db->prepare(
        'INSERT INTO poli (kode_poli, nama_poli, deskripsi, icon, warna, is_active, created_at, updated_at)
         VALUES (:kode, :nama, :desc, :icon, :warna, 1, NOW(), NOW())
         ON DUPLICATE KEY UPDATE nama_poli = VALUES(nama_poli), icon = VALUES(icon), warna = VALUES(warna)'
    );

    foreach ($polis as [$kode, $nama, $desc, $icon, $warna]) {
        $stmtPoli->execute([
            'kode' => $kode,
            'nama' => $nama,
            'desc' => $desc,
            'icon' => $icon,
            'warna' => $warna,
        ]);
    }
    echo "   [OK] Data master poli berhasil dibuat (5 poliklinik)\n";

    // 3. Data Master Dokter
    echo "3. Seeding Data Master Dokter...\n";
    $dokters = [
        ['U01', 'DOK-001', 'dr. Andi Pratama', 'Dokter Umum', '081211110001', 'andi.pratama@puskesmas.id'],
        ['U01', 'DOK-002', 'dr. Sarah Wijaya', 'Dokter Umum', '081211110002', 'sarah.wijaya@puskesmas.id'],
        ['G01', 'DOK-003', 'drg. Hendra Gunawan', 'Dokter Gigi', '081211110003', 'hendra.gunawan@puskesmas.id'],
        ['K01', 'DOK-004', 'dr. Maya Kartika, Sp.A', 'Spesialis Anak', '081211110004', 'maya.kartika@puskesmas.id'],
        ['L01', 'DOK-005', 'dr. Bambang Irawan', 'Kesehatan Lansia', '081211110005', 'bambang.irawan@puskesmas.id'],
        ['M01', 'DOK-006', 'dr. Lisa Nurhaliza, Sp.M', 'Spesialis Mata', '081211110006', 'lisa.nurhaliza@puskesmas.id'],
    ];

    $stmtDok = $db->prepare(
        'INSERT INTO dokter (poli_id, kode_dokter, nama_dokter, spesialisasi, no_telepon, email, is_active, created_at, updated_at)
         SELECT id, :kode, :nama, :spesialis, :telp, :email, 1, NOW(), NOW()
         FROM poli WHERE kode_poli = :kodepoli LIMIT 1
         ON DUPLICATE KEY UPDATE nama_dokter = VALUES(nama_dokter), spesialisasi = VALUES(spesialisasi)'
    );

    foreach ($dokters as [$kodepoli, $kode, $nama, $spesialis, $telp, $email]) {
        $stmtDok->execute([
            'kodepoli' => $kodepoli,
            'kode' => $kode,
            'nama' => $nama,
            'spesialis' => $spesialis,
            'telp' => $telp,
            'email' => $email,
        ]);
    }
    echo "   [OK] Data master dokter berhasil dibuat (6 dokter)\n";

    // 4. Data Master Jadwal Praktik
    echo "4. Seeding Jadwal Praktik Dokter...\n";
    $hariList = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    
    // Ambil daftar dokter yang aktif
    $allDoctors = $db->query("SELECT id, poli_id FROM dokter WHERE is_active = 1")->fetchAll();
    $stmtJadwal = $db->prepare(
        'INSERT INTO jadwal_dokter (dokter_id, poli_id, hari, jam_mulai, jam_selesai, kuota, is_active, created_at, updated_at)
         VALUES (:did, :pid, :hari, :mulai, :selesai, :kuota, 1, NOW(), NOW())
         ON DUPLICATE KEY UPDATE kuota = VALUES(kuota)'
    );

    foreach ($allDoctors as $doc) {
        $did = is_object($doc) ? $doc->id : $doc['id'];
        $pid = is_object($doc) ? $doc->poli_id : $doc['poli_id'];
        foreach ($hariList as $hari) {
            $stmtJadwal->execute([
                'did' => $did,
                'pid' => $pid,
                'hari' => $hari,
                'mulai' => '08:00:00',
                'selesai' => '14:00:00',
                'kuota' => 30,
            ]);
        }
    }
    echo "   [OK] Jadwal praktik harian (Senin - Sabtu) berhasil diaktifkan.\n";

    // 5. Data Master Pengaturan Sistem
    echo "5. Seeding Pengaturan Sistem & Identitas Faskes...\n";
    $settings = [
        ['nama_puskesmas', 'Puskesmas Sehat Sentosa', 'identitas', 'Nama resmi fasilitas kesehatan'],
        ['alamat_puskesmas', 'Jl. Merdeka No. 100, Jakarta Pusat', 'identitas', 'Alamat lengkap faskes'],
        ['telepon_puskesmas', '(021) 555-0199', 'identitas', 'Nomor telepon resmi faskes'],
        ['email_puskesmas', 'puskesmas@sehatsentosa.go.id', 'identitas', 'Alamat email resmi faskes'],
        ['kepala_puskesmas', 'dr. H. Budi Santoso, M.Kes', 'identitas', 'Nama dan gelar kepala puskesmas'],
        ['nip_kepala', '19780512 200312 1 002', 'identitas', 'NIP Kepala Puskesmas'],
        ['running_text_display', 'Selamat Datang di Puskesmas Sehat Sentosa • Harap perhatikan nomor antrean pada layar monitor • Siapkan Kartu Identitas (KTP/BPJS) saat dipanggil ke ruang pemeriksaan • Jaga kebersihan dan ketertiban area ruang tunggu • Layanan Pengaduan: 0812-0000-0000', 'display', 'Teks berjalan pada layar display monitor'],
        ['audio_chime_aktif', '1', 'display', 'Status aktif suara bel pemanggilan antrean (1: aktif, 0: nonaktif)'],
        ['kuota_harian_default', '30', 'antrean', 'Kuota pendaftaran harian standar per poli'],
    ];

    $stmtSet = $db->prepare(
        'INSERT INTO pengaturan (kunci, nilai, kategori, keterangan, updated_at)
         VALUES (:kunci, :nilai, :kategori, :ket, NOW())
         ON DUPLICATE KEY UPDATE nilai = VALUES(nilai)'
    );

    foreach ($settings as [$kunci, $nilai, $kategori, $ket]) {
        $stmtSet->execute([
            'kunci' => $kunci,
            'nilai' => $nilai,
            'kategori' => $kategori,
            'ket' => $ket,
        ]);
    }
    echo "   [OK] Pengaturan default sistem berhasil disimpan.\n";

    $db->commit();
    echo "\n=== SEEDER SELESAI DENGAN SUKSES! ===\n";
    echo "Semua master data dan akun awal siap digunakan.\n";

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo "\n[ERROR SEEDING]: " . $e->getMessage() . "\n";
    exit(1);
}

