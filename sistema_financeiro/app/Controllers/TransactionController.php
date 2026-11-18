<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Core\HttpException;
use App\Core\Request;
use App\Core\Response;
use App\Core\ValidationException;
use App\Core\Validator;
use App\Models\Account;
use App\Models\Category;
use App\Models\Transaction;

/**
 * Controla receitas e despesas e garante que cada alteração seja refletida no saldo.
 * As operações financeiras usam transações do banco para evitar saldos pela metade.
 */
final class TransactionController extends Controller
{
    public function index(Request $r): never
    {
        $f = Validator::validate($r->all(), ['account_id' => ['sometimes', 'integer'], 'type' => ['sometimes', 'in:' . implode(',', Transaction::TYPES)], 'date' => ['sometimes', 'date'], 'start_date' => ['sometimes', 'date'], 'end_date' => ['sometimes', 'date']]);
        Response::json(Transaction::allForUser($this->userId($r), $f));
    }
    /** Cria a transação e atualiza o saldo de forma atômica. */
    public function store(Request $r): never
    {
        $d = $this->validated($r);
        $t = Database::transaction(function () use ($r, $d) {
            $uid = $this->userId($r);
            $a = $this->account($d['account_id'], $uid, true, true);
            $this->category($d['category_id'], $uid, $d['type']);
            Account::updateBalance((int)$a['id'], $this->balance((float)$a['balance'], $d['type'], (float)$d['amount']));
            $d['user_id'] = $uid;
            return Transaction::create($d);
        });
        Response::json($t, 201);
    }
    public function show(Request $r): never
    {
        $t = Transaction::findForUser($this->id($r, 'transaction'), $this->userId($r)) ?? $this->notFound();
        Response::json($t);
    }
    /** Desfaz o efeito antigo no saldo antes de aplicar os novos dados. */
    public function update(Request $r): never
    {
        $id = $this->id($r, 'transaction');
        $d = $this->validated($r);
        $t = Database::transaction(function () use ($r, $id, $d) {
            $uid = $this->userId($r);
            $old = Transaction::findForUser($id, $uid) ?? $this->notFound();
            if (Transaction::hasGoals($id)) {
                throw new HttpException(409, 'Contribuições de metas devem ser alteradas pela tela de metas.');
            }
            $this->category($d['category_id'], $uid, $d['type']);
            $oldAccount = $this->account((int)$old['account_id'], $uid, true);
            $reverted = $this->balance((float)$oldAccount['balance'], $old['type'], (float)$old['amount'], true);
            if ((int)$old['account_id'] === (int)$d['account_id']) {
                if ($oldAccount['status'] !== 'active') {
                    throw new ValidationException(['account_id' => ['Selecione uma conta ativa.']]);
                }
                Account::updateBalance((int)$oldAccount['id'], $this->balance($reverted, $d['type'], (float)$d['amount']));
            } else {
                $newAccount = $this->account((int)$d['account_id'], $uid, true, true);
                Account::updateBalance((int)$oldAccount['id'], $reverted);
                Account::updateBalance((int)$newAccount['id'], $this->balance((float)$newAccount['balance'], $d['type'], (float)$d['amount']));
            }
            Transaction::update($id, $d);
            return Transaction::findForUser($id, $uid);
        });
        Response::json($t);
    }
    public function destroy(Request $r): never
    {
        $id = $this->id($r, 'transaction');
        Database::transaction(function () use ($r, $id) {
            $uid = $this->userId($r);
            $t = Transaction::findForUser($id, $uid) ?? $this->notFound();
            if (Transaction::hasGoals($id)) {
                throw new HttpException(409, 'Contribuições de metas devem ser removidas pela tela de metas.');
            }
            $a = $this->account((int)$t['account_id'], $uid, true);
            Account::updateBalance((int)$a['id'], $this->balance((float)$a['balance'], $t['type'], (float)$t['amount'], true));
            Transaction::delete($id);
        });
        Response::json(null, 204);
    }
    /**
     * Valida o formulário e converte os valores enviados pelo HTML.
     * Campos de select e number chegam como texto, mesmo quando representam números.
     */
    private function validated(Request $r): array
    {
        $data = Validator::validate($r->all(), [
            'account_id' => ['required', 'integer'],
            'category_id' => ['required', 'integer'],
            'description' => ['required', 'string', 'min:3', 'max:255'],
            'amount' => ['required', 'numeric', 'gt:0'],
            'type' => ['required', 'in:' . implode(',', Transaction::TYPES)],
            'transaction_date' => ['required', 'date'],
        ]);

        $data['account_id'] = (int) $data['account_id'];
        $data['category_id'] = (int) $data['category_id'];
        $data['amount'] = (float) $data['amount'];

        return $data;
    }
    private function account(int $id, int $uid, bool $lock = false, bool $activeOnly = false): array
    {
        $a = Account::findForUser($id, $uid, $lock) ?? $this->notFound();
        if ($activeOnly && $a['status'] !== 'active') {
            throw new ValidationException(['account_id' => ['Selecione uma conta ativa.']]);
        }
        return $a;
    }
    private function category(int $id, int $uid, string $type, bool $activeOnly = true): array
    {
        $c = Category::findForUser($id, $uid) ?? $this->notFound();
        if ($activeOnly && $c['status'] !== 'active') {
            throw new ValidationException(['category_id' => ['Selecione uma categoria ativa.']]);
        }
        if ($c['type'] !== $type) {
            throw new ValidationException(['category_id' => ['A categoria deve possuir o mesmo tipo da transação.']]);
        }
        return $c;
    }
    private function balance(float $balance, string $type, float $amount, bool $reverse = false): float
    {
        $effect = $type === 'income' ? $amount : -$amount;
        $result = round($balance + ($reverse ? -$effect : $effect), 2);
        if ($result < 0) {
            throw new ValidationException(['amount' => ['Saldo insuficiente para realizar esta operação.']]);
        }
        return $result;
    }
}
