<?php

declare(strict_types=1);

use App\Controllers\{AccountController, AuthController, CategoryController, DashboardController, GoalController, PageController, PreferenceController, ProfileController, TransactionController};
use App\Core\Router;

/** @var Router $router */
$router->get('/', [PageController::class, 'home'], true, true);
$router->get('/login', [PageController::class, 'login']);
$router->get('/cadastro', [PageController::class, 'register']);
$router->get('/dashboard', [PageController::class, 'dashboard'], true, true);
$router->get('/contas', [PageController::class, 'accounts'], true, true);
$router->get('/categorias', [PageController::class, 'categories'], true, true);
$router->get('/transacoes', [PageController::class, 'transactions'], true, true);
$router->get('/metas', [PageController::class, 'goals'], true, true);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->get('/api/auth/me', [AuthController::class, 'me'], true);
$router->post('/api/auth/refresh', [AuthController::class, 'refresh'], true);
$router->post('/api/auth/logout', [AuthController::class, 'logout'], true);
$router->put('/api/profile', [ProfileController::class, 'update'], true);
$router->put('/api/profile/password', [ProfileController::class, 'changePassword'], true);
$router->delete('/api/profile', [ProfileController::class, 'destroy'], true);
$router->get('/api/preferences', [PreferenceController::class, 'show'], true);
$router->put('/api/preferences', [PreferenceController::class, 'update'], true);
$router->get('/api/dashboard', [DashboardController::class, 'index'], true);
foreach (['accounts' => [AccountController::class, 'account'], 'categories' => [CategoryController::class, 'category'], 'transactions' => [TransactionController::class, 'transaction'], 'goals' => [GoalController::class, 'goal']] as $resource => [$controller, $parameter]) {
    $router->get("/api/$resource", [$controller, 'index'], true);
    $router->post("/api/$resource", [$controller, 'store'], true);
    $router->get("/api/$resource/{{$parameter}}", [$controller, 'show'], true);
    $router->put("/api/$resource/{{$parameter}}", [$controller, 'update'], true);
    $router->delete("/api/$resource/{{$parameter}}", [$controller, 'destroy'], true);
}
$router->post('/api/goals/{goal}/contributions', [GoalController::class, 'contribute'], true);
$router->delete('/api/goals/{goal}/contributions/{transaction}', [GoalController::class, 'removeContribution'], true);
