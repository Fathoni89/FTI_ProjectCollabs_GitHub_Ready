<?php
declare(strict_types=1);
require __DIR__.'/app/bootstrap.php';
$token=(string)($config['setup_token']??'');$supplied=(string)($_POST['token']??$_GET['token']??'');
if(strlen($token)<32||str_starts_with($token,'GANTI_')||!hash_equals($token,$supplied)){http_response_code(404);exit('Installer tidak aktif atau token salah. Periksa README.');}
if((int)(one('SELECT COUNT(*) AS n FROM users WHERE role="admin"')['n']??0)>0){http_response_code(403);exit('Administrator sudah ada. HAPUS install.php dari hosting.');}
$message='';
if($_SERVER['REQUEST_METHOD']==='POST'){
    csrf_check();$name=post('full_name',120);$email=strtolower(post('email',160));$pass=post('password',500);
    if(strlen($name)<3||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pass)<12)$message='Lengkapi nama, email, dan kata sandi minimal 12 karakter.';
    else{
        run('INSERT INTO users(full_name,email,password_hash,role) VALUES(?,?,?,"admin")',[$name,$email,password_hash($pass,PASSWORD_DEFAULT)]);
        echo '<!doctype html><html lang="id"><meta charset="utf-8"><title>Instalasi sukses</title><body style="font:18px system-ui;max-width:700px;margin:8vh auto"><h1>Administrator berhasil dibuat</h1><p>HAPUS file <strong>install.php</strong> dari server. Setelah itu, login dari <a href="'.h(route_url('login')).'">portal FTI Fest</a>.</p></body></html>';exit;
    }
}
?><!doctype html><html lang="id"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Aktivasi Administrator</title><link rel="stylesheet" href="<?=h(app_url('/assets/style.css'))?>"><main class="auth-container"><section class="panel auth-card"><h1>Aktivasi administrator</h1><p>Buat akun admin pertama. Hapus file install.php setelah berhasil.</p><?php if($message):?><div class="alert error"><?=h($message)?></div><?php endif;?><form method="post"><input type="hidden" name="token" value="<?=h($token)?>"><?=csrf_field()?><label>Nama lengkap<input name="full_name" required maxlength="120"></label><label>Email admin<input name="email" type="email" required maxlength="160"></label><label>Password (min. 12 karakter)<input name="password" type="password" minlength="12" required></label><button class="btn primary" type="submit">Buat administrator</button></form></section></main></html>
