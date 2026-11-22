<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RecaptchaService
{
    protected string $secretKey;
    protected string $verifyUrl = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct()
    {
        $this->secretKey = config('services.recaptcha.secret_key', '6LfEiisuAAAAAEPuGqfaxFfGOtUGHNdZq0djWVkd');
    }

    /**
     * Verify the reCAPTCHA token with Google's siteverify API.
     */
    public function verify(?string $token, ?string $ip = null): bool
    {
        if (empty($token)) {
            return false;
        }

        // Allow test token in local / testing environments
        if (app()->environment('testing', 'local') && in_array($token, ['test-valid-token', 'passthrough-token', 'valid-recaptcha-token'], true)) {
            return true;
        }

        try {
            $response = Http::asForm()->timeout(6)->post($this->verifyUrl, [
                'secret' => $this->secretKey,
                'response' => $token,
                'remoteip' => $ip,
            ]);

            if ($response->successful()) {
                $body = $response->json();
                return (bool) ($body['success'] ?? false);
            }

            Log::warning('Google reCAPTCHA verification failed HTTP status', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Exception during reCAPTCHA verification: ' . $e->getMessage());
            return false;
        }
    }
}
