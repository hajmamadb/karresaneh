<?php
$p = new PDO("mysql:host=127.0.0.1;dbname=resaname_editorial;charset=utf8mb4", "resaname_editorial_user", "790a141d76426c44657744741607b1c2c");
$p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$codes = ["school-frankfurt", "school-chicago", "school-palo-alto"];
$stmt = $p->prepare("SELECT code, title_fa, description, properties FROM kb_entities WHERE code = ?");
foreach ($codes as $c) {
    $stmt->execute([$c]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) { echo "$c : NOT FOUND\n"; continue; }
    $desc = (string)$row['description'];
    $props = json_decode((string)$row['properties'], true);
    echo "=== $c ({$row['title_fa']}) ===\n";
    echo "  desc_len: " . strlen($desc) . "\n";
    echo "  desc: " . $desc . "\n";
    echo "  props_keys: " . json_encode(array_keys($props ?? []), JSON_UNESCAPED_UNICODE) . "\n";
    foreach (["founding_period","key_figures","main_theses","methodologies","influenced_fields","notable_works","criticisms"] as $k) {
        echo "    $k: " . (isset($props[$k]) ? "OK" : "MISSING") . "\n";
    }
}
echo "DONE\n";
