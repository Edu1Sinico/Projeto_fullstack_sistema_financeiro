<?php
declare(strict_types=1);
require_once dirname(__DIR__).'/app/bootstrap.php';

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Execute este arquivo somente pelo terminal.');
}

try {
    $host = (string) env('DB_HOST', '127.0.0.1');
    $port = (string) env('DB_PORT', '3306');
    $pdo = new PDO("mysql:host={$host};port={$port};charset=utf8mb4", (string) env('DB_USERNAME', 'root'), (string) env('DB_PASSWORD', ''), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec((string) file_get_contents(__DIR__.'/schema.sql'));
    echo "Banco de dados preparado com sucesso.\n";
} catch (PDOException $exception) {
    fwrite(STDERR, "Não foi possível preparar o banco: {$exception->getMessage()}\n");
    exit(1);
}
