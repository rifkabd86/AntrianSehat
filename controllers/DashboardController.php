<?php
/**
 * ANTRIAN SEHAT - Dashboard Controller
 * 
 * Mengelola tampilan dashboard dinamis untuk seluruh role:
 * Administrator, Petugas Pendaftaran, Petugas Pelayanan, dan Pasien.
 */

class DashboardController extends Controller
{
    private AntreanModel $antreanModel;
    private PasienModel $pasienModel;
    private PoliModel $poliModel;

    public function __construct()
    {
        AuthMiddleware::requireAuth();
        $this->antreanModel = new AntreanModel();
        $this->pasienModel = new PasienModel();
        $this->poliModel = new PoliModel();
    }

    public function index(): void
    {
        $role = $_SESSION['user_role'] ?? 'admin';
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        $today = date('Y-m-d');
        
        $stats = [];
        $antreanSaatIni = [];
        $chartData = ['labels' => [], 'data' => []];
        $chartKunjunganPeriods = ['daily' => ['labels' => [], 'data' => []], 'weekly' => ['labels' => [], 'data' => []], 'monthly' => ['labels' => [], 'data' => []]];
        $chartDistribution = ['labels' => [], 'data' => [], 'colors' => []];
        $chartPoli = ['labels' => [], 'data' => [], 'colors' => []];
        $poliList = [];
        $myQueue = null;
        $myHistory = [];
        $recentRegistrations = [];
        $recentActivity = [];
        $patientProfile = null;
        $totalPasien = 0;
        $totalPengguna = 0;
        $totalPetugas = 0;
        $pendaftaranHariIni = 0;
        $pasienBaruHariIni = 0;

        $userModel = new UserModel();

        if ($role === 'pasien') {
            $patientProfile = $this->pasienModel->findByUserId($userId);
            if ($patientProfile) {
                $myHistory = $this->pasienModel->getRiwayatKunjungan((int) $patientProfile->id);
                foreach ($myHistory as $h) {
                    $tgl = $h->tanggal_antrean;
                    $st = $h->status;
                    if ($tgl === date('Y-m-d') && in_array($st, ['menunggu', 'dipanggil', 'dilayani'])) {
                        $myQueue = $h;
                        break;
                    }
                }
            }
            $poliList = $this->poliModel->getWithStats($today);
            $stats = $this->antreanModel->getTodaySummary($today);
        } else {
            $stats = $this->antreanModel->getTodaySummary($today);
            $antreanSaatIni = $this->antreanModel->getActiveList($today, null, 10);
            
            // Grafik
            $chartData = $this->antreanModel->getChartStats7Hari();
            $chartKunjunganPeriods = $this->antreanModel->getChartKunjunganPeriods();
            $chartDistribution = $this->antreanModel->getChartDistribution($today);
            $chartPoli = $this->antreanModel->getChartPoliVisits($today);

            // Statistik Utama (Wajib dari Database aktual)
            $totalPengguna = $userModel->countByRole();
            $totalPasien = $this->pasienModel->countSearch();
            $totalPetugas = $userModel->countByRole('admin')
                + $userModel->countByRole('petugas_pendaftaran')
                + $userModel->countByRole('petugas_pelayanan');

            $poliList = $this->poliModel->getWithStats($today);

            $pendaftaranModel = new PendaftaranModel();
            $pendaftaranHariIni = $pendaftaranModel->countForDate($today);
            $recentRegistrations = $pendaftaranModel->getList(['tanggal' => $today], 8, 0);
            $pasienBaruHariIni = $this->pasienModel->countCreatedBetween(
                $today . ' 00:00:00',
                date('Y-m-d', strtotime($today . ' +1 day')) . ' 00:00:00'
            );

            $recentActivity = (new AuditLog())->getRecent(8);
        }

        $this->view('dashboard/index', [
            'title' => 'Dashboard',
            'layout' => 'main',
            'role' => $role,
            'stats' => $stats,
            'antreanSaatIni' => $antreanSaatIni,
            'chartData' => $chartData,
            'chartKunjunganPeriods' => $chartKunjunganPeriods,
            'chartDistribution' => $chartDistribution,
            'chartPoli' => $chartPoli,
            'poliList' => $poliList,
            'totalPasien' => $totalPasien,
            'myQueue' => $myQueue,
            'myHistory' => $myHistory,
            'patientProfile' => $patientProfile,
            'totalPengguna' => $totalPengguna,
            'totalPetugas' => $totalPetugas,
            'pendaftaranHariIni' => $pendaftaranHariIni,
            'pasienBaruHariIni' => $pasienBaruHariIni,
            'recentRegistrations' => $recentRegistrations,
            'recentActivity' => $recentActivity,
        ]);
    }
}
