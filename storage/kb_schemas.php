<?php
$p = new PDO("mysql:host=127.0.0.1;port=3306;dbname=resaname_editorial;charset=utf8mb4", "resaname_editorial_user", "790a141d76426c44657744741607b1c2c", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
foreach (['kb_entities', 'kb_entity_types', 'kb_relations'] as $t) {
    echo "== $t ==\n";
    $r = $p->query("SHOW COLUMNS FROM `$t`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($r as $c) echo $c['Field'], "  ", $c['Type'], " ", ($c['Null'] === 'NO' ? 'NOT NULL' : 'NULL'), "\n";
}
echo "== sample SCHOOL rows ==\n";
$r = $p->query("SELECT code, abstract IS NOT NULL AND abstract<>'' AS has_abs, properties IS NOT NULL AND properties<>'' AS has_props, LENGTH(description) AS dlen FROM kb_entities WHERE entity_type='SCHOOL'")->fetchAll(PDO::FETCH_ASSOC);
print_r($r);
