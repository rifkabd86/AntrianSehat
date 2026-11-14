<?php

class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        @ini_set('session.use_strict_mode', '0');
        @ini_set('session.use_only_cookies', '1');
        @ini_set('session.use_trans_sid', '0');

        // Deteksi direktori sesi yang dapat ditulis (writable)
        $sessionDir = defined('BASE_PATH') ? BASE_PATH . '/storage/sessions' : dirname(__DIR__) . '/storage/sessions';
        if (!is_dir($sessionDir)) {
            @mkdir($sessionDir, 0777, true);
        }

        if (is_dir($sessionDir) && is_writable($sessionDir)) {
            @session_save_path($sessionDir);
        } else {
            $tempDir = sys_get_temp_dir();
            if (@is_dir($tempDir) && @is_writable($tempDir)) {
                @session_save_path($tempDir);
            }
        }

        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            $isSecure = (!empty($_SERVER['HTTPS']) && strtolower($_SERVER['HTTPS']) !== 'off' && $_SERVER['HTTPS'] !== '0')
                || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443)
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https');

            // Gunakan SameSite None jika HTTPS, atau Lax jika HTTP, domain kosong agar match current host
            session_set_cookie_params([
                'lifetime' => 0,
                'path'     => '/',
                'domain'   => '',
                'secure'   => $isSecure,
                'httponly' => true,
                'samesite' => $isSecure ? 'None' : 'Lax',
            ]);
        }

        @session_start();
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public static function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public static function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    public static function destroy(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
        }
    }
}