<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function __construct(private readonly JwtService $jwt) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = User::create($data);

            $user->categories()->createMany([
                ['name' => 'Salário', 'type' => 'income'],
                ['name' => 'Investimentos', 'type' => 'income'],
                ['name' => 'Alimentação', 'type' => 'expense'],
                ['name' => 'Moradia', 'type' => 'expense'],
                ['name' => 'Transporte', 'type' => 'expense'],
                ['name' => 'Lazer', 'type' => 'expense'],
                ['name' => 'Saúde', 'type' => 'expense'],
            ]);

            return $user;
        });

        return $this->tokenResponse($user, 201);
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if ($user === null || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'E-mail ou senha incorretos.'], 401);
        }

        return $this->tokenResponse($user);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    public function refresh(Request $request): JsonResponse
    {
        return $this->tokenResponse($request->user());
    }

    public function logout(): JsonResponse
    {
        return response()->json([
            'message' => 'Logout realizado. Remova o token no front-end.',
        ]);
    }

    private function tokenResponse(User $user, int $status = 200): JsonResponse
    {
        return response()->json([
            'access_token' => $this->jwt->createToken($user),
            'token_type' => 'Bearer',
            'expires_in' => $this->jwt->ttlInSeconds(),
            'user' => $user,
        ], $status);
    }
}
