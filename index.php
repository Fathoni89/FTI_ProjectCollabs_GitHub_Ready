<?php
declare(strict_types=1);
require __DIR__ . '/app/bootstrap.php';

$action=(string)($_POST['action']??'');
if($_SERVER['REQUEST_METHOD']==='POST') {
    csrf_check();
    try {
        switch($action){
        case 'login':
            $email=post('email',160);
            run('DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 2 DAY)');
            if(too_many_login_attempts($email))throw new RuntimeException('Terlalu banyak percobaan pada akun ini. Coba 15 menit lagi.');
            if(!attempt_login($email,(string)($_POST['password']??''))){note_login_failure($email);throw new RuntimeException('Email atau kata sandi tidak sesuai.');}
            clear_login_failures($email);flash('success','Login berhasil.');redirect_to('dashboard');
        case 'register':
            global $config;
            $reg=(string)($config['registration_code']??'');
            if(strlen($reg)<10 || str_starts_with($reg,'GANTI_'))throw new RuntimeException('Pendaftaran mandiri belum diaktifkan oleh admin.');
            if(!hash_equals($reg,post('registration_code',120)))throw new RuntimeException('Kode pendaftaran tidak sesuai.');
            $name=post('name',120);$email=strtolower(post('email',160));$nim=post('nim',40);
            $class=post('class_name',20);$pwd=(string)($_POST['password']??'');
            if(strlen($name)<3 || !filter_var($email,FILTER_VALIDATE_EMAIL) || !allowed_class($class) || strlen($nim)<5 || strlen($pwd)<12)
                throw new RuntimeException('Lengkapi nama, email, NIM, kelas, dan kata sandi minimal 12 karakter.');
            if(one('SELECT id FROM users WHERE email=?',[$email]))throw new RuntimeException('Email sudah digunakan.');
            run('INSERT INTO users(full_name,email,nim,class_name,password_hash,role) VALUES(?,?,?,?,?,"student")',[$name,$email,$nim,$class,password_hash($pwd,PASSWORD_DEFAULT)]);
            flash('success','Akun berhasil dibuat. Silakan masuk.');redirect_to('login');
        case 'logout':
            unset($_SESSION['uid']);session_regenerate_id(true);flash('success','Anda telah keluar.');redirect_to('home');
        case 'save_project':
            $u=require_role(['student']);
            if(setting('submissions_open','1')!=='1')throw new RuntimeException('Periode pengumpulan proyek sudah ditutup.');
            $id=(int)($_POST['id']??0);
            $old=$id?project_by_id($id):null;
            if($id && (!$old || !can_edit_project($old)))throw new RuntimeException('Proyek ini tidak dapat diubah.');
            $title=post('title',180);$groupCode=post('group_code',35);$groupName=post('group_name',100);
            $className=post('class_name',20);$category=post('category',30);$intent=post('intent',12);
            if(!$title || !$groupCode || !$groupName || !allowed_class($className))throw new RuntimeException('Judul, kode kelompok, nama kelompok, dan kelas wajib diisi.');
            if(!array_key_exists($category,['campus'=>1,'umkm'=>1,'agritech'=>1,'tourism'=>1,'environment'=>1,'community'=>1,'other'=>1]))throw new RuntimeException('Kategori tidak valid.');
            if(!preg_match('/^[a-zA-Z0-9_-]{2,35}$/',$groupCode))throw new RuntimeException('Kode kelompok hanya boleh huruf, angka, - dan _.');
            if(!in_array($intent,['save','submit'],true))throw new RuntimeException('Aksi tidak valid.');
            $input=[
                'short_description'=>post('short_description',350),'problem'=>post('problem',10000),
                'solution'=>post('solution',10000),'target_users'=>post('target_users',350),
                'key_features'=>post('key_features',10000),'tech_stack'=>post('tech_stack',500),
                'github_url'=>post('github_url',500),'figma_url'=>post('figma_url',500),
                'live_url'=>post('live_url',500),'video_url'=>post('video_url',500),
            ];
            foreach(['github_url','figma_url','live_url','video_url'] as $key)if(!valid_http_url($input[$key]))throw new RuntimeException('Tautan '.str_replace('_',' ',$key).' harus URL HTTP/HTTPS yang valid.');
            $members=parse_member_input();
            if($intent==='submit' && (!$input['short_description']||!$input['problem']||!$input['solution']||!$input['target_users']||!$input['key_features']||count($members)<1))
                throw new RuntimeException('Lengkapi ringkasan, masalah, solusi, pengguna, fitur utama, dan anggota sebelum kirim review.');
            $uploaded=[];
            try {
                foreach(['poster','cover','report'] as $kind){$file=upload_file($kind.'_file',$kind);if($file)$uploaded[$kind]=$file;}
                $postFile=$uploaded['poster']??($old['poster_file']??null);
                $coverFile=$uploaded['cover']??($old['cover_file']??null);
                $reportFile=$uploaded['report']??($old['report_file']??null);
                if($intent==='submit' && (!$postFile||!$reportFile||!array_filter([$input['github_url'],$input['figma_url'],$input['live_url'],$input['video_url']])))
                    throw new RuntimeException('Untuk dikirim, wajib ada poster, laporan PDF, dan minimal satu tautan (demo/video/repo/Figma).');
                $academic=setting('academic_year','2026/2027');
                db()->beginTransaction();
                if($old){
                    run('UPDATE projects SET title=?,class_name=?,group_code=?,group_name=?,category=?,short_description=?,problem=?,solution=?,target_users=?,key_features=?,tech_stack=?,github_url=?,figma_url=?,live_url=?,video_url=?,poster_file=?,cover_file=?,report_file=?,status=?,submitted_at=? WHERE id=?',
                        [$title,$className,$groupCode,$groupName,$category,$input['short_description'],$input['problem'],$input['solution'],$input['target_users'],$input['key_features'],$input['tech_stack'],
                         $input['github_url']?:null,$input['figma_url']?:null,$input['live_url']?:null,$input['video_url']?:null,$postFile,$coverFile,$reportFile,$intent==='submit'?'submitted':$old['status'],
                         $intent==='submit'?date('Y-m-d H:i:s'):$old['submitted_at'],$id]);
                    if($intent==='submit')run('UPDATE projects SET review_note=NULL,reviewed_by=NULL,reviewed_at=NULL WHERE id=?',[$id]);
                }else{
                    $slug=slugify($title).'-'.bin2hex(random_bytes(4));
                    run('INSERT INTO projects(owner_id,academic_year,class_name,group_code,group_name,title,slug,category,short_description,problem,solution,target_users,key_features,tech_stack,github_url,figma_url,live_url,video_url,poster_file,cover_file,report_file,status,submitted_at) VALUES(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)',
                       [$u['id'],$academic,$className,$groupCode,$groupName,$title,$slug,$category,$input['short_description'],$input['problem'],$input['solution'],$input['target_users'],$input['key_features'],$input['tech_stack'],
                        $input['github_url']?:null,$input['figma_url']?:null,$input['live_url']?:null,$input['video_url']?:null,$postFile,$coverFile,$reportFile,$intent==='submit'?'submitted':'draft',$intent==='submit'?date('Y-m-d H:i:s'):null]);
                    $id=(int)db()->lastInsertId();
                }
                replace_members($id,$members);
                db()->commit();
                foreach($uploaded as $kind=>$file){$former=$old[$kind.'_file']??null; if($former && $former!==$file)@unlink(__DIR__.'/storage/uploads/'.$former);}
                audit($intent==='submit'?'project_submitted':'project_saved',$id);
                flash('success',$intent==='submit'?'Proyek dikirim untuk review dosen/admin.':'Draf proyek berhasil disimpan.');
                redirect_to('dashboard');
            }catch(Throwable $e){
                if(db()->inTransaction())db()->rollBack();
                foreach($uploaded as $file)@unlink(__DIR__.'/storage/uploads/'.$file);
                throw $e;
            }
        case 'review_project':
            $u=require_role(['admin','lecturer']);$id=(int)($_POST['id']??0);
            $p=project_by_id($id);if(!$p||$p['status']!=='submitted')throw new RuntimeException('Hanya proyek yang menunggu review dapat dinilai.');
            $decision=post('decision',15);if(!in_array($decision,['approved','revision'],true))throw new RuntimeException('Keputusan tidak valid.');
            $note=post('review_note',3000);if($decision==='revision' && strlen($note)<5)throw new RuntimeException('Catatan revisi harus jelas.');
            run('UPDATE projects SET status=?,review_note=?,reviewed_by=?,reviewed_at=NOW(),published=0 WHERE id=?',[$decision,$note,$u['id'],$id]);
            audit('project_'.$decision,$id,$note);flash('success','Keputusan review tersimpan.');redirect_to('review',['id'=>$id]);
        case 'save_schedule':
            require_role(['admin','committee']);$id=(int)($_POST['id']??0);$p=project_by_id($id);
            if(!$p||$p['status']!=='approved')throw new RuntimeException('Jadwal hanya untuk proyek yang telah disetujui.');
            $format=post('event_format',15);if(!in_array($format,['undecided','seminar','exhibition','both'],true))throw new RuntimeException('Format kegiatan tidak valid.');
            $date=post('scheduled_at',30);$scheduled=null;
            if($date!==''){
                $dt=DateTimeImmutable::createFromFormat('!Y-m-d\TH:i',$date);
                if(!$dt || $dt->format('Y-m-d\TH:i')!==$date)throw new RuntimeException('Tanggal/jam tidak valid.');
                $scheduled=$dt->format('Y-m-d H:i:s');
            }
            run('UPDATE projects SET event_format=?,booth_number=?,session_label=?,scheduled_at=?,venue=? WHERE id=?',[$format,post('booth_number',40)?:null,post('session_label',100)?:null,$scheduled,post('venue',150)?:null,$id]);
            audit('schedule_updated',$id);flash('success','Jadwal/booth berhasil diperbarui.');redirect_to('manage');
        case 'toggle_publish':
            require_role(['admin']);$id=(int)($_POST['id']??0);$p=project_by_id($id);
            if(!$p||$p['status']!=='approved')throw new RuntimeException('Hanya proyek disetujui yang dapat dipublikasikan.');
            $published=(int)$p['published']?0:1;
            run('UPDATE projects SET published=? WHERE id=?',[$published,$id]);
            audit($published?'published':'unpublished',$id);flash('success',$published?'Proyek tampil di katalog publik.':'Proyek disembunyikan dari katalog.');redirect_to('manage');
        case 'toggle_featured':
            require_role(['admin']);$id=(int)($_POST['id']??0);$p=project_by_id($id);
            if(!$p||$p['status']!=='approved'||!(int)$p['published'])throw new RuntimeException('Hanya proyek publik yang dapat diunggulkan.');
            run('UPDATE projects SET featured=? WHERE id=?',[(int)$p['featured']?0:1,$id]);
            audit('featured_toggled',$id);flash('success','Pengaturan sorotan diperbarui.');redirect_to('manage');
        case 'save_settings':
            require_role(['admin']);
            if(!preg_match('/^\d{4}\/\d{4}$/',post('academic_year',20)))throw new RuntimeException('Tahun akademik harus berformat 2026/2027.');
            $eventDate=post('event_date',30);
            if($eventDate!==''){
                $parsed=DateTimeImmutable::createFromFormat('!Y-m-d',$eventDate);
                if(!$parsed || $parsed->format('Y-m-d')!==$eventDate)throw new RuntimeException('Tanggal acara tidak valid.');
            }
            $fields=['event_name'=>130,'academic_year'=>20,'event_month'=>60,'event_date'=>30,'event_location'=>170,'event_description'=>900];
            foreach($fields as $key=>$len){$v=post($key,$len);run('INSERT INTO settings(setting_key,setting_value) VALUES(?,?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',[$key,$v]);}
            $open=isset($_POST['submissions_open'])?'1':'0';
            run('INSERT INTO settings(setting_key,setting_value) VALUES("submissions_open",?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)',[$open]);
            audit('event_settings_updated');flash('success','Pengaturan kegiatan berhasil disimpan.');redirect_to('event');
        case 'change_password':
            $u=require_login();
            $current=(string)($_POST['current_password']??'');
            $new=(string)($_POST['new_password']??'');
            $record=one('SELECT password_hash FROM users WHERE id=?',[$u['id']]);
            if(!$record || !password_verify($current,$record['password_hash']))throw new RuntimeException('Kata sandi saat ini tidak sesuai.');
            if(strlen($new)<12 || strlen($new)>200)throw new RuntimeException('Kata sandi baru minimal 12 dan maksimal 200 karakter.');
            run('UPDATE users SET password_hash=? WHERE id=?',[password_hash($new,PASSWORD_DEFAULT),$u['id']]);
            session_regenerate_id(true);audit('password_changed');flash('success','Kata sandi diperbarui.');redirect_to('account');
        case 'reset_password':
            $admin=require_role(['admin']);$target=(int)($_POST['id']??0);$new=(string)($_POST['new_password']??'');
            if(strlen($new)<12 || strlen($new)>200 || !one('SELECT id FROM users WHERE id=?',[$target]))throw new RuntimeException('Akun atau kata sandi tidak valid (minimal 12 karakter).');
            run('UPDATE users SET password_hash=? WHERE id=?',[password_hash($new,PASSWORD_DEFAULT),$target]);
            audit('password_reset',null,'Target user ID '.$target);flash('success','Password akun diperbarui. Informasikan lewat jalur privat.');redirect_to('users');
        case 'toggle_user':
            $admin=require_role(['admin']);$target=(int)($_POST['id']??0);
            if($target===(int)$admin['id'])throw new RuntimeException('Tidak dapat menonaktifkan akun sendiri.');
            $record=one('SELECT role,is_active FROM users WHERE id=?',[$target]);
            if(!$record)throw new RuntimeException('Pengguna tidak ditemukan.');
            if($record['role']==='admin' && (int)$record['is_active']===1){
                $admins=(int)(one('SELECT COUNT(*) AS n FROM users WHERE role="admin" AND is_active=1')['n']??0);
                if($admins<=1)throw new RuntimeException('Admin aktif terakhir tidak dapat dinonaktifkan.');
            }
            run('UPDATE users SET is_active=? WHERE id=?',[(int)$record['is_active']?0:1,$target]);
            audit('user_status_changed',null,'Target user ID '.$target);flash('success','Status akun diubah.');redirect_to('users');
        case 'create_user':
            require_role(['admin']);$name=post('full_name',120);$email=strtolower(post('email',160));
            $pwd=(string)($_POST['password']??'');$role=post('role',20);$class=post('class_name',20);
            if(strlen($name)<3||!filter_var($email,FILTER_VALIDATE_EMAIL)||strlen($pwd)<12||!in_array($role,['student','committee','lecturer','admin'],true))throw new RuntimeException('Data pengguna tidak valid. Kata sandi minimal 12 karakter.');
            if($class!==''&&!allowed_class($class))throw new RuntimeException('Kelas tidak valid.');
            run('INSERT INTO users(full_name,email,nim,class_name,password_hash,role) VALUES(?,?,?,?,?,?)',[$name,$email,post('nim',40)?:null,$class?:null,password_hash($pwd,PASSWORD_DEFAULT),$role]);
            audit('user_created',null,$role.':'.$email);flash('success','Akun baru berhasil dibuat.');redirect_to('users');
        default: throw new RuntimeException('Tindakan tidak dikenali.');
        }
    } catch(RuntimeException $e){
        flash('error',$e->getMessage());
        $returnPages=['save_project'=>'edit','login'=>'login','register'=>'register','change_password'=>'account',
            'reset_password'=>'users','toggle_user'=>'users','create_user'=>'users','save_schedule'=>'manage',
            'toggle_publish'=>'manage','toggle_featured'=>'manage','review_project'=>'review','save_settings'=>'event'];
        $returnPage=$returnPages[$action]??'dashboard';
        $params=($action==='save_project' && !empty($_POST['id']))?['id'=>(int)$_POST['id']]:[];
        redirect_to($returnPage,$params);
    }
      catch(PDOException $e){error_log('[FTIFest action] '.$e->getMessage());flash('error','Operasi gagal. Periksa duplikasi email, kode kelompok, atau koneksi database.');redirect_to('dashboard');}
}
$page=(string)($_GET['page']??'home');
switch($page){
case 'home':
    $q=trim(mb_safe((string)($_GET['q']??''),100));$category=(string)($_GET['category']??'');
    $where=['p.status="approved"','p.published=1'];$params=[];
    if($category!==''&&array_key_exists($category,['campus'=>1,'umkm'=>1,'agritech'=>1,'tourism'=>1,'environment'=>1,'community'=>1,'other'=>1])){$where[]='p.category=?';$params[]=$category;}
    if($q!==''){$where[]='(p.title LIKE ? OR p.short_description LIKE ? OR p.group_name LIKE ?)';$pattern='%'.str_replace(['%','_'],['\\%','\\_'],$q).'%';array_push($params,$pattern,$pattern,$pattern);}
    $whereSQL=implode(' AND ',$where);
    $count=(int)(one('SELECT COUNT(*) AS n FROM projects p WHERE '.$whereSQL,$params)['n']??0);
    $n=min(1000,max(1,(int)($_GET['n']??1)));$size=12;$offset=($n-1)*$size;
    $projects=all('SELECT p.id,p.slug,p.title,p.group_name,p.class_name,p.category,p.short_description,p.cover_file,p.poster_file,p.event_format,p.booth_number,p.scheduled_at,p.featured FROM projects p WHERE '.$whereSQL.' ORDER BY p.featured DESC,p.updated_at DESC LIMIT '.$size.' OFFSET '.$offset,$params);
    $publicCount=(int)(one('SELECT COUNT(*) AS n FROM projects WHERE status="approved" AND published=1')['n']??0);
    render('home',compact('q','category','projects','count','n','size','publicCount'));break;
case 'login':render('login');break;
case 'register':render('register');break;
case 'dashboard':
    $u=require_login();
    if($u['role']==='student')$projects=all('SELECT * FROM projects WHERE owner_id=? ORDER BY updated_at DESC',[$u['id']]);
    elseif($u['role']==='lecturer')$projects=all('SELECT * FROM projects WHERE status="submitted" ORDER BY submitted_at ASC');
    else $projects=all('SELECT * FROM projects ORDER BY updated_at DESC LIMIT 50');
    render('dashboard',compact('u','projects'));break;
case 'edit':
    require_role(['student']);$id=(int)($_GET['id']??0);
    $project=$id?project_by_id($id):null;
    if($id && (!$project || !can_edit_project($project)))bail('Proyek tidak ditemukan atau tidak dapat diedit.',403);
    $members=$id?member_rows($id):[];
    render('edit',compact('project','members'));break;
case 'project':
case 'print':
    $id=(int)($_GET['id']??0);$slug=trim((string)($_GET['slug']??''));
    $project=$id?project_by_id($id):($slug?one('SELECT p.*,u.full_name AS submitter_name FROM projects p JOIN users u ON p.owner_id=u.id WHERE p.slug=?',[$slug]):null);
    if(!$project || !can_read_project($project))bail('Proyek tidak ditemukan atau belum dipublikasikan.',404);
    $members=member_rows((int)$project['id']);render($page,compact('project','members'));break;
case 'review':
    require_role(['admin','lecturer']);$id=(int)($_GET['id']??0);
    if($id){$project=project_by_id($id);if(!$project)bail('Proyek tidak ditemukan.',404);$members=member_rows($id);render('review_detail',compact('project','members'));}
    else{$projects=all('SELECT p.*,u.full_name AS submitter_name FROM projects p JOIN users u ON p.owner_id=u.id WHERE p.status IN ("submitted","approved","revision") ORDER BY (p.status="submitted") DESC,p.updated_at DESC');render('review',compact('projects'));}
    break;
case 'manage':
    require_role(['admin','committee']);$projects=all('SELECT p.*,u.full_name AS submitter_name FROM projects p JOIN users u ON p.owner_id=u.id WHERE p.status="approved" ORDER BY p.scheduled_at IS NULL,p.scheduled_at,p.class_name,p.group_code');render('manage',compact('projects'));break;
case 'event':require_role(['admin']);render('event');break;
case 'account':require_login();render('account');break;
case 'users':
    require_role(['admin']);$users=all('SELECT id,full_name,email,nim,class_name,role,is_active,created_at FROM users ORDER BY created_at DESC LIMIT 300');render('users',compact('users'));break;
case 'export':
    require_role(['admin','committee']);
    $rows=all('SELECT p.class_name,p.group_code,p.group_name,p.title,p.category,p.status,p.event_format,p.booth_number,p.session_label,p.scheduled_at,p.venue,p.published,u.full_name AS coordinator,u.email FROM projects p JOIN users u ON p.owner_id=u.id ORDER BY p.class_name,p.group_code');
    header('Content-Type: text/csv; charset=utf-8');header('Content-Disposition: attachment; filename="rekap-fti-fest.csv"');
    echo "\xEF\xBB\xBF";$out=fopen('php://output','w');
    fputcsv($out,['Kelas','Kode Kelompok','Nama Kelompok','Produk','Kategori','Status','Format','Booth','Sesi','Jadwal','Lokasi','Publik','Koordinator','Email']);
    foreach($rows as $row){$safe=array_map(static function($v){$s=(string)$v;return preg_match('/^[=+\-@\t\r]/u',$s)?"'".$s:$s;},array_values($row));fputcsv($out,$safe);}
    fclose($out);exit;
default:bail('Halaman tidak ditemukan.',404);
}
