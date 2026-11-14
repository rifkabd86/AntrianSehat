-- =====================================================
-- ANTRIAN SEHAT - PRODUCTION DATABASE SCHEMA
-- Sistem Informasi Pendaftaran Pasien dan Manajemen Antrean Pelayanan Puskesmas
-- =====================================================
-- Engine: InnoDB
-- Character Set: utf8mb4 / utf8mb4_unicode_ci
-- Compatible: MySQL 8.0+ / MariaDB 10.4+
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+07:00";

-- =====================================================
-- TABEL 1: users (Akun Pengguna Sistem)
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
-- TABEL 2: pasien (Data Pasien & Rekam Medis)
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
-- TABEL 3: poli (Master Data Poliklinik)
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
-- TABEL 4: dokter (Master Data Dokter)
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
-- TABEL 5: jadwal_dokter (Jadwal Pelayanan Dokter)
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
-- TABEL 6: pendaftaran (Transaksi Pendaftaran Kunjungan)
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
-- TABEL 7: antrean (Nomor Antrean Pelayanan)
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
-- TABEL 8: kunjungan (Catatan Pelayanan Medis)
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
-- TABEL 9: notifikasi (Notifikasi Internal Pengguna)
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
-- TABEL 10: audit_logs (Audit Trail Keamanan & Aktivitas)
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
-- TABEL 11: pengaturan (Pengaturan Aplikasi & Faskes)
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

SET FOREIGN_KEY_CHECKS = 1;

