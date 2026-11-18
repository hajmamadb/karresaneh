<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$db = new PDO(
    "mysql:host=" . ($env['EDITORIAL_DB_HOST'] ?? '127.0.0.1') . ";port=" . ($env['EDITORIAL_DB_PORT'] ?? 3306) . ";dbname=" . ($env['EDITORIAL_DB_NAME'] ?? 'resaname_editorial') . ";charset=utf8mb4",
    $env['EDITORIAL_DB_USER'], $env['EDITORIAL_DB_PASS'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
foreach (['analysis-content', 'analysis-discourse', 'analysis-sna'] as $c) {
    $s = $db->prepare("SELECT code, LENGTH(description) dlen, (properties IS NOT NULL AND properties <> '') p, (abstract IS NOT NULL AND abstract <> '') a FROM kb_entities WHERE code=?");
    $s->execute([$c]);
    $r = $s->fetch(PDO::FETCH_ASSOC);
    echo $r['code'] . " desc_len=" . $r['dlen'] . " props=" . ($r['p'] ? 'yes' : 'no') . " abstract=" . ($r['a'] ? 'yes' : 'no') . PHP_EOL;
}
