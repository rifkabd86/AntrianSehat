<?php
/**
 * ANTRIAN SEHAT - Notifikasi Model
 * 
 * Model untuk mengelola notifikasi pengguna.
 * Menyediakan method statis untuk membuat notifikasi dan method instance untuk query.
 */

class NotifikasiModel extends Model
{
    /**
     * Nama tabel database
     * @var string
     */
    protected string $table = 'notifikasi';

    /**
     * Buat notifikasi baru
     * 
     * Method statis untuk memudahkan pembuatan notifikasi dari mana saja.
     * 
     * @param int $userId ID user penerima
     * @param string $judul Judul notifikasi
     * @param string $pesan Isi pesan notifikasi
     * @param string $tipe Tipe notifikasi (info, success, warning, danger)
     * @param string|null $link Link terkait notifikasi
     * @return void
     */
    public static function createForUser(int $userId, string $judul, string $pesan, string $tipe = 'info', ?string $link = null): void
    {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO notifikasi (user_id, judul, pesan, tipe, link, is_read, created_at)
                VALUES (:user_id, :judul, :pesan, :tipe, :link, 0, :created_at)";

        $stmt = $db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':judul', $judul);
        $stmt->bindValue(':pesan', $pesan);
        $stmt->bindValue(':tipe', $tipe);
        $stmt->bindValue(':link', $link);
        $stmt->bindValue(':created_at', date('Y-m-d H:i:s'));
        $stmt->execute();
    }

    /**
     * Ambil notifikasi terbaru untuk user tertentu
     * 
     * @param int $userId ID user
     * @param int $limit Jumlah notifikasi yang diambil
     * @return array
     */
    public function getByUser(int $userId, int $limit = 10): array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE user_id = :user_id 
                ORDER BY created_at DESC 
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca
     * 
     * @param int $userId ID user
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} 
                WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result->total;
    }

    /**
     * Tandai satu notifikasi sebagai sudah dibaca
     * Memverifikasi kepemilikan notifikasi untuk keamanan.
     * 
     * @param int $id ID notifikasi
     * @param int $userId ID user (untuk verifikasi kepemilikan)
     * @return bool
     */
    public function markAsRead(int $id, int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1 
                WHERE id = :id AND user_id = :user_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Tandai semua notifikasi user sebagai sudah dibaca
     * 
     * @param int $userId ID user
     * @return bool
     */
    public function markAllAsRead(int $userId): bool
    {
        $sql = "UPDATE {$this->table} 
                SET is_read = 1 
                WHERE user_id = :user_id AND is_read = 0";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Hapus notifikasi yang lebih lama dari X hari
     * 
     * @param int $days Jumlah hari
     * @return bool
     */
    public function deleteOld(int $days = 30): bool
    {
        $sql = "DELETE FROM {$this->table} 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :days DAY)";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
