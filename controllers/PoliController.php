<?php
/**
 * ANTRIAN SEHAT - Poli Controller
 * 
 * Mengelola data poli/poliklinik Puskesmas.
 */

class PoliController extends Controller
{
    private PoliModel $poliModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->poliModel = new PoliModel();
    }

    public function index(): void
    {
        $list = $this->poliModel->getWithStats();

        $this->view('poli/index', [
            'title' => 'Data Poli & Pelayanan',
            'layout' => 'main',
            'list' => $list,
        ]);
    }

    public function create(): void
    {
        AuthMiddleware::requireRole(['admin']);

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            $kode = strtoupper(trim($_POST['kode_poli'] ?? ''));
            $nama = trim($_POST['nama_poli'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $icon = trim($_POST['icon'] ?? 'fas fa-stethoscope');
            $warna = trim($_POST['warna'] ?? '#2563EB');

            if (empty($kode) || empty($nama)) {
                Helper::flash('error', 'Kode poli dan nama poli wajib diisi.', 'error');
                $this->redirect('poli/create');
            }

            if ($this->poliModel->findByKode($kode)) {
                Helper::flash('error', 'Kode poli sudah digunakan.', 'error');
                $this->redirect('poli/create');
            }

            $id = $this->poliModel->create([
                'kode_poli' => $kode,
                'nama_poli' => $nama,
                'deskripsi' => $deskripsi,
                'icon' => $icon,
                'warna' => $warna,
                'is_active' => 1,
            ]);

            AuditLog::log('Tambah Data Poli', 'poli', $id, null, ['kode' => $kode, 'nama' => $nama]);
            Helper::flash('success', "Poli {$nama} berhasil ditambahkan.", 'success');
            $this->redirect('poli');
        }

        $this->view('poli/create', [
            'title' => 'Tambah Poli Baru',
            'layout' => 'main',
        ]);
    }

    public function edit(string $id): void
    {
        AuthMiddleware::requireRole(['admin']);
        $pid = (int)$id;
        $poli = $this->poliModel->findById($pid);
        if (!$poli) {
            Helper::flash('error', 'Data poli tidak ditemukan.', 'error');
            $this->redirect('poli');
        }

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            $nama = trim($_POST['nama_poli'] ?? '');
            $deskripsi = trim($_POST['deskripsi'] ?? '');
            $icon = trim($_POST['icon'] ?? 'fas fa-stethoscope');
            $warna = trim($_POST['warna'] ?? '#2563EB');
            $isActive = isset($_POST['is_active']) ? 1 : 0;

            if (empty($nama)) {
                Helper::flash('error', 'Nama poli wajib diisi.', 'error');
                $this->redirect('poli/edit/' . $pid);
            }

            $this->poliModel->update($pid, [
                'nama_poli' => $nama,
                'deskripsi' => $deskripsi,
                'icon' => $icon,
                'warna' => $warna,
                'is_active' => $isActive,
            ]);

            AuditLog::log('Edit Data Poli', 'poli', $pid, null, ['nama' => $nama]);
            Helper::flash('success', 'Data poli berhasil diperbarui.', 'success');
            $this->redirect('poli');
        }

        $this->view('poli/edit', [
            'title' => 'Edit Data Poli',
            'layout' => 'main',
            'poli' => $poli,
        ]);
    }
}

