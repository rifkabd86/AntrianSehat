<?php

class UserController extends Controller
{
    private const ROLES = ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'];

    private UserModel $userModel;
    private PasienModel $pasienModel;

    public function __construct()
    {
        AuthMiddleware::requireRole('admin');
        $this->userModel = new UserModel();
        $this->pasienModel = new PasienModel();
    }

    public function index(): void
    {
        $search = mb_substr(trim((string) ($_GET['search'] ?? '')), 0, 100);
        $role = (string) ($_GET['role'] ?? '');
        $status = (string) ($_GET['status'] ?? '');
        if (!in_array($role, self::ROLES, true)) {
            $role = '';
        }
        if (!in_array($status, ['active', 'inactive'], true)) {
            $status = '';
        }

        $page = max(1, (int) ($_GET['page'] ?? 1));
        $perPage = 15;
        $total = $this->userModel->countAll($search, $role ?: null, $status ?: null);
        $totalPages = max(1, (int) ceil($total / $perPage));
        $page = min($page, $totalPages);

        $this->view('users/index', [
            'title' => 'Manajemen Pengguna',
            'layout' => 'main',
            'users' => $this->userModel->getAllPaginated($page, $perPage, $search, $role ?: null, $status ?: null),
            'search' => $search,
            'role' => $role,
            'status' => $status,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'totalPages' => $totalPages,
            'roles' => self::ROLES,
        ]);
    }

    public function create(): void
    {
        $this->view('users/form', [
            'title' => 'Tambah Pengguna',
            'layout' => 'main',
            'user' => null,
            'patient' => null,
            'roles' => self::ROLES,
        ]);
    }

    public function store(): void
    {
        CsrfMiddleware::validateRequest();
        $data = $this->readAccountInput();
        $password = (string) ($_POST['password'] ?? '');
        $errors = $this->validateAccount($data, $password, null);
        if ($data['role'] === 'pasien') {
            $errors = array_merge($errors, $this->validatePatientInput($_POST, null));
        }
        if ($this->userModel->usernameExists($data['username'])) {
            $errors[] = 'Username sudah digunakan.';
        }
        if ($this->userModel->emailExists($data['email'])) {
            $errors[] = 'Email sudah digunakan.';
        }
        if ($data['role'] === 'pasien' && strlen((string) ($_POST['nik'] ?? '')) === 16
            && $this->pasienModel->findByNIK(trim((string) $_POST['nik']))) {
            $errors[] = 'NIK sudah terdaftar.';
        }

        if ($errors !== []) {
            Helper::flash('error', implode(' ', array_unique($errors)), 'error');
            Helper::setOldInput($data + ['role' => $data['role']]);
            $this->redirect('users/create');
        }

        $userId = Database::getInstance()->transaction(function () use ($data, $password): int {
            $userId = $this->userModel->createUser($data + [
                'password' => $password,
                'is_active' => 1,
            ]);
            if ($userId === false) {
                throw new RuntimeException('Pengguna tidak dapat disimpan.');
            }

            if ($data['role'] === 'pasien') {
                $this->createPatientProfile((int) $userId, $_POST, $data['nama_lengkap'], $data['no_telepon']);
            }
            AuditLog::log('Penambahan Pengguna', 'users', (int) $userId, null, [
                'role' => $data['role'],
                'email' => $data['email'],
            ]);
            return (int) $userId;
        });

        Helper::clearOldInput();
        Helper::flash('success', 'Pengguna berhasil ditambahkan.', 'success');
        $this->redirect('users/detail/' . $userId);
    }

    public function detail(string $id): void
    {
        $user = $this->findManagedUser($id);
        $this->view('users/detail', [
            'title' => 'Detail Pengguna',
            'layout' => 'main',
            'user' => $user,
            'patient' => $this->pasienModel->findByUserId((int) $user->id),
            'roles' => self::ROLES,
        ]);
    }

    public function edit(string $id): void
    {
        $user = $this->findManagedUser($id);
        $this->view('users/form', [
            'title' => 'Edit Pengguna',
            'layout' => 'main',
            'user' => $user,
            'patient' => $this->pasienModel->findByUserId((int) $user->id),
            'roles' => self::ROLES,
        ]);
    }

    public function update(string $id): void
    {
        CsrfMiddleware::validateRequest();
        $user = $this->findManagedUser($id);
        $userId = (int) $user->id;
        $data = $this->readAccountInput();
        $errors = $this->validateAccount($data, '', $userId, false);

        if ($this->userModel->usernameExists($data['username'], $userId)) {
            $errors[] = 'Username sudah digunakan.';
        }
        if ($this->userModel->emailExists($data['email'], $userId)) {
            $errors[] = 'Email sudah digunakan.';
        }
        if ($userId === (int) $_SESSION['user_id'] && $data['role'] !== $user->role) {
            $errors[] = 'Role akun administrator yang sedang digunakan tidak dapat diubah.';
        }

        $patient = $this->pasienModel->findByUserId($userId);
        if ($data['role'] === 'pasien') {
            $errors = array_merge($errors, $this->validatePatientInput($_POST, $patient ? (int) $patient->id : null));
            $nik = trim((string) ($_POST['nik'] ?? ''));
            $existingPatient = strlen($nik) === 16 ? $this->pasienModel->findByNIK($nik) : false;
            if ($existingPatient && (!$patient || (int) $existingPatient->id !== (int) $patient->id)) {
                $errors[] = 'NIK sudah terdaftar.';
            }
        }

        if ($errors !== []) {
            Helper::flash('error', implode(' ', array_unique($errors)), 'error');
            Helper::setOldInput($data);
            $this->redirect('users/edit/' . $userId);
        }

        if ($user->role === 'admin' && $data['role'] !== 'admin' && $this->userModel->countByRole('admin', true) <= 1) {
            Helper::flash('error', 'Role administrator aktif terakhir tidak dapat diubah.', 'error');
            $this->redirect('users/detail/' . $userId);
        }

        Database::getInstance()->transaction(function () use ($user, $userId, $data, $patient): void {
            if (!$this->userModel->updateAccount($userId, $data)) {
                throw new RuntimeException('Data pengguna tidak dapat diperbarui.');
            }
            if ($data['role'] === 'pasien') {
                if ($patient) {
                    $this->updatePatientProfile($patient, $_POST, $data['nama_lengkap'], $data['no_telepon']);
                } else {
                    $this->createPatientProfile($userId, $_POST, $data['nama_lengkap'], $data['no_telepon']);
                }
            } elseif ($patient && !$this->pasienModel->update((int) $patient->id, [
                'nama_lengkap' => $data['nama_lengkap'],
                'no_telepon' => $data['no_telepon'],
            ])) {
                throw new RuntimeException('Data pasien terkait tidak dapat diperbarui.');
            }

            $oldRole = (string) $user->role;
            if ($oldRole !== $data['role']) {
                AuditLog::log('Perubahan Role Pengguna', 'users', $userId, ['role' => $oldRole], ['role' => $data['role']]);
            } else {
                AuditLog::log('Perubahan Data Pengguna', 'users', $userId, null, [
                    'role' => $data['role'],
                    'email' => $data['email'],
                ]);
            }
        });

        Helper::clearOldInput();
        Helper::flash('success', 'Data pengguna berhasil diperbarui.', 'success');
        $this->redirect('users/detail/' . $userId);
    }

    public function setStatus(string $id): void
    {
        CsrfMiddleware::validateRequest();
        $user = $this->findManagedUser($id);
        $userId = (int) $user->id;
        $status = (string) ($_POST['is_active'] ?? '');
        if (!in_array($status, ['0', '1'], true)) {
            Helper::flash('error', 'Status akun tidak valid.', 'error');
            $this->redirect('users/detail/' . $userId);
        }

        $active = $status === '1';
        if ($userId === (int) $_SESSION['user_id'] && !$active) {
            Helper::flash('error', 'Anda tidak dapat menonaktifkan akun sendiri.', 'error');
            $this->redirect('users/detail/' . $userId);
        }
        if ($user->role === 'admin' && (int) $user->is_active === 1 && !$active
            && $this->userModel->countByRole('admin', true) <= 1) {
            Helper::flash('error', 'Administrator aktif terakhir tidak dapat dinonaktifkan.', 'error');
            $this->redirect('users/detail/' . $userId);
        }
        if (!$this->userModel->setActive($userId, $active)) {
            throw new RuntimeException('Status akun tidak dapat diperbarui.');
        }

        AuditLog::log('Perubahan Status Akun', 'users', $userId, [
            'is_active' => (int) $user->is_active,
        ], [
            'is_active' => $active ? 1 : 0,
        ]);
        Helper::flash('success', $active ? 'Akun berhasil diaktifkan.' : 'Akun berhasil dinonaktifkan.', 'success');
        $this->redirect('users/detail/' . $userId);
    }

    public function resetPassword(string $id): void
    {
        CsrfMiddleware::validateRequest();
        $user = $this->findManagedUser($id);
        $userId = (int) $user->id;
        if ($userId === (int) $_SESSION['user_id']) {
            Helper::flash('error', 'Gunakan halaman profil untuk mengubah password akun sendiri.', 'error');
            $this->redirect('users/detail/' . $userId);
        }

        $password = (string) ($_POST['password'] ?? '');
        $confirmation = (string) ($_POST['password_confirm'] ?? '');
        if (strlen($password) < 8 || strlen($password) > 72 || $password !== $confirmation) {
            Helper::flash('error', 'Password harus 8–72 byte dan konfirmasinya harus sesuai.', 'error');
            $this->redirect('users/detail/' . $userId);
        }
        if (!$this->userModel->updatePassword($userId, $password)) {
            throw new RuntimeException('Password pengguna tidak dapat direset.');
        }

        AuditLog::log('Reset Password oleh Administrator', 'users', $userId);
        Helper::flash('success', 'Password pengguna berhasil direset.', 'success');
        $this->redirect('users/detail/' . $userId);
    }

    private function readAccountInput(): array
    {
        return [
            'username' => trim((string) ($_POST['username'] ?? '')),
            'nama_lengkap' => trim((string) ($_POST['nama_lengkap'] ?? '')),
            'email' => strtolower(trim((string) ($_POST['email'] ?? ''))),
            'no_telepon' => trim((string) ($_POST['no_telepon'] ?? '')),
            'role' => trim((string) ($_POST['role'] ?? '')),
        ];
    }

    private function validateAccount(array $data, string $password, ?int $userId, bool $requirePassword = true): array
    {
        $errors = [];
        if (mb_strlen($data['nama_lengkap']) < 2 || mb_strlen($data['nama_lengkap']) > 100) {
            $errors[] = 'Nama lengkap wajib diisi (maksimal 100 karakter).';
        }
        if (!preg_match('/^[a-zA-Z0-9_.-]{3,50}$/', $data['username'])) {
            $errors[] = 'Username harus 3–50 karakter dan hanya menggunakan huruf, angka, titik, garis bawah, atau tanda hubung.';
        }
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL) || strlen($data['email']) > 100) {
            $errors[] = 'Alamat email tidak valid.';
        }
        if ($data['no_telepon'] !== '' && !preg_match('/^\+?[0-9\s()-]{8,20}$/', $data['no_telepon'])) {
            $errors[] = 'Nomor telepon tidak valid.';
        }
        if (!in_array($data['role'], self::ROLES, true)) {
            $errors[] = 'Role pengguna tidak valid.';
        }
        if (($requirePassword && (strlen($password) < 8 || strlen($password) > 72))) {
            $errors[] = 'Password harus terdiri dari 8 hingga 72 byte.';
        }
        return $errors;
    }

    private function validatePatientInput(array $input, ?int $patientId): array
    {
        $errors = [];
        $nik = trim((string) ($input['nik'] ?? ''));
        $birthDate = trim((string) ($input['tanggal_lahir'] ?? ''));
        $birth = DateTimeImmutable::createFromFormat('!Y-m-d', $birthDate);
        if (strlen($nik) !== 16 || !ctype_digit($nik)) {
            $errors[] = 'Pasien harus memiliki NIK 16 digit.';
        }
        if (!$birth || $birth->format('Y-m-d') !== $birthDate || $birth > new DateTimeImmutable('today')) {
            $errors[] = 'Tanggal lahir pasien tidak valid.';
        }
        if (!in_array((string) ($input['jenis_kelamin'] ?? ''), ['Laki-laki', 'Perempuan'], true)) {
            $errors[] = 'Jenis kelamin pasien tidak valid.';
        }
        if (trim((string) ($input['tempat_lahir'] ?? '')) === '') {
            $errors[] = 'Tempat lahir pasien wajib diisi.';
        }
        if (!in_array((string) ($input['golongan_darah'] ?? '-'), ['A', 'B', 'AB', 'O', '-'], true)) {
            $errors[] = 'Golongan darah tidak valid.';
        }
        return $errors;
    }

    private function createPatientProfile(int $userId, array $input, string $name, string $phone): void
    {
        $patientId = $this->pasienModel->create([
            'user_id' => $userId,
            'no_rekam_medis' => $this->pasienModel->generateUniqueNoRM(),
            'nik' => trim((string) $input['nik']),
            'nama_lengkap' => $name,
            'jenis_kelamin' => $input['jenis_kelamin'],
            'tempat_lahir' => trim((string) $input['tempat_lahir']),
            'tanggal_lahir' => $input['tanggal_lahir'],
            'alamat' => trim((string) ($input['alamat'] ?? '')) ?: '-',
            'no_telepon' => $phone,
            'golongan_darah' => $input['golongan_darah'] ?? '-',
        ]);
        if ($patientId === false) {
            throw new RuntimeException('Profil pasien tidak dapat disimpan.');
        }
    }

    private function updatePatientProfile(object $patient, array $input, string $name, string $phone): void
    {
        if (!$this->pasienModel->update((int) $patient->id, [
            'nik' => trim((string) $input['nik']),
            'nama_lengkap' => $name,
            'jenis_kelamin' => $input['jenis_kelamin'],
            'tempat_lahir' => trim((string) $input['tempat_lahir']),
            'tanggal_lahir' => $input['tanggal_lahir'],
            'alamat' => trim((string) ($input['alamat'] ?? '')) ?: '-',
            'no_telepon' => $phone,
            'golongan_darah' => $input['golongan_darah'] ?? '-',
        ])) {
            throw new RuntimeException('Profil pasien tidak dapat diperbarui.');
        }
    }

    private function findManagedUser(string $id): object
    {
        if (!ctype_digit($id) || (int) $id < 1) {
            http_response_code(404);
            require BASE_PATH . '/views/errors/404.php';
            exit;
        }
        $user = $this->userModel->findById((int) $id);
        if (!$user || !empty($user->deleted_at)) {
            http_response_code(404);
            require BASE_PATH . '/views/errors/404.php';
            exit;
        }
        return $user;
    }
}
