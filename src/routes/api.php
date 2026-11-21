<?php

use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for KLINIKU
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {
    // Patient Registration Endpoints
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/auth/register', [RegisterController::class, 'register']);
    Route::match(['get', 'post'], '/check-nik', [RegisterController::class, 'checkNik']);
    Route::get('/polis', [RegisterController::class, 'getPoliList']);
});

// Root API aliases
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::match(['get', 'post'], '/check-nik', [RegisterController::class, 'checkNik']);
Route::get('/polis', [RegisterController::class, 'getPoliList']);
