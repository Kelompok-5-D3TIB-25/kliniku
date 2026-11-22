<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JwtAuthenticate
{
    protected JwtService $jwtService;

    public function __construct(JwtService $jwtService)
    {
        $this->jwtService = $jwtService;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token otentikasi (Bearer token) tidak disertakan. Silakan login terlebih dahulu.',
            ], 401);
        }

        $user = $this->jwtService->getUserFromToken($token);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Token otentikasi tidak valid atau telah kedaluwarsa. Silakan login kembali.',
            ], 401);
        }

        if ($user->user_status !== 'aktif') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda berstatus non-aktif. Silakan hubungi pihak administrasi KLINIKU.',
            ], 403);
        }

        // Bind user to Laravel auth & request
        auth()->setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
