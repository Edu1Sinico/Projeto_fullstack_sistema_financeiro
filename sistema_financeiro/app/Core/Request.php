<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    /** @var array<string, mixed> */
    private array $body;
    /** @var array<string, string> */
    private array $params = [];
    /** @var array<string, mixed>|null */
    private ?array $user = null;

    public function __construct()
    {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (str_contains($contentType, 'application/json')) {
            $decoded = json_decode((string) file_get_contents('php://input'), true);
            $this->body = is_array($decoded) ? $decoded : [];
        } else {
            $this->body = $_POST;
        }
    }

    public function method(): string
    {
        return strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    }

    public function path(): string
    {
        $path = rawurldecode((string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH));
        $base = base_url();
        if ($base !== '' && ($path === $base || str_starts_with($path, $base.'/'))) {
            $path = substr($path, strlen($base));
        }

        return '/'.trim($path, '/');
    }

    /** @return array<string, mixed> */
    public function all(): array
    {
        return array_merge($_GET, $this->body);
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return $this->body[$key] ?? $_GET[$key] ?? $default;
    }

    public function header(string $name): ?string
    {
        $key = 'HTTP_'.strtoupper(str_replace('-', '_', $name));

        return $_SERVER[$key] ?? null;
    }

    /**
     * O Apache (XAMPP) costuma não repassar o header Authorization para o PHP,
     * e depois do RewriteRule ele pode chegar como REDIRECT_HTTP_AUTHORIZATION.
     * Por isso procuramos o token em todos os lugares possíveis.
     */
    public function authorizationHeader(): ?string
    {
        foreach (['HTTP_AUTHORIZATION', 'REDIRECT_HTTP_AUTHORIZATION'] as $key) {
            if (!empty($_SERVER[$key])) {
                return (string) $_SERVER[$key];
            }
        }

        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $name => $value) {
                if (strcasecmp((string) $name, 'Authorization') === 0 && $value !== '') {
                    return (string) $value;
                }
            }
        }

        return null;
    }

    public function bearerToken(): ?string
    {
        $header = $this->authorizationHeader();
        if ($header !== null && preg_match('/^Bearer\s+(.+)$/i', $header, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    /** @param array<string, string> $params */
    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key): ?string
    {
        return $this->params[$key] ?? null;
    }

    /** @param array<string, mixed> $user */
    public function setUser(array $user): void
    {
        $this->user = $user;
    }

    /** @return array<string, mixed> */
    public function user(): array
    {
        if ($this->user === null) {
            throw new HttpException(401, 'Usuário não autenticado.');
        }

        return $this->user;
    }

    public function expectsJson(): bool
    {
        return str_starts_with($this->path(), '/api/') || str_contains((string) $this->header('Accept'), 'application/json');
    }
}
