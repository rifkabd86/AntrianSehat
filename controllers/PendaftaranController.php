<?php
/**
 * ANTRIAN SEHAT - Pendaftaran Controller
 * 
 * Mengelola pendaftaran kunjungan pasien dan penerbitan nomor antrean otomatis.
 */

class PendaftaranController extends Controller
{
    private PendaftaranModel $pendaftaranModel;
    private PasienModel $pasienModel;
    private PoliModel $poliModel;
    private DokterModel $dokterModel;
    private JadwalModel $jadwalModel;
    private AntreanModel $antreanModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->pendaftaranModel = new PendaftaranModel();
        $this->pasienModel = new PasienModel();
        $this->poliModel = new PoliModel();
        $this->dokterModel = new DokterModel();
        $this->jadwalModel = new JadwalModel();
        $this->antreanModel = new AntreanModel();
    }

    /**
     * Tampilkan riwayat pendaftaran atau form
     */
    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'pasien';
        
        if ($role === 'pasien') {
            $this->create();
            return;
        }
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran']);

        $filters = [
            'tanggal' => trim((string) ($_GET['tanggal'] ?? date('Y-m-d'))),
            'poli_id' => max(0, (int) ($_GET['poli_id'] ?? 0)),
            'status'  => (string) ($_GET['status'] ?? ''),
            'search'  => mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100),
        ];
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['tanggal'])) {
            $filters['tanggal'] = date('Y-m-d');
        }
        if (!in_array($filters['status'], ['', 'confirmed', 'cancelled'], true)) {
            $filters['status'] = '';
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        $total = $this->pendaftaranModel->countList($filters);
        $totalPages = max(1, (int) ceil($total / $limit));
        $page = min($page, $totalPages);
        $offset = ($page - 1) * $limit;
        $list = $this->pendaftaranModel->getList($filters, $limit, $offset);
        $poliList = $this->poliModel->getActive();

        $this->view('pendaftaran/index', [
            'title' => 'Pendaftaran Pasien',
            'layout' => 'main',
            'list' => $list,
            'poliList' => $poliList,
            'filters' => $filters,
            'page' => $page,
            'perPage' => $limit,
            'total' => $total,
            'totalPages' => $totalPages,
        ]);
    }

    /**
     * Form Pendaftaran Baru (Sesuai Mockup UI)
     */
    public function create(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran', 'pasien']);
        $role = $_SESSION['user_role'] ?? '';
        $userId = $_SESSION['user_id'] ?? 0;

        $pasienDefault = null;
        if ($role === 'pasien') {
            $pasienDefault = $this->pasienModel->findByUserId($userId);
            if (!$pasienDefault) {
                Helper::flash('error', 'Profil pasien belum tersedia. Silakan hubungi petugas pendaftaran.', 'error');
                $this->redirect('dashboard');
            }
        }

        $poliList = $this->poliModel->getActive();
        $dokterList = $this->dokterModel->getAllWithPoli(true);

        $this->view('pendaftaran/create', [
            'title' => 'Pendaftaran Pasien & Ambil Antrian',
            'layout' => 'main',
            'poliList' => $poliList,
            'dokterList' => $dokterList,
            'pasienDefault' => $pasienDefault,
        ]);
    }

    /**
     * Proses Simpan Pendaftaran & Ambil Antrean
     */
    public function store(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran', 'pasien']);
        CsrfMiddleware::validateRequest();

        $role = $_SESSION['user_role'] ?? '';
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $namaLengkap = trim((string) ($_POST['nama_lengkap'] ?? ''));
        $tanggalLahir = trim((string) ($_POST['tanggal_lahir'] ?? ''));
        $jenisKelamin = trim((string) ($_POST['jenis_kelamin'] ?? ''));
        $noTelepon = trim((string) ($_POST['no_telepon'] ?? ''));
        $nik = trim((string) ($_POST['nik'] ?? ''));
        $tempatLahir = trim((string) ($_POST['tempat_lahir'] ?? ''));
        $alamat = trim((string) ($_POST['alamat'] ?? ''));
        $poliId = (int)($_POST['poli_id'] ?? 0);
        $dokterId = (int)($_POST['dokter_id'] ?? 0);
        $keluhan = mb_substr(trim((string) ($_POST['keluhan'] ?? 'Pemeriksaan umum')), 0, 1000);
        
        $inputTgl = trim((string)($_POST['tanggal_kunjungan'] ?? ''));
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $inputTgl) && $inputTgl >= date('Y-m-d')) {
            $tanggalKunjungan = $inputTgl;
        } else {
            $tanggalKunjungan = date('Y-m-d');
        }

        $pasien = null;
        $newPatient = false;
        if ($role === 'pasien') {
            $pasien = $this->pasienModel->findByUserId($userId);
            if (!$pasien) {
                Helper::flash('error', 'Profil pasien belum tersedia. Silakan hubungi petugas pendaftaran.', 'error');
                $this->redirect('dashboard');
            }
            $namaLengkap = (string) $pasien->nama_lengkap;
            $tanggalLahir = (string) $pasien->tanggal_lahir;
            $jenisKelamin = (string) $pasien->jenis_kelamin;
            $noTelepon = (string) $pasien->no_telepon;
        } else {
            if (strlen($nik) !== 16 || !ctype_digit($nik)) {
                Helper::flash('error', 'NIK pasien harus terdiri dari 16 digit angka.', 'error');
                Helper::setOldInput(array_intersect_key($_POST, array_flip(['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'no_telepon', 'alamat', 'poli_id', 'dokter_id', 'keluhan'])));
                $this->redirect('pendaftaran/create');
            }
            $pasien = $this->pasienModel->findByNIK($nik);
            if (!$pasien) {
                $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $tanggalLahir);
                if (
                    $namaLengkap === ''
                    || mb_strlen($namaLengkap) > 100
                    || !$birth
                    || $birth->format('Y-m-d') !== $tanggalLahir
                    || $birth > new DateTimeImmutable('today')
                    || !in_array($jenisKelamin, ['Laki-laki', 'Perempuan'], true)
                    || $tempatLahir === ''
                    || !preg_match('/^\+?[0-9\s()-]{8,20}$/', $noTelepon)
                ) {
                    Helper::flash('error', 'Data pasien baru tidak lengkap atau tidak valid.', 'error');
                    Helper::setOldInput(array_intersect_key($_POST, array_flip(['nama_lengkap', 'nik', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'no_telepon', 'alamat', 'poli_id', 'dokter_id', 'keluhan'])));
                    $this->redirect('pendaftaran/create');
                }
                $newPatient = true;
            }
        }

        $poli = $poliId > 0 ? $this->poliModel->findById($poliId) : null;
        if (!$poli || (int) ($poli->is_active ?? 0) !== 1) {
            Helper::flash('error', 'Poli tujuan tidak valid.', 'error');
            $this->redirect('pendaftaran/create');
        }

        $dayNames = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat',
            'Saturday' => 'Sabtu',
        ];
        $targetDayName = $dayNames[date('l', strtotime($tanggalKunjungan))] ?? 'Senin';
        $schedules = $this->jadwalModel->getByPoliAndHari($poliId, $targetDayName);
        $schedule = null;
        if ($dokterId === 0 && $schedules !== []) {
            $schedule = $schedules[0];
            $dokterId = (int) $schedule->dokter_id;
        }
        foreach ($schedules as $candidate) {
            if ($dokterId > 0 && (int) $candidate->dokter_id === $dokterId) {
                $schedule = $candidate;
                break;
            }
        }
        if (!$schedule) {
            Helper::flash('error', 'Dokter tidak memiliki jadwal aktif pada hari ini.', 'error');
            $this->redirect('pendaftaran/create');
        }

        $patientId = $pasien ? (int) $pasien->id : null;
        if ($patientId !== null && $this->pendaftaranModel->checkExisting($patientId, $poliId, $tanggalKunjungan)) {
            Helper::flash('warning', 'Pasien sudah terdaftar di poli ini untuk hari ini.', 'warning');
            $this->redirect($role === 'pasien' ? 'antrean/saya' : 'antrean');
        }
        if (!$this->jadwalModel->getSisaKuota((int) $schedule->id, $tanggalKunjungan)['is_tersedia']) {
            Helper::flash('error', 'Kuota pendaftaran untuk jadwal ini sudah penuh.', 'error');
            $this->redirect('pendaftaran/create');
        }

        $poli = $this->poliModel->findById($poliId);
        [$pendaftaranId, $antrean] = Database::getInstance()->transaction(function () use (
            $pasien, $newPatient, $userId, $nik, $namaLengkap, $jenisKelamin,
            $tempatLahir, $tanggalLahir, $alamat, $noTelepon, $poliId, $dokterId,
            $schedule, $tanggalKunjungan, $keluhan, $poli
        ): array {
            $patientId = $pasien ? (int) $pasien->id : null;
            if ($newPatient) {
                $patientId = (int) $this->pasienModel->create([
                    'user_id' => null,
                    'no_rekam_medis' => $this->pasienModel->generateUniqueNoRM(),
                    'nik' => $nik,
                    'nama_lengkap' => $namaLengkap,
                    'jenis_kelamin' => $jenisKelamin,
                    'tempat_lahir' => $tempatLahir,
                    'tanggal_lahir' => $tanggalLahir,
                    'alamat' => $alamat !== '' ? $alamat : '-',
                    'no_telepon' => $noTelepon,
                ]);
            }
            if (!$patientId) {
                throw new RuntimeException('Data pasien tidak dapat disimpan.');
            }

            $registrationId = $this->pendaftaranModel->create([
                'pasien_id' => $patientId,
                'poli_id' => $poliId,
                'dokter_id' => $dokterId,
                'jadwal_id' => (int) $schedule->id,
                'tanggal_kunjungan' => $tanggalKunjungan,
                'keluhan' => $keluhan,
                'status' => 'confirmed',
                'registered_by' => $userId,
            ]);
            if ($registrationId === false) {
                throw new RuntimeException('Pendaftaran tidak dapat disimpan.');
            }

            $queue = $this->antreanModel->issueQueue(
                (int) $registrationId,
                $patientId,
                $poliId,
                $dokterId,
                (string) $poli->kode_poli,
                $tanggalKunjungan
            );
            $queueId = (int) (is_object($queue) ? ($queue->id ?? 0) : ($queue['id'] ?? 0));
            $queueNo = (string) (is_object($queue) ? ($queue->no_antrean ?? '') : ($queue['no_antrean'] ?? ''));
            AuditLog::log('Pendaftaran Kunjungan & Antrean', 'antrean', $queueId, null, [
                'no_antrean' => $queueNo,
                'pasien_id' => $patientId,
                'poli_id' => $poliId,
            ]);
            return [(int) $registrationId, $queue];
        });

        $noAntrean = (string) (is_object($antrean) ? ($antrean->no_antrean ?? '') : ($antrean['no_antrean'] ?? ''));
        $patientUserId = $role === 'pasien' ? $userId : (int) ($pasien->user_id ?? 0);
        if ($patientUserId > 0) {
            NotifikasiModel::createForUser(
                $patientUserId,
                'Nomor Antrean Diterbitkan',
                "Nomor antrean Anda adalah {$noAntrean} untuk poli {$poli->nama_poli}.",
                'success'
            );
        }

        Helper::clearOldInput();
        Helper::flash('success', "Pendaftaran Berhasil! Nomor Antrean Anda: {$noAntrean}", 'success');
        $this->redirect('pendaftaran/tiket/' . $pendaftaranId);
    }

    /**
     * Tampilkan Tiket / Bukti Pendaftaran
     */
    public function tiket(string $id): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien']);
        $pendaftaran = $this->pendaftaranModel->findWithDetails((int)$id);
        if (
            !$pendaftaran
            || (($_SESSION['user_role'] ?? '') === 'pasien'
                && (int) ($pendaftaran->patient_user_id ?? 0) !== (int) $_SESSION['user_id'])
        ) {
            Helper::flash('error', 'Data pendaftaran tidak ditemukan.', 'error');
            $this->redirect('dashboard');
        }

        $this->view('pendaftaran/tiket', [
            'title' => 'Bukti Pendaftaran & Tiket Antrean',
            'layout' => 'main',
            'data' => $pendaftaran,
        ]);
    }
}
