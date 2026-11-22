<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FlowKirimService
{
    protected string $apiKey;
    protected string $baseUrl;
    protected string $defaultChannel;
    protected string $brand;
    protected int $defaultTtl;

    public function __construct()
    {
        $this->apiKey = (string) config('services.flowkirim.api_key', 'fk_live_25ebb5646a8d7afd3d9e526d394f81098a8e48b0ed355b0a');
        $this->baseUrl = rtrim((string) config('services.flowkirim.base_url', 'https://scan.flowkirim.com/api/v1/verify'), '/');
        $this->defaultChannel = (string) config('services.flowkirim.channel', 'whatsapp_tap');
        $this->brand = (string) config('services.flowkirim.brand', 'KLINIKU');
        $this->defaultTtl = (int) config('services.flowkirim.ttl', 300);
    }

    /**
     * Start verification (Tap-to-Verify or OTP code).
     */
    public function startVerification(
        string $phone,
        ?string $channel = null,
        ?string $brand = null,
        ?int $ttl = null,
        ?string $idempotencyKey = null
    ): array {
        $channel = $channel ?: $this->defaultChannel;
        $brand = $brand ?: $this->brand;
        $ttl = $ttl ?: $this->defaultTtl;

        // Normalize phone number (FlowKirim accepts 08xx or 62xx)
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        // Support simulated verification for testing or local simulation
        if (app()->environment('testing') && str_starts_with($phone, '62999')) {
            return [
                'success' => true,
                'is_mock' => true,
                'data' => [
                    'request_id' => 'mock-verify-' . bin2hex(random_bytes(8)),
                    'channel' => $channel,
                    'expires_in' => $ttl,
                    'masked_phone' => '*********' . substr($phone, -4),
                    'wa_number' => '6285113736221',
                    'wa_deeplink' => 'https://wa.me/6285113736221?text=VERIFY-KLINIKU-MOCK',
                ],
            ];
        }

        try {
            $headers = [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ];

            if ($idempotencyKey) {
                $headers['Idempotency-Key'] = $idempotencyKey;
            }

            $response = Http::withHeaders($headers)
                ->timeout(8)
                ->post($this->baseUrl . '/start', [
                    'phone' => $phone,
                    'channel' => $channel,
                    'brand' => substr($brand, 0, 40),
                    'ttl' => $ttl,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'data' => $response->json('data'),
                ];
            }

            $error = $response->json('error') ?? [
                'code' => 'HTTP_' . $response->status(),
                'message' => $response->body(),
            ];

            Log::warning('FlowKirim start verification returned error', [
                'status' => $response->status(),
                'error' => $error,
            ]);

            return [
                'success' => false,
                'http_status' => $response->status(),
                'error' => $error,
            ];
        } catch (\Throwable $e) {
            Log::error('FlowKirim API connection exception: ' . $e->getMessage());

            return [
                'success' => false,
                'http_status' => 503,
                'error' => [
                    'code' => 'SERVICE_UNAVAILABLE',
                    'message' => 'Gagal terhubung ke server verifikasi FlowKirim.',
                ],
            ];
        }
    }

    /**
     * Poll / check verification status by request_id (for whatsapp_tap).
     */
    public function checkStatus(string $requestId): array
    {
        // Handle mock request_id in testing
        if (str_starts_with($requestId, 'mock-verify-')) {
            return [
                'success' => true,
                'status' => 'verified',
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(6)->get($this->baseUrl . '/' . $requestId);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('data.status'),
                ];
            }

            return [
                'success' => false,
                'http_status' => $response->status(),
                'error' => $response->json('error') ?? ['message' => 'Gagal memeriksa status verifikasi.'],
            ];
        } catch (\Throwable $e) {
            Log::error('FlowKirim checkStatus exception: ' . $e->getMessage());

            return [
                'success' => false,
                'http_status' => 503,
                'error' => ['message' => 'Gagal menghubungi server verifikasi FlowKirim.'],
            ];
        }
    }

    /**
     * Verify manual OTP code by request_id and code.
     */
    public function checkCode(string $requestId, string $code): array
    {
        if (str_starts_with($requestId, 'mock-verify-')) {
            $status = ($code === '1234' || $code === '123456') ? 'verified' : 'invalid';
            return [
                'success' => true,
                'status' => $status,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(6)->post($this->baseUrl . '/check', [
                'request_id' => $requestId,
                'code' => $code,
            ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('data.status'),
                ];
            }

            return [
                'success' => false,
                'http_status' => $response->status(),
                'error' => $response->json('error') ?? ['message' => 'Gagal memverifikasi kode.'],
            ];
        } catch (\Throwable $e) {
            Log::error('FlowKirim checkCode exception: ' . $e->getMessage());

            return [
                'success' => false,
                'http_status' => 503,
                'error' => ['message' => 'Gagal menghubungi server verifikasi.'],
            ];
        }
    }
}
