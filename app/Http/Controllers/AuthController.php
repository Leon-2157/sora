<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login dan dapatkan Bearer Token
     */
    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        // Cek kredensial
        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => 'Kredensial tidak cocok.',
            ]);
        }

        // Cek status akun aktif
        if (! $user->is_active) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun tidak aktif.',
            ], 403);
        }

        // Token via sanctum
        $token = $user->createToken('api_sora')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil.',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                ],
            ],
        ], 200);
    }

    // Logout user
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil.',
        ], 200);
    }

    // Profil user sedang login
    public function profile(Request $request)
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Profil berhasil diambil.',
            'data' => [
                'user' => $request->user(),
            ],
        ], 200);
    }
}
