<?php

require_once dirname(__DIR__) . '/config/bootstrap.php';

$previewRoles = ['admin', 'registration', 'service', 'patient'];
$previewRole = $_GET['role'] ?? 'admin';
if (!in_array($previewRole, $previewRoles, true)) {
    $previewRole = 'admin';
}

(new View())->render('pages/dashboard', [
    'title' => 'Pratinjau Dashboard',
    'previewRole' => $previewRole,
], 'foundation');