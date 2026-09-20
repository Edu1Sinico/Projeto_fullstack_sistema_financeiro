<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Retorna os indicadores da tela inicial em uma única resposta.
     * Este é um controller invocável: por isso utiliza __invoke e possui uma única ação.
     */
    public function __invoke(Request $request): JsonResponse
    {
        // A consulta-base limita todas as somas às contas do usuário autenticado.
        $transactions = Transaction::whereHas(
            'account',
            fn ($query) => $query->where('user_id', $request->user()->id),
        );

        return response()->json([
            // clone evita que o filtro de receita seja reaproveitado na consulta de despesas.
            'total_balance' => number_format((float) $request->user()->accounts()->sum('balance'), 2, '.', ''),
            'total_income' => number_format((float) (clone $transactions)->where('type', 'income')->sum('amount'), 2, '.', ''),
            'total_expense' => number_format((float) (clone $transactions)->where('type', 'expense')->sum('amount'), 2, '.', ''),
            'accounts' => $request->user()->accounts()->latest()->get(),
            'goals' => $request->user()->goals()->latest()->get(),
        ]);
    }
}
