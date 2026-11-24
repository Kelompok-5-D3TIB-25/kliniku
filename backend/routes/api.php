<?php

use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\LoketController;
use App\Http\Controllers\Api\QueueTrackerController;
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

    // Booking & Quota Engine (US-03)
    Route::get('/booking/dokter', [BookingController::class, 'getDokterList']);
    Route::post('/booking/cek-kuota', [BookingController::class, 'cekKuota']);

    // Live Queue Tracker & TV Display (US-02)
    Route::get('/display/tv', [QueueTrackerController::class, 'tvDisplay']);
    Route::get('/antrean/live/{id}', [QueueTrackerController::class, 'liveTracking']);
    Route::get('/antrean/poli/{poli_id}/hari-ini', [QueueTrackerController::class, 'poliHariIni']);

    // Protected Endpoints (Requires Bearer JWT token)
    Route::middleware('jwt.auth')->group(function () {
        Route::get('/me', [LoginController::class, 'me']);
        Route::get('/auth/me', [LoginController::class, 'me']);
        Route::post('/logout', [LoginController::class, 'logout']);
        Route::post('/auth/logout', [LoginController::class, 'logout']);

        // Booking e-Tiket details (Any authenticated role with authorization check)
        Route::get('/booking/{id}/tiket', [BookingController::class, 'getTiket']);

        // Patient-only operations
        Route::middleware('role:pasien')->group(function () {
            Route::post('/booking', [BookingController::class, 'store']);
            Route::post('/booking/{id}/batal', [BookingController::class, 'cancel']);
            Route::get('/antrean/saya', [QueueTrackerController::class, 'antreanSaya']);
        });

        // Admin Loket / Staf operations (US-04 & US-08)
        Route::middleware('role:staf')->group(function () {
            Route::post('/loket/checkin-qr', [LoketController::class, 'checkInQr']);
            Route::post('/loket/pendaftaran-walkin', [LoketController::class, 'pendaftaranWalkin']);
            Route::get('/loket/antrean-hari-ini', [LoketController::class, 'antreanHariIni']);
            Route::post('/loket/antrean/{id}/panggil', [LoketController::class, 'panggil']);
            Route::post('/loket/antrean/{id}/panggil-ulang', [LoketController::class, 'panggilUlang']);
            Route::post('/loket/antrean/{id}/lewati', [LoketController::class, 'lewati']);
            Route::post('/loket/antrean/{id}/layani', [LoketController::class, 'layani']);
        });
    });
});

// Root API aliases with identical throttling
Route::middleware('throttle:api')->group(function () {
    Route::post('/register', [RegisterController::class, 'register']);
    Route::post('/auth/register', [RegisterController::class, 'register']);
    Route::match(['get', 'post'], '/check-nik', [RegisterController::class, 'checkNik']);
    Route::get('/polis', [RegisterController::class, 'getPoliList']);

    Route::get('/booking/dokter', [BookingController::class, 'getDokterList']);
    Route::post('/booking/cek-kuota', [BookingController::class, 'cekKuota']);

    Route::get('/display/tv', [QueueTrackerController::class, 'tvDisplay']);
    Route::get('/antrean/live/{id}', [QueueTrackerController::class, 'liveTracking']);
    Route::get('/antrean/poli/{poli_id}/hari-ini', [QueueTrackerController::class, 'poliHariIni']);

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

        Route::get('/booking/{id}/tiket', [BookingController::class, 'getTiket']);

        Route::middleware('role:pasien')->group(function () {
            Route::post('/booking', [BookingController::class, 'store']);
            Route::post('/booking/{id}/batal', [BookingController::class, 'cancel']);
            Route::get('/antrean/saya', [QueueTrackerController::class, 'antreanSaya']);
        });

        Route::middleware('role:staf')->group(function () {
            Route::post('/loket/checkin-qr', [LoketController::class, 'checkInQr']);
            Route::post('/loket/pendaftaran-walkin', [LoketController::class, 'pendaftaranWalkin']);
            Route::get('/loket/antrean-hari-ini', [LoketController::class, 'antreanHariIni']);
            Route::post('/loket/antrean/{id}/panggil', [LoketController::class, 'panggil']);
            Route::post('/loket/antrean/{id}/panggil-ulang', [LoketController::class, 'panggilUlang']);
            Route::post('/loket/antrean/{id}/lewati', [LoketController::class, 'lewati']);
            Route::post('/loket/antrean/{id}/layani', [LoketController::class, 'layani']);
        });
    });
});
