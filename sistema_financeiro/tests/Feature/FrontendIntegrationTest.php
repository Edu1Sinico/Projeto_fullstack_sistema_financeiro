<?php

use App\Models\User;
use App\Services\JwtService;
use Illuminate\Support\Facades\Hash;

test('login sets the JWT cookie and protected Blade pages render real data', function () {
    $user = User::factory()->create([
        'email' => 'blade@example.com',
        'password' => 'senha-segura',
    ]);
    $user->accounts()->create([
        'name' => 'Conta da interface',
        'type' => 'checking',
        'status' => 'active',
        'balance' => 250,
    ]);

    $login = $this->postJson('/api/auth/login', [
        'email' => 'blade@example.com',
        'password' => 'senha-segura',
    ])->assertOk()->assertCookie('finaxis_token');

    // Simula exatamente o cookie sem criptografia recebido pelo navegador.
    $this->withUnencryptedCookie('finaxis_token', $login->json('access_token'))
        ->get('/contas')
        ->assertOk()
        ->assertSee('Conta da interface');
});

test('duplicate registration returns a readable Portuguese validation message', function () {
    User::factory()->create(['email' => 'existente@example.com']);

    $this->postJson('/api/auth/register', [
        'name' => 'Usuário Teste',
        'email' => 'existente@example.com',
        'password' => 'senha-segura',
        'password_confirmation' => 'senha-segura',
    ])->assertUnprocessable()
        ->assertJsonPath('errors.email.0', 'Este e-mail já está cadastrado.');
});

test('profile, password and preferences are updated for the authenticated user', function () {
    $user = User::factory()->create(['password' => 'senha-segura']);
    $token = app(JwtService::class)->createToken($user);

    $this->withToken($token)->putJson('/api/profile', ['name' => 'Nome atualizado'])
        ->assertOk()->assertJsonPath('name', 'Nome atualizado');

    $this->withToken($token)->putJson('/api/profile/password', [
        'current_password' => 'senha-segura',
        'password' => 'nova-senha-segura',
        'password_confirmation' => 'nova-senha-segura',
    ])->assertOk();

    expect(Hash::check('nova-senha-segura', $user->fresh()->password))->toBeTrue();

    $this->withToken($token)->putJson('/api/preferences', ['theme' => 'dark'])
        ->assertOk()->assertJsonPath('theme', 'dark');
});

test('goal order only stores goals owned by the authenticated user', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $token = app(JwtService::class)->createToken($user);
    $first = $user->goals()->create(['title' => 'Primeira meta', 'target_amount' => 100]);
    $second = $user->goals()->create(['title' => 'Segunda meta', 'target_amount' => 200]);
    $foreign = $other->goals()->create(['title' => 'Meta alheia', 'target_amount' => 300]);

    $this->withToken($token)->putJson('/api/preferences', [
        'goal_order' => [$second->id, $foreign->id, $first->id],
    ])->assertOk()->assertJsonPath('goal_order', [$second->id, $first->id]);
});
