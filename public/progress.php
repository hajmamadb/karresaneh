<?php
require dirname(__DIR__).'/bootstrap.php';
use Resaname\Structure\ContentTypes;

function h($v){return \Resaname\Support::e((string)$v);}

\Resaname\Auth::requireLogin();

$db=\Resaname\Db::editorial();

// شروعِ بچِ بازبینی سراسری: اولین کارِ آن (id=1104)؛ همه‌ی کارها و نسخ‌هایی که بعد از آن ساخته شده‌اند متعلق به همین بچ‌اند
$qb=$db->query("SELECT MIN(created_at) AS bs, COUNT(*) AS n FROM editorial_jobs WHERE id>=1104");
$batch=$qb->fetch();
$batchStart=$batch['bs'];

// شمارش صف (کارهای این بچ)
$queue=$db->query("SELECT status,COUNT(*) c FROM editorial_jobs WHERE id>=1104 GROUP BY status")->fetchAll(PDO::FETCH_KEY_PAIR);
$pending=(int)($queue['pending'] ?? 0); $running=(int)($queue['running'] ?? 0); $done=(int)($queue['done'] ?? 0);
$failed=(int)($queue['failed'] ?? 0);

// اسناد کل + اسندی که دور اولِ بچ برایشان تمام شده (حداقل یک ai_draft جدید دارند)
$total=(int)$db->query("SELECT COUNT(*) FROM editorial_documents")->fetchColumn();
$qd=$db->prepare("SELECT COUNT(*) FROM editorial_documents d WHERE EXISTS (SELECT 1 FROM editorial_revisions r WHERE r.document_id=d.id AND r.revision_type='ai_draft' AND r.created_at>=?)");
$qd->execute([$batchStart]);
$firstPass=(int)$qd->fetchColumn();
$pct=$total ? round(100*$firstPass/$total,1) : 0;

// طبقه‌بندی‌شده‌ها به تفکیک نوع
$cls=$db->query("SELECT content_type, content_category, COUNT(*) c FROM editorial_documents WHERE content_type IS NOT NULL GROUP BY content_type, content_category ORDER BY c DESC")->fetchAll();
$classified=(int)array_sum(array_column($cls,'c'));

// توزیع امتیازِ دورهای این بچ
$dist=$db->prepare("SELECT quality_score, COUNT(*) c FROM editorial_revisions WHERE revision_type='ai_draft' AND created_at>=? GROUP BY quality_score ORDER BY quality_score");
$dist->execute([$batchStart]);
$dist=$dist->fetchAll();
$rounds=(int)array_sum(array_column($dist,'c'));
$reached90=$db->prepare("SELECT COUNT(DISTINCT document_id) FROM editorial_revisions WHERE revision_type='ai_draft' AND created_at>=? AND quality_score>=90");
$reached90->execute([$batchStart]); $reached90=(int)$reached90->fetchColumn();

// تخمین سرعت و پایان دور اول — همه در زمانِ خودِ DB محاسبه می‌شود.
// نکته: ساعت سیستم روز ۱۱/۰۸/۱۴۰۵ (۱۱/۱۱ میلادی) ساعت ~۱۰:۵۵ (به‌وقتِ DB) ۱۲ ساعت جلو کشیده شد
// (رفع خودکار ویندوز). به‌خاطر همین، از MIN(created_at) برای «ساز» استفاده نمی‌کنیم؛
// شروع بچ در مقیاسِ ساعتِ فعلیِ DB = ۲۰۲۶-۱۱-۱۰ ۱۴:۲۹:۵۶ (یعنی ۰۲:۲۹:۵۶ پیش‌از-رفع + ۱۲ ساعت).
$elapsedMin=(int)$db->query("SELECT GREATEST(1, TIMESTAMPDIFF(MINUTE, '2026-11-10 14:29:56', NOW()))")->fetchColumn();
$rate=$elapsedMin ? $firstPass/$elapsedMin : 0; // کارت بر دقیقه
$remaining=$total-$firstPass;
$etaMin=$rate>0 ? $remaining/$rate : null;
$etaStr=($etaMin!==null && $remaining>0) ? round($etaMin/60,1) : null;

// آخرین فعالیت worker از لاگ
$logTail='';
$logFile=dirname(__DIR__).'/storage/logs/app.log';
if (is_file($logFile)) {
    $lines=file($logFile, FILE_IGNORE_NEW_LINES|FILE_SKIP_EMPTY_LINES);
    if ($lines) $logTail=end($lines);
}

// به‌روزرسانی خودکار هر ۳ دقیقه
$refreshAt=date('H:i:s', time()+180);
?>
<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta http-equiv="refresh" content="180">
<title>پیشرفت بازبینی سراسری</title><link rel="stylesheet" href="/style.css"></head><body>
<header><a href="/">← بازگشت به فهرست</a><strong>پیشرفت بازبینی سراسری</strong></header>
<main>
<h1>بازبینی هوش مصنوعی — همه‌ی محتواها</h1>
<p class="muted">این صفحه هر ۳ دقیقه به‌صورت خودکار به‌روزرسانی می‌شود (نوبت بعدی: <?=h($refreshAt)?>).</p>

<?php if ($logTail): ?>
<p class="muted">آخرین فعالیت worker: <code><?= h(mb_substr($logTail,0,160)) ?></code></p>
<?php else: ?>
<p class="flash err">لاگ worker یافت نشد — بررسی کنید که worker در حال اجراست.</p>
<?php endif; ?>

<div class="stats">
  <a class="statcard" href="/" title="میز سردبیری"><b><?= (int)$total ?></b><span>کل اسناد</span></a>
  <div class="statcard"><b><?= (int)$firstPass ?></b><span>دور اول کامل (<?= (float)$pct ?>٪)</span></div>
  <div class="statcard"><b><?= (int)$pending ?></b><span>در صف</span></div>
  <div class="statcard"><b><?= (int)$running ?></b><span>در حال پردازش</span></div>
  <div class="statcard"><b><?= (int)$classified ?></b><span>طبقه‌بندی‌شده</span></div>
  <div class="statcard"><b><?= (int)$reached90 ?></b><span>رسیده به 90+</span></div>
</div>

<div style="margin:18px 0">
  <div style="background:#e5e7eb;border-radius:8px;height:22px;overflow:hidden">
    <div style="background:#059669;height:100%;width:<?= (float)$pct ?>%;transition:width .5s"></div>
  </div>
  <p class="muted" style="margin:6px 0 0">
    دور اول: <?= (int)$firstPass ?> از <?= (int)$total ?>
    <?php if ($etaStr!==null): ?>
      | سرعت: <?= number_format($rate,2) ?> کارت/دقیقه | با این سرعت، پایان دور اول ≈ <?= (float)$etaStr ?> ساعت دیگر
    <?php else: ?>| دور اول تمام شد ✅<?php endif; ?>
  </p>
</div>

<h2>توزیع امتیازِ <?= (int)$rounds ?> دور اخیر</h2>
<table><thead><tr><th>امتیاز</th><th>تعداد</th><th></th></tr></thead><tbody>
<?php foreach ($dist as $row): $s=(int)$row['quality_score']; $w=$rounds ? round(100*(int)$row['c']/$rounds) : 0; ?>
  <tr>
    <td><b class="score score-<?= $s>=80?'high':($s>=50?'mid':'low') ?>"><?= (int)$s ?></b></td>
    <td><?= (int)$row['c'] ?></td>
    <td style="width:40%"><div style="background:#059669;height:10px;width:<?= (int)$w ?>%;border-radius:4px"></div></div>
  </tr>
<?php endforeach; ?>
</tbody></table>

<h2>نوع محتوا (به تفکیک)</h2>
<table><thead><tr><th>نوع</th><th>دسته</th><th>تعداد</th></tr></thead><tbody>
<?php if (!$cls): ?><tr><td colspan="3" class="muted">هنوز چیزی طبقه‌بندی نشده.</td></tr><?php endif; ?>
<?php foreach ($cls as $row): ?>
  <tr><td><b><?= h($row['content_type']) ?></b></td><td class="muted"><?= h(ContentTypes::categoryLabel($row['content_category'])) ?></td><td><?= (int)$row['c'] ?></td></tr>
<?php endforeach; ?>
</tbody></table>

<?php if ($failed>0): ?><p class="flash err"><?= (int)$failed ?> کار با خطا تمام شده‌اند — در صف دوباره بررسی کنید.</p><?php endif; ?>
</main></body></html>
