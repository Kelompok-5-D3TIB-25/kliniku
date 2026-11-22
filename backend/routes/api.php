<?php

use App\Http\Controllers\Api\LoginController;
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

    // Login & Email OTP Verification Endpoints
    Route::post('/login', [LoginController::class, 'login']);
    Route::post('/auth/login', [LoginController::class, 'login']);
    Route::match(['get', 'post'], '/verify-otp', [LoginController::class, 'verifyOtp']);
    Route::match(['get', 'post'], '/auth/verify-otp', [LoginController::class, 'verifyOtp']);
    Route::post('/resend-otp', [LoginController::class, 'resendOtp']);
    Route::post('/auth/resend-otp', [LoginController::class, 'resendOtp']);

    // Protected Endpoints (Requires Bearer JWT token)
    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [LoginController::class, 'me']);
        Route::get('/auth/me', [LoginController::class, 'me']);
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/auth/logout', [LoginController::class, 'logout']);
    });
});

// Root API aliases
Route::post('/register', [RegisterController::class, 'register']);
Route::post('/auth/register', [RegisterController::class, 'register']);
Route::match(['get', 'post'], '/check-nik', [RegisterController::class, 'checkNik']);
Route::get('/polis', [RegisterController::class, 'getPoliList']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/auth/login', [LoginController::class, 'login']);
Route::match(['get', 'post'], '/verify-otp', [LoginController::class, 'verifyOtp']);
Route::match(['get', 'post'], '/auth/verify-otp', [LoginController::class, 'verifyOtp']);
Route::post('/resend-otp', [LoginController::class, 'resendOtp']);
Route::post('/auth/resend-otp', [LoginController::class, 'resendOtp']);
Route::middleware('jwt.auth')->group(function () {
    Route::get('/me', [LoginController::class, 'me']);
    Route::get('/auth/me', [LoginController::class, 'me']);
    Route::post('/logout', [LoginController::class, 'logout']);
    Route::post('/auth/logout', [LoginController::class, 'logout']);
});
