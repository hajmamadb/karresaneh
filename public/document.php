<?php
require dirname(__DIR__).'/bootstrap.php';
\Resaname\Auth::requireLogin();
use Resaname\Structure\Dossier;
use Resaname\Structure\PersonDossier;
use Resaname\Structure\ContentTypes;
$db=\Resaname\Db::editorial();
$id=(int)($_GET['id']??0);
$q=$db->prepare("SELECT * FROM editorial_documents WHERE id=?"); $q->execute([$id]); $doc=$q->fetch();
if(!$doc){http_response_code(404);exit('محتوا پیدا نشد');}

// ---- POST: تصمیمات ویراستاری (تأیید / رد / بازبینی مجدد AI / ویرایش دستی) ----
if ($_SERVER['REQUEST_METHOD']==='POST') {
    \Resaname\Support::verifyCsrf();
    $me=\Resaname\Auth::user();
    $action=(string)($_POST['action'] ?? '');
    $note=trim((string)($_POST['note'] ?? ''));
    $flash=null;
    try {
        if ($action==='queue_person_dossier' && $doc['source_table']==='theorists') {
            $chk=$db->prepare("SELECT COUNT(*) FROM editorial_jobs WHERE document_id=? AND job_type='person_dossier' AND status IN ('pending','running')");
            $chk->execute([$id]);
            if ((int)$chk->fetchColumn()===0) {
                $ins=$db->prepare("INSERT INTO editorial_jobs (document_id,job_type,status,available_at,updated_at) VALUES (?,?,?,NOW(),NOW())");
                $ins->execute([$id,'person_dossier','pending']);
            }
            $flash=['ok','پرونده‌ی شخص به صف بازسازی اضافه شد.'];
        }
        elseif ($action==='approve') {
            $db->prepare("INSERT INTO editorial_reviews (document_id,editor_id,decision,note) VALUES (?,?,?,?)")->execute([$id,(int)$me['id'],'approved',$note!==''?$note:null]);
            $content=(string)($doc['dossier_json'] ?? $doc['current_json']);
            $db->prepare("INSERT INTO editorial_revisions (document_id,revision_type,content_json,content_hash,quality_score,created_by,note) VALUES (?,?,?,?,?,?,?)")
                ->execute([$id,'approved',$content,hash('sha256',$content),$doc['quality_score']!==null?(int)$doc['quality_score']:null,(int)$me['id'],$note!==''?$note:null]);
            $db->prepare("UPDATE editorial_documents SET workflow_status='approved',updated_at=NOW() WHERE id=?")->execute([$id]);
            $flash=['ok','محتوا تأیید شد ✅ — وضعیت: «تأییدشده». (نسخه‌ی تأییدشده در تاریخچه‌ی نسخه‌ها ثبت شد)'];
        }
        elseif ($action==='reject') {
            $db->prepare("INSERT INTO editorial_reviews (document_id,editor_id,decision,note) VALUES (?,?,?,?)")->execute([$id,(int)$me['id'],'rejected',$note!==''?$note:null]);
            $db->prepare("UPDATE editorial_documents SET workflow_status='rejected',updated_at=NOW() WHERE id=?")->execute([$id]);
            $flash=['err','محتوا رد شد ❌ — حالا می‌توانید «بازبینی مجدد AI» با نظر خودتان درخواست کنید یا خودتان ویرایش کنید.'];
        }
        elseif ($action==='requeue') {
            $ins=$db->prepare("INSERT INTO editorial_jobs (document_id,job_type,status,available_at,updated_at,feedback) VALUES (?,'ai_review','pending',NOW(),NOW(),?)");
            $ins->execute([$id,$note!==''?$note:null]);
            $db->prepare("UPDATE editorial_documents SET workflow_status='queued',updated_at=NOW() WHERE id=?")->execute([$id]);
            $w=\Resaname\Support::startWorker(dirname(__DIR__));
            $flash=['ok','درخواست بازبینی مجدد توسط هوش مصنوعی به صف اضافه شد. AI بررسی را تا رسیدن به امتیاز ۹۰ (با حداکثر ۲ دور تکمیلی خودکار) ادامه می‌دهد. '.$w['message']];
        }
        elseif ($action==='save_dossier') {
            if ($doc['source_table']!=='cards') {
                $flash=['err','ویرایش ساختاریافته فقط برای کارت‌های نظریه در دسترس است.'];
            } else {
                $normalized=Dossier::normalize(dossierFromPost($_POST));
                $sJson=json_encode($normalized,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                $hash=hash('sha256',$sJson);
                $db->prepare("UPDATE editorial_documents SET dossier_json=?,dossier_hash=?,updated_at=NOW() WHERE id=?")->execute([$sJson,$hash,$id]);
                $db->prepare("INSERT INTO editorial_revisions (document_id,revision_type,content_json,content_hash,created_by,note) VALUES (?,?,?,?,?,?)")
                    ->execute([$id,'editor_edit',$sJson,$hash,(int)$me['id'],'ویرایش دستی پرونده‌ی نظریه توسط ویراستار']);
                $db->prepare("UPDATE editorial_documents SET workflow_status=IF(workflow_status IN ('rejected','error'),'draft_ready',workflow_status),updated_at=NOW() WHERE id=?")->execute([$id]);
                $flash=['ok','ویرایش شما در پرونده‌ی ساختاریافته ذخیره شد ✅ (نسخه در تاریخچه ثبت شد)'];
            }
        }
        elseif ($action==='save_legacy') {
            $raw=trim((string)($_POST['json'] ?? ''));
            $data=json_decode($raw,true);
            if (!is_array($data)) {
                $flash=['err','JSON نامعتبر است؛ لطفاً فرمت را اصلاح و دوباره ذخیره کنید.'];
            } else {
                $ka=array_keys($original); $kb=array_keys($data); sort($ka); sort($kb);
                if ($ka!==$kb) {
                    $flash=['err','فیلد اضافه یا کم نشود: می‌توانید فقط مقدار فیلدهای موجود را تغییر دهید.'];
                } else {
                    $sJson=json_encode($data,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                    $hash=hash('sha256',$sJson);
                    $db->prepare("UPDATE editorial_documents SET current_json=?,current_hash=?,updated_at=NOW() WHERE id=?")->execute([$sJson,$hash,$id]);
                    $db->prepare("INSERT INTO editorial_revisions (document_id,revision_type,content_json,content_hash,created_by,note) VALUES (?,?,?,?,?,?)")
                        ->execute([$id,'editor_edit',$sJson,$hash,(int)$me['id'],'ویرایش دستی نسخه جاری توسط ویراستار']);
                    $db->prepare("UPDATE editorial_documents SET workflow_status=IF(workflow_status IN ('rejected','error'),'draft_ready',workflow_status),updated_at=NOW() WHERE id=?")->execute([$id]);
                    $flash=['ok','ویرایش شما در نسخه جاری ذخیره شد ✅'];
                }
            }
        }
        else {
            $flash=['err','عملیات ناشناخته.'];
        }
    } catch (\Throwable $e) {
        \Resaname\Support::log('document_action_error',['id'=>$id,'action'=>$action,'error'=>$e->getMessage()]);
        $flash=['err','خطا: '.mb_substr($e->getMessage(),0,300)];
    }
    if ($flash) $_SESSION['flash']=$flash;
    header('Location: /document.php?id='.$id); exit;
}
$flash=($_SESSION['flash'] ?? null); unset($_SESSION['flash']);

$original=json_decode($doc['original_json'],true) ?: [];
$current=json_decode($doc['current_json'],true) ?: [];
$dossier=($doc['dossier_json'] ?? null) ? (json_decode($doc['dossier_json'],true) ?: null) : null;
$isCard=($doc['source_table']==='cards') && $dossier!==null;

// ---- داسییر ساختاریافته شخص (نظریه‌پرداز/دانشمند/مؤثر) ----
$personDossier=null; $personMeta=null;
if ($doc['source_table']==='theorists') {
    try {
        $pq=$db->prepare("SELECT * FROM editorial_person_dossiers WHERE document_id=?"); $pq->execute([$id]);
        if ($row=$pq->fetch()) { $personDossier=json_decode($row['dossier_json'],true) ?: null; $personMeta=$row; }
    } catch (\Throwable) { $personDossier=null; } // جدول هنوز مهاجرت نشده
}
$pdStatusL=['ai_draft'=>'پیش‌نویس AI','review'=>'در حال بررسی','approved'=>'تأییدشده','published'=>'انتشاریافته'];
function h($v){return \Resaname\Support::e((string)$v);}

// ---- helpers برای کادر نمایشی (نمای مخاطب، مطابق سایت اصلی) ----
function pv_str($v){ return trim((string)($v ?? '')); }
function pv_p($s){ // متن چندخطی → پاراگراف‌بندی
  $out='';
  foreach (preg_split('/\r?\n+/u',(string)$s) as $line){
    $line=trim($line);
    if($line!=='') $out.='<p>'.h($line).'</p>';
  }
  return $out;
}
function pv_lead($s,$n=170){ // پیش‌نویس خلاصه‌شده با «…» مثل سایت اصلی
  $s=pv_str($s);
  if($s==='') return '';
  $t=mb_substr($s,0,$n);
  return h($t).(mb_strlen($s)>$n?'…':'');
}
function pv_section($title,$body,$para=false){
  $b=pv_str($body);
  if($b==='') return '';
  return '<h4>'.h($title).'</h4>'.($para?pv_p($b):'<p>'.h($b).'</p>');
}
function pv_img($url,$alt=''){
  $url=pv_str($url);
  if($url==='') return '';
  if($url[0]==='/' && strncmp($url,'//',2)!==0) $url='https://resaname.ir'.$url;
  return '<img src="'.h($url).'" alt="'.h($alt).'" loading="lazy" referrerpolicy="no-referrer">';
}
function pv_live($url){
  $url=pv_str($url);
  if($url==='') return '';
  return '<p class="pv-live">نمای مخاطب از سایت اصلی: <a href="'.h($url).'" target="_blank" rel="noopener">'.h($url).'</a></p>';
}

// ---- helpers برای داسییر ساختاریافته ----
function ds_flat($val): string {
    if (is_array($val)) {
        $parts=[];
        foreach ($val as $v) $parts[] = is_array($v) ? json_encode($v,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) : (string)$v;
        return implode('، ', $parts);
    }
    return (string)$val;
}
function ds_render(string $key, $v): string {
    $f=Dossier::byKey($key);
    if (Dossier::isEmpty($v)) {
        return '<p class="ds-empty">'.($f && $f['required'] ? 'خالی — در نوبت بعدی (جستجوی عمیق) تکمیل می‌شود' : '—').'</p>';
    }
    $type=$f['type'] ?? 'text';
    $sub=$f['subkeys'] ?? [];
    if ($type==='text') return pv_p($v);
    if ($type==='number') return '<p><b>'.h((string)$v).'</b></p>';
    if ($type==='array') {
        $items='';
        foreach ((array)$v as $it) {
            if (is_array($it)) {
                $cells='';
                foreach ($it as $k=>$val) {
                    if (Dossier::isEmpty($val)) continue;
                    $cells.='<span class="ds-kv"><b>'.h($sub[$k] ?? $k).':</b> '.h(ds_flat($val)).'</span>';
                }
                $items.='<li>'.$cells.'</li>';
            } else {
                $items.='<li>'.h((string)$it).'</li>';
            }
        }
        return '<ul class="ds-list">'.$items.'</ul>';
    }
    if ($type==='json' && is_array($v)) {
        $cells='';
        foreach ($v as $k=>$val) {
            if (Dossier::isEmpty($val)) continue;
            $cells.='<span class="ds-kv"><b>'.h($sub[$k] ?? $k).':</b> '.h(ds_flat($val)).'</span>';
        }
        return $cells!=='' ? '<div class="ds-json">'.$cells.'</div>' : '<p class="ds-empty">—</p>';
    }
    return '<p>'.h((string)$v).'</p>';
}
// ---- helpers برای داسییر ساختاریافته شخص ----
function pd_list_items(array $items, array $sub): string {
    $out='';
    foreach ($items as $it) {
        if (!is_array($it)) { $out.='<li>'.h((string)$it).'</li>'; continue; }
        $cells='';
        foreach ($it as $k=>$val) {
            if (PersonDossier::isEmpty($val)) continue;
            if ($k==='url' && is_string($val) && trim($val)!=='') {
                $u=trim($val);
                $cells.='<span class="ds-kv"><b>'.h($sub['url'] ?? 'لینک').':</b> <a href="'.h($u).'" target="_blank" rel="noopener">'.h($u).'</a></span>';
            } else {
                $cells.='<span class="ds-kv"><b>'.h($sub[$k] ?? $k).':</b> '.h(ds_flat($val)).'</span>';
            }
        }
        if ($cells!=='') $out.='<li>'.$cells.'</li>';
    }
    return $out;
}
function pd_render(string $key, $v): string {
    $f=PersonDossier::byKey($key);
    if (PersonDossier::isEmpty($v)) {
        return '<p class="ds-empty">'.($f && $f['required'] ? 'خالی — نیازمند پژوهش عمیق در نوبت بعدی' : '—').'</p>';
    }
    $type=$f['type'] ?? 'text';
    $sub=$f['subkeys'] ?? [];
    if ($type==='text') return pv_p($v);
    if ($type==='number') return '<p><b>'.h((string)$v).'</b></p>';
    // فیلدهای «متن + لینک» (روش، میراث، نقدها)
    if ($type==='json' && is_array($v) && array_key_exists('text',$v) && array_key_exists('links',$v)) {
        $out='';
        if (trim((string)($v['text'] ?? ''))!=='') $out.=pv_p($v['text']);
        if (!PersonDossier::isEmpty($v['links'])) $out.='<ul class="ds-list">'.pd_list_items((array)$v['links'], ['label'=>'مورد مرتبط','url'=>'لینک درون‌سایتی']).'</ul>';
        return $out;
    }
    if ($type==='array') {
        return '<ul class="ds-list">'.pd_list_items((array)$v, $sub).'</ul>';
    }
    if ($type==='json' && is_array($v)) {
        $cells='';
        foreach ($v as $k=>$val) {
            if (PersonDossier::isEmpty($val)) continue;
            if (is_array($val)) {
                $cells.='<span class="ds-kv"><b>'.h($sub[$k] ?? $k).':</b> <ul class="ds-list ds-nested">'.pd_list_items($val, []).'</ul></span>';
            } elseif (is_bool($val)) {
                $cells.='<span class="ds-kv"><b>'.h($sub[$k] ?? $k).':</b> '.h($val ? 'بله' : 'خیر').'</span>';
            } else {
                $cells.='<span class="ds-kv"><b>'.h($sub[$k] ?? $k).':</b> '.h((string)$val).'</span>';
            }
        }
        return $cells!=='' ? '<div class="ds-json">'.$cells.'</div>' : '<p class="ds-empty">—</p>';
    }
    return '<p>'.h((string)$v).'</p>';
}
function issueFieldLabel(string $name): string {
    $f=Dossier::byKey($name);
    if (!$f) { $pf=PersonDossier::byKey($name); if ($pf) return h($pf['label']).' <code class="ds-key">'.$name.'</code>'; }
    return $f ? h($f['label']).' <code class="ds-key">'.$name.'</code>' : h($name);
}

/** خواندن فرم ویرایش داسییر (نام‌ها: d[کلید] / d[کلید][نمتر][زیرکلید]) و بازسازی آرایه‌ی ۲۲ فیلدی */
function dossierFromPost(array $post): array {
    $d=Dossier::blank();
    $in=(array)($post['d'] ?? []);
    foreach (Dossier::fields() as $f) {
        $k=$f['key']; $raw=$in[$k] ?? null; $type=$f['type']; $sub=$f['subkeys'] ?? [];
        if ($type==='text' || $type==='number') {
            $s=trim((string)$raw);
            if ($type==='number' && $s!=='' && preg_match('/^-?\d{1,4}(-\d{1,4})?$/', $s)) {
                $d[$k]=preg_match('/^-?\d{1,4}$/', $s) ? (int)$s : $s;
            } else {
                $d[$k]=$s;
            }
        }
        elseif ($type==='array') {
            $items=[];
            if (is_array($raw)) {
                foreach ($raw as $it) {
                    if ($sub) {
                        $o=[]; $any=false;
                        foreach ($sub as $sk=>$lbl) {
                            $sv=trim((string)(is_array($it) ? ($it[$sk] ?? '') : ''));
                            if ($sv!=='') $any=true;
                            $o[$sk]=$sv;
                        }
                        if ($any) $items[]=$o;
                    } else {
                        $s=trim((string)$it);
                        if ($s!=='') $items[]=$s;
                    }
                }
            }
            $d[$k]=$items;
        }
        elseif ($type==='json') {
            $raw=is_array($raw) ? $raw : [];
            $o=[]; $any=false;
            foreach ($sub as $sk=>$lbl) {
                $sv=trim((string)($raw[$sk] ?? ''));
                if ($k==='model' && $sk==='elements') {
                    $els=array_values(array_filter(array_map('trim',preg_split('/\r?\n/',$sv)),fn($x)=>$x!==''));
                    $o[$sk]=$els;
                    if ($els) $any=true;
                } else {
                    $o[$sk]=$sv;
                    if ($sv!=='') $any=true;
                }
            }
            $d[$k]=$any ? $o : null;
        }
    }
    return $d;
}

/** کنترل ورودی یک فیلد در فرم ویرایش */
function ef_control(string $key, $value): string {
    $f=Dossier::byKey($key);
    $type=$f['type'] ?? 'text';
    $sub=$f['subkeys'] ?? [];
    if ($type==='text') {
        $rows=mb_strlen((string)$value)>300 ? 8 : (mb_strlen((string)$value)>100 ? 5 : 3);
        return '<textarea name="d['.$key.']" rows="'.$rows.'">'.h((string)$value).'</textarea>';
    }
    if ($type==='number') {
        return '<input type="text" name="d['.$key.']" value="'.h((string)$value).'" style="max-width:240px">';
    }
    if ($type==='json') {
        $cells='';
        foreach ($sub as $sk=>$lbl) {
            $cv=$value[$sk] ?? '';
            if ($key==='model' && $sk==='elements') {
                $lines=is_array($cv) ? implode("\n", array_map(fn($e)=>is_array($e)?(string)json_encode($e,JSON_UNESCAPED_UNICODE):(string)$e, $cv)) : (string)$cv;
                $cells.='<label class="ef-lbl block">'.h($lbl).' (هر عنصر در یک خط) <textarea name="d['.$key.']['.$sk.']" rows="3" dir="ltr" style="text-align:right">'.h($lines).'</textarea></label>';
            } else {
                $cvStr=is_array($cv) ? (string)json_encode($cv,JSON_UNESCAPED_UNICODE) : (string)$cv;
                $cells.='<label class="ef-lbl">'.h($lbl).' <input type="text" name="d['.$key.']['.$sk.']" value="'.h($cvStr).'"></label>';
            }
        }
        return $cells;
    }
    // type=array
    $items=(array)$value;
    $rows='';
    $n=0;
    foreach ($items as $it) {
        if ($sub) {
            $cells='';
            foreach ($sub as $sk=>$lbl) {
                $cv=is_array($it) ? (string)($it[$sk] ?? '') : '';
                if ($sk==='definition') {
                    $cells.='<label class="ef-lbl block">'.h($lbl).' <textarea name="d['.$key.']['.$n.']['.$sk.']" rows="2">'.h($cv).'</textarea></label>';
                } else {
                    $cells.='<label class="ef-lbl">'.h($lbl).' <input type="text" name="d['.$key.']['.$n.']['.$sk.']" value="'.h($cv).'"></label>';
                }
            }
        } else {
            $cells='<label class="ef-lbl"><input type="text" name="d['.$key.']['.$n.']" value="'.h((string)$it).'"></label>';
        }
        $rows.='<div class="ef-item">'.$cells.'<button type="button" class="btn-sm danger" onclick="efRemove(this)">✕ حذف</button></div>';
        $n++;
    }
    // الگوی مورد جدید (کلیک «افزودن»)
    if ($sub) {
        $tplCells='';
        foreach ($sub as $sk=>$lbl) $tplCells.='<label class="ef-lbl">'.h($lbl).' <input type="text" name="d['.$key.'][__I__]['.$sk.']" value=""></label>';
    } else {
        $tplCells='<label class="ef-lbl"><input type="text" name="d['.$key.'][__I__]" value=""></label>';
    }
    $rows.='<template data-ef-tpl="'.$key.'">'.$tplCells.'</template>';
    $rows.='<button type="button" class="btn-sm" data-ef-add="'.$key.'">＋ افزودن مورد</button>';
    return '<div class="ef-items" data-ef-wrap="'.$key.'" data-n="'.$n.'">'.$rows.'</div>';
}

// ---- نمایشی — نمای مخاطب (بر اساس نسخه جاری/اولیه خام) ----
$pv = (!empty($current) && is_array($current)) ? $current : $original;
$table=$doc['source_table'];
$pv_html='';
if($table==='cards'){
  $title=pv_str($pv['title_fa'] ?? '') ?: pv_str($pv['title_en'] ?? '') ?: pv_str($doc['title']);
  $intro=pv_str($pv['core_logic'] ?? '');
  $pv_html.='<h3 class="pv-title">'.h($title).'</h3>';
  if($intro!=='') $pv_html.='<p class="pv-lead">'.h($title).'؛ '.pv_lead($intro).'</p>';
  $pv_html.=pv_img($pv['image_url'] ?? '',$title);
  if($intro!=='') $pv_html.='<p class="pv-intro">'.h($intro).'</p>';
  $pv_html.=pv_section('مفاهیم اصلی',$pv['constructs'] ?? '');
  $pv_html.=pv_section('مثال',$pv['example'] ?? '');
  $pv_html.=pv_section('نقد و بررسی',$pv['critique'] ?? '');
  $pv_html.=pv_section('تحلیل تفصیلی',$pv['detailed_analysis'] ?? '',true);
  $pv_html.=pv_section('منابع پیشنهادی',$pv['recommended_sources'] ?? '');
  $pv_html.=pv_live('https://resaname.ir/cards/'.rawurlencode($doc['source_id']));
}
elseif($table==='library_resources'){
  $title=pv_str($pv['title'] ?? '') ?: pv_str($doc['title']);
  $summary=pv_str($pv['summary'] ?? '');
  $pv_html.='<h3 class="pv-title">'.h($title).'</h3>';
  if($summary!=='') $pv_html.='<p class="pv-lead">'.pv_lead($summary).'</p>';
  $pv_html.=pv_img($pv['image_url'] ?? '',$title);
  if($summary!=='') $pv_html.='<p>'.h($summary).'</p>';
  $pv_html.=pv_section('پدیدآورنده',$pv['creator'] ?? '');
  $pv_html.=pv_section('ناشر / نشریه',$pv['publisher'] ?? '');
  $year=pv_str($pv['publication_year'] ?? '') ?: pv_str($pv['publication_date'] ?? '');
  $pv_html.=pv_section('سال انتشار',$year);
  $pv_html.=pv_section('زبان',$pv['language'] ?? '');
  $pv_html.=pv_section('معرفی و توضیحات',$pv['body'] ?? '',true);
  $links='';
  if(pv_str($pv['source_url'] ?? '')!=='') $links.='<a href="'.h($pv['source_url']).'" target="_blank" rel="noopener">منبع</a>';
  if(pv_str($pv['file_url'] ?? '')!=='') $links.='<a href="'.h($pv['file_url']).'" target="_blank" rel="noopener">فایل</a>';
  if(pv_str($pv['video_url'] ?? '')!=='') $links.='<a href="'.h($pv['video_url']).'" target="_blank" rel="noopener">ویدیو</a>';
  if(pv_str($pv['embed_url'] ?? '')!=='') $links.='<a href="'.h($pv['embed_url']).'" target="_blank" rel="noopener">جای‌گذاری</a>';
  if(pv_str($pv['doi'] ?? '')!=='') $links.='<span class="pv-meta">DOI: '.h($pv['doi']).'</span>';
  if(pv_str($pv['isbn'] ?? '')!=='') $links.='<span class="pv-meta">ISBN: '.h($pv['isbn']).'</span>';
  if(is_numeric($pv['duration_seconds'] ?? null) && ((int)$pv['duration_seconds']>0)) $links.='<span class="pv-meta">مدت: '.gmdate('i:s',(int)$pv['duration_seconds']).'</span>';
  if($links!=='') $pv_html.='<p class="pv-links">'.$links.'</p>';
  $slug=pv_str($pv['slug'] ?? '');
  $pv_html.=pv_live('https://resaname.ir/resources/'.rawurlencode($slug !== '' ? $slug : $doc['source_id']));
}
elseif($table==='theorists'){
  $name=pv_str($pv['name'] ?? '') ?: pv_str($doc['title']);
  $bio=pv_str($pv['bio'] ?? '');
  $pv_html.='<h3 class="pv-title">'.h($name).'</h3>';
  if($bio!=='') $pv_html.='<p class="pv-lead">'.h($name).'؛ '.pv_lead($bio).'</p>';
  $pv_html.=pv_img($pv['avatar_url'] ?? '',$name);
  if($bio!=='') $pv_html.=pv_p($bio);
  $pv_html.=pv_live('https://resaname.ir/theorists/'.rawurlencode($doc['source_id']));
}
else{
  $pv_html.='<p class="pv-empty">برای این جدول قالب نمایشی تعریف نشده است.</p>';
}

// ---- محاسبه‌ی تغییرات: کارت خام → داسییر ساختاریافته ----
function normWs($s){ return trim(preg_replace('/\s+/u',' ',(string)$s)); }
$diff=[];
if ($isCard) {
    foreach (Dossier::fields() as $f) {
        $k=$f['key'];
        $new=$dossier[$k] ?? null;
        $srcField=$f['source'] ? $f['source'][0] : null;
        $old=$srcField ? ($original[$srcField] ?? null) : null;
        $oldText=ds_flat($old);
        $newText=ds_flat($new);
        if (Dossier::isEmpty($new)) $status='empty';
        elseif (Dossier::isEmpty($old)) $status='new';
        elseif (normWs($oldText)===normWs($newText)) $status='same';
        else $status='changed';
        $diff[$k]=['label'=>$f['label'],'key'=>$k,'status'=>$status,'old'=>$oldText,'new'=>$newText,'src'=>$srcField];
    }
}
$statusL=['changed'=>'ویرایش/چیدمان','new'=>'ساختاردهی‌شده','empty'=>'خالی','same'=>'بدون تغییر'];
$diffShown=array_values(array_filter($diff,fn($d)=>$d['status']!=='same'));
$wfL=['imported'=>'واردشده','queued'=>'در صف','processing'=>'در حال پردازش','draft_ready'=>'پیش‌نویس آماده بررسی','review'=>'در حال بررسی','approved'=>'تأییدشده','rejected'=>'ردشده','published'=>'انتشاریافته','error'=>'خطا'];
$editing=(int)($_GET['edit'] ?? 0)===1;
$jchk=$db->prepare("SELECT COUNT(*) FROM editorial_jobs WHERE document_id=? AND job_type='ai_review' AND status IN ('pending','running')");
$jchk->execute([$id]);
$inQueue=(int)$jchk->fetchColumn()>0;

// ---- تاریخچه‌ی امتیاز: همه‌ی نسخه‌های امتیازدار (دورهای AI + تأییدها)، ترتیب زمانی ----
$sq=$db->prepare("SELECT revision_type, quality_score, note, created_at FROM editorial_revisions WHERE document_id=? AND quality_score IS NOT NULL ORDER BY id ASC");
$sq->execute([$id]);
$scoreRows=[]; $prevScore=null;
foreach ($sq->fetchAll() as $r) {
    $s=(int)$r['quality_score'];
    $scoreRows[]=['type'=>$r['revision_type'],'score'=>$s,'delta'=>$prevScore===null?null:$s-$prevScore,'note'=>(string)$r['note'],'at'=>$r['created_at']];
    $prevScore=$s;
}
$scoreRowsRev=array_reverse($scoreRows);
$prevScoreForLine=(count($scoreRows)>=2) ? $scoreRows[count($scoreRows)-2]['score'] : null;
$deltaForLine=(count($scoreRows)>=2) ? $scoreRows[count($scoreRows)-1]['score']-$scoreRows[count($scoreRows)-2]['score'] : null;
$scoreCls=function($s){ return $s>=80?'high':($s>=50?'mid':'low'); };
$revL=['ai_draft'=>'بازبینی/اصلاح AI','editor_edit'=>'ویرایش ویراستار','approved'=>'تأیید ویراستار','original'=>'نسخه‌ی اولیه'];

// ---- پیام تأیید نهایی (امتیاز جاری + دور قبل) و آخرین نسخه‌ی تأییدشده ----
$approveMsg='تأیید این محتوا به‌عنوان نسخه‌ی نهایی؟\n\nامتیاز جاری: '.((int)($doc['quality_score'] ?? 0)).'/۱۰۰';
if ($prevScoreForLine!==null) {
    $approveMsg.=' (دور قبل: '.((int)$prevScoreForLine).' · تغییر: '.($deltaForLine>0?'+':'').$deltaForLine.')';
}
$approveMsg.="\n\nنسخه‌ی تأییدشده با امتیاز جاری در تاریخچه به‌عنوان «تأیید ویراستار» ثبت می‌شود و محتوا با این امتیاز به‌عنوان محتوای تاییدشده‌ی نهایی باقی می‌ماند.";
$approval=null;
if ($doc['workflow_status']==='approved') {
    $aq=$db->prepare("SELECT r.quality_score,r.created_at,r.id,r.note,u.display_name FROM editorial_revisions r LEFT JOIN editorial_users u ON u.id=r.created_by WHERE r.document_id=? AND r.revision_type='approved' ORDER BY r.id DESC LIMIT 1");
    $aq->execute([$id]);
    $approval=$aq->fetch();
}
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=h($doc['title'])?></title><link rel="stylesheet" href="/style.css"></head><body><header><a href="/">← بازگشت به فهرست</a><strong>بررسی محتوا</strong></header><main>
<h1><?=h($doc['title'])?></h1>
<p>جدول: <?=h($doc['source_table'])?> | شناسه: <?=h($doc['source_id'])?> | وضعیت: <b class="wf-<?=h($doc['workflow_status'])?>"><?=h($wfL[$doc['workflow_status']] ?? $doc['workflow_status'])?></b><?php if ($doc['content_type']!==null && $doc['content_type']!==''): ?> | نوع محتوا: <b><?= h($doc['content_type']) ?></b> <small class="muted">(<?= h(ContentTypes::categoryLabel($doc['content_category'])) ?>)</small><?php endif; ?><?= $doc['quality_score']!==null ? ' | امتیاز AI: <b class="score score-'.($doc['quality_score']>=80?'high':($doc['quality_score']>=50?'mid':'low')).'">'.((int)$doc['quality_score']).'</b>'
   .($prevScoreForLine!==null ? ' <small class="muted">(قبلی: '.$prevScoreForLine.' · '
        .($deltaForLine>0?'+':'').$deltaForLine
        .' <span class="score score-'.($deltaForLine>=0?'high':'low').'">'.($deltaForLine>=0?'▲':'▼').'</span></small>)' : '') : '' ?></p>

<?php if($flash): ?><div class="flash <?= $flash[0]==='ok' ? 'ok' : 'err' ?>"><?= h($flash[1]) ?></div><?php endif; ?>

<?php if ($approval): ?>
<div class="flash ok approved-banner">
  ✅ <b>محتوای تأییدشده‌ی نهایی</b> — تأییدشده توسط <b><?= h($approval['display_name'] ?? 'ویراستار') ?></b> در <?= h(\Resaname\Support::faDate($approval['created_at'])) ?>
  <?php if ($approval['quality_score']!==null): ?> با امتیاز <b class="score score-<?= $scoreCls((int)$approval['quality_score']) ?>"><?= (int)$approval['quality_score'] ?></b><?php endif; ?>
  <?php if (trim((string)$approval['note'])!==''): ?> — «<?= h(mb_substr((string)$approval['note'],0,160)) ?>»<?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ نوار تصمیم ویراستار ============ -->
<form method="post" class="actionbar">
  <input type="hidden" name="_csrf" value="<?= \Resaname\Support::csrf() ?>">
  <label class="ef-lbl block">نظر / توضیح (اختیاری — برای «رد» یا «بازبینی مجدد AI» حتماً بنویسید تا هوش مصنوعی دور بعد با توجه به آن بازبینی کند):
    <textarea name="note" rows="2" placeholder="مثال: سهم نظریه‌پرداز دوم اشتباه ذکر شده؛ سال شکل‌گیری باید بازه باشد؛ بخش نقدها ضعیف است..."></textarea>
  </label>
  <div class="actionbar-btns">
    <button type="submit" name="action" value="approve" class="btn ok" <?= $inQueue ? 'disabled' : '' ?> onclick="return confirm(this.dataset.msg)" data-msg="<?= h($approveMsg) ?>">✅ تأیید محتوا<?= $inQueue ? ' (در انتظار پایان دور جدید)' : '' ?></button>
    <button type="submit" name="action" value="reject" class="btn no" <?= $inQueue ? 'disabled' : '' ?>>❌ رد محتوا</button>
    <button type="submit" name="action" value="requeue" class="btn re" <?= $inQueue ? 'disabled' : '' ?>>🔁 بازبینی مجدد توسط AI<?= $inQueue ? ' (هم‌اکنون در صف است)' : '' ?></button>
    <a class="btn ed" href="/document.php?id=<?= (int)$id ?>&edit=1">✏️ ویرایش توسط خودم</a>
  </div>
  <p class="muted" style="margin:8px 0 0">با «تأیید» محتوا برای انتشار نهایی آماده می‌شود (ثبت در جدول بررسی‌ها + تاریخچه نسخه‌ها). با «رد» می‌توانید بعداً یا AI را با نظرتان بازبینی مجدد کنید یا خودتان ویرایش کنید. بعد از هر دور AI، اگر امتیاز زیر ۹۰ باشد، worker به‌صورت خودکار تا رسیدن به ۹۰ (حداکثر ۲ دور تکمیلی یا تا پایان بهبود) بازبینی را ادامه می‌دهد. همه‌ی عملیات‌ها در `editorial_reviews` و `editorial_revisions` ثبت می‌شوند.</p>
</form>

<?php
$iq=$db->prepare("SELECT * FROM editorial_issues WHERE document_id=? ORDER BY id");$iq->execute([$id]);$aiIssues=$iq->fetchAll();
$sevL=['info'=>'اطلاعاتی','warning'=>'هشدار','error'=>'جدی'];
$typeL=['typo'=>'املا/نگارش','factual'=>'فکتی','structure'=>'ساختاری','reference'=>'منبع','content'=>'محتوا','other'=>'سایر'];
?>
<?php if ($editing): ?>
<!-- ============ فرم ویرایش دستی ویراستار ============ -->
<form method="post" class="dossier edit-mode">
  <input type="hidden" name="_csrf" value="<?= \Resaname\Support::csrf() ?>">
  <input type="hidden" name="action" value="<?= $doc['source_table']==='cards' ? 'save_dossier' : 'save_legacy' ?>">
  <h2>ویرایش دستی توسط ویراستار
    <?php if ($doc['source_table']==='cards'): ?><small class="muted">پرونده‌ی ساختاریافته · ۲۲ فیلد · ذخیره در <code class="ds-key">dossier_json</code></small>
    <?php else: ?><small class="muted">نسخه جاری (<code class="ds-key">current_json</code>) — فقط مقدار فیلدهای موجود قابل تغییر است، فیلد اضافه/کم نمی‌شود</small><?php endif; ?>
  </h2>
  <?php if ($doc['source_table']==='cards'): ?>
  <?php foreach (Dossier::grouped() as $gk=>$g): ?>
  <div class="ds-group">
    <h3 class="ds-group-title"><?= h($g['label']) ?></h3>
    <?php foreach ($g['fields'] as $f):
        $v=$dossier[$f['key']] ?? null;
    ?>
    <div class="ds-field ef-field">
      <h4 class="ds-field-title">
        <?= h($f['label']) ?>
        <code class="ds-key"><?= h($f['key']) ?></code>
        <?php if ($f['required']): ?><span class="ds-req">ضروری</span><?php endif; ?>
      </h4>
      <div class="ds-body"><?= ef_control($f['key'], $v) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <?php else: ?>
  <label class="ef-lbl block">محتوای JSON (نسخه جاری) — می‌توانید مقادیر را ویرایش کنید:
    <textarea name="json" rows="22" dir="ltr" style="text-align:left;font:12px/1.7 Consolas,monospace"><?=h(json_encode($current,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES))?></textarea>
  </label>
  <?php endif; ?>
  <div class="actionbar-btns">
    <button type="submit" class="btn ok">💾 ذخیره ویرایش من</button>
    <a class="btn" href="/document.php?id=<?= (int)$id ?>">انصراف</a>
  </div>
</form>
<script>
function efRemove(btn){ var it=btn.closest('.ef-item'); if(it) it.remove(); }
document.querySelectorAll('[data-ef-add]').forEach(function(btn){
  btn.addEventListener('click', function(){
    var key=btn.getAttribute('data-ef-add');
    var wrap=document.querySelector('[data-ef-wrap="'+key+'"]');
    var tpl=wrap.querySelector('template[data-ef-tpl="'+key+'"]');
    if(!wrap||!tpl) return;
    var idx=parseInt(wrap.getAttribute('data-n')||'0',10);
    wrap.setAttribute('data-n',String(idx+1));
    var clone=tpl.content.cloneNode(true);
    var item=document.createElement('div');
    item.className='ef-item';
    item.appendChild(clone);
    item.querySelectorAll('input').forEach(function(el){ el.name=el.name.replace(/__I__/g,String(idx)); });
    var rm=document.createElement('button');
    rm.type='button'; rm.className='btn-sm danger'; rm.textContent='✕ حذف';
    rm.onclick=function(){ item.remove(); };
    item.appendChild(rm);
    wrap.appendChild(item);
  });
});
</script>
<?php endif; ?>

<?php if($doc['quality_score']!==null||$aiIssues||$scoreRowsRev): ?><section class="ai-box"><h2>بررسی هوش مصنوعی</h2>
<?php if($doc['quality_score']!==null): ?><p>امتیاز جاری: <b class="score score-<?= $scoreCls((int)$doc['quality_score']) ?>"><?= (int)$doc['quality_score'] ?></b> از ۱۰۰<?= $prevScoreForLine!==null ? ' <small class="muted">(دور قبل: '.$prevScoreForLine.' · تغییر: '
        .($deltaForLine>0?'+':'').$deltaForLine.')</small>' : '' ?></p><?php endif; ?>
<?php if($scoreRowsRev): ?>
<h3>تاریخچه‌ی امتیاز (هر دور بررسی / هر نسخه‌ی ثبت‌شده)</h3>
<table>
<thead><tr><th>تاریخ</th><th>امتیاز</th><th>تغییر</th><th>رویداد</th><th>خلاصه</th></tr></thead>
<tbody>
<?php foreach($scoreRowsRev as $r): ?>
<tr>
<td><?= h(\Resaname\Support::faDate($r['at'])) ?></td>
<td><b class="score score-<?= $scoreCls($r['score']) ?>"><?= $r['score'] ?></b></td>
<td><?= $r['delta']===null ? '—' : '<span class="score score-'.($r['delta']>=0?'high':'low').'">'.($r['delta']>0?'+':'').$r['delta'].'</span>' ?></td>
<td><?= h($revL[$r['type']] ?? $r['type']) ?></td>
<td><?= $r['note']!=='' ? h(mb_substr($r['note'],0,140)) : '—' ?></td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
<?php endif; ?>
<?php if($aiIssues): ?><table><thead><tr><th>فیلد</th><th>نوع</th><th>درجه</th><th>شرح مشکل</th><th>پیشنهاد</th></tr></thead><tbody>
<?php foreach($aiIssues as $i): ?><tr><td><?= issueFieldLabel((string)$i['field_name']) ?></td><td><?=h($typeL[$i['issue_type']] ?? $i['issue_type'])?></td><td class="sev-<?=h($i['severity'])?>"><?=h($sevL[$i['severity']] ?? $i['severity'])?></td><td><?=h($i['description'])?></td><td><?=h($i['suggestion'])?></td></tr><?php endforeach; ?>
</tbody></table>
<?php else: ?><p class="pv-empty">مشکلی ثبت نشده است.</p><?php endif; ?>
</section><?php endif; ?>

<?php if ($doc['source_table']==='theorists'): ?>
<!-- ============ داسییر ساختاریافته شخص (ساختار رسمی ۲۱ فیلدی / ۵ گروه) ============ -->
<section class="dossier">
  <h2>پرونده‌ی ساختاریافته شخص <small class="muted">نظریه‌پرداز / دانشمند / مؤثر</small></h2>
  <?php if ($personDossier!==null): ?>
  <p class="muted">ساختار رسمی · نسخه <?= (int)PersonDossier::VERSION ?> · تکمیل: <b><?= (int)($personMeta['completeness'] ?? 0) ?>٪</b> · وضعیت: <b><?= h($pdStatusL[$personMeta['status']] ?? $personMeta['status']) ?></b> · به‌روزرسانی: <?= h(\Resaname\Support::faDate($personMeta['updated_at'])) ?></p>
  <?php foreach (PersonDossier::grouped() as $gk=>$g): ?>
  <div class="ds-group">
    <h3 class="ds-group-title"><?= h($g['label']) ?></h3>
    <?php foreach ($g['fields'] as $f):
        $v=$personDossier[$f['key']] ?? null;
        $empty=PersonDossier::isEmpty($v);
    ?>
    <div class="ds-field<?= $empty ? ' ds-empty-field' : '' ?>">
      <h4 class="ds-field-title">
        <?= h($f['label']) ?>
        <code class="ds-key"><?= h($f['key']) ?></code>
        <?php if ($f['required']): ?><span class="ds-req">ضروری</span><?php endif; ?>
        <?php if (!empty($f['links'])): ?><span class="ds-req">لینک‌دار</span><?php endif; ?>
        <?php if ($empty): ?><span class="ds-missing">خالی</span><?php endif; ?>
      </h4>
      <div class="ds-body"><?= pd_render($f['key'], $v) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
  <form method="post" class="field" style="margin-top:16px">
    <input type="hidden" name="_csrf" value="<?= \Resaname\Support::csrf() ?>">
    <input type="hidden" name="action" value="queue_person_dossier">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <button type="submit" class="btn">🔄 بازسازی پرونده (افزودن به صف)</button>
    <span class="hint">برای ساخت مجدد پرونده با آخرین نسخه‌ی مدل و فهرست محتوا؛ در صف person_dossier قرار می‌گیرد.</span>
  </form>
  <?php else: ?>
  <p class="pv-empty">پرونده‌ی ساختاریافته‌ی این شخص هنوز ساخته نشده است.</p>
  <form method="post" class="field">
    <input type="hidden" name="_csrf" value="<?= \Resaname\Support::csrf() ?>">
    <input type="hidden" name="action" value="queue_person_dossier">
    <input type="hidden" name="id" value="<?= (int)$id ?>">
    <button type="submit" class="btn go">👤 ساخت پرونده‌ی ساختاریافته (۲۱ بخش) و افزودن به صف</button>
    <span class="hint">هوش مصنوعی موظف است هر ۲۱ بخشِ ساختار ۵ گروهی را رعایت کند؛ فکت ساختگی مجاز نیست و لینک‌ها فقط از محتوای موجود سایت ساخته می‌شوند. worker باید روشن باشد (یا cron آن را اجرا کند).</span>
  </form>
  <?php endif; ?>
</section>
<?php endif; ?>

<?php if ($isCard && !$editing): ?>
<!-- ============ داسییر ساختاریافته (ساختار رسمی ۲۲ فیلدی) ============ -->
<section class="dossier">
  <h2>پرونده‌ی ساختاریافته نظریه <small class="muted">ساختار رسمی · نسخه <?= (int)Dossier::VERSION ?></small></h2>
  <?php foreach (Dossier::grouped() as $gk=>$g): ?>
  <div class="ds-group">
    <h3 class="ds-group-title"><?= h($g['label']) ?></h3>
    <?php foreach ($g['fields'] as $f):
        $v=$dossier[$f['key']] ?? null;
        $empty=Dossier::isEmpty($v);
    ?>
    <div class="ds-field<?= $empty ? ' ds-empty-field' : '' ?>">
      <h4 class="ds-field-title">
        <?= h($f['label']) ?>
        <code class="ds-key"><?= h($f['key']) ?></code>
        <?php if ($f['required']): ?><span class="ds-req">ضروری</span><?php endif; ?>
        <?php if ($empty): ?><span class="ds-missing">خالی</span><?php endif; ?>
      </h4>
      <div class="ds-body"><?= ds_render($f['key'], $v) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
</section>

<?php if ($diffShown): ?>
<section class="ai-box diff-box">
  <h2>تغییرات اعمال‌شده (چیدمان + ویراستاری در ساختار جدید: <?= count($diffShown) ?> بخش)</h2>
  <p class="muted">ستون راست: محتوای مربوط در کارت خام · ستون چپ: همان محتوا در بخش ساختاریافته‌ی پرونده. بخش‌های خالی به‌صورت خودکار در «بررسی هوش مصنوعی» گزارش شده‌اند.</p>
  <?php foreach ($diffShown as $d): ?>
  <div class="diff-field">
    <h3>بخش: <?= h($d['label']) ?> <code class="ds-key"><?= h($d['key']) ?></code> <span class="ds-status st-<?= h($d['status']) ?>"><?= h($statusL[$d['status']]) ?></span></h3>
    <div class="diff-cols">
      <div><span class="diff-label old">منبع (کارت خام<?= $d['src']!==null ? ' · '.h($d['src']) : '' ?>)</span><div class="diff-old"><?= $d['old']==='' ? '(—)' : h($d['old']) ?></div></div>
      <div><span class="diff-label new">پرونده‌ی ساختاریافته</span><div class="diff-new"><?= $d['new']==='' ? '(خالی)' : h($d['new']) ?></div></div>
    </div>
  </div>
  <?php endforeach; ?>
</section>
<?php endif; ?>

<div class="columns pvrow">
  <section><h2>نمایشی — نمای مخاطب (سایت اصلی)</h2><div class="pv"><?= $pv_html ?></div></section>
  <section><h2>JSON خام</h2>
    <details><summary>نسخه اولیه (original_json)</summary><pre><?=h(json_encode($original,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre></details>
    <details><summary>نسخه جاری ویرایش‌شده (current_json)</summary><pre><?=h(json_encode($current,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre></details>
  </section>
</div>
<?php else: ?>
<?php if (!$editing): ?>
<!-- ============ چیدمان قبلی (جداول غیر-کارت یا بدون داسییر) ============ -->
<div class="columns">
  <section><h2>نسخه اولیه</h2><pre><?=h(json_encode($original,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre></section>
  <section><h2>نمایشی — نمای مخاطب</h2><div class="pv"><?= $pv_html ?></div></section>
  <section><h2>نسخه جاری</h2><pre><?=h(json_encode($current,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE))?></pre></section>
</div>
<?php
function diffVal($v){ if($v===null) return null; return is_scalar($v) ? (string)$v : json_encode($v,JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT); }
$changed=[];
foreach (is_array($original)?$original:[] as $k=>$v) { $cv=$current[$k] ?? null; if (diffVal($v)!==diffVal($cv)) $changed[$k]=[$v,$cv]; }
foreach (is_array($current)?$current:[] as $k=>$v) { if (!array_key_exists($k,is_array($original)?$original:[])) $changed[$k]=[null,$v]; }
?>
<?php if($changed): ?><section class="ai-box diff-box"><h2>تغییرات اعمال‌شده (<?= count($changed) ?> فیلد)</h2>
<?php foreach($changed as $k=>$pair): $ov=diffVal($pair[0]); $nv=diffVal($pair[1]); ?>
<div class="diff-field">
<h3>فیلد: <?= h($k) ?></h3>
<div class="diff-cols">
<div><span class="diff-label old">نسخه اولیه</span><div class="diff-old"><?= h($ov ?? '(نمی‌باشد)') ?></div></div>
<div><span class="diff-label new">نسخه جاری (پیشنهادهای AI)</span><div class="diff-new"><?= h($nv ?? '(حذف شده)') ?></div></div>
</div>
</div>
<?php endforeach; ?></section><?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<p class="notice">ویرایش دستی، تأیید و رد محتوا فعال است و همه‌ی تصمیم‌ها در تاریخچه ثبت می‌شوند. «انتشار» به سایت اصلی فعلاً غیرفعال است تا نگاشت فیلدها و قرارداد API تأیید شود.</p>
</main></body></html>
