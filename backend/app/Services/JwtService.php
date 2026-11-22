<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Log;

class JwtService
{
    protected string $secret;
    protected int $ttl;
    protected string $algorithm = 'HS256';

    public function __construct()
    {
        $this->secret = (string) (config('services.jwt.secret') ?: config('app.key', 'default-kliniku-jwt-secret-key-32b'));
        $this->ttl = (int) config('services.jwt.ttl', 86400);
    }

    /**
     * Generate signed JWT string for a user.
     */
    public function generateToken(User $user, ?int $customTtl = null): string
    {
        $ttl = $customTtl ?: $this->ttl;
        $issuedAt = time();
        $expiresAt = $issuedAt + $ttl;

        $payload = [
            'iss' => 'KLINIKU',
            'sub' => $user->user_id,
            'user_id' => $user->user_id,
            'nik' => $user->user_nik,
            'role' => $user->user_role,
            'nama' => $user->user_nama,
            'iat' => $issuedAt,
            'exp' => $expiresAt,
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    /**
     * Decode and validate JWT string. Returns payload array or null if invalid.
     */
    public function decodeToken(string $token): ?array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algorithm));
            return (array) $decoded;
        } catch (\Throwable $e) {
            Log::debug('JWT decode failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Resolve User instance from a valid JWT token.
     */
    public function getUserFromToken(string $token): ?User
    {
        $payload = $this->decodeToken($token);

        if (!$payload || empty($payload['sub'])) {
            return null;
        }

        return User::with(['pasien.poli', 'dokter.poli', 'staf'])->find($payload['sub']);
    }

    /**
     * Get default TTL in seconds.
     */
    public function getTtl(): int
    {
        return $this->ttl;
    }
}
