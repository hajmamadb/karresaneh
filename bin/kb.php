<?php
require dirname(__DIR__) . '/bootstrap.php';
require dirname(__DIR__) . '/app/Knowledge/EntityTypes.php';
require dirname(__DIR__) . '/app/Knowledge/RelationTypes.php';
require dirname(__DIR__) . '/app/Knowledge/FacetTypes.php';
require dirname(__DIR__) . '/app/Knowledge/KnowledgeBase.php';

if (PHP_SAPI !== 'cli') exit("CLI only\n");

/**
 * هسته دانش علمی — خط فرمان
 *
 * usage:
 *   php bin/kb.php install                     ساخت جداول kb_* + بارگذاری ثبت‌نام‌سازها
 *   php bin/kb.php seed                        بارگذاری کامل طبقه‌بندی (۵۸۱ موجودیت + ۳۷۵ رابطه)
 *   php bin/kb.php status                      آمار هسته دانش
 *   php bin/kb.php validate                    گزارش یکپارچگی
 *   php bin/kb.php list [TYPE] [--limit=N]     فهرست موجودیت‌ها
 *   php bin/kb.php show <code>                 پرونده‌ی کامل یک موجودیت
 *   php bin/kb.php search <query> [--type=TYPE] [--limit=N]
 *   php bin/kb.php graph <code> [--depth=1] [--rel=TYPE] [--dir=both|out|in]
 *   php bin/kb.php add --code= --fa= --en= --type= [--parent=] [--abstract=] [--json='{}']
 *   php bin/kb.php relate --source= --target= --type= [--note=] [--weight=50] [--confidence=suggested]
 *   php bin/kb.php facet --code= --facet= --value= [--value-en=]
 *   php bin/kb.php link --code= --type=card|theorist|library_resource|category --id= [--title=] [--confidence=suggested]
 *   php bin/kb.php link-main [--apply]         تطبیق با دیتابیس اصلی
 *   php bin/kb.php menu [بخش]                   منوی ده‌گانه‌ی دانشنامه
 *   php bin/kb.php export [--file=kb.json]     خروجی JSON کامل
 */

use Resaname\Knowledge\KnowledgeBase as KB;
use Resaname\Knowledge\EntityTypes;
use Resaname\Knowledge\RelationTypes;
use Resaname\Knowledge\FacetTypes;

$args  = array_slice($argv, 1);
$cmd   = $args[0] ?? 'help';
$rest  = array_slice($args, 1);
$opts  = [];
$pos   = [];
foreach ($rest as $a) {
    if (str_starts_with($a, '--')) {
        [$k, $v] = array_pad(explode('=', substr($a, 2), 2), 2, true);
        $opts[$k] = $v;
    } else {
        $pos[] = $a;
    }
}

function out(string $s): void { echo $s, "\n"; }
function die_err(string $s): void { fwrite(STDERR, "خطا: $s\n"); exit(1); }

$ed = \Resaname\Db::editorial();

switch ($cmd) {

    case 'install':
        KB::install($ed);
        KB::seedRegistries($ed);
        out('جداول هسته دانش ساخته/به‌روز شدند. ثبت‌نام‌سازها: ' .
            count(EntityTypes::all()) . ' نوع موجودیت، ' .
            count(RelationTypes::all()) . ' نوع رابطه، ' .
            count(FacetTypes::all()) . ' بعد فاکت.');
        break;

    case 'seed':
        $dir  = dirname(__DIR__) . '/app/Knowledge/Seed/';
        $ent  = [];
        foreach (['Schools', 'Paradigms', 'Designs', 'Collection', 'Analysis', 'ToolsQualityEthics', 'Theories', 'Persons'] as $f) {
            $ent = array_merge($ent, require $dir . $f . '.php');
        }
        $rels = require $dir . 'Relations.php';
        $facs = [];
        foreach (glob($dir . 'Facets_*.php') ?: [] as $ff) {
            $facs = array_merge($facs, require $ff);
        }
        $stats = KB::seedContent($ed, $ent, $rels, $facs);
        out("seed شد: موجودیت={$stats['entities']} رابطه={$stats['relations']} فاکت={$stats['facets']}");
        break;

    case 'status':
        $s = KB::status($ed);
        out("موجودیت‌های فعال: {$s['entities']}");
        foreach ($s['entities_by_type'] as $r) out(sprintf("   %-4s %-28s %d", $r['entity_type'], $r['label_fa'], $r['c']));
        out('روابط فعال: ' . $s['relations'] . '   |   فاکت‌ها: ' . $s['facets'] . '   |   پیوندهای درون‌سایتی: ' . $s['links']);
        foreach ($s['relations_by_confidence'] as $r) out(sprintf("   اطمینان %-10s %d", $r['confidence'], $r['c']));
        out('موجودیت‌ها بدون چکیده (منتظر ویراستار هوشمند): ' . $s['pending_content']);
        break;

    case 'validate':
        $issues = KB::validate($ed);
        if (!$issues) out('یکپارچگی هسته دانش سالم است. ✔');
        foreach ($issues as $i) out('- ' . $i);
        break;

    case 'list':
        $type  = strtoupper((string)($pos[0] ?? ($opts['type'] ?? '')));
        $limit = (int)($opts['limit'] ?? 100);
        $sql  = "SELECT code,title_fa,title_en,entity_type,parent_code FROM kb_entities WHERE status='active'";
        $par  = [];
        if ($type) { $sql .= " AND entity_type=?"; $par[] = $type; }
        $sql .= " ORDER BY entity_type,id LIMIT " . max(1, min(2000, $limit));
        $rows = $ed->prepare($sql);
        $rows->execute($par);
        $all = $rows->fetchAll();
        foreach ($all as $r) {
            out(sprintf("%-38s %-6s %-30s %s%s", $r['code'], $r['entity_type'], mb_substr($r['title_fa'], 0, 40), $r['title_en'] ?? '', $r['parent_code'] ? "  (پدر: {$r['parent_code']})" : ''));
        }
        out('--- ' . count($all) . ' رکورد');
        break;

    case 'show':
        $e = KB::get($ed, (string)($pos[0] ?? ''));
        if (!$e) die_err('موجودیت پیدا نشد: ' . ($pos[0] ?? ''));
        out(json_encode($e, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        break;

    case 'search':
        $rows = KB::search($ed, (string)($pos[0] ?? ''), $opts['type'] ?? null, (int)($opts['limit'] ?? 20));
        if (!$rows) out('نتیجه‌ای پیدا نشد.');
        foreach ($rows as $r) {
            out(sprintf("[score=%d] %-36s %-6s %s %s", $r['score'], $r['code'], $r['entity_type'], $r['title_fa'], $r['title_en'] ?? ''));
        }
        break;

    case 'graph':
        $n = KB::neighbors($ed, (string)($pos[0] ?? ''), (int)($opts['depth'] ?? 1), $opts['rel'] ?? null, (string)($opts['dir'] ?? 'both'));
        foreach ($n as $r) {
            out(sprintf("%-36s ←[%s]→ %s (%s) [دور=%d, %s]", $pos[0], $r['relation_type'], $r['other'], $r['other_title'], $r['other_type'], $r['confidence']));
        }
        out('--- ' . count($n) . ' همسایه');
        break;

    case 'add':
        if (isset($opts['json'])) {
            $e = json_decode((string)$opts['json'], true);
            if (!is_array($e)) die_err('JSON نامعتبر');
        } else {
            $e = array_filter([
                'code' => $opts['code'] ?? null,
                'title_fa' => $opts['fa'] ?? null,
                'title_en' => $opts['en'] ?? null,
                'entity_type' => $opts['type'] ?? null,
                'parent_code' => $opts['parent'] ?? null,
                'abstract' => $opts['abstract'] ?? null,
            ], fn($v) => $v !== null);
        }
        try {
            out('ذخیره شد: ' . KB::addEntity($ed, $e, 'cli'));
        } catch (\InvalidArgumentException $x) { die_err($x->getMessage()); }
        break;

    case 'relate':
        try {
            $r = KB::addRelation($ed, [
                'source' => $opts['source'] ?? '',
                'target' => $opts['target'] ?? '',
                'relation_type' => $opts['type'] ?? '',
                'note' => $opts['note'] ?? null,
                'weight' => $opts['weight'] ?? 50,
                'confidence' => $opts['confidence'] ?? 'suggested',
            ], 'cli');
            out("رابطه ثبت شد: {$r['source']} → [{$r['relation_type']}] → {$r['target']}");
        } catch (\InvalidArgumentException $x) { die_err($x->getMessage()); }
        break;

    case 'facet':
        try {
            KB::addFacet($ed, (string)($opts['code'] ?? ''), (string)($opts['facet'] ?? ''), (string)($opts['value'] ?? ''), $opts['value-en'] ?? null, 'cli');
            out('فاکت ثبت شد.');
        } catch (\InvalidArgumentException $x) { die_err($x->getMessage()); }
        break;

    case 'link':
        try {
            KB::linkMain($ed, (string)($opts['code'] ?? ''), (string)($opts['type'] ?? ''), (string)($opts['id'] ?? ''), $opts['title'] ?? null, (string)($opts['confidence'] ?? 'suggested'), 'cli');
            out('پیوند ثبت شد.');
        } catch (\InvalidArgumentException $x) { die_err($x->getMessage()); }
        break;

    case 'link-main':
        $main = \Resaname\Db::main();
        $m = KB::matchMain($main, $ed, !empty($opts['apply']));
        $verified = count(array_filter($m, fn($x) => $x['confidence'] === 'verified'));
        out("تطبیق‌ها: " . count($m) . " (دقیق: {$verified}، پیشنهادی: " . (count($m) - $verified) . ')');
        foreach (array_slice($m, 0, 30) as $x) {
            out(sprintf("  %-36s ~ %-12s %s (%s) [%s]", $x['entity_code'], $x['link_type'], $x['link_title'], $x['link_id'], $x['confidence']));
        }
        if (count($m) > 30) out('  … و ' . (count($m) - 30) . ' مورد دیگر (برای ذخیره: php bin/kb.php link-main --apply)');
        if (!empty($opts['apply'])) out('همه‌ی تطبیق‌ها در kb_entity_links ذخیره شد.');
        break;

    case 'menu':
        out(KB::menu($ed, $pos[0] ?? null));
        break;

    case 'export':
        $json = KB::exportJson($ed);
        if (!empty($opts['file'])) { file_put_contents($opts['file'], $json); out('خروجی در ' . $opts['file'] . ' ذخیره شد.'); }
        else out($json);
        break;

    case 'help':
    default:
        $u = file_get_contents(__FILE__);
        if (preg_match('/usage:(.*)\*\//s', $u, $m)) {
            $hl = array_map(fn($l) => trim(ltrim(trim($l), '*')), explode("\n", $m[1]));
            $hl = array_values(array_filter($hl, fn($l) => $l !== ''));
            out(implode("\n", $hl));
        }
        out('');
        out('انواع موجودیت: ' . implode(', ', array_keys(EntityTypes::all())));
        out('انواع رابطه: ' . implode(', ', array_keys(RelationTypes::all())));
        out('ابعاد فاکت: ' . implode(', ', array_keys(FacetTypes::all())));
}
