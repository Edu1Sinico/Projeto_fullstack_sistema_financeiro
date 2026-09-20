<?php

use Illuminate\Support\Facades\Route;

Route::get('/', fn () => response()->json([
    'application' => 'Sistema Financeiro',
    'status' => 'online',
]));
