<?php
declare(strict_types=1);
function h(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
function mb_safe(string $s, int $max): string {return function_exists('mb_substr') ? mb_substr($s,0,$max) : substr($s,0,$max);}
function app_url(string $extra=''): string {global $config; return rtrim((string)$config['app_url'],'/') . $extra;}
function route_url(string $page='home', array $params=[]): string {
    $params=['page'=>$page]+$params;
    return app_url('/index.php?'.http_build_query($params));
}
function redirect_to(string $page, array $params=[]): never {
    header('Location: '.route_url($page,$params),true,303); exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf']=bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function csrf_field(): string {return '<input type="hidden" name="csrf" value="'.h(csrf_token()).'">';}
function csrf_check(): void {
    if (!hash_equals(csrf_token(), (string)($_POST['csrf']??''))) {http_response_code(419); exit('Sesi formulir kedaluwarsa. Muat ulang halaman.');}
}
function flash(string $type, string $message): void {$_SESSION['flash'][]=['type'=>$type,'message'=>$message];}
function flashes(): array {$v=$_SESSION['flash']??[];unset($_SESSION['flash']);return $v;}
function post(string $key, int $max=5000): string {return trim(mb_safe((string)($_POST[$key]??''),$max));}
function valid_http_url(string $url): bool {
    if ($url==='') return true;
    if(strlen($url)>500 || !filter_var($url,FILTER_VALIDATE_URL))return false;
    $parts=parse_url($url);
    return in_array(strtolower($parts['scheme']??''),['https','http'],true) && !isset($parts['user']) && !isset($parts['pass']);
}
function safe_url(string $url): string {return valid_http_url($url)?$url:'#';}
function category_name(string $value): string {
    return ['campus'=>'Smart Campus','umkm'=>'UMKM & Ekonomi Kreatif','agritech'=>'Pertanian & Agro','tourism'=>'Pariwisata','environment'=>'Lingkungan','community'=>'Layanan Masyarakat','other'=>'Lainnya'][$value]??'Lainnya';
}
function status_name(string $value): string {return ['draft'=>'Draf','submitted'=>'Menunggu Review','revision'=>'Perlu Revisi','approved'=>'Disetujui'][$value]??$value;}
function format_name(string $value): string {return ['undecided'=>'Belum dijadwalkan','seminar'=>'Seminar Produk','exhibition'=>'Pameran Produk','both'=>'Seminar + Pameran'][$value]??$value;}
function short_date(?string $v): string {if(!$v) return 'Belum ditentukan'; try{return (new DateTimeImmutable($v))->format('d M Y, H:i');}catch(Throwable){return '—';}}
function allowed_class(string $value): bool{return in_array($value,['IF-5A','IF-5B','IF-5C','IF-5D','Lainnya'],true);}
function slugify(string $value): string {
    $s=strtolower(iconv('UTF-8','ASCII//TRANSLIT//IGNORE',$value)?:$value);
    $s=trim(preg_replace('/[^a-z0-9]+/','-',$s)??'','-');
    return $s!==''?substr($s,0,150):'proyek';
}
function render(string $view, array $vars=[]): void {
    extract($vars, EXTR_SKIP);
    require dirname(__DIR__) . '/views/header.php';
    require dirname(__DIR__) . '/views/'.$view.'.php';
    require dirname(__DIR__) . '/views/footer.php';
}
function bail(string $message, int $status=400): never {
    http_response_code($status);
    render('error',['error_message'=>$message]); exit;
}
function text_lines(string $v): string {return nl2br(h($v),false);}
