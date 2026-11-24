<?php
declare(strict_types=1);
function project_by_id(int $id): ?array {return one('SELECT p.*,u.full_name AS submitter_name FROM projects p JOIN users u ON p.owner_id=u.id WHERE p.id=?',[$id]);}
function can_read_project(array $p): bool {
    if($p['status']==='approved' && (int)$p['published']===1)return true;
    $user=current_user(); if(!$user)return false;
    return (int)$p['owner_id']===(int)$user['id'] || in_array($user['role'],['admin','lecturer','committee'],true);
}
function can_edit_project(array $p): bool {
    $user=current_user(); return $user && $user['role']==='student' && (int)$p['owner_id']===(int)$user['id'] && in_array($p['status'],['draft','revision'],true);
}
function member_rows(int $id): array {return all('SELECT full_name,nim,contribution FROM project_members WHERE project_id=? ORDER BY ordering,id',[$id]);}
function parse_member_input(): array {
    $names=$_POST['member_name']??[];$nims=$_POST['member_nim']??[];$roles=$_POST['member_contribution']??[];
    if(!is_array($names)||!is_array($nims)||!is_array($roles))throw new RuntimeException('Format anggota tidak valid.');
    $out=[];
    foreach(array_slice($names,0,15) as $idx=>$name){
        $name=trim(mb_safe((string)$name,120)); $nim=trim(mb_safe((string)($nims[$idx]??''),40));
        $role=trim(mb_safe((string)($roles[$idx]??''),130));
        if($name===''&&$nim==='')continue;
        if($name===''||$nim==='')throw new RuntimeException('Nama dan NIM setiap anggota harus diisi berpasangan.');
        $out[]=['name'=>$name,'nim'=>$nim,'role'=>$role];
    }
    if(count($names)>15)throw new RuntimeException('Maksimum 15 anggota per kelompok.');
    return $out;
}
function replace_members(int $projectId,array $members): void {
    run('DELETE FROM project_members WHERE project_id=?',[$projectId]);
    foreach($members as $i=>$m){run('INSERT INTO project_members(project_id,full_name,nim,contribution,ordering) VALUES(?,?,?,?,?)',[$projectId,$m['name'],$m['nim'],$m['role'],$i]);}
}
function upload_file(string $key, string $kind): ?string {
    if(!isset($_FILES[$key])||($_FILES[$key]['error']??UPLOAD_ERR_NO_FILE)===UPLOAD_ERR_NO_FILE)return null;
    $f=$_FILES[$key];
    if(!isset($f['error'])||$f['error']!==UPLOAD_ERR_OK || !is_uploaded_file($f['tmp_name']))throw new RuntimeException('Gagal mengunggah '.$kind.'. Periksa batas upload pada hosting.');
    global $config;
    $limit=((int)($config['uploads'][$kind.'_max_mb']??5))*1048576;
    if((int)$f['size']>$limit)throw new RuntimeException(ucfirst($kind).' melebihi batas '.($limit / 1048576).' MB.');
    $mime=(new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    $map=($kind==='report')?['application/pdf'=>'pdf']:['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];
    if(!isset($map[$mime]))throw new RuntimeException('Format file '.$kind.' tidak diperbolehkan.');
    $filename=bin2hex(random_bytes(20)).'.'.$map[$mime];
    $dest=dirname(__DIR__).'/storage/uploads/'.$filename;
    if(!move_uploaded_file($f['tmp_name'],$dest))throw new RuntimeException('Folder penyimpanan tidak dapat ditulis.');
    @chmod($dest,0640);
    return $filename;
}
function public_project_url(array $project): string {return route_url('project',['slug'=>$project['slug']]);}
