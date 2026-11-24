<?php
$me=current_user();
$eventName=setting('event_name','FTI FEST 2027');
$eventMonth=setting('event_month','Januari 2027');
$pageTitle=match($view){
    'home'=>'Galeri Inovasi','dashboard'=>'Dashboard','edit'=>'Editor Proyek','project'=>'Detail Proyek','review'=>'Review Proyek','review_detail'=>'Review Proyek','manage'=>'Manajemen Pameran','users'=>'Pengguna','event'=>'Pengaturan Acara','account'=>'Akun Saya','print'=>'Kartu Produk',default=>'FTI Fest'};
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="description" content="Portal pengumpulan dan showcase Proyek Kolaboratif Semester V – FTI Universitas Sebelas April.">
<title><?=h($pageTitle)?> • <?=h($eventName)?></title>
<link rel="stylesheet" href="<?=h(app_url('/assets/style.css'))?>">
<script src="<?=h(app_url('/assets/qr-local.js'))?>" defer></script>
<script src="<?=h(app_url('/assets/app.js'))?>" defer></script>
</head>
<body>
<a href="#main" class="skip-link">Lewati ke konten</a>
<header class="site-header">
  <div class="container nav-wrap">
    <a href="<?=h(route_url('home'))?>" class="brand" aria-label="Beranda FTI Fest">
      <span class="brand-icon">F</span>
      <span class="brand-text"><strong>FTI<span>FEST</span></strong><small>PROJECT SHOWCASE</small></span>
    </a>
    <button class="nav-toggle" type="button" aria-controls="primary-nav" aria-expanded="false"><span class="nav-bars">☰</span><span class="sr-only">Buka menu</span></button>
    <nav id="primary-nav" class="site-nav" aria-label="Navigasi utama">
      <a href="<?=h(route_url('home'))?>" <?=($view==='home')?'aria-current="page"':''?>>Galeri Proyek</a>
      <?php if($me): ?>
        <a href="<?=h(route_url('dashboard'))?>">Dashboard</a>
        <?php if($me['role']==='student'):?><a href="<?=h(route_url('edit'))?>">Kumpulkan Proyek</a><?php endif;?>
        <?php if(in_array($me['role'],['lecturer','admin'],true)):?><a href="<?=h(route_url('review'))?>">Review</a><?php endif;?>
        <?php if(in_array($me['role'],['committee','admin'],true)):?><a href="<?=h(route_url('manage'))?>">Jadwal & Booth</a><?php endif;?>
        <?php if($me['role']==='admin'):?><a href="<?=h(route_url('event'))?>">Acara</a><?php endif;?>
        <a href="<?=h(route_url('account'))?>" class="nav-user" title="Pengaturan akun"><?=h($me['full_name'])?></a>
        <form method="post" class="inline-form"><?=csrf_field()?><input type="hidden" name="action" value="logout"><button type="submit" class="btn nav-logout">Keluar</button></form>
      <?php else:?>
        <a href="<?=h(route_url('login'))?>">Masuk</a>
        <a class="btn nav-cta" href="<?=h(route_url('register'))?>">Daftar Mahasiswa</a>
      <?php endif; ?>
    </nav>
  </div>
</header>
<main id="main">
<div class="container flashes" role="status" aria-live="polite">
<?php foreach(flashes() as $f):?><div class="alert <?=h($f['type'])?>"><strong><?=($f['type']==='error'?'Perhatian':'Informasi')?></strong><span><?=h($f['message'])?></span><button type="button" class="alert-close" aria-label="Tutup pemberitahuan">×</button></div><?php endforeach;?>
</div>
