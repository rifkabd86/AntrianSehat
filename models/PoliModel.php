<?php
/**
 * ANTRIAN SEHAT - Poli Model
 * 
 * Model untuk mengelola data Poli/Poliklinik.
 */

class PoliModel extends Model
{
    protected string $table = 'poli';

    /**
     * Ambil semua poli yang aktif
     */
    public function getActive(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = 1 ORDER BY id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cari poli berdasarkan kode poli
     */
    public function findByKode(string $kodePoli)
    {
        $sql = "SELECT * FROM {$this->table} WHERE kode_poli = :kode LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':kode', $kodePoli);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Ambil poli lengkap dengan jumlah dokter dan antrean hari ini
     */
    public function getWithStats(string $tanggal = ''): array
    {
        if (empty($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $sql = "SELECT p.*,
                       COUNT(DISTINCT d.id) as total_dokter,
                       COUNT(DISTINCT CASE WHEN a.tanggal_antrean = :tgl_total THEN a.id END) as antrean_hari_ini,
                       COUNT(DISTINCT CASE WHEN a.tanggal_antrean = :tgl_waiting AND a.status = 'menunggu' THEN a.id END) as antrean_menunggu,
                       COUNT(DISTINCT CASE WHEN a.tanggal_antrean = :tgl_serving AND a.status = 'dilayani' THEN a.id END) as antrean_dilayani
                FROM {$this->table} p
                LEFT JOIN dokter d ON p.id = d.poli_id AND d.is_active = 1
                LEFT JOIN antrean a ON p.id = a.poli_id
                WHERE p.is_active = 1
                GROUP BY p.id
                ORDER BY p.id ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':tgl_total', $tanggal);
        $stmt->bindValue(':tgl_waiting', $tanggal);
        $stmt->bindValue(':tgl_serving', $tanggal);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
