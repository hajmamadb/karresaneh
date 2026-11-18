<?php
require dirname(__DIR__).'/bootstrap.php';
\Resaname\Auth::requireLogin();
$db=\Resaname\Db::editorial();
function h($v){return \Resaname\Support::e((string)$v);}

// ---------- فیلترها ----------
$q=trim((string)($_GET['q'] ?? ''));
$table=(string)($_GET['table'] ?? '');
$wf=(string)($_GET['wf'] ?? '');
$score=(string)($_GET['score'] ?? '');
$page=max(1,(int)($_GET['page'] ?? 1));
$per=25;

$WF=['imported'=>'واردشده','queued'=>'در صف','processing'=>'در حال پردازش','draft_ready'=>'پیش‌نویس آماده بررسی','review'=>'در حال بررسی','approved'=>'تأییدشده','rejected'=>'ردشده','published'=>'انتشاریافته','error'=>'خطا'];
$TBL=['cards'=>'کارت','theorists'=>'نظریه‌پرداز','library_resources'=>'منبع کتابخانه'];
$SCORES=['low'=>'نمره کم (زیر ۵۰)','mid'=>'متوسط (۵۰-۷۹)','high'=>'بالا (۸۰ به بالا)','none'=>'هنوز نمره ندارد'];

$where=[]; $params=[];
if ($q !== '') { $where[]='(title LIKE ? OR source_id LIKE ?)'; $like='%'.$q.'%'; $params[]=$like; $params[]=$like; }
if (isset($TBL[$table])) { $where[]='source_table=?'; $params[]=$table; }
if (isset($WF[$wf])) { $where[]='workflow_status=?'; $params[]=$wf; }
if ($score==='low') $where[]='quality_score IS NOT NULL AND quality_score<50';
elseif ($score==='mid') $where[]='quality_score>=50 AND quality_score<80';
elseif ($score==='high') $where[]='quality_score>=80';
elseif ($score==='none') $where[]='quality_score IS NULL';
$whereSql=$where ? 'WHERE '.implode(' AND ',$where) : '';

$cnt=$db->prepare("SELECT COUNT(*) FROM editorial_documents $whereSql"); $cnt->execute($params); $total=(int)$cnt->fetchColumn();
$pages=max(1,(int)ceil($total/$per));
$page=min($page,$pages);
$offset=($page-1)*$per;

$stmt=$db->prepare("SELECT d.id,d.source_table,d.source_id,d.title,d.workflow_status,d.quality_score,d.updated_at,d.content_category,d.content_type,
    (SELECT r2.quality_score FROM editorial_revisions r2 WHERE r2.document_id=d.id AND r2.quality_score IS NOT NULL ORDER BY r2.id DESC LIMIT 1 OFFSET 1) AS prev_score,
    (pd.id IS NOT NULL) AS has_dossier, pd.status AS dossier_status
    FROM editorial_documents d
    LEFT JOIN editorial_person_dossiers pd ON pd.document_id=d.id
    $whereSql ORDER BY d.updated_at DESC LIMIT $per OFFSET $offset");
$stmt->execute($params);
$rows=$stmt->fetchAll();

// شمارنده‌های کلی (بدون فیلتر)
$counts=$db->query("SELECT workflow_status,COUNT(*) c FROM editorial_documents GROUP BY workflow_status")->fetchAll();

// رشته query برای صفحه‌بندی (حفظ فیلترها)
function qs(array $over=[]): string {
    $base=['q'=>$_GET['q'] ?? '', 'table'=>$_GET['table'] ?? '', 'wf'=>$_GET['wf'] ?? '', 'score'=>$_GET['score'] ?? ''];
    $base=array_merge($base,$over);
    return http_build_query(array_filter($base,fn($v)=>$v!==''));
}
// صفحه‌های نمایش (حداکثر ۷ لینک)
$pstart=max(1,$page-3); $pend=min($pages,$pstart+6); $pstart=max(1,$pend-6);
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>میز سردبیری رسانامه</title><link rel="stylesheet" href="/style.css"></head><body>
<header><strong>رسانامه | کارخانه هوشمندسازی</strong><span><?=h($_SESSION['editor_user']['display_name'])?><?= ($_SESSION['editor_user']['role'] ?? '')==='admin' ? ' · <a href="/settings.php">تنظیمات</a>' : '' ?> · <a href="/logout.php">خروج</a></span></header>
<main><h1>میز سردبیری</h1><section class="stats"><?php foreach($counts as $c): ?><a class="statcard" href="/?wf=<?=h($c['workflow_status'])?>" title="فیلتر روی: <?=h($WF[$c['workflow_status']] ?? $c['workflow_status'])?>"><b><?=h($c['c'])?></b><span><?=h($WF[$c['workflow_status']] ?? $c['workflow_status'])?></span></a><?php endforeach; ?></section>

<h2>محتواها <small class="muted">(<?= (int)$total ?> مورد<?= $page>1 || $pages>1 ? ' — صفحه '.$page.' از '.$pages : '' ?>)</small></h2>
<form method="get" class="filterbar">
  <input type="search" name="q" value="<?=h($q)?>" placeholder="جستجو در عنوان یا شناسه...">
  <select name="table">
    <option value="">همه انواع</option>
    <?php foreach($TBL as $k=>$lab): ?><option value="<?=h($k)?>" <?= $table===$k?'selected':'' ?>><?=h($lab)?></option><?php endforeach; ?>
  </select>
  <select name="wf">
    <option value="">همه وضعیت‌ها</option>
    <?php foreach($WF as $k=>$lab): ?><option value="<?=h($k)?>" <?= $wf===$k?'selected':'' ?>><?=h($lab)?></option><?php endforeach; ?>
  </select>
  <select name="score">
    <option value="">همه نمرات</option>
    <?php foreach($SCORES as $k=>$lab): ?><option value="<?=h($k)?>" <?= $score===$k?'selected':'' ?>><?=h($lab)?></option><?php endforeach; ?>
  </select>
  <button type="submit" class="btn">فیلتر</button>
  <a class="btn" href="/">پاک کردن</a>
</form>

<table><thead><tr><th>عنوان</th><th>نوع</th><th>نوع محتوا</th><th>شناسه</th><th>وضعیت</th><th>امتیاز</th><th>پرونده</th><th>به‌روزرسانی</th><th></th></tr></thead><tbody>
<?php if(!$rows): ?><tr><td colspan="9" class="muted">موردی با این فیلترها پیدا نشد.</td></tr><?php endif; ?>
<?php $pdStL=['ai_draft'=>'پیش‌نویس AI','review'=>'در حال بررسی','approved'=>'تأییدشده','published'=>'انتشاریافته'];
foreach($rows as $r): $sc=$r['quality_score']; $ps=$r['prev_score']; $delta=($sc!==null&&$ps!==null)?((int)$sc-(int)$ps):null; ?><tr>
  <td><?=h($r['title'])?></td>
  <td><?=h($TBL[$r['source_table']] ?? $r['source_table'])?></td>
  <td><?php if ($r['content_type']!==null && $r['content_type']!==''): ?><b><?= h($r['content_type']) ?></b> <small class="muted"><?= h(\Resaname\Structure\ContentTypes::categoryLabel($r['content_category'])) ?></small><?php else: ?><span class="muted">—</span><?php endif; ?></td>
  <td><?=h($r['source_id'])?></td>
  <td><?=h($WF[$r['workflow_status']] ?? $r['workflow_status'])?></td>
  <td><?= $sc===null ? '<span class="muted">—</span>' : '<b class="score score-'.($sc>=80?'high':($sc>=50?'mid':'low')).'">'.((int)$sc).'</b>'.($delta!==null ? ' <small class="muted">(قبلی: '.((int)$ps).' · '.($delta>0?'+':'').$delta.' <span class="score score-'.($delta>=0?'high':'low').'">'.($delta>=0?'▲':'▼').'</span></small>' : '') ?></td>
  <td><?= ($r['source_table']==='theorists' && !empty($r['has_dossier'])) ? '<b title="'.h($pdStL[$r['dossier_status']] ?? (string)$r['dossier_status']).'">✅</b>' : '<span class="muted">—</span>' ?></td>
  <td class="muted"><?=h(\Resaname\Support::faDate($r['updated_at']))?></td>
  <td><a href="/document.php?id=<?=(int)$r['id']?>">بررسی</a></td>
</tr><?php endforeach; ?>
</tbody></table>

<?php if($pages>1): ?><nav class="pager">
<?php if($page>1): ?><a href="/?<?=h(qs(['page'=>$page-1]))?>">› قبلی</a><?php endif; ?>
<?php for($i=$pstart;$i<=$pend;$i++): ?>
  <?php if($i===$page): ?><span class="cur"><?= (int)$i ?></span><?php else: ?><a href="/?<?=h(qs(['page'=>$i]))?>"><?= (int)$i ?></a><?php endif; ?>
<?php endfor; ?>
<?php if($page<$pages): ?><a href="/?<?=h(qs(['page'=>$page+1]))?>">بعدی ‹</a><?php endif; ?>
</nav><?php endif; ?>
</main></body></html>
