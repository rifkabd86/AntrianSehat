<?php
/**
 * ANTRIAN SEHAT - Pendaftaran Model
 * 
 * Model untuk mengelola pendaftaran kunjungan pasien.
 */

class PendaftaranModel extends Model
{
    protected string $table = 'pendaftaran';

    /**
     * Cek apakah pasien sudah mendaftar di poli yang sama pada tanggal yang sama
     */
    public function checkExisting(int $pasienId, int $poliId, string $tanggal): ?object
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE pasien_id = :pid AND poli_id = :polid AND tanggal_kunjungan = :tgl AND status != 'cancelled'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid', $pasienId, PDO::PARAM_INT);
        $stmt->bindValue(':polid', $poliId, PDO::PARAM_INT);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res ? (object)$res : null;
    }

    public function countForDate(string $tanggal): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE tanggal_kunjungan = :tanggal AND status <> 'cancelled'"
        );
        $stmt->execute([':tanggal' => $tanggal]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Ambil pendaftaran lengkap dengan relasi
     */
    public function findWithDetails(int $id)
    {
        $sql = "SELECT p.*,
                       pas.no_rekam_medis, pas.nik, pas.nama_lengkap as nama_pasien, pas.jenis_kelamin, pas.no_telepon, pas.tanggal_lahir,
                       pas.user_id as patient_user_id,
                       pol.nama_poli, pol.kode_poli,
                       d.nama_dokter, d.spesialisasi,
                       j.jam_mulai, j.jam_selesai,
                       a.no_antrean, a.no_urut, a.status as status_antrean
                FROM {$this->table} p
                JOIN pasien pas ON p.pasien_id = pas.id
                JOIN poli pol ON p.poli_id = pol.id
                JOIN dokter d ON p.dokter_id = d.id
                JOIN jadwal_dokter j ON p.jadwal_id = j.id
                LEFT JOIN antrean a ON a.pendaftaran_id = p.id
                WHERE p.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Ambil daftar pendaftaran hari ini / filter
     */
    public function getList(array $filters = [], int $limit = 20, int $offset = 0): array
    {
        $sql = "SELECT p.*,
                       pas.no_rekam_medis, pas.nama_lengkap as nama_pasien, pas.no_telepon,
                       pol.nama_poli, pol.kode_poli,
                       d.nama_dokter,
                       a.id as id_antrean, a.no_antrean, a.status as status_antrean
                FROM {$this->table} p
                JOIN pasien pas ON p.pasien_id = pas.id
                JOIN poli pol ON p.poli_id = pol.id
                JOIN dokter d ON p.dokter_id = d.id
                LEFT JOIN antrean a ON a.pendaftaran_id = p.id
                WHERE 1=1";

        [$filterSql, $params] = $this->buildListFilters($filters);
        $sql .= $filterSql;

        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function countList(array $filters = []): int
    {
        $sql = "SELECT COUNT(DISTINCT p.id)
                FROM {$this->table} p
                JOIN pasien pas ON p.pasien_id = pas.id
                LEFT JOIN antrean a ON a.pendaftaran_id = p.id
                WHERE 1=1";
        [$filterSql, $params] = $this->buildListFilters($filters);
        $stmt = $this->db->prepare($sql . $filterSql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    private function buildListFilters(array $filters): array
    {
        $sql = '';
        $params = [];
        if (!empty($filters['tanggal'])) {
            $sql .= " AND p.tanggal_kunjungan = :filter_tanggal";
            $params[':filter_tanggal'] = $filters['tanggal'];
        }
        if (!empty($filters['poli_id'])) {
            $sql .= " AND p.poli_id = :filter_poli";
            $params[':filter_poli'] = $filters['poli_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND p.status = :filter_status";
            $params[':filter_status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (pas.nama_lengkap LIKE :name_search OR pas.no_rekam_medis LIKE :record_search OR a.no_antrean LIKE :queue_search)";
            $keyword = '%' . $filters['search'] . '%';
            $params[':name_search'] = $keyword;
            $params[':record_search'] = $keyword;
            $params[':queue_search'] = $keyword;
        }

        return [$sql, $params];
    }
}
