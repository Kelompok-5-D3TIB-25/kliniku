<?php

/**
 * KLINIKU — Production Entry Point Bridge for Shared Hosting / InfinityFree
 * 
 * File ini ditempatkan di dalam folder /htdocs di hosting InfinityFree.
 * Bertugas menjembatani request HTTP publik menuju aplikasi Laravel yang berada di /backend.
 */

use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Cek mode pemeliharaan (Maintenance mode)
if (file_exists($maintenance = __DIR__ . '/../backend/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 2. Muat autoloader Composer dari folder /backend
require __DIR__ . '/../backend/vendor/autoload.php';

// 3. Inisialisasi aplikasi Laravel
$app = require_once __DIR__ . '/../backend/bootstrap/app.php';

// 4. Tangani request HTTP yang masuk dan kembalikan respons ke browser
$app->handleRequest(Request::capture());
