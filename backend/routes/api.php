<?php

use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes for KLINIKU
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    // Patient Registration Endpoints
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/auth/register', [RegisterController::class, 'register']);
    Route::match(['get', 'post'], '/check-nik', [RegisterController::class, 'checkNik']);
    Route::get('/polis', [RegisterController::class, 'getPoliList']);

    // Login (Protected by throttle:login & Google reCAPTCHA)
    Route::middleware('throttle:login')->group(function () {
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/auth/login', [LoginController::class, 'login']);
    });

    // OTP Verification & Resend
    Route::middleware('throttle:otp')->group(function () {
        Route::match(['get', 'post'], '/verify-otp', [LoginController::class, 'verifyOtp']);
        Route::match(['get', 'post'], '/auth/verify-otp', [LoginController::class, 'verifyOtp']);
    });

    Route::middleware('throttle:otp_resend')->group(function () {
        Route::post('/resend-otp', [LoginController::class, 'resendOtp']);
        Route::post('/auth/resend-otp', [LoginController::class, 'resendOtp']);
    });

    // Protected Endpoints (Requires Bearer JWT token)
    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [LoginController::class, 'me']);
        Route::get('/auth/me', [LoginController::class, 'me']);
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/auth/logout', [LoginController::class, 'logout']);
    });
});

// Root API aliases with identical throttling
Route::middleware('throttle:api')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/auth/register', [RegisterController::class, 'register']);
    Route::match(['get', 'post'], '/check-nik', [RegisterController::class, 'checkNik']);
    Route::get('/polis', [RegisterController::class, 'getPoliList']);

    Route::middleware('throttle:login')->group(function () {
        Route::post('/login', [LoginController::class, 'login']);
        Route::post('/auth/login', [LoginController::class, 'login']);
    });

    Route::middleware('throttle:otp')->group(function () {
        Route::match(['get', 'post'], '/verify-otp', [LoginController::class, 'verifyOtp']);
        Route::match(['get', 'post'], '/auth/verify-otp', [LoginController::class, 'verifyOtp']);
    });

    Route::middleware('throttle:otp_resend')->group(function () {
        Route::post('/resend-otp', [LoginController::class, 'resendOtp']);
        Route::post('/auth/resend-otp', [LoginController::class, 'resendOtp']);
    });

    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [LoginController::class, 'me']);
        Route::get('/auth/me', [LoginController::class, 'me']);
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/auth/logout', [LoginController::class, 'logout']);
    });
});
