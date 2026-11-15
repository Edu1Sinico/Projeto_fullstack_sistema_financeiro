<?php
declare(strict_types=1);

require_once dirname(__DIR__).'/app/bootstrap.php';

use App\Core\Jwt;
use App\Core\ValidationException;
use App\Core\Validator;

$passed = 0;
$failed = 0;

function test(string $description, callable $callback): void
{
    global $passed, $failed;
    try {
        $callback();
        $passed++;
        echo "[OK] {$description}\n";
    } catch (Throwable $exception) {
        $failed++;
        echo "[FALHOU] {$description}: {$exception->getMessage()}\n";
    }
}

function expect(bool $condition, string $message = 'Condição não atendida.'): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

test('JWT é criado e recupera o usuário correto', function (): void {
    $token = Jwt::create(['id' => 42]);
    $payload = Jwt::decode($token);
    expect((int) $payload['sub'] === 42);
});

test('JWT alterado é rejeitado', function (): void {
    $token = Jwt::create(['id' => 42]);
    try {
        Jwt::decode($token.'x');
    } catch (Throwable) {
        return;
    }
    throw new RuntimeException('Token inválido foi aceito.');
});

test('Validação aceita dados financeiros válidos', function (): void {
    $data = Validator::validate(['amount' => '25.50', 'type' => 'income'], [
        'amount' => ['required', 'numeric', 'gt:0'],
        'type' => ['required', 'in:income,expense'],
    ]);
    expect($data['amount'] === '25.50');
});

test('Validação rejeita valor financeiro negativo', function (): void {
    try {
        Validator::validate(['amount' => '-1'], ['amount' => ['required', 'numeric', 'gt:0']]);
    } catch (ValidationException) {
        return;
    }
    throw new RuntimeException('Valor negativo foi aceito.');
});

echo "\nResultado: {$passed} aprovado(s), {$failed} falha(s).\n";
exit($failed === 0 ? 0 : 1);
