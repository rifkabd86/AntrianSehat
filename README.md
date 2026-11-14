# ANTRIAN SEHAT

**Sistem Informasi Pendaftaran Pasien dan Manajemen Antrean Pelayanan Puskesmas**

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-5.3-7952B3?logo=bootstrap&logoColor=white)
![Status](https://img.shields.io/badge/Status-Production%20Ready-success)

---

## 📋 Deskripsi Aplikasi

**ANTRIAN SEHAT** adalah aplikasi web berbasis PHP Native OOP MVC untuk mendigitalisasi proses pelayanan kesehatan di fasilitas kesehatan tingkat pertama (Puskesmas/Klinik). Sistem mengelola seluruh alur kerja mulai dari pendaftaran mandiri pasien, penerbitan nomor antrean otomatis, pemanggilan antrean loket terpadu, display TV antrean bersuara, rekam medis pasien, jadwal dokter, laporan pelayanan, hingga pencatatan audit log keamanan secara komprehensif.

Seluruh data transaksi dan aktivitas operasional tersimpan secara permanen pada database **MySQL/MariaDB** menggunakan **PDO Prepared Statements**.

---

## 🚀 Fitur Utama

1. **Role-Based Access Control (RBAC):**
   - **Administrator:** Akses penuh manajemen master data, pengguna, audit log, laporan, dan pengaturan faskes.
   - **Petugas Pendaftaran:** Pendaftaran pasien baru, pencarian NIK & No. RM, verifikasi antrean.
   - **Petugas Pelayanan:** Loket pemanggilan antrean real-time, panggilan ulang, pencatatan diagnosa medis, dan status pelayanan selesai.
   - **Pasien:** Portal mandiri untuk pendaftaran kunjungan, pengambilan nomor antrean, cetak bukti tiket antrean, riwayat kunjungan, dan notifikasi.
2. **Dashboard Statistik Real-Time:**
   - 4 Kartu Statistik Berwarna (Total Pengguna, Total Pasien, Total Petugas, Antrean Menunggu) dalam susunan presisi 2 baris × 2 kolom.
   - Grafik Kunjungan Pasien dengan filter periode Harian, Mingguan, dan Bulanan (Chart.js).
   - Grafik Distribusi Status Antrean dan Kunjungan per Poli.
3. **Manajemen Antrean & Loket Pemanggilan:**
   - Penerbitan kode dan nomor urut antrean unik otomatis per poli per tanggal.
   - Transaksi database dengan row-level locking (`FOR UPDATE`) untuk mencegah duplikasi nomor antrean saat beban tinggi.
4. **Layar Display Monitor TV Antrean:**
   - Menampilkan nomor antrean yang sedang dilayani dan nomor antrean berikutnya per poli secara real-time.
   - Dilengkapi jam digital, teks informasi berjalan (*running text*), dan integrasi audio chime pemanggilan.
5. **Laporan & Rekapitulasi Pelayanan:**
   - Rekap transaksi pendaftaran dan kunjungan pasien dengan filter rentang tanggal, poli, dan dokter.
   - Format siap cetak (*print-friendly*).
6. **Keamanan & Audit Trail:**
   - Proteksi CSRF berbasis sesi dengan verifikasi kriptografis dan grace period.
   - Enkripsi password menggunakan `password_hash()` BCRYPT.
   - Pencatatan otomatis setiap aktivitas penting pada tabel `audit_logs` (IP address, user agent, data lama, dan data baru).
   - Proteksi Apache `.htaccess` untuk mengunci berkas sensitif (`.env`, storage, log, upload script execution).

---

## 🛠️ Stack Teknologi

| Komponen | Teknologi |
| :--- | :--- |
| **Backend** | PHP 8.3 Native (Object-Oriented Programming, MVC Architecture) |
| **Database** | MySQL 8.0+ / MariaDB 10.4+ (InnoDB Engine, PDO Prepared Statements) |
| **Frontend** | HTML5, CSS3, Bootstrap 5.3, Font Awesome 6 |
| **Visualisasi** | Chart.js 4.4 |
| **Web Server** | Apache 2.4+ (dengan mod_rewrite & mod_headers) |

---

## 📁 Struktur Direktori Project

```text
Antrian Sehat/
├── config/              # Konfigurasi aplikasi, database, skema SQL, dan diagnostik setup
│   ├── config.php
│   ├── bootstrap.php
│   ├── database.php
│   ├── database.sql
│   ├── index.php
│   ├── README.md
│   └── setup.php
├── controllers/         # Controller MVC (Auth, Dashboard, Pasien, Antrean, Poli, Dokter, dll.)
├── core/                # Komponen inti aplikasi (Controller, Database, Model, Router, Security, Session, Middleware, Helpers)
├── database/            # Skema database, migrasi, dan seeder
│   ├── antrian_sehat.sql# Skema lengkap dengan data awal
│   ├── schema.sql       # Struktur skema final seluruh tabel
│   ├── migrations/      # Skrip migrasi database
│   └── seeders/         # Master seeder data awal (DatabaseSeeder.php)
├── models/              # Model data (Antrean, Pasien, Poli, Dokter, Jadwal, Pendaftaran, Users, dll.)
├── public/              # Document Root publik
│   ├── .htaccess        # Rewrite rules untuk hosting
│   ├── index.php        # Front controller aplikasi
│   ├── css/             # Stylesheet utama (style.css, dashboard.css)
│   ├── js/              # JavaScript interaktif (app.js, frontend.js, queue-display.js)
│   ├── images/          # Gambar & aset visual publik
│   └── uploads/         # Berkas unggahan pengguna (terproteksi .htaccess)
├── views/               # Template View Bootstrap 5 berdasarkan modul
│   ├── layouts/         # Layout utama (main.php, auth.php, foundation.php, header.php, sidebar.php, footer.php)
│   ├── auth/            # Login, Register, Profile, Change Password
│   ├── dashboard/       # Dashboard utama
│   ├── pasien/          # Master data pasien & riwayat
│   ├── pendaftaran/     # Form pendaftaran & tiket antrean
│   ├── antrean/         # Loket pemanggilan & daftar antrean
│   ├── display/         # Layar TV monitor antrean
│   ├── poli/            # Master poli
│   ├── dokter/          # Master dokter & jadwal
│   ├── jadwal/          # Jadwal praktik dokter
│   ├── laporan/         # Laporan & cetak
│   ├── audit/           # Audit trail log
│   ├── notifikasi/      # Notifikasi pengguna
│   ├── settings/        # Pengaturan puskesmas
│   ├── users/           # Manajemen pengguna
│   └── errors/          # Halaman error (403, 404)
├── routes/              # Peta rute URL aplikasi (web.php)
├── storage/             # Direktori sesi, log, dan cache
├── tests/               # Pengujian otomatis (lint, view renderer, E2E)
├── docs/                # Dokumentasi lengkap
├── .env.example         # Template konfigurasi environment
├── .gitignore           # Proteksi berkas sensitif Git
├── .htaccess            # Proteksi root Apache
├── index.php            # Entry point root aplikasi
└── README.md            # Dokumentasi ini
```

---

## ⚡ Panduan Instalasi Cepat

1. **Salin project ke direktori htdocs XAMPP:**
   ```bash
   C:\xampp\htdocs\Antrian Sehat
   ```
2. **Buat database MySQL `antrian_sehat` dan import skema:**
   Import berkas `database/schema.sql` melalui phpMyAdmin.
3. **Konfigurasi file `.env`:**
   Salin `.env.example` menjadi `.env` dan sesuaikan kredensial MySQL.
4. **Jalankan Seeder Master Data:**
   ```bash
   php database/seeders/DatabaseSeeder.php
   ```
5. **Akses aplikasi di browser:**
   ```
   http://localhost/Antrian%20Sehat
   ```

> Untuk panduan instalasi lengkap pada hosting cPanel / VPS, baca [docs/INSTALLATION.md](docs/INSTALLATION.md).

---

## 🔑 Akun Default Sistem

| Role | Username | Password Default |
| :--- | :--- | :--- |
| **Administrator** | `admin` | `password123` |
| **Petugas Pendaftaran** | `petugas1` | `password123` |
| **Petugas Pelayanan** | `petugas2` | `password123` |
| **Pasien** | `pasien1` | `password123` |

---

## 🧪 Hasil Pengujian Sistem (Automated Test Suite)

- **End-to-End Business Flow:** 30 dari 30 skenario pengujian lulus (`tests/test_master_e2e.php`) — **100% PASS**.
- **PHP Syntax & Linter Check:** 100 berkas PHP lulus tanpa error (`tests/lint.php`) — **0 Error**.
- **Template View Rendering:** 37 dari 37 file template view sukses dirender (`tests/test_all_views.php`) — **100% PASS**.
- **CSRF Token Lifecycle:** Validasi token, grace period, dan multi-tab lulus (`tests/test_csrf_and_registration.php`) — **100% PASS**.

Detail lengkap hasil pengujian dapat dibaca pada [docs/TESTING.md](docs/TESTING.md).
Panduan perilisan hosting dan backup dapat dibaca pada [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).

---

**ANTRIAN SEHAT** — *Sistem Manajemen Pelayanan Antrean Kesehatan Modern, Aman, dan Andal.*
