<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        if ($status !== 204) {
            echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        }
        exit;
    }

    public static function redirect(string $location, int $status = 302): never
    {
        header('Location: '.$location, true, $status);
        exit;
    }

    /** @param array<string, mixed> $data */
    public static function view(string $view, array $data = [], string $layout = 'layouts/app'): never
    {
        extract($data, EXTR_SKIP);
        ob_start();
        require base_path('app/Views/'.$view.'.php');
        $content = (string) ob_get_clean();
        require base_path('app/Views/'.$layout.'.php');
        exit;
    }
}
