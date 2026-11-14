# Data Dictionary ANTRIAN SEHAT

Semua tabel menggunakan InnoDB, `utf8mb4`, dan collation `utf8mb4_unicode_ci`. `id` adalah `INT UNSIGNED AUTO_INCREMENT` primary key kecuali dinyatakan lain. Kolom timestamp mengikuti definisi tiap tabel; kolom `updated_at` diperbarui otomatis.

## 1. users

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK, auto increment |
| username | VARCHAR(50) | Tidak | UNIQUE |
| password | VARCHAR(255) | Tidak | Hash dari `password_hash()`, bukan plaintext |
| nama_lengkap | VARCHAR(100) | Tidak | Nama pengguna |
| email | VARCHAR(100) | Ya | UNIQUE; banyak NULL diperbolehkan |
| no_telepon | VARCHAR(20) | Ya | Nomor kontak |
| role | ENUM | `pasien` | admin, petugas_pendaftaran, petugas_pelayanan, pasien |
| avatar | VARCHAR(255) | Ya | Path relatif media |
| is_active | TINYINT(1) | 1 | Status akun |
| last_login | DATETIME | Ya | Login terakhir |
| reset_token | VARCHAR(255) | Ya | Disimpan hanya bila fitur pemulihan dibuat |
| reset_token_expiry | DATETIME | Ya | Kedaluwarsa token |
| deleted_at | DATETIME | Ya | Soft delete akun |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Index tambahan: `role`, `is_active`.

## 2. pasien

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| user_id | INT UNSIGNED | Ya | UNIQUE, FK `users.id`, ON DELETE SET NULL |
| no_rekam_medis | VARCHAR(20) | Tidak | UNIQUE |
| nik | VARCHAR(16) | Tidak | UNIQUE |
| nama_lengkap | VARCHAR(100) | Tidak | Index pencarian nama |
| jenis_kelamin | ENUM | Tidak | Laki-laki atau Perempuan |
| tempat_lahir | VARCHAR(100) | Tidak | Tempat lahir |
| tanggal_lahir | DATE | Tidak | Tanggal lahir |
| alamat | TEXT | Tidak | Alamat pasien |
| no_telepon | VARCHAR(20) | Tidak | Kontak pasien |
| golongan_darah | ENUM | `-` | A, B, AB, O, atau - |
| deleted_at | DATETIME | Ya | Soft delete profil |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

## 3. poli

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| kode_poli | VARCHAR(10) | Tidak | UNIQUE |
| nama_poli | VARCHAR(100) | Tidak | Nama layanan |
| deskripsi | TEXT | Ya | Deskripsi |
| icon | VARCHAR(50) | `fas fa-stethoscope` | Ikon UI opsional |
| warna | VARCHAR(7) | `#2563EB` | Warna representasi UI |
| is_active | TINYINT(1) | 1 | Status tersedia |
| deleted_at | DATETIME | Ya | Soft delete |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Index tambahan: `is_active`.

## 4. dokter

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| poli_id | INT UNSIGNED | Tidak | FK `poli.id`, ON DELETE RESTRICT |
| kode_dokter | VARCHAR(20) | Tidak | UNIQUE |
| nama_dokter | VARCHAR(100) | Tidak | Nama dokter |
| spesialisasi | VARCHAR(100) | Ya | Spesialisasi |
| no_telepon | VARCHAR(20) | Ya | Kontak |
| email | VARCHAR(100) | Ya | Email |
| foto | VARCHAR(255) | Ya | Path relatif media |
| is_active | TINYINT(1) | 1 | Status praktik |
| deleted_at | DATETIME | Ya | Soft delete |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Unique gabungan `(id, poli_id)` menjaga validasi composite FK jadwal. Index tambahan: `poli_id`, `is_active`.

## 5. jadwal_dokter

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| dokter_id | INT UNSIGNED | Tidak | FK ke dokter |
| poli_id | INT UNSIGNED | Tidak | FK ke poli; pasangan dengan dokter divalidasi FK gabungan |
| hari | ENUM | Tidak | Senin sampai Minggu |
| jam_mulai | TIME | Tidak | Awal praktik |
| jam_selesai | TIME | Tidak | Akhir praktik |
| kuota | INT | 30 | Kapasitas jadwal |
| is_active | TINYINT(1) | 1 | Status jadwal |
| deleted_at | DATETIME | Ya | Soft delete |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Unique `(dokter_id, hari, jam_mulai, jam_selesai)` mencegah jadwal identik. Index tambahan: `dokter_id`, `poli_id`, `hari`. Unique gabungan `(id, dokter_id, poli_id)` menjadi target FK pendaftaran.

## 6. pendaftaran

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| pasien_id | INT UNSIGNED | Tidak | FK `pasien.id`, ON DELETE RESTRICT |
| poli_id | INT UNSIGNED | Tidak | FK `poli.id`, ON DELETE RESTRICT |
| dokter_id | INT UNSIGNED | Tidak | FK `dokter.id`, ON DELETE RESTRICT |
| jadwal_id | INT UNSIGNED | Tidak | FK jadwal; composite FK memastikan poli/dokter cocok |
| tanggal_kunjungan | DATE | Tidak | Tanggal layanan |
| keluhan | TEXT | Ya | Keluhan awal |
| status | ENUM | `pending` | pending, confirmed, cancelled |
| registered_by | INT UNSIGNED | Ya | FK `users.id`, ON DELETE SET NULL |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Unique `(pasien_id, poli_id, tanggal_kunjungan)` mencegah pendaftaran ganda. Index tambahan: pasien, poli, dokter, tanggal, status. Unique key gabungan dipakai antrean untuk memastikan data sumber konsisten.

## 7. antrean

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| pendaftaran_id | INT UNSIGNED | Tidak | UNIQUE, FK ke pendaftaran |
| pasien_id | INT UNSIGNED | Tidak | FK pasien dan bagian composite FK pendaftaran |
| poli_id | INT UNSIGNED | Tidak | FK poli dan bagian unique nomor/urutan |
| dokter_id | INT UNSIGNED | Tidak | FK dokter dan bagian composite FK pendaftaran |
| no_antrean | VARCHAR(10) | Tidak | Nomor tampilan |
| no_urut | INT | Tidak | Urutan numerik per poli/tanggal |
| tanggal_antrean | DATE | Tidak | Harus sama dengan tanggal pendaftaran |
| status | ENUM | `menunggu` | menunggu, dipanggil, dilayani, selesai, dilewati, dibatalkan |
| waktu_dipanggil | DATETIME | Ya | Waktu panggilan |
| waktu_mulai_layanan | DATETIME | Ya | Awal pelayanan |
| waktu_selesai | DATETIME | Ya | Akhir pelayanan |
| alasan_batal | TEXT | Ya | Alasan pembatalan |
| called_by | INT UNSIGNED | Ya | FK user pemanggil, ON DELETE SET NULL |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Unique `(no_antrean, tanggal_antrean, poli_id)` dan `(tanggal_antrean, poli_id, no_urut)` mencegah nomor/urutan ganda. Index tambahan: pasien, poli, dokter, tanggal, status, urutan. Composite FK mengikat antrean ke seluruh identitas dan tanggal pendaftaran.

## 8. kunjungan

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| antrean_id | INT UNSIGNED | Tidak | UNIQUE, FK antrean, ON DELETE RESTRICT |
| petugas_id | INT UNSIGNED | Ya | FK `users.id`, ON DELETE SET NULL |
| diagnosa | VARCHAR(500) | Ya | Catatan diagnosa |
| catatan | TEXT | Ya | Ringkasan pelayanan |
| status | ENUM | `berlangsung` | berlangsung, selesai, dirujuk |
| waktu_mulai | DATETIME | Ya | Awal pelayanan |
| waktu_selesai | DATETIME | Ya | Akhir pelayanan |
| created_at, updated_at | DATETIME | Otomatis | Timestamp |

Index tambahan: `status`, `waktu_mulai`. Unique antrean memastikan maksimal satu kunjungan per antrean.

## 9. notifikasi

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| user_id | INT UNSIGNED | Tidak | FK `users.id`, ON DELETE CASCADE |
| judul | VARCHAR(200) | Tidak | Judul |
| pesan | TEXT | Tidak | Isi |
| tipe | ENUM | `info` | info, success, warning, danger |
| link | VARCHAR(255) | Ya | Path aplikasi internal |
| is_read | TINYINT(1) | 0 | Status dibaca |
| created_at | DATETIME | Saat ini | Waktu dibuat |

Index tambahan: `user_id`, `is_read`, `created_at`.

## 10. audit_logs

| Kolom | Tipe | Null/default | Kunci/keterangan |
| --- | --- | --- | --- |
| id | INT UNSIGNED | Tidak | PK |
| user_id | INT UNSIGNED | Ya | FK `users.id`, ON DELETE SET NULL |
| username | VARCHAR(50) | Tidak | Snapshot identitas pelaku |
| role | VARCHAR(30) | Tidak | Snapshot role saat aksi |
| aksi | VARCHAR(100) | Tidak | Nama tindakan |
| tabel | VARCHAR(50) | Ya | Nama entitas terkait |
| record_id | INT UNSIGNED | Ya | ID record terkait, bukan FK agar histori tetap disimpan |
| data_lama | JSON | Ya | Snapshot sebelum perubahan |
| data_baru | JSON | Ya | Snapshot sesudah perubahan |
| ip_address | VARCHAR(45) | Ya | IPv4/IPv6 |
| user_agent | TEXT | Ya | Informasi klien |
| created_at | DATETIME | Saat ini | Waktu aksi |

Index tambahan: `user_id`, `aksi`, `tabel`, `created_at`.

## Kebijakan Penghapusan

Data master dan akun menggunakan `deleted_at`/`is_active`; penghapusan dilakukan sebagai soft delete. Data transaksi menggunakan `ON DELETE RESTRICT` agar riwayat pendaftaran, antrean, dan kunjungan tidak hilang. Referensi pelaku pada log/aktivitas boleh menjadi NULL sehingga histori tetap tersimpan jika akun dihapus secara fisik di luar alur aplikasi.