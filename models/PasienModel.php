<?php
/**
 * ANTRIAN SEHAT - Pasien Model
 * 
 * Model untuk mengelola data pasien.
 * Menangani CRUD data pasien, pencarian NIK/Nama/RM, dan riwayat kunjungan.
 */

class PasienModel extends Model
{
    protected string $table = 'pasien';

    /**
     * Cari pasien berdasarkan NIK
     */
    public function findByNIK(string $nik)
    {
        $sql = "SELECT * FROM {$this->table} WHERE nik = :nik LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':nik', $nik);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cari pasien berdasarkan Nomor Rekam Medis
     */
    public function findByNoRM(string $noRM)
    {
        $sql = "SELECT * FROM {$this->table} WHERE no_rekam_medis = :rm LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':rm', $noRM);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cari pasien berdasarkan User ID
     */
    public function findByUserId(int $userId)
    {
        $sql = "SELECT * FROM {$this->table} WHERE user_id = :uid LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Pencarian pasien dengan filter & pagination
     */
    public function search(string $keyword = '', int $limit = 10, int $offset = 0): array
    {
        $sql = "SELECT p.*, u.username, u.email 
                FROM {$this->table} p 
                LEFT JOIN users u ON p.user_id = u.id";
        
        $params = [];
        if (!empty($keyword)) {
            $sql .= " WHERE p.nama_lengkap LIKE :name_search OR p.nik LIKE :nik_search OR p.no_rekam_medis LIKE :record_search OR p.no_telepon LIKE :phone_search";
            $searchTerm = '%' . $keyword . '%';
            $params = [
                ':name_search' => $searchTerm,
                ':nik_search' => $searchTerm,
                ':record_search' => $searchTerm,
                ':phone_search' => $searchTerm,
            ];
        }

        $sql .= " ORDER BY p.id DESC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Hitung total pasien dari pencarian
     */
    public function countSearch(string $keyword = ''): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        $params = [];

        if (!empty($keyword)) {
            $sql .= " WHERE nama_lengkap LIKE :name_search OR nik LIKE :nik_search OR no_rekam_medis LIKE :record_search OR no_telepon LIKE :phone_search";
            $searchTerm = '%' . $keyword . '%';
            $params = [
                ':name_search' => $searchTerm,
                ':nik_search' => $searchTerm,
                ':record_search' => $searchTerm,
                ':phone_search' => $searchTerm,
            ];
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $res = $stmt->fetch();

        return (int) (is_object($res) ? ($res->total ?? 0) : ($res['total'] ?? 0));
    }

    public function countCreatedBetween(string $start, string $end): int
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM {$this->table} WHERE created_at >= :start AND created_at < :end"
        );
        $stmt->execute([':start' => $start, ':end' => $end]);
        return (int) $stmt->fetchColumn();
    }

    /**
     * Ambil pasien terbaru
     */
    public function getRecent(int $limit = 5): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY id DESC LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil riwayat kunjungan pasien
     */
    public function getRiwayatKunjungan(int $pasienId): array
    {
        $sql = "SELECT a.*, p.tanggal_kunjungan, p.keluhan, 
                       pol.nama_poli, pol.kode_poli,
                       d.nama_dokter, d.spesialisasi
                FROM antrean a
                JOIN pendaftaran p ON a.pendaftaran_id = p.id
                JOIN poli pol ON a.poli_id = pol.id
                JOIN dokter d ON a.dokter_id = d.id
                WHERE a.pasien_id = :pid
                ORDER BY a.tanggal_antrean DESC, a.id DESC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid', $pasienId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Buat Nomor Rekam Medis unik berikutnya
     */
    public function generateUniqueNoRM(): string
    {
        $year = date('Y');
        $prefix = "RM-{$year}";
        
        $sql = "SELECT no_rekam_medis FROM {$this->table} 
                WHERE no_rekam_medis LIKE :prefix 
                ORDER BY no_rekam_medis DESC LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':prefix', "{$prefix}%");
        $stmt->execute();
        $last = $stmt->fetch();

        if ($last) {
            $lastRM = is_object($last) ? $last->no_rekam_medis : $last['no_rekam_medis'];
            $num = (int)substr($lastRM, -4);
            $next = $num + 1;
        } else {
            $next = 1;
        }

        return sprintf('%s%04d', $prefix, $next);
    }
}
