<?php
$f = __DIR__ . '/../app/Knowledge/Seed/Schools.php';
$c = file_get_contents($f);
$l = explode("\n", $c);
$paloLine = -1;
foreach ($l as $i => $line) {
    if (str_contains($line, "مهم فقط چه گفتی، چگونه گفتی است") && str_contains($line, "'main_theses'")) { $paloLine = $i; break; }
}
echo "Palo main_theses line: $paloLine\n";
$paloKeyLine = -1;
for ($i = $paloLine; $i >= max(0, $paloLine - 5); $i--) {
    if (str_contains($l[$i], "'key_figures'") && str_contains($l[$i], 'واتسلاوئیک')) { $paloKeyLine = $i; break; }
}
echo "Palo key_figures line: $paloKeyLine\n";
$paloFull = "          'founding_period' => '۱۹۵۰ (کلینیک بیتسن، پالو آلتو)',\n"
     . "          'key_figures' => ['گرگوری بیتسن', 'پل واتسلاوئیک', 'جین ولک‌لند', 'دونالد هاجینز'],\n"
     . "          'main_theses' => ['ارتباط هم‌زمان کنش و رابطه است (دوشاخصی)', 'مهم فقط چه گفتی، چگونه گفتی است', 'ارتباط سیستمی و توالی‌دار است، نه فرستار-گزارنده', 'بازنهاده‌ی دوگانه هسته‌ی اختلالات ارتباطی است'],\n"
     . "          'methodologies' => ['تحلیل الگوهای توالی‌دار تعامل', 'تحلیل فراارتباط و سیگنال‌های رابطه‌ای', 'تحلیل سایبرناتیک فرآیندهای ارتباطی', 'تحلیل بالینی خانواده (تصویربرداری و پیوستگی جلسه)'],\n"
     . "          'influenced_fields' => ['درمان خانواده', 'مطالعات بین‌فردی', 'سایبرنتیک', 'مطالعات سازمانی', 'مطالعات هوش مصنوعی و ارتباط ماشینی'],\n"
     . "          'notable_works' => ['پراگماتیک ارتباطات انسانی (واتسلاوئیک و همکاران، ۱۹۶۷)', 'گام‌های فراتر از ذهن (بیتسن، ۱۹۷۲)'],\n"
     . "          'criticisms' => ['جبرگرایی الگویی در تحلیل تعامل (بی‌توجهی به عاملیت فردی)', 'گفتاری‌محوری بیش از حد در هزینه‌ی محتوا', 'چالش تعمیم‌پذیری یافته‌های بالینی به جوامع غیرغربی']]";
$l = array_merge(array_slice($l, 0, $paloKeyLine), [ $paloFull ], array_slice($l, $paloLine + 1));
$c2 = implode("\n", $l);
file_put_contents($f, $c2);
echo "Palo Alto seed updated. founding in file: " . (str_contains($c2, "۱۹۵۰ (کلینیک بیتسن") ? "YES" : "NO") . "\n";
// Verify PHP syntax
echo "PHP syntax check: ";
exec("php -l " . escapeshellarg($f) . " 2>&1", $out, $rc);
echo implode("\n", $out) . " (rc=$rc)\n";
