<?php
/**
 * ANTRIAN SEHAT - Pengaturan Model
 * 
 * Model untuk mengelola konfigurasi aplikasi, identitas faskes,
 * pengaturan operasional antrean, dan tampilan display.
 */

class PengaturanModel extends Model
{
    protected string $table = 'pengaturan';

    /**
     * Ambil seluruh pengaturan sebagai associative array [kunci => nilai]
     */
    public function getAllKeyValue(): array
    {
        try {
            $sql = "SELECT kunci, nilai FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $rows = $stmt->fetchAll();

            $result = [];
            foreach ($rows as $row) {
                $k = is_object($row) ? $row->kunci : $row['kunci'];
                $v = is_object($row) ? $row->nilai : $row['nilai'];
                $result[$k] = $v;
            }
            return $result;
        } catch (\Throwable $e) {
            // Fallback default jika tabel belum di-create
            return $this->getDefaults();
        }
    }

    /**
     * Ambil satu nilai pengaturan berdasarkan kunci
     */
    public function get(string $key, ?string $default = null): ?string
    {
        try {
            $sql = "SELECT nilai FROM {$this->table} WHERE kunci = :kunci LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->bindValue(':kunci', $key);
            $stmt->execute();
            $row = $stmt->fetch();
            if ($row) {
                return is_object($row) ? $row->nilai : $row['nilai'];
            }
        } catch (\Throwable $e) {
            // fallback
        }

        $defaults = $this->getDefaults();
        return $defaults[$key] ?? $default;
    }

    /**
     * Update beberapa pengaturan sekaligus
     */
    public function updateBatch(array $settings): bool
    {
        $sql = "INSERT INTO {$this->table} (kunci, nilai, updated_at) 
                VALUES (:kunci, :nilai, NOW()) 
                ON DUPLICATE KEY UPDATE nilai = VALUES(nilai), updated_at = NOW()";

        $stmt = $this->db->prepare($sql);
        foreach ($settings as $key => $val) {
            $stmt->bindValue(':kunci', $key);
            $stmt->bindValue(':nilai', (string)$val);
            $stmt->execute();
        }
        return true;
    }

    /**
     * Nilai default sistem
     */
    public function getDefaults(): array
    {
        return [
            'nama_puskesmas'       => 'Puskesmas Sehat Sentosa',
            'alamat_puskesmas'     => 'Jl. Merdeka No. 100, Jakarta Pusat',
            'telepon_puskesmas'    => '(021) 555-0199',
            'email_puskesmas'      => 'puskesmas@sehatsentosa.go.id',
            'kepala_puskesmas'     => 'dr. H. Budi Santoso, M.Kes',
            'nip_kepala'           => '19780512 200312 1 002',
            'running_text_display' => 'Selamat Datang di Puskesmas Sehat Sentosa • Harap perhatikan nomor antrean pada layar monitor • Siapkan Kartu Identitas (KTP/BPJS) saat dipanggil ke ruang pemeriksaan • Jaga kebersihan dan ketertiban area ruang tunggu • Layanan Pengaduan: 0812-0000-0000',
            'audio_chime_aktif'    => '1',
            'kuota_harian_default' => '30',
        ];
    }
}

