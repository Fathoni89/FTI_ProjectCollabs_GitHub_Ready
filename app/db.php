<?php
declare(strict_types=1);
function db(): PDO {
    static $instance = null;
    if ($instance instanceof PDO) return $instance;
    global $config;
    $d=$config['db']??[];
    $dsn='mysql:host='.($d['host']??'localhost').';port='.($d['port']??'3306').';dbname='.($d['name']??'').';charset=utf8mb4';
    try {
        $instance = new PDO($dsn,(string)($d['user']??''),(string)($d['pass']??''),[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
        ]);
        return $instance;
    } catch(PDOException $e) {
        error_log('[FTIFest DB] '.$e->getMessage());
        http_response_code(503);
        exit('Koneksi database belum tersedia. Periksa config.php, hak akses database, dan impor skema SQL.');
    }
}
function one(string $sql, array $args=[]): ?array {
    $st=db()->prepare($sql); $st->execute($args); $row=$st->fetch(); return $row ?: null;
}
function all(string $sql, array $args=[]): array {
    $st=db()->prepare($sql); $st->execute($args); return $st->fetchAll();
}
function run(string $sql, array $args=[]): int {
    $st=db()->prepare($sql); $st->execute($args); return $st->rowCount();
}
function setting(string $key, string $fallback=''): string {
    $row=one('SELECT setting_value FROM settings WHERE setting_key=?',[$key]);
    return $row ? (string)$row['setting_value'] : $fallback;
}
function audit(string $action, ?int $projectId=null, string $details=''): void {
    $user=current_user();
    run('INSERT INTO audit_logs(actor_id,project_id,action,details) VALUES(?,?,?,?)',
        [$user ? (int)$user['id'] : null,$projectId,$action,mb_safe($details,1000)]);
}
