<?php

class Database
{
    private static ?Database $instance = null;
    private ?PDO $connection = null;

    private function __construct()
    {
        $config = require BASE_PATH . '/config/database.php';
        $my = $config['mysql'];
        $options = $config['options'];

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $my['host'],
            $my['port'],
            $my['dbname'],
            $my['charset']
        );

        $this->connection = new PDO($dsn, $my['username'], $my['password'], $options);
    }

    private function __clone()
    {
    }

    public function __wakeup(): void
    {
        throw new RuntimeException('Database singleton tidak dapat di-unserialize.');
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }

    public function transaction(callable $callback): mixed
    {
        $this->connection->beginTransaction();
        try {
            $result = $callback($this->connection);
            $this->connection->commit();
            return $result;
        } catch (Throwable $exception) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $exception;
        }
    }
}
