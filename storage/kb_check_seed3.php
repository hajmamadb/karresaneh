<?php
$f = __DIR__ . '/../app/Knowledge/Seed/Schools.php';
$c = file_get_contents($f);
$l = explode("\n", $c);

// Find the Chicago main_theses line
$chicagoLine = -1; $paloLine = -1;
foreach ($l as $i => $line) {
    if (str_contains($line, "شهر کارگاه آزمایش جامعه‌شناسی است") && str_contains($line, "'main_theses'")) $chicagoLine = $i;
    if (str_contains($line, "مهم فقط چه گفتی، چگونه گفتی است") && str_contains($line, "'main_theses'")) $paloLine = $i;
}
echo "Chicago main_theses line: $chicagoLine\n";
echo "Palo main_theses line: $paloLine\n";

function replaceLine(array &$l, int $idx, string $newBlock): void {
    $l[$idx] = rtrim($newBlock, "\n");
}

// Chicago new block (replace main_theses line, keep key_figures above it — we replace the main_theses line with expanded block ending in ]]
$chicagoNew = "          'founding_period' => '۱۸۹۲ (بخش جامعه‌شناسی دانشگاه شیکاگو)',\n"
     . "          'main_theses' => ['جامعه در تعامل نمادین ساخته می‌شود', 'شهر کارگاه آزمایش جامعه‌شناسی است', 'پژوهش میدانی و مشاهده معیار معرفت است', 'عمل اجتماعی محصول تفسیر نمادهاست'],\n"
     . "          'methodologies' => ['مشاهده‌ی مشارکتی و اثنوگرافی شهری', 'مطالعه‌ی موردی (case study) محله‌ها', 'پژوهش میدانی کیفی و پرونده‌های زندگی (biography)', 'تحلیل اسناد و آرشیوهای محلی'],\n"
     . "          'influenced_fields' => ['جامعه‌شناسی شهری', 'مطالعات مهاجرت', 'روان‌شناسی اجتماعی', 'پژوهش‌های کیفی', 'مطالعات رسانه‌ی محلی'],\n"
     . "          'notable_works' => ['شهر (پارک، بورگز و مک‌کنزی، ۱۹۲۵)', 'معرفت‌شناسی (مید، ۱۹۳۴)', 'تعامل‌گرایی نمادین (بلومر، ۱۹۶۹)'],\n"
     . "          'criticisms' => ['تأکید بر تجربه‌ی فردی و بی‌توجهی به ساختارهای کلان قدرت', 'مشکلات تعمیم‌پذیری مطالعه‌های موردی', 'گرایش ناسیونالیسم آمریکایی در برخی پژوهش‌های مهاجرتی']]";

// But key_figures for Chicago should also add Nels Anderson & Louis Wirth. Find that line.
$chicagoKeyLine = -1;
foreach ($l as $i => $line) {
    if (str_contains($line, "روبرت پارک", ) && str_contains($line, "'key_figures'") && $i > $chicagoLine - 3 && $i < $chicagoLine + 1) $chicagoKeyLine = $i;
}
// Simpler: find key_figures line just above chicago main_theses
for ($i = $chicagoLine; $i >= max(0, $chicagoLine - 5); $i--) {
    if (str_contains($l[$i], "'key_figures'") && str_contains($l[$i], 'پارک')) { $chicagoKeyLine = $i; break; }
}
echo "Chicago key_figures line: $chicagoKeyLine\n";
$chicagoKeyNew = "          'key_figures' => ['روبرت پارک', 'ارنست بورگز', 'جورج هربرت مید', 'هربرت بلومر', 'نلس اندرسون', 'لوئیس ورث'],";

$chicagoFull = $chicagoKeyNew . "\n" . $chicagoNew . ",";

// Palo Alto
$paloNew = "          'founding_period' => '۱۹۵۰ (کلینیک بیتسن، پالو آلتو)',\n"
     . "          'main_theses' => ['ارتباط هم‌زمان کنش و رابطه است (دوشاخصی)', 'مهم فقط چه گفتی، چگونه گفتی است', 'ارتباط سیستمی و توالی‌دار است، نه فرستار-گزارنده', 'بازنهاده‌ی دوگانه هسته‌ی اختلالات ارتباطی است'],\n"
     . "          'methodologies' => ['تحلیل الگوهای توالی‌دار تعامل', 'تحلیل فراارتباط و سیگنال‌های رابطه‌ای', 'تحلیل سایبرناتیک فرآیندهای ارتباطی', 'تحلیل بالینی خانواده (تصویربرداری و پیوستگی جلسه)'],\n"
     . "          'influenced_fields' => ['درمان خانواده', 'مطالعات بین‌فردی', 'سایبرنتیک', 'مطالعات سازمانی', 'مطالعات هوش مصنوعی و ارتباط ماشینی'],\n"
     . "          'notable_works' => ['پراگماتیک ارتباطات انسانی (واتسلاوئیک و همکاران، ۱۹۶۷)', 'گام‌های فراتر از ذهن (بیتسن، ۱۹۷۲)'],\n"
     . "          'criticisms' => ['جبرگرایی الگویی در تحلیل تعامل (بی‌توجهی به عاملیت فردی)', 'گفتاری‌محوری بیش از حد در هزینه‌ی محتوا', 'چالش تعمیم‌پذیری یافته‌های بالینی به جوامع غیرغربی']]";
$paloKeyLine = -1;
for ($i = $paloLine; $i >= max(0, $paloLine - 5); $i--) {
    if (str_contains($l[$i], "'key_figures'") && str_contains($l[$i], 'واتسلاوئیک')) { $paloKeyLine = $i; break; }
}
echo "Palo key_figures line: $paloKeyLine\n";
$paloKeyNew = "          'key_figures' => ['گرگوری بیتسن', 'پل واتسلاوئیک', 'جین ولک‌لند', 'دونالد هاجینز'],";
$paloFull = $paloKeyNew . "\n" . $paloNew . ",";

// Rebuild: replace from key_line to main_theses line (inclusive) for each
// Chicago: replace lines [chicagoKeyLine..chicagoLine] with chicagoFull
// Palo: replace lines [paloKeyLine..paloLine] with paloFull
// Do from bottom to top to keep indices valid
if ($paloKeyLine < $chicagoKeyLine) { $order = [['palo', $paloKeyLine, $paloLine, $paloFull], ['chi', $chicagoKeyLine, $chicagoLine, $chicagoFull]]; }
else { $order = [['chi', $chicagoKeyLine, $chicagoLine, $chicagoFull], ['palo', $paloKeyLine, $paloLine, $paloFull]]; }
// Ensure descending order by start index
usort($order, fn($a, $b) => $b[1] <=> $a[1]);
foreach ($order as [$tag, $start, $end, $block]) {
    $slice = implode("\n", array_slice($l, $start, $end - $start + 1));
    echo "Replacing $tag lines $start..$end (len " . strlen($slice) . ")\n";
    $l = array_merge(array_slice($l, 0, $start), [ $block ], array_slice($l, $end + 1));
}
$c2 = implode("\n", $l);
file_put_contents($f, $c2);
echo "Done. Chicago founding in file: " . (str_contains($c2, "۱۸۹۲ (بخش جامعه‌شناسی") ? "YES" : "NO") . "\n";
echo "Palo founding in file: " . (str_contains($c2, "۱۹۵۰ (کلینیک بیتسن") ? "YES" : "NO") . "\n";
