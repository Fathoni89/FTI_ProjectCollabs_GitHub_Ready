<?php
declare(strict_types=1);

$configFile = dirname(__DIR__) . '/config.php';
if (!is_file($configFile)) {
    http_response_code(503);
    header('Content-Type: text/html; charset=utf-8');
    echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Konfigurasi awal</title><style>body{font:16px system-ui;background:#f3f6fa;color:#17304c;padding:10vh 8vw}main{max-width:680px;background:white;padding:36px;border-radius:18px;box-shadow:0 12px 40px #162b4a18}code{background:#edf3f9;padding:2px 6px}</style><main><h1>Portal belum dikonfigurasi</h1><p>Salin <code>config.example.php</code> menjadi <code>config.php</code>, isi kredensial MySQL dan URL domain, kemudian impor <code>database/schema.sql</code>. Lihat README.md pada paket aplikasi.</p></main></html>';
    exit;
}
$config = require $configFile;
if (!is_array($config)) { http_response_code(500); exit('Kesalahan konfigurasi.'); }
date_default_timezone_set((string)($config['timezone'] ?? 'Asia/Jakarta'));
$https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
if (PHP_SAPI !== 'cli') {
    session_name('FTIFESTSESSID');
    session_set_cookie_params(['lifetime'=>0, 'path'=>'/', 'secure'=>$https, 'httponly'=>true, 'samesite'=>'Lax']);
    session_start();
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header("Content-Security-Policy: default-src 'self'; base-uri 'self'; frame-ancestors 'self'; form-action 'self'; object-src 'none'; script-src 'self'; style-src 'self'; img-src 'self' data:; connect-src 'self';");
    header('Cache-Control: private, no-store');
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/projects.php';
