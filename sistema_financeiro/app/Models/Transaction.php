<?php

declare(strict_types=1);

namespace App\Models;

final class Transaction extends Model
{
    public const TYPES = ['income', 'expense'];
    public static function allForUser(int $userId, array $filters = [], bool $activeAccountOnly = false): array
    {
        $sql = 'SELECT t.*,a.name account_name,a.status account_status,c.name category_name,c.status category_status,(SELECT COUNT(*) FROM goal_transaction gt WHERE gt.transaction_id=t.id) goals_count FROM transactions t JOIN accounts a ON a.id=t.account_id JOIN categories c ON c.id=t.category_id WHERE a.user_id=?';
        $p = [$userId];
        if ($activeAccountOnly) {
            $sql .= " AND a.status='active'";
        }
        foreach (['account_id' => 't.account_id', 'type' => 't.type'] as $k => $col) {
            if (isset($filters[$k]) && $filters[$k] !== '') {
                $sql .= " AND $col=?";
                $p[] = $filters[$k];
            }
        }
        if (!empty($filters['date'])) {
            $sql .= ' AND t.transaction_date=?';
            $p[] = $filters['date'];
        }
        if (!empty($filters['start_date'])) {
            $sql .= ' AND t.transaction_date>=?';
            $p[] = $filters['start_date'];
        }
        if (!empty($filters['end_date'])) {
            $sql .= ' AND t.transaction_date<=?';
            $p[] = $filters['end_date'];
        }
        $sql .= ' ORDER BY t.transaction_date DESC,t.id DESC';
        $s = self::db()->prepare($sql);
        $s->execute($p);
        return $s->fetchAll();
    }
    public static function findForUser(int $id, int $userId): ?array
    {
        $s = self::db()->prepare('SELECT t.*,a.user_id,a.name account_name,c.name category_name,c.type category_type FROM transactions t JOIN accounts a ON a.id=t.account_id JOIN categories c ON c.id=t.category_id WHERE t.id=? AND a.user_id=? LIMIT 1');
        $s->execute([$id, $userId]);
        return $s->fetch() ?: null;
    }
    public static function create(array $data): array
    {
        $s = self::db()->prepare('INSERT INTO transactions (account_id,category_id,description,amount,type,transaction_date,created_at,updated_at) VALUES (?,?,?,?,?,?,NOW(),NOW())');
        $s->execute([$data['account_id'], $data['category_id'], trim($data['description']), $data['amount'], $data['type'], $data['transaction_date']]);
        return self::findForUser((int)self::db()->lastInsertId(), (int)$data['user_id']);
    }
    public static function update(int $id, array $data): void
    {
        $s = self::db()->prepare('UPDATE transactions SET account_id=?,category_id=?,description=?,amount=?,type=?,transaction_date=?,updated_at=NOW() WHERE id=?');
        $s->execute([$data['account_id'], $data['category_id'], trim($data['description']), $data['amount'], $data['type'], $data['transaction_date'], $id]);
    }
    public static function delete(int $id): void
    {
        $s = self::db()->prepare('DELETE FROM transactions WHERE id=?');
        $s->execute([$id]);
    }
    public static function hasGoals(int $id): bool
    {
        $s = self::db()->prepare('SELECT COUNT(*) FROM goal_transaction WHERE transaction_id=?');
        $s->execute([$id]);
        return (int)$s->fetchColumn() > 0;
    }
}
