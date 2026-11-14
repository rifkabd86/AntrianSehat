<?php
/**
 * ANTRIAN SEHAT - Konfigurasi Aplikasi
 * 
 * File ini memuat konfigurasi utama aplikasi termasuk
 * koneksi database, timezone, dan pengaturan error reporting.
 */

// Load .env file if exists
$envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        // Skip comments
        if (strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);
            // Remove surrounding quotes
            $value = trim($value, '"\'');
            if (getenv($key) === false) {
                if (!array_key_exists($key, $_ENV)) {
                    $_ENV[$key] = $value;
                }
                if (function_exists('putenv')) {
                    @putenv($key . '=' . $_ENV[$key]);
                }
            }
        }
    }
}

/**
 * Helper function to get environment variable with default fallback
 */
function env(string $key, $default = null)
{
    $value = getenv($key);
    if ($value === false) {
        $value = $_ENV[$key] ?? $default;
        if ($value === $default) {
            return $default;
        }
    }
    // Cast boolean-like strings
    if (is_string($value)) {
        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;
            case 'false':
            case '(false)':
                return false;
            case 'null':
            case '(null)':
                return null;
            case 'empty':
            case '(empty)':
                return '';
        }
    }
    return $value;
}

// ============================================================
// Auto-detect BASE_URL
// ============================================================
$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https'))
    ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'antrian-sehat.rf.gd';
$rawScript = $_SERVER['SCRIPT_NAME'] ?? '';
$scriptDir = $rawScript !== '' ? str_replace('\\', '/', dirname($rawScript)) : '';
if ($scriptDir === '.' || $scriptDir === '/') {
    $scriptDir = '';
}
$baseDir = rtrim(preg_replace('#/public$#', '', $scriptDir), '/');
$autoDetectedUrl = $protocol . '://' . $host . ($baseDir !== '' ? $baseDir : '');

// ============================================================
// Application Constants
// ============================================================
define('APP_NAME', 'ANTRIAN SEHAT');
define('APP_VERSION', '1.0.0');
define('APP_DEBUG', env('APP_DEBUG', false));
// Gunakan autoDetectedUrl bila APP_URL kosong atau tidak sesuai protokol request aktif
$configuredAppUrl = env('APP_URL');
define('BASE_URL', rtrim(!empty($configuredAppUrl) ? $configuredAppUrl : $autoDetectedUrl, '/'));
define('BASE_PATH', dirname(__DIR__));

// ============================================================
// Database Constants (Fallback langsung ke InfinityFree)
// ============================================================
define('DB_HOST', env('DB_HOST', 'sql303.infinityfree.com'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'if0_43381174_db_antriansehat'));
define('DB_USER', env('DB_USER', 'if0_43381174'));
define('DB_PASS', env('DB_PASS', 'JAFrPgN5pMnZx'));

// ============================================================
// Timezone
// ============================================================
date_default_timezone_set('Asia/Jakarta');

// ============================================================
// Error Reporting
// ============================================================
if (APP_DEBUG) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(0);
    // Log errors to file in production
    ini_set('log_errors', 1);
    ini_set('error_log', BASE_PATH . '/storage/logs/error.log');
}