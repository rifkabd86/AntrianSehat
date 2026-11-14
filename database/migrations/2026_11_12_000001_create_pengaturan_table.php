<?php
/**
 * Migration: Create Pengaturan Table if not exists
 */

require dirname(__DIR__, 2) . '/config/bootstrap.php';

echo "Running migration: create pengaturan table...\n";

$db = Database::getInstance()->getConnection();

$sql = "CREATE TABLE IF NOT EXISTS `pengaturan` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kunci` VARCHAR(50) NOT NULL,
    `nilai` TEXT DEFAULT NULL,
    `kategori` VARCHAR(50) NOT NULL DEFAULT 'umum',
    `keterangan` VARCHAR(255) DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kunci` (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";

$db->exec($sql);

$insertSql = "INSERT INTO `pengaturan` (`kunci`, `nilai`, `kategori`, `keterangan`) VALUES
('nama_puskesmas', 'Puskesmas Sehat Sentosa', 'identitas', 'Nama resmi fasilitas kesehatan'),
('alamat_puskesmas', 'Jl. Merdeka No. 100, Jakarta Pusat', 'identitas', 'Alamat lengkap faskes'),
('telepon_puskesmas', '(021) 555-0199', 'identitas', 'Nomor telepon resmi'),
('email_puskesmas', 'puskesmas@sehatsentosa.go.id', 'identitas', 'Alamat email resmi'),
('kepala_puskesmas', 'dr. H. Budi Santoso, M.Kes', 'identitas', 'Nama dan gelar kepala puskesmas'),
('nip_kepala', '19780512 200312 1 002', 'identitas', 'NIP Kepala Puskesmas'),
('running_text_display', 'Selamat Datang di Puskesmas Sehat Sentosa • Harap perhatikan nomor antrean pada layar monitor • Siapkan Kartu Identitas (KTP/BPJS) saat dipanggil ke ruang pemeriksaan • Jaga kebersihan dan ketertiban area ruang tunggu • Layanan Pengaduan: 0812-0000-0000', 'display', 'Teks berjalan pada layar display monitor'),
('audio_chime_aktif', '1', 'display', 'Status aktif suara bel pemanggilan antrean (1: aktif, 0: nonaktif)'),
('kuota_harian_default', '30', 'antrean', 'Kuota pendaftaran harian standar per poli')
ON DUPLICATE KEY UPDATE `kunci` = VALUES(`kunci`);";

$db->exec($insertSql);

echo "Migration completed successfully! Table 'pengaturan' is ready.\n";

