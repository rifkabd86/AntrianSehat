<?php
/**
 * ANTRIAN SEHAT - Jadwal Controller
 * 
 * Mengelola jadwal praktik dokter, penempatan poli, kuota, dan validasi bentrok jadwal.
 */

class JadwalController extends Controller
{
    private JadwalModel $jadwalModel;
    private DokterModel $dokterModel;
    private PoliModel $poliModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->jadwalModel = new JadwalModel();
        $this->dokterModel = new DokterModel();
        $this->poliModel = new PoliModel();
    }

    /**
     * Tampilkan seluruh jadwal dokter
     */
    public function index(): void
    {
        $filters = [
            'poli_id'   => (int)($_GET['poli_id'] ?? 0),
            'dokter_id' => (int)($_GET['dokter_id'] ?? 0),
            'hari'      => trim((string)($_GET['hari'] ?? '')),
        ];

        $list = $this->jadwalModel->getAllWithDetails($filters);
        $poliList = $this->poliModel->getActive();
        $dokterList = $this->dokterModel->getAllWithPoli(true);

        $this->view('jadwal/index', [
            'title'      => 'Jadwal Praktik Dokter',
            'layout'     => 'main',
            'list'       => $list,
            'poliList'   => $poliList,
            'dokterList' => $dokterList,
            'filters'    => $filters,
        ]);
    }

    /**
     * Form Tambah Jadwal Baru
     */
    public function create(): void
    {
        AuthMiddleware::requireRole(['admin']);

        $selectedDokterId = (int)($_GET['dokter_id'] ?? 0);
        $dokterList = $this->dokterModel->getAllWithPoli(true);
        $poliList = $this->poliModel->getActive();

        $this->view('jadwal/form', [
            'title'            => 'Tambah Jadwal Praktik',
            'layout'           => 'main',
            'jadwal'           => null,
            'dokterList'       => $dokterList,
            'poliList'         => $poliList,
            'selectedDokterId' => $selectedDokterId,
        ]);
    }

    /**
     * Simpan Jadwal Baru
     */
    public function store(): void
    {
        AuthMiddleware::requireRole(['admin']);
        CsrfMiddleware::validateRequest();

        $dokterId = (int)($_POST['dokter_id'] ?? 0);
        $poliId = (int)($_POST['poli_id'] ?? 0);
        $hari = trim((string)($_POST['hari'] ?? ''));
        $jamMulai = trim((string)($_POST['jam_mulai'] ?? ''));
        $jamSelesai = trim((string)($_POST['jam_selesai'] ?? ''));
        $kuota = max(1, (int)($_POST['kuota'] ?? 30));

        // Validasi
        $validDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        if (!$dokterId || !$poliId || !in_array($hari, $validDays, true) || empty($jamMulai) || empty($jamSelesai)) {
            Helper::flash('error', 'Semua kolom wajib diisi dengan benar.', 'error');
            Helper::setOldInput($_POST);
            $this->redirect('jadwal/create');
        }

        if (strtotime($jamMulai) >= strtotime($jamSelesai)) {
            Helper::flash('error', 'Jam mulai harus lebih awal dari jam selesai.', 'error');
            Helper::setOldInput($_POST);
            $this->redirect('jadwal/create');
        }

        // Cek bentrok jadwal dokter
        if ($this->jadwalModel->checkConflict($dokterId, $hari, $jamMulai, $jamSelesai)) {
            Helper::flash('error', "Dokter tersebut sudah memiliki jadwal praktik lain pada hari {$hari} yang tumpang tindih.", 'error');
            Helper::setOldInput($_POST);
            $this->redirect('jadwal/create');
        }

        $id = $this->jadwalModel->create([
            'dokter_id'   => $dokterId,
            'poli_id'     => $poliId,
            'hari'        => $hari,
            'jam_mulai'   => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'kuota'       => $kuota,
            'is_active'   => 1,
        ]);

        AuditLog::log('Tambah Jadwal Dokter', 'jadwal_dokter', $id, null, [
            'dokter_id' => $dokterId,
            'poli_id'   => $poliId,
            'hari'      => $hari,
            'jam'       => "{$jamMulai} - {$jamSelesai}",
        ]);

        Helper::flash('success', 'Jadwal dokter berhasil ditambahkan.', 'success');
        $this->redirect('jadwal');
    }

    /**
     * Form Edit Jadwal
     */
    public function edit(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        $jid = (int)$id;
        $jadwal = $this->jadwalModel->findWithDetails($jid);
        if (!$jadwal) {
            Helper::flash('error', 'Data jadwal tidak ditemukan.', 'error');
            $this->redirect('jadwal');
        }

        $dokterList = $this->dokterModel->getAllWithPoli(true);
        $poliList = $this->poliModel->getActive();

        $this->view('jadwal/form', [
            'title'            => 'Edit Jadwal Praktik',
            'layout'           => 'main',
            'jadwal'           => $jadwal,
            'dokterList'       => $dokterList,
            'poliList'         => $poliList,
            'selectedDokterId' => (int)$jadwal->dokter_id,
        ]);
    }

    /**
     * Update Jadwal
     */
    public function update(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        CsrfMiddleware::validateRequest();
        $jid = (int)$id;

        $jadwal = $this->jadwalModel->findById($jid);
        if (!$jadwal) {
            Helper::flash('error', 'Data jadwal tidak ditemukan.', 'error');
            $this->redirect('jadwal');
        }

        $dokterId = (int)($_POST['dokter_id'] ?? 0);
        $poliId = (int)($_POST['poli_id'] ?? 0);
        $hari = trim((string)($_POST['hari'] ?? ''));
        $jamMulai = trim((string)($_POST['jam_mulai'] ?? ''));
        $jamSelesai = trim((string)($_POST['jam_selesai'] ?? ''));
        $kuota = max(1, (int)($_POST['kuota'] ?? 30));
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $validDays = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        if (!$dokterId || !$poliId || !in_array($hari, $validDays, true) || empty($jamMulai) || empty($jamSelesai)) {
            Helper::flash('error', 'Semua kolom wajib diisi dengan benar.', 'error');
            $this->redirect('jadwal/edit/' . $jid);
        }

        if (strtotime($jamMulai) >= strtotime($jamSelesai)) {
            Helper::flash('error', 'Jam mulai harus lebih awal dari jam selesai.', 'error');
            $this->redirect('jadwal/edit/' . $jid);
        }

        // Cek bentrok (kecuali jadwal saat ini)
        if ($this->jadwalModel->checkConflict($dokterId, $hari, $jamMulai, $jamSelesai, $jid)) {
            Helper::flash('error', "Dokter tersebut sudah memiliki jadwal lain pada hari {$hari} yang tumpang tindih.", 'error');
            $this->redirect('jadwal/edit/' . $jid);
        }

        $this->jadwalModel->update($jid, [
            'dokter_id'   => $dokterId,
            'poli_id'     => $poliId,
            'hari'        => $hari,
            'jam_mulai'   => $jamMulai,
            'jam_selesai' => $jamSelesai,
            'kuota'       => $kuota,
            'is_active'   => $isActive,
        ]);

        AuditLog::log('Edit Jadwal Dokter', 'jadwal_dokter', $jid, null, [
            'dokter_id' => $dokterId,
            'hari'      => $hari,
            'jam'       => "{$jamMulai} - {$jamSelesai}",
        ]);

        Helper::flash('success', 'Jadwal dokter berhasil diperbarui.', 'success');
        $this->redirect('jadwal');
    }

    /**
     * Hapus / Nonaktifkan Jadwal
     */
    public function delete(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        CsrfMiddleware::validateRequest();
        $jid = (int)$id;

        $jadwal = $this->jadwalModel->findById($jid);
        if (!$jadwal) {
            Helper::flash('error', 'Data jadwal tidak ditemukan.', 'error');
            $this->redirect('jadwal');
        }

        // Cek apakah jadwal sudah pernah dipakai pendaftaran
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE jadwal_id = :jid");
        $stmt->bindValue(':jid', $jid, PDO::PARAM_INT);
        $stmt->execute();
        $hasUsage = (int)$stmt->fetchColumn() > 0;

        if ($hasUsage) {
            // Nonaktifkan saja untuk menjaga integritas data relasional
            $this->jadwalModel->update($jid, ['is_active' => 0]);
            AuditLog::log('Nonaktifkan Jadwal Dokter', 'jadwal_dokter', $jid);
            Helper::flash('info', 'Jadwal telah digunakan pada transaksi pendaftaran, sehingga statusnya dinonaktifkan (arsip aman).', 'info');
        } else {
            $this->jadwalModel->delete($jid);
            AuditLog::log('Hapus Jadwal Dokter', 'jadwal_dokter', $jid);
            Helper::flash('success', 'Jadwal dokter berhasil dihapus.', 'success');
        }

        $this->redirect('jadwal');
    }
}

