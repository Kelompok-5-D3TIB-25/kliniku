<?php

/**
 * KLINIKU — Production Entry Point for InfinityFree (/htdocs)
 * 
 * Karena sistem InfinityFree Free Hosting menghapus berkas di luar /htdocs,
 * seluruh kode aplikasi berada di dalam /htdocs dan diamankan melalui .htaccess.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Cek mode pemeliharaan (Maintenance mode)
if (file_exists($maintenance = __DIR__ . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 2. Muat autoloader Composer produksi
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
} else {
    http_response_code(500);
    die('Error: Folder vendor/ tidak ditemukan. Pastikan folder vendor telah di-upload ke /htdocs.');
}

// 3. Inisialisasi aplikasi Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';

// 4. Tangani request HTTP yang masuk dan kirimkan respons ke browser
$app->handleRequest(Request::capture());
