<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\Jwt;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Category;
use App\Models\User;

final class AuthController extends Controller
{
    /** Cadastra o usuário e as categorias iniciais na mesma transação. */
    public function register(Request $request): never
    {
        $data = Validator::validate($request->all(), [
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
        if (User::findByEmail($data['email']) !== null) {
            throw new ValidationException(['email' => ['Este e-mail já está cadastrado.']]);
        }

        $user = Database::transaction(function () use ($data): array {
            $user = User::create($data['name'], $data['email'], $data['password']);
            foreach ([
                ['Salário', 'income'], ['Investimentos', 'income'], ['Alimentação', 'expense'],
                ['Moradia', 'expense'], ['Transporte', 'expense'], ['Lazer', 'expense'], ['Saúde', 'expense'],
            ] as [$name, $type]) {
                Category::create((int) $user['id'], compact('name', 'type'));
            }
            return $user;
        });

        $this->tokenResponse($user, 201);
    }

    /** Compara a senha recebida com o hash salvo no MySQL. */
    public function login(Request $request): never
    {
        $data = Validator::validate($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);
        $user = User::findByEmail($data['email']);
        if ($user === null || !password_verify($data['password'], $user['password'])) {
            Response::json(['message' => 'E-mail ou senha incorretos.'], 401);
        }
        $this->tokenResponse($user);
    }

    public function me(Request $request): never { Response::json(User::publicData($request->user())); }
    public function refresh(Request $request): never { $this->tokenResponse($request->user()); }

    public function logout(Request $request): never
    {
        $this->writeCookie('', time() - 3600);
        Response::json(['message' => 'Logout realizado.']);
    }

    private function tokenResponse(array $user, int $status = 200): never
    {
        $token = Jwt::create($user);
        $this->writeCookie($token, time() + Jwt::ttl());
        Response::json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => Jwt::ttl(),
            'user' => User::publicData($user),
        ], $status);
    }

    private function writeCookie(string $value, int $expires): void
    {
        setcookie('finaxis_token', $value, [
            'expires' => $expires,
            'path' => base_url() ?: '/',
            'secure' => (bool) env('COOKIE_SECURE', false),
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}
