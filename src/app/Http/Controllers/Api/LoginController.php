<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use App\Services\FlowKirimService;
use App\Services\JwtService;
use App\Services\RecaptchaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
{
    protected RecaptchaService $recaptchaService;
    protected JwtService $jwtService;
    protected FlowKirimService $flowKirimService;

    public function __construct(
        RecaptchaService $recaptchaService,
        JwtService $jwtService,
        FlowKirimService $flowKirimService
    ) {
        $this->recaptchaService = $recaptchaService;
        $this->jwtService = $jwtService;
        $this->flowKirimService = $flowKirimService;
    }

    /**
     * Step 1: Submit Credentials & ReCaptcha, start FlowKirim WhatsApp Tap-to-Verify.
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

        // 4. Trigger FlowKirim Verify Start (whatsapp_tap)
        $verifyResult = $this->flowKirimService->startVerification($user->user_nowa, 'whatsapp_tap');

        // Handle successful FlowKirim start
        if ($verifyResult['success'] ?? false) {
            $verifyData = $verifyResult['data'];
            $requestId = $verifyData['request_id'];
            $expiresIn = $verifyData['expires_in'] ?? 300;

            // Store verification session in cache
            Cache::put('flowkirim_session_' . $requestId, [
                'user_id' => $user->user_id,
                'user_nik' => $user->user_nik,
                'phone' => $user->user_nowa,
                'expires_at' => now()->addSeconds($expiresIn)->timestamp,
            ], now()->addSeconds($expiresIn + 60));

            return response()->json([
                'success' => true,
                'message' => 'Kredensial dan reCAPTCHA valid. Silakan selesaikan verifikasi via WhatsApp untuk masuk.',
                'step' => 'otp_verification',
                'data' => [
                    'request_id' => $requestId,
                    'channel' => $verifyData['channel'] ?? 'whatsapp_tap',
                    'masked_phone' => $verifyData['masked_phone'] ?? '*********' . substr($user->user_nowa, -4),
                    'wa_deeplink' => $verifyData['wa_deeplink'] ?? null,
                    'wa_number' => $verifyData['wa_number'] ?? null,
                    'expires_in' => $expiresIn,
                    'action_guide' => 'Buka tautan wa_deeplink di WhatsApp lalu tekan Kirim. Kemudian periksa status dengan memanggil endpoint /api/v1/verify-otp.',
                ],
            ], 200);
        }

        // Handle INSUFFICIENT_BALANCE or errors from FlowKirim
        $errorCode = $verifyResult['error']['code'] ?? 'UNKNOWN_ERROR';
        $errorMessage = $verifyResult['error']['message'] ?? 'Gagal memulai verifikasi FlowKirim.';

        // In testing / local development, provide simulated session if wallet balance is zero
        if (app()->environment('local', 'testing') && $errorCode === 'INSUFFICIENT_BALANCE') {
            $mockRequestId = 'mock-verify-' . bin2hex(random_bytes(8));
            Cache::put('flowkirim_session_' . $mockRequestId, [
                'user_id' => $user->user_id,
                'user_nik' => $user->user_nik,
                'phone' => $user->user_nowa,
                'expires_at' => now()->addSeconds(300)->timestamp,
            ], 360);

            return response()->json([
                'success' => true,
                'is_simulation' => true,
                'message' => 'Kredensial valid. [SIMULASI TESTING: Saldo FlowKirim habis, sesi simulasi WhatsApp Tap dibuat otomatis agar pengujian dapat berjalan].',
                'step' => 'otp_verification',
                'data' => [
                    'request_id' => $mockRequestId,
                    'channel' => 'whatsapp_tap',
                    'masked_phone' => '*********' . substr($user->user_nowa, -4),
                    'wa_deeplink' => 'https://wa.me/6285113736221?text=VERIFY-SIMULATED-' . substr($mockRequestId, -8),
                    'expires_in' => 300,
                    'note' => 'Top up saldo FlowKirim untuk mengaktifkan pengiriman WhatsApp live.',
                ],
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Kredensial Anda benar, namun pengiriman verifikasi WhatsApp melalui FlowKirim belum dapat diproses (' . $errorCode . ': ' . $errorMessage . ').',
            'error' => $verifyResult['error'] ?? null,
        ], $verifyResult['http_status'] ?? 502);
    }

    /**
     * Step 2: Validate OTP / Poll Tap-to-Verify Status.
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'request_id' => ['required', 'string'],
            'code' => ['nullable', 'string', 'min:4', 'max:8'],
        ], [
            'request_id.required' => 'Parameter request_id wajib disertakan.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter tidak valid.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $requestId = $request->input('request_id');
        $session = Cache::get('flowkirim_session_' . $requestId);

        if (!$session) {
            return response()->json([
                'success' => false,
                'status' => 'expired',
                'message' => 'Sesi verifikasi tidak ditemukan atau telah kedaluwarsa. Silakan lakukan login ulang.',
            ], 410);
        }

        // Verify with FlowKirim: either manual code check or polling status
        if ($request->filled('code')) {
            $checkResult = $this->flowKirimService->checkCode($requestId, $request->input('code'));
        } else {
            $checkResult = $this->flowKirimService->checkStatus($requestId);
        }

        $status = $checkResult['status'] ?? 'pending';

        if ($status === 'verified') {
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
            Cache::forget('flowkirim_session_' . $requestId);

            return response()->json([
                'success' => true,
                'message' => 'Verifikasi WhatsApp berhasil! Selamat datang di Portal KLINIKU.',
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

        if ($status === 'pending') {
            return response()->json([
                'success' => false,
                'status' => 'pending',
                'message' => 'Menunggu verifikasi WhatsApp. Silakan buka tautan wa_deeplink dan kirim pesan verifikasi.',
            ], 202);
        }

        if ($status === 'expired') {
            Cache::forget('flowkirim_session_' . $requestId);
            return response()->json([
                'success' => false,
                'status' => 'expired',
                'message' => 'Sesi verifikasi WhatsApp telah kedaluwarsa. Silakan lakukan login ulang.',
            ], 410);
        }

        return response()->json([
            'success' => false,
            'status' => $status,
            'message' => 'Kode atau status verifikasi tidak valid (' . $status . '). Silakan coba kembali.',
        ], 422);
    }

    /**
     * Resend verification request.
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $requestId = $request->input('request_id');
        $session = $requestId ? Cache::get('flowkirim_session_' . $requestId) : null;

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi verifikasi sebelumnya tidak ditemukan. Silakan login kembali.',
            ], 404);
        }

        $user = User::find($session['user_id']);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Pengguna tidak ditemukan.',
            ], 404);
        }

        Cache::forget('flowkirim_session_' . $requestId);

        $verifyResult = $this->flowKirimService->startVerification($user->user_nowa, 'whatsapp_tap');

        if ($verifyResult['success'] ?? false) {
            $newRequestId = $verifyResult['data']['request_id'];
            $expiresIn = $verifyResult['data']['expires_in'] ?? 300;

            Cache::put('flowkirim_session_' . $newRequestId, [
                'user_id' => $user->user_id,
                'user_nik' => $user->user_nik,
                'phone' => $user->user_nowa,
                'expires_at' => now()->addSeconds($expiresIn)->timestamp,
            ], now()->addSeconds($expiresIn + 60));

            return response()->json([
                'success' => true,
                'message' => 'Permintaan verifikasi WhatsApp baru berhasil dibuat.',
                'data' => $verifyResult['data'],
            ]);
        }

        // Local simulation fallback
        if (app()->environment('local', 'testing')) {
            $mockRequestId = 'mock-verify-' . bin2hex(random_bytes(8));
            Cache::put('flowkirim_session_' . $mockRequestId, [
                'user_id' => $user->user_id,
                'user_nik' => $user->user_nik,
                'phone' => $user->user_nowa,
                'expires_at' => now()->addSeconds(300)->timestamp,
            ], 360);

            return response()->json([
                'success' => true,
                'is_simulation' => true,
                'message' => 'Permintaan verifikasi simulasi dibuat.',
                'data' => [
                    'request_id' => $mockRequestId,
                    'channel' => 'whatsapp_tap',
                    'wa_deeplink' => 'https://wa.me/6285113736221?text=VERIFY-SIMULATED',
                    'expires_in' => 300,
                ],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim ulang verifikasi via FlowKirim.',
            'error' => $verifyResult['error'] ?? null,
        ], 502);
    }

    /**
     * Get authenticated user profile using Bearer JWT token.
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();

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
        ]);
    }

    /**
     * End user session / logout.
     */
    public function logout(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil. Sesi Anda telah diakhiri.',
        ]);
    }

    /**
     * Helper to format profile data by role.
     */
    protected function buildProfileData(User $user): ?array
    {
        if ($user->user_role === 'pasien' && $user->pasien) {
            return [
                'norm' => $user->pasien->pasien_norm,
                'jeniskelamin' => $user->pasien->pasien_jeniskelamin,
                'tgllahir' => $user->pasien->pasien_tgllahir,
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
                'kuotaharian' => $user->dokter->dokter_kuotaharian,
                'poli' => $user->dokter->poli?->poli_nama,
            ];
        }

        if ($user->user_role === 'staf' && $user->staf) {
            return [
                'jabatan' => $user->staf->staf_jabatan,
                'shift' => $user->staf->staf_shiftnama,
                'jam_kerja' => $user->staf->staf_shiftmulai . ' - ' . $user->staf->staf_shiftselesai,
            ];
        }

        return null;
    }
}
