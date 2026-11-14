<?php
/**
 * ANTRIAN SEHAT - Audit Log Model
 * 
 * Model untuk mencatat dan mengelola log audit aktivitas pengguna.
 * Menyediakan method statis untuk pencatatan dan method instance untuk query.
 */

class AuditLog extends Model
{
    /**
     * Nama tabel database
     * @var string
     */
    protected string $table = 'audit_logs';

    /**
     * Catat aktivitas audit
     * 
     * Method statis untuk memudahkan pencatatan dari mana saja.
     * Secara otomatis mengambil data user dari session.
     * 
     * @param string $aksi Aksi yang dilakukan (Login, Logout, dll)
     * @param string|null $tabel Nama tabel yang terpengaruh
     * @param int|string|null $recordId ID record yang terpengaruh
     * @param mixed $dataLama Data sebelum perubahan (array atau string)
     * @param mixed $dataBaru Data setelah perubahan (array atau string)
     * @return void
     */
    public static function log(string $aksi, ?string $tabel = null, $recordId = null, $dataLama = null, $dataBaru = null): void
    {
        $db = Database::getInstance()->getConnection();

        $user = $_SESSION['user'] ?? [];
        $userId = (int) ($_SESSION['user_id'] ?? (is_object($user) ? ($user->id ?? 0) : ($user['id'] ?? 0))) ?: null;
        $username = (string) (is_object($user) ? ($user->username ?? '') : ($user['username'] ?? ($_SESSION['user_username'] ?? 'system')));
        $role = (string) (is_object($user) ? ($user->role ?? '') : ($user['role'] ?? ($_SESSION['user_role'] ?? 'system')));
        self::insert($db, $userId, $username, $role, $aksi, $tabel, $recordId, $dataLama, $dataBaru);
    }

    public static function logAuthentication(
        string $aksi,
        string $username,
        ?int $userId = null,
        string $role = 'guest'
    ): void {
        $db = Database::getInstance()->getConnection();
        $username = mb_substr(preg_replace('/[\r\n\x00-\x1F]/u', '', $username) ?? '', 0, 50);
        self::insert($db, $userId, $username !== '' ? $username : 'unknown', $role, $aksi, 'users', $userId, null, null);
    }

    public static function countRecentLoginFailures(string $ipAddress, int $minutes = 15): int
    {
        $minutes = max(1, min(60, $minutes));
        $statement = Database::getInstance()->getConnection()->prepare(
            "SELECT COUNT(*) FROM audit_logs
             WHERE aksi = 'Login Gagal'
               AND ip_address = :ip_address
               AND created_at >= DATE_SUB(NOW(), INTERVAL {$minutes} MINUTE)"
        );
        $statement->bindValue(':ip_address', $ipAddress);
        $statement->execute();
        return (int) $statement->fetchColumn();
    }

    private static function insert(
        PDO $db,
        ?int $userId,
        string $username,
        string $role,
        string $aksi,
        ?string $tabel,
        int|string|null $recordId,
        mixed $dataLama,
        mixed $dataBaru
    ): void {
        $encode = static function (mixed $data): ?string {
            if ($data === null) {
                return null;
            }
            if (is_string($data)) {
                return $data;
            }
            $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
            return $json;
        };

        $statement = $db->prepare(
            'INSERT INTO audit_logs
                (user_id, username, role, aksi, tabel, record_id, data_lama, data_baru, ip_address, user_agent, created_at)
             VALUES
                (:user_id, :username, :role, :aksi, :tabel, :record_id, :data_lama, :data_baru, :ip_address, :user_agent, :created_at)'
        );
        $statement->bindValue(':user_id', $userId, $userId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $statement->bindValue(':username', mb_substr($username, 0, 50));
        $statement->bindValue(':role', $role);
        $statement->bindValue(':aksi', mb_substr($aksi, 0, 100));
        $statement->bindValue(':tabel', $tabel);
        $statement->bindValue(':record_id', $recordId);
        $statement->bindValue(':data_lama', $encode($dataLama));
        $statement->bindValue(':data_baru', $encode($dataBaru));
        $statement->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
        $statement->bindValue(':user_agent', mb_substr((string) ($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 2000));
        $statement->bindValue(':created_at', date('Y-m-d H:i:s'));
        $statement->execute();
    }

    /**
     * Ambil ringkasan statistik untuk 4 kartu audit log
     */
    public function getStatsSummary(): array
    {
        $db = $this->db;

        // 1. Total aktivitas
        $stmtTotal = $db->query("SELECT COUNT(*) FROM {$this->table}");
        $totalAktivitas = (int)$stmtTotal->fetchColumn();

        // 2. Aktivitas hari ini
        $stmtToday = $db->query("SELECT COUNT(*) FROM {$this->table} WHERE DATE(created_at) = CURDATE()");
        $aktivitasHariIni = (int)$stmtToday->fetchColumn();

        // 3. Total pengguna yang beraktivitas
        $stmtUsers = $db->query("SELECT COUNT(DISTINCT username) FROM {$this->table}");
        $totalPengguna = (int)$stmtUsers->fetchColumn();

        // 4. Aktivitas terakhir
        $stmtLast = $db->query("SELECT aksi, username, created_at FROM {$this->table} ORDER BY created_at DESC, id DESC LIMIT 1");
        $lastActivity = $stmtLast->fetch();

        return [
            'total_aktivitas'   => $totalAktivitas,
            'aktivitas_hari_ini'=> $aktivitasHariIni,
            'total_pengguna'    => $totalPengguna,
            'aktivitas_terakhir'=> $lastActivity ?: null,
        ];
    }

    /**
     * Ambil daftar username unik yang pernah tercatat di audit log
     */
    public function getUserList(): array
    {
        $sql = "SELECT DISTINCT username FROM {$this->table} ORDER BY username ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Ambil data audit log dengan pagination dan filter
     * 
     * @param int $page Halaman saat ini
     * @param int $perPage Jumlah per halaman
     * @param array $filters Filter: username, aksi, tabel, date_from, date_to, search
     * @return array
     */
    public function getAllPaginated(int $page = 1, int $perPage = 20, array $filters = []): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT * FROM {$this->table} WHERE 1=1";
        $params = [];

        // Filter username spesifik
        if (!empty($filters['username'])) {
            $sql .= " AND username = :username";
            $params[':username'] = $filters['username'];
        }

        // Filter user_id
        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        // Filter aksi
        if (!empty($filters['aksi'])) {
            $sql .= " AND aksi = :aksi";
            $params[':aksi'] = $filters['aksi'];
        }

        // Filter tabel
        if (!empty($filters['tabel'])) {
            $sql .= " AND tabel = :tabel";
            $params[':tabel'] = $filters['tabel'];
        }

        // Filter tanggal mulai
        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        // Filter tanggal akhir
        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        // Filter pencarian deskripsi / teks
        if (!empty($filters['search'])) {
            $sql .= " AND (username LIKE :search OR aksi LIKE :search2 OR tabel LIKE :search3 OR ip_address LIKE :search4 OR data_baru LIKE :search5)";
            $keyword = '%' . $filters['search'] . '%';
            $params[':search'] = $keyword;
            $params[':search2'] = $keyword;
            $params[':search3'] = $keyword;
            $params[':search4'] = $keyword;
            $params[':search5'] = $keyword;
        }

        $sql .= " ORDER BY created_at DESC, id DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function getRecent(int $limit = 8): array
    {
        $stmt = $this->db->prepare(
            "SELECT id, username, role, aksi, tabel, created_at, ip_address
             FROM {$this->table}
             ORDER BY created_at DESC, id DESC
             LIMIT :limit"
        );
        $stmt->bindValue(':limit', max(1, min(25, $limit)), PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Hitung total audit log dengan filter
     * 
     * @param array $filters Filter yang sama dengan getAllPaginated
     * @return int
     */
    public function countAll(array $filters = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";
        $params = [];

        if (!empty($filters['username'])) {
            $sql .= " AND username = :username";
            $params[':username'] = $filters['username'];
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND user_id = :user_id";
            $params[':user_id'] = $filters['user_id'];
        }

        if (!empty($filters['aksi'])) {
            $sql .= " AND aksi = :aksi";
            $params[':aksi'] = $filters['aksi'];
        }

        if (!empty($filters['tabel'])) {
            $sql .= " AND tabel = :tabel";
            $params[':tabel'] = $filters['tabel'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(created_at) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(created_at) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (username LIKE :search OR aksi LIKE :search2 OR tabel LIKE :search3 OR ip_address LIKE :search4 OR data_baru LIKE :search5)";
            $keyword = '%' . $filters['search'] . '%';
            $params[':search'] = $keyword;
            $params[':search2'] = $keyword;
            $params[':search3'] = $keyword;
            $params[':search4'] = $keyword;
            $params[':search5'] = $keyword;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) (is_object($result) ? ($result->total ?? 0) : ($result['total'] ?? 0));
    }

    /**
     * Ambil daftar jenis aksi yang unik untuk filter dropdown
     * 
     * @return array
     */
    public function getAksiTypes(): array
    {
        $sql = "SELECT DISTINCT aksi FROM {$this->table} WHERE aksi IS NOT NULL AND aksi != '' ORDER BY aksi ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
