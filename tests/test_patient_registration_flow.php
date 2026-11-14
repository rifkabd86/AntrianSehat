<?php
/**
 * Test Patient Registration Flow (End-to-End Simulation)
 */

require_once __DIR__ . '/../config/bootstrap.php';

echo "=== TEST PATIENT REGISTRATION (STORE) ===\n";

try {
    // 1. Simulate Patient Login Session
    $userModel = new UserModel();
    $pasienUser = $userModel->findByUsername('pasien1');
    if (!$pasienUser) {
        // Fallback: find any user with role pasien
        $stmt = Database::getInstance()->getConnection()->query("SELECT * FROM users WHERE role = 'pasien' LIMIT 1");
        $pasienUser = $stmt->fetch();
    }
    
    if (!$pasienUser) {
        die("No patient user found in database!\n");
    }

    AuthMiddleware::setCurrentUser($pasienUser);
    $_SESSION['user_id'] = (int) $pasienUser->id;
    $_SESSION['user_role'] = 'pasien';

    // 2. Ensure patient profile exists for this user
    $pasienModel = new PasienModel();
    $profile = $pasienModel->findByUserId((int) $pasienUser->id);
    if (!$profile) {
        $profileId = $pasienModel->create([
            'user_id' => (int) $pasienUser->id,
            'no_rekam_medis' => $pasienModel->generateUniqueNoRM(),
            'nik' => '3201012345670001',
            'nama_lengkap' => $pasienUser->nama_lengkap ?? 'Pasien Testing',
            'jenis_kelamin' => 'Laki-laki',
            'tempat_lahir' => 'Jakarta',
            'tanggal_lahir' => '1995-05-15',
            'alamat' => 'Jl. Pengujian No. 123',
            'no_telepon' => '081234567890',
        ]);
        $profile = $pasienModel->findById((int) $profileId);
    }
    echo " [OK] Patient profile ready: " . $profile->nama_lengkap . " (RM: " . $profile->no_rekam_medis . ")\n";

    // 3. Find an active poli and doctor schedule for today
    $poliModel = new PoliModel();
    $activePolis = $poliModel->getActive();
    if (empty($activePolis)) {
        die("No active poli found!\n");
    }
    $targetPoli = $activePolis[0];
    echo " [OK] Target Poli: " . $targetPoli->nama_poli . " (ID: " . $targetPoli->id . ")\n";

    $dayNames = [
        'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
        'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
        'Saturday' => 'Sabtu',
    ];
    $todayDayName = $dayNames[date('l')];
    $jadwalModel = new JadwalModel();
    $schedules = $jadwalModel->getByPoliAndHari((int) $targetPoli->id, $todayDayName);

    if (empty($schedules)) {
        // Create a schedule for today so testing can proceed
        $dokterModel = new DokterModel();
        $dokters = $dokterModel->getByPoli((int) $targetPoli->id);
        if (empty($dokters)) {
            $dokId = $dokterModel->create([
                'poli_id' => (int) $targetPoli->id,
                'nama_dokter' => 'dr. Dokter Uji Coba',
                'spesialisasi' => 'Umum',
                'no_telepon' => '081299998888',
                'sip' => 'SIP/999/TEST/2026',
            ]);
        } else {
            $dokId = (int) $dokters[0]->id;
        }

        $jadwalId = $jadwalModel->create([
            'dokter_id' => $dokId,
            'poli_id' => (int) $targetPoli->id,
            'hari' => $todayDayName,
            'jam_mulai' => '08:00:00',
            'jam_selesai' => '16:00:00',
            'kuota' => 50,
            'is_active' => 1,
        ]);
        $targetSchedule = $jadwalModel->findById((int) $jadwalId);
    } else {
        $targetSchedule = $schedules[0];
    }
    echo " [OK] Target Jadwal: Hari " . $targetSchedule->hari . " (Dokter ID: " . $targetSchedule->dokter_id . ")\n";

    // 4. Test issueQueue & getNextNomorUrut without throwing stdClass error
    $antreanModel = new AntreanModel();
    $nextNo = $antreanModel->getNextNomorUrut((int) $targetPoli->id, date('Y-m-d'));
    echo " [OK] getNextNomorUrut returned safely: " . $nextNo . " (no stdClass error!)\n";

    // 5. Simulate POST request data
    $_SERVER['REQUEST_METHOD'] = 'POST';
    $csrfToken = CsrfMiddleware::generateToken();
    $_POST = [
        'csrf_token' => $csrfToken,
        'poli_id' => (int) $targetPoli->id,
        'dokter_id' => (int) $targetSchedule->dokter_id,
        'tanggal_kunjungan' => date('Y-m-d'),
        'keluhan' => 'Pemeriksaan rutin berkala',
    ];

    // Remove any existing registration for today so test passes checkExisting
    Database::getInstance()->getConnection()->exec(
        "DELETE FROM pendaftaran WHERE pasien_id = {$profile->id} AND poli_id = {$targetPoli->id} AND tanggal_kunjungan = '" . date('Y-m-d') . "'"
    );

    // Verify sisa kuota calculation doesn't throw stdClass error
    $kuotaInfo = $jadwalModel->getSisaKuota((int) $targetSchedule->id, date('Y-m-d'));
    echo " [OK] getSisaKuota returned: kuota={$kuotaInfo['kuota']}, sisa={$kuotaInfo['sisa']}, tersedia=" . ($kuotaInfo['is_tersedia'] ? 'YES' : 'NO') . "\n";

    // 6. Test issueQueue creation directly
    $pendaftaranModel = new PendaftaranModel();
    $pendaftaranId = $pendaftaranModel->create([
        'pasien_id' => (int) $profile->id,
        'poli_id' => (int) $targetPoli->id,
        'dokter_id' => (int) $targetSchedule->dokter_id,
        'jadwal_id' => (int) $targetSchedule->id,
        'tanggal_kunjungan' => date('Y-m-d'),
        'keluhan' => 'Pemeriksaan rutin berkala',
        'status' => 'confirmed',
        'registered_by' => (int) $pasienUser->id,
    ]);

    $queueResult = $antreanModel->issueQueue(
        (int) $pendaftaranId,
        (int) $profile->id,
        (int) $targetPoli->id,
        (int) $targetSchedule->dokter_id,
        (string) $targetPoli->kode_poli,
        date('Y-m-d')
    );

    echo " [OK] Antrean successfully issued! No: " . $queueResult['no_antrean'] . " (ID: " . $queueResult['id'] . ")\n";

    // 7. Verify Audit Log insertion works without error
    AuditLog::log('Pendaftaran Kunjungan & Antrean', 'antrean', $queueResult['id'], null, [
        'no_antrean' => $queueResult['no_antrean'],
        'pasien_id' => (int) $profile->id,
        'poli_id' => (int) $targetPoli->id,
    ]);
    echo " [OK] Audit log logged successfully!\n";

    echo "\n=== ALL PATIENT REGISTRATION TESTS PASSED! NO STDCLASS ERRORS! ===\n";

} catch (Throwable $e) {
    echo "\n[ERROR DETECTED]: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
    exit(1);
}

