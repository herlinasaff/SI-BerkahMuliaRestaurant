<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Endpoint Login (Admin, Kasir, Pemilik)
     */
    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::with('role')->where('username', $request->username)->first();

        // Cek kecocokan user dan password hash
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Username atau password salah!'
            ], 401);
        }

        if ($user->status !== 'active') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dinonaktifkan. Hubungi admin.'
            ], 403);
        }

        // Generate token kriptografis 60 karakter
        $plainToken = Str::random(60);

        // Simpan versi SHA-256 Hash ke database
        $user->update([
            'api_token' => hash('sha256', $plainToken),
            'last_login_at' => now(),
        ]);

        $currentShift = Shift::getCurrentShift();

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil! Selamat bertugas.',
            'data' => [
                'token' => $plainToken,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role->name ?? 'unknown',
                    'role_label' => $user->role->display_name ?? 'Staff',
                ],
                'current_shift' => $currentShift ? [
                    'id' => $currentShift->id,
                    'name' => $currentShift->name,
                    'start_time' => $currentShift->start_time,
                    'end_time' => $currentShift->end_time,
                ] : null,
            ]
        ]);
    }

    /**
     * Cek data user yang sedang login & shift aktif
     */
    public function me(Request $request): JsonResponse
    {
        $user = $request->user()->load('role');
        $currentShift = Shift::getCurrentShift();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'username' => $user->username,
                    'role' => $user->role->name,
                    'role_label' => $user->role->display_name,
                ],
                'current_shift' => $currentShift,
            ]
        ]);
    }

    /**
     * Logout & Hancurkan Token Sesi
     */
    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user) {
            $user->update(['api_token' => null]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Berhasil logout. Sesi Anda telah diakhiri.'
        ]);
    }
}
