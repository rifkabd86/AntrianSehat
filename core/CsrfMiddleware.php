<?php
/**
 * ANTRIAN SEHAT - CSRF Middleware
 * 
 * Middleware untuk proteksi Cross-Site Request Forgery (CSRF).
 * Menghasilkan dan memvalidasi CSRF token pada semua form POST.
 */

class CsrfMiddleware
{
    public function handle(): void
    {
        self::validateRequest();
    }

    /**
     * Nama session key untuk CSRF token
     */
    private const TOKEN_KEY = '_csrf_token';

    /**
     * Nama field form untuk CSRF token
     */
    private const FIELD_NAME = 'csrf_token';

    /**
     * Nama cookie fallback untuk hosting (InfinityFree)
     */
    private const COOKIE_NAME = 'as_csrf_token';

    /**
     * Pastikan session sudah berjalan
     */
    private static function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            if (class_exists('Session')) {
                Session::start();
            } else {
                @session_start();
            }
        }
    }

    /**
     * Generate CSRF token baru dan simpan di session + cookie fallback
     * 
     * @return string Token yang dihasilkan
     */
    public static function generateToken(): string
    {
        self::ensureSession();

        $token = bin2hex(random_bytes(32));

        // Simpan token sebelumnya untuk grace period
        if (!empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY . '_prev'] = $_SESSION[self::TOKEN_KEY];
            $_SESSION[self::TOKEN_KEY . '_prev_time'] = time();
        }

        // Sinkronisasi semua kemungkinan session key di aplikasi
        $_SESSION[self::TOKEN_KEY] = $token;
        $_SESSION[self::FIELD_NAME] = $token;
        $_SESSION['csrf_token'] = $token;
        $_SESSION[self::TOKEN_KEY . '_time'] = time();

        // Simpan juga di cookie sebagai fallback jika session file di hosting terlambat ditulis
        if (PHP_SAPI !== 'cli' && !headers_sent()) {
            @setcookie(self::COOKIE_NAME, $token, [
                'expires'  => time() + 7200,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $_COOKIE[self::COOKIE_NAME] = $token;
        }

        return $token;
    }

    /**
     * Ambil token yang ada di session, atau buat baru jika belum ada
     * 
     * @return string
     */
    public static function getToken(): string
    {
        self::ensureSession();

        $existingToken = $_SESSION[self::TOKEN_KEY]
            ?? $_SESSION[self::FIELD_NAME]
            ?? $_SESSION['csrf_token']
            ?? $_COOKIE[self::COOKIE_NAME]
            ?? '';

        if (empty($existingToken) || !is_string($existingToken)) {
            return self::generateToken();
        }

        // Pastikan semua key sinkron
        $_SESSION[self::TOKEN_KEY] = $existingToken;
        $_SESSION[self::FIELD_NAME] = $existingToken;
        $_SESSION['csrf_token'] = $existingToken;
        if (empty($_SESSION[self::TOKEN_KEY . '_time'])) {
            $_SESSION[self::TOKEN_KEY . '_time'] = time();
        }

        if (PHP_SAPI !== 'cli' && !headers_sent() && empty($_COOKIE[self::COOKIE_NAME])) {
            @setcookie(self::COOKIE_NAME, $existingToken, [
                'expires'  => time() + 7200,
                'path'     => '/',
                'secure'   => false,
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            $_COOKIE[self::COOKIE_NAME] = $existingToken;
        }

        return $existingToken;
    }

    /**
     * Generate hidden input HTML field berisi CSRF token
     * 
     * @return string HTML hidden input
     */
    public static function getTokenField(): string
    {
        $token = self::getToken();
        return '<input type="hidden" name="' . self::FIELD_NAME . '" value="' . htmlspecialchars($token, ENT_QUOTES, 'UTF-8') . '">';
    }

    /**
     * Alias untuk getTokenField()
     */
    public static function field(): string
    {
        return self::getTokenField();
    }

    /**
     * Verifikasi apakah token yang diberikan cocok dengan token di session atau cookie
     * 
     * @param string $token Token yang akan diverifikasi
     * @return bool
     */
    public static function verifyToken(string $token): bool
    {
        self::ensureSession();

        if (empty($token) || !is_string($token)) {
            return false;
        }

        $candidates = [
            $_SESSION[self::TOKEN_KEY] ?? null,
            $_SESSION[self::FIELD_NAME] ?? null,
            $_SESSION['csrf_token'] ?? null,
            $_SESSION[self::TOKEN_KEY . '_prev'] ?? null,
            $_COOKIE[self::COOKIE_NAME] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!empty($candidate) && is_string($candidate) && hash_equals($candidate, $token)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validasi CSRF token dari request POST
     * 
     * @return void
     */
    public static function validateRequest(): void
    {
        self::ensureSession();

        // 🚨 Matikan sementara validasi CSRF khusus untuk jalur halaman login
        $uri = $_SERVER['REQUEST_URI'] ?? '';
        if (str_contains($uri, '/login')) {
            return;
        }

        // Cegah validasi ganda dalam 1 siklus request yang sama
        if (!empty($GLOBALS['__csrf_validated'])) {
            return;
        }

        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if (!in_array($method, ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            return;
        }

        // Ambil token dari berbagai sumber (POST, HTTP Header, JSON Body)
        $token = $_POST[self::FIELD_NAME]
            ?? $_POST['_csrf_token']
            ?? $_POST['_token']
            ?? '';

        if (empty($token) && !empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_CSRF_TOKEN'];
        }
        if (empty($token) && !empty($_SERVER['HTTP_X_XSRF_TOKEN'])) {
            $token = $_SERVER['HTTP_X_XSRF_TOKEN'];
        }

        if (empty($token)) {
            $rawInput = file_get_contents('php://input');
            if (!empty($rawInput)) {
                $jsonData = json_decode($rawInput, true);
                if (is_array($jsonData)) {
                    $token = $jsonData[self::FIELD_NAME]
                        ?? $jsonData['_csrf_token']
                        ?? $jsonData['_token']
                        ?? '';
                }
            }
        }

        if (!is_string($token) || $token === '' || !self::verifyToken($token)) {
            http_response_code(403);

            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'message' => 'Token keamanan tidak valid atau telah kadaluarsa. Silakan ulangi pengisian formulir.'
                ];
            }

            self::generateToken();
            
            // Redirect aman menggunakan BASE_URL dengan penanganan JSON yang bersih
            $targetLogin = rtrim(BASE_URL, '/') . '/login';
            if (!headers_sent()) {
                header('Location: ' . $targetLogin);
            } else {
                $safeUrlJson = json_encode($targetLogin, JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
                echo '<script>window.location.href = ' . $safeUrlJson . ';</script>';
            }
            exit;
        }

        // Tandai bahwa request ini sudah lolos validasi CSRF
        $GLOBALS['__csrf_validated'] = true;
    }
}