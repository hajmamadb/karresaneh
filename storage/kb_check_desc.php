<?php
$p = new PDO("mysql:host=127.0.0.1;dbname=resaname_editorial;charset=utf8mb4", "resaname_editorial_user", "790a141d76426c44657744741607b1c2c");
$p->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$r = $p->query("SELECT COUNT(*) FROM kb_entities WHERE description IS NOT NULL AND description <> ''");
echo "entities with description: " . $r->fetchColumn() . "\n";
$r = $p->query("SELECT COUNT(*) FROM kb_entities WHERE entity_type='SCHOOL' AND description IS NOT NULL AND description <> ''");
echo "SCHOOL with description: " . $r->fetchColumn() . "\n";
$r = $p->query("SELECT code, CHAR_LENGTH(description) AS len FROM kb_entities WHERE entity_type='SCHOOL' AND description IS NOT NULL AND description <> '' ORDER BY code");
echo "\nAll SCHOOL descriptions (code, char_len):\n";
foreach ($r as $row) echo "  {$row['code']}: {$row['len']}\n";
