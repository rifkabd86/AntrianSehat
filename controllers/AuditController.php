<?php
/**
 * ANTRIAN SEHAT - Audit Log Controller
 * 
 * Hanya dapat diakses oleh Administrator untuk memantau audit trail aktivitas sistem.
 * Bersifat Read-Only (tidak ada fitur edit atau hapus).
 */

class AuditController extends Controller
{
    private AuditLog $auditModel;

    public function __construct()
    {
        AuthMiddleware::requireRole(['admin']);
        $this->auditModel = new AuditLog();
    }

    public function index(): void
    {
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 15;
        
        $filters = [
            'search'    => trim((string)($_GET['search'] ?? '')),
            'username'  => trim((string)($_GET['username'] ?? '')),
            'aksi'      => trim((string)($_GET['aksi'] ?? '')),
            'date_from' => trim((string)($_GET['date_from'] ?? '')),
            'date_to'   => trim((string)($_GET['date_to'] ?? '')),
        ];

        // Validasi format tanggal jika diisi
        if (!empty($filters['date_from']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_from'])) {
            $filters['date_from'] = '';
        }
        if (!empty($filters['date_to']) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $filters['date_to'])) {
            $filters['date_to'] = '';
        }

        $logs = $this->auditModel->getAllPaginated($page, $limit, $filters);
        $total = $this->auditModel->countAll($filters);
        $totalPages = max(1, (int)ceil($total / $limit));
        
        // 4 Kartu Statistik Audit Log aktual dari database
        $stats = $this->auditModel->getStatsSummary();
        
        // List Aksi dan List Pengguna untuk filter dropdown
        $aksiTypes = $this->auditModel->getAksiTypes();
        $userList = $this->auditModel->getUserList();

        $this->view('audit/index', [
            'title'      => 'Audit Log Sistem',
            'layout'     => 'main',
            'logs'       => $logs,
            'stats'      => $stats,
            'total'      => $total,
            'totalPages' => $totalPages,
            'page'       => $page,
            'perPage'    => $limit,
            'filters'    => $filters,
            'aksiTypes'  => $aksiTypes,
            'userList'   => $userList,
        ]);
    }
}
