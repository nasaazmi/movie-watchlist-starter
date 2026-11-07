<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:50', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:255'],
        ]);

        $user = User::create($data);

        return response()->json([
            'message' => 'Registrasi berhasil.',
            'user'    => $this->userPayload($user),
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $guard = $this->guard();
        $token = $guard->attempt($credentials);

        if (! $token) {
            return response()->json([
                'message' => 'Email atau password salah.',
            ], 401);
        }

        return response()->json([
            'token'      => $token,
            'token_type' => 'bearer',
            'expires_in' => $guard->factory()->getTTL() * 60,
            'user'       => $this->userPayload($guard->user()),
        ], 200);
    }

    public function me(): JsonResponse
    {
        return response()->json([
            'user' => $this->userPayload($this->guard()->user()),
        ]);
    }

    private function guard(): JWTGuard
    {
        return Auth::guard('api');
    }

    private function userPayload($user): array
    {
        return [
            'id'       => $user->id,
            'username' => $user->username,
            'email'    => $user->email,
        ];
    }
}