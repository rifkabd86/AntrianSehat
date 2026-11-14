<?php
/**
 * ANTRIAN SEHAT - Dokter Model
 * 
 * Model untuk mengelola data dokter dan spesialisasi.
 */

class DokterModel extends Model
{
    protected string $table = 'dokter';

    /**
     * Ambil semua dokter dengan relasi nama poli
     */
    public function getAllWithPoli(bool $activeOnly = false): array
    {
        $sql = "SELECT d.*, p.nama_poli, p.kode_poli, p.warna as warna_poli 
                FROM {$this->table} d
                JOIN poli p ON d.poli_id = p.id";
        
        if ($activeOnly) {
            $sql .= " WHERE d.is_active = 1 AND p.is_active = 1";
        }
        $sql .= " ORDER BY p.id ASC, d.nama_dokter ASC";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil dokter berdasarkan ID Poli
     */
    public function getByPoliId(int $poliId): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE poli_id = :pid AND is_active = 1 ORDER BY nama_dokter ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':pid', $poliId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Cari satu dokter lengkap dengan info poli
     */
    public function findWithPoli(int $id)
    {
        $sql = "SELECT d.*, p.nama_poli, p.kode_poli 
                FROM {$this->table} d
                JOIN poli p ON d.poli_id = p.id
                WHERE d.id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Ambil jadwal praktik dokter
     */
    public function getJadwal(int $dokterId): array
    {
        $sql = "SELECT j.*, p.nama_poli 
                FROM jadwal_dokter j
                JOIN poli p ON j.poli_id = p.id
                WHERE j.dokter_id = :did AND j.is_active = 1
                ORDER BY FIELD(j.hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':did', $dokterId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

