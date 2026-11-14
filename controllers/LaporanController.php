<?php
/**
 * ANTRIAN SEHAT - Laporan Controller
 * 
 * Mengelola rekapitulasi laporan kunjungan pasien, statistik antrean harian/mingguan/bulanan,
 * dan cetak / ekspor laporan.
 */

class LaporanController extends Controller
{
    private AntreanModel $antreanModel;
    private PoliModel $poliModel;

    public function __construct()
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran', 'petugas_pelayanan']);
        $this->antreanModel = new AntreanModel();
        $this->poliModel = new PoliModel();
    }

    public function index(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $poliId = (int)($_GET['poli_id'] ?? 0);
        $status = $_GET['status'] ?? '';

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'poli_id'    => $poliId > 0 ? $poliId : null,
            'status'     => $status !== '' ? $status : null,
        ];

        // Query rekapitulasi data
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT a.*, pas.nama_lengkap as nama_pasien, pas.no_rekam_medis, pas.nik,
                       pol.nama_poli, d.nama_dokter
                FROM antrean a
                JOIN pasien pas ON a.pasien_id = pas.id
                JOIN poli pol ON a.poli_id = pol.id
                JOIN dokter d ON a.dokter_id = d.id
                WHERE a.tanggal_antrean BETWEEN :start AND :end";

        $params = [':start' => $startDate, ':end' => $endDate];
        if ($filters['poli_id']) {
            $sql .= " AND a.poli_id = :polid";
            $params[':polid'] = $filters['poli_id'];
        }
        if ($filters['status']) {
            $sql .= " AND a.status = :st";
            $params[':st'] = $filters['status'];
        }
        $sql .= " ORDER BY a.tanggal_antrean DESC, a.no_urut ASC";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $poliList = $this->poliModel->getActive();

        // Hitung rekap statistik
        $totalKunjungan = count($rows);
        $totalSelesai = 0;
        $totalBatal = 0;
        foreach ($rows as $r) {
            $st = is_object($r) ? $r->status : $r['status'];
            if ($st === 'selesai') $totalSelesai++;
            if ($st === 'dibatalkan') $totalBatal++;
        }

        $this->view('laporan/index', [
            'title' => 'Laporan Kunjungan & Antrean',
            'layout' => 'main',
            'rows' => $rows,
            'filters' => $filters,
            'poliList' => $poliList,
            'totalKunjungan' => $totalKunjungan,
            'totalSelesai' => $totalSelesai,
            'totalBatal' => $totalBatal,
        ]);
    }

    public function cetak(): void
    {
        $startDate = $_GET['start_date'] ?? date('Y-m-01');
        $endDate = $_GET['end_date'] ?? date('Y-m-d');
        $poliId = (int)($_GET['poli_id'] ?? 0);

        $db = Database::getInstance()->getConnection();
        $sql = "SELECT a.*, pas.nama_lengkap as nama_pasien, pas.no_rekam_medis,
                       pol.nama_poli, d.nama_dokter
                FROM antrean a
                JOIN pasien pas ON a.pasien_id = pas.id
                JOIN poli pol ON a.poli_id = pol.id
                JOIN dokter d ON a.dokter_id = d.id
                WHERE a.tanggal_antrean BETWEEN :start AND :end";

        $params = [':start' => $startDate, ':end' => $endDate];
        if ($poliId > 0) {
            $sql .= " AND a.poli_id = :polid";
            $params[':polid'] = $poliId;
        }
        $sql .= " ORDER BY a.tanggal_antrean ASC, a.no_urut ASC";

        $stmt = $db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $this->view('laporan/cetak', [
            'title' => 'Cetak Laporan Kunjungan',
            'layout' => null, // Standalone print template
            'rows' => $rows,
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}

