<?php

declare(strict_types=1);

define('ROOT_PATH', dirname(__DIR__));

require_once __DIR__.'/helpers.php';

spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relative = str_replace('\\', DIRECTORY_SEPARATOR, substr($class, strlen($prefix)));
    $file = __DIR__.DIRECTORY_SEPARATOR.$relative.'.php';
    if (is_file($file)) {
        require_once $file;
    }
});

App\Core\Env::load(base_path('.env'));
date_default_timezone_set((string) env('APP_TIMEZONE', 'America/Sao_Paulo'));

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('finaxis_session');
    session_start();
}
