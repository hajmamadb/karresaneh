<?php
require dirname(__DIR__).'/bootstrap.php';

if (PHP_SAPI !== 'cli') exit("CLI only\n");

/**
 * تأیید گروهیِ سندهایی که به امتیاز استاندارد (>= 90) رسیده‌اند.
 * دقیقاً همان سه عملیات «تأیید» در document.php را انجام می‌دهد:
 *  1) ثبت در editorial_reviews (decision=approved)
 *  2) ثبت نسخه‌ی approved در editorial_revisions (با امتیاز لحظه‌ی تأیید)
 *  3) workflow_status='approved'
 * فقط سندهای draft_ready را می‌گیرد (تأیید دوباره نمی‌شود) و سندی که هنوز
 * دورِ خودکارِ pending/running دارد را رد می‌کند (همان قاعده‌ی دکمه‌ی تأییدِ UI).
 *
 * usage: php bin/approve-high.php [حداقل امتیاز، پیش‌فرض 90]
 */
$minScore = isset($argv[1]) ? max(0, min(100, (int)$argv[1])) : 90;

$db = \Resaname\Db::editorial();
$me = $db->query("SELECT id, display_name FROM editorial_users WHERE username='editor'")->fetch();
if (!$me) exit("No editor user found\n");

$q = $db->prepare("SELECT id, title, quality_score, dossier_json, current_json FROM editorial_documents d
                   WHERE quality_score >= ? AND workflow_status='draft_ready'
                     AND NOT EXISTS (SELECT 1 FROM editorial_jobs j WHERE j.document_id=d.id AND j.status IN ('pending','running'))
                   ORDER BY id");
$q->execute([$minScore]);
$docs = $q->fetchAll();

if (!$docs) {
    echo "No documents with score >= $minScore in draft_ready.\n";
    exit(0);
}

$note = "تأیید گروهی توسط ویراستار: امتیاز {$minScore}+ (استاندارد رسانامه)";
$insR = $db->prepare("INSERT INTO editorial_reviews (document_id, editor_id, decision, note) VALUES (?,?,?,?)");
$insRev = $db->prepare("INSERT INTO editorial_revisions (document_id, revision_type, content_json, content_hash, quality_score, created_by, note) VALUES (?,?,?,?,?,?,?)");
$upd = $db->prepare("UPDATE editorial_documents SET workflow_status='approved', updated_at=NOW() WHERE id=?");

$count = 0;
foreach ($docs as $d) {
    $score = $d['quality_score'] !== null ? (int)$d['quality_score'] : null;
    $content = (string)($d['dossier_json'] ?: $d['current_json']);
    $db->beginTransaction();
    try {
        $insR->execute([$d['id'], (int)$me['id'], 'approved', $note]);
        $insRev->execute([$d['id'], 'approved', $content, hash('sha256', $content), $score, (int)$me['id'], $note]);
        $upd->execute([$d['id']]);
        $db->commit();
        $count++;
        echo "approved doc {$d['id']} (score $score): {$d['title']}\n";
    } catch (\Throwable $e) {
        $db->rollBack();
        \Resaname\Support::log('bulk_approve_failed', ['doc' => $d['id'], 'error' => $e->getMessage()]);
        echo "FAILED doc {$d['id']}: {$e->getMessage()}\n";
    }
}
\Resaname\Support::log('bulk_approve', ['count' => $count, 'min_score' => $minScore, 'by' => $me['display_name']]);
echo "Done: $count document(s) approved.\n";
