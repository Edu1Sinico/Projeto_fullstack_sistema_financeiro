<?php

declare(strict_types=1);

namespace App\Core;

use App\Models\User;
use Throwable;

final class Auth
{
    public static function authenticate(Request $request, bool $web = false): void
    {
        $token = $request->bearerToken() ?? ($_COOKIE['finaxis_token'] ?? null);
        if (!is_string($token) || $token === '') {
            self::unauthorized($web, 'Token não informado.');
        }

        try {
            $payload = Jwt::decode($token);
            $user = User::find((int) ($payload['sub'] ?? 0));
        } catch (Throwable) {
            self::unauthorized($web, 'Token inválido ou expirado.');
        }

        if ($user === null) {
            self::unauthorized($web, 'Usuário do token não encontrado.');
        }

        $request->setUser($user);
    }

    private static function unauthorized(bool $web, string $message): never
    {
        if ($web) {
            Response::redirect(url('/login'));
        }

        throw new HttpException(401, $message);
    }
}
