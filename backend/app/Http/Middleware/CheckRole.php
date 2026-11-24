<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     * Check if authenticated user's role matches any of the required roles.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Otentikasi diperlukan. Silakan login terlebih dahulu.',
            ], 401);
        }

        if (!in_array($user->user_role, $roles, true)) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak. Peran Anda (' . $user->user_role . ') tidak memiliki wewenang untuk mengakses sumber daya ini.',
            ], 403);
        }

        return $next($request);
    }
}
