<?php

class AuthMiddleware
{
    private const IDLE_TIMEOUT = 1800;

    public function handle(): void
    {
        self::requireAuth();
    }

    public static function isAuthenticated(): bool
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            Session::start();
        }
        return isset($_SESSION['user_id']) && (int) $_SESSION['user_id'] > 0;
    }

    public static function isRole(string|array $roles): bool
    {
        if (!self::isAuthenticated()) {
            return false;
        }

        self::requireAuth();
        $role = $_SESSION['user_role'] ?? '';
        return is_array($roles) ? in_array($role, $roles, true) : $role === $roles;
    }

    public static function requireAuth(): void
    {
        if (!self::isAuthenticated()) {
            self::redirectToLogin('Silakan login terlebih dahulu untuk mengakses halaman ini.');
        }

        $lastActivity = (int) ($_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? time());
        if ($lastActivity > 0 && (time() - $lastActivity) > self::IDLE_TIMEOUT) {
            $sessionUser = $_SESSION['user'] ?? [];
            try {
                AuditLog::logAuthentication(
                    'Session Kedaluwarsa',
                    (string) ($sessionUser['username'] ?? $_SESSION['user_username'] ?? 'unknown'),
                    null,
                    (string) ($_SESSION['user_role'] ?? 'guest')
                );
            } catch (Throwable $e) {}
            self::redirectToLogin('Sesi Anda berakhir karena tidak aktif. Silakan login kembali.');
        }

        $user = (new UserModel())->findById((int) $_SESSION['user_id']);
        if (
            !$user
            || (int) ($user->is_active ?? 0) !== 1
            || !empty($user->deleted_at)
            || !in_array($user->role ?? '', ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'], true)
        ) {
            $sessionUser = $_SESSION['user'] ?? [];
            try {
                AuditLog::logAuthentication(
                    'Akses Ditolak Akun Tidak Aktif',
                    (string) ($sessionUser['username'] ?? $_SESSION['user_username'] ?? 'unknown'),
                    $user ? (int) $user->id : null,
                    (string) ($_SESSION['user_role'] ?? 'guest')
                );
            } catch (Throwable $e) {}
            self::redirectToLogin('Akun Anda tidak aktif. Silakan hubungi administrator.');
        }

        self::setCurrentUser($user);
        $_SESSION['last_activity'] = time();
    }

    public static function requireRole(string|array $roles): void
    {
        self::requireAuth();
        if (!self::isRoleInSession($roles)) {
            http_response_code(403);
            require BASE_PATH . '/views/errors/403.php';
            exit;
        }
    }

    public static function getCurrentUser(): ?array
    {
        if (!self::isAuthenticated()) {
            return null;
        }

        self::requireAuth();
        return $_SESSION['user'] ?? null;
    }

    public static function setCurrentUser(object $user): void
    {
        $userId = (int) $user->id;
        $role = (string) $user->role;
        $name = (string) ($user->nama_lengkap ?? '');
        $username = (string) ($user->username ?? '');
        $email = (string) ($user->email ?? '');

        $_SESSION['user_id'] = $userId;
        $_SESSION['user_role'] = $role;
        $_SESSION['user_nama'] = $name;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_username'] = $username;
        $_SESSION['user'] = [
            'id' => $userId,
            'username' => $username,
            'nama_lengkap' => $name,
            'email' => $email,
            'role' => $role,
        ];
    }

    public static function logout(): void
    {
        Session::destroy();
    }

    public static function isSessionExpired(int $maxLifetime = self::IDLE_TIMEOUT): bool
    {
        $lastActivity = (int) ($_SESSION['last_activity'] ?? $_SESSION['login_time'] ?? 0);
        return !self::isAuthenticated() || $lastActivity === 0 || time() - $lastActivity > $maxLifetime;
    }

    public static function regenerateSession(): void
    {
        // Gunakan false (jangan hapus file lama seketika) agar tidak race-condition di InfinityFree
        if (session_status() === PHP_SESSION_ACTIVE && !headers_sent()) {
            @session_regenerate_id(false);
        }
    }

    private static function isRoleInSession(string|array $roles): bool
    {
        $role = $_SESSION['user_role'] ?? '';
        return is_array($roles) ? in_array($role, $roles, true) : $role === $roles;
    }

    private static function redirectToLogin(string $message): never
    {
        Session::destroy();
        Session::start();
        Helper::flash('error', $message, 'warning');
        if (session_status() === PHP_SESSION_ACTIVE) {
            @session_write_close();
        }
        $target = rtrim(BASE_URL, '/') . '/login';
        if (!headers_sent()) {
            header('Location: ' . $target);
        } else {
            echo '<script>window.location.href=' . json_encode($target) . ';</script>';
        }
        exit;
    }
}