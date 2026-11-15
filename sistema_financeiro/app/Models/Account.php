<?php
declare(strict_types=1);
namespace App\Models;
final class Account extends Model
{
    public const TYPES=['checking','savings','wallet']; public const STATUSES=['active','inactive'];
    public static function allForUser(int $userId,bool $activeOnly=false): array { $sql='SELECT * FROM accounts WHERE user_id=?'.($activeOnly?" AND status='active'":'').' ORDER BY created_at DESC,id DESC'; $s=self::db()->prepare($sql); $s->execute([$userId]); return $s->fetchAll(); }
    public static function findForUser(int $id,int $userId,bool $lock=false): ?array { $s=self::db()->prepare('SELECT * FROM accounts WHERE id=? AND user_id=?'.($lock?' FOR UPDATE':'').' LIMIT 1'); $s->execute([$id,$userId]); return $s->fetch() ?: null; }
    public static function create(int $userId,array $data): array { $s=self::db()->prepare('INSERT INTO accounts (user_id,name,type,status,balance,created_at,updated_at) VALUES (?,?,?,?,?,NOW(),NOW())'); $s->execute([$userId,trim($data['name']),$data['type'],$data['status']??'active',$data['balance']??0]); return self::findForUser((int)self::db()->lastInsertId(),$userId); }
    public static function update(int $id,int $userId,array $data): array { $s=self::db()->prepare('UPDATE accounts SET name=?,type=?,status=?,updated_at=NOW() WHERE id=? AND user_id=?'); $s->execute([trim($data['name']),$data['type'],$data['status'],$id,$userId]); return self::findForUser($id,$userId); }
    public static function updateBalance(int $id,float $balance): void { $s=self::db()->prepare('UPDATE accounts SET balance=?,updated_at=NOW() WHERE id=?'); $s->execute([number_format($balance,2,'.',''),$id]); }
    public static function delete(int $id,int $userId): void { $s=self::db()->prepare('DELETE FROM accounts WHERE id=? AND user_id=?'); $s->execute([$id,$userId]); }
}
