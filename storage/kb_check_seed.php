<?php
$c = file_get_contents(__DIR__ . '/../app/Knowledge/Seed/Schools.php');
$l = explode("\n", $c);
$l25 = $l[24];
echo "Len: " . strlen($l25) . "\n";
for ($i = 0; $i < min(120, strlen($l25)); $i++) printf("%02x ", ord($l25[$i]));
echo "\n";
// Also check if the exact string I want to replace exists
$needle = "'main_theses' => ['رسانه ابزار ایدئولوژیک حاکمانه', 'فرهنگ صنعتی مصرف‌کننده را انفعالی می‌کند', 'آزادی از طریق نقد و رهایی‌بخشی عقل']]],";
echo "Needle found: " . (str_contains($c, $needle) ? "YES" : "NO") . "\n";
$needle2 = "آزادی از طریق نقد و رهایی‌بخشی عقل']]],";
echo "Needle2 found: " . (str_contains($c, $needle2) ? "YES" : "NO") . "\n";
