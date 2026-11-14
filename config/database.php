<?php
/**
 * ANTRIAN SEHAT - Database Configuration
 * 
 * Pengaturan koneksi database MySQL/MariaDB.
 * Untuk hosting di InfinityFree, sesuaikan kredensial di bawah ini atau melalui file .env.
 */

return [
    // Preferred driver: 'mysql'
    'driver' => function_exists('env') ? env('DB_DRIVER', 'mysql') : 'mysql',

    // MySQL Settings (Fallback langsung ke InfinityFree)
    'mysql' => [
        'host'     => defined('DB_HOST') ? DB_HOST : (function_exists('env') ? env('DB_HOST', 'sql303.infinityfree.com') : 'sql303.infinityfree.com'),
        'port'     => defined('DB_PORT') ? DB_PORT : (function_exists('env') ? env('DB_PORT', '3306') : '3306'),
        'dbname'   => defined('DB_NAME') ? DB_NAME : (function_exists('env') ? env('DB_NAME', 'if0_43381174_db_antriansehat') : 'if0_43381174_db_antriansehat'),
        'username' => defined('DB_USER') ? DB_USER : (function_exists('env') ? env('DB_USER', 'if0_43381174') : 'if0_43381174'),
        'password' => defined('DB_PASS') ? DB_PASS : (function_exists('env') ? env('DB_PASS', 'JAFrPgN5pMnZx') : 'JAFrPgN5pMnZx'),
        'charset'  => 'utf8mb4'
    ],

    // Strict Mode PDO Options
    'options' => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
        PDO::ATTR_EMULATE_PREPARES   => false,
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci',
    ]
];