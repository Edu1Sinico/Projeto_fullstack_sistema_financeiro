<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Account;
use App\Models\Goal;
use App\Models\Transaction;

/** Reúne em uma única resposta os totais e listas usados pelo Dashboard e pelo mobile. */
final class DashboardController extends Controller
{
    public function index(Request $r): never
    {
        $uid = $this->userId($r);
        $accounts = Account::allForUser($uid, true);
        $transactions = Transaction::allForUser($uid, [], true);
        $income = 0.0;
        $expenses = 0.0;
        foreach ($transactions as $t) {
            if ($t['type'] === 'income') {
                $income += (float)$t['amount'];
            } else {
                $expenses += (float)$t['amount'];
            }
        }
        Response::json(['balance' => array_sum(array_map(fn($a) => (float)$a['balance'], $accounts)), 'income' => $income, 'expenses' => $expenses, 'result' => $income - $expenses, 'accounts' => $accounts, 'recent_transactions' => array_slice($transactions, 0, 10), 'goals' => Goal::allForUser($uid)]);
    }
}
