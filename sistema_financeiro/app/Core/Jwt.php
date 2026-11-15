<?php

declare(strict_types=1);

namespace App\Core;

final class Jwt
{
    /** @param array<string, mixed> $user */
    public static function create(array $user): string
    {
        $now = time();
        $header = ['typ' => 'JWT', 'alg' => 'HS256'];
        $payload = [
            'iss' => (string) env('APP_URL', 'http://localhost'),
            'sub' => (string) $user['id'],
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + self::ttl(),
        ];
        $segments = [self::encode($header), self::encode($payload)];
        $signature = hash_hmac('sha256', implode('.', $segments), self::secret(), true);
        $segments[] = self::base64UrlEncode($signature);

        return implode('.', $segments);
    }

    /** @return array<string, mixed> */
    public static function decode(string $token): array
    {
        $segments = explode('.', $token);
        if (count($segments) !== 3) {
            throw new HttpException(401, 'Token inválido ou expirado.');
        }

        [$header, $payload, $signature] = $segments;
        $expected = self::base64UrlEncode(hash_hmac('sha256', $header.'.'.$payload, self::secret(), true));
        if (!hash_equals($expected, $signature)) {
            throw new HttpException(401, 'Token inválido ou expirado.');
        }

        $decoded = json_decode(self::base64UrlDecode($payload), true);
        if (!is_array($decoded) || ($decoded['nbf'] ?? 0) > time() || ($decoded['exp'] ?? 0) <= time()) {
            throw new HttpException(401, 'Token inválido ou expirado.');
        }

        return $decoded;
    }

    public static function ttl(): int
    {
        return max(1, (int) env('JWT_TTL', 120)) * 60;
    }

    private static function secret(): string
    {
        $secret = (string) env('JWT_SECRET', '');
        if (strlen($secret) < 32) {
            throw new HttpException(500, 'JWT_SECRET deve possuir pelo menos 32 caracteres.');
        }

        return $secret;
    }

    /** @param array<string, mixed> $value */
    private static function encode(array $value): string
    {
        return self::base64UrlEncode((string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    }

    private static function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'));
    }
}
