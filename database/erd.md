# ERD ANTRIAN SEHAT

ERD berikut sesuai dengan skema `database/antrian_sehat.sql`. Data master memakai soft delete; record transaksi mempertahankan foreign key dan tidak dihapus permanen.

```mermaid
erDiagram
    users ||--o| pasien : memiliki_akun
    poli ||--o{ dokter : menaungi
    dokter ||--o{ jadwal_dokter : memiliki
    poli ||--o{ jadwal_dokter : menyediakan
    pasien ||--o{ pendaftaran : membuat
    poli ||--o{ pendaftaran : dipilih
    dokter ||--o{ pendaftaran : melayani
    jadwal_dokter ||--o{ pendaftaran : menjadi_jadwal
    users o|--o{ pendaftaran : mendaftarkan
    pendaftaran ||--o| antrean : menghasilkan
    users o|--o{ antrean : memanggil
    antrean ||--o| kunjungan : dilayani
    users o|--o{ kunjungan : melayani
    users ||--o{ notifikasi : menerima
    users o|--o{ audit_logs : melakukan

    users {
        int id PK
        varchar username UK
        varchar password
        enum role
        datetime deleted_at
    }
    pasien {
        int id PK
        int user_id FK_UK
        varchar no_rekam_medis UK
        varchar nik UK
        varchar nama_lengkap
        datetime deleted_at
    }
    poli {
        int id PK
        varchar kode_poli UK
        varchar nama_poli
        datetime deleted_at
    }
    dokter {
        int id PK
        int poli_id FK
        varchar kode_dokter UK
        varchar nama_dokter
        datetime deleted_at
    }
    jadwal_dokter {
        int id PK
        int dokter_id FK
        int poli_id FK
        enum hari
        time jam_mulai
        time jam_selesai
        int kuota
        datetime deleted_at
    }
    pendaftaran {
        int id PK
        int pasien_id FK
        int poli_id FK
        int dokter_id FK
        int jadwal_id FK
        date tanggal_kunjungan
        enum status
    }
    antrean {
        int id PK
        int pendaftaran_id FK_UK
        int pasien_id FK
        int poli_id FK
        int dokter_id FK
        varchar no_antrean
        int no_urut
        date tanggal_antrean
        enum status
    }
    kunjungan {
        int id PK
        int antrean_id FK_UK
        int petugas_id FK
        varchar diagnosa
        enum status
        datetime waktu_mulai
        datetime waktu_selesai
    }
    notifikasi {
        int id PK
        int user_id FK
        varchar judul
        boolean is_read
    }
    audit_logs {
        int id PK
        int user_id FK
        varchar username
        varchar aksi
        json data_lama
        json data_baru
    }
```

## Kardinalitas dan Aturan

- `users` ke `pasien` adalah 1 ke 0..1 melalui `pasien.user_id` yang nullable dan unik. Pasien tanpa akun tetap diperbolehkan.
- Satu `poli` menaungi banyak dokter dan satu dokter memiliki banyak jadwal. Composite foreign key memastikan pasangan dokter-poli pada jadwal benar.
- Pendaftaran menunjuk tepat satu pasien, poli, dokter, dan jadwal. Composite foreign key memastikan poli/dokter sama dengan jadwal yang dipilih.
- Satu pendaftaran menghasilkan paling banyak satu antrean. Foreign key gabungan pada antrean memastikan pasien, poli, dokter, dan tanggal sama dengan pendaftaran sumber.
- Satu antrean memiliki paling banyak satu catatan kunjungan. Kunjungan wajib merujuk antrean yang ada.
- User yang menghapus akun atau dinonaktifkan tidak menghapus riwayat pendaftaran, antrean, kunjungan, maupun audit.
- Unique key mencegah duplikasi username/email/kode master, pasien-poli-tanggal, antrean per pendaftaran, nomor/urutan per poli-tanggal, dan kunjungan per antrean.

## Alokasi Nomor Antrean

Pembuatan pendaftaran dan antrean harus berjalan dalam satu transaksi PDO. Kunci baris `poli` dengan `SELECT id FROM poli WHERE id = :poli_id FOR UPDATE` sebelum membaca `MAX(no_urut)` untuk pasangan poli/tanggal. Kunci ini menserialisasi request yang membuat antrean pada poli dan tanggal yang sama, termasuk request dari jadwal dokter berbeda. Setelah memastikan jadwal aktif dan kuota belum penuh, insert pendaftaran, hitung nomor berikutnya, insert antrean, lalu commit. Jika salah satu langkah gagal, rollback. Unique key antrean tetap menjadi pertahanan terakhir terhadap race condition.

Implementasi modul pendaftaran belum termasuk Tahap 1-3; `Database::transaction()` menyediakan primitive transaksi untuk modul tersebut.