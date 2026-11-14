<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Seeder hanya dapat dijalankan melalui CLI.');
}

require dirname(__DIR__, 2) . '/config/config.php';
require BASE_PATH . '/core/Database.php';

$accounts = [
    ['admin', 'Administrator Puskesmas', 'admin@antriansehat.id', '081200000001', 'admin'],
    ['petugas1', 'Siti Rahayu', 'petugas1@antriansehat.id', '081200000002', 'petugas_pendaftaran'],
    ['petugas2', 'Ahmad Fauzi', 'petugas2@antriansehat.id', '081200000003', 'petugas_pelayanan'],
    ['pasien1', 'Rina Aprilia', 'pasien1@antriansehat.id', '081234567890', 'pasien'],
];

$statement = Database::getInstance()->getConnection()->prepare(
    'INSERT INTO users (username, password, nama_lengkap, email, no_telepon, role, is_active)
     VALUES (:username, :password, :name, :email, :phone, :role, 1)
     ON DUPLICATE KEY UPDATE username = VALUES(username)'
);

foreach ($accounts as [$username, $name, $email, $phone, $role]) {
    $statement->execute([
        'username' => $username,
        'password' => password_hash('password123', PASSWORD_DEFAULT),
        'name' => $name,
        'email' => $email,
        'phone' => $phone,
        'role' => $role,
    ]);
}

echo "Akun awal tersedia. Password awal: password123\n";