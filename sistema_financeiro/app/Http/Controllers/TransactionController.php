<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class TransactionController extends Controller
{
    /**
     * Lista as transações do usuário e aplica filtros opcionais de conta,
     * tipo, data exata ou intervalo de datas.
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'account_id' => ['sometimes', 'integer'],
            'type' => ['sometimes', Rule::in(Transaction::TYPES)],
            'date' => ['sometimes', 'date'],
            'start_date' => ['sometimes', 'date'],
            'end_date' => ['sometimes', 'date', 'after_or_equal:start_date'],
        ]);

        $transactions = Transaction::query()
            // Carrega conta e categoria junto da consulta para evitar consultas repetidas.
            ->with(['account:id,name', 'category:id,name,type'])
            // O usuário só enxerga transações ligadas às próprias contas.
            ->whereHas('account', fn ($query) => $query->where('user_id', $request->user()->id))
            ->when($filters['account_id'] ?? null, fn ($query, $id) => $query->where('account_id', $id))
            ->when($filters['type'] ?? null, fn ($query, $type) => $query->where('type', $type))
            ->when($filters['date'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', $date))
            ->when($filters['start_date'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', '>=', $date))
            ->when($filters['end_date'] ?? null, fn ($query, $date) => $query->whereDate('transaction_date', '<=', $date))
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->get();

        return response()->json($transactions);
    }

    /**
     * Cria uma transação e atualiza o saldo da conta de forma atômica.
     * Se qualquer etapa falhar, DB::transaction desfaz toda a operação.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $transaction = DB::transaction(function () use ($request, $data) {
            // lockForUpdate impede duas requisições de alterarem o mesmo saldo simultaneamente.
            $account = $this->lockedAccount($request, $data['account_id']);
            $this->categoryForUser($request, $data['category_id'], $data['type']);

            $account->update([
                'balance' => $this->newBalance($account->balance, $data['type'], $data['amount']),
            ]);

            return $account->transactions()->create($data);
        });

        return response()->json($transaction->load('account:id,name', 'category:id,name,type'), 201);
    }

    /** Retorna a transação com sua conta, categoria e eventuais metas relacionadas. */
    public function show(Request $request, int $transaction): JsonResponse
    {
        return response()->json(
            $this->findTransaction($request, $transaction)->load('account:id,name', 'category:id,name,type', 'goals'),
        );
    }

    /**
     * Edita uma transação sem corromper o saldo: primeiro desfaz o efeito antigo
     * e depois aplica o novo. Também permite transferir a transação para outra conta.
     */
    public function update(Request $request, int $transaction): JsonResponse
    {
        $data = $this->validated($request);

        $transaction = DB::transaction(function () use ($request, $transaction, $data) {
            $transaction = $this->findTransaction($request, $transaction);
            $this->categoryForUser($request, $data['category_id'], $data['type']);

            // O novo valor precisa continuar cobrindo tudo o que já foi destinado às metas.
            $contributed = (float) $transaction->goals()->sum('contributed_amount');
            if ($contributed > (float) $data['amount']) {
                throw ValidationException::withMessages([
                    'amount' => 'O valor não pode ser menor que o total já contribuído para metas.',
                ]);
            }

            // Reverte a receita/despesa original para reconstruir o saldo anterior.
            $oldAccount = $this->lockedAccount($request, $transaction->account_id);
            $oldRevertedBalance = $this->newBalance(
                $oldAccount->balance,
                $transaction->type,
                $transaction->amount,
                reverse: true,
            );

            if ($oldAccount->id === (int) $data['account_id']) {
                // Na mesma conta, aplica o novo efeito sobre o saldo já revertido.
                $oldAccount->update([
                    'balance' => $this->newBalance($oldRevertedBalance, $data['type'], $data['amount']),
                ]);
            } else {
                // Em contas diferentes, corrige a antiga e aplica o valor na nova conta.
                $newAccount = $this->lockedAccount($request, $data['account_id']);
                $oldAccount->update(['balance' => $oldRevertedBalance]);
                $newAccount->update([
                    'balance' => $this->newBalance($newAccount->balance, $data['type'], $data['amount']),
                ]);
            }

            $transaction->update($data);

            return $transaction;
        });

        return response()->json($transaction->load('account:id,name', 'category:id,name,type'));
    }

    /**
     * Exclui a transação somente depois de reverter seu efeito no saldo.
     * Uma receita já gasta não pode ser removida se isso produzir saldo negativo.
     */
    public function destroy(Request $request, int $transaction): JsonResponse
    {
        DB::transaction(function () use ($request, $transaction) {
            $transaction = $this->findTransaction($request, $transaction);
            $account = $this->lockedAccount($request, $transaction->account_id);

            $account->update([
                'balance' => $this->newBalance(
                    $account->balance,
                    $transaction->type,
                    $transaction->amount,
                    reverse: true,
                ),
            ]);

            $transaction->delete();
        });

        return response()->json(null, 204);
    }

    /**
     * Valida o formato comum aos métodos store e update.
     * A existência e a propriedade da conta/categoria são verificadas separadamente.
     *
     * @return array{
     *     account_id: int,
     *     category_id: int,
     *     description: string,
     *     amount: numeric-string|int|float,
     *     type: string,
     *     transaction_date: string
     * }
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'account_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'description' => ['required', 'string', 'min:3', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', Rule::in(Transaction::TYPES)],
            'transaction_date' => ['required', 'date'],
        ]);
    }

    /**
     * Localiza a transação por meio de uma conta do usuário atual.
     * IDs inexistentes ou de terceiros recebem a mesma resposta 404.
     */
    private function findTransaction(Request $request, int $id): Transaction
    {
        return Transaction::whereHas(
            'account',
            fn ($query) => $query->where('user_id', $request->user()->id),
        )->findOrFail($id);
    }

    /** Busca e bloqueia a conta até o término da transação de banco atual. */
    private function lockedAccount(Request $request, int $id): Account
    {
        return $request->user()->accounts()->lockForUpdate()->findOrFail($id);
    }

    /**
     * Verifica propriedade e coerência: uma categoria de receita não pode ser
     * usada em uma despesa, e vice-versa.
     */
    private function categoryForUser(Request $request, int $id, string $type): Category
    {
        $category = $request->user()->categories()->findOrFail($id);

        if ($category->type !== $type) {
            throw ValidationException::withMessages([
                'category_id' => 'A categoria deve possuir o mesmo tipo da transação.',
            ]);
        }

        return $category;
    }

    /**
     * Calcula o novo saldo. Receita soma e despesa subtrai; com reverse=true
     * o efeito é invertido para permitir edição ou exclusão segura.
     */
    private function newBalance(string|float $balance, string $type, string|float $amount, bool $reverse = false): string
    {
        $effect = $type === 'income' ? (float) $amount : -(float) $amount;
        $result = round((float) $balance + ($reverse ? -$effect : $effect), 2);

        if ($result < 0) {
            throw ValidationException::withMessages([
                'amount' => 'Saldo insuficiente para realizar esta operação.',
            ]);
        }

        return number_format($result, 2, '.', '');
    }
}
