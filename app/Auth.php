<?php
namespace Resaname;
use PDO;
final class Auth {
    public static function user(): ?array { return $_SESSION['editor_user'] ?? null; }
    public static function requireLogin(): void {
        if (!self::user()) { header('Location: /login.php'); exit; }
    }
    public static function login(PDO $db,string $username,string $password): bool {
        $q=$db->prepare("SELECT id,username,password_hash,display_name,role FROM editorial_users WHERE username=? AND is_active=1 LIMIT 1");
        $q->execute([$username]); $u=$q->fetch();
        if (!$u || !password_verify($password,$u['password_hash'])) return false;
        session_regenerate_id(true);
        unset($u['password_hash']); $_SESSION['editor_user']=$u;
        $db->prepare("UPDATE editorial_users SET last_login_at=NOW() WHERE id=?")->execute([$u['id']]);
        return true;
    }
}
