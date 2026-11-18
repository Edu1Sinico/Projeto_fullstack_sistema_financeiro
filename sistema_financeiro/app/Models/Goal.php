<?php

declare(strict_types=1);

namespace App\Models;

final class Goal extends Model
{
    public static function allForUser(int $userId): array
    {
        $s = self::db()->prepare('SELECT g.*,COALESCE(SUM(gt.contributed_amount),0) current_amount FROM goals g LEFT JOIN goal_transaction gt ON gt.goal_id=g.id WHERE g.user_id=? GROUP BY g.id ORDER BY g.created_at DESC,g.id DESC');
        $s->execute([$userId]);
        return array_map([self::class, 'withComputed'], $s->fetchAll());
    }
    public static function findForUser(int $id, int $userId): ?array
    {
        $s = self::db()->prepare('SELECT g.*,COALESCE(SUM(gt.contributed_amount),0) current_amount FROM goals g LEFT JOIN goal_transaction gt ON gt.goal_id=g.id WHERE g.id=? AND g.user_id=? GROUP BY g.id LIMIT 1');
        $s->execute([$id, $userId]);
        $g = $s->fetch();
        return $g ? self::withComputed($g) : null;
    }
    public static function create(int $userId, array $data): array
    {
        $s = self::db()->prepare('INSERT INTO goals (user_id,title,target_amount,deadline,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())');
        $s->execute([$userId, trim($data['title']), $data['target_amount'], $data['deadline'] ?: null]);
        return self::findForUser((int)self::db()->lastInsertId(), $userId);
    }
    public static function update(int $id, int $userId, array $data): array
    {
        $s = self::db()->prepare('UPDATE goals SET title=?,target_amount=?,deadline=?,updated_at=NOW() WHERE id=? AND user_id=?');
        $s->execute([trim($data['title']), $data['target_amount'], $data['deadline'] ?: null, $id, $userId]);
        return self::findForUser($id, $userId);
    }
    public static function delete(int $id, int $userId): void
    {
        $s = self::db()->prepare('DELETE FROM goals WHERE id=? AND user_id=?');
        $s->execute([$id, $userId]);
    }
    public static function contributions(int $goalId, int $userId): array
    {
        $s = self::db()->prepare('SELECT t.*,gt.contributed_amount FROM goal_transaction gt JOIN transactions t ON t.id=gt.transaction_id JOIN accounts a ON a.id=t.account_id WHERE gt.goal_id=? AND a.user_id=? ORDER BY t.transaction_date DESC,t.id DESC');
        $s->execute([$goalId, $userId]);
        return $s->fetchAll();
    }
    public static function attachContribution(int $goalId, int $transactionId, float $amount): void
    {
        $s = self::db()->prepare('INSERT INTO goal_transaction (goal_id,transaction_id,contributed_amount,created_at,updated_at) VALUES (?,?,?,NOW(),NOW())');
        $s->execute([$goalId, $transactionId, $amount]);
    }
    public static function contribution(int $goalId, int $transactionId): ?array
    {
        $s = self::db()->prepare('SELECT * FROM goal_transaction WHERE goal_id=? AND transaction_id=? LIMIT 1');
        $s->execute([$goalId, $transactionId]);
        return $s->fetch() ?: null;
    }
    public static function detachContribution(int $goalId, int $transactionId): void
    {
        $s = self::db()->prepare('DELETE FROM goal_transaction WHERE goal_id=? AND transaction_id=?');
        $s->execute([$goalId, $transactionId]);
    }
    public static function withComputed(array $g): array
    {
        $g['current_amount'] = number_format((float)$g['current_amount'], 2, '.', '');
        $g['completed'] = (float)$g['current_amount'] >= (float)$g['target_amount'];
        $g['progress_percentage'] = (float)$g['target_amount'] > 0 ? round(min(100, ((float)$g['current_amount'] / (float)$g['target_amount']) * 100), 2) : 0;
        return $g;
    }
}
