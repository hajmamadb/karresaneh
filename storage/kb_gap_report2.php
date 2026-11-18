<?php
$p = new PDO("mysql:host=127.0.0.1;port=3306;dbname=resaname_editorial;charset=utf8mb4", "resaname_editorial_user", "790a141d76426c44657744741607b1c2c", [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]);
$rows = $p->query("SELECT entity_type, code, title_fa, (abstract IS NOT NULL AND abstract<>'') AS has_abs, (properties IS NOT NULL AND JSON_LENGTH(properties) > 0) AS has_props, (description IS NOT NULL AND description<>'') AS has_desc FROM kb_entities WHERE status='active'")->fetchAll();
$byType = [];
foreach ($rows as $r) {
    $t = $r['entity_type'];
    if (!isset($byType[$t])) $byType[$t] = ['abs' => 0, 'props' => 0, 'desc' => 0, 'total' => 0, 'missing' => []];
    $byType[$t]['total']++;
    if ($r['has_abs']) $byType[$t]['abs']++;
    if ($r['has_props']) $byType[$t]['props']++;
    if ($r['has_desc']) $byType[$t]['desc']++;
    if (!$r['has_abs'] || !$r['has_props']) $byType[$t]['missing'][] = $r['code'] . (empty($r['has_abs']) ? '(no-abs)' : '') . (empty($r['has_props']) ? '(no-props)' : '');
}
$sumAbs = 0; $sumProps = 0; $sumDesc = 0; $sumTot = 0;
foreach ($byType as $t => $b) {
    $ap = round(100 * $b['abs'] / $b['total']); $pp = round(100 * $b['props'] / $b['total']); $dp = round(100 * $b['desc'] / $b['total']);
    $sumAbs += $b['abs']; $sumProps += $b['props']; $sumDesc += $b['desc']; $sumTot += $b['total'];
    $miss = implode(', ', array_slice($b['missing'], 0, 8)) . (count($b['missing']) > 8 ? ' ...(' . count($b['missing']) . ')' : '');
    printf("%-12s %4d%% %4d%% %4d%% %5d  %s\n", $t, $ap, $pp, $dp, $b['total'], $miss);
}
printf("%-12s %4d%% %4d%% %4d%% %5d\n", 'ALL', round(100*$sumAbs/$sumTot), round(100*$sumProps/$sumTot), round(100*$sumDesc/$sumTot), $sumTot);
