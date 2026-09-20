<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json($request->user()->accounts()->latest()->get());
    }

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

    public function show(Request $request, int $account): JsonResponse
    {
        return response()->json($this->findAccount($request, $account));
    }

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

    public function destroy(Request $request, int $account): JsonResponse
    {
        $this->findAccount($request, $account)->delete();

        return response()->json(null, 204);
    }

    private function findAccount(Request $request, int $id): Account
    {
        return $request->user()->accounts()->findOrFail($id);
    }
}
