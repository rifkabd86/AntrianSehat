<?php
/**
 * ANTRIAN SEHAT - Setup & Environment Diagnostic Script
 * 
 * Script pemeriksaan kesiapan lingkungan aplikasi ANTRIAN SEHAT:
 * 1. Memeriksa versi PHP (>= 8.1) dan ekstensi wajib (PDO, pdo_mysql, mbstring, session, json)
 * 2. Menguji koneksi database MySQL (antrian_sehat)
 * 3. Memverifikasi ketersediaan 10 tabel utama sistem antrean puskesmas
 */

require_once __DIR__ . '/config.php';
require_once BASE_PATH . '/core/Database.php';

$checks = [
    'PHP Version >= 8.1 (' . PHP_VERSION . ')' => version_compare(PHP_VERSION, '8.1.0', '>='),
    'Ekstensi PDO & PDO MySQL' => extension_loaded('pdo') && extension_loaded('pdo_mysql'),
    'Ekstensi MBString' => extension_loaded('mbstring'),
    'Ekstensi Session & JSON' => extension_loaded('session') && extension_loaded('json'),
    'File Schema SQL Tersedia' => is_file(BASE_PATH . '/database/antrian_sehat.sql'),
];

$dbConnected = false;
$tablesCount = 0;
$dbError = '';

try {
    $pdo = Database::getInstance()->getConnection();
    $dbConnected = true;
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tablesCount = count($tables);
} catch (Throwable $e) {
    $dbError = $e->getMessage();
}

$checks['Koneksi Database MySQL (' . DB_NAME . ')'] = $dbConnected;
$checks['Tabel Database Lengkap (' . $tablesCount . ' tabel)'] = $dbConnected && $tablesCount >= 10;

if (PHP_SAPI === 'cli') {
    echo "=== ANTRIAN SEHAT - DIAGNOSTIK SETUP ===\n";
    foreach ($checks as $label => $ok) {
        echo ($ok ? "[OK]   " : "[FAIL] ") . $label . "\n";
    }
    if ($dbError !== '') {
        echo "Error DB: {$dbError}\n";
    }
    exit(in_array(false, $checks, true) ? 1 : 0);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup & Diagnostik Sistem - <?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 680px;">
        <div class="card border-0 shadow-sm rounded-4 p-4">
            <h4 class="fw-bold text-primary mb-1"><?= htmlspecialchars(APP_NAME, ENT_QUOTES, 'UTF-8') ?></h4>
            <p class="text-muted small mb-4">Diagnostik Kesiapan Lingkungan & Basis Data Puskesmas</p>
            <ul class="list-group list-group-flush mb-4">
                <?php foreach ($checks as $label => $ok): ?>
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                        <span><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                        <span class="badge rounded-pill <?= $ok ? 'bg-success' : 'bg-danger' ?>">
                            <?= $ok ? 'Siap' : 'Perlu Perhatian' ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="text-end">
                <a href="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>/login" class="btn btn-primary rounded-pill px-4">Buka Aplikasi</a>
            </div>
        </div>
    </div>
</body>
</html>
