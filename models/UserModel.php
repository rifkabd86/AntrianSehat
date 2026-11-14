<?php
/**
 * ANTRIAN SEHAT - User Model
 * 
 * Model untuk mengelola data pengguna (users).
 * Menyediakan method autentikasi, CRUD, dan pencarian pengguna.
 */

class UserModel extends Model
{
    /**
     * Nama tabel database
     * @var string
     */
    protected string $table = 'users';

    /**
     * Cari user berdasarkan username
     * 
     * @param string $username
     * @return object|false
     */
    public function findByUsername(string $username)
    {
        $sql = "SELECT * FROM {$this->table} WHERE username = :username LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cari user berdasarkan email
     * 
     * @param string $email
     * @return object|false
     */
    public function findByEmail(string $email)
    {
        $sql = "SELECT * FROM {$this->table} WHERE email = :email AND deleted_at IS NULL LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Cari user berdasarkan username atau email
     * 
     * @param string $value Username atau email
     * @return object|false
     */
    public function findByUsernameOrEmail(string $value)
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE (username = :username OR email = :email) AND deleted_at IS NULL
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $value);
        $stmt->bindValue(':email', $value);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Autentikasi user dengan username/email dan password
     * 
     * @param string $username Username atau email
     * @param string $password Password plain text
     * @return object|false User object jika berhasil, false jika gagal
     */
    public function authenticate(string $username, string $password)
    {
        $user = $this->findByUsernameOrEmail($username);

        static $dummyHash;
        $dummyHash ??= password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);
        $passwordHash = $user ? $user->password : $dummyHash;
        $passwordMatches = password_verify($password, $passwordHash);

        if (
            !$user
            || !$passwordMatches
            || (int) ($user->is_active ?? 0) !== 1
            || !empty($user->deleted_at)
            || !in_array($user->role ?? '', ['admin', 'petugas_pendaftaran', 'petugas_pelayanan', 'pasien'], true)
        ) {
            return false;
        }

        return $user;
    }

    /**
     * Buat user baru dengan password yang di-hash
     * 
     * @param array $data Data user
     * @return string|false Last insert ID atau false
     */
    public function createUser(array $data)
    {
        // Hash password jika ada
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        // Set default values
        if (!isset($data['is_active'])) {
            $data['is_active'] = 1;
        }

        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }

        if (!isset($data['updated_at'])) {
            $data['updated_at'] = date('Y-m-d H:i:s');
        }

        return $this->create($data);
    }

    /**
     * Update password user
     * 
     * @param int $userId ID user
     * @param string $newPassword Password baru (plain text)
     * @return bool
     */
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $sql = "UPDATE {$this->table} SET password = :password, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':password', password_hash($newPassword, PASSWORD_DEFAULT));
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Update profil user (tanpa password)
     * 
     * @param int $userId ID user
     * @param array $data Data profil yang akan diupdate
     * @return bool
     */
    public function updateProfile(int $userId, array $data): bool
    {
        // Pastikan password tidak diupdate melalui method ini
        unset($data['password']);

        // Set timestamp update
        $data['updated_at'] = date('Y-m-d H:i:s');

        return $this->update($userId, $data);
    }

    /**
     * Ambil semua user berdasarkan role
     * 
     * @param string $role Role user (admin, dokter, petugas, pasien)
     * @return array
     */
    public function getAllByRole(string $role): array
    {
        $sql = "SELECT * FROM {$this->table}
                WHERE role = :role AND deleted_at IS NULL
                ORDER BY nama_lengkap ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':role', $role);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil data user dengan pagination dan filter
     * 
     * @param int $page Halaman saat ini
     * @param int $perPage Jumlah per halaman
     * @param string|null $search Kata kunci pencarian
     * @param string|null $role Filter berdasarkan role
     * @return array
     */
    public function getAllPaginated(
        int $page = 1,
        int $perPage = 10,
        ?string $search = null,
        ?string $role = null,
        ?string $status = null
    ): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT id, username, email, nama_lengkap, role, no_telepon, is_active, avatar, last_login, created_at 
                FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (username LIKE :search OR email LIKE :search2 OR nama_lengkap LIKE :search3)";
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
        }

        if (!empty($role)) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }

        if ($status === 'active' || $status === 'inactive') {
            $sql .= ' AND is_active = :is_active';
            $params[':is_active'] = $status === 'active' ? 1 : 0;
        }

        $sql .= " ORDER BY created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Hitung total user dengan filter
     * 
     * @param string|null $search Kata kunci pencarian
     * @param string|null $role Filter role
     * @return int
     */
    public function countAll(?string $search = null, ?string $role = null, ?string $status = null): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (username LIKE :search OR email LIKE :search2 OR nama_lengkap LIKE :search3)";
            $params[':search'] = '%' . $search . '%';
            $params[':search2'] = '%' . $search . '%';
            $params[':search3'] = '%' . $search . '%';
        }

        if (!empty($role)) {
            $sql .= " AND role = :role";
            $params[':role'] = $role;
        }

        if ($status === 'active' || $status === 'inactive') {
            $sql .= ' AND is_active = :is_active';
            $params[':is_active'] = $status === 'active' ? 1 : 0;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result->total;
    }

    /**
     * Toggle status aktif user
     * 
     * @param int $userId ID user
     * @return bool
     */
    public function setActive(int $userId, bool $isActive): bool
    {
        $sql = "UPDATE {$this->table}
                SET is_active = :is_active, updated_at = :updated_at
                WHERE id = :id AND deleted_at IS NULL";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':is_active', $isActive ? 1 : 0, PDO::PARAM_INT);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function emailExists(string $email, ?int $exceptUserId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE email = :email AND deleted_at IS NULL";
        if ($exceptUserId !== null) {
            $sql .= ' AND id <> :except_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':email', $email);
        if ($exceptUserId !== null) {
            $stmt->bindValue(':except_id', $exceptUserId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function usernameExists(string $username, ?int $exceptUserId = null): bool
    {
        $sql = "SELECT 1 FROM {$this->table} WHERE username = :username AND deleted_at IS NULL";
        if ($exceptUserId !== null) {
            $sql .= ' AND id <> :except_id';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':username', $username);
        if ($exceptUserId !== null) {
            $stmt->bindValue(':except_id', $exceptUserId, PDO::PARAM_INT);
        }
        $stmt->execute();
        return (bool) $stmt->fetchColumn();
    }

    public function countByRole(?string $role = null, ?bool $active = null): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table} WHERE deleted_at IS NULL";
        $params = [];
        if ($role !== null) {
            $sql .= ' AND role = :role';
            $params[':role'] = $role;
        }
        if ($active !== null) {
            $sql .= ' AND is_active = :is_active';
            $params[':is_active'] = $active ? 1 : 0;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function countCreatedBetween(string $start, string $end, ?string $role = null): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}
                WHERE deleted_at IS NULL AND created_at >= :start AND created_at < :end";
        $params = [':start' => $start, ':end' => $end];
        if ($role !== null) {
            $sql .= ' AND role = :role';
            $params[':role'] = $role;
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function updateAccount(int $userId, array $data): bool
    {
        $allowed = ['username', 'email', 'nama_lengkap', 'no_telepon', 'role'];
        $data = array_intersect_key($data, array_flip($allowed));
        if ($data === []) {
            return false;
        }
        $data['updated_at'] = date('Y-m-d H:i:s');
        return $this->update($userId, $data);
    }

    /**
     * Set token reset password
     * 
     * @param int $userId ID user
     * @param string $token Token reset
     * @param string $expiry Waktu kedaluwarsa (format datetime)
     * @return bool
     */
    public function setResetToken(int $userId, string $token, string $expiry): bool
    {
        $sql = "UPDATE {$this->table} SET reset_token = :token, reset_token_expiry = :expiry, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':expiry', $expiry);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Cari user berdasarkan reset token yang masih valid
     * 
     * @param string $token Token reset
     * @return object|false
     */
    public function findByResetToken(string $token)
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE reset_token = :token 
                AND reset_token_expiry > :now 
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':token', $token);
        $stmt->bindValue(':now', date('Y-m-d H:i:s'));
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Hapus reset token setelah digunakan
     * 
     * @param int $userId ID user
     * @return bool
     */
    public function clearResetToken(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET reset_token = NULL, reset_token_expiry = NULL, updated_at = :updated_at WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':updated_at', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    /**
     * Update timestamp last login
     * 
     * @param int $userId ID user
     * @return bool
     */
    public function updateLastLogin(int $userId): bool
    {
        $sql = "UPDATE {$this->table} SET last_login = :last_login WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':last_login', date('Y-m-d H:i:s'));
        $stmt->bindValue(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}
