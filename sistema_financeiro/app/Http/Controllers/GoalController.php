<?php

namespace App\Http\Controllers;

use App\Models\Goal;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoalController extends Controller
{
    /** Lista somente as metas pertencentes ao usuário autenticado. */
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->goals()->latest()->get());
    }

    /** Cria uma meta. O valor atual começará em zero e será calculado pelas contribuições. */
    public function store(Request $request): JsonResponse
    {
        $goal = $request->user()->goals()->create($this->validated($request));

        return response()->json($goal, 201);
    }

    /** Exibe a meta e as transações que contribuíram para ela. */
    public function show(Request $request, int $goal): JsonResponse
    {
        return response()->json(
            $this->findGoal($request, $goal)->load('transactions:id,description,amount,type,transaction_date'),
        );
    }

    /**
     * Atualiza os dados da meta sem permitir que o novo objetivo fique abaixo
     * do valor que já foi contribuído.
     */
    public function update(Request $request, int $goal): JsonResponse
    {
        $goal = $this->findGoal($request, $goal);
        $data = $this->validated($request);

        if ((float) $data['target_amount'] < (float) $goal->current_amount) {
            throw ValidationException::withMessages([
                'target_amount' => 'O objetivo não pode ser menor que o valor já contribuído.',
            ]);
        }

        $goal->update($data);

        return response()->json($goal);
    }

    /**
     * Exclui a meta e devolve às contas os valores reservados por contribuições.
     * Tudo acontece na mesma transação para não deixar saldos inconsistentes.
     */
    public function destroy(Request $request, int $goal): JsonResponse
    {
        DB::transaction(function () use ($request, $goal) {
            $goal = $this->findGoal($request, $goal);

            foreach ($goal->transactions()->with('account')->get() as $transaction) {
                $transaction->account()->lockForUpdate()->increment('balance', $transaction->pivot->contributed_amount);
                $transaction->delete();
            }

            $goal->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Reserva dinheiro de uma conta para a meta. Uma transação de despesa é
     * criada automaticamente, permitindo auditar a movimentação no extrato.
     */
    public function contribute(Request $request, int $goal): JsonResponse
    {
        $data = $request->validate([
            'account_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $goal = $this->findGoal($request, $goal);
        $transaction = DB::transaction(function () use ($request, $goal, $data) {
            $account = $request->user()->accounts()
                ->where('status', 'active')
                ->lockForUpdate()
                ->findOrFail($data['account_id']);

            if ((float) $data['amount'] > (float) $account->balance) {
                throw ValidationException::withMessages([
                    'amount' => 'Saldo insuficiente para realizar esta contribuição.',
                ]);
            }

            if ((float) $goal->current_amount + (float) $data['amount'] > (float) $goal->target_amount) {
                throw ValidationException::withMessages([
                    'amount' => 'A contribuição não pode ultrapassar o valor restante da meta.',
                ]);
            }

            $category = $request->user()->categories()->firstOrCreate([
                'name' => 'Contribuição para metas',
                'type' => 'expense',
            ], [
                'status' => 'inactive',
            ]);

            // Garante que a categoria interna nunca apareça nos filtros de cadastro.
            if ($category->status !== 'inactive') {
                $category->update(['status' => 'inactive']);
            }

            $account->decrement('balance', $data['amount']);

            $transaction = $account->transactions()->create([
                'category_id' => $category->id,
                'description' => "Contribuição: {$goal->title}",
                'amount' => $data['amount'],
                'type' => 'expense',
                'transaction_date' => now()->toDateString(),
            ]);

            $goal->transactions()->attach($transaction->id, [
                'contributed_amount' => $data['amount'],
            ]);

            return $transaction;
        });

        return response()->json([
            'goal' => $goal->fresh()->load('transactions'),
            'transaction' => $transaction->load('account:id,name', 'category:id,name,type'),
        ], 201);
    }

    /** Remove uma contribuição, apaga sua transação e devolve o valor à conta. */
    public function removeContribution(Request $request, int $goal, int $transaction): JsonResponse
    {
        DB::transaction(function () use ($request, $goal, $transaction) {
            $goal = $this->findGoal($request, $goal);
            $transaction = $this->findTransaction($request, $transaction);
            $contribution = $goal->transactions()->whereKey($transaction->id)->firstOrFail();

            $account = $request->user()->accounts()->lockForUpdate()->findOrFail($transaction->account_id);
            $account->increment('balance', $contribution->pivot->contributed_amount);
            $goal->transactions()->detach($transaction->id);
            $transaction->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Valida os campos usados na criação e edição de uma meta.
     *
     * @return array{title: string, target_amount: numeric-string|int|float, deadline?: string|null}
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'deadline' => ['nullable', 'date'],
        ]);
    }

    /** Busca a meta no relacionamento do usuário para aplicar a autorização por propriedade. */
    private function findGoal(Request $request, int $id): Goal
    {
        return $request->user()->goals()->findOrFail($id);
    }

    /**
     * Aceita somente transações ligadas a uma conta do usuário atual.
     * Assim, uma transação alheia não pode financiar a meta.
     */
    private function findTransaction(Request $request, int $id): Transaction
    {
        return Transaction::whereHas(
            'account',
            fn ($query) => $query->where('user_id', $request->user()->id),
        )->findOrFail($id);
    }
}
