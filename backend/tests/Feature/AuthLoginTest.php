<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use App\Services\JwtService;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    public function test_login_fails_when_required_fields_missing(): void
    {
        $response = $this->postJson('/api/v1/login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nik', 'password', 'recaptcha_token']);
    }

    public function test_login_fails_on_invalid_nik_format(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => '12345',
            'password' => 'Pasien123!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['nik']);
    }

    public function test_login_fails_when_recaptcha_verification_fails(): void
    {
        // Mock RecaptchaService returning false
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->once()->andReturn(false);
        });

        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'Pasien123!',
            'recaptcha_token' => 'invalid-bot-token',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'errors' => [
                'recaptcha_token' => ['Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa.'],
            ],
        ]);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'WrongPassword123!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Login gagal. NIK atau kata sandi yang Anda masukkan tidak sesuai.',
        ]);
    }

    public function test_login_fails_with_unregistered_nik(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => '9999999999999999',
            'password' => 'AnyPassword123!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'success' => false,
            'message' => 'Login gagal. NIK atau kata sandi yang Anda masukkan tidak sesuai.',
        ]);
    }

    public function test_successful_patient_login_sends_email_otp(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658', // Yusuf Hidayat (Patient 1)
            'password' => 'Pasien123!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'step' => 'otp_verification',
            'data' => [
                'channel' => 'email',
            ],
        ]);

        $sessionId = $response->json('data.otp_session_id');
        $this->assertNotEmpty($sessionId);

        // Verify email was sent
        Mail::assertSent(OtpMail::class, function ($mail) {
            return $mail->hasTo('lintarkertapati@student.uns.ac.id') && strlen($mail->otpCode) === 6;
        });
    }

    public function test_verify_otp_with_correct_code_issues_jwt_and_profile(): void
    {
        Mail::fake();

        $loginResponse = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'Pasien123!',
            'recaptcha_token' => 'test-valid-token',
        ]);
        $sessionId = $loginResponse->json('data.otp_session_id');
        $sessionData = Cache::get('email_otp_session_' . $sessionId);
        $this->assertNotNull($sessionData);
        $otpCode = $sessionData['otp_code'];

        $response = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => $sessionId,
            'otp_code' => $otpCode,
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'status' => 'verified',
                'user' => [
                    'user_id' => 'USR-PAS-0001',
                    'user_role' => 'pasien',
                    'user_nama' => 'Yusuf Hidayat',
                    'user_nik' => '3372020912254658',
                    'user_email' => 'lintarkertapati@student.uns.ac.id',
                    'user_status' => 'aktif',
                    'profile' => [
                        'norm' => 'RM-2026-0001',
                        'jeniskelamin' => 'Laki-laki',
                        'poli' => 'Poli KIA / Anak',
                    ],
                ],
                'authorization' => [
                    'type' => 'Bearer',
                    'expires_in' => 86400,
                ],
            ],
        ]);

        $token = $response->json('data.authorization.token');
        $this->assertNotEmpty($token);
    }

    public function test_verify_otp_fails_with_wrong_code(): void
    {
        Cache::put('email_otp_session_test-wrong', [
            'user_id' => 'USR-PAS-0001',
            'user_nik' => '3372020912254658',
            'email' => 'lintarkertapati@student.uns.ac.id',
            'otp_code' => '123456',
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], 300);

        $response = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => 'test-wrong',
            'otp_code' => '999999',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'status' => 'invalid_code',
        ]);
    }

    public function test_verify_otp_expired_or_invalid_session_returns_410(): void
    {
        $response = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => 'non-existent-or-expired-session',
            'otp_code' => '123456',
        ]);

        $response->assertStatus(410);
        $response->assertJson([
            'success' => false,
            'status' => 'expired',
        ]);
    }

    public function test_resend_otp_creates_new_session_and_sends_email(): void
    {
        Mail::fake();

        Cache::put('email_otp_session_old-session', [
            'user_id' => 'USR-PAS-0001',
            'user_nik' => '3372020912254658',
            'email' => 'lintarkertapati@student.uns.ac.id',
            'otp_code' => '111111',
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], 300);

        $response = $this->postJson('/api/v1/resend-otp', [
            'otp_session_id' => 'old-session',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $newSessionId = $response->json('data.otp_session_id');
        $this->assertNotEmpty($newSessionId);
        $this->assertNotEquals('old-session', $newSessionId);

        Mail::assertSent(OtpMail::class);
    }

    public function test_authenticated_me_endpoint_returns_user_profile(): void
    {
        $user = User::where('user_nik', '3372020912254658')->firstOrFail();
        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'user_id' => 'USR-PAS-0001',
                'user_nama' => 'Yusuf Hidayat',
                'user_email' => 'lintarkertapati@student.uns.ac.id',
                'profile' => [
                    'norm' => 'RM-2026-0001',
                ],
            ],
        ]);
    }

    public function test_protected_routes_reject_missing_or_invalid_token(): void
    {
        // Missing token
        $responseNoToken = $this->getJson('/api/v1/me');
        $responseNoToken->assertStatus(401);

        // Invalid token
        $responseInvalid = $this->withHeader('Authorization', 'Bearer invalid-token-string')
            ->getJson('/api/v1/me');
        $responseInvalid->assertStatus(401);
    }

    public function test_logout_endpoint_succeeds_with_bearer_token(): void
    {
        $user = User::where('user_nik', '3372020912254658')->firstOrFail();
        $jwtService = app(JwtService::class);
        $token = $jwtService->generateToken($user);

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'message' => 'Logout berhasil. Sesi Anda telah diakhiri.',
        ]);
    }
}
