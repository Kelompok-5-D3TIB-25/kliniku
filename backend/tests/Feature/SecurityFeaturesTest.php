<?php

namespace Tests\Feature;

use App\Models\Pasien;
use App\Models\User;
use App\Services\RecaptchaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class SecurityFeaturesTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        // Clear rate limiters and cache before each test
        RateLimiter::clear('3372020912254658|127.0.0.1');
        RateLimiter::clear('api|127.0.0.1');
        RateLimiter::clear('otp_verify|127.0.0.1');
        RateLimiter::clear('otp_resend|127.0.0.1');
        Cache::flush();
    }

    /*
    |--------------------------------------------------------------------------
    | 1. PASSWORD SECURITY TESTS
    |--------------------------------------------------------------------------
    */

    public function test_registered_user_password_is_hashed_with_bcrypt_and_never_plaintext(): void
    {
        $plainPassword = 'MySecretPassword123!';
        $nik = '3372091508980099';

        // Clean up test NIK
        User::where('user_nik', $nik)->delete();

        $response = $this->postJson('/api/v1/register', [
            'nik' => $nik,
            'nama' => 'Bcrypt Security Verification',
            'email' => 'bcrypt.test@kliniku.id',
            'nowa' => '081299887766',
            'password' => $plainPassword,
            'alamat' => 'Jl. Keamanan Siber No. 1, Surakarta',
        ]);

        $response->assertStatus(201);

        // Fetch user directly from database
        $user = User::where('user_nik', $nik)->first();
        $this->assertNotNull($user);

        // 1. Password must NEVER equal plaintext
        $this->assertNotEquals($plainPassword, $user->user_password);

        // 2. Password must start with $2y$ (Bcrypt hash prefix)
        $this->assertStringStartsWith('$2y$', $user->user_password);

        // 3. Password hash must verify with Bcrypt Hash::check
        $this->assertTrue(Hash::check($plainPassword, $user->user_password));

        // 4. Verification fails on wrong password
        $this->assertFalse(Hash::check('WrongPassword123!', $user->user_password));
    }

    public function test_user_password_is_hidden_from_array_and_json_serialization(): void
    {
        $user = User::where('user_role', 'pasien')->first();
        $this->assertNotNull($user);

        // Serialization to Array must not expose user_password
        $userArray = $user->toArray();
        $this->assertArrayNotHasKey('user_password', $userArray);

        // Serialization to JSON must not expose user_password
        $userJson = json_encode($user);
        $this->assertStringNotContainsString('user_password', $userJson);
    }

    /*
    |--------------------------------------------------------------------------
    | 2. CAPTCHA VERIFICATION TESTS
    |--------------------------------------------------------------------------
    */

    public function test_login_succeeds_with_valid_captcha_token(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'Pasien123!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'step' => 'otp_verification',
        ]);
    }

    public function test_login_fails_with_invalid_captcha_token(): void
    {
        // Mock RecaptchaService returning false for invalid token
        $this->mock(RecaptchaService::class, function ($mock) {
            $mock->shouldReceive('verify')->andReturn(false);
        });

        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'Pasien123!',
            'recaptcha_token' => 'invalid-bot-challenge-token',
        ]);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'errors' => [
                'recaptcha_token' => ['Verifikasi bot reCAPTCHA tidak valid atau telah kedaluwarsa.'],
            ],
        ]);
    }

    public function test_login_fails_when_captcha_token_is_missing(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'nik' => '3372020912254658',
            'password' => 'Pasien123!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['recaptcha_token']);
    }

    /*
    |--------------------------------------------------------------------------
    | 3. ONE-TIME PASSWORD (OTP) TESTS (TIME-BOUND & SINGLE-USE)
    |--------------------------------------------------------------------------
    */

    public function test_otp_is_single_use_and_cannot_be_reused_after_successful_verification(): void
    {
        $sessionId = 'test_single_use_session_' . uniqid();
        $otpCode = '654321';
        $user = User::where('user_role', 'pasien')->first();

        // Put active OTP session into cache with 300s TTL
        Cache::put('email_otp_session_' . $sessionId, [
            'user_id' => $user->user_id,
            'user_nik' => $user->user_nik,
            'email' => $user->user_email,
            'otp_code' => $otpCode,
            'expires_at' => now()->addSeconds(300)->timestamp,
        ], 300);

        // First verification attempt: MUST SUCCEED (200 OK)
        $firstAttempt = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => $sessionId,
            'otp_code' => $otpCode,
        ]);
        $firstAttempt->assertStatus(200);
        $firstAttempt->assertJson([
            'success' => true,
            'data' => ['status' => 'verified'],
        ]);

        // Second verification attempt with same OTP & session: MUST FAIL (410 Gone / Expired)
        $secondAttempt = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => $sessionId,
            'otp_code' => $otpCode,
        ]);
        $secondAttempt->assertStatus(410);
        $secondAttempt->assertJson([
            'success' => false,
            'status' => 'expired',
        ]);
    }

    public function test_otp_cannot_be_verified_after_expiration(): void
    {
        $sessionId = 'expired_session_' . uniqid();
        // Do not put in cache or let it expire
        $response = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => $sessionId,
            'otp_code' => '123456',
        ]);

        $response->assertStatus(410);
        $response->assertJson([
            'success' => false,
            'status' => 'expired',
        ]);
    }

    public function test_otp_session_is_locked_and_cleared_after_5_failed_attempts(): void
    {
        // Disable throttle middleware to specifically test OTP session-level lockout logic
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $sessionId = 'brute_force_otp_session_' . uniqid();
        $correctCode = '999111';
        $user = User::where('user_role', 'pasien')->first();

        Cache::put('email_otp_session_' . $sessionId, [
            'user_id' => $user->user_id,
            'user_nik' => $user->user_nik,
            'email' => $user->user_email,
            'otp_code' => $correctCode,
            'expires_at' => now()->addSeconds(300)->timestamp,
        ], 300);

        // Attempt 1 to 4: returns 422 with remaining attempts
        for ($i = 1; $i <= 4; $i++) {
            $res = $this->postJson('/api/v1/verify-otp', [
                'otp_session_id' => $sessionId,
                'otp_code' => '000000',
            ]);
            $res->assertStatus(422);
            $res->assertJson([
                'success' => false,
                'status' => 'invalid_code',
                'attempts_left' => 5 - $i,
            ]);
        }

        // 5th failed attempt: session is locked and destroyed
        $fifthAttempt = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => $sessionId,
            'otp_code' => '000000',
        ]);
        $fifthAttempt->assertStatus(422);
        $fifthAttempt->assertJson([
            'success' => false,
            'status' => 'locked',
        ]);

        // Subsequent attempt with even the CORRECT code now fails because session was destroyed
        $aftermathAttempt = $this->postJson('/api/v1/verify-otp', [
            'otp_session_id' => $sessionId,
            'otp_code' => $correctCode,
        ]);
        $aftermathAttempt->assertStatus(410);
    }

    /*
    |--------------------------------------------------------------------------
    | 4. SQL INJECTION PREVENTION TESTS
    |--------------------------------------------------------------------------
    */

    public function test_login_resists_sql_injection_payloads(): void
    {
        $sqlInjectionPayloads = [
            "' OR '1'='1",
            "3372020912254658' OR '1'='1' --",
            "'; DROP TABLE user; --",
            "' UNION SELECT * FROM user --",
            "1' OR 1=1 #",
            "' OR ''='",
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $response = $this->postJson('/api/v1/login', [
                'nik' => $payload,
                'password' => $payload,
                'recaptcha_token' => 'test-valid-token',
            ]);

            // Must be rejected by validation (422) or authentication (401), NEVER execute raw SQL or return 500
            $this->assertContains($response->status(), [401, 422]);
            $response->assertJson(['success' => false]);
        }

        // Assert user table still exists and remains completely intact
        $this->assertGreaterThan(0, User::count());
    }

    public function test_check_nik_resists_sql_injection_payloads(): void
    {
        $payloads = [
            "' OR '1'='1",
            "3372020912254658' OR 1=1 --",
            "'; DROP TABLE pasien; --",
        ];

        foreach ($payloads as $payload) {
            $response = $this->postJson('/api/v1/check-nik', [
                'nik' => $payload,
            ]);

            $response->assertStatus(422);
            $response->assertJson([
                'success' => false,
                'available' => false,
            ]);
        }

        // Assert pasien table still exists and data remains intact
        $this->assertGreaterThan(0, Pasien::count());
    }

    public function test_registration_resists_sql_injection_in_text_fields(): void
    {
        $cleanNik = '3372091508980077';
        User::where('user_nik', $cleanNik)->delete();

        $sqlPayload = "Budi Hartono'; DROP TABLE user; --";

        $response = $this->postJson('/api/v1/register', [
            'nik' => $cleanNik,
            'nama' => $sqlPayload,
            'email' => 'sqli.test@kliniku.id',
            'nowa' => '081299998888',
            'password' => 'SafePassword123!',
            'alamat' => "Jl. Raya No. 1' OR 1=1; --",
        ]);

        $response->assertStatus(201);

        // Verify that the payload is saved safely as LITERAL TEXT without executing SQL
        $createdUser = User::where('user_nik', $cleanNik)->first();
        $this->assertNotNull($createdUser);
        $this->assertEquals($sqlPayload, $createdUser->user_nama);

        // Verify user table was not dropped
        $this->assertTrue(DB::getSchemaBuilder()->hasTable('user'));
    }

    /*
    |--------------------------------------------------------------------------
    | 5. RATE LIMITING TESTS
    |--------------------------------------------------------------------------
    */

    public function test_login_is_rate_limited_after_5_attempts(): void
    {
        $nik = '3372020912254658';
        $ip = '127.0.0.1';
        $key = $nik . '|' . $ip;
        RateLimiter::clear($key);

        // Send 5 login attempts (all return 401 wrong password)
        for ($i = 1; $i <= 5; $i++) {
            $response = $this->postJson('/api/v1/login', [
                'nik' => $nik,
                'password' => 'WrongPassword!',
                'recaptcha_token' => 'test-valid-token',
            ]);
            $response->assertStatus(401);
        }

        // 6th attempt must be throttled with HTTP 429 Too Many Requests
        $throttledResponse = $this->postJson('/api/v1/login', [
            'nik' => $nik,
            'password' => 'WrongPassword!',
            'recaptcha_token' => 'test-valid-token',
        ]);

        $throttledResponse->assertStatus(429);
        $throttledResponse->assertJson([
            'success' => false,
            'message' => 'Terlalu banyak percobaan login gagal. Demi keamanan, silakan tunggu 1 menit sebelum mencoba kembali.',
        ]);
        $this->assertTrue($throttledResponse->headers->has('Retry-After'));
    }

    public function test_otp_resend_is_rate_limited_after_3_attempts(): void
    {
        $sessionId = 'resend_throttle_session_' . uniqid();
        RateLimiter::clear('otp_resend|127.0.0.1');
        RateLimiter::clear('api|127.0.0.1');

        $user = User::where('user_role', 'pasien')->first();
        Cache::put('email_otp_session_' . $sessionId, [
            'user_id' => $user->user_id,
            'user_nik' => $user->user_nik,
            'email' => $user->user_email,
            'otp_code' => '112233',
            'expires_at' => now()->addSeconds(300)->timestamp,
        ], 300);

        Mail::fake();

        // 3 resend attempts allowed
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->postJson('/api/v1/resend-otp', [
                'otp_session_id' => $sessionId,
            ]);
            // If session was replaced, keep testing with the new session or rate limiter key
            if ($response->status() === 200 && isset($response->json('data')['otp_session_id'])) {
                $sessionId = $response->json('data')['otp_session_id'];
            }
        }

        // 4th request on the same IP must be throttled with HTTP 429
        $throttledResponse = $this->postJson('/api/v1/resend-otp', [
            'otp_session_id' => $sessionId,
        ]);

        // Rate limiter returns 429
        $this->assertEquals(429, $throttledResponse->status());
        $throttledResponse->assertJson(['success' => false]);
    }
}
