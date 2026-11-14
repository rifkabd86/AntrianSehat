<?php
try {
    $pdo = new PDO("mysql:host=localhost;port=3306;charset=utf8mb4", "root", "", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    echo "MYSQL_CONNECTED\n";
    $stmt = $pdo->query("SHOW DATABASES LIKE 'antrian_sehat'");
    $db = $stmt->fetch();
    echo "DATABASE_EXISTS: " . ($db ? "YES" : "NO") . "\n";
    if ($db) {
        $pdo->query("USE antrian_sehat");
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        echo "TABLES: " . implode(", ", $tables) . "\n";
        foreach ($tables as $t) {
            $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
            echo " - $t: $count rows\n";
        }
    }
} catch (Exception $e) {
    echo "MYSQL_ERROR: " . $e->getMessage() . "\n";
}

