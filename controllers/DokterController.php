<?php
/**
 * ANTRIAN SEHAT - Dokter Controller
 * 
 * Mengelola data dokter, spesialisasi, dan jadwal praktik.
 */

class DokterController extends Controller
{
    private DokterModel $dokterModel;
    private PoliModel $poliModel;
    private JadwalModel $jadwalModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->dokterModel = new DokterModel();
        $this->poliModel = new PoliModel();
        $this->jadwalModel = new JadwalModel();
    }

    public function index(): void
    {
        $list = $this->dokterModel->getAllWithPoli();
        $poliList = $this->poliModel->getActive();

        $this->view('dokter/index', [
            'title' => 'Data Dokter & Jadwal',
            'layout' => 'main',
            'list' => $list,
            'poliList' => $poliList,
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::requireRole(['admin']);

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            $nama = trim($_POST['nama_dokter'] ?? '');
            $kode = strtoupper(trim($_POST['kode_dokter'] ?? ''));
            $poliId = (int)($_POST['poli_id'] ?? 0);
            $spesialisasi = trim($_POST['spesialisasi'] ?? '');
            $telp = trim($_POST['no_telepon'] ?? '');

            if (empty($nama) || empty($poliId)) {
                Helper::flash('error', 'Nama dokter dan poli wajib diisi.', 'error');
                $this->redirect('dokter/create');
            }

            if (empty($kode)) {
                $kode = 'DKT' . mt_rand(100, 999);
            }

            $id = $this->dokterModel->create([
                'poli_id' => $poliId,
                'kode_dokter' => $kode,
                'nama_dokter' => $nama,
                'spesialisasi' => $spesialisasi,
                'no_telepon' => $telp,
                'is_active' => 1,
            ]);

            AuditLog::log('Tambah Data Dokter', 'dokter', $id, null, ['nama' => $nama]);
            Helper::flash('success', "Dokter {$nama} berhasil ditambahkan.", 'success');
            $this->redirect('dokter');
        }

        $poliList = $this->poliModel->getActive();
        $this->view('dokter/create', [
            'title' => 'Tambah Dokter Baru',
            'layout' => 'main',
            'poliList' => $poliList,
        ]);
    }

    public function edit(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        $did = (int)$id;
        $dokter = $this->dokterModel->findById($did);
        if (!$dokter) {
            Helper::flash('error', 'Data dokter tidak ditemukan.', 'error');
            $this->redirect('dokter');
        }

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            $nama = trim($_POST['nama_dokter'] ?? '');
            $kode = strtoupper(trim($_POST['kode_dokter'] ?? ''));
            $poliId = (int)($_POST['poli_id'] ?? 0);
            $spesialisasi = trim($_POST['spesialisasi'] ?? '');
            $telp = trim($_POST['no_telepon'] ?? '');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($nama) || empty($poliId)) {
                Helper::flash('error', 'Nama dokter dan poli wajib diisi.', 'error');
                $this->redirect('dokter/edit/' . $did);
            }

            $this->dokterModel->update($did, [
                'poli_id' => $poliId,
                'kode_dokter' => $kode,
                'nama_dokter' => $nama,
                'spesialisasi' => $spesialisasi,
                'no_telepon' => $telp,
                'is_active' => $isActive,
            ]);

            AuditLog::log('Edit Data Dokter', 'dokter', $did, null, ['nama' => $nama]);
            Helper::flash('success', "Data dokter {$nama} berhasil diperbarui.", 'success');
            $this->redirect('dokter');
        }

        $poliList = $this->poliModel->getActive();
        $this->view('dokter/edit', [
            'title' => 'Edit Data Dokter',
            'layout' => 'main',
            'dokter' => $dokter,
            'poliList' => $poliList,
        ]);
    }

    public function delete(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        CsrfMiddleware::validateRequest();
        $did = (int)$id;

        $dokter = $this->dokterModel->findById($did);
        if (!$dokter) {
            Helper::flash('error', 'Data dokter tidak ditemukan.', 'error');
            $this->redirect('dokter');
        }

        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM pendaftaran WHERE dokter_id = :did");
        $stmt->bindValue(':did', $did, PDO::PARAM_INT);
        $stmt->execute();
        $usedInPendaftaran = (int)$stmt->fetchColumn() > 0;

        if ($usedInPendaftaran) {
            $this->dokterModel->update($did, ['is_active' => 0]);
            AuditLog::log('Nonaktifkan Dokter', 'dokter', $did);
            Helper::flash('info', 'Dokter telah memiliki data kunjungan pendaftaran, sehingga statusnya dinonaktifkan (arsip aman).', 'info');
        } else {
            $this->dokterModel->delete($did);
            AuditLog::log('Hapus Dokter', 'dokter', $did);
            Helper::flash('success', 'Data dokter berhasil dihapus.', 'success');
        }

        $this->redirect('dokter');
    }

    public function jadwal(string $id): void
    {
        $did = (int)$id;
        $dokter = $this->dokterModel->findWithPoli($did);
        if (!$dokter) {
            Helper::flash('error', 'Data dokter tidak ditemukan.', 'error');
            $this->redirect('dokter');
        }

        $jadwalList = $this->dokterModel->getJadwal($did);

        $docName = is_object($dokter) ? ($dokter->nama_dokter ?? '') : ($dokter['nama_dokter'] ?? '');
        $this->view('dokter/jadwal', [
            'title' => 'Jadwal Praktik ' . $docName,
            'layout' => 'main',
            'dokter' => $dokter,
            'jadwalList' => $jadwalList,
        ]);
    }

    /**
     * API untuk mengambil daftar dokter berdasarkan Poli (JSON)
     */
    public function apiByPoli(string $poliId): void
    {
        $pid = (int)$poliId;
        $dokters = $this->dokterModel->getByPoliId($pid);
        
        $result = [];
        foreach ($dokters as $d) {
            $jadwal = $this->dokterModel->getJadwal((int)$d->id);
            $result[] = [
                'id' => $d->id,
                'nama_dokter' => $d->nama_dokter,
                'spesialisasi' => $d->spesialisasi,
                'jadwal' => $jadwal,
            ];
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'data' => $result
        ]);
        exit;
    }
}
