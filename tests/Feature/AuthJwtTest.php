<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('registers a user, creates default categories and returns a JWT', function () {
    $response = $this->postJson('/api/auth/register', [
        'name' => 'Maria Silva',
        'email' => 'maria@example.com',
        'password' => 'senha-segura',
        'password_confirmation' => 'senha-segura',
    ]);

    $response->assertCreated()
        ->assertJsonStructure(['access_token', 'token_type', 'expires_in', 'user'])
        ->assertJsonPath('token_type', 'Bearer');

    $user = User::where('email', 'maria@example.com')->firstOrFail();
    expect(Hash::check('senha-segura', $user->password))->toBeTrue();
    expect($user->categories()->count())->toBe(7);
});

test('logs in and accesses a protected route with the JWT', function () {
    User::factory()->create([
        'email' => 'joao@example.com',
        'password' => 'senha-segura',
    ]);

    $login = $this->postJson('/api/auth/login', [
        'email' => 'joao@example.com',
        'password' => 'senha-segura',
    ])->assertOk();

    $this->withToken($login->json('access_token'))
        ->getJson('/api/auth/me')
        ->assertOk()
        ->assertJsonPath('email', 'joao@example.com');
});

test('rejects invalid credentials and missing tokens', function () {
    User::factory()->create(['email' => 'ana@example.com']);

    $this->postJson('/api/auth/login', [
        'email' => 'ana@example.com',
        'password' => 'incorreta',
    ])->assertUnauthorized();

    $this->getJson('/api/accounts')->assertUnauthorized();
});
