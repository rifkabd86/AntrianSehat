<?php
/**
 * ANTRIAN SEHAT - Antrean Model
 * 
 * Model inti untuk manajemen antrean Puskesmas.
 * Menangani penerbitan nomor antrean, pemanggilan, status, dan data monitor display.
 */

class AntreanModel extends Model
{
    protected string $table = 'antrean';

    /**
     * Hitung ringkasan statistik antrean hari ini
     */
    public function getTodaySummary(?string $tanggal = null): array
    {
        $tanggal ??= date('Y-m-d');

        $sql = "SELECT 
                    COUNT(*) as total_hari_ini,
                    COUNT(CASE WHEN status = 'menunggu' THEN 1 END) as dalam_antrean,
                    COUNT(CASE WHEN status = 'dipanggil' THEN 1 END) as dipanggil,
                    COUNT(CASE WHEN status = 'dilayani' THEN 1 END) as sedang_dilayani,
                    COUNT(CASE WHEN status = 'selesai' THEN 1 END) as selesai,
                    COUNT(CASE WHEN status = 'dilewati' THEN 1 END) as dilewati,
                    COUNT(CASE WHEN status = 'dibatalkan' THEN 1 END) as dibatalkan
                FROM {$this->table}
                WHERE tanggal_antrean = :tgl";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->execute();
        $res = $stmt->fetch();

        return [
            'total_hari_ini'   => (int) (is_object($res) ? ($res->total_hari_ini ?? 0) : ($res['total_hari_ini'] ?? 0)),
            'dalam_antrean'    => (int) (is_object($res) ? ($res->dalam_antrean ?? 0) : ($res['dalam_antrean'] ?? 0)),
            'dipanggil'        => (int) (is_object($res) ? ($res->dipanggil ?? 0) : ($res['dipanggil'] ?? 0)),
            'sedang_dilayani'  => (int) (is_object($res) ? ($res->sedang_dilayani ?? 0) : ($res['sedang_dilayani'] ?? 0)),
            'selesai'          => (int) (is_object($res) ? ($res->selesai ?? 0) : ($res['selesai'] ?? 0)),
            'dilewati'         => (int) (is_object($res) ? ($res->dilewati ?? 0) : ($res['dilewati'] ?? 0)),
            'dibatalkan'       => (int) (is_object($res) ? ($res->dibatalkan ?? 0) : ($res['dibatalkan'] ?? 0)),
        ];
    }

    /**
     * Dapatkan nomor urut dan kode antrean berikutnya untuk poli dan tanggal tertentu
     */
    public function getNextNomorUrut(int $poliId, string $tanggal): int
    {
        $sql = "SELECT MAX(no_urut) as max_urut 
                FROM {$this->table} 
                WHERE poli_id = :polid AND tanggal_antrean = :tgl FOR UPDATE";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':polid', $poliId, PDO::PARAM_INT);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->execute();
        $res = $stmt->fetch();
        $max = 0;
        if ($res) {
            $max = (int) (is_object($res) ? ($res->max_urut ?? 0) : ($res['max_urut'] ?? 0));
        }
        return $max + 1;
    }

    /**
     * Terbitkan nomor antrean baru
     */
    public function issueQueue(int $pendaftaranId, int $pasienId, int $poliId, int $dokterId, string $kodePoli, string $tanggal): array
    {
        $noUrut = $this->getNextNomorUrut($poliId, $tanggal);
        $noAntrean = Helper::generateNoAntrean($kodePoli, $noUrut);

        $sql = "INSERT INTO {$this->table} 
                (pendaftaran_id, pasien_id, poli_id, dokter_id, no_antrean, no_urut, tanggal_antrean, status, created_at, updated_at)
                VALUES 
                (:pend_id, :pas_id, :pol_id, :dok_id, :no_ant, :no_ur, :tgl, 'menunggu', NOW(), NOW())";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pend_id', $pendaftaranId, PDO::PARAM_INT);
        $stmt->bindValue(':pas_id', $pasienId, PDO::PARAM_INT);
        $stmt->bindValue(':pol_id', $poliId, PDO::PARAM_INT);
        $stmt->bindValue(':dok_id', $dokterId, PDO::PARAM_INT);
        $stmt->bindValue(':no_ant', $noAntrean);
        $stmt->bindValue(':no_ur', $noUrut, PDO::PARAM_INT);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->execute();

        return [
            'id' => (int)$this->db->lastInsertId(),
            'no_antrean' => $noAntrean,
            'no_urut' => $noUrut,
        ];
    }

    /**
     * Ambil antrean saat ini (untuk tabel dashboard dan antrean)
     */
    public function getActiveList(?string $tanggal = null, ?int $poliId = null, int $limit = 20): array
    {
        $tanggal ??= date('Y-m-d');

        $sql = "SELECT a.*, 
                       pas.nama_lengkap as nama_pasien, pas.no_rekam_medis, pas.nik, pas.no_telepon,
                       pas.user_id as patient_user_id,
                       pol.nama_poli, pol.kode_poli, pol.warna as warna_poli,
                       d.nama_dokter
                FROM {$this->table} a
                JOIN pasien pas ON a.pasien_id = pas.id
                JOIN poli pol ON a.poli_id = pol.id
                JOIN dokter d ON a.dokter_id = d.id
                WHERE a.tanggal_antrean = :tgl";

        $params = [':tgl' => $tanggal];
        if ($poliId !== null && $poliId > 0) {
            $sql .= " AND a.poli_id = :polid";
            $params[':polid'] = $poliId;
        }

        $sql .= " ORDER BY FIELD(a.status, 'dipanggil', 'dilayani', 'menunggu', 'dilewati', 'selesai', 'dibatalkan'), a.no_urut ASC LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil antrean dengan filter pencarian & pagination lengkap
     */
    public function getFiltered(array $filters = [], int $limit = 15, int $offset = 0): array
    {
        $sql = "SELECT a.*, 
                       pas.nama_lengkap as nama_pasien, pas.no_rekam_medis, pas.nik, pas.no_telepon,
                       pas.user_id as patient_user_id,
                       pol.nama_poli, pol.kode_poli,
                       d.nama_dokter
                FROM {$this->table} a
                JOIN pasien pas ON a.pasien_id = pas.id
                JOIN poli pol ON a.poli_id = pol.id
                JOIN dokter d ON a.dokter_id = d.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['tanggal'])) {
            $sql .= " AND a.tanggal_antrean = :tgl";
            $params[':tgl'] = $filters['tanggal'];
        }
        if (!empty($filters['poli_id'])) {
            $sql .= " AND a.poli_id = :polid";
            $params[':polid'] = $filters['poli_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :st";
            $params[':st'] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= ' AND pas.user_id = :user_id';
            $params[':user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pas.nama_lengkap LIKE :name_search OR a.no_antrean LIKE :queue_search OR pas.no_rekam_medis LIKE :record_search)";
            $keyword = '%' . $filters['search'] . '%';
            $params[':name_search'] = $keyword;
            $params[':queue_search'] = $keyword;
            $params[':record_search'] = $keyword;
        }

        $sql .= " ORDER BY a.tanggal_antrean DESC, a.no_urut ASC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total untuk filter antrean
     */
    public function countFiltered(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total 
                FROM {$this->table} a
                JOIN pasien pas ON a.pasien_id = pas.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['tanggal'])) {
            $sql .= " AND a.tanggal_antrean = :tgl";
            $params[':tgl'] = $filters['tanggal'];
        }
        if (!empty($filters['poli_id'])) {
            $sql .= " AND a.poli_id = :polid";
            $params[':polid'] = $filters['poli_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :st";
            $params[':st'] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= ' AND pas.user_id = :user_id';
            $params[':user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pas.nama_lengkap LIKE :name_search OR a.no_antrean LIKE :queue_search OR pas.no_rekam_medis LIKE :record_search)";
            $keyword = '%' . $filters['search'] . '%';
            $params[':name_search'] = $keyword;
            $params[':queue_search'] = $keyword;
            $params[':record_search'] = $keyword;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $res = $stmt->fetch();
        return (int) (is_object($res) ? ($res->total ?? 0) : ($res['total'] ?? 0));
    }

    /**
     * Panggil antrean berikutnya untuk poli tertentu dengan database transaction & locking
     */
    public function callNext(int $poliId, int $userId): ?object
    {
        $tanggal = date('Y-m-d');
        
        return Database::getInstance()->transaction(function() use ($poliId, $userId, $tanggal) {
            // Cari antrean menunggu nomor urut terendah dengan FOR UPDATE lock
            $sql = "SELECT id FROM {$this->table} 
                    WHERE poli_id = :polid AND tanggal_antrean = :tgl AND status = 'menunggu'
                    ORDER BY no_urut ASC LIMIT 1 FOR UPDATE";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':polid', $poliId, PDO::PARAM_INT);
            $stmt->bindValue(':tgl', $tanggal);
            $stmt->execute();
            $target = $stmt->fetch();

            if (!$target) {
                return null;
            }

            $targetId = (int)(is_object($target) ? $target->id : $target['id']);

            // Update status menjadi 'dipanggil'
            $updateSql = "UPDATE {$this->table} 
                          SET status = 'dipanggil', waktu_dipanggil = NOW(), called_by = :uid, updated_at = NOW() 
                          WHERE id = :id";
            $uStmt = $this->db->prepare($updateSql);
            $uStmt->bindValue(':uid', $userId, PDO::PARAM_INT);
            $uStmt->bindValue(':id', $targetId, PDO::PARAM_INT);
            $uStmt->execute();

            return (object)$this->findWithDetails($targetId);
        });
    }

    /**
     * Update status antrean dengan transaksi aman
     */
    public function updateStatus(int $antreanId, string $status, ?string $alasan = null, ?int $userId = null): bool
    {
        $validStatuses = ['menunggu', 'dipanggil', 'dilayani', 'selesai', 'dilewati', 'dibatalkan'];
        if (!in_array($status, $validStatuses, true)) {
            return false;
        }

        $sql = "UPDATE {$this->table} SET status = :status, updated_at = NOW()";

        if ($status === 'dipanggil') {
            $sql .= ", waktu_dipanggil = NOW()";
            if ($userId) {
                $sql .= ", called_by = :uid";
            }
        } elseif ($status === 'dilayani') {
            $sql .= ", waktu_mulai_layanan = NOW()";
        } elseif ($status === 'selesai') {
            $sql .= ", waktu_selesai = NOW()";
        } elseif ($status === 'dibatalkan') {
            $sql .= ", alasan_batal = :alasan";
        }

        $sql .= " WHERE id = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':id', $antreanId, PDO::PARAM_INT);
        if ($status === 'dipanggil' && $userId) {
            $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        }
        if ($status === 'dibatalkan') {
            $stmt->bindValue(':alasan', $alasan ?? 'Dibatalkan oleh petugas');
        }

        return $stmt->execute();
    }

    /**
     * Ambil data lengkap satu antrean
     */
    public function findWithDetails(int $id)
    {
        $sql = "SELECT a.*, 
                       pas.nama_lengkap as nama_pasien, pas.no_rekam_medis, pas.nik, pas.jenis_kelamin, pas.no_telepon, pas.tanggal_lahir, pas.user_id as patient_user_id,
                       pol.nama_poli, pol.kode_poli,
                       d.nama_dokter, d.spesialisasi
                FROM {$this->table} a
                JOIN pasien pas ON a.pasien_id = pas.id
                JOIN poli pol ON a.poli_id = pol.id
                JOIN dokter d ON a.dokter_id = d.id
                WHERE a.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Ambil data untuk layar display TV Puskesmas secara real-time
     */
    public function getDisplayData(): array
    {
        $tanggal = date('Y-m-d');

        // Antrean yang sedang dipanggil atau dilayani
        $sqlActive = "SELECT a.no_antrean, a.status, a.waktu_dipanggil,
                             pol.nama_poli, pol.kode_poli, d.nama_dokter
                      FROM {$this->table} a
                      JOIN poli pol ON a.poli_id = pol.id
                      JOIN dokter d ON a.dokter_id = d.id
                      WHERE a.tanggal_antrean = :tgl AND a.status IN ('dipanggil', 'dilayani')
                      ORDER BY a.waktu_dipanggil DESC LIMIT 1";
        $stmt = $this->db->prepare($sqlActive);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->execute();
        $current = $stmt->fetch();

        // Antrean aktif per masing-masing poli
        $sqlPerPoli = "SELECT pol.id as poli_id, pol.nama_poli, pol.kode_poli, pol.warna,
                              (SELECT no_antrean FROM antrean a2 
                               WHERE a2.poli_id = pol.id AND a2.tanggal_antrean = :called_date AND a2.status IN ('dipanggil', 'dilayani') 
                               ORDER BY a2.waktu_dipanggil DESC LIMIT 1) as no_sedang_dipanggil,
                              (SELECT COUNT(*) FROM antrean a3 
                               WHERE a3.poli_id = pol.id AND a3.tanggal_antrean = :waiting_date AND a3.status = 'menunggu') as sisa_antrean
                       FROM poli pol
                       WHERE pol.is_active = 1
                       ORDER BY pol.id ASC";
        $stmtPoli = $this->db->prepare($sqlPerPoli);
        $stmtPoli->bindValue(':called_date', $tanggal);
        $stmtPoli->bindValue(':waiting_date', $tanggal);
        $stmtPoli->execute();
        $poliList = $stmtPoli->fetchAll();

        // Daftar 5 antrean berikutnya
        $sqlNext = "SELECT a.no_antrean, pol.nama_poli
                    FROM {$this->table} a
                    JOIN poli pol ON a.poli_id = pol.id
                    WHERE a.tanggal_antrean = :tgl AND a.status = 'menunggu'
                    ORDER BY a.no_urut ASC LIMIT 5";
        $stmtNext = $this->db->prepare($sqlNext);
        $stmtNext->bindValue(':tgl', $tanggal);
        $stmtNext->execute();
        $nextQueues = $stmtNext->fetchAll();

        return [
            'current' => $current,
            'poli_list' => $poliList,
            'next_queues' => $nextQueues,
            'server_time' => date('H:i:s'),
            'server_date' => Helper::formatTanggal($tanggal),
        ];
    }

    /**
     * Statistik kunjungan 7 hari terakhir untuk Chart.js
     */
    public function getChartStats7Hari(): array
    {
        $dates = [];
        $labels = [];
        $counts = [];

        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dates[] = $d;
            $labels[] = date('d M', strtotime($d));
            $counts[$d] = 0;
        }

        $startDate = $dates[0];
        $endDate = end($dates);

        $sql = "SELECT tanggal_antrean, COUNT(*) as total 
                FROM {$this->table} 
                WHERE tanggal_antrean BETWEEN :start AND :end 
                GROUP BY tanggal_antrean";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':start', $startDate);
        $stmt->bindValue(':end', $endDate);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $tgl = is_object($row) ? $row->tanggal_antrean : $row['tanggal_antrean'];
            $tot = (int)(is_object($row) ? $row->total : $row['total']);
            if (isset($counts[$tgl])) {
                $counts[$tgl] = $tot;
            }
        }

        return [
            'labels' => $labels,
            'data'   => array_values($counts),
        ];
    }

    /**
     * Data grafik kunjungan multi-periode (Harian, Mingguan, Bulanan)
     */
    public function getChartKunjunganPeriods(): array
    {
        // 1. Daily (7 Hari Terakhir)
        $dailyLabels = [];
        $dailyData = [];
        $dayMap = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-{$i} days"));
            $dayAbbr = $dayMap[date('D', strtotime($d))] ?? date('D', strtotime($d));
            $dailyLabels[] = $dayAbbr;
            $dailyData[$d] = 0;
        }
        $dStart = date('Y-m-d', strtotime('-6 days'));
        $dEnd = date('Y-m-d');
        $stmtDaily = $this->db->prepare(
            "SELECT tanggal_antrean, COUNT(*) as total 
             FROM {$this->table} 
             WHERE tanggal_antrean BETWEEN :s AND :e 
             GROUP BY tanggal_antrean"
        );
        $stmtDaily->execute([':s' => $dStart, ':e' => $dEnd]);
        foreach ($stmtDaily->fetchAll() as $r) {
            $tgl = is_object($r) ? $r->tanggal_antrean : $r['tanggal_antrean'];
            $tot = (int)(is_object($r) ? $r->total : $r['total']);
            if (isset($dailyData[$tgl])) {
                $dailyData[$tgl] = $tot;
            }
        }

        // 2. Weekly (4 Minggu Terakhir)
        $weeklyLabels = [];
        $weeklyData = [];
        for ($w = 3; $w >= 0; $w--) {
            $wStart = date('Y-m-d', strtotime("-" . (($w + 1) * 7 - 1) . " days"));
            $wEnd = date('Y-m-d', strtotime("-{$w} weeks"));
            $weeklyLabels[] = 'Mgu ' . date('d/m', strtotime($wStart));

            $stmtW = $this->db->prepare(
                "SELECT COUNT(*) as total FROM {$this->table} WHERE tanggal_antrean BETWEEN :s AND :e"
            );
            $stmtW->execute([':s' => $wStart, ':e' => $wEnd]);
            $weeklyData[] = (int)$stmtW->fetchColumn();
        }

        // 3. Monthly (6 Bulan Terakhir)
        $monthlyLabels = [];
        $monthlyData = [];
        for ($m = 5; $m >= 0; $m--) {
            $mStart = date('Y-m-01', strtotime("-{$m} months"));
            $mEnd = date('Y-m-t', strtotime("-{$m} months"));
            $monthlyLabels[] = date('M Y', strtotime($mStart));

            $stmtM = $this->db->prepare(
                "SELECT COUNT(*) as total FROM {$this->table} WHERE tanggal_antrean BETWEEN :s AND :e"
            );
            $stmtM->execute([':s' => $mStart, ':e' => $mEnd]);
            $monthlyData[] = (int)$stmtM->fetchColumn();
        }

        return [
            'daily' => [
                'labels' => $dailyLabels,
                'data' => array_values($dailyData)
            ],
            'weekly' => [
                'labels' => $weeklyLabels,
                'data' => $weeklyData
            ],
            'monthly' => [
                'labels' => $monthlyLabels,
                'data' => $monthlyData
            ],
        ];
    }

    /**
     * Data grafik distribusi status antrean hari ini
     */
    public function getChartDistribution(?string $tanggal = null): array
    {
        $summary = $this->getTodaySummary($tanggal);
        return [
            'labels' => ['Menunggu', 'Dipanggil', 'Sedang Dilayani', 'Selesai', 'Dibatalkan'],
            'data' => [
                $summary['dalam_antrean'],
                $summary['dipanggil'],
                $summary['sedang_dilayani'],
                $summary['selesai'],
                $summary['dibatalkan'],
            ],
            'colors' => ['#F59E0B', '#3B82F6', '#8B5CF6', '#10B981', '#EF4444'],
        ];
    }

    /**
     * Data grafik kunjungan antrean per Poli
     */
    public function getChartPoliVisits(?string $tanggal = null): array
    {
        $tanggal ??= date('Y-m-d');
        $sql = "SELECT p.nama_poli, p.warna, COUNT(a.id) as total_antrean
                FROM poli p
                LEFT JOIN antrean a ON p.id = a.poli_id AND a.tanggal_antrean = :tgl
                WHERE p.is_active = 1
                GROUP BY p.id, p.nama_poli, p.warna
                ORDER BY p.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        $labels = [];
        $data = [];
        $colors = [];
        foreach ($rows as $r) {
            $labels[] = is_object($r) ? $r->nama_poli : $r['nama_poli'];
            $data[] = (int)(is_object($r) ? $r->total_antrean : $r['total_antrean']);
            $colors[] = !empty(is_object($r) ? $r->warna : $r['warna']) ? (is_object($r) ? $r->warna : $r['warna']) : '#2563EB';
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'colors' => $colors,
        ];
    }
}
