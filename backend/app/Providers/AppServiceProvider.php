<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Global API Rate Limiter (60 requests per minute per IP / user)
        RateLimiter::for('api', function (Request $request) {
            $key = $request->user()?->user_id ?: $request->ip();
            return Limit::perMinute(60)->by($key)->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan (Too Many Requests). Batas kuota request API terlampaui.',
                ], 429, $headers);
            });
        });

        // 2. Login Rate Limiter (5 attempts per minute per NIK + IP)
        RateLimiter::for('login', function (Request $request) {
            $nik = trim((string) $request->input('nik', ''));
            $key = Str::transliterate(Str::lower($nik)) . '|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan login gagal. Demi keamanan, silakan tunggu 1 menit sebelum mencoba kembali.',
                ], 429, $headers);
            });
        });

        // 3. OTP Verification Rate Limiter (5 attempts per minute per IP)
        RateLimiter::for('otp', function (Request $request) {
            $key = 'otp_verify|' . $request->ip();
            return Limit::perMinute(5)->by($key)->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak percobaan verifikasi kode OTP. Silakan tunggu 1 menit sebelum mencoba kembali.',
                ], 429, $headers);
            });
        });

        // 4. OTP Resend Rate Limiter (3 requests per minute per IP)
        RateLimiter::for('otp_resend', function (Request $request) {
            $key = 'otp_resend|' . $request->ip();
            return Limit::perMinute(3)->by($key)->response(function (Request $request, array $headers) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terlalu banyak permintaan kirim ulang kode OTP. Silakan tunggu 1 menit sebelum mencoba kembali.',
                ], 429, $headers);
            });
        });
    }
}
