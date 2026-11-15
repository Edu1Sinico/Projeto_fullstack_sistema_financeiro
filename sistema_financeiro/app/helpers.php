<?php

declare(strict_types=1);

use App\Core\Env;

function env(string $key, mixed $default = null): mixed
{
    return Env::get($key, $default);
}

function base_path(string $path = ''): string
{
    return ROOT_PATH.($path !== '' ? DIRECTORY_SEPARATOR.ltrim($path, '\\/') : '');
}

function base_url(): string
{
    $path = trim((string) env('APP_BASE_PATH', ''), '/');

    return $path === '' ? '' : '/'.$path;
}

function url(string $path = ''): string
{
    return base_url().($path !== '' ? '/'.ltrim($path, '/') : '');
}

function asset(string $path): string
{
    $file = base_path('public/'.ltrim($path, '/'));
    $version = is_file($file) ? '?v='.filemtime($file) : '';

    return base_url().'/public/'.ltrim($path, '/').$version;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function json_attr(array $value): string
{
    return e((string) json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
}

function money(float|string $value): string
{
    return number_format((float) $value, 2, ',', '.');
}

function date_br(?string $value): string
{
    if ($value === null || $value === '') {
        return 'Sem prazo';
    }

    return date('d/m/Y', strtotime($value));
}

function lower(string $value): string
{
    return function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
}
