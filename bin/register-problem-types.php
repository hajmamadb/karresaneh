<?php
require dirname(__DIR__).'/bootstrap.php';
$db=\Resaname\Db::editorial();

/**
 * ثبتِ «نوعِ مشکل» برای ۴۰ کارتِ 86–89 — بعد از اینکه دورِ ارتقاء نشان داد
 * داورِ AI روی این کارت‌ها ناپایاست و بدونِ هیچِ تغییرِ محتواییِ واقعی، امتیاز
 * را ۴ تا ۷ نمره بالا می‌برد. این ثبت‌ها برای تصمیمِ ویراستار/کاربر است.
 *
 * هر کارتِ هدف یک issue با issue_type='process' و severity='warning' می‌گیرد:
 *  - field_name=NULL (مشکلِ کلیِ کارت، نه یک فیلدِ خاص)
 *  - is_resolved=0 (در انتظارِ تصمیم)
 *  - description = نوعِ مشکل + شواهدِ دقیق
 *  - suggestion  = راهِ حلِ پیشنهادی
 */

// 40 docs in 86-89 (draft_ready)
$docs=$db->query("SELECT d.id, d.title, d.quality_score s, d.content_type,
    (SELECT COUNT(*) FROM editorial_issues i WHERE i.document_id=d.id) n_issues
    FROM editorial_documents d
    WHERE d.workflow_status='draft_ready' AND d.quality_score BETWEEN 86 AND 89
    ORDER BY d.id")->fetchAll();
if(!count($docs)) { echo "no target docs\n"; exit(0); }

$ins=$db->prepare("INSERT INTO editorial_issues
    (document_id, job_id, field_name, issue_type, severity, description, suggestion)
    VALUES (?, NULL, NULL, 'process', 'warning', ?, ?)");

$desc='در دورِ ارتقاءِ ۱۵/۱۱، امتیازِ این کارت توسطِ داورِ AI از 86–89 به 92–95 رسید، بدونِ هیچِ تغییرِ واقعیِ محتوایی (۲۰ فیلدِ داسییر بایت‌به‌بایت یکسان ماند). یعنی داورِ AI روی این کارتِ خاص ناپایاست و امتیازِ 92–95 را نمی‌توان معتبر شمرد. امتیازِ معتبرِ فعلی: '.
  '%d. برای رسیدنِ صادقانه به 90+ یا اصلاح، نیاز به بازبینیِ انسانیِ دقیق (نه دورِ خودکار) دارد. نوعِ مشکلِ باقیمانده (بر اساسِ گزارش‌هایِ AI): ';
$sug='ارزیابیِ انسانی: 1) مشکلاتِ باقیمانده را در UI ببینید و مشخص کنید کدام‌ها با محتوایِ موجودِ کارت قابلِ حل‌اند (در همان فیلد ویرایش کنید) و کدام‌ها نیاز به داده‌ی منبعِ جدید دارند. 2) اگر تصمیم به ارتقاء گرفتید، ویرایشِ دستی + ثبتِ نسخه انجام شود؛ سیستمِ خودکار را برای این کارت فعال نکنید.';

$descTpl=sprintf($desc, 0); // placeholder for score
$count=0;
foreach($docs as $d){
    // جمعِ نوعِ مشکلاتِ باقیمانده
    $mix=$db->query("SELECT issue_type, COUNT(*) c FROM editorial_issues WHERE document_id={$d['id']} GROUP BY issue_type ORDER BY c DESC")->fetchAll();
    $mixStr='(هیچ)';
    if(count($mix)){
        $parts=array();
        foreach($mix as $m) $parts[]=$m['issue_type'].'×'.$m['c'];
        $mixStr=implode('، ',$parts);
    }
    $fullDesc=sprintf($desc,$d['s']).$mixStr;
    $ins->execute(array($d['id'],$fullDesc,$sug));
    $count++;
    echo "registered #{$d['id']} (score {$d['s']}, mix: $mixStr)\n";
}
echo "\nTOTAL registered: $count process issues\n";
echo "check: SELECT COUNT(*) FROM editorial_issues WHERE issue_type='process' AND is_resolved=0;\n";
