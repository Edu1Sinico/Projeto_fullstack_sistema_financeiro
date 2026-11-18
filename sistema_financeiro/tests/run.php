<?php
declare(strict_types=1);

require_once dirname(__DIR__).'/app/bootstrap.php';

use App\Core\Jwt;
use App\Core\Request;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Controllers\TransactionController;

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

test('Formulário de transação converte IDs e valor para tipos numéricos', function (): void {
    $oldPost = $_POST;
    $oldMethod = $_SERVER['REQUEST_METHOD'] ?? null;
    $oldContentType = $_SERVER['CONTENT_TYPE'] ?? null;

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $_SERVER['CONTENT_TYPE'] = 'application/x-www-form-urlencoded';
    $_POST = [
        'account_id' => '7',
        'category_id' => '3',
        'description' => 'Compra de teste',
        'amount' => '100.50',
        'type' => 'expense',
        'transaction_date' => '2026-11-17',
    ];

    try {
        $method = new ReflectionMethod(TransactionController::class, 'validated');
        $data = $method->invoke(new TransactionController(), new Request());
        expect($data['account_id'] === 7, 'account_id não foi convertido para inteiro.');
        expect($data['category_id'] === 3, 'category_id não foi convertido para inteiro.');
        expect($data['amount'] === 100.5, 'amount não foi convertido para número.');
    } finally {
        $_POST = $oldPost;
        if ($oldMethod === null) {
            unset($_SERVER['REQUEST_METHOD']);
        } else {
            $_SERVER['REQUEST_METHOD'] = $oldMethod;
        }
        if ($oldContentType === null) {
            unset($_SERVER['CONTENT_TYPE']);
        } else {
            $_SERVER['CONTENT_TYPE'] = $oldContentType;
        }
    }
});

echo "\nResultado: {$passed} aprovado(s), {$failed} falha(s).\n";
exit($failed === 0 ? 0 : 1);
