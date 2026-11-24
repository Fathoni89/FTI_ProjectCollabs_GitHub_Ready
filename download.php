<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';
$id=(int)($_GET['id']??0);$type=(string)($_GET['type']??'');
$map=['poster'=>'poster_file','cover'=>'cover_file','report'=>'report_file'];
if(!$id||!isset($map[$type])){http_response_code(404);exit('File tidak ditemukan.');}
$p=project_by_id($id);
if(!$p||!can_read_project($p)){http_response_code(404);exit('File tidak ditemukan.');}
if($type==='report'){
    $user=current_user();
    if(!$user || ((int)$p['owner_id']!==(int)$user['id'] && !in_array($user['role'],['admin','lecturer'],true))){http_response_code(403);exit('Laporan internal hanya untuk tim dan pengelola.');}
}
$filename=(string)($p[$map[$type]]??'');
if(!$filename||!preg_match('/^[a-f0-9]{40}\.(pdf|jpg|png|webp)$/',$filename)){http_response_code(404);exit('File tidak ditemukan.');}
$file=__DIR__.'/storage/uploads/'.$filename;
if(!is_file($file)){http_response_code(404);exit('File belum tersedia.');}
$mime=(new finfo(FILEINFO_MIME_TYPE))->file($file);
$allowed=$type==='report'?['application/pdf']:['image/jpeg','image/png','image/webp'];
if(!in_array($mime,$allowed,true)){http_response_code(415);exit('Format file tidak valid.');}
header('Content-Type: '.$mime);header('Content-Length: '.filesize($file));
header('Content-Disposition: '.($type==='report'?'attachment':'inline').'; filename="'.($type==='report'?'laporan-proyek.pdf':$filename).'"');
header('Cache-Control: private, max-age=120');
readfile($file);
