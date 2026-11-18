<?php
require dirname(__DIR__).'/bootstrap.php';

if (PHP_SAPI !== 'cli') exit("CLI only\n");

/**
 * AI worker — کارهای صف editorial_jobs را برمی‌دارد:
 *  - ai_review / cards:      چیدمان + ویراستاری محتوای خام در «داسییر ساختاریافته
 *    نظریه» ۲۲ فیلدی (Resaname\Structure\Dossier) و ذخیره در dossier_json.
 *  - ai_review / سایر جداول: ویراستاری سنتی و ذخیره در current_json.
 *  - person_dossier:         ساخت «داسییر ساختاریافته شخص» ۲۱ فیلدی (۵ گروه) برای
 *    نظریه‌پردازان، دانشمندان و مؤثرین (Resaname\Structure\PersonDossier) و ذخیره
 *    در جدول editorial_person_dossiers.
 *
 * usage:
 *   php bin/worker.php            تمام کارهای pending را پردازش کن
 *   php bin/worker.php --max=10   حداکثر ۱۰ کار
 *   php bin/worker.php --status   وضعیت صف و خروج
 *   php bin/worker.php --model=qwen38   مدل خاص (پیش‌فرض: AI_MODEL از .env)
 *   php bin/worker.php --name=w0 --shard=0/3 --model=...   موازی‌سازی خردی:
 *     این worker فقط کارهایی با document_id % 3 == 0 را می‌برد (هر سند همیشه
 *     در همان شارد و همان مدل باقی می‌ماند).
 */

use Resaname\Structure\Dossier;
use Resaname\Structure\PersonDossier;
use Resaname\Structure\ContentTypes;

// امتیاز استاندارد: هر دور ai_review زیر این امتیاز، دور بعد را خودکار می‌سازد
define('SCORE_TARGET', 90);
define('MAX_AUTO_ROUNDS', 2); // حداکثر دورهای تکمیلی خودکار پس از درخواست ویراستار

$db=\Resaname\Db::editorial();

$opts=[];
foreach (array_slice($argv,1) as $a) {
    if (str_starts_with($a,'--max=')) $opts['max']=max(1,(int)substr($a,6));
    elseif (str_starts_with($a,'--model=')) $opts['model']=substr($a,8);
    elseif (str_starts_with($a,'--name=')) $opts['name']=substr($a,7);
    elseif (str_starts_with($a,'--shard=')) $opts['shard']=substr($a,8);
    elseif ($a==='--status') $opts['status']=true;
}
// خردسازی: --shard=K/N — این worker فقط کارهایی با document_id % N == K را می‌برد.
// (دورهای بعدیِ یک سند — بازتولید خودکار — همان document_id را دارند، پس همیشه
//  در همان شارد و همان مدل باقی می‌مانند و زنجیره‌ی امتیازدهی با یک مدل ثابت می‌ماند.)
$shardK=null;$shardN=null;
if (!empty($opts['shard'])) {
    if (preg_match('/^(\d+)\/(\d+)$/', $opts['shard'], $m)) { $shardK=(int)$m[1]; $shardN=(int)$m[2]; }
    elseif (ctype_digit($opts['shard'])) { $shardK=0; $shardN=(int)$opts['shard']; }
    if ($shardN===null || $shardN<2 || $shardK<0 || $shardK>=$shardN) { echo "Invalid --shard value: {$opts['shard']} (expected K/N)\n"; exit(1); }
}
$workerName=trim((string)($opts['name'] ?? 'worker'));
$workerModel=trim((string)($opts['model'] ?? ''));

// آزادسازی کارهایی که بیش از ۱۵ دقیقه در حالت running گیر کرده‌اند
$db->exec("UPDATE editorial_jobs SET status='pending',locked_at=NULL WHERE status='running' AND locked_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");

if (!empty($opts['status'])) {
    $rows=$db->query("SELECT status,COUNT(*) c FROM editorial_jobs GROUP BY status")->fetchAll();
    $parts=[];
    foreach ($rows as $r) $parts[]=$r['status'].'='.$r['c'];
    echo 'Job queue: '.(implode(' ',$parts) ?: '(empty)')."\n";
    $docs=$db->query("SELECT workflow_status,COUNT(*) c FROM editorial_documents GROUP BY workflow_status ORDER BY c DESC")->fetchAll();
    $parts2=[];
    foreach ($docs as $d) $parts2[]=$d['workflow_status'].'='.$d['c'];
    echo 'Documents: '.implode(' ',$parts2)."\n";
    $withDossier=(int)$db->query("SELECT COUNT(*) FROM editorial_documents WHERE dossier_json IS NOT NULL")->fetchColumn();
    echo 'Dossier (structured cards): '.$withDossier."\n";
    try {
        $withPerson=(int)$db->query("SELECT COUNT(*) FROM editorial_person_dossiers")->fetchColumn();
        echo 'Person dossiers (structured persons): '.$withPerson."\n";
    } catch (\Throwable) { /* جدول هنوز مهاجرت نشده */ }
    exit(0);
}

if (!\Resaname\Config::bool('AI_ENABLED')) {
    echo "AI is disabled (AI_ENABLED=false). Enable it from the settings page or .env first.\n";
    exit(0);
}

// قفل تک‌نمونه‌ای به‌ازای هر نام worker (workerهای موازی، قفل جداگانه دارند)
$storage=dirname(__DIR__).'/storage';
if (!is_dir($storage)) mkdir($storage,0700,true);
$lockFile=$storage.'/worker'.($workerName==='worker' ? '' : '-'.$workerName).'.lock';
$lockData=json_decode((string)@file_get_contents($lockFile),true);
if (is_array($lockData) && isset($lockData['started_at']) && (time()-(int)$lockData['started_at'])<300 && (int)($lockData['pid'] ?? 0)!==getmypid()) {
    echo "Another worker instance ({$workerName}) appears to be active. Exiting.\n";
    exit(0);
}
file_put_contents($lockFile,json_encode(['pid'=>getmypid(),'started_at'=>time(),'name'=>$workerName,'model'=>$workerModel]));
register_shutdown_function(function () use ($lockFile) { @unlink($lockFile); });

$provider=new \Resaname\AI\IsmcProvider($workerModel!=='' ? $workerModel : null);
$max=isset($opts['max']) ? $opts['max'] : 10000;
$done=0;
$attempted=0;

echo 'Worker ['.$workerName.'] started. model='.($workerModel!=='' ? $workerModel : '(env default)').', shard='.($shardN!==null ? $shardK.'/'.$shardN : 'all').PHP_EOL;
while ($attempted<$max) {
    $db->beginTransaction();
    if ($shardN!==null) {
        $sel=$db->prepare("SELECT id FROM editorial_jobs j WHERE j.status='pending' AND j.available_at<=NOW() AND (j.document_id % ? = ? OR j.document_id IS NULL) ORDER BY j.id LIMIT 1 FOR UPDATE");
        $sel->execute([$shardN,$shardK]);
        $st=$sel->fetch();
    } else {
        $st=$db->query("SELECT id FROM editorial_jobs WHERE status='pending' AND available_at<=NOW() ORDER BY id LIMIT 1 FOR UPDATE")->fetch();
    }
    if (!$st) {
        $db->rollBack();
        // اگر همه‌ی کارها در backoff (مثلاً به‌خاطر قطعی شبکه) هستند، خارج نشو؛ صبر کن و دوباره امتحان کن
        $backoff=(int)$db->query("SELECT COUNT(*) FROM editorial_jobs WHERE status='pending' AND available_at>NOW()")->fetchColumn();
        if ($shardN!==null) {
            $mine=$db->prepare("SELECT COUNT(*) FROM editorial_jobs WHERE status='pending' AND (document_id % ? = ? OR document_id IS NULL)");
            $mine->execute([$shardN,$shardK]);
            $mine=(int)$mine->fetchColumn();
            if ($mine===0) {
                echo 'My shard is exhausted. Exiting.'.PHP_EOL;
                break;
            }
            echo 'Waiting: my shard has '.$mine.' job(s) in retry backoff. Re-checking in 30s...'.PHP_EOL;
            sleep(30);
            $db->exec("UPDATE editorial_jobs SET status='pending',locked_at=NULL WHERE status='running' AND locked_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            continue;
        }
        if ($backoff>0) {
            echo 'Waiting: '.$backoff.' job(s) in retry backoff. Re-checking in 30s...'.PHP_EOL;
            sleep(30);
            // ایمنی: آزادسازی کارهایی که بیش از ۱۵ دقیقه در running گیر کرده‌اند
            $db->exec("UPDATE editorial_jobs SET status='pending',locked_at=NULL WHERE status='running' AND locked_at < DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
            continue;
        }
        break;
    }
    $db->prepare("UPDATE editorial_jobs SET status='running',locked_at=NOW() WHERE id=?")->execute([$st['id']]);
    $db->commit();
    $job=$db->query('SELECT * FROM editorial_jobs WHERE id='.(int)$st['id'])->fetch();
    $attempted++;
    try {
        $score=processAiJob($db,$provider,$job);
        $db->prepare("UPDATE editorial_jobs SET status='done' WHERE id=?")->execute([$job['id']]);
        $done++;
        echo 'Job #'.$job['id'].' (document '.$job['document_id'].') done.'.PHP_EOL;
        maybeAutoRetry($db,$job,$score);
    } catch (\Throwable $e) {
        $msg=mb_substr($e->getMessage(),0,2000);
        \Resaname\Support::log('ai_job_failed',['job'=>$job['id'],'doc'=>$job['document_id'],'error'=>$msg]);
        $isNetwork=(strpos($msg,'HTTP error 0')!==false)
            || (stripos($msg,'could not resolve host')!==false)
            || (stripos($msg,'timed out')!==false)
            || (stripos($msg,'connection')!==false)
            || (strpos($msg,'error 429')!==false); // rate limit: گذرا، attempt مصرف نمی‌شود
        if ($isNetwork) {
            // قطعی گذرای شبکه: attempt مصرف نمی‌شود؛ بعد از ۱۲۰ ثانیه دوباره امتحان می‌شود
            $db->prepare("UPDATE editorial_jobs SET status='pending',available_at=DATE_ADD(NOW(),INTERVAL 120 SECOND),last_error=? WHERE id=?")
                ->execute([$msg,$job['id']]);
            echo 'Job #'.$job['id'].' network blip, retry in 120s (no attempt counted): '.$msg.PHP_EOL;
        } else {
            $attempts=(int)$job['attempts']+1;
            if ($attempts<3) {
                $db->prepare("UPDATE editorial_jobs SET status='pending',attempts=?,available_at=DATE_ADD(NOW(),INTERVAL ? SECOND),last_error=? WHERE id=?")
                    ->execute([$attempts,60*$attempts,$msg,$job['id']]);
                echo 'Job #'.$job['id'].' failed, retry #'.$attempts.' in '.(60*$attempts).'s: '.$msg.PHP_EOL;
            } else {
                $db->prepare("UPDATE editorial_jobs SET status='failed',attempts=?,last_error=? WHERE id=?")->execute([$attempts,$msg,$job['id']]);
                $db->prepare("UPDATE editorial_documents SET workflow_status='error' WHERE id=?")->execute([$job['document_id']]);
                echo 'Job #'.$job['id'].' permanently failed: '.$msg.PHP_EOL;
            }
        }
    }
}
echo 'Worker finished. '.$done.' done, '.$attempted.' attempted.'.PHP_EOL;

/** پرامپت سیستمی — کارت‌های نظریه: چیدمان + ویراستاری در داسییر ساختاریافته */
function aiSystemPromptCards(): string {
    return "شما ویراستار ارشد و مسئول کنترل کیفیت «رسانامه» (resaname.ir)، دانشنامه فارسی نظریه‌های ارتباطات، مدیریت رسانه و علوم ارتباطات هستید.
وظیفه شما: محتوای خامِ یک کارتِ نظریه (JSON) را **در ساختار رسمی «داسییر ساختاریافته نظریه» (۲۲ فیلد)** قرار دهید و هم‌زمان ویرایش کنید.

## قوانین سخت (حتماً رعایت کنید)
1) فقط از محتوای داده‌شده استفاده کنید (در دورهای بازبینی: «داسییر دور قبل» مبنای اصلی است و «محتوای خام» فقط مرجع راستی‌آزمایی فکت‌هاست)؛ از دانش خودتان فکت، نام، سال، عدد یا منبع جدید **اضافه یا اختراع نکنید**.
2) اگر فیلدی از داسییر در کارت خام معادل مشخصی ندارد، آن را **خالی** بگذارید (متن: \"\"، آرایه: []، آبجکت: {}، عدد: null) و حتماً در issues با field=کلیدِ همان فیلد، type=structure، severity=warning گزارش کنید.
3) کلیدهای `slug` و `title` هرگز دست‌نخورده می‌مانند (مگر اصلاح املای آشکار).
4) املا و نگارش را اصلاح کنید: نیم‌فاصله، فاصله‌های اضافه/ناقص، اعداد و حروف لاتین که باید فارسی باشند، نشانه‌گذاری.
5) انسجام فکتی (نام‌ها، سال‌ها، اعداد، ارجاع به نظریه‌پردازان) را بررسی کنید؛ اگر با اطمینان نمی‌توانید اصلاح کنید، دست نزنید و در issues با type=factual گزارش کنید.
6) تقسیم متن‌های بلند (مثل detailed_analysis) بین فیلدهای مناسب **مجاز و مورد انتظار** است؛ محتوای موجود حذف نشود، فقط در جای درست بچیند و روان‌تر شود.
7) فیلدهای «رابطه‌ای» (آرایه/آبجکت) را دقیقاً با همان ساختار زیرفیلدی که در مشخصه آمده پر کنید.

## مشخصه ساختار خروجی (dossier) — دقیقاً ۲۲ فیلد در ۴ گروه:
". Dossier::promptSpec() ."

## راهنمای نگاشت (محتوای هر فیلد از کجا در کارت خام است):
". mappingBlock() ."

## طبقه‌بندی نوع محتوا (الزامی)
این کارت لزوماً «نظریه» نیست؛ ممکن است مدل، مفهوم، اثر، مکتب، پدیده، روش پژوهش، استراتژی و ... باشد. قبل از بررسی، **نوع واقعیِ محتوا** را از فهرست رسمیِ زیر شناسایی کن و در خروجی بده:
- \"content_category\": کلید دسته (یکی از: theoretical, intellectual, knowledge, concept, method, applied)
- \"content_type\": دقیقااً یکی از لیبل‌های زیر (کلمه‌به‌کلمه و با همین شکل انگلیسی داخل پرانتز):
". ContentTypes::promptList() ."

اگر محتوا با هیچ‌کدام جور درنیامد، هر دو فیلد را \"\" (خالی) بگذار و در issues با type=structure، severity=info گزارش کن.

## امتیازدهی
score را صادقانه بر اساس: (الف) تکمیل بودن فیلدهای ضروریِ متناسب با نوع شناسایی‌شده، (ب) صحت و انسجام محتوا، (ج) روانی و درک‌پذیری فارسی بده. اگر هر فیلد ضروریِ واقعیِ آن نوع خالی باشد، score بیش از ۷۰ نباشد.
مهم: اگر نوع محتوا مثلااً «مفهوم» یا «اثر» است، به‌خاطر خالی بودن فیلدهای اختصاصیِ نظریه (مثل theorists یا critiques) امتیاز کم نکن؛ تکمیل بودن را بر اساس آنچه برای همان نوع محتوا معنادار است بسنج.

## خروجی
فقط یک JSON معتبر (بدون متن اضافی، بدون کد بلاک) با ساختار دقیق:
{\"score\":<int 0-100>,\"summary\":\"<نظر کلی در یک جمله فارسی>\",\"issues\":[{\"field\":\"<کلید فیلد داسییر>\",\"type\":\"typo|factual|structure|reference|content|other\",\"severity\":\"info|warning|error\",\"description\":\"<شرح مشکل>\",\"suggestion\":\"<پیشنهاد اصلاح>\"}],\"dossier\":{<دقیقاً ۲۲ فیلد>}},\"content_category\":\"<کلید دسته>\",\"content_type\":\"<لیبل رسمیِ نوع>\"}}
اگر مشکلی یافت نشد، issues خالی باشد و score بالا باشد.";
}

/** پرامپت سیستمی — جداول غیر-کارت (سنتی) */
function aiSystemPromptLegacy(): string {
    return "شما ویراستار ارشد و مسئول کنترل کیفیت «رسانامه» (resaname.ir) هستید.
وظیفه شما بررسی دقیق محتوایی است که به‌صورت JSON داده می‌شود و ارائه نسخه اصلاح‌شده.

مواد بررسی:
1) املا و نگارش: غلط املایی فارسی، نیم‌فاصله‌ها، فاصله‌های اضافه یا ناقص، کاراکترهای لاتین یا اعدادی که باید فارسی/عربی باشند، نشانه‌گذاری.
2) انسجام فکتی: تناقض بین بخش‌ها (نام‌ها، سال‌ها، اعداد، عناوین)، ارجاع نادرست به نظریه‌پردازان.
3) ساختار و پیکربندی: متن گنگ، بی‌معنی یا نیمه‌ترجمه، فیلدهای مهم خالی، تکرار غیرضروری.
4) منابع: معتبر بودن و کامل بودن منابع پیشنهادی.
5) روانی و درک‌پذیری متن برای مخاطب فارسی‌زبان.

قوانین اصلاح:
- فقط اصلاحات قطعی و منطقی را اعمال کنید. هرگز فکت، رقم، نام، سال یا مفهوم را تغییر یا اختراع نکنید.
- فیلدهای «id» و «slug» هرگز دست‌نخورده می‌مانند.
- suggested_json باید دقیقااً همان فیلدهای ورودی را داشته باشد (هیچ فیلدی حذف یا اضافه نشود).

خروجی: فقط یک JSON معتبر (بدون متن اضافی، بدون کد بلاک) با ساختار دقیق:
{\"score\":<int 0-100>,\"summary\":\"<نظر کلی در یک جمله>\",\"issues\":[{\"field\":\"<نام فیلد>\",\"type\":\"typo|factual|structure|reference|content|other\",\"severity\":\"info|warning|error\",\"description\":\"<شرح مشکل>\",\"suggestion\":\"<پیشنهاد اصلاح>\"}],\"suggested_json\":{...}}
اگر مشکلی یافت نشد، issues خالی باشد و score بالا باشد.";
}

/** بلوک راهنمای نگاشت: هر فیلد داسییر ← فیلدهای خامِ کارت */
function mappingBlock(): string {
    $lines=[];
    foreach (Dossier::fields() as $f) {
        $src=$f['source'] ? implode(', ',$f['source']) : '(معادل مستقیم در منبع ندارد — در صورت نبود، خالی بگذارید)';
        $lines[]=' - '.$f['key'].'  ←  '.$src;
    }
    return implode("\n",$lines);
}

/** پرامپت سیستمی — داسییر ساختاریافته شخص (نظریه‌پرداز/دانشمند/مؤثر) */
function personDossierSystemPrompt(): string {
    return "شما ویراستار علمی «رسانامه» (resaname.ir)، دانشنامه فارسی نظریه‌های ارتباطات، مدیریت رسانه و علوم ارتباطات هستید، و مسئول ساخت «داسییر ساختاریافته شخص» برای نظریه‌پردازان، دانشمندان و مؤثرین هستید.

## قانون سخت ساختار
ساختار داسییر رسمی و ملزم است: **۲۱ فیلد در ۵ گروه** (فهرست دقیق و شماره‌گذاری‌شده زیر). در خروجی، هر ۲۱ کلید باید حضور داشته باشد؛ حذف، اضافه یا تغییر نام هیچ کلیدی ممنوع است.

## مشخصه ساختار خروجی (dossier) — ۲۱ فیلد در ۵ گروه:
". PersonDossier::promptSpec() ."

## قوانین پرکردن (حتماا‌ رعایت کنید)
1) وفاداری به واقعیت: فقط فکت‌های مستند و کاملاً شناخته‌شده را بنویسید. اگر از صحت سال، مدرک، سمت، جایزه یا اثر مطمئن نیستید، آن فیلد را **خالی** بگذارید (متن: \"\"، آرایه: []، آبجکت: {}، مقدار: null) و حتماً در issues با field=کلیدِ همان فیلد، type=content، severity=info به‌عنوان «نیازمند پژوهش عمیق» گزارش کنید. **اختراع یا حدس زدن فکت ممنوع است.**
2) هویت شخص: نام، نام اصلی و slug دقیقاً از داده‌ی خامِ شخص برداشته شود؛ `id` و `slug` هرگز دست‌نخورده می‌مانند.
3) معرفی کوتاه (introduction): ۲۰۰ تا ۲۵۰ کلمه، علمی و بدون لحن تبلیغاتی.
4) لینک‌های درون‌سایتی: **فقط** در فیلدهای «لینک‌دار» و **فقط** از فهرست «محتوای سایت» که در پیام کاربر داده می‌شود. قالب URL ثابت است:
   - کارت/نظریه: https://resaname.ir/cards/{slug}
   - نظریه‌پرداز: https://resaname.ir/theorists/{slug}
   - منبع کتابخانه: https://resaname.ir/resources/{slug}
   اگر موردِ موردنظرتان در فهرست نیست، نام آن را بنویسید و `url` را null بگذارید و در issues با type=reference گزارش کنید. ساخت URL برای موردی که در فهرست نیست ممنوع است.
5) منابع و مآخذ (references): حداقل ۵ منبع معتبر (ترکیب فارسی و انگلیسی) شامل آثار اصلی، زندگی‌نامه‌های معتبر، مقالات علمی، مصاحبه‌ها و اسناد تاریخی. DOI و لینک ناشر فقط اگر با قطعیت می‌دانید؛ **ساخت DOI ممنوع است**.
6) نگارش فارسی: نیم‌فاصله‌ی درست، اعداد فارسی، بدون کلمه‌ی لاتین در وسط متن فارسی (به‌جز نام خاص داخل پرانتز).
7) هر فیلدی را که خالی می‌گذارید، حتماً در issues گزارش کنید.

## امتیازدهی
score را بر اساس (الف) تکمیل بودن ۲۱ فیلد، (ب) صحت و اعتبار فکت‌ها، (ج) وجود لینک‌های درون‌سایتی و منابع بدهید.

## خروجی
فقط یک JSON معتبر (بدون متن اضافی، بدون کد بلاک) با ساختار دقیق:
{\"score\":<int 0-100>,\"summary\":\"<نظری کلی در یک جمله فارسی>\",\"issues\":[{\"field\":\"<کلید یکی از ۲۱ فیلد رسمی>\",\"type\":\"typo|factual|structure|reference|content|other\",\"severity\":\"info|warning|error\",\"description\":\"<شرح مشکل>\",\"suggestion\":\"<پیشنهاد اصلاح>\"}],\"dossier\":{<دقیقااً ۲۱ کلید رسمی، نه کمتر نه بیشتر>}}";
}

/** فهرست محتوای سایت (slug + نام) برای لینک‌های درون‌سایتی */
function siteIndex(): string {
    $m=\Resaname\Db::main();
    $lines=[];
    try {
        foreach ($m->query("SELECT slug,title_fa FROM cards WHERE status='published' ORDER BY id LIMIT 1000") as $r)
            $lines[]='کارت | '.$r['slug'].' | '.trim((string)$r['title_fa']);
        foreach ($m->query("SELECT slug,name FROM theorists ORDER BY id LIMIT 1000") as $r)
            $lines[]='نظریه‌پرداز | '.$r['slug'].' | '.trim((string)$r['name']);
        foreach ($m->query("SELECT slug,title FROM library_resources WHERE status='published' ORDER BY id LIMIT 1000") as $r)
            $lines[]='منبع | '.$r['slug'].' | '.trim((string)$r['title']);
    } catch (\Throwable $e) {
        \Resaname\Support::log('site_index_failed',['error'=>$e->getMessage()]);
    }
    return $lines ? implode("\n",$lines) : '(فهرست محتوای سایت خالی است — هیچ لینکی اضافه نکنید)';
}

/** پردازش یک کار person_dossier — داسییر ساختاریافته ۲۱ فیلدی شخص */
function processPersonDossierJob(\PDO $db, \Resaname\AI\IsmcProvider $p, array $job): void {
    $q=$db->prepare("SELECT * FROM editorial_documents WHERE id=?");
    $q->execute([$job['document_id']]);
    $doc=$q->fetch();
    if (!$doc) throw new \RuntimeException('Document '.$job['document_id'].' not found');
    if ($doc['source_table']!=='theorists') {
        throw new \RuntimeException('person_dossier job requires a theorist document (got: '.$doc['source_table'].')');
    }
    // داده‌ی خام: مرجع رسمی برای نام و slug؛ اگر ویرایشی روی current_json هست، آن را هم ببیند
    $raw=json_decode($doc['original_json'],true) ?: [];
    $system=personDossierSystemPrompt();
    $user='نوع سند: پروفایل شخص (نظریه‌پرداز / دانشمند / مؤثر)'
        .PHP_EOL.'داده‌ی خام شخص (منبع رسمی برای نام، slug و تصویر):'
        .PHP_EOL.json_encode($raw,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)
        .PHP_EOL.PHP_EOL.'فهرست محتوای سایت (برای لینک‌های درون‌سایتی) — فرمت: نوع | slug | نام:'
        .PHP_EOL.siteIndex();
    $res=$p->complete($system,$user);
    $out=extractJson(aiContent($res));
    if ($out===null) throw new \RuntimeException('AI response is not valid JSON');
    $score=isset($out['score']) && is_numeric($out['score']) ? max(0,min(100,(int)$out['score'])) : null;
    $summary=trim((string)($out['summary'] ?? ''));
    $issues=validateIssues($out['issues'] ?? null);
    // مشکلات جدید به‌جای مشکلات قبلی (برای اجرای تکراری)
    $db->prepare("DELETE FROM editorial_issues WHERE document_id=?")->execute([$doc['id']]);
    $insI=$db->prepare("INSERT INTO editorial_issues (document_id,job_id,field_name,issue_type,severity,description,suggestion) VALUES (?,?,?,?,?,?,?)");
    $reportedFields=[];
    foreach ($issues as $i) { $insI->execute([$doc['id'],$job['id'],$i['field'],$i['type'],$i['severity'],$i['description'],$i['suggestion']]); $reportedFields[$i['field']]=1; }
    // داسییر فقط پذیرفته می‌شود اگر هر ۲۱ کلید رسمی دست‌نخورده حضور داشته باشند (ساختار ملزم)
    $dossierRaw=is_array($out['dossier'] ?? null) ? $out['dossier'] : null;
    $dossierOk=$dossierRaw!==null && PersonDossier::exactKeys($dossierRaw);
    $complete=null;
    if ($dossierOk) {
        $dossier=PersonDossier::normalize($dossierRaw);
        $dJson=json_encode($dossier,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
        $hash=hash('sha256',$dJson);
        $complete=PersonDossier::completeness($dossier);
        $db->prepare("INSERT INTO editorial_person_dossiers
            (document_id,person_type,structure_version,dossier_json,dossier_hash,completeness,status,created_at,updated_at)
            VALUES (:doc,'theorist',:ver,:json,:hash,:complete,'ai_draft',NOW(),NOW())
            ON DUPLICATE KEY UPDATE
              structure_version=VALUES(structure_version),
              dossier_json=VALUES(dossier_json),
              dossier_hash=VALUES(dossier_hash),
              completeness=VALUES(completeness),
              status='ai_draft',
              updated_at=NOW()")
            ->execute([':doc'=>$doc['id'], ':ver'=>PersonDossier::VERSION, ':json'=>$dJson, ':hash'=>$hash, ':complete'=>$complete]);
        $db->prepare("INSERT INTO editorial_revisions (document_id,revision_type,content_json,content_hash,note) VALUES (?,?,?,?,?)")
            ->execute([$doc['id'],'ai_draft',$dJson,$hash,$summary!=='' ? 'پرونده‌ی شخص — '.$summary : 'پرونده‌ی شخص']);
        // تضمین: فیلدهای خالی که AI گزارش نکرده، خودکار گزارش شوند
        foreach (PersonDossier::emptyRequired($dossier) as $f) {
            if (!isset($reportedFields[$f['key']])) {
                $insI->execute([$doc['id'],$job['id'],$f['key'],'content','info',
                    'بخش «'.$f['label'].'» خالی است؛ نیازمند پژوهش عمیق در نوبت بعدی.','']);
            }
        }
    } else {
        $insI->execute([$doc['id'],$job['id'],'dossier','structure','error',
            'کلیدهای ۲۱ فیلدِ رسمیِ پرونده به‌طور کامل و بدون تغییر در خروجی AI نبود؛ پرونده‌ی قبلی (در صورت وجود) باقی می‌ماند.','خروجی مدل با ساختار رسمی تطبیق داده شود.']);
        \Resaname\Support::log('person_dossier_rejected',['doc'=>$doc['id'],'reason'=>'dossier missing or key set mismatch']);
    }
    \Resaname\Support::log('person_dossier_done',['doc'=>$doc['id'],'score'=>$score,'issues'=>count($issues),'dossier'=>$dossierOk,'completeness'=>$complete]);
}

/** پردازش یک کار ai_review؛ برگشت: امتیاز نهایی (برای تصمیم‌گیری حلقه‌ی خودکار) */
function processAiJob(\PDO $db, \Resaname\AI\IsmcProvider $p, array $job): ?int {
    $jobType=(string)($job['job_type'] ?? '');
    if ($jobType==='person_dossier') { processPersonDossierJob($db,$p,$job); return null; }
    if ($jobType !== 'ai_review') return null; // رزرو برای انواع شغل آینده
    $q=$db->prepare("SELECT * FROM editorial_documents WHERE id=?");
    $q->execute([$job['document_id']]);
    $doc=$q->fetch();
    if (!$doc) throw new \RuntimeException('Document '.$job['document_id'].' not found');
    $isCard=($doc['source_table']==='cards');
    // کارت‌ها: اگر نسخه‌ی بهینه‌شده‌ی دور قبل (dossier_json) وجود دارد، «مبنای اصلی» این دور آن است و
    // محتوای خام فقط مرجع راستی‌آزمایی فکت‌ها می‌شود (دورهای بعد روی خروجی بهینه‌شده‌ی دور قبل سوار می‌شوند).
    $prevDossier=null; $raw=null;
    if ($isCard) {
        $raw=json_decode($doc['original_json'],true) ?: [];
        $pd=json_decode((string)($doc['dossier_json'] ?? ''),true);
        if (is_array($pd)) $prevDossier=$pd;
        $data=$prevDossier ?? $raw;
    } else {
        $data=json_decode($doc['current_json'] ?: $doc['original_json'],true) ?: [];
    }
    $db->prepare("UPDATE editorial_documents SET workflow_status='processing' WHERE id=?")->execute([$doc['id']]);
    $system=$isCard ? aiSystemPromptCards() : aiSystemPromptLegacy();
    // توجه: عدد هدف در پرامپت نمی‌رود — مدل باید صادقانه امتیاز بدهد (آزمون نشان داد اعلام هدف، امتیازِ همان محتوا را +۴۰ می‌کند).
    // هدف فقط شرط توقف حلقه‌ی خودکار است و در feedback دورهای تکمیلی به‌صورت «بهبود» ابلاغ می‌شود.
    $scoring='امتیاز را صادقانه بر اساس معیارهای مشخصه بده (تکمیل فیلدهای ضروری، صحت و انسجام، روانی). اگر داده‌ی منبع ناکافی یا متناقض است، صادقانه در issues گزارش بده و برای بالا بردن امتیاز، محتوا اختراع نکن.'.PHP_EOL.PHP_EOL;
    if ($prevDossier) {
        $user=$scoring
            .'## داسییر دور قبل (مبنای اصلی این دور):'.PHP_EOL.PHP_EOL
            .'داسییر زیر نتیجه‌ی دور قبلی است. بخش‌های درست و کامل آن را (با ویرایش جزئی در صورت نیاز) حفظ کن، بخش‌های ضعیف و مشکلات گزارش‌شده را در چارچوب محتوای موجود تقویت کن، و داسییر کامل ۲۲ فیلدیِ بهبودیافته را بازگردان.'.PHP_EOL.PHP_EOL
            .'داسییر دور قبل:'.PHP_EOL
            .json_encode($prevDossier,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL.PHP_EOL
            .'## محتوای خامِ کارت (فقط برای راستی‌آزمایی فکت‌ها — مبنای این دور نیست):'.PHP_EOL.PHP_EOL
            .json_encode($raw,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
    } else {
        $user=$scoring.'جدول منبع: '.$doc['source_table'].PHP_EOL.'محتوای JSON:'.PHP_EOL
            .json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
    // بازبینی مجدد با نظر ویراستار: اگر برای این کار «feedback» ثبت شده، به مدل ابلاغ می‌شود
    $feedback=trim((string)($job['feedback'] ?? ''));
    if ($feedback!=='') {
        $user='## نکته‌ی مهم از ویراستار (از دور قبل) — حتماً در بازبینی این بار به آن عمل کن:'.PHP_EOL.PHP_EOL.$feedback.PHP_EOL.PHP_EOL.$user;
    }
    $res=$p->complete($system,$user);
    $out=extractJson(aiContent($res));
    if ($out===null) throw new \RuntimeException('AI response is not valid JSON');
    $score=isset($out['score']) && is_numeric($out['score']) ? max(0,min(100,(int)$out['score'])) : null;
    $summary=trim((string)($out['summary'] ?? ''));
    $issues=validateIssues($out['issues'] ?? null);

    // مشکلات جدید به‌جای مشکلات قبلی (برای اجرای تکراری)
    $db->prepare("DELETE FROM editorial_issues WHERE document_id=?")->execute([$doc['id']]);
    $insI=$db->prepare("INSERT INTO editorial_issues (document_id,job_id,field_name,issue_type,severity,description,suggestion) VALUES (?,?,?,?,?,?,?)");
    $reportedFields=[];
    foreach ($issues as $i) { $insI->execute([$doc['id'],$job['id'],$i['field'],$i['type'],$i['severity'],$i['description'],$i['suggestion']]); $reportedFields[$i['field']]=1; }

    // طبقه‌بندی نوع محتوا (فقط کارت‌ها): خروجی مدل اعتبارسنجی می‌شود؛ اگر در فهرست رسمی بود ذخیره،
    // اگر نبود، مقدار قبلی دست‌نخورده می‌ماند و به‌عنوان مشکل گزارش می‌شود.
    if ($isCard) {
        $ct=ContentTypes::validate($out['content_category'] ?? null, $out['content_type'] ?? null);
        if ($ct !== null) {
            $db->prepare("UPDATE editorial_documents SET content_category=?,content_type=? WHERE id=?")
                ->execute([$ct['category'],$ct['type'],$doc['id']]);
            \Resaname\Support::log('content_classified',['doc'=>$doc['id'],'category'=>$ct['category'],'type'=>$ct['type']]);
        } else {
            $insI->execute([$doc['id'],$job['id'],'content_type','structure','info',
                'نوع محتوای برگشتیِ AI در فهرست رسمی نبود؛ مقدار قبلی (در صورت وجود) باقی می‌ماند.','نوع محتوا دقیقاً از فهرست رسمی انتخاب شود.']);
            $issues[]=['field'=>'content_type','type'=>'structure','severity'=>'info','description'=>'(نوع غیررسمی)','suggestion'=>''];
        }
    }

    $draftOk=false;
    if ($isCard) {
        $dossier=is_array($out['dossier'] ?? null) ? $out['dossier'] : null;
        if ($dossier!==null && Dossier::exactKeys($dossier)) {
            $normalized=Dossier::normalize($dossier);
            $sJson=json_encode($normalized,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
            $hash=hash('sha256',$sJson);
            $db->prepare("UPDATE editorial_documents SET dossier_json=?,dossier_hash=?,structure_version=?,quality_score=?,workflow_status='draft_ready' WHERE id=?")
                ->execute([$sJson,$hash,Dossier::VERSION,$score,$doc['id']]);
            $db->prepare("INSERT INTO editorial_revisions (document_id,revision_type,content_json,content_hash,quality_score,note) VALUES (?,?,?,?,?,?)")
                ->execute([$doc['id'],'ai_draft',$sJson,$hash,$score,$summary!=='' ? $summary : null]);
            $draftOk=true;
            // تضمین: فیلدهای ضروریِ خالی که AI گزارش نکرده، خودکار گزارش شوند
            foreach (Dossier::emptyRequired($normalized) as $f) {
                if (!isset($reportedFields[$f['key']])) {
                    $insI->execute([$doc['id'],$job['id'],$f['key'],'structure','warning',
                        'بخش «'.$f['label'].'» در کارت خام معادل مشخصی ندارد و خالی است؛ در نوبت بعدی (جستجوی عمیق) تکمیل شود.','']);
                    $issues[]=['field'=>$f['key'],'type'=>'structure','severity'=>'warning','description'=>'(خالی)','suggestion'=>''];
                }
            }
        } else {
            $db->prepare("UPDATE editorial_documents SET quality_score=?,workflow_status='draft_ready' WHERE id=?")->execute([$score,$doc['id']]);
            \Resaname\Support::log('ai_draft_rejected',['doc'=>$doc['id'],'reason'=>'dossier missing or key set mismatch']);
        }
    } else {
        // جداول غیر-کارت: پیش‌نویس ویرایش‌شده در current_json
        $suggested=is_array($out['suggested_json'] ?? null) ? $out['suggested_json'] : null;
        if ($suggested!==null && count($suggested)>0 && sameKeys($data,$suggested)) {
            $sJson=json_encode($suggested,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
            $hash=hash('sha256',$sJson);
            $db->prepare("UPDATE editorial_documents SET current_json=?,current_hash=?,quality_score=?,workflow_status='draft_ready' WHERE id=?")
                ->execute([$sJson,$hash,$score,$doc['id']]);
            $db->prepare("INSERT INTO editorial_revisions (document_id,revision_type,content_json,content_hash,quality_score,note) VALUES (?,?,?,?,?,?)")
                ->execute([$doc['id'],'ai_draft',$sJson,$hash,$score,$summary!=='' ? $summary : null]);
            $draftOk=true;
        } else {
            $db->prepare("UPDATE editorial_documents SET quality_score=?,workflow_status='draft_ready' WHERE id=?")->execute([$score,$doc['id']]);
            \Resaname\Support::log('ai_draft_rejected',['doc'=>$doc['id'],'reason'=>'suggested_json missing or key set mismatch']);
        }
    }
    \Resaname\Support::log('ai_review_done',['doc'=>$doc['id'],'score'=>$score,'issues'=>count($issues),'draft'=>$draftOk,'structure'=>$isCard?Dossier::VERSION:null]);
    return $score;
}

/**
 * حلقه‌ی خودکار رسیدن به امتیاز استاندارد:
 * اگر امتیاز یک دور ai_review کمتر از SCORE_TARGET (۹۰) بود، worker خودکار دور بعدی
 * را با «feedback» هدفمند (امتیاز قبل + فهرست نقاط ضعف) به صف برمی‌گرداند.
 *  - سقف: MAX_AUTO_ROUNDS دور تکمیلی (جلوگیری از حلقه‌ی بی‌پایان)
 *  - تشخیص رکود: اگر امتیاز نسبت به دور قبل بهبود نیافته، ادامه دادن بی‌فایده است و توقف می‌کنیم
 */
function maybeAutoRetry(\PDO $db, array $job, ?int $score): void {
    if ((string)($job['job_type'] ?? '') !== 'ai_review') return;
    if ($score === null || $score >= SCORE_TARGET) return;
    $round=(int)($job['auto_retry'] ?? 0);
    if ($round >= MAX_AUTO_ROUNDS) {
        \Resaname\Support::log('auto_rounds_exhausted',['doc'=>$job['document_id'],'score'=>$score,'rounds'=>$round]);
        echo 'Doc '.$job['document_id'].': score '.$score.' — max auto rounds ('.MAX_AUTO_ROUNDS.') reached; needs editor attention.'.PHP_EOL;
        return;
    }
    // تشخیص رکود: امتیازِ دورِ قبل (نسخه‌ی امتیازدارِ قبل از این نسخه)
    $pq=$db->prepare("SELECT quality_score FROM editorial_revisions WHERE document_id=? AND quality_score IS NOT NULL ORDER BY id DESC LIMIT 1 OFFSET 1");
    $pq->execute([$job['document_id']]);
    $prevScore=$pq->fetchColumn();
    if ($prevScore!==false && $prevScore!==null && (int)$prevScore >= $score) {
        \Resaname\Support::log('auto_retry_stalled',['doc'=>$job['document_id'],'score'=>$score,'prev'=>(int)$prevScore]);
        echo 'Doc '.$job['document_id'].': score stalled at '.$score.' (prev '.(int)$prevScore.') — auto-retry stopped.'.PHP_EOL;
        return;
    }
    $next=$round+1;
    // فهرست ۵ نکته‌ی اصلیِ دور قبل برای تمرکز دور بعد
    $iq=$db->prepare("SELECT field_name,severity,description FROM editorial_issues WHERE document_id=? ORDER BY FIELD(severity,'error','warning','info'),id LIMIT 5");
    $iq->execute([$job['document_id']]);
    $focus=[];
    foreach ($iq->fetchAll() as $i) {
        $focus[]='- ['.$i['severity'].'] '.($i['field_name']!=='' ? $i['field_name'].': ':'').$i['description'];
    }
    $fb='دور خودکار '.$next.': امتیاز صادقانه‌ی دور قبل '.$score.'/۱۰۰ بود. '.
        'لطفاً در چارچوب محتوای موجود (فقط چیدمان و ویراستاری) نقاط ضعف زیر را تا حد ممکن اصلاح و داسییر را بهبود بده؛ سپس امتیاز را صادقانه بر اساس معیارها بزن. '.
        'نقاط ضعف اصلیِ دور قبل:'.PHP_EOL.(implode(PHP_EOL,$focus) ?: '(مشکل مشخصی گزارش نشده بود)').PHP_EOL.PHP_EOL.
        'مهم: اگر خودِ داده‌ی منبع ناکافی یا متناقض است، برای بالا بردن امتیاز محتوا اختراع نکن؛ آن را به‌عنوان مشکل «factual» گزارش کن تا امتیاز همان عدد صادقانه بماند.';
    $db->prepare("INSERT INTO editorial_jobs (document_id,job_type,status,available_at,updated_at,feedback,auto_retry) VALUES (?,'ai_review','pending',NOW(),NOW(),?,?)")
        ->execute([$job['document_id'],$fb,$next]);
    $db->prepare("UPDATE editorial_documents SET workflow_status='queued',updated_at=NOW() WHERE id=?")->execute([$job['document_id']]);
    \Resaname\Support::log('auto_retry_scheduled',['doc'=>$job['document_id'],'score'=>$score,'next_round'=>$next]);
    echo 'Doc '.$job['document_id'].': score '.$score.' < '.SCORE_TARGET.' → auto round '.$next.' scheduled.'.PHP_EOL;
}

/** متن پاسخ مدل از قالب‌های رایج chat-completions */
function aiContent($res): string {
    if (is_string($res)) return $res;
    if (is_array($res)) {
        $c=$res['choices'][0]['message']['content'] ?? null;
        if ($c===null) $c=$res['message']['content'] ?? null;
        if ($c===null) $c=$res['content'] ?? null;
        if (is_string($c)) return $c;
    }
    return '';
}

/** استخراج JSON از پاسخ (با حذف کد بلاک احتمالی) */
function extractJson(string $text): ?array {
    $t=trim($text);
    if (preg_match('/```[a-zA-Z0-9]*\s*(\{[\s\S]*\})\s*```/',$t,$m)) $t=trim($m[1]);
    $s=strpos($t,'{'); $e=strrpos($t,'}');
    if ($s===false || $e===false || $e<=$s) return null;
    $d=json_decode(substr($t,$s,$e-$s+1),true);
    return is_array($d) ? $d : null;
}

/** اعتبارسنجی و محدودسازی فهرست مشکلات */
function validateIssues($raw): array {
    if (!is_array($raw)) return [];
    $types=['typo','factual','structure','reference','content','other'];
    $sevs=['info','warning','error'];
    $out=[];
    foreach (array_slice($raw,0,60) as $i) {
        if (!is_array($i)) continue;
        $d=trim((string)($i['description'] ?? ''));
        if ($d==='') continue;
        $t=(string)($i['type'] ?? 'other');
        $s=(string)($i['severity'] ?? 'warning');
        $out[]=[
            'field'=>mb_substr(trim((string)($i['field'] ?? '')),0,120),
            'type'=>in_array($t,$types,true) ? $t : 'other',
            'severity'=>in_array($s,$sevs,true) ? $s : 'warning',
            'description'=>mb_substr($d,0,2000),
            'suggestion'=>mb_substr(trim((string)($i['suggestion'] ?? '')),0,2000),
        ];
    }
    return $out;
}

/** آیا دو آرایه دقیقااً همان کلیدها را دارند؟ */
function sameKeys(array $a, array $b): bool {
    $ka=array_keys($a); $kb=array_keys($b);
    sort($ka); sort($kb);
    return $ka===$kb;
}
