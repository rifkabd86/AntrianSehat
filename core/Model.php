<?php
/**
 * ANTRIAN SEHAT - Base Model
 * 
 * Kelas dasar untuk semua model.
 * Menyediakan koneksi database dan method CRUD umum.
 */

class Model
{
    /**
     * PDO database connection
     * @var PDO
     */
    protected PDO $db;

    /**
     * Nama tabel database
     * Harus di-set oleh child class
     * @var string
     */
    protected string $table = '';

    /**
     * Primary key column
     * @var string
     */
    protected string $primaryKey = 'id';

    /**
     * Constructor - mendapatkan koneksi database dari singleton
     */
    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Ambil semua data dari tabel
     * 
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $direction Arah pengurutan (ASC/DESC)
     * @return array
     */
    public function findAll(string $orderBy = '', string $direction = 'ASC'): array
    {
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($orderBy)) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu data berdasarkan ID
     * 
     * @param int|string $id
     * @return object|false
     */
    public function findById($id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Ambil data berdasarkan kondisi tertentu
     * 
     * @param array $conditions Array asosiatif kolom => nilai
     * @param string $orderBy Kolom untuk pengurutan
     * @param string $direction Arah pengurutan
     * @return array
     */
    public function findWhere(array $conditions, string $orderBy = '', string $direction = 'ASC'): array
    {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $paramName = ':where_' . $column;
            $whereClauses[] = "{$column} = {$paramName}";
            $params[$paramName] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses);

        if (!empty($orderBy)) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /**
     * Ambil satu data berdasarkan kondisi
     * 
     * @param array $conditions Array asosiatif kolom => nilai
     * @return object|false
     */
    public function findOneWhere(array $conditions)
    {
        $whereClauses = [];
        $params = [];

        foreach ($conditions as $column => $value) {
            $paramName = ':where_' . $column;
            $whereClauses[] = "{$column} = {$paramName}";
            $params[$paramName] = $value;
        }

        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' AND ', $whereClauses) . " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        return $stmt->fetch();
    }

    /**
     * Insert data baru ke tabel
     * 
     * @param array $data Array asosiatif kolom => nilai
     * @return string|false Last insert ID atau false jika gagal
     */
    public function create(array $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $stmt = $this->db->prepare($sql);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':' . $key, $value);
        }

        if ($stmt->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Update data berdasarkan ID
     * 
     * @param int|string $id
     * @param array $data Array asosiatif kolom => nilai
     * @return bool
     */
    public function update($id, array $data): bool
    {
        $setClauses = [];
        foreach (array_keys($data) as $column) {
            $setClauses[] = "{$column} = :set_{$column}";
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClauses) .
            " WHERE {$this->primaryKey} = :id";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);

        foreach ($data as $key => $value) {
            $stmt->bindValue(':set_' . $key, $value);
        }

        return $stmt->execute();
    }

    /**
     * Hapus data berdasarkan ID
     * 
     * @param int|string $id
     * @return bool
     */
    public function delete($id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':id', $id);
        return $stmt->execute();
    }

    /**
     * Hitung jumlah total record
     * 
     * @param array $conditions Kondisi opsional
     * @return int
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";

        $params = [];
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $paramName = ':cnt_' . $column;
                $whereClauses[] = "{$column} = {$paramName}";
                $params[$paramName] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result->total;
    }

    /**
     * Ambil data dengan pagination
     * 
     * @param int $page Halaman saat ini (dimulai dari 1)
     * @param int $perPage Jumlah data per halaman
     * @param string $orderBy Kolom pengurutan
     * @param string $direction Arah pengurutan
     * @param array $conditions Kondisi filter opsional
     * @return array ['data' => [], 'total' => int, 'per_page' => int, 'current_page' => int, 'last_page' => int]
     */
    public function paginate(int $page = 1, int $perPage = 10, string $orderBy = '', string $direction = 'ASC', array $conditions = []): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        // Count total records
        $total = $this->count($conditions);
        $lastPage = max(1, (int) ceil($total / $perPage));

        // Build query
        $sql = "SELECT * FROM {$this->table}";

        $params = [];
        if (!empty($conditions)) {
            $whereClauses = [];
            foreach ($conditions as $column => $value) {
                $paramName = ':pg_' . $column;
                $whereClauses[] = "{$column} = {$paramName}";
                $params[$paramName] = $value;
            }
            $sql .= " WHERE " . implode(' AND ', $whereClauses);
        }

        if (!empty($orderBy)) {
            $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
            $sql .= " ORDER BY {$orderBy} {$direction}";
        }

        $sql .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $value) {
            $stmt->bindValue($param, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'data'         => $stmt->fetchAll(),
            'total'        => $total,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => $lastPage,
        ];
    }

    /**
     * Cek apakah record dengan nilai tertentu sudah ada
     * 
     * @param string $column Nama kolom
     * @param mixed $value Nilai yang dicek
     * @param int|string|null $excludeId ID yang dikecualikan (untuk update)
     * @return bool
     */
    public function exists(string $column, $value, $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as total FROM {$this->table} WHERE {$column} = :value";
        $params = [':value' => $value];

        if ($excludeId !== null) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql);
        foreach ($params as $param => $val) {
            $stmt->bindValue($param, $val);
        }
        $stmt->execute();
        $result = $stmt->fetch();
        return (int) $result->total > 0;
    }

    /**
     * Eksekusi raw query
     * 
     * @param string $sql SQL query
     * @param array $params Parameter binding
     * @return \PDOStatement
     */
    public function raw(string $sql, array $params = []): \PDOStatement
    {
        $stmt = $this->db->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->execute();
        return $stmt;
    }

    /**
     * Begin database transaction
     * 
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    /**
     * Commit transaction
     * 
     * @return bool
     */
    public function commit(): bool
    {
        return $this->db->commit();
    }

    /**
     * Rollback transaction
     * 
     * @return bool
     */
    public function rollback(): bool
    {
        return $this->db->rollBack();
    }
}
