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
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->goals()->latest()->get());
    }

    public function store(Request $request): JsonResponse
    {
        $goal = $request->user()->goals()->create($this->validated($request));

        return response()->json($goal, 201);
    }

    public function show(Request $request, int $goal): JsonResponse
    {
        return response()->json(
            $this->findGoal($request, $goal)->load('transactions:id,description,amount,type,transaction_date'),
        );
    }

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

    public function destroy(Request $request, int $goal): JsonResponse
    {
        $this->findGoal($request, $goal)->delete();

        return response()->json(null, 204);
    }

    public function contribute(Request $request, int $goal): JsonResponse
    {
        $data = $request->validate([
            'transaction_id' => ['required', 'integer'],
            'contributed_amount' => ['required', 'numeric', 'gt:0'],
        ]);

        $goal = $this->findGoal($request, $goal);
        $transaction = $this->findTransaction($request, $data['transaction_id']);

        DB::transaction(function () use ($goal, $transaction, $data) {
            $otherContributions = (float) DB::table('goal_transaction')
                ->where('transaction_id', $transaction->id)
                ->where('goal_id', '!=', $goal->id)
                ->sum('contributed_amount');

            if ($otherContributions + (float) $data['contributed_amount'] > (float) $transaction->amount) {
                throw ValidationException::withMessages([
                    'contributed_amount' => 'As contribuições não podem superar o valor da transação.',
                ]);
            }

            $currentWithoutThisTransaction = (float) DB::table('goal_transaction')
                ->where('goal_id', $goal->id)
                ->where('transaction_id', '!=', $transaction->id)
                ->sum('contributed_amount');

            if ($currentWithoutThisTransaction + (float) $data['contributed_amount'] > (float) $goal->target_amount) {
                throw ValidationException::withMessages([
                    'contributed_amount' => 'A contribuição não pode ultrapassar o objetivo da meta.',
                ]);
            }

            $goal->transactions()->syncWithoutDetaching([
                $transaction->id => ['contributed_amount' => $data['contributed_amount']],
            ]);
        });

        return response()->json($goal->fresh()->load('transactions'), 201);
    }

    public function removeContribution(Request $request, int $goal, int $transaction): JsonResponse
    {
        $goal = $this->findGoal($request, $goal);
        $transaction = $this->findTransaction($request, $transaction);
        $goal->transactions()->detach($transaction->id);

        return response()->json(null, 204);
    }

    /** @return array{title: string, target_amount: numeric-string|int|float, deadline?: string|null} */
    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:100'],
            'target_amount' => ['required', 'numeric', 'gt:0'],
            'deadline' => ['nullable', 'date'],
        ]);
    }

    private function findGoal(Request $request, int $id): Goal
    {
        return $request->user()->goals()->findOrFail($id);
    }

    private function findTransaction(Request $request, int $id): Transaction
    {
        return Transaction::whereHas(
            'account',
            fn ($query) => $query->where('user_id', $request->user()->id),
        )->findOrFail($id);
    }
}
