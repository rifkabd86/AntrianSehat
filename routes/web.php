<?php
/**
 * ANTRIAN SEHAT - Web Routes
 * 
 * Pendaftaran seluruh endpoint URL aplikasi ke controller yang bersangkutan.
 */

// Home & Auth Routes
$router->get('/', 'HomeController@index');
$router->get('/login', 'AuthController@login');
$router->post('/login', 'AuthController@login');
$router->get('/logout', 'AuthController@logout');
$router->post('/logout', 'AuthController@logout');
$router->get('/auth/logout', 'AuthController@logout');
$router->post('/auth/logout', 'AuthController@logout');

$router->get('/register', 'AuthController@register');
$router->post('/register', 'AuthController@register');
$router->get('/forgot-password', 'AuthController@forgotPassword');
$router->post('/forgot-password', 'AuthController@forgotPassword');
$router->get('/profile', 'AuthController@profile');
$router->post('/profile', 'AuthController@profile');
$router->get('/change-password', 'AuthController@changePassword');
$router->get('/auth/change-password', 'AuthController@changePassword');
$router->post('/change-password', 'AuthController@changePassword');

// Administrator-only user management
$router->get('/users', 'UserController@index');
$router->get('/users/create', 'UserController@create');
$router->post('/users/store', 'UserController@store');
$router->get('/users/detail/{id}', 'UserController@detail');
$router->get('/users/edit/{id}', 'UserController@edit');
$router->post('/users/update/{id}', 'UserController@update');
$router->post('/users/status/{id}', 'UserController@setStatus');
$router->post('/users/reset-password/{id}', 'UserController@resetPassword');

// Dashboard
$router->get('/dashboard', 'DashboardController@index');

// Pendaftaran Pasien & Ambil Antrean
$router->get('/pendaftaran', 'PendaftaranController@index');
$router->get('/pendaftaran/create', 'PendaftaranController@create');
$router->post('/pendaftaran/store', 'PendaftaranController@store');
$router->get('/pendaftaran/tiket/{id}', 'PendaftaranController@tiket');

// Antrean Pasien & Loket
$router->get('/antrean', 'AntreanController@index');
$router->get('/antrean/saya', 'AntreanController@myQueues');
$router->get('/riwayat', 'AntreanController@myHistory');
$router->get('/antrean/detail/{id}', 'AntreanController@detail');
$router->get('/antrean/loket', 'AntreanController@loket');
$router->post('/antrean/panggil-berikutnya', 'AntreanController@panggilBerikutnya');
$router->post('/antrean/update-status', 'AntreanController@updateStatus');
$router->get('/antrean/display-data', 'AntreanController@displayData');

// Display Monitor TV Publik
$router->get('/display', 'AntreanController@display');

// Data Pasien (CRUD)
$router->get('/pasien', 'PasienController@index');
$router->get('/pasien/create', 'PasienController@create');
$router->post('/pasien/store', 'PasienController@store');
$router->get('/pasien/detail/{id}', 'PasienController@detail');
$router->get('/pasien/edit/{id}', 'PasienController@edit');
$router->post('/pasien/update/{id}', 'PasienController@update');
$router->post('/pasien/delete/{id}', 'PasienController@delete');

// Data Poli
$router->get('/poli', 'PoliController@index');
$router->get('/poli/create', 'PoliController@create');
$router->post('/poli/create', 'PoliController@create');
$router->get('/poli/edit/{id}', 'PoliController@edit');
$router->post('/poli/edit/{id}', 'PoliController@edit');

// Data Dokter & Jadwal
$router->get('/dokter', 'DokterController@index');
$router->get('/dokter/create', 'DokterController@create');
$router->post('/dokter/create', 'DokterController@create');
$router->get('/dokter/edit/{id}', 'DokterController@edit');
$router->post('/dokter/edit/{id}', 'DokterController@edit');
$router->post('/dokter/delete/{id}', 'DokterController@delete');
$router->get('/dokter/jadwal/{id}', 'DokterController@jadwal');
$router->get('/api/dokter-by-poli/{id}', 'DokterController@apiByPoli');

// Data Jadwal Dokter
$router->get('/jadwal', 'JadwalController@index');
$router->get('/jadwal/create', 'JadwalController@create');
$router->post('/jadwal/store', 'JadwalController@store');
$router->get('/jadwal/edit/{id}', 'JadwalController@edit');
$router->post('/jadwal/update/{id}', 'JadwalController@update');
$router->post('/jadwal/delete/{id}', 'JadwalController@delete');

// Pengaturan Puskesmas & Sistem
$router->get('/settings', 'SettingsController@index');
$router->post('/settings/update', 'SettingsController@update');

// Laporan
$router->get('/laporan', 'LaporanController@index');
$router->get('/laporan/cetak', 'LaporanController@cetak');

// Audit Log & Notifikasi
$router->get('/audit', 'AuditController@index');
$router->get('/notifikasi', 'NotifikasiController@index');
