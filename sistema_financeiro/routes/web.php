<?php

use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

Route::view('/login', 'auth.login')->name('login');
Route::view('/cadastro', 'auth.register')->name('register');

Route::middleware('jwt.auth')->group(function () {
    Route::get('/', fn () => redirect()->route('dashboard'));
    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/contas', [PageController::class, 'accounts'])->name('accounts');
    Route::get('/transacoes', [PageController::class, 'transactions'])->name('transactions');
    Route::get('/categorias', [PageController::class, 'categories'])->name('categories');
    Route::get('/metas', [PageController::class, 'goals'])->name('goals');
});
