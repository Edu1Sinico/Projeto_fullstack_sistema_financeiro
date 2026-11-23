<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use Throwable;

final class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection instanceof PDO) {
            return self::$connection;
        }

        $host = (string) env('DB_HOST', '127.0.0.1');
        $port = (string) env('DB_PORT', '3306');
        $database = (string) env('DB_DATABASE', 'sistema_financeiro');
        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";

        self::$connection = new PDO($dsn, (string) env('DB_USERNAME', 'root'), (string) env('DB_PASSWORD', ''), self::options() + [
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        return self::$connection;
    }

    /**
     * Opções comuns do PDO. Bancos MySQL na nuvem (como o TiDB Cloud) exigem conexão
     * criptografada (SSL); no XAMPP deixe DB_SSL=false (ou sem a linha).
     * O TiDB Cloud usa certificado público, então a verificação fica ligada.
     *
     * @return array<int, mixed>
     */
    public static function options(): array
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            // Usa no banco o mesmo fuso do PHP (APP_TIMEZONE). Bancos na nuvem
            // costumam estar em UTC, o que deixaria created_at 3 horas adiantado.
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET time_zone = '" . date('P') . "'",
        ];

        if ((bool) env('DB_SSL', false)) {
            $options[PDO::MYSQL_ATTR_SSL_CA] = (string) env('DB_SSL_CA', '/etc/ssl/certs/ca-certificates.crt');
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = (bool) env('DB_SSL_VERIFY', true);
        }

        return $options;
    }

    public static function transaction(callable $callback): mixed
    {
        $pdo = self::connection();
        $pdo->beginTransaction();

        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }
}
