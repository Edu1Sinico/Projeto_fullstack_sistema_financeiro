<?php

declare(strict_types=1);

namespace App\Models;

final class User extends Model
{
    public static function find(int $id): ?array
    {
        $s = self::db()->prepare('SELECT id,name,email,password,created_at,updated_at FROM users WHERE id=? LIMIT 1');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
    public static function findByEmail(string $email): ?array
    {
        $s = self::db()->prepare('SELECT id,name,email,password,created_at,updated_at FROM users WHERE email=? LIMIT 1');
        $s->execute([strtolower(trim($email))]);
        return $s->fetch() ?: null;
    }
    public static function create(string $name, string $email, string $password): array
    {
        $s = self::db()->prepare('INSERT INTO users (name,email,password,created_at,updated_at) VALUES (?,?,?,NOW(),NOW())');
        $s->execute([trim($name), strtolower(trim($email)), password_hash($password, PASSWORD_DEFAULT)]);
        return self::find((int)self::db()->lastInsertId());
    }
    public static function updateName(int $id, string $name): array
    {
        $s = self::db()->prepare('UPDATE users SET name=?,updated_at=NOW() WHERE id=?');
        $s->execute([trim($name), $id]);
        return self::find($id);
    }
    public static function updatePassword(int $id, string $password): void
    {
        $s = self::db()->prepare('UPDATE users SET password=?,updated_at=NOW() WHERE id=?');
        $s->execute([password_hash($password, PASSWORD_DEFAULT), $id]);
    }
    public static function delete(int $id): void
    {
        $s = self::db()->prepare('DELETE FROM users WHERE id=?');
        $s->execute([$id]);
    }
    public static function publicData(array $user): array
    {
        unset($user['password']);
        return $user;
    }
}
