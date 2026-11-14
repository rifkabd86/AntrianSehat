<?php
/**
 * ANTRIAN SEHAT - Auth Controller
 * 
 * Mengelola autentikasi pengguna: Login, Logout, Registrasi Pasien,
 * Lupa Password, Ubah Password, dan Profil Pengguna.
 */

class AuthController extends Controller
{
    private UserModel $userModel;
    private PasienModel $pasienModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->pasienModel = new PasienModel();
    }

    /**
     * Halaman Login
     */
    public function login(): void
    {
        if (AuthMiddleware::isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();

            $usernameInput = $_POST['username'] ?? '';
            $passwordInput = $_POST['password'] ?? '';
            $username = is_string($usernameInput) ? trim($usernameInput) : '';
            $password = is_string($passwordInput) ? $passwordInput : '';
            $ipAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');

            // Proteksi rate-limiting login: batas 20 percobaan per 15 menit agar tidak memblokir pengguna sah di hosting
            try {
                if (AuditLog::countRecentLoginFailures($ipAddress, 15) >= 20) {
                    Helper::flash('error', 'Terlalu banyak percobaan masuk yang gagal. Silakan coba lagi dalam beberapa menit.', 'warning');
                    Helper::setOldInput(['username' => $username]);
                    $this->redirect('login');
                }
            } catch (Throwable $e) {
                // Abaikan jika tabel audit log belum siap
            }

            if (mb_strlen($username) > 100 || strlen($password) > 72) {
                try {
                    AuditLog::logAuthentication('Login Gagal', $username);
                } catch (Throwable $e) {}
                Helper::flash('error', 'Username/email atau password tidak valid, atau akun tidak aktif.', 'error');
                Helper::setOldInput(['username' => mb_substr($username, 0, 100)]);
                $this->redirect('login');
            }

            $user = $this->userModel->authenticate($username, $password);
            if (!$user) {
                try {
                    AuditLog::logAuthentication('Login Gagal', $username);
                } catch (Throwable $e) {}
                Helper::flash('error', 'Username/email atau password tidak valid, atau akun tidak aktif.', 'error');
                Helper::setOldInput(['username' => $username]);
                $this->redirect('login');
            }

            AuthMiddleware::regenerateSession();
            $userId = (int) $user->id;
            $userName = (string) $user->nama_lengkap;
            AuthMiddleware::setCurrentUser($user);
            $_SESSION['login_time'] = time();
            $_SESSION['last_activity'] = time();

            try {
                $this->userModel->updateLastLogin($userId);
                AuditLog::logAuthentication('Login Berhasil', (string) $user->username, $userId, (string) $user->role);
            } catch (Throwable $e) {}

            Helper::flash('success', "Selamat datang kembali, {$userName}!", 'success');
            $this->redirect('dashboard');
        }

        $this->view('auth/login', [
            'title' => 'Login',
            'layout' => 'auth',
        ]);
    }

    /**
     * Registrasi Akun Pasien
     */
    public function register(): void
    {
        if (AuthMiddleware::isAuthenticated()) {
            $this->redirect('dashboard');
        }

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();

            $data = [
                'nama_lengkap' => trim((string) ($_POST['nama_lengkap'] ?? '')),
                'nik' => trim((string) ($_POST['nik'] ?? '')),
                'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
                'no_telepon' => trim((string) ($_POST['no_telepon'] ?? '')),
                'tanggal_lahir' => trim((string) ($_POST['tanggal_lahir'] ?? '')),
                'jenis_kelamin' => trim((string) ($_POST['jenis_kelamin'] ?? '')),
                'tempat_lahir' => trim((string) ($_POST['tempat_lahir'] ?? '')),
                'alamat' => trim((string) ($_POST['alamat'] ?? '')),
                'golongan_darah' => trim((string) ($_POST['golongan_darah'] ?? '-')),
            ];
            $password = (string) ($_POST['password'] ?? '');
            $passwordConfirm = (string) ($_POST['password_confirm'] ?? '');
            $errors = [];

            if ($data['nama_lengkap'] === '' || mb_strlen($data['nama_lengkap']) > 100) {
                $errors[] = 'Nama lengkap wajib diisi.';
            }
            if (strlen($data['nik']) !== 16 || !ctype_digit($data['nik'])) {
                $errors[] = 'NIK wajib 16 digit angka.';
            }
            $birthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $data['tanggal_lahir']);
            if (
                !$birthDate
                || $birthDate->format('Y-m-d') !== $data['tanggal_lahir']
                || $birthDate > new DateTimeImmutable('today')
            ) {
                $errors[] = 'Tanggal lahir wajib diisi.';
            }
            if (!in_array($data['jenis_kelamin'], ['Laki-laki', 'Perempuan'], true)) {
                $errors[] = 'Jenis kelamin wajib dipilih.';
            }
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 100) {
                $errors[] = 'Format email tidak valid.';
            }
            if (!preg_match('/^\+?[0-9\s()-]{8,20}$/', $data['no_telepon'])) {
                $errors[] = 'Nomor telepon tidak valid.';
            }
            if ($data['tempat_lahir'] === '' || mb_strlen($data['tempat_lahir']) > 100) {
                $errors[] = 'Tempat lahir wajib diisi.';
            }
            if (!in_array($data['golongan_darah'], ['A', 'B', 'AB', 'O', '-'], true)) {
                $errors[] = 'Golongan darah tidak valid.';
            }
            if (strlen($password) < 8 || strlen($password) > 72) {
                $errors[] = 'Password harus terdiri dari 8 hingga 72 byte.';
            }
            if ($password !== $passwordConfirm) {
                $errors[] = 'Konfirmasi password tidak cocok.';
            }
            if (($_POST['terms'] ?? '') !== '1') {
                $errors[] = 'Persetujuan ketentuan penggunaan wajib dicentang.';
            }

            if ($data['email'] !== '' && $this->userModel->emailExists($data['email'])) {
                $errors[] = 'Email sudah terdaftar.';
            }
            if ($data['nik'] !== '' && $this->pasienModel->findByNIK($data['nik'])) {
                $errors[] = 'NIK sudah terdaftar dalam sistem.';
            }

            if ($errors !== []) {
                Helper::flash('error', implode(' ', $errors), 'error');
                Helper::setOldInput($data);
                $this->redirect('register');
            }

            $username = $this->generatePatientUsername($data['email']);
            $database = Database::getInstance();
            try {
                [$userId, $noRM] = $database->transaction(function () use ($data, $password, $username): array {
                    $userId = $this->userModel->createUser([
                        'username' => $username,
                        'password' => $password,
                        'nama_lengkap' => $data['nama_lengkap'],
                        'email' => $data['email'],
                        'no_telepon' => $data['no_telepon'],
                        'role' => 'pasien',
                        'is_active' => 1,
                    ]);
                    if ($userId === false) {
                        throw new RuntimeException('Akun pasien tidak dapat disimpan.');
                    }

                    $noRM = $this->pasienModel->generateUniqueNoRM();
                    $patientId = $this->pasienModel->create([
                        'user_id' => (int) $userId,
                        'no_rekam_medis' => $noRM,
                        'nik' => $data['nik'],
                        'nama_lengkap' => $data['nama_lengkap'],
                        'jenis_kelamin' => $data['jenis_kelamin'],
                        'tempat_lahir' => $data['tempat_lahir'],
                        'tanggal_lahir' => $data['tanggal_lahir'],
                        'alamat' => $data['alamat'] !== '' ? $data['alamat'] : '-',
                        'no_telepon' => $data['no_telepon'],
                        'golongan_darah' => $data['golongan_darah'],
                    ]);
                    if ($patientId === false) {
                        throw new RuntimeException('Profil pasien tidak dapat disimpan.');
                    }

                    try {
                        AuditLog::logAuthentication('Registrasi Pasien', $username, (int) $userId, 'pasien');
                    } catch (Throwable $e) {}
                    return [(int) $userId, $noRM];
                });
            } catch (PDOException $exception) {
                if ($exception->getCode() !== '23000') {
                    throw $exception;
                }
                Helper::flash('error', 'Email atau NIK sudah terdaftar.', 'error');
                Helper::setOldInput($data);
                $this->redirect('register');
            }

            try {
                NotifikasiModel::createForUser(
                    $userId,
                    'Selamat Datang di ANTRIAN SEHAT',
                    "Pendaftaran akun Anda berhasil. Nomor Rekam Medis Anda adalah {$noRM}.",
                    'success'
                );
            } catch (Throwable $e) {}

            Helper::clearOldInput();
            Helper::flash('success', "Registrasi berhasil! Nomor Rekam Medis Anda: {$noRM}. Silakan login.", 'success');
            $this->redirect('login');
        }

        $this->view('auth/register', [
            'title' => 'Registrasi Pasien',
            'layout' => 'auth',
        ]);
    }

    /**
     * Lupa Password
     */
    public function forgotPassword(): void
    {
        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            Helper::flash(
                'info',
                'Pemulihan password melalui email belum tersedia. Silakan hubungi administrator.',
                'info'
            );
            $this->redirect('forgot-password');
        }

        $this->view('auth/forgot_password', [
            'title' => 'Lupa Password',
            'layout' => 'auth',
        ]);
    }

    /**
     * Profil Pengguna
     */
    public function profile(): void
    {
        AuthMiddleware::requireAuth();
        $userId = (int) $_SESSION['user_id'];
        $user = $this->userModel->findById($userId);
        $pasien = $this->pasienModel->findByUserId($userId);

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            $nama = trim((string) ($_POST['nama_lengkap'] ?? ''));
            $email = strtolower(trim((string) ($_POST['email'] ?? '')));
            $telp = trim((string) ($_POST['no_telepon'] ?? ''));

            if ($nama === '' || mb_strlen($nama) > 100) {
                Helper::flash('error', 'Nama lengkap wajib diisi (maksimal 100 karakter).', 'error');
                $this->redirect('profile');
            }
            if ($email !== '' && (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100)) {
                Helper::flash('error', 'Format email tidak valid.', 'error');
                $this->redirect('profile');
            }
            if ($email !== '' && $this->userModel->emailExists($email, $userId)) {
                Helper::flash('error', 'Email sudah digunakan akun lain.', 'error');
                $this->redirect('profile');
            }
            if ($telp !== '' && !preg_match('/^\+?[0-9\s()-]{8,20}$/', $telp)) {
                Helper::flash('error', 'Nomor telepon tidak valid.', 'error');
                $this->redirect('profile');
            }

            Database::getInstance()->transaction(function () use ($userId, $nama, $email, $telp): void {
                if (!$this->userModel->updateProfile($userId, [
                    'nama_lengkap' => $nama,
                    'email' => $email !== '' ? $email : null,
                    'no_telepon' => $telp !== '' ? $telp : null,
                ])) {
                    throw new RuntimeException('Profil pengguna tidak dapat diperbarui.');
                }
                $patient = $this->pasienModel->findByUserId($userId);
                if ($patient && !$this->pasienModel->update((int) $patient->id, [
                    'nama_lengkap' => $nama,
                    'no_telepon' => $telp,
                ])) {
                    throw new RuntimeException('Profil pasien tidak dapat disinkronkan.');
                }
                try {
                    AuditLog::log('Perubahan Profil', 'users', $userId);
                } catch (Throwable $e) {}
            });

            Helper::clearOldInput();
            Helper::flash('success', 'Profil Anda berhasil diperbarui.', 'success');
            $this->redirect('profile');
        }

        $this->view('auth/profile', [
            'title' => 'Profil Pengguna',
            'user' => $user,
            'pasien' => $pasien,
            'layout' => 'main',
        ]);
    }

    /**
     * Ubah Password
     */
    public function changePassword(): void
    {
        AuthMiddleware::requireAuth();
        $userId = (int) $_SESSION['user_id'];

        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
            $curr = (string) ($_POST['current_password'] ?? '');
            $new = (string) ($_POST['new_password'] ?? '');
            $confirm = (string) ($_POST['confirm_password'] ?? '');

            $user = $this->userModel->findById($userId);
            if (!$user || !password_verify($curr, $user->password)) {
                try {
                    AuditLog::log('Perubahan Password Gagal', 'users', $userId);
                } catch (Throwable $e) {}
                Helper::flash('error', 'Password saat ini salah.', 'error');
                $this->redirect('change-password');
            }

            if (strlen($new) < 8 || strlen($new) > 72) {
                Helper::flash('error', 'Password baru harus terdiri dari 8 hingga 72 byte.', 'error');
                $this->redirect('change-password');
            }
            if ($new !== $confirm) {
                Helper::flash('error', 'Konfirmasi password baru tidak cocok.', 'error');
                $this->redirect('change-password');
            }

            if (!$this->userModel->updatePassword($userId, $new)) {
                throw new RuntimeException('Password tidak dapat diperbarui.');
            }
            AuthMiddleware::regenerateSession();
            try {
                AuditLog::log('Perubahan Password', 'users', $userId);
            } catch (Throwable $e) {}
            Helper::flash('success', 'Password berhasil diubah.', 'success');
            $this->redirect('change-password');
        }

        $this->view('auth/change_password', [
            'title' => 'Ubah Password',
            'layout' => 'main',
        ]);
    }

    /**
     * Logout
     */
    public function logout(): void
    {
        if ($this->isPost()) {
            CsrfMiddleware::validateRequest();
        }
        if (isset($_SESSION['user_id'])) {
            try {
                AuditLog::log('Logout', 'users', $_SESSION['user_id']);
            } catch (Throwable $e) {}
        }
        Session::destroy();
        Session::start();
        Helper::flash('success', 'Anda telah berhasil logout.', 'info');
        $this->redirect('login');
    }

    private function generatePatientUsername(string $email): string
    {
        $localPart = strstr($email, '@', true);
        $base = preg_replace('/[^a-zA-Z0-9_]/', '_', $localPart ?: 'pasien');
        $base = trim((string) $base, '_');
        $base = substr($base !== '' ? $base : 'pasien', 0, 30);

        do {
            $username = 'pasien_' . $base . '_' . random_int(100000, 999999);
        } while ($this->userModel->usernameExists($username));

        return $username;
    }
}