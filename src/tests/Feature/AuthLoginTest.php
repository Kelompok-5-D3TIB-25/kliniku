<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FlowKirimService;
use App\Services\JwtService;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
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

    public function test_successful_patient_login_initiates_flowkirim_whatsapp_tap_verification(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658', // Yusuf Hidayat (Patient 1)
            'password' => 'Pasien123!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'step' => 'otp_verification',
        ]);

        $requestId = $response->json('data.request_id');
        $this->assertNotEmpty($requestId);
        $this->assertEquals('whatsapp_tap', $response->json('data.channel'));
        $this->assertNotEmpty($response->json('data.wa_deeplink'));
    }

    public function test_verify_otp_status_verified_issues_jwt_and_profile(): void
    {
        $loginResponse = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'Pasien123!',
            'recaptcha_token' => 'test-valid-token',
        ]);
        $requestId = $loginResponse->json('data.request_id');

        $response = $this->postJson('/api/v1/verify-otp', [
            'request_id' => $requestId,
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

    public function test_verify_otp_status_pending_returns_202(): void
    {
        $this->mock(FlowKirimService::class, function ($mock) {
            $mock->shouldReceive('checkStatus')->with('mock-pending-req')->once()->andReturn([
                'success' => true,
                'status' => 'pending',
            ]);
        });

        Cache::put('flowkirim_session_mock-pending-req', [
            'user_id' => 'USR-PAS-0001',
            'user_nik' => '3372020912254658',
            'phone' => '085113736221',
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], 300);

        $response = $this->postJson('/api/v1/verify-otp', [
            'request_id' => 'mock-pending-req',
        ]);

        $response->assertStatus(202);
        $response->assertJson([
            'success' => false,
            'status' => 'pending',
        ]);
    }

    public function test_verify_otp_expired_or_invalid_request_id_returns_410(): void
    {
        $response = $this->postJson('/api/v1/verify-otp', [
            'request_id' => 'non-existent-or-expired-request',
        ]);

        $response->assertStatus(410);
        $response->assertJson([
            'success' => false,
            'status' => 'expired',
        ]);
    }

    public function test_resend_otp_creates_new_session(): void
    {
        Cache::put('flowkirim_session_old-request-id', [
            'user_id' => 'USR-PAS-0001',
            'user_nik' => '3372020912254658',
            'phone' => '085113736221',
            'expires_at' => now()->addMinutes(5)->timestamp,
        ], 300);

        $response = $this->postJson('/api/v1/resend-otp', [
            'request_id' => 'old-request-id',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $newRequestId = $response->json('data.request_id');
        $this->assertNotEmpty($newRequestId);
        $this->assertNotEquals('old-request-id', $newRequestId);
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
