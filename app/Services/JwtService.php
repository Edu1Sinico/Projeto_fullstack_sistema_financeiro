<?php

namespace App\Services;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use RuntimeException;
use stdClass;

class JwtService
{
    public function createToken(User $user): string
    {
        $now = time();

        return JWT::encode([
            'iss' => config('app.url'),
            'sub' => (string) $user->id,
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + (config('jwt.ttl') * 60),
        ], $this->secret(), config('jwt.algorithm'));
    }

    public function decode(string $token): stdClass
    {
        return JWT::decode(
            $token,
            new Key($this->secret(), config('jwt.algorithm')),
        );
    }

    public function ttlInSeconds(): int
    {
        return config('jwt.ttl') * 60;
    }

    private function secret(): string
    {
        $secret = (string) config('jwt.secret');

        if (strlen($secret) < 32) {
            throw new RuntimeException('JWT_SECRET deve possuir pelo menos 32 caracteres.');
        }

        return $secret;
    }
}
