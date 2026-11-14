<?php

require_once dirname(__DIR__) . '/config/bootstrap.php';

class FoundationSmokeController
{
    public function index(): void
    {
        echo 'ANTRIAN SEHAT';
    }
}

$checks = [];
$check = static function (string $name, bool $passed) use (&$checks): void {
    $checks[$name] = $passed;
};

$check('environment configuration', DB_NAME === 'antrian_sehat');
$check('autoloading', class_exists('UserModel') && class_exists('NotifikasiModel') && class_exists('Router'));

$pdo = Database::getInstance()->getConnection();
$check('PDO connection', $pdo->query('SELECT 1')->fetchColumn() === 1);
$check('schema tables', (int) $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn() >= 10);
$userModel = new UserModel();
$check('model query', $userModel->count() >= 4);

$seedAccounts = [
    'admin' => 'admin',
    'petugas1' => 'petugas_pendaftaran',
    'petugas2' => 'petugas_pelayanan',
    'pasien1' => 'pasien',
];
$testUsers = [];
foreach ($seedAccounts as $username => $expectedRole) {
    $user = $userModel->authenticate($username, 'password123');
    $check("authentication role {$expectedRole}", $user && $user->role === $expectedRole);
    if ($user) {
        $testUsers[$expectedRole] = $user;
    }
}
$check('wrong password rejected', !$userModel->authenticate('admin', 'incorrect-password'));
$check('SQL injection login rejected', !$userModel->authenticate("' OR 1=1 --", 'password123'));

$adminId = (int) $pdo->query("SELECT id FROM users WHERE username = 'admin'")->fetchColumn();
$pdo->beginTransaction();
try {
    $pdo->prepare('UPDATE users SET is_active = 0 WHERE id = :id')->execute([':id' => $adminId]);
    $check('inactive account rejected', !$userModel->authenticate('admin', 'password123'));
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
}

$existingEmail = (string) $pdo->query("SELECT email FROM users WHERE email IS NOT NULL LIMIT 1")->fetchColumn();
$existingNik = (string) $pdo->query("SELECT nik FROM pasien LIMIT 1")->fetchColumn();
$check('duplicate email detected', $userModel->emailExists($existingEmail));
$check('duplicate NIK detected', (bool) (new PasienModel())->findByNIK($existingNik));
$csrfToken = CsrfMiddleware::generateToken();
$check('CSRF token accepted', CsrfMiddleware::verifyToken($csrfToken));
$check('invalid CSRF token rejected', !CsrfMiddleware::verifyToken('invalid-token'));
$check('XSS output escaped', SecurityHelper::escape('<script>alert(1)</script>') === '&lt;script&gt;alert(1)&lt;/script&gt;');
$displayJson = json_encode((new AntreanModel())->getDisplayData());
$check('public display excludes patient identity', !str_contains($displayJson, 'nama_pasien') && !str_contains($displayJson, 'pasien_id'));
$check('patient search query', is_array((new PasienModel())->search('smoke-search', 1)));
$check('queue filtered search query', is_array((new AntreanModel())->getFiltered(['search' => 'smoke-search'], 1)));
$check('queue count filtered search query', (new AntreanModel())->countFiltered(['search' => 'smoke-search']) >= 0);
$check('registration search query', is_array((new PendaftaranModel())->getList(['search' => 'smoke-search'], 1)));

$dashboardLabels = [
    'admin' => 'Aktivitas Terbaru',
    'petugas_pendaftaran' => 'Antrean Hari Ini',
    'petugas_pelayanan' => 'Sedang Dilayani',
    'pasien' => 'Riwayat Kunjungan Saya',
];
foreach ($dashboardLabels as $role => $expectedLabel) {
    if (!isset($testUsers[$role])) {
        $check("dashboard {$role} renders", false);
        continue;
    }
    AuthMiddleware::setCurrentUser($testUsers[$role]);
    $_SESSION['login_time'] = time();
    $_SESSION['last_activity'] = time();
    ob_start();
    (new DashboardController())->index();
    $html = ob_get_clean();
    $check("dashboard {$role} renders", str_contains($html, $expectedLabel));
}

$_SESSION = [];
$rolledBack = false;
try {
    Database::getInstance()->transaction(static function (PDO $connection): void {
        $connection->query('SELECT 1');
        throw new RuntimeException('rollback smoke test');
    });
} catch (RuntimeException) {
    $rolledBack = !$pdo->inTransaction();
}
$check('transaction rollback', $rolledBack);
$check('session', Session::get('foundation_smoke') === null);
Session::put('foundation_smoke', 'active');
$check('session read/write', Session::get('foundation_smoke') === 'active');
Session::forget('foundation_smoke');

$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/foundation-smoke';
$router = new Router();
$router->get('/foundation-smoke', 'FoundationSmokeController@index');
ob_start();
$router->dispatch();
$html = ob_get_clean();
$check('router/controller/view', str_contains($html, 'ANTRIAN SEHAT'));
$check('authentication middleware state', AuthMiddleware::isAuthenticated() === false);

foreach ($checks as $name => $passed) {
    printf("%s: %s\n", $passed ? 'PASS' : 'FAIL', $name);
}

exit(in_array(false, $checks, true) ? 1 : 0);