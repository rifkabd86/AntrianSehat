<?php
/**
 * ANTRIAN SEHAT - Antrean Controller
 * 
 * Mengelola daftar antrean pasien, loket pemanggilan petugas,
 * aksi antrean (panggil, mulai, selesaikan, lewati, batal),
 * dan layar display publik Puskesmas.
 */

class AntreanController extends Controller
{
    private AntreanModel $antreanModel;
    private PoliModel $poliModel;
    private PasienModel $pasienModel;

    public function __construct()
    {
        $this->antreanModel = new AntreanModel();
        $this->poliModel = new PoliModel();
        $this->pasienModel = new PasienModel();
    }

    /**
     * Tampilan Daftar Antrian Pasien (Sesuai Mockup UI)
     */
    public function index(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien']);

        $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
        $poliId = (int)($_GET['poli_id'] ?? 0);
        $status = trim($_GET['status'] ?? '');
        $tanggal = $_GET['tanggal'] ?? date('Y-m-d');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $filters = [
            'search'  => $search,
            'poli_id' => $poliId > 0 ? $poliId : null,
            'status'  => $status !== '' ? $status : null,
            'tanggal' => $tanggal,
        ];
        if (($_SESSION['user_role'] ?? '') === 'pasien') {
            $filters['user_id'] = (int) $_SESSION['user_id'];
        }

        $list = $this->antreanModel->getFiltered($filters, $limit, $offset);
        $total = $this->antreanModel->countFiltered($filters);
        $totalPages = max(1, ceil($total / $limit));
        $poliList = $this->poliModel->getActive();

        $this->view('antrean/index', [
            'title' => 'Daftar Antrian Pasien',
            'layout' => 'main',
            'list' => $list,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
            'poliList' => $poliList,
            'filters' => $filters,
        ]);
    }

    /**
     * Detail Antrean Pasien
     */
    public function detail(string $id): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien']);

        $data = $this->antreanModel->findWithDetails((int)$id);
        if (
            !$data
            || (($_SESSION['user_role'] ?? '') === 'pasien'
                && (int) ($data->patient_user_id ?? 0) !== (int) $_SESSION['user_id'])
        ) {
            http_response_code(404);
            Helper::flash('error', 'Data antrean tidak ditemukan.', 'error');
            $this->redirect('antrean');
        }

        $this->view('antrean/detail', [
            'title' => 'Detail Antrean Pasien',
            'layout' => 'main',
            'data' => $data,
        ]);
    }

    public function myQueues(): void
    {
        AuthMiddleware::requireRole('pasien');
        $this->index();
    }

    public function myHistory(): void
    {
        AuthMiddleware::requireRole('pasien');
        $patient = $this->pasienModel->findByUserId((int) $_SESSION['user_id']);
        $history = $patient ? $this->pasienModel->getRiwayatKunjungan((int) $patient->id) : [];
        $this->view('pasien/riwayat', [
            'title' => 'Riwayat Kunjungan',
            'layout' => 'main',
            'history' => $history,
        ]);
    }

    /**
     * Loket Pemanggilan untuk Petugas Pelayanan
     */
    public function loket(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pelayanan']);

        $poliId = (int)($_GET['poli_id'] ?? 1);
        $poliList = $this->poliModel->getActive();
        $activeList = $this->antreanModel->getActiveList(date('Y-m-d'), $poliId, 15);
        $stats = $this->antreanModel->getTodaySummary();

        // Cari yang sedang dipanggil / dilayani di poli ini
        $currentlyServing = null;
        foreach ($activeList as $item) {
            $st = is_object($item) ? $item->status : $item['status'];
            if (in_array($st, ['dipanggil', 'dilayani'])) {
                $currentlyServing = $item;
                break;
            }
        }

        $this->view('antrean/loket', [
            'title' => 'Loket Pemanggilan Pasien',
            'layout' => 'main',
            'poliId' => $poliId,
            'poliList' => $poliList,
            'activeList' => $activeList,
            'currentlyServing' => $currentlyServing,
            'stats' => $stats,
        ]);
    }

    /**
     * Aksi Panggil Antrean Berikutnya (AJAX / Form)
     */
    public function panggilBerikutnya(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pelayanan']);
        CsrfMiddleware::validateRequest();

        $poliId = (int)($_POST['poli_id'] ?? 1);
        $userId = $_SESSION['user_id'];

        $result = $this->antreanModel->callNext($poliId, $userId);

        if ($result) {
            AuditLog::log('Panggil Antrean', 'antrean', $result->id, null, [
                'no_antrean' => $result->no_antrean,
                'poli_id' => $poliId,
            ]);

            if ($this->isAjax()) {
                $this->json(['success' => true, 'data' => $result]);
            }
            Helper::flash('success', "Memanggil nomor antrean {$result->no_antrean} ({$result->nama_pasien})", 'success');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Tidak ada antrean yang menunggu di poli ini.']);
            }
            Helper::flash('info', 'Tidak ada antrean yang menunggu di poli ini.', 'info');
        }

        $this->redirect('antrean/loket?poli_id=' . $poliId);
    }

    /**
     * Aksi Ubah Status Antrean (Mulai Layani, Selesai, Lewati, Batalkan)
     */
    public function updateStatus(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pelayanan']);
        CsrfMiddleware::validateRequest();

        $id = (int)($_POST['id'] ?? 0);
        $status = trim($_POST['status'] ?? '');
        $alasan = trim($_POST['alasan'] ?? '');
        $poliId = (int)($_POST['poli_id'] ?? 1);
        $userId = $_SESSION['user_id'];

        $success = $this->antreanModel->updateStatus($id, $status, $alasan, $userId);

        if ($success) {
            AuditLog::log("Update Status Antrean: {$status}", 'antrean', $id, null, ['status' => $status]);

            // Jika selesai atau dibatalkan, buat notifikasi ke pasien
            $antrean = $this->antreanModel->findWithDetails($id);
            if ($antrean) {
                $msg = match($status) {
                    'dilayani' => "Nomor antrean {$antrean->no_antrean} sedang dilayani oleh dokter.",
                    'selesai' => "Pelayanan untuk nomor antrean {$antrean->no_antrean} telah selesai.",
                    'dilewati' => "Nomor antrean {$antrean->no_antrean} dilewati karena tidak hadir saat dipanggil.",
                    'dibatalkan' => "Nomor antrean {$antrean->no_antrean} dibatalkan.",
                    default => "Status antrean diperbarui."
                };
                // Ambil user_id dari pasien jika ada
                $pasien = $this->pasienModel->findById($antrean->pasien_id);
                if ($pasien && !empty($pasien->user_id)) {
                    NotifikasiModel::createForUser((int) $pasien->user_id, 'Status Antrean', $msg, 'info');
                }
            }

            if ($this->isAjax()) {
                $this->json(['success' => true]);
            }
            Helper::flash('success', "Status antrean berhasil diubah menjadi: {$status}", 'success');
        } else {
            if ($this->isAjax()) {
                $this->json(['success' => false, 'message' => 'Gagal mengubah status antrean.'], 400);
            }
            Helper::flash('error', 'Gagal memperbarui status antrean.', 'error');
        }

        $this->redirect('antrean/loket?poli_id=' . $poliId);
    }

    /**
     * Layar Monitor Display Antrean Publik
     */
    public function display(): void
    {
        $this->view('display/index', [
            'title' => 'Layar Monitor Antrean Puskesmas',
            'layout' => null, // Standalone fullscreen monitor view
        ]);
    }

    /**
     * Endpoint API JSON untuk pembaruan display real-time (Fetch API)
     */
    public function displayData(): void
    {
        $data = $this->antreanModel->getDisplayData();
        $this->json($data);
    }
}
