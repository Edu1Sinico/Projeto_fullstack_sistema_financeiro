<?php

declare(strict_types=1);

namespace App\Models;

final class Category extends Model
{
    public const TYPES = ['income', 'expense'];
    public const STATUSES = ['active', 'inactive'];
    public static function allForUser(int $userId, bool $activeOnly = false): array
    {
        $sql = 'SELECT * FROM categories WHERE user_id=?' . ($activeOnly ? " AND status='active'" : '') . ' ORDER BY type,name';
        $s = self::db()->prepare($sql);
        $s->execute([$userId]);
        return $s->fetchAll();
    }
    public static function findForUser(int $id, int $userId): ?array
    {
        $s = self::db()->prepare('SELECT * FROM categories WHERE id=? AND user_id=? LIMIT 1');
        $s->execute([$id, $userId]);
        return $s->fetch() ?: null;
    }
    public static function existsNameType(int $userId, string $name, string $type, ?int $ignoreId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM categories WHERE user_id=? AND name=? AND type=?';
        $p = [$userId, trim($name), $type];
        if ($ignoreId !== null) {
            $sql .= ' AND id<>?';
            $p[] = $ignoreId;
        }
        $s = self::db()->prepare($sql);
        $s->execute($p);
        return (int)$s->fetchColumn() > 0;
    }
    public static function create(int $userId, array $data): array
    {
        $s = self::db()->prepare('INSERT INTO categories (user_id,name,type,status,created_at,updated_at) VALUES (?,?,?,?,NOW(),NOW())');
        $s->execute([$userId, trim($data['name']), $data['type'], $data['status'] ?? 'active']);
        return self::findForUser((int)self::db()->lastInsertId(), $userId);
    }
    public static function update(int $id, int $userId, array $data): array
    {
        $s = self::db()->prepare('UPDATE categories SET name=?,type=?,status=?,updated_at=NOW() WHERE id=? AND user_id=?');
        $s->execute([trim($data['name']), $data['type'], $data['status'], $id, $userId]);
        return self::findForUser($id, $userId);
    }
    public static function hasTransactions(int $id): bool
    {
        $s = self::db()->prepare('SELECT COUNT(*) FROM transactions WHERE category_id=?');
        $s->execute([$id]);
        return (int)$s->fetchColumn() > 0;
    }
    public static function goalContribution(int $userId): array
    {
        $s = self::db()->prepare("SELECT * FROM categories WHERE user_id=? AND name='Contribuição para metas' AND type='expense' LIMIT 1");
        $s->execute([$userId]);
        $c = $s->fetch();
        if (!$c) {
            return self::create($userId, ['name' => 'Contribuição para metas', 'type' => 'expense', 'status' => 'inactive']);
        }
        if ($c['status'] !== 'inactive') {
            $c = self::update((int)$c['id'], $userId, ['name' => $c['name'], 'type' => 'expense', 'status' => 'inactive']);
        }
        return $c;
    }
    public static function delete(int $id, int $userId): void
    {
        $s = self::db()->prepare('DELETE FROM categories WHERE id=? AND user_id=?');
        $s->execute([$id, $userId]);
    }
}
