<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateApiToken
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Ambil Bearer Token dari Header HTTP
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token autentikasi tidak ditemukan. Silakan login kembali.'
            ], 401);
        }

        // 2. Hash token yang dikirimkan menggunakan SHA-256 untuk dicocokkan ke database
        $hashedToken = hash('sha256', $token);
        $user = User::with('role')->where('api_token', $hashedToken)->where('status', 'active')->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Sesi login tidak valid atau telah berakhir. Silakan login ulang.'
            ], 401);
        }

        // 3. Daftarkan user ke sistem Auth Laravel
        Auth::setUser($user);
        $request->setUserResolver(fn () => $user);

        return $next($request);
    }
}
