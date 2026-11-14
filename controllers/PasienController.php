<?php
/**
 * ANTRIAN SEHAT - Pasien Controller
 * 
 * Mengelola CRUD data pasien, pencarian data medis, dan detail riwayat kunjungan.
 */

class PasienController extends Controller
{
    private PasienModel $pasienModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->pasienModel = new PasienModel();
    }

    /**
     * Daftar Seluruh Pasien
     */
    public function index(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran']);
        $search = trim($_GET['search'] ?? '');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $list = $this->pasienModel->search($search, $limit, $offset);
        $total = $this->pasienModel->countSearch($search);
        $totalPages = max(1, ceil($total / $limit));

        $this->view('pasien/index', [
            'title' => 'Data Pasien',
            'layout' => 'main',
            'list' => $list,
            'search' => $search,
            'total' => $total,
            'totalPages' => $totalPages,
            'page' => $page,
        ]);
    }

    /**
     * Detail Pasien & Riwayat Kunjungan (Sesuai Mockup UI)
     */
    public function detail(string $id): void
    {
        $role = $_SESSION['user_role'] ?? '';
        if (!in_array($role, ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'], true)) {
            Helper::flash('error', 'Akses tidak diizinkan.', 'error');
            $this->redirect('dashboard');
        }

        $pasien = $this->pasienModel->findById((int)$id);
        if (!$pasien) {
            Helper::flash('error', 'Data pasien tidak ditemukan.', 'error');
            $this->redirect('pasien');
        }

        // Jika role pasien, pastikan hanya dapat melihat datanya sendiri
        if ($role === 'pasien' && isset($pasien->user_id) && (int)$pasien->user_id !== (int)($_SESSION['user_id'] ?? 0)) {
            Helper::flash('error', 'Anda hanya dapat melihat data profil Anda sendiri.', 'error');
            $this->redirect('dashboard');
        }

        $riwayat = $this->pasienModel->getRiwayatKunjungan((int)$id);

        $this->view('pasien/detail', [
            'title' => 'Detail Pasien',
            'layout' => 'main',
            'pasien' => $pasien,
            'riwayat' => $riwayat,
        ]);
    }

    /**
     * Form Tambah Pasien Baru
     */
    public function create(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran']);

        $this->view('pasien/create', [
            'title' => 'Tambah Pasien Baru',
            'layout' => 'main',
        ]);
    }

    /**
     * Simpan Pasien Baru
     */
    public function store(): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran']);
        CsrfMiddleware::validateRequest();

        $nama = trim($_POST['nama_lengkap'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $tglLahir = trim($_POST['tanggal_lahir'] ?? '');
        $gender = trim($_POST['jenis_kelamin'] ?? '');
        $telp = trim($_POST['no_telepon'] ?? '');

        if (empty($nama) || empty($nik) || empty($tglLahir) || empty($gender) || empty($telp)) {
            Helper::flash('error', 'Semua kolom bertanda bintang (*) wajib diisi.', 'error');
            Helper::setOldInput($_POST);
            $this->redirect('pasien/create');
        }

        if ($this->pasienModel->findByNIK($nik)) {
            Helper::flash('error', 'NIK sudah terdaftar dalam sistem.', 'error');
            Helper::setOldInput($_POST);
            $this->redirect('pasien/create');
        }

        $noRM = $this->pasienModel->generateUniqueNoRM();
        $id = $this->pasienModel->create([
            'no_rekam_medis' => $noRM,
            'nik' => $nik,
            'nama_lengkap' => $nama,
            'jenis_kelamin' => $gender,
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? '-'),
            'tanggal_lahir' => $tglLahir,
            'alamat' => trim($_POST['alamat'] ?? '-'),
            'no_telepon' => $telp,
            'golongan_darah' => $_POST['golongan_darah'] ?? '-',
        ]);

        AuditLog::log('Tambah Pasien Baru', 'pasien', $id, null, ['no_rm' => $noRM, 'nama' => $nama]);
        Helper::flash('success', "Pasien {$nama} berhasil ditambahkan dengan No. RM: {$noRM}", 'success');
        $this->redirect('pasien/detail/' . $id);
    }

    /**
     * Form Edit Pasien
     */
    public function edit(string $id): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran']);

        $pasien = $this->pasienModel->findById((int)$id);
        if (!$pasien) {
            Helper::flash('error', 'Data pasien tidak ditemukan.', 'error');
            $this->redirect('pasien');
        }

        $this->view('pasien/edit', [
            'title' => 'Edit Data Pasien',
            'layout' => 'main',
            'pasien' => $pasien,
        ]);
    }

    /**
     * Update Data Pasien
     */
    public function update(string $id): void
    {
        AuthMiddleware::requireRole(['admin', 'petugas_pendaftaran']);
        CsrfMiddleware::validateRequest();

        $pid = (int)$id;
        $pasien = $this->pasienModel->findById($pid);
        if (!$pasien) {
            Helper::flash('error', 'Data pasien tidak ditemukan.', 'error');
            $this->redirect('pasien');
        }

        $nama = trim($_POST['nama_lengkap'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $tglLahir = trim($_POST['tanggal_lahir'] ?? '');
        $gender = trim($_POST['jenis_kelamin'] ?? '');
        $telp = trim($_POST['no_telepon'] ?? '');

        if (empty($nama) || empty($nik) || empty($tglLahir) || empty($gender) || empty($telp)) {
            Helper::flash('error', 'Semua kolom bertanda bintang (*) wajib diisi.', 'error');
            $this->redirect('pasien/edit/' . $pid);
        }

        // Cek jika NIK diganti dan sudah milik orang lain
        $existing = $this->pasienModel->findByNIK($nik);
        if ($existing) {
            $exId = is_object($existing) ? $existing->id : $existing['id'];
            if ($exId != $pid) {
                Helper::flash('error', 'NIK sudah terdaftar untuk pasien lain.', 'error');
                $this->redirect('pasien/edit/' . $pid);
            }
        }

        $this->pasienModel->update($pid, [
            'nik' => $nik,
            'nama_lengkap' => $nama,
            'jenis_kelamin' => $gender,
            'tempat_lahir' => trim($_POST['tempat_lahir'] ?? '-'),
            'tanggal_lahir' => $tglLahir,
            'alamat' => trim($_POST['alamat'] ?? '-'),
            'no_telepon' => $telp,
            'golongan_darah' => $_POST['golongan_darah'] ?? '-',
        ]);

        AuditLog::log('Edit Data Pasien', 'pasien', $pid, null, ['nama' => $nama]);
        Helper::flash('success', 'Data pasien berhasil diperbarui.', 'success');
        $this->redirect('pasien/detail/' . $pid);
    }

    /**
     * Hapus Pasien (Sesuai Aturan Integritas Data)
     */
    public function delete(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        CsrfMiddleware::validateRequest();

        $pid = (int)$id;
        $pasien = $this->pasienModel->findById($pid);
        if (!$pasien) {
            Helper::flash('error', 'Data pasien tidak ditemukan.', 'error');
            $this->redirect('pasien');
        }

        // Cek apakah pasien punya relasi antrean atau kunjungan
        $riwayat = $this->pasienModel->getRiwayatKunjungan($pid);
        if (!empty($riwayat)) {
            Helper::flash('error', 'Pasien memiliki riwayat antrean/kunjungan, tidak dapat dihapus demi integritas data rekam medis.', 'error');
            $this->redirect('pasien/detail/' . $pid);
        }

        $nama = is_object($pasien) ? $pasien->nama_lengkap : $pasien['nama_lengkap'];
        $this->pasienModel->delete($pid);

        AuditLog::log('Hapus Data Pasien', 'pasien', $pid, ['nama' => $nama], null);
        Helper::flash('success', "Data pasien {$nama} telah dihapus.", 'info');
        $this->redirect('pasien');
    }
}
