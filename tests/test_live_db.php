<?php
require 'config/bootstrap.php';
$pdo = Database::getInstance()->getConnection();
$tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
echo "Connected to MySQL successfully!\n";
echo "Tables in database '" . DB_NAME . "':\n";
foreach ($tables as $t) {
    $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    echo " - $t ($count rows)\n";
}

