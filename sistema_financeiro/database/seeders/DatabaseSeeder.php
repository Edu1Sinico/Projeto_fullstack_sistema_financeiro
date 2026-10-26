<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Usuário de Teste',
            'email' => 'test@example.com',
        ]);

        $user->categories()->createMany([
            ['name' => 'Salário', 'type' => 'income'],
            ['name' => 'Investimentos', 'type' => 'income'],
            ['name' => 'Alimentação', 'type' => 'expense'],
            ['name' => 'Moradia', 'type' => 'expense'],
            ['name' => 'Transporte', 'type' => 'expense'],
            ['name' => 'Lazer', 'type' => 'expense'],
            ['name' => 'Saúde', 'type' => 'expense'],
        ]);
    }
}
