<?php
declare(strict_types=1);
function current_user(): ?array {
    static $cache=[];
    $id=(int)($_SESSION['uid']??0);
    if(!$id)return null;
    if(!array_key_exists($id,$cache)) {
        $cache[$id]=one('SELECT id,full_name,email,nim,class_name,role,is_active FROM users WHERE id=?',[$id]);
    }
    return $cache[$id] && (int)$cache[$id]['is_active']===1 ? $cache[$id] : null;
}
function require_login(): array {
    $user=current_user(); if(!$user){flash('error','Silakan login terlebih dahulu.');redirect_to('login');}
    return $user;
}
function can_role(array $roles): bool {$user=current_user();return $user!==null && in_array($user['role'],$roles,true);}
function require_role(array $roles): array {$user=require_login();if(!in_array($user['role'],$roles,true))bail('Anda tidak memiliki izin untuk mengakses halaman ini.',403);return $user;}
function attempt_login(string $email, string $password): bool {
    $record=one('SELECT id,password_hash,is_active FROM users WHERE email=?',[strtolower(trim($email))]);
    if(!$record || (int)$record['is_active']!==1 || !password_verify($password,$record['password_hash']))return false;
    session_regenerate_id(true);
    $_SESSION['uid']=(int)$record['id'];
    return true;
}

function login_fingerprint(string $email): string {
    return hash('sha256', strtolower(trim($email)).'|'.(string)($_SERVER['REMOTE_ADDR']??'localhost'));
}
function too_many_login_attempts(string $email): bool {
    $row=one('SELECT COUNT(*) AS n FROM login_attempts WHERE fingerprint=? AND attempted_at > DATE_SUB(NOW(), INTERVAL 15 MINUTE)',[login_fingerprint($email)]);
    return (int)($row['n']??0)>=8;
}
function note_login_failure(string $email): void {
    run('INSERT INTO login_attempts(fingerprint) VALUES(?)',[login_fingerprint($email)]);
}
function clear_login_failures(string $email): void {
    run('DELETE FROM login_attempts WHERE fingerprint=?',[login_fingerprint($email)]);
}
