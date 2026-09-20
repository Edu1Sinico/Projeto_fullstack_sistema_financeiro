<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    /**
     * Lista somente as contas do usuário identificado pelo JWT.
     * O relacionamento accounts() já adiciona o filtro user_id à consulta.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->accounts()->latest()->get());
    }

    /**
     * Valida e cria uma conta pertencente ao usuário autenticado.
     * O saldo inicial é opcional e nunca pode ser negativo.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'type' => ['required', Rule::in(Account::TYPES)],
            'balance' => ['sometimes', 'numeric', 'min:0'],
        ]);

        $account = $request->user()->accounts()->create($data);

        return response()->json($account, 201);
    }

    /** Retorna uma conta específica, desde que pertença ao usuário autenticado. */
    public function show(Request $request, int $account): JsonResponse
    {
        return response()->json($this->findAccount($request, $account));
    }

    /**
     * Altera apenas nome e tipo. O saldo não é editado diretamente porque
     * suas mudanças devem acontecer por meio das transações financeiras.
     */
    public function update(Request $request, int $account): JsonResponse
    {
        $account = $this->findAccount($request, $account);
        $data = $request->validate([
            'name' => ['required', 'string', 'min:3', 'max:100'],
            'type' => ['required', Rule::in(Account::TYPES)],
        ]);

        $account->update($data);

        return response()->json($account);
    }

    /** Exclui a conta encontrada no conjunto de contas do usuário atual. */
    public function destroy(Request $request, int $account): JsonResponse
    {
        $this->findAccount($request, $account)->delete();

        return response()->json(null, 204);
    }

    /**
     * Centraliza a busca segura por conta. findOrFail devolve 404 quando o ID
     * não existe ou pertence a outro usuário, sem revelar dados de terceiros.
     */
    private function findAccount(Request $request, int $id): Account
    {
        return $request->user()->accounts()->findOrFail($id);
    }
}
