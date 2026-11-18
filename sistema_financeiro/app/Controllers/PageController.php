<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Models\Account;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Preference;
use App\Models\Transaction;
use App\Models\User;

/** Monta as páginas web e entrega para cada view somente os dados de que ela precisa. */
final class PageController extends Controller
{
    public function home(Request $r): never
    {
        Response::redirect(url('/dashboard'));
    }
    public function login(Request $r): never
    {
        $this->redirectAuthenticatedUser($r);
        Response::view('auth/login', ['title' => 'Login', 'cardClass' => ''], 'layouts/auth');
    }
    public function register(Request $r): never
    {
        $this->redirectAuthenticatedUser($r);
        Response::view('auth/register', ['title' => 'Cadastro', 'cardClass' => 'auth-register-card'], 'layouts/auth');
    }

    private function redirectAuthenticatedUser(Request $request): void
    {
        try {
            Auth::authenticate($request);
        } catch (HttpException $exception) {
            if ($exception->status !== 401) {
                throw $exception;
            }
            return;
        }

        Response::redirect(url('/dashboard'));
    }

    public function dashboard(Request $r): never
    {
        $uid = $this->userId($r);
        $this->page('dashboard', $r, ['accounts' => Account::allForUser($uid, true), 'categories' => Category::allForUser($uid, true), 'transactions' => Transaction::allForUser($uid, [], true), 'goals' => $this->orderedGoals($uid)], 'Dashboard', ['dashboard.js']);
    }
    public function accounts(Request $r): never
    {
        $this->page('accounts', $r, ['accounts' => Account::allForUser($this->userId($r))], 'Contas', ['resources.js']);
    }
    public function categories(Request $r): never
    {
        $this->page('categories', $r, ['categories' => Category::allForUser($this->userId($r))], 'Categorias', ['resources.js']);
    }
    public function transactions(Request $r): never
    {
        $uid = $this->userId($r);
        $this->page('transactions', $r, ['transactions' => Transaction::allForUser($uid), 'accounts' => Account::allForUser($uid, true), 'categories' => Category::allForUser($uid, true)], 'Transações', ['resources.js']);
    }
    public function goals(Request $r): never
    {
        $uid = $this->userId($r);
        $this->page('goals', $r, ['goals' => $this->orderedGoals($uid), 'accounts' => Account::allForUser($uid, true)], 'Metas', ['resources.js']);
    }
    private function page(string $view, Request $r, array $data, string $title, array $scripts): never
    {
        $user = User::publicData($r->user());
        $preference = Preference::forUser((int)$user['id']);
        Response::view($view, array_merge($data, compact('title', 'scripts', 'user', 'preference'), ['activePage' => $view]));
    }
    private function orderedGoals(int $uid): array
    {
        $goals = Goal::allForUser($uid);
        $order = Preference::forUser($uid)['goal_order'];
        $positions = array_flip(array_map('intval', $order));
        usort($goals, fn($a, $b) => ($positions[(int)$a['id']] ?? PHP_INT_MAX - (int)$a['id']) <=> ($positions[(int)$b['id']] ?? PHP_INT_MAX - (int)$b['id']));
        return $goals;
    }
}
