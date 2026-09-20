<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $transactions = Transaction::whereHas(
            'account',
            fn ($query) => $query->where('user_id', $request->user()->id),
        );

        return response()->json([
            'total_balance' => number_format((float) $request->user()->accounts()->sum('balance'), 2, '.', ''),
            'total_income' => number_format((float) (clone $transactions)->where('type', 'income')->sum('amount'), 2, '.', ''),
            'total_expense' => number_format((float) (clone $transactions)->where('type', 'expense')->sum('amount'), 2, '.', ''),
            'accounts' => $request->user()->accounts()->latest()->get(),
            'goals' => $request->user()->goals()->latest()->get(),
        ]);
    }
}
