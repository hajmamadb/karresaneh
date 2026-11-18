<?php
require dirname(__DIR__).'/bootstrap.php';
\Resaname\Auth::requireLogin();
$me=\Resaname\Auth::user();
if (($me['role'] ?? '') !== 'admin') { http_response_code(403); exit('دسترسی فقط برای مدیر'); }
$db=\Resaname\Db::editorial();
$envFile=dirname(__DIR__).'/.env';
function h($v){return \Resaname\Support::e((string)$v);}

function aiDefaults(): array {
    return ['AI_ENABLED'=>'false','AI_BASE_URL'=>'https://ai.ismc.ir/api','AI_CHAT_PATH'=>'','AI_API_KEY'=>'','AI_MODEL'=>'qwen38','AI_TIMEOUT'=>'90'];
}
function readAiEnv(): array {
    $m=aiDefaults();
    foreach ($m as $k=>$d) $m[$k]=\Resaname\Config::get($k,$d);
    return $m;
}
function writeAiEnv(string $file, array $v): void {
    $lines=@(file($file,FILE_IGNORE_NEW_LINES)) ?: [];
    $keys=array_keys($v);
    $out=[]; $seen=[];
    foreach ($lines as $line) {
        $t=trim($line);
        if ($t!=='' && $t[0]!=='#' && str_contains($t,'=')) {
            $k=trim(explode('=',$t,2)[0]);
            if (in_array($k,$keys,true)) { $out[]=$k.'='.$v[$k]; $seen[$k]=1; continue; }
        }
        $out[]=$line;
    }
    foreach ($keys as $k) if (!isset($seen[$k])) $out[]=$k.'='.$v[$k];
    file_put_contents($file,implode("\n",$out)."\n",LOCK_EX);
}
function validateAi(array $v, bool $strict): array {
    $e=[];
    if (!preg_match('~^https?://~i',$v['AI_BASE_URL'])) $e[]='آدرس سرویس باید با http:// یا https:// شروع شود.';
    if ($strict) {
        if ($v['AI_CHAT_PATH']==='') $e[]='مسیر API (AI_CHAT_PATH) وارد نشده است.';
        if ($v['AI_API_KEY']==='') $e[]='کلید API هوش مصنوعی وارد نشده است.';
    }
    if (preg_match('/[\r\n]/',$v['AI_API_KEY'])) $e[]='کلید API نباید شامل خط‌پر باشد.';
    if (!preg_match('/^\d{1,120}$/',$v['AI_TIMEOUT'])) $e[]='تایم‌اوت باید عدد باشد (ثانیه).';
    return $e;
}
/** افزودن کارهای داده‌شده به صف و راه‌اندازی worker */
function enqueueAndStartJobs(\PDO $db, string $sql): array {
    $stIns=$db->prepare($sql);
    $stIns->execute();
    $ins=(int)$stIns->rowCount();
    $pending=(int)$db->query("SELECT COUNT(*) FROM editorial_jobs WHERE status IN ('pending','running')")->fetchColumn();
    if ($pending===0) return ['ok','محتوای جدیدی برای افزودن به صف نبود (همه محتواها در صف هستند یا پردازش شده‌اند).'];
    $w=\Resaname\Support::startWorker(dirname(__DIR__));
    $added=$ins>0 ? ((int)$ins).' محتوا به صف اضافه شد. ' : '';
    if (!$w['started'] && $ins===0) return ['err',$added.'فرآیند worker اجرا نشد.'];
    return ['ok',$added.$w['message'].' '.$pending.' کار در صف است.'];
}

$flash=null;
if ($_SERVER['REQUEST_METHOD']==='POST') {
    \Resaname\Support::verifyCsrf();
    $action=(string)($_POST['action'] ?? '');
    $cur=readAiEnv();
    $v=[
        'AI_ENABLED'=>((($_POST['ai_enabled'] ?? '')==='true')?'true':'false'),
        'AI_BASE_URL'=>trim((string)($_POST['ai_base_url'] ?? '')),
        'AI_CHAT_PATH'=>trim((string)($_POST['ai_chat_path'] ?? '')),
        'AI_API_KEY'=>trim((string)($_POST['ai_api_key'] ?? '')),
        'AI_MODEL'=>trim((string)($_POST['ai_model'] ?? '')),
        'AI_TIMEOUT'=>trim((string)($_POST['ai_timeout'] ?? '')),
    ];
    if ($v['AI_API_KEY']==='') $v['AI_API_KEY']=$cur['AI_API_KEY']; // خالی = حفظ کلید فعلی
    if ($v['AI_MODEL']==='') $v['AI_MODEL']='qwen38';
    try {
        if ($action==='save') {
            $err=validateAi($v,false);
            if ($err) $flash=['err',implode(' — ',$err)];
            else { writeAiEnv($envFile,$v); $flash=['ok','تنظیمات در فایل .env ذخیره شد.']; }
        } elseif ($action==='test' || $action==='start') {
            $err=validateAi($v,true);
            if ($err) $flash=['err',implode(' — ',$err)];
            else {
                $v['AI_ENABLED']='true';
                writeAiEnv($envFile,$v);
                \Resaname\Config::load($envFile);
                if ($action==='test') {
                    $p=new \Resaname\AI\IsmcProvider();
                    $res=$p->complete('تو یک دستیار فنی هستی. به پیام کاربر فقط با یک جمله کوتاه فارسی پاسخ بده.','سلام، اتصال درست است؟');
                    $c='';
                    if (is_array($res)) $c=$res['choices'][0]['message']['content'] ?? ($res['content'] ?? json_encode($res,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
                    $c=is_string($c)&&trim($c)!==''?trim($c):'(پاسخ خالی)';
                    $flash=['ok','اتصال موفق بود ✅ — پاسخ مدل: '.mb_substr($c,0,300)];
                } elseif ($action==='start' || $action==='start_persons') {
                    // start:         بررسی/اصلاح همه‌ی محتواها (ai_review)
                    // start_persons: ساخت داسییر ساختاریافته ۲۱ فیلدی نظریه‌پردازان/دانشمندان (person_dossier)
                    $sql=$action==='start_persons'
                        ? "INSERT INTO editorial_jobs (document_id,job_type,status,available_at,updated_at)
                            SELECT d.id,'person_dossier','pending',NOW(),NOW() FROM editorial_documents d
                            WHERE d.source_table='theorists'
                              AND NOT EXISTS (SELECT 1 FROM editorial_jobs j WHERE j.document_id=d.id AND j.job_type='person_dossier' AND j.status IN ('pending','running'))"
                        : "INSERT INTO editorial_jobs (document_id,job_type,status,available_at,updated_at)
                            SELECT d.id,'ai_review','pending',NOW(),NOW() FROM editorial_documents d
                            WHERE d.workflow_status IN ('imported','error')
                              AND NOT EXISTS (SELECT 1 FROM editorial_jobs j WHERE j.document_id=d.id AND j.job_type='ai_review' AND j.status IN ('pending','running'))";
                    $flash=enqueueAndStartJobs($db,$sql);
                }
            }
        } elseif ($action==='restructure') {
            // restructure: بازساخت همه‌ی کارت‌های نظریه در ساختار داسییر ۲۲ فیلدی (ai_review)
            $err=validateAi($v,true);
            if ($err) $flash=['err',implode(' — ',$err)];
            else {
                $v['AI_ENABLED']='true';
                writeAiEnv($envFile,$v);
                \Resaname\Config::load($envFile);
                $sql="INSERT INTO editorial_jobs (document_id,job_type,status,available_at,updated_at)
                    SELECT d.id,'ai_review','pending',NOW(),NOW() FROM editorial_documents d
                    WHERE d.source_table='cards'
                      AND NOT EXISTS (SELECT 1 FROM editorial_jobs j WHERE j.document_id=d.id AND j.job_type='ai_review' AND j.status IN ('pending','running'))";
                $db->prepare("UPDATE editorial_documents SET workflow_status='queued' WHERE source_table='cards' AND workflow_status IN ('imported','draft_ready','error')")->execute();
                $flash=enqueueAndStartJobs($db,$sql);
                if ($flash[0]==='ok') $flash[1].=' (بازساخت کارت‌ها در ساختار پرونده ۲۲ فیلدی)';
            }
        } else {
            $flash=['err','عملیات ناشناخته.'];
        }
    } catch (\Throwable $e) {
        \Resaname\Support::log('settings_action_error',['action'=>$action,'error'=>$e->getMessage()]);
        $flash=['err','خطا: '.mb_substr($e->getMessage(),0,300)];
    }
    header('Location: /settings.php'); exit;
}

$ai=readAiEnv();
$maskKey=$ai['AI_API_KEY']===''?'':(strlen($ai['AI_API_KEY'])>10?substr($ai['AI_API_KEY'],0,4).'••••'.substr($ai['AI_API_KEY'],-4):'••••');
$jobs=$db->query("SELECT status,COUNT(*) c FROM editorial_jobs GROUP BY status")->fetchAll();
$lastJobs=$db->query("SELECT j.id,j.status,j.job_type,j.attempts,j.last_error,d.title,d.workflow_status,j.updated_at FROM editorial_jobs j JOIN editorial_documents d ON d.id=j.document_id ORDER BY j.updated_at DESC LIMIT 8")->fetchAll();
$docStats=$db->query("SELECT workflow_status,COUNT(*) c FROM editorial_documents GROUP BY workflow_status ORDER BY c DESC")->fetchAll();
try { $personDossiers=(int)$db->query("SELECT COUNT(*) FROM editorial_person_dossiers")->fetchColumn(); } catch (\Throwable) { $personDossiers=null; }
try { $cardDossiers=(int)$db->query("SELECT COUNT(*) FROM editorial_documents WHERE source_table='cards' AND dossier_json IS NOT NULL")->fetchColumn(); } catch (\Throwable) { $cardDossiers=null; }
$jobL=['pending'=>'در صف','running'=>'در حال اجرا','done'=>'انجام‌شده','failed'=>'ناموفق'];
$wfL=['imported'=>'واردشده','queued'=>'در صف','processing'=>'در حال پردازش','draft_ready'=>'پیش‌نویس آماده بررسی','review'=>'در حال بررسی','approved'=>'تأییدشده','rejected'=>'ردشده','published'=>'انتشاریافته','error'=>'خطا'];
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>تنظیمات — میز سردبیری</title><link rel="stylesheet" href="/style.css"></head>
<body>
<header><a href="/">← بازگشت به داشبورد</a><strong>تنظیمات — رسانامه</strong></header>
<main>
<h1>تنظیمات هوش مصنوعی</h1>
<?php if($flash): ?><div class="flash <?= $flash[0]==='ok' ? 'ok' : 'err' ?>"><?= h($flash[1]) ?></div><?php endif; ?>
<form method="post" class="field">
<input type="hidden" name="_csrf" value="<?= \Resaname\Support::csrf() ?>">
<label>فعال‌سازی هوش مصنوعی (AI_ENABLED)</label>
<select name="ai_enabled">
  <option value="false" <?= $ai['AI_ENABLED']==='true' ? '' : 'selected' ?>>خاموش</option>
  <option value="true"  <?= $ai['AI_ENABLED']==='true' ? 'selected' : '' ?>>روشن</option>
</select>
<label>آدرس سرویس (AI_BASE_URL)</label>
<input type="text" name="ai_base_url" value="<?= h($ai['AI_BASE_URL']) ?>">
<label>مسیر endpoint گفتگو (AI_CHAT_PATH)</label>
<input type="text" name="ai_chat_path" value="<?= h($ai['AI_CHAT_PATH']) ?>" placeholder="مثال: v1/chat/completions">
<label>کلید API (AI_API_KEY)</label>
<input type="password" name="ai_api_key" value="" placeholder="<?= h($maskKey!=='' ? $maskKey.' (برای حفظ کلید فعلی خالی بگذارید)' : 'هنوز وارد نشده') ?>">
<label>نام مدل (AI_MODEL)</label>
<input type="text" name="ai_model" value="<?= h($ai['AI_MODEL']) ?>">
<label>تایم‌اوت (ثانیه) (AI_TIMEOUT)</label>
<input type="number" name="ai_timeout" value="<?= h($ai['AI_TIMEOUT']) ?>">
<div class="btns">
<button type="submit" name="action" value="save" class="btn">💾 ذخیره تنظیمات</button>
<button type="submit" name="action" value="test" class="btn">🧪 ذخیره و تست اتصال</button>
<button type="submit" name="action" value="start" class="btn go">🚀 فعال‌سازی هوش مصنوعی و شروع کار</button>
<button type="submit" name="action" value="start_persons" class="btn go">👤 ساخت پرونده‌ی نظریه‌پردازان و دانشمندان</button>
<button type="submit" name="action" value="restructure" class="btn go">🏗️ بازساخت همه‌ی کارت‌ها در ساختار پرونده ۲۲ فیلدی</button>
</div>
<p class="hint">با دکمه‌ی «🚀 فعال‌سازی و شروع کار» مقادیر ذخیره می‌شود، AI_ENABLED روشن می‌شود، همه‌ی محتواهای واردشده/داری-از-خطا به صف بررسی می‌روند و فرآیند worker به‌صورت خودکار راه‌اندازی می‌شود. worker هر محتوا را بررسی و امتیاز می‌دهد، مشکلات را ثبت می‌کند و در صورت نیاز پیش‌نویس اصلاح‌شده می‌سازد که در صفحه‌ی بررسی همان محتوا قابل دیدن است.</p>
<p class="hint">با دکمه‌ی «👤 ساخت پرونده‌ی نظریه‌پردازان و دانشمندان» برای همه‌ی سندهای جدول theorists، کار ساخت «پرونده‌ی ساختاریافته شخص» (۲۱ فیلد در ۵ گروه: شناسنامه، زندگی‌نامه و مسیر علمی، اندیشه‌ها و دستاوردها، نقد و ارزیابی، ارتباطات و منابع) به صف اضافه می‌شود. هوش مصنوعی موظف است هر ۲۱ بخش را رعایت کند؛ لینک‌های درون‌سایتی فقط از محتوای موجود سایت ساخته می‌شوند. نتیجه در صفحه‌ی بررسی هر نظریه‌پرداز قابل دیدن است.</p>
<p class="hint">با دکمه‌ی «🏗️ بازساخت کارت‌ها» همه‌ی کارت‌های نظریه (حتی اگر قبلاً بررسی شده باشند) دوباره در ساختار رسمی «پرونده‌ی ساختاریافته نظریه» (۲۲ فیلد در ۴ گروه: شناسنامه، بنیان‌های نظری، تبیین و ارزیابی، منابع) چیده و ویرایش می‌شوند. هوش مصنوعی فقط محتوای موجود را جابه‌جا و اصلاح می‌کند و چیزی اختراع نمی‌کند؛ بخش‌های بدون معادل خالی می‌مانند و گزارش می‌شوند (تکمیل آن‌ها در نوبت بعدی با جستجوی عمیق انجام می‌شود).</p>
</form>

<h2>وضعیت صف کارها</h2>
<section class="stats">
<?php foreach ($jobL as $k=>$lab): $c=0; foreach($jobs as $j) if($j['status']===$k) $c=(int)$j['c']; ?><div><b><?= $c ?></b><span><?= $lab ?></span></div><?php endforeach; ?>
</section>

<h2>وضعیت محتواها</h2>
<section class="stats">
<?php foreach($docStats as $d): ?><div><b><?= (int)$d['c'] ?></b><span><?= h($wfL[$d['workflow_status']] ?? $d['workflow_status']) ?></span></div><?php endforeach; ?>
<?php if($personDossiers!==null): ?><div><b><?= (int)$personDossiers ?></b><span>پرونده‌ی شخص ساخته‌شده</span></div><?php endif; ?>
<?php if($cardDossiers!==null): ?><div><b><?= (int)$cardDossiers ?></b><span>پرونده‌ی نظریه (کارت) ساخته‌شده</span></div><?php endif; ?>
</section>

<?php if($lastJobs): ?><h2>آخرین کارهای صف</h2><table><thead><tr><th>محتوا</th><th>نوع کار</th><th>وضعیت کار</th><th>وضعیت محتوا</th><th>تلاش</th><th>آخرین خطا</th></tr></thead><tbody>
<?php foreach($lastJobs as $j): ?><tr><td><?= h($j['title']) ?></td><td><?= h($j['job_type']) ?></td><td><?= h($jobL[$j['status']] ?? $j['status']) ?></td><td><?= h($wfL[$j['workflow_status']] ?? $j['workflow_status']) ?></td><td><?= (int)$j['attempts'] ?></td><td><?= h(mb_substr((string)$j['last_error'],0,120)) ?></td></tr><?php endforeach; ?>
</tbody></table><?php endif; ?>
</main></body></html>
