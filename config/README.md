# ANTRIAN SEHAT - Konfigurasi Sistem

Folder `config/` berisi seluruh file konfigurasi utama untuk aplikasi **ANTRIAN SEHAT** (Sistem Informasi Pendaftaran Pasien dan Manajemen Antrean Pelayanan Puskesmas).

## Daftar File

| File | Fungsi |
|---|---|
| `config.php` | Pengaturan umum aplikasi (nama aplikasi, timezone `Asia/Jakarta`, auto-detect `BASE_URL`, pembacaan `.env`, error reporting) |
| `bootstrap.php` | Inisialisasi autoloader class (`core/`, `controllers/`, `models/`), helper global, dan session |
| `database.php` | Inisialisasi kelas koneksi database PDO MySQL/MariaDB (`core/Database.php`) |
| `database.sql` | Skema SQL lengkap beserta data awal untuk inisialisasi database MySQL (`antrian_sehat`) |
| `setup.php` | Script diagnostik & pemeriksaan kesiapan lingkungan (PHP extensions, koneksi MySQL, tabel database) |
| `index.php` | Proteksi direktori untuk mencegah directory listing |

## Cara Menggunakan

### 1. Import Database Manual
```bash
mysql -u root -p antrian_sehat < config/database.sql
```
Atau melalui `database/antrian_sehat.sql` di phpMyAdmin.

### 2. Konfigurasi Koneksi Database
Sesuaikan pengaturan pada file `.env` di root proyek atau konstanta default pada `config/config.php`:
```ini
DB_HOST=localhost
DB_PORT=3306
DB_NAME=antrian_sehat
DB_USER=root
DB_PASS=
```
