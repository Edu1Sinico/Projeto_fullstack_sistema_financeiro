<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class PageController extends Controller
{
    public function dashboard(Request $request): View
    {
        $accounts = $request->user()->accounts()->where('status', 'active')->latest()->get();
        $categories = $request->user()->categories()->where('status', 'active')->orderBy('name')->get();
        $transactions = Transaction::with(['account:id,name,status', 'category:id,name,type,status'])
            ->withCount('goals')
            ->whereHas('account', fn ($query) => $query
                ->where('user_id', $request->user()->id)
                ->where('status', 'active'))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return view('dashboard', [
            'accounts' => $accounts,
            'categories' => $categories,
            'transactions' => $transactions,
            'goals' => $this->orderedGoals($request),
        ]);
    }

    public function accounts(Request $request): View
    {
        return view('accounts', [
            'accounts' => $request->user()->accounts()->latest()->get(),
        ]);
    }

    public function transactions(Request $request): View
    {
        $transactions = Transaction::with(['account:id,name,status', 'category:id,name,type,status'])
            ->withCount('goals')
            ->whereHas('account', fn ($query) => $query->where('user_id', $request->user()->id))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return view('transactions', [
            'transactions' => $transactions,
            'accounts' => $request->user()->accounts()->where('status', 'active')->orderBy('name')->get(),
            'categories' => $request->user()->categories()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    public function categories(Request $request): View
    {
        return view('categories', [
            'categories' => $request->user()->categories()->latest()->get(),
        ]);
    }

    public function goals(Request $request): View
    {
        return view('goals', [
            'goals' => $this->orderedGoals($request),
            'accounts' => $request->user()->accounts()->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /** @return Collection<int, Goal> */
    private function orderedGoals(Request $request): Collection
    {
        $goals = $request->user()->goals()->get();
        $order = $request->user()->preference?->goal_order ?? [];
        $positions = array_flip(array_map('intval', $order));

        return $goals->sortBy(
            fn (Goal $goal) => $positions[$goal->id] ?? PHP_INT_MAX - $goal->id,
        )->values();
    }
}
