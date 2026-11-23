<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Mail\OtpMail;
use App\Models\User;
use App\Services\JwtService;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    protected RecaptchaService $recaptchaService;
    protected JwtService $jwtService;

    public function __construct(
        RecaptchaService $recaptchaService,
        JwtService $jwtService
    ) {
        $this->recaptchaService = $recaptchaService;
        $this->jwtService = $jwtService;
    }

    /**
     * Step 1: Submit Credentials & reCAPTCHA, generate and send 6-digit Email OTP.
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // 1. Verify Google reCAPTCHA "I'm not a robot" token
        $isCaptchaValid = $this->recaptchaService->verify($validated['recaptcha_token'], $request->ip());

        if (!$isCaptchaValid) {
            return response()->json([
                'success' => false,
                'message' => 'Verifikasi reCAPTCHA gagal. Pastikan Anda telah mencentang widget "Saya bukan robot" dengan benar.',
                'errors' => [
                    'recaptcha_token' => ['Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa.'],
                ],
            ], 422);
        }

        // 2. Verify User Credentials (NIK & Password)
        /** @var User|null $user */
        $user = User::with(['pasien.poli', 'dokter.poli', 'staf'])
            ->where('user_nik', $validated['nik'])
            ->first();

        if (!$user || !Hash::check($validated['password'], $user->user_password)) {
            return response()->json([
                'success' => false,
                'message' => 'Login gagal. NIK atau kata sandi yang Anda masukkan tidak sesuai.',
            ], 401);
        }

        // 3. Check Account Status
        if ($user->user_status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda berstatus non-aktif. Silakan hubungi bagian loket atau administrasi KLINIKU.',
            ], 403);
        }

        // 4. Ensure User Has Registered Email
        if (empty($user->user_email)) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda belum memiliki alamat email yang terdaftar untuk verifikasi OTP. Silakan hubungi bagian administrasi.',
            ], 422);
        }

        // 5. Generate 6-Digit OTP Code & Session ID
        $otpCode = sprintf('%06d', random_int(100000, 999999));
        $sessionId = 'otp_' . bin2hex(random_bytes(16));
        $expiresIn = 300; // 5 minutes

        // Store session in Cache
        Cache::put('email_otp_session_' . $sessionId, [
            'user_id' => $user->user_id,
            'user_nik' => $user->user_nik,
            'email' => $user->user_email,
            'otp_code' => $otpCode,
            'expires_at' => now()->addSeconds($expiresIn)->timestamp,
        ], now()->addSeconds($expiresIn + 30));

        // 6. Send OTP Email via SMTP
        $isSimulation = false;
        try {
            Mail::to($user->user_email)->send(new OtpMail($user->user_nama, $otpCode, $expiresIn));
        } catch (\Throwable $e) {
            Log::warning('SMTP Mail sending failed: ' . $e->getMessage(), [
                'user_id' => $user->user_id,
                'email' => $user->user_email,
            ]);

            // In local/testing environment, fallback gracefully if outbound SMTP port is blocked
            if (app()->environment('local', 'testing')) {
                $isSimulation = true;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirimkan kode OTP ke email Anda. Silakan coba kembali sesaat lagi.',
                    'error' => config('app.debug') ? $e->getMessage() : null,
                ], 500);
            }
        }

        $maskedEmail = $this->maskEmail($user->user_email);

        $responsePayload = [
            'success' => true,
            'message' => "Kredensial dan reCAPTCHA valid. Kode OTP 6 digit telah dikirimkan ke email {$maskedEmail}.",
            'step' => 'otp_verification',
            'data' => [
                'otp_session_id' => $sessionId,
                'request_id' => $sessionId, // alias for backward compatibility
                'channel' => 'email',
                'masked_email' => $maskedEmail,
                'expires_in' => $expiresIn,
                'action_guide' => "Buka kotak masuk atau folder spam email {$maskedEmail}, salin 6 digit kode OTP, lalu verifikasi pada endpoint /api/v1/verify-otp.",
            ],
        ];

        if ($isSimulation) {
            $responsePayload['is_simulation'] = true;
            $responsePayload['note'] = 'Mode Pengujian Lokal: Koneksi SMTP ke internet dibatasi oleh firewall, kode OTP tersimpan di sesi untuk verifikasi.';
            $responsePayload['data']['preview_otp'] = $otpCode;
        }

        return response()->json($responsePayload, 200);
    }

    /**
     * Step 2: Validate 6-Digit Email OTP Code & Issue JWT Bearer Token.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'otp_session_id' => ['nullable', 'string'],
            'request_id' => ['nullable', 'string'],
            'otp_code' => ['nullable', 'string'],
            'code' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $sessionId = $request->input('otp_session_id') ?: $request->input('request_id');
        $inputCode = trim((string) ($request->input('otp_code') ?: $request->input('code')));

        if (empty($sessionId)) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter otp_session_id (atau request_id) wajib disertakan.',
            ], 422);
        }

        if (empty($inputCode)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP 6 digit wajib dimasukkan.',
            ], 422);
        }

        $session = Cache::get('email_otp_session_' . $sessionId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'status' => 'expired',
                'message' => 'Sesi verifikasi OTP tidak ditemukan atau telah kedaluwarsa. Silakan lakukan login ulang.',
            ], 410);
        }

        // Validate OTP code
        if ($inputCode !== (string) $session['otp_code']) {
            $failedAttempts = ($session['failed_attempts'] ?? 0) + 1;
            if ($failedAttempts >= 5) {
                Cache::forget('email_otp_session_' . $sessionId);
                return response()->json([
                    'success' => false,
                    'status' => 'locked',
                    'message' => 'Terlalu banyak percobaan kode OTP yang salah. Sesi telah dibatalkan demi keamanan. Silakan login ulang.',
                ], 422);
            }

            $session['failed_attempts'] = $failedAttempts;
            $remainingSeconds = max(1, ($session['expires_at'] ?? now()->timestamp) - now()->timestamp);
            Cache::put('email_otp_session_' . $sessionId, $session, $remainingSeconds);

            $attemptsLeft = 5 - $failedAttempts;
            return response()->json([
                'success' => false,
                'status' => 'invalid_code',
                'message' => "Kode OTP yang Anda masukkan salah. Sisa kesempatan: {$attemptsLeft} kali.",
                'attempts_left' => $attemptsLeft,
            ], 422);
        }

        /** @var User|null $user */
        $user = User::with(['pasien.poli', 'dokter.poli', 'staf'])->find($session['user_id']);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan dalam sistem.',
            ], 404);
        }

        // Issue JWT token
        $token = $this->jwtService->generateToken($user);
        Cache::forget('email_otp_session_' . $sessionId);

        return response()->json([
            'success' => true,
            'message' => 'Verifikasi OTP email berhasil! Selamat datang di Portal KLINIKU.',
            'data' => [
                'status' => 'verified',
                'user' => [
                    'user_id' => $user->user_id,
                    'user_role' => $user->user_role,
                    'user_nama' => $user->user_nama,
                    'user_nik' => $user->user_nik,
                    'user_nowa' => $user->user_nowa,
                    'user_email' => $user->user_email,
                    'user_status' => $user->user_status,
                    'profile' => $this->buildProfileData($user),
                ],
                'authorization' => [
                    'token' => $token,
                    'type' => 'Bearer',
                    'expires_in' => $this->jwtService->getTtl(),
                ],
            ],
        ], 200);
    }

    /**
     * Resend verification request (send new OTP code).
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $sessionId = $request->input('otp_session_id') ?: $request->input('request_id');
        $session = $sessionId ? Cache::get('email_otp_session_' . $sessionId) : null;

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi sebelumnya tidak ditemukan atau telah kedaluwarsa. Silakan login kembali.',
            ], 404);
        }

        $user = User::find($session['user_id']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        Cache::forget('email_otp_session_' . $sessionId);

        $newOtpCode = sprintf('%06d', random_int(100000, 999999));
        $newSessionId = 'otp_' . bin2hex(random_bytes(16));
        $expiresIn = 300;

        Cache::put('email_otp_session_' . $newSessionId, [
            'user_id' => $user->user_id,
            'user_nik' => $user->user_nik,
            'email' => $user->user_email,
            'otp_code' => $newOtpCode,
            'expires_at' => now()->addSeconds($expiresIn)->timestamp,
        ], now()->addSeconds($expiresIn + 30));

        $isSimulation = false;
        try {
            Mail::to($user->user_email)->send(new OtpMail($user->user_nama, $newOtpCode, $expiresIn));
        } catch (\Throwable $e) {
            Log::warning('Resend SMTP Mail failed: ' . $e->getMessage());
            if (app()->environment('local', 'testing')) {
                $isSimulation = true;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengirimkan kode OTP baru ke email Anda.',
                ], 500);
            }
        }

        $maskedEmail = $this->maskEmail($user->user_email);

        $responsePayload = [
            'success' => true,
            'message' => "Kode OTP baru telah berhasil dikirimkan ke email {$maskedEmail}.",
            'data' => [
                'otp_session_id' => $newSessionId,
                'request_id' => $newSessionId,
                'channel' => 'email',
                'masked_email' => $maskedEmail,
                'expires_in' => $expiresIn,
            ],
        ];

        if ($isSimulation) {
            $responsePayload['is_simulation'] = true;
            $responsePayload['data']['preview_otp'] = $newOtpCode;
        }

        return response()->json($responsePayload, 200);
    }

    /**
     * Get Current Authenticated User Profile (Protected).
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User|null $user */
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak terotentikasi.',
            ], 401);
        }

        $user->loadMissing(['pasien.poli', 'dokter.poli', 'staf']);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->user_id,
                'user_role' => $user->user_role,
                'user_nama' => $user->user_nama,
                'user_nik' => $user->user_nik,
                'user_nowa' => $user->user_nowa,
                'user_email' => $user->user_email,
                'user_status' => $user->user_status,
                'profile' => $this->buildProfileData($user),
            ],
        ], 200);
    }

    /**
     * Terminate / Logout Session (Protected).
     */
    public function logout(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sesi Anda telah diakhiri.',
        ], 200);
    }

    /**
     * Build polymorphic profile data according to user role.
     */
    protected function buildProfileData(User $user): ?array
    {
        if ($user->user_role === 'pasien' && $user->pasien) {
            return [
                'norm' => $user->pasien->pasien_norm,
                'jeniskelamin' => $user->pasien->pasien_jeniskelamin,
                'tgllahir' => $user->pasien->pasien_tgllahir ? (is_string($user->pasien->pasien_tgllahir) ? substr($user->pasien->pasien_tgllahir, 0, 10) : $user->pasien->pasien_tgllahir->format('Y-m-d')) : null,
                'alamat' => $user->pasien->pasien_alamat,
                'nobpjs' => $user->pasien->pasien_nobpjs,
                'goldarah' => $user->pasien->pasien_goldarah,
                'alergiobat' => $user->pasien->pasien_alergiobat,
                'poli' => $user->pasien->poli?->poli_nama,
            ];
        }

        if ($user->user_role === 'dokter' && $user->dokter) {
            return [
                'sip' => $user->dokter->dokter_sip,
                'spesialisasi' => $user->dokter->dokter_spesialisasi,
                'poli' => $user->dokter->poli?->poli_nama,
            ];
        }

        if ($user->user_role === 'staf' && $user->staf) {
            return [
                'jabatan' => $user->staf->staf_jabatan,
                'shift' => $user->staf->staf_shift,
            ];
        }

        return null;
    }

    /**
     * Helper to mask email address for privacy.
     * e.g., lintarkertapati@student.uns.ac.id -> l***i@student.uns.ac.id
     */
    protected function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) {
            return '****@****';
        }

        $username = $parts[0];
        $domain = $parts[1];

        $len = strlen($username);
        if ($len <= 2) {
            $maskedUsername = substr($username, 0, 1) . '*';
        } else {
            $maskedUsername = substr($username, 0, 1) . str_repeat('*', min(4, $len - 2)) . substr($username, -1);
        }

        return $maskedUsername . '@' . $domain;
    }
}
