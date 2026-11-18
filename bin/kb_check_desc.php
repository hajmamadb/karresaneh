<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$db = new PDO(
    "mysql:host=" . $env['EDITORIAL_DB_HOST'] . ";port=" . $env['EDITORIAL_DB_PORT'] . ";dbname=" . $env['EDITORIAL_DB_NAME'] . ";charset=utf8mb4",
    $env['EDITORIAL_DB_USER'], $env['EDITORIAL_DB_PASS'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);
$stmt = $db->query("SELECT code, CHAR_LENGTH(description) as desc_chars, LENGTH(description) as desc_bytes, LENGTH(properties) as props_bytes FROM kb_entities WHERE code IN ('subfield-ai-media-studies','subfield-digital-media-studies','subfield-platform-studies')");
foreach ($stmt as $row) {
    echo $row['code'] . ': desc=' . $row['desc_chars'] . " chars (" . $row['desc_bytes'] . " bytes), props=" . $row['props_bytes'] . " bytes\n";
}
