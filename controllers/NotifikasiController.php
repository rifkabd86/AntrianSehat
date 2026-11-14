<?php
/**
 * ANTRIAN SEHAT - Notifikasi Controller
 * 
 * Mengelola notifikasi internal untuk pengguna yang sedang login.
 */

class NotifikasiController extends Controller
{
    private NotifikasiModel $notifModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->notifModel = new NotifikasiModel();
    }

    public function index(): void
    {
        $userId = $_SESSION['user_id'];
        $list = $this->notifModel->getByUser($userId, 30);

        // Tandai semua sebagai dibaca saat membuka halaman
        $this->notifModel->markAllAsRead($userId);

        $this->view('notifikasi/index', [
            'title' => 'Notifikasi',
            'layout' => 'main',
            'list' => $list,
        ]);
    }
}

