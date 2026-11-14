-- =====================================================
-- ANTRIAN SEHAT - Sistem Informasi Pendaftaran Pasien
-- dan Manajemen Antrean Pelayanan Puskesmas
-- =====================================================
-- Database: antrian_sehat
-- Version: 1.0.0
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+07:00";
SET NAMES utf8mb4;

-- Buat database

-- =====================================================
-- TABEL 1: users (Akun Pengguna)
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `no_telepon` VARCHAR(20) DEFAULT NULL,
    `role` ENUM('admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien') NOT NULL DEFAULT 'pasien',
    `avatar` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `last_login` DATETIME DEFAULT NULL,
    `reset_token` VARCHAR(255) DEFAULT NULL,
    `reset_token_expiry` DATETIME DEFAULT NULL,
    `deleted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_username` (`username`),
    UNIQUE KEY `uk_email` (`email`),
    INDEX `idx_role` (`role`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 2: pasien (Data Pasien)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pasien` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `no_rekam_medis` VARCHAR(20) NOT NULL,
    `nik` VARCHAR(16) NOT NULL,
    `nama_lengkap` VARCHAR(100) NOT NULL,
    `jenis_kelamin` ENUM('Laki-laki', 'Perempuan') NOT NULL,
    `tempat_lahir` VARCHAR(100) NOT NULL,
    `tanggal_lahir` DATE NOT NULL,
    `alamat` TEXT NOT NULL,
    `no_telepon` VARCHAR(20) NOT NULL,
    `golongan_darah` ENUM('A', 'B', 'AB', 'O', '-') DEFAULT '-',
    `deleted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_no_rekam_medis` (`no_rekam_medis`),
    UNIQUE KEY `uk_nik` (`nik`),
    UNIQUE KEY `uk_pasien_user` (`user_id`),
    INDEX `idx_nama` (`nama_lengkap`),
    INDEX `idx_user_id` (`user_id`),
    CONSTRAINT `fk_pasien_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 3: poli (Data Poli/Poliklinik)
-- =====================================================
CREATE TABLE IF NOT EXISTS `poli` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kode_poli` VARCHAR(10) NOT NULL,
    `nama_poli` VARCHAR(100) NOT NULL,
    `deskripsi` TEXT DEFAULT NULL,
    `icon` VARCHAR(50) DEFAULT 'fas fa-stethoscope',
    `warna` VARCHAR(7) DEFAULT '#2563EB',
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kode_poli` (`kode_poli`),
    INDEX `idx_is_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 4: dokter (Data Dokter)
-- =====================================================
CREATE TABLE IF NOT EXISTS `dokter` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `poli_id` INT UNSIGNED NOT NULL,
    `kode_dokter` VARCHAR(20) NOT NULL,
    `nama_dokter` VARCHAR(100) NOT NULL,
    `spesialisasi` VARCHAR(100) DEFAULT NULL,
    `no_telepon` VARCHAR(20) DEFAULT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `foto` VARCHAR(255) DEFAULT NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kode_dokter` (`kode_dokter`),
    UNIQUE KEY `uk_dokter_poli` (`id`, `poli_id`),
    INDEX `idx_poli_id` (`poli_id`),
    INDEX `idx_is_active` (`is_active`),
    CONSTRAINT `fk_dokter_poli` FOREIGN KEY (`poli_id`) REFERENCES `poli`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 5: jadwal_dokter (Jadwal Praktik Dokter)
-- =====================================================
CREATE TABLE IF NOT EXISTS `jadwal_dokter` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `dokter_id` INT UNSIGNED NOT NULL,
    `poli_id` INT UNSIGNED NOT NULL,
    `hari` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu') NOT NULL,
    `jam_mulai` TIME NOT NULL,
    `jam_selesai` TIME NOT NULL,
    `kuota` INT NOT NULL DEFAULT 30,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_dokter_id` (`dokter_id`),
    INDEX `idx_poli_id` (`poli_id`),
    INDEX `idx_hari` (`hari`),
    UNIQUE KEY `uk_jadwal_dokter_waktu` (`dokter_id`, `hari`, `jam_mulai`, `jam_selesai`),
    UNIQUE KEY `uk_jadwal_dokter_poli` (`id`, `dokter_id`, `poli_id`),
    CONSTRAINT `fk_jadwal_dokter` FOREIGN KEY (`dokter_id`) REFERENCES `dokter`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `fk_jadwal_dokter_poli` FOREIGN KEY (`dokter_id`, `poli_id`) REFERENCES `dokter`(`id`, `poli_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_jadwal_poli` FOREIGN KEY (`poli_id`) REFERENCES `poli`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 6: pendaftaran (Pendaftaran Kunjungan)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pendaftaran` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pasien_id` INT UNSIGNED NOT NULL,
    `poli_id` INT UNSIGNED NOT NULL,
    `dokter_id` INT UNSIGNED NOT NULL,
    `jadwal_id` INT UNSIGNED NOT NULL,
    `tanggal_kunjungan` DATE NOT NULL,
    `keluhan` TEXT DEFAULT NULL,
    `status` ENUM('pending', 'confirmed', 'cancelled') NOT NULL DEFAULT 'pending',
    `registered_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_pasien_id` (`pasien_id`),
    INDEX `idx_poli_id` (`poli_id`),
    INDEX `idx_dokter_id` (`dokter_id`),
    INDEX `idx_tanggal` (`tanggal_kunjungan`),
    INDEX `idx_status` (`status`),
    UNIQUE KEY `uk_pasien_poli_tanggal` (`pasien_id`, `poli_id`, `tanggal_kunjungan`),
    UNIQUE KEY `uk_pendaftaran_konsistensi` (`id`, `pasien_id`, `poli_id`, `dokter_id`, `tanggal_kunjungan`),
    UNIQUE KEY `uk_pendaftaran_jadwal` (`id`, `jadwal_id`, `dokter_id`, `poli_id`),
    CONSTRAINT `fk_pendaftaran_pasien` FOREIGN KEY (`pasien_id`) REFERENCES `pasien`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pendaftaran_poli` FOREIGN KEY (`poli_id`) REFERENCES `poli`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pendaftaran_dokter` FOREIGN KEY (`dokter_id`) REFERENCES `dokter`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pendaftaran_jadwal` FOREIGN KEY (`jadwal_id`) REFERENCES `jadwal_dokter`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pendaftaran_jadwal_konsisten` FOREIGN KEY (`jadwal_id`, `dokter_id`, `poli_id`) REFERENCES `jadwal_dokter`(`id`, `dokter_id`, `poli_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_pendaftaran_user` FOREIGN KEY (`registered_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 7: antrean (Nomor Antrean)
-- =====================================================
CREATE TABLE IF NOT EXISTS `antrean` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `pendaftaran_id` INT UNSIGNED NOT NULL,
    `pasien_id` INT UNSIGNED NOT NULL,
    `poli_id` INT UNSIGNED NOT NULL,
    `dokter_id` INT UNSIGNED NOT NULL,
    `no_antrean` VARCHAR(10) NOT NULL,
    `no_urut` INT NOT NULL,
    `tanggal_antrean` DATE NOT NULL,
    `status` ENUM('menunggu', 'dipanggil', 'dilayani', 'selesai', 'dilewati', 'dibatalkan') NOT NULL DEFAULT 'menunggu',
    `waktu_dipanggil` DATETIME DEFAULT NULL,
    `waktu_mulai_layanan` DATETIME DEFAULT NULL,
    `waktu_selesai` DATETIME DEFAULT NULL,
    `alasan_batal` TEXT DEFAULT NULL,
    `called_by` INT UNSIGNED DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_pendaftaran` (`pendaftaran_id`),
    UNIQUE KEY `uk_no_antrean_tanggal_poli` (`no_antrean`, `tanggal_antrean`, `poli_id`),
    UNIQUE KEY `uk_urut_tanggal_poli` (`tanggal_antrean`, `poli_id`, `no_urut`),
    INDEX `idx_pasien_id` (`pasien_id`),
    INDEX `idx_poli_id` (`poli_id`),
    INDEX `idx_dokter_id` (`dokter_id`),
    INDEX `idx_tanggal` (`tanggal_antrean`),
    INDEX `idx_status` (`status`),
    INDEX `idx_no_urut` (`no_urut`),
    CONSTRAINT `fk_antrean_pendaftaran` FOREIGN KEY (`pendaftaran_id`) REFERENCES `pendaftaran`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_antrean_pasien` FOREIGN KEY (`pasien_id`) REFERENCES `pasien`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_antrean_poli` FOREIGN KEY (`poli_id`) REFERENCES `poli`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_antrean_dokter` FOREIGN KEY (`dokter_id`) REFERENCES `dokter`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_antrean_pendaftaran_data` FOREIGN KEY (`pendaftaran_id`, `pasien_id`, `poli_id`, `dokter_id`, `tanggal_antrean`) REFERENCES `pendaftaran`(`id`, `pasien_id`, `poli_id`, `dokter_id`, `tanggal_kunjungan`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_antrean_caller` FOREIGN KEY (`called_by`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 8: kunjungan (Catatan Pelayanan)
-- =====================================================
CREATE TABLE IF NOT EXISTS `kunjungan` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `antrean_id` INT UNSIGNED NOT NULL,
    `petugas_id` INT UNSIGNED DEFAULT NULL,
    `diagnosa` VARCHAR(500) DEFAULT NULL,
    `catatan` TEXT DEFAULT NULL,
    `status` ENUM('berlangsung', 'selesai', 'dirujuk') NOT NULL DEFAULT 'berlangsung',
    `waktu_mulai` DATETIME DEFAULT NULL,
    `waktu_selesai` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kunjungan_antrean` (`antrean_id`),
    INDEX `idx_kunjungan_status` (`status`),
    INDEX `idx_kunjungan_waktu_mulai` (`waktu_mulai`),
    CONSTRAINT `fk_kunjungan_antrean` FOREIGN KEY (`antrean_id`) REFERENCES `antrean`(`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT `fk_kunjungan_petugas` FOREIGN KEY (`petugas_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 9: notifikasi (Notifikasi Internal)
-- =====================================================
CREATE TABLE IF NOT EXISTS `notifikasi` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED NOT NULL,
    `judul` VARCHAR(200) NOT NULL,
    `pesan` TEXT NOT NULL,
    `tipe` ENUM('info', 'success', 'warning', 'danger') NOT NULL DEFAULT 'info',
    `link` VARCHAR(255) DEFAULT NULL,
    `is_read` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_is_read` (`is_read`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_notifikasi_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- TABEL 10: audit_logs (Audit Log)
-- =====================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT UNSIGNED DEFAULT NULL,
    `username` VARCHAR(50) NOT NULL,
    `role` VARCHAR(30) NOT NULL,
    `aksi` VARCHAR(100) NOT NULL,
    `tabel` VARCHAR(50) DEFAULT NULL,
    `record_id` INT UNSIGNED DEFAULT NULL,
    `data_lama` JSON DEFAULT NULL,
    `data_baru` JSON DEFAULT NULL,
    `ip_address` VARCHAR(45) DEFAULT NULL,
    `user_agent` TEXT DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_aksi` (`aksi`),
    INDEX `idx_tabel` (`tabel`),
    INDEX `idx_created_at` (`created_at`),
    CONSTRAINT `fk_audit_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- DATA AWAL: Users (Akun Pengujian)
-- =====================================================
-- Password awal untuk seluruh akun seed: password123
INSERT INTO `users` (`username`, `password`, `nama_lengkap`, `email`, `no_telepon`, `role`, `is_active`) VALUES
('admin', '$2y$10$MW/he09/WWGqHrHc4cZu..olK9feDZxZ9zdil0PKUis36WhR5bqUC', 'Administrator Puskesmas', 'admin@antriansehat.id', '081200000001', 'admin', 1),
('petugas1', '$2y$10$MW/he09/WWGqHrHc4cZu..olK9feDZxZ9zdil0PKUis36WhR5bqUC', 'Siti Rahayu', 'petugas1@antriansehat.id', '081200000002', 'petugas_pendaftaran', 1),
('petugas2', '$2y$10$MW/he09/WWGqHrHc4cZu..olK9feDZxZ9zdil0PKUis36WhR5bqUC', 'Ahmad Fauzi', 'petugas2@antriansehat.id', '081200000003', 'petugas_pelayanan', 1),
('pasien1', '$2y$10$MW/he09/WWGqHrHc4cZu..olK9feDZxZ9zdil0PKUis36WhR5bqUC', 'Rina Aprilia', 'pasien1@antriansehat.id', '081234567890', 'pasien', 1)
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- =====================================================
-- DATA AWAL: Poli
-- =====================================================
INSERT INTO `poli` (`kode_poli`, `nama_poli`, `deskripsi`, `icon`, `warna`, `is_active`) VALUES
('PLU', 'Poli Umum', 'Pelayanan kesehatan umum untuk semua keluhan', 'fas fa-stethoscope', '#2563EB', 1),
('PLG', 'Poli Gigi', 'Pelayanan kesehatan gigi dan mulut', 'fas fa-tooth', '#10B981', 1),
('PLA', 'Poli Anak', 'Pelayanan kesehatan anak dan tumbuh kembang', 'fas fa-baby', '#F59E0B', 1),
('PLK', 'Poli KIA', 'Pelayanan Kesehatan Ibu dan Anak', 'fas fa-female', '#EC4899', 1),
('PLD', 'Poli Penyakit Dalam', 'Pelayanan penyakit dalam dan konsultasi spesialis', 'fas fa-heartbeat', '#EF4444', 1)
ON DUPLICATE KEY UPDATE `kode_poli` = VALUES(`kode_poli`);

-- =====================================================
-- DATA AWAL: Dokter
-- =====================================================
INSERT INTO `dokter` (`poli_id`, `kode_dokter`, `nama_dokter`, `spesialisasi`, `no_telepon`, `is_active`) VALUES
((SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'DKT001', 'dr. Budi Santoso', 'Dokter Umum', '081300000001', 1),
((SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'DKT002', 'dr. Ani Wijaya', 'Dokter Umum', '081300000002', 1),
((SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLG'), 'DKT003', 'drg. Dewi Lestari', 'Dokter Gigi', '081300000003', 1),
((SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLA'), 'DKT004', 'dr. Andi Pratama, Sp.A', 'Spesialis Anak', '081300000004', 1),
((SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLK'), 'DKT005', 'dr. Nurhayati, Sp.OG', 'Spesialis Obstetri & Ginekologi', '081300000005', 1),
((SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLD'), 'DKT006', 'dr. Agus Salim, Sp.PD', 'Spesialis Penyakit Dalam', '081300000006', 1)
ON DUPLICATE KEY UPDATE `kode_dokter` = VALUES(`kode_dokter`);

-- =====================================================
-- DATA AWAL: Jadwal Dokter
-- =====================================================
INSERT INTO `jadwal_dokter` (`dokter_id`, `poli_id`, `hari`, `jam_mulai`, `jam_selesai`, `kuota`, `is_active`) VALUES
-- dr. Budi Santoso - Poli Umum
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT001'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Senin', '08:00:00', '12:00:00', 30, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT001'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Selasa', '08:00:00', '12:00:00', 30, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT001'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Rabu', '08:00:00', '12:00:00', 30, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT001'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Kamis', '08:00:00', '12:00:00', 30, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT001'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Jumat', '08:00:00', '11:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT001'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Sabtu', '08:00:00', '12:00:00', 20, 1),
-- dr. Ani Wijaya - Poli Umum
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT002'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Senin', '13:00:00', '16:00:00', 25, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT002'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Rabu', '13:00:00', '16:00:00', 25, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT002'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Jumat', '13:00:00', '16:00:00', 25, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT002'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLU'), 'Minggu', '08:00:00', '12:00:00', 15, 1),
-- drg. Dewi Lestari - Poli Gigi
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT003'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLG'), 'Senin', '08:00:00', '12:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT003'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLG'), 'Selasa', '08:00:00', '12:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT003'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLG'), 'Kamis', '08:00:00', '12:00:00', 20, 1),
-- dr. Andi Pratama - Poli Anak
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT004'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLA'), 'Selasa', '08:00:00', '12:00:00', 25, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT004'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLA'), 'Kamis', '08:00:00', '12:00:00', 25, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT004'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLA'), 'Sabtu', '08:00:00', '12:00:00', 20, 1),
-- dr. Nurhayati - Poli KIA
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT005'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLK'), 'Senin', '08:00:00', '12:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT005'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLK'), 'Rabu', '08:00:00', '12:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT005'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLK'), 'Jumat', '08:00:00', '12:00:00', 20, 1),
-- dr. Agus Salim - Poli Penyakit Dalam
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT006'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLD'), 'Selasa', '08:00:00', '12:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT006'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLD'), 'Kamis', '08:00:00', '12:00:00', 20, 1),
( (SELECT `id` FROM `dokter` WHERE `kode_dokter` = 'DKT006'), (SELECT `id` FROM `poli` WHERE `kode_poli` = 'PLD'), 'Sabtu', '08:00:00', '11:00:00', 15, 1)
ON DUPLICATE KEY UPDATE `dokter_id` = VALUES(`dokter_id`);

-- =====================================================
-- DATA AWAL: Pasien
-- =====================================================
INSERT INTO `pasien` (`user_id`, `no_rekam_medis`, `nik`, `nama_lengkap`, `jenis_kelamin`, `tempat_lahir`, `tanggal_lahir`, `alamat`, `no_telepon`, `golongan_darah`) VALUES
((SELECT `id` FROM `users` WHERE `username` = 'pasien1'), 'RM-20250001', '3201234567890001', 'Rina Aprilia', 'Perempuan', 'Jakarta', '1995-03-12', 'Jl. Merdeka No. 10, Jakarta Selatan', '081234567890', 'A'),
(NULL, 'RM-20250002', '3201234567890002', 'Budi Santoso', 'Laki-laki', 'Bandung', '1988-07-25', 'Jl. Asia Afrika No. 5, Bandung', '081234567891', 'B'),
(NULL, 'RM-20250003', '3201234567890003', 'Siti Nurhaliza', 'Perempuan', 'Surabaya', '1992-11-08', 'Jl. Tunjungan No. 15, Surabaya', '081234567892', 'O'),
(NULL, 'RM-20250004', '3201234567890004', 'Andi Pratama', 'Laki-laki', 'Yogyakarta', '1990-01-20', 'Jl. Malioboro No. 20, Yogyakarta', '081234567893', 'AB'),
(NULL, 'RM-20250005', '3201234567890005', 'Dewi Lestari', 'Perempuan', 'Semarang', '1985-06-14', 'Jl. Pandanaran No. 8, Semarang', '081234567894', 'A'),
(NULL, 'RM-20250006', '3201234567890006', 'Agus Salim', 'Laki-laki', 'Medan', '1978-09-30', 'Jl. Gatot Subroto No. 12, Medan', '081234567895', 'B'),
(NULL, 'RM-20250007', '3201234567890007', 'Nabila Putri', 'Perempuan', 'Makassar', '1998-04-05', 'Jl. Pettarani No. 3, Makassar', '081234567896', 'O'),
(NULL, 'RM-20250008', '3201234567890008', 'Rudi Hartono', 'Laki-laki', 'Denpasar', '1982-12-18', 'Jl. Sunset Road No. 7, Denpasar', '081234567897', 'A')
ON DUPLICATE KEY UPDATE `no_rekam_medis` = VALUES(`no_rekam_medis`);

-- =====================================================
-- TABEL 11: pengaturan (Pengaturan Aplikasi)
-- =====================================================
CREATE TABLE IF NOT EXISTS `pengaturan` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `kunci` VARCHAR(50) NOT NULL,
    `nilai` TEXT DEFAULT NULL,
    `kategori` VARCHAR(50) NOT NULL DEFAULT 'umum',
    `keterangan` VARCHAR(255) DEFAULT NULL,
    `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uk_kunci` (`kunci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pengaturan` (`kunci`, `nilai`, `kategori`, `keterangan`) VALUES
('nama_puskesmas', 'Puskesmas Sehat Sentosa', 'identitas', 'Nama resmi fasilitas kesehatan'),
('alamat_puskesmas', 'Jl. Merdeka No. 100, Jakarta Pusat', 'identitas', 'Alamat lengkap faskes'),
('telepon_puskesmas', '(021) 555-0199', 'identitas', 'Nomor telepon resmi'),
('email_puskesmas', 'puskesmas@sehatsentosa.go.id', 'identitas', 'Alamat email resmi'),
('kepala_puskesmas', 'dr. H. Budi Santoso, M.Kes', 'identitas', 'Nama dan gelar kepala puskesmas'),
('nip_kepala', '19780512 200312 1 002', 'identitas', 'NIP Kepala Puskesmas'),
('running_text_display', 'Selamat Datang di Puskesmas Sehat Sentosa • Harap perhatikan nomor antrean pada layar monitor • Siapkan Kartu Identitas (KTP/BPJS) saat dipanggil ke ruang pemeriksaan • Jaga kebersihan dan ketertiban area ruang tunggu • Layanan Pengaduan: 0812-0000-0000', 'display', 'Teks berjalan pada layar display monitor'),
('audio_chime_aktif', '1', 'display', 'Status aktif suara bel pemanggilan antrean (1: aktif, 0: nonaktif)'),
('kuota_harian_default', '30', 'antrean', 'Kuota pendaftaran harian standar per poli')
ON DUPLICATE KEY UPDATE `kunci` = VALUES(`kunci`);

-- =====================================================
-- DATA AWAL: Pendaftaran & Antrean Hari Ini (Mockup UI)
-- =====================================================
INSERT INTO `pendaftaran` (`id`, `pasien_id`, `poli_id`, `dokter_id`, `jadwal_id`, `tanggal_kunjungan`, `keluhan`, `status`) VALUES
(1, 1, 1, 1, 1, CURDATE(), 'Pemeriksaan rutin tekanan darah', 'confirmed'),
(2, 2, 1, 1, 1, CURDATE(), 'Demam dan flu sejak kemarin', 'confirmed'),
(3, 3, 2, 3, 11, CURDATE(), 'Pembersihan karang gigi (scaling)', 'confirmed'),
(4, 4, 3, 4, 14, CURDATE(), 'Konsultasi imunisasi anak', 'confirmed'),
(5, 5, 1, 1, 1, CURDATE(), 'Pusing berputar dan mual', 'confirmed'),
(6, 6, 5, 6, 20, CURDATE(), 'Kontrol gula darah dan kolesterol', 'confirmed'),
(7, 7, 2, 3, 11, CURDATE(), 'Tambal gigi berlubang', 'confirmed'),
(8, 8, 1, 1, 1, CURDATE(), 'Batuk berdahak', 'confirmed')
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);

INSERT INTO `antrean` (`id`, `pendaftaran_id`, `pasien_id`, `poli_id`, `dokter_id`, `no_antrean`, `no_urut`, `tanggal_antrean`, `status`, `waktu_dipanggil`) VALUES
(1, 1, 1, 1, 1, 'A012', 12, CURDATE(), 'dilayani', NOW() - INTERVAL 15 MINUTE),
(2, 2, 2, 1, 1, 'A013', 13, CURDATE(), 'menunggu', NULL),
(3, 3, 3, 2, 3, 'B004', 4, CURDATE(), 'menunggu', NULL),
(4, 4, 4, 3, 4, 'C002', 2, CURDATE(), 'menunggu', NULL),
(5, 5, 5, 1, 1, 'A014', 14, CURDATE(), 'menunggu', NULL),
(6, 6, 6, 5, 6, 'E001', 1, CURDATE(), 'menunggu', NULL),
(7, 7, 7, 2, 3, 'B005', 5, CURDATE(), 'menunggu', NULL),
(8, 8, 8, 1, 1, 'A015', 15, CURDATE(), 'menunggu', NULL)
ON DUPLICATE KEY UPDATE `id` = VALUES(`id`);

COMMIT;

SET FOREIGN_KEY_CHECKS = 1;
