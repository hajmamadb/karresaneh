<?php
$f = __DIR__ . '/../app/Knowledge/Seed/Schools.php';
$c = file_get_contents($f);
echo "Has ZWNJ: " . (str_contains($c, "\u{200c}") ? "YES" : "NO") . "\n";
$l = explode("\n", $c);
$l24 = $l[23]; $l25 = $l[24];
echo "L24 last 10 hex: ";
for ($i = max(0, strlen($l24) - 10); $i < strlen($l24); $i++) printf("%02x ", ord($l24[$i]));
echo "\nL25 first 30 hex: ";
for ($i = 0; $i < 30; $i++) printf("%02x ", ord($l25[$i]));
echo "\n";
// Build the exact old string from the file and replace
$old = $l[24] . "\n";
$new = "          'founding_period' => '۱۹۲۳ (مؤسسه‌ی پژوهش اجتماعی، فرانکفورت)',\n"
     . "          'key_figures' => ['ماکس هورکهایمر', 'تئوادور آدورنو', 'یورگن هابرماس', 'هربرت ماکس', 'والتر بنیامین'],\n"
     . "          'main_theses' => ['رسانه ابزار ایدئولوژیک حاکمانه است', 'فرهنگ صنعتی مصرف‌کننده را انفعالی می‌کند', 'آزادی از طریق نقد و رهایی‌بخشی عقل به دست می‌آید', 'نظریه باید نه فقط جهان را تبیین، بلکه آن را بدل کند'],\n"
     . "          'methodologies' => ['تحلیل ایدئولوژیک متن‌های رسانه‌ای', 'روش‌های ترکیبی نظری-تجربی (نقد+آمار)', 'تاریخ‌نگاری انتقادی رسانه', 'تحلیل گفتمان سیاسی'],\n"
     . "          'influenced_fields' => ['مطالعات رسانه انتقادی', 'اقتصاد سیاسی رسانه', 'مطالعات فرهنگی', 'علوم اجتماعی', 'فلسفه معاصر'],\n"
     . "          'notable_works' => ['روشن‌افروختگی: کهن‌فلسفه به‌مثابه‌ی ایدئولوژی (هورکهایمر و آدورنو، ۱۹۴۷)', 'مینیمای مورالیایا (آدورنو، ۱۹۵۱)', 'انسان یک‌بعدی (ماکس، ۱۹۶۴)', 'کنش ارتباطی (هابرماس، ۱۹۸۱)'],\n"
     . "          'criticisms' => ['نقد «صنعت فرهنگ» مخاطب را انفعالی می‌پندارد و عاملیت مخاطب را نادیده می‌گیرد (نقد مطالعات فرهنگی)', 'گرایش‌های جبرگرا و کلان‌گرا در تحلیل اثر رسانه', 'فاصله‌ی نظریه‌ای از داده‌های تجربی کمّی']]],\n";
// Replace line 25 (index 24) with the new multi-line block
$c_new = substr_replace($c, $new, strpos($c, $old), strlen($old));
file_put_contents($f, $c_new);
echo "Frankfurt seed updated.\n";
// Verify
$c2 = file_get_contents($f);
echo "founding_period in file: " . (str_contains($c2, 'founding_period') ? "YES" : "NO") . "\n";
