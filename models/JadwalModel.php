<?php
/**
 * ANTRIAN SEHAT - Jadwal Dokter Model
 * 
 * Model untuk mengelola jadwal praktik dokter per hari dan jam layanan.
 */

class JadwalModel extends Model
{
    protected string $table = 'jadwal_dokter';

    /**
     * Ambil seluruh jadwal dengan relasi dokter dan poli
     */
    public function getAllWithDetails(array $filters = []): array
    {
        $sql = "SELECT j.*, d.nama_dokter, d.kode_dokter, d.spesialisasi, 
                       p.nama_poli, p.kode_poli, p.warna as warna_poli
                FROM {$this->table} j
                JOIN dokter d ON j.dokter_id = d.id
                JOIN poli p ON j.poli_id = p.id
                WHERE 1=1";

        $params = [];
        if (!empty($filters['poli_id'])) {
            $sql .= " AND j.poli_id = :polid";
            $params[':polid'] = (int)$filters['poli_id'];
        }
        if (!empty($filters['dokter_id'])) {
            $sql .= " AND j.dokter_id = :did";
            $params[':did'] = (int)$filters['dokter_id'];
        }
        if (!empty($filters['hari'])) {
            $sql .= " AND j.hari = :hari";
            $params[':hari'] = $filters['hari'];
        }
        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND j.is_active = :act";
            $params[':act'] = (int)$filters['is_active'];
        }

        $sql .= " ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'), j.jam_mulai ASC";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil 1 jadwal dengan detail dokter dan poli
     */
    public function findWithDetails(int $id): ?object
    {
        $sql = "SELECT j.*, d.nama_dokter, d.spesialisasi, p.nama_poli 
                FROM {$this->table} j
                JOIN dokter d ON j.dokter_id = d.id
                JOIN poli p ON j.poli_id = p.id
                WHERE j.id = :id LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();
        return $res ? (object)$res : null;
    }

    /**
     * Ambil jadwal dokter berdasarkan Poli dan Hari
     */
    public function getByPoliAndHari(int $poliId, string $hari): array
    {
        $sql = "SELECT j.*, d.nama_dokter, d.spesialisasi, p.nama_poli
                FROM {$this->table} j
                JOIN dokter d ON j.dokter_id = d.id
                JOIN poli p ON j.poli_id = p.id
                WHERE j.poli_id = :pid AND j.hari = :hari AND j.is_active = 1 AND d.is_active = 1
                ORDER BY j.jam_mulai ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid', $poliId, PDO::PARAM_INT);
        $stmt->bindValue(':hari', $hari);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Validasi bentrok jadwal dokter (Dokter yang sama, hari yang sama, jam tumpang tindih)
     */
    public function checkConflict(int $dokterId, string $hari, string $jamMulai, string $jamSelesai, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM {$this->table}
                WHERE dokter_id = :did 
                  AND hari = :hari 
                  AND is_active = 1
                  AND (
                      (jam_mulai < :selesai AND jam_selesai > :mulai)
                  )";

        $params = [
            ':did'     => $dokterId,
            ':hari'    => $hari,
            ':mulai'   => $jamMulai,
            ':selesai' => $jamSelesai,
        ];

        if ($excludeId !== null) {
            $sql .= " AND id != :exid";
            $params[':exid'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        return (bool)$stmt->fetch();
    }

    /**
     * Cek sisa kuota untuk jadwal dan tanggal tertentu
     */
    public function getSisaKuota(int $jadwalId, string $tanggal): array
    {
        $sql = "SELECT j.kuota,
                       COUNT(p.id) as terpakai
                FROM {$this->table} j
                LEFT JOIN pendaftaran p ON p.jadwal_id = j.id 
                     AND p.tanggal_kunjungan = :tgl 
                     AND p.status != 'cancelled'
                WHERE j.id = :jid
                GROUP BY j.id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tgl', $tanggal);
        $stmt->bindValue(':jid', $jadwalId, PDO::PARAM_INT);
        $stmt->execute();
        $res = $stmt->fetch();

        $kuota = (int) (is_object($res) ? ($res->kuota ?? 0) : ($res['kuota'] ?? 0));
        $terpakai = (int) (is_object($res) ? ($res->terpakai ?? 0) : ($res['terpakai'] ?? 0));
        $sisa = max(0, $kuota - $terpakai);

        return [
            'kuota'       => $kuota,
            'terpakai'    => $terpakai,
            'sisa'        => $sisa,
            'is_tersedia' => $sisa > 0
        ];
    }
}
