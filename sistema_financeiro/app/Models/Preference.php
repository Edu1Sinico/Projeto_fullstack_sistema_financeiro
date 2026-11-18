<?php

declare(strict_types=1);

namespace App\Models;

final class Preference extends Model
{
    public const THEMES = ['light', 'dark'];
    public static function forUser(int $userId): array
    {
        $s = self::db()->prepare('SELECT * FROM user_preferences WHERE user_id=? LIMIT 1');
        $s->execute([$userId]);
        $p = $s->fetch();
        if (!$p) {
            $i = self::db()->prepare("INSERT INTO user_preferences (user_id,theme,goal_order,created_at,updated_at) VALUES (?,'light',NULL,NOW(),NOW())");
            $i->execute([$userId]);
            return self::forUser($userId);
        }
        $p['goal_order'] = $p['goal_order'] ? json_decode($p['goal_order'], true) : [];
        return $p;
    }
    public static function update(int $userId, array $data): array
    {
        $p = self::forUser($userId);
        $theme = $data['theme'] ?? $p['theme'];
        $order = array_key_exists('goal_order', $data) ? array_values($data['goal_order']) : $p['goal_order'];
        $s = self::db()->prepare('UPDATE user_preferences SET theme=?,goal_order=?,updated_at=NOW() WHERE user_id=?');
        $s->execute([$theme, json_encode($order), $userId]);
        return self::forUser($userId);
    }
}
