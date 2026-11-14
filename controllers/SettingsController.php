<?php
/**
 * ANTRIAN SEHAT - Settings Controller
 * 
 * Mengelola konfigurasi umum aplikasi, identitas Puskesmas,
 * pengaturan operasional antrean, dan display monitor TV.
 */

class SettingsController extends Controller
{
    private PengaturanModel $pengaturanModel;

    public function __construct()
    {
        AuthMiddleware::requireRole('admin');
        $this->pengaturanModel = new PengaturanModel();
    }

    public function index(): void
    {
        $settings = $this->pengaturanModel->getAllKeyValue();

        $this->view('settings/index', [
            'title' => 'Pengaturan Aplikasi',
            'layout' => 'main',
            'settings' => $settings,
        ]);
    }

    public function update(): void
    {
        CsrfMiddleware::validateRequest();

        $allowedKeys = [
            'nama_puskesmas',
            'alamat_puskesmas',
            'telepon_puskesmas',
            'email_puskesmas',
            'kepala_puskesmas',
            'nip_kepala',
            'running_text_display',
            'audio_chime_aktif',
            'kuota_harian_default',
        ];

        $payload = [];
        foreach ($allowedKeys as $k) {
            if (isset($_POST[$k])) {
                $payload[$k] = trim((string)$_POST[$k]);
            }
        }

        if (empty($payload['nama_puskesmas'])) {
            Helper::flash('error', 'Nama Puskesmas tidak boleh kosong.', 'error');
            $this->redirect('settings');
        }

        $this->pengaturanModel->updateBatch($payload);

        AuditLog::log('Perubahan Pengaturan Aplikasi', 'pengaturan', null, null, $payload);
        Helper::flash('success', 'Pengaturan aplikasi berhasil disimpan.', 'success');
        $this->redirect('settings');
    }
}

