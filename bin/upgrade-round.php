<?php
require dirname(__DIR__).'/bootstrap.php';

if (PHP_SAPI !== 'cli') exit("CLI only\n");

/**
 * «دور ارتقاء»ِ دقیق برای کارت‌های نزدیک به استاندارد (86–89).
 *
 * اصول (تصمیمِ ۱۵/۱۱):
 *  - هیچ هدفِ عددی (مثل ۹۰) به مدل داده نمی‌شود؛ مدل فقط مشکلاتِ باقیمانده را
 *    تا حدِ قابل‌حل با محتوای موجود برطرف می‌کند.
 *  - امتیازِ نهایی کاملاً به‌ساحتِ داور بازمی‌گردد؛ اگر ۹۰ نداد، همان ثبت
 *    می‌شود و هیچ‌چیز دستکاری نمی‌شود.
 *  - فقط یک دور؛ بعد از آن کارت (چه ۹۰ شده چه نشده) دیگر پردازش خودکار ندارد.
 *
 * usage:
 *   php bin/upgrade-round.php [id[,id,...]]   فقط سندهای ذکرشده
 *   php bin/upgrade-round.php                 همه‌ی draft_ready در بازه‌ی 86–89
 *   php bin/upgrade-round.php --dry           فقط فهرست اهداف
 */

use Resaname\AI\IsmcProvider;
use Resaname\Support;

$db=\Resaname\Db::editorial();

$flags=array(); $ids=array();
foreach (array_slice($argv,1) as $a) {
    if ($a==='-' || strpos($a,'-')===0) $flags[]=$a;
    else {
        foreach (explode(',',$a) as $p) { $p=trim($p); if ($p!=='') $ids[]=(int)$p; }
    }
}
$dry = (in_array('--dry',$flags) || in_array('-dry',$flags));

$in='';
if (count($ids)) { $in=implode(',',$ids); $where='d.id IN ('.$in.')'; }
else { $where='d.quality_score BETWEEN 86 AND 89'; }

$docs=$db->query('SELECT d.id, d.title, d.quality_score s, d.dossier_json, d.original_json
                  FROM editorial_documents d
                  WHERE d.workflow_status=\'draft_ready\' AND '.$where.'
                  ORDER BY d.id')->fetchAll();
if (!count($docs)) { echo "No target documents.\n"; exit(0); }

if ($dry) {
    echo 'DRY RUN — '.count($docs)." target(s):\n";
    foreach ($docs as $d) echo '  #'.$d['id'].' ('.$d['s'].') '.$d['title']."\n";
    exit(0);
}

function upgradeSystem() {
    return 'تو ویراستارِ ارشدِ دانشنامه‌ی رسانه و داورِ امتیازدهیِ آن هستی. این سند قبلاً چند دور بازبینی گذشته و به امتیاز نزدیکی به استاندارد رسیده، اما با یک یا چند مشکلِ باقیمانده مانده است. مأموریتِ این دورِ نهایی: 1) فهرستِ مشکلاتِ باقیمانده را ببین و هرکدام را تا جایی که با محتوای موجودِ کارت (داسییر فعلی و داده‌ی خام) صادقانه قابل حل است، برطرف کن: اگر فیلدی خالی مانده و داده‌ی خام واقعاً چیزی برای آن دارد که در داسییر نشسته، آن را پر کن؛ اگر ارجاع ناقص، آلیاس ناقص یا تناقض کوچکی هست که داده‌ی خام پاسخش را می‌دهد، اصلاح کن؛ اگر مشکل از جنس «داده در منبع نیست» است، آن فیلد را دست نزن و همان وضعیت را حفظ کن. 2) هرگز چیزی را اختراع، تخمین یا ترویج نکن. هر جمله‌ای که می‌نویسی باید در داسییر فعلی یا داده‌ی خام ریشه داشته باشد. 3) ساختار ۲۲ فیلدی را دقیقاً حفظ کن (همان JSON ورودی). 4) در پایان، امتیازِ صادقانه‌ات را به‌عنوان score بده — فقط بر اساس کیفیتِ واقعیِ محتوای نهایی؛ نه بر اساس هیچ انتظاری از قبل. 5) مشکلاتِ باقیمانده‌ی حل‌نشده را (اگر هست) در issues گزارش کن تا ویراستار بداند. خروجی: JSON با همان ساختارِ داسییر + کلید score (عدد 0 تا 100) + کلید summary (یک خط) + کلید issues (مجموعه‌ای، می‌تواند خالی باشد)';
}

/**
 * درجه‌ی «تغییر واقعی محتوایی» بین دو داسییر (0 تا 1) — فقط ۲۰ فیلدِ
 * محتواییِ داسییر می‌شود؛ فیلد summary (که گاهی به متنِ خودارزیابیِ مدل
 * بدل می‌شود) در حسابِ تغییرِ محتوایی نمی‌آید.
 */
function contentChangeRatio($oldD, $newD) {
    $fields=array('title_fa','title_en','aliases','slug','theorists','year','history','field',
        'main_problem','assumptions','key_concepts','principles','model','mechanism',
        'applications','evolution','critiques','related','sources','research_guide');
    $changed=0; $total=count($fields);
    foreach($fields as $f){
        $oj=json_encode($oldD[$f] ?? null, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        $nj=json_encode($newD[$f] ?? null, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
        if($oj!==$nj) $changed++;
    }
    return $changed/$total;
}

function upgradeUser($doc, $issues, $raw) {
    $dossier=$doc['dossier_json'];
    $isList='';
    if (count($issues)) {
        $isList="\n\nمشکلاتِ باقیمانده از آخرین دور (خروجیِ داور):\n";
        foreach ($issues as $i) {
            $isList .= '- ['.$i['issue_type'].'/'.$i['severity'].'] ';
            $isList .= ($i['field_name'] ? $i['field_name'].' : ' : '(کلی) ');
            $isList .= mb_substr((string)$i['description'],0,200);
            if (!empty($i['suggestion'])) $isList .= ' ← پیشنهاد: '.mb_substr((string)$i['suggestion'],0,120);
            $isList .= "\n";
        }
    }
    return 'داسییرِ فعلیِ سند:'.PHP_EOL.$dossier
        ."\n\nداده‌ی خامِ کارت (مرجعِ حقیقت):".PHP_EOL.$raw
        .$isList;
}

function aiContent($res) {
    if (is_string($res)) return $res;
    if (is_array($res)) {
        $c=$res['choices'][0]['message']['content'] ?? null;
        if ($c===null) $c=$res['message']['content'] ?? null;
        if ($c===null) $c=$res['content'] ?? null;
        if (is_string($c)) return $c;
    }
    return '';
}

function extractJson($raw) {
    $raw=trim($raw);
    if ($raw==='') return null;
    $raw=preg_replace('/^```(?:json)?\s*/i','',$raw);
    $raw=preg_replace('/\s*```$/','',$raw);
    $raw=trim($raw);
    $j=json_decode($raw,true);
    if (is_array($j)) return $j;
    $s=strpos($raw,'{'); $e=strrpos($raw,'}');
    if ($s!==false && $e!==false && $e>$s) {
        $j=json_decode(substr($raw,$s,$e-$s+1),true);
        if (is_array($j)) return $j;
    }
    return null;
}

function validateIssues($v) {
    if (!is_array($v)) return array();
    $out=array();
    foreach ($v as $i) {
        if (!is_array($i)) continue;
        $desc=trim((string)($i['description'] ?? $i['desc'] ?? $i['issue'] ?? ''));
        if ($desc==='') continue;
        $sev=$i['severity'] ?? 'warning';
        if (!in_array($sev,array('info','warning','error'))) $sev='warning';
        $type=is_string($i['type'] ?? null) ? mb_substr($i['type'],0,40) : 'other';
        $fld=is_string($i['field'] ?? null) ? mb_substr($i['field'],0,120) : null;
        $sug=trim((string)($i['suggestion'] ?? $i['fix'] ?? ''));
        $out[]=array('issue_type'=>$type,'severity'=>$sev,'field_name'=>$fld,'description'=>$desc,'suggestion'=>$sug);
    }
    return $out;
}

$p=new IsmcProvider();
$uid=$db->query("SELECT id FROM editorial_users WHERE username='editor'")->fetchColumn();
$insR=$db->prepare("INSERT INTO editorial_revisions
    (document_id,revision_type,content_json,content_hash,created_by,quality_score,note)
    VALUES (?,?,?,?,?,?,?)");
$insI=$db->prepare("INSERT INTO editorial_issues (document_id,job_id,field_name,issue_type,severity,description,suggestion)
    VALUES (?,?,?,?,?,?,?)");

foreach ($docs as $doc) {
    $id=$doc['id'];
    $issues=$db->query('SELECT issue_type,severity,field_name,description,suggestion
        FROM editorial_issues WHERE document_id='.$id.'
        ORDER BY FIELD(severity,\'error\',\'warning\',\'info\'), id')->fetchAll();
    $raw=trim((string)$doc['original_json']);
    if ($raw==='' || $raw==='null') $raw='(داده‌ی خام موجود نیست — فقط داسییرِ فعلی مرجع است)';

    $user=upgradeUser($doc,$issues,$raw);

    try {
        $res=$p->complete(upgradeSystem(),$user);
    } catch (\Throwable $e) {
        Support::log('upgrade_round_failed',array('doc'=>$id,'error'=>$e->getMessage()));
        echo "DOC #".$id.': FAILED — '.$e->getMessage()."\n";
        continue;
    }
    $text=aiContent($res);
    if ($text==='') {
        Support::log('upgrade_round_failed',array('doc'=>$id,'error'=>'empty AI content'));
        echo "DOC #".$id.': FAILED — empty AI content'."\n";
        continue;
    }
    $out=extractJson($text);
    if ($out===null) {
        Support::log('upgrade_round_failed',array('doc'=>$id,'error'=>'AI response is not valid JSON'));
        echo "DOC #".$id.': FAILED — not valid JSON'."\n";
        continue;
    }
    // شاید کل داسییر داخل یک کلیدِ تودرتو برگشته
    if (!isset($out['title_fa']) && !isset($out['summary'])) {
        foreach ($out as $k=>$v) {
            if (is_array($v) && isset($v['title_fa'])) { $out=$v; break; }
        }
    }
    if (!isset($out['title_fa']) && !isset($out['summary'])) {
        Support::log('upgrade_round_failed',array('doc'=>$id,'error'=>'response missing dossier fields'));
        echo "DOC #".$id.': FAILED — response missing dossier fields'."\n";
        continue;
    }
    $score=is_numeric($out['score'] ?? null) ? max(0,min(100,(int)$out['score'])) : null;
    if ($score===null) {
        Support::log('upgrade_round_failed',array('doc'=>$id,'error'=>'no numeric score in response'));
        echo "DOC #".$id.': FAILED — no numeric score'."\n";
        continue;
    }
    $dossierJson=json_encode($out,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);

    // ---- gate: امتیازِ 90+ فقط در صورتِ تغییرِ واقعیِ محتوا پذیرفته می‌شود ----
    // (آزمونِ #325 نشان داد مدل با محتوایِ ۱۰۰٪ یکسان، ۸۸ را به ۹۲ تبدیل کرد؛
    //  یعنی خودارزیابیِ داور روی این کارت‌ها ناپایدار است و +4 واقعی نبود)
    $oldD=json_decode($doc['dossier_json'],true);
    $change=is_array($oldD) ? contentChangeRatio($oldD,$out) : 1.0;
    if ($score>=90 && $change < 0.10) {
        Support::log('upgrade_round_rejected',array('doc'=>$id,'score'=>$score,'change'=>round($change,3),'reason'=>'score>=90 without real content change'));
        echo "DOC #".$id.': REJECTED — score '.$score.' with only '.round($change*100,1).'% real content change (unstable judge; not accepted)'."\n";
        continue;
    }

    $db->prepare('DELETE FROM editorial_issues WHERE document_id=?')->execute(array($id));
    $newIssues=validateIssues($out['issues'] ?? null);
    foreach ($newIssues as $i) {
        $insI->execute(array($id,null,$i['field_name'],$i['issue_type'],$i['severity'],$i['description'],$i['suggestion'] ?: null));
    }
    $insR->execute(array($id,'ai_draft',$dossierJson,hash('sha256',$dossierJson),$uid,$score,'دور ارتقاء (upgrade round)'));

    $db->prepare('UPDATE editorial_documents SET quality_score=?, dossier_json=?, current_json=? WHERE id=?')
        ->execute(array($score,$dossierJson,$dossierJson,$id));

    Support::log('upgrade_round_done',array('doc'=>$id,'score'=>$score,'prev'=>(int)$doc['s'],'issues'=>count($newIssues)));
    $flag=$score>=90 ? ' ✅ REACHED 90+' : '';
    echo 'DOC #'.$id.': '.$doc['s'].' → '.$score.' ('.$doc['title'].') issues='.count($newIssues).' change='.round($change*100,1).'%'.$flag."\n";
}

$rows=$db->query("SELECT id,title,quality_score s FROM editorial_documents
                  WHERE workflow_status='draft_ready' AND quality_score>=90
                    AND NOT EXISTS (SELECT 1 FROM editorial_jobs j WHERE j.document_id=editorial_documents.id AND j.status IN ('pending','running'))
                  ORDER BY id")->fetchAll();
echo "\n=== آماده‌ی تأیید (90+) ===\n";
foreach ($rows as $r) echo '  #'.$r['id'].' ('.$r['s'].') '.$r['title']."\n";
echo 'TOTAL '.count($rows)." approvable\n";
echo "Done.\n";
