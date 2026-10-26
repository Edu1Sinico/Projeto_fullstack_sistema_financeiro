<?php

use App\Models\Transaction;
use App\Models\User;
use App\Services\JwtService;

function authenticatedUser(): array
{
    $user = User::factory()->create();
    $token = app(JwtService::class)->createToken($user);

    return [$user, $token];
}

test('keeps accounts isolated by authenticated user', function () {
    [$owner] = authenticatedUser();
    [, $otherToken] = authenticatedUser();
    $account = $owner->accounts()->create([
        'name' => 'Conta principal',
        'type' => 'checking',
        'balance' => 100,
    ]);

    $this->withToken($otherToken)
        ->getJson("/api/accounts/{$account->id}")
        ->assertNotFound();
});

test('updates the account balance when transactions are created, changed and deleted', function () {
    [$user, $token] = authenticatedUser();
    $account = $user->accounts()->create([
        'name' => 'Conta principal',
        'type' => 'checking',
        'balance' => 0,
    ]);
    $income = $user->categories()->create(['name' => 'Salário', 'type' => 'income']);
    $expense = $user->categories()->create(['name' => 'Mercado', 'type' => 'expense']);

    $this->withToken($token)->postJson('/api/transactions', [
        'account_id' => $account->id,
        'category_id' => $income->id,
        'description' => 'Pagamento mensal',
        'amount' => 1000,
        'type' => 'income',
        'transaction_date' => '2026-09-19',
    ])->assertCreated();

    $expenseTransaction = $this->withToken($token)->postJson('/api/transactions', [
        'account_id' => $account->id,
        'category_id' => $expense->id,
        'description' => 'Compras do mês',
        'amount' => 250,
        'type' => 'expense',
        'transaction_date' => '2026-09-19',
    ])->assertCreated();

    expect($account->fresh()->balance)->toBe('750.00');

    $transactionId = $expenseTransaction->json('id');
    $this->withToken($token)->putJson("/api/transactions/{$transactionId}", [
        'account_id' => $account->id,
        'category_id' => $expense->id,
        'description' => 'Compras corrigidas',
        'amount' => 200,
        'type' => 'expense',
        'transaction_date' => '2026-09-19',
    ])->assertOk();

    expect($account->fresh()->balance)->toBe('800.00');

    $this->withToken($token)
        ->deleteJson("/api/transactions/{$transactionId}")
        ->assertNoContent();

    expect($account->fresh()->balance)->toBe('1000.00');
});

test('rejects expenses without enough balance and categories with another type', function () {
    [$user, $token] = authenticatedUser();
    $account = $user->accounts()->create([
        'name' => 'Carteira',
        'type' => 'wallet',
        'balance' => 50,
    ]);
    $income = $user->categories()->create(['name' => 'Salário', 'type' => 'income']);

    $payload = [
        'account_id' => $account->id,
        'category_id' => $income->id,
        'description' => 'Compra inválida',
        'amount' => 100,
        'type' => 'expense',
        'transaction_date' => '2026-09-19',
    ];

    $this->withToken($token)->postJson('/api/transactions', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('category_id');

    $expense = $user->categories()->create(['name' => 'Alimentação', 'type' => 'expense']);
    $payload['category_id'] = $expense->id;

    $this->withToken($token)->postJson('/api/transactions', $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('amount');

    expect($account->fresh()->balance)->toBe('50.00');
});

test('calculates goal progress from transaction contributions', function () {
    [$user, $token] = authenticatedUser();
    $account = $user->accounts()->create([
        'name' => 'Poupança',
        'type' => 'savings',
        'balance' => 1000,
    ]);
    $category = $user->categories()->create(['name' => 'Investimentos', 'type' => 'income']);
    $goal = $user->goals()->create([
        'title' => 'Reserva de emergência',
        'target_amount' => 2000,
    ]);

    $response = $this->withToken($token)->postJson("/api/goals/{$goal->id}/contributions", [
        'account_id' => $account->id,
        'amount' => 600,
    ]);

    $response->assertCreated()
        ->assertJsonPath('goal.current_amount', '600.00')
        ->assertJsonPath('goal.completed', false)
        ->assertJsonPath('goal.progress_percentage', 30);

    expect($account->fresh()->balance)->toBe('400.00');
});

test('does not change the type of a category already used by a transaction', function () {
    [$user, $token] = authenticatedUser();
    $account = $user->accounts()->create([
        'name' => 'Conta principal',
        'type' => 'checking',
        'balance' => 100,
    ]);
    $category = $user->categories()->create(['name' => 'Mercado', 'type' => 'expense']);
    Transaction::create([
        'account_id' => $account->id,
        'category_id' => $category->id,
        'description' => 'Compra',
        'amount' => 20,
        'type' => 'expense',
        'transaction_date' => '2026-09-19',
    ]);

    $this->withToken($token)->putJson("/api/categories/{$category->id}", [
        'name' => 'Mercado',
        'type' => 'income',
    ])->assertConflict();
});

test('does not allow contributions above the goal target', function () {
    [$user, $token] = authenticatedUser();
    $account = $user->accounts()->create([
        'name' => 'Poupança',
        'type' => 'savings',
        'balance' => 1000,
    ]);
    $category = $user->categories()->create(['name' => 'Salário', 'type' => 'income']);
    $goal = $user->goals()->create([
        'title' => 'Compra planejada',
        'target_amount' => 500,
    ]);

    $this->withToken($token)->postJson("/api/goals/{$goal->id}/contributions", [
        'account_id' => $account->id,
        'amount' => 600,
    ])->assertUnprocessable()->assertJsonValidationErrors('amount');
});

test('inactive accounts and categories remain manageable but are marked for frontend filters', function () {
    [$user, $token] = authenticatedUser();

    $account = $this->withToken($token)->postJson('/api/accounts', [
        'name' => 'Conta arquivada',
        'type' => 'checking',
        'status' => 'inactive',
        'balance' => 0,
    ])->assertCreated()->json();

    $category = $this->withToken($token)->postJson('/api/categories', [
        'name' => 'Categoria arquivada',
        'type' => 'expense',
        'status' => 'inactive',
    ])->assertCreated()->json();

    expect($account['status'])->toBe('inactive');
    expect($category['status'])->toBe('inactive');
    expect($user->accounts()->where('status', 'active')->count())->toBe(0);
});
