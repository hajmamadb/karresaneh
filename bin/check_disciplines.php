<?php
$env = parse_ini_file(__DIR__ . '/../.env');
$host = $env['EDITORIAL_DB_HOST'] ?? '127.0.0.1';
$port = $env['EDITORIAL_DB_PORT'] ?? 3306;
$name = $env['EDITORIAL_DB_NAME'] ?? 'resaname_editorial';
$user = $env['EDITORIAL_DB_USER'] ?? 'root';
$pass = $env['EDITORIAL_DB_PASS'] ?? '';

$db = new PDO(
    "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
    $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$stmt = $db->prepare("SELECT code, title_fa, LENGTH(description) as desc_len, LENGTH(properties) as props_len, updated_at FROM kb_entities WHERE entity_type = 'DISCIPLINE' AND status = 'active' ORDER BY code");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($rows)) {
    echo "No rows found\n";
} else {
    foreach ($rows as $row) {
        echo sprintf(
            "%s | desc_len=%d | props_len=%d | updated=%s\n",
            $row['code'],
            $row['desc_len'] ?: 0,
            $row['props_len'] ?: 0,
            $row['updated_at']
        );
    }
}
echo "Total: " . count($rows) . "\n";
