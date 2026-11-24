<?php
/** CLI only: php tests/selftest.php */
declare(strict_types=1);
if (PHP_SAPI !== 'cli') {http_response_code(403); exit('Forbidden');}
require dirname(__DIR__).'/app/helpers.php';
require dirname(__DIR__).'/app/projects.php';
$test=function (bool $cond,string $desc):void { if(!$cond){fwrite(STDERR,"FAIL: $desc\n");exit(1);}echo "PASS: $desc\n";};
$test(h('<script>')==='&lt;script&gt;','HTML escape');
$test(valid_http_url('https://example.org/demo'),'HTTPS URL accepted');
$test(!valid_http_url('javascript:alert(1)'),'JavaScript URL rejected');
$test(!valid_http_url('https://name:pwd@example.org'),'Embedded URL credentials rejected');
$test(slugify('Smart Lab 2027!')==='smart-lab-2027','Slug generation');
$_POST=['member_name'=>['Athoillah','Rina'],'member_nim'=>['230600001','230600002'],'member_contribution'=>['UI/UX','Web']];
$members=parse_member_input();
$test(count($members)===2,'Two team members parsed');
$_POST=['member_name'=>['Alice'],'member_nim'=>[''],'member_contribution'=>['UX']];
$threw=false;try{parse_member_input();}catch(RuntimeException){$threw=true;}
$test($threw,'Incomplete member rejected');
echo "CORE SELF-TEST PASSED\n";
