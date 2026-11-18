<?php
namespace Resaname\Knowledge;

/**
 * هسته دانش علمی رسانامه — سرویس اصلی (Knowledge Base Service)
 *
 * لایه‌ی واحدی برای دسترسی به جداول kb_* در دیتابیس واسط:
 *  - install():      ساخت/به‌روزرسانی جداول (idempotent)
 *  - seed():         بارگذاری اولیه‌ی طبقه‌بندی جامع + روابط + فاکت‌ها (idempotent)
 *  - addEntity() / addRelation() / addFacet() / linkMain(): ورود جدید (با ممیزی)
 *  - search() / get() / neighbors() / menu() / export(): موتور خواندن و ناوبری
 *  - validate():     اعتبارسنجی یکپارچگی شبکه دانش
 *  - matchMain():    تطبیق خودکار موجودیت‌ها با محتوای دیتابیس اصلی (cards/theorists/resources)
 *
 * قوانین معماری (مستند در docs/knowledge-core.md):
 *  1) هر مفهوم فقط یک موجودیت دارد (code یکتا)؛ تکرار نام ممنوع.
 *  2) درخت والد/فرز فقط برای ناوبری است؛ معنای علمی در kb_relations است.
 *  3) مرز روش/فن/ابزار/طرح با entity_type رعایت می‌شود.
 *  4) همه‌ی تغییرات در kb_audit_log ردپا دارند.
 */
final class KnowledgeBase {

    /** قالب سطر موجودیت seed: [code, title_fa, title_en, TYPE, parent_code, abstract?, properties?] */
    public const ROW_CODE = 0;
    public const ROW_FA   = 1;
    public const ROW_EN   = 2;
    public const ROW_TYPE = 3;
    public const ROW_PAR  = 4;

    // ------------------------------------------------------------------
    // نصب
    // ------------------------------------------------------------------

    /** ساخت جداول هسته دانش (idempotent) */
    public static function install(\PDO $db): void {
        $sql = file_get_contents(dirname(__DIR__, 2) . '/database/kb_schema.sql');
        foreach (array_filter(array_map('trim', preg_split('/;\s*(?:\r?\n|$)/', $sql))) as $statement) {
            $db->exec($statement);
        }
        self::log($db, null, 'install', ['tables' => 8], 'kb');
    }

    /** بارگذاری ثبت‌نام‌سازها (انواع موجودیت/رابطه/فاکت) از تک منبع حقیقت PHP */
    public static function seedRegistries(\PDO $db): void {
        $ins = $db->prepare("INSERT INTO kb_entity_types (code,label_fa,label_en,domain,description,sort_order)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE label_fa=VALUES(label_fa),label_en=VALUES(label_en),domain=VALUES(domain),description=VALUES(description)");
        $i = 0;
        foreach (EntityTypes::all() as $code => [$fa, $en, $domain, $desc]) {
            $ins->execute([$code, $fa, $en, $domain, $desc, $i++]);
        }
        $ins = $db->prepare("INSERT INTO kb_relation_types (code,label_fa,label_en,description,symmetric,sort_order)
            VALUES (?,?,?,?,?,?)
            ON DUPLICATE KEY UPDATE label_fa=VALUES(label_fa),label_en=VALUES(label_en),description=VALUES(description),symmetric=VALUES(symmetric)");
        $i = 0;
        foreach (RelationTypes::all() as $code => [$fa, $en, $desc, $sym]) {
            $ins->execute([$code, $fa, $en, $desc, $sym ? 1 : 0, $i++]);
        }
        $ins = $db->prepare("INSERT INTO kb_facet_dimensions (code,label_fa,label_en,description,sort_order)
            VALUES (?,?,?,?,?)
            ON DUPLICATE KEY UPDATE label_fa=VALUES(label_fa),label_en=VALUES(label_en),description=VALUES(description)");
        $i = 0;
        foreach (FacetTypes::all() as $code => [$fa, $en, $desc]) {
            $ins->execute([$code, $fa, $en, $desc, $i++]);
        }
        self::log($db, null, 'seed_registries', [
            'entity_types' => count(EntityTypes::all()),
            'relation_types' => count(RelationTypes::all()),
            'facet_dimensions' => count(FacetTypes::all()),
        ], 'kb');
    }

    // ------------------------------------------------------------------
    // seed کامل (طبقه‌بندی جامع)
    // ------------------------------------------------------------------

    /**
     * بارگذاری کامل هسته دانش: موجودیت‌ها + سلسله‌مراتب + روابط + فاکت‌ها.
     * کاملاً idempotent — قابل اجرای مکرر.
     *
     * @param array $entityRows  سطرو‌های موجودیت (قالب ROW_*)
     * @param array $relationRows سطرو‌های رابطه: [source, target, TYPE, note?, weight?, confidence?]
     * @param array $facetRows    سطرو‌های فاکت: [code, ['facet_code'=>['value_fa'[, 'value_en']], ...]]
     */
    public static function seedContent(\PDO $db, array $entityRows, array $relationRows, array $facetRows): array {
        $stats = ['entities' => 0, 'relations' => 0, 'facets' => 0];

        // ۱) موجودیت‌ها — اول والدین (بدون والد)، سپس فرزندان؛ دو پاسه برای اطمینان
        $byCode = [];
        foreach ($entityRows as $row) $byCode[$row[self::ROW_CODE]] = $row;
        $order = self::topoOrder($byCode);
        foreach ($order as $code) {
            $row = $byCode[$code];
            self::upsertEntity($db, $row, 'seed');
            $stats['entities']++;
        }

        // ۲) روابط
        $rel = $db->prepare("INSERT INTO kb_relations (source_code,target_code,relation_type,note,weight,confidence,created_by,created_at,updated_at)
            VALUES (?,?,?,?,?,?,?,NOW(),NOW())
            ON DUPLICATE KEY UPDATE note=IF(VALUES(note) IS NULL,note,VALUES(note)),
                weight=VALUES(weight),confidence=VALUES(confidence),updated_at=NOW()");
        foreach ($relationRows as $r) {
            if (!isset($byCode[$r[0]], $byCode[$r[1]])) continue; // رابطه‌ای که دو سرش موجود نیست
            $rel->execute([
                $r[0], $r[1], $r[2],
                $r[3] ?? null,
                $r[4] ?? 50,
                $r[5] ?? 'suggested',
                'seed',
            ]);
            $stats['relations']++;
        }

        // ۳) فاکت‌ها
        $fac = $db->prepare("INSERT INTO kb_entity_facets (entity_code,facet_code,value_fa,value_en,created_at)
            VALUES (?,?,?,?,NOW())
            ON DUPLICATE KEY UPDATE value_en=IF(VALUES(value_en) IS NULL,value_en,VALUES(value_en))");
        foreach ($facetRows as [$code, $facets]) {
            if (!isset($byCode[$code])) continue;
            foreach ($facets as $dim => $values) {
                if (!FacetTypes::exists((string)$dim)) continue;
                $n = 0;
                foreach ((array)$values as $v) {
                    if (is_array($v)) {
                        $fac->execute([$code, $dim, (string)$v[0], $v[1] ?? null]);
                    } else {
                        $fac->execute([$code, $dim, (string)$v, null]);
                    }
                    $stats['facets']++;
                    $n++;
                }
            }
        }

        self::log($db, null, 'seed_content', $stats, 'kb');
        return $stats;
    }

    /** مرتب‌سازی توپولوژیک: والد قبل از فرزند (دورزدن در صورت چرخه) */
    private static function topoOrder(array $byCode): array {
        $visited = []; $stack = [];
        $order = [];
        $walk = function (string $code) use ($byCode, &$visited, &$stack, &$order, &$walk) {
            if (isset($visited[$code]) || in_array($code, $stack, true)) return;
            $stack[] = $code;
            $parent = $byCode[$code][self::ROW_PAR] ?? null;
            if ($parent && isset($byCode[$parent])) $walk($parent);
            array_pop($stack);
            $visited[$code] = true;
            $order[] = $code;
        };
        foreach (array_keys($byCode) as $code) $walk($code);
        return $order;
    }

    /** درج/به‌روزرسانی یک موجودیت (قالب ROW_*) */
    public static function upsertEntity(\PDO $db, array $row, string $source = 'manual', string $status = 'active'): string {
        [$code, $fa, $en, $type, $parent] = $row;
        $abstract = $row[5] ?? null;
        $props = $row[6] ?? null;
        if (!EntityTypes::exists($type)) throw new \InvalidArgumentException("Unknown entity type: $type");
        $domain = EntityTypes::domainOf($type);
        $json = $props !== null ? json_encode($props, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;

        $q = $db->prepare("SELECT code FROM kb_entities WHERE code=?");
        $q->execute([$code]);
        $exists = (bool)$q->fetchColumn();
        $db->prepare("INSERT INTO kb_entities (code,slug,title_fa,title_en,entity_type,domain,parent_code,abstract,properties,sort_order,status,created_at,updated_at)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,NOW(),NOW())
            ON DUPLICATE KEY UPDATE
                title_fa=VALUES(title_fa),
                title_en=VALUES(title_en),
                entity_type=VALUES(entity_type),
                domain=VALUES(domain),
                parent_code=VALUES(parent_code),
                abstract=IF(VALUES(abstract) IS NULL,abstract,VALUES(abstract)),
                properties=IF(VALUES(properties) IS NULL,properties,VALUES(properties)),
                updated_at=NOW()")
            ->execute([$code, $code, $fa, $en, $type, $domain, $parent, $abstract, $json, 0, $status]);
        self::log($db, $code, $exists ? 'update' : 'create', ['type' => $type, 'title' => $fa], $source);
        return $code;
    }

    // ------------------------------------------------------------------
    // ورود دستی / MCP (با اعتبارسنجی)
    // ------------------------------------------------------------------

    /** ساخت یا به‌روزرسانی موجودیت؛ برگشت: code */
    public static function addEntity(\PDO $db, array $e, string $source = 'manual'): string {
        $code = self::slugifyCode((string)($e['code'] ?? ''));
        if ($code === '' || strlen($code) > 80) throw new \InvalidArgumentException('code نامعتبر است (1-80 کاراکتر، حروف کوچک لاتین و خط تیره)');
        $fa = trim((string)($e['title_fa'] ?? ''));
        if ($fa === '') throw new \InvalidArgumentException('title_fa الزامی است');
        $type = strtoupper(trim((string)($e['entity_type'] ?? '')));
        if (!EntityTypes::exists($type)) throw new \InvalidArgumentException('entity_type نامعتبر: ' . $e['entity_type']);

        $parent = null;
        if (!empty($e['parent_code'])) {
            $p = trim((string)$e['parent_code']);
            if (!self::entityExists($db, $p)) throw new \InvalidArgumentException('parent_code وجود ندارد: ' . $p);
            $parent = $p;
        }
        $row = [
            $code,
            $fa,
            trim((string)($e['title_en'] ?? '')) ?: null,
            $type,
            $parent,
            trim((string)($e['abstract'] ?? '')) ?: null,
            $e['properties'] ?? null,
        ];
        return self::upsertEntity($db, $row, $source, 'active');
    }

    /** افزودن رابطه (اعتبارسنجی کد رابطه + وجود دو سر) */
    public static function addRelation(\PDO $db, array $r, string $source = 'manual'): array {
        $s = trim((string)($r['source'] ?? ''));
        $t = trim((string)($r['target'] ?? ''));
        $type = strtoupper(trim((string)($r['relation_type'] ?? '')));
        if ($s === '' || $t === '') throw new \InvalidArgumentException('source و target الزامی است');
        if (!RelationTypes::exists($type)) throw new \InvalidArgumentException('relation_type نامعتبر: ' . $r['relation_type']);
        if (!$db->query("SELECT COUNT(*) FROM kb_entities WHERE code='" . self::q($s) . "'")->fetchColumn()) throw new \InvalidArgumentException('source وجود ندارد: ' . $s);
        if (!$db->query("SELECT COUNT(*) FROM kb_entities WHERE code='" . self::q($t) . "'")->fetchColumn()) throw new \InvalidArgumentException('target وجود ندارد: ' . $t);
        $db->prepare("INSERT INTO kb_relations (source_code,target_code,relation_type,note,weight,confidence,status,created_by,created_at,updated_at)
            VALUES (?,?,?,?,?,?, 'active', ?,NOW(),NOW())
            ON DUPLICATE KEY UPDATE note=IF(VALUES(note) IS NULL,note,VALUES(note)),weight=VALUES(weight),confidence=VALUES(confidence),status='active',updated_at=NOW()")
            ->execute([
                $s, $t, $type,
                trim((string)($r['note'] ?? '')) ?: null,
                max(1, min(100, (int)($r['weight'] ?? 50))),
                in_array($r['confidence'] ?? '', ['verified', 'suggested', 'ai_draft'], true) ? $r['confidence'] : 'suggested',
                $source,
            ]);
        self::log($db, $s, 'relate', ['target' => $t, 'type' => $type], $source);
        return ['source' => $s, 'target' => $t, 'relation_type' => $type];
    }

    /** افزودن فاکت */
    public static function addFacet(\PDO $db, string $code, string $facet, string $valueFa, ?string $valueEn = null, string $source = 'manual'): void {
        if (!self::entityExists($db, $code)) throw new \InvalidArgumentException('entity وجود ندارد: ' . $code);
        if (!FacetTypes::exists($facet)) throw new \InvalidArgumentException('facet نامعتبر: ' . $facet);
        $db->prepare("INSERT INTO kb_entity_facets (entity_code,facet_code,value_fa,value_en,created_at)
            VALUES (?,?,?,?,NOW())
            ON DUPLICATE KEY UPDATE value_en=IF(VALUES(value_en) IS NULL,value_en,VALUES(value_en))")
            ->execute([$code, $facet, trim($valueFa), $valueEn]);
        self::log($db, $code, 'facet', ['facet' => $facet, 'value' => $valueFa], $source);
    }

    /** پیوند یک موجودیت به محتوای دیتابیس اصلی */
    public static function linkMain(\PDO $db, string $code, string $linkType, string $linkId, ?string $title = null, string $confidence = 'suggested', string $source = 'manual'): void {
        if (!self::entityExists($db, $code)) throw new \InvalidArgumentException('entity وجود ندارد: ' . $code);
        if (!in_array($linkType, ['card', 'theorist', 'library_resource', 'category'], true)) throw new \InvalidArgumentException('link_type نامعتبر');
        $db->prepare("INSERT INTO kb_entity_links (entity_code,link_type,link_id,link_title,confidence,created_by,created_at)
            VALUES (?,?,?,?,?,?,NOW())
            ON DUPLICATE KEY UPDATE link_title=IF(VALUES(link_title) IS NULL,link_title,VALUES(link_title)),confidence=VALUES(confidence),created_by=VALUES(created_by)")
            ->execute([$code, $linkType, $linkId, $title, $confidence, $source]);
        self::log($db, $code, 'link', ['type' => $linkType, 'id' => $linkId], $source);
    }

    public static function entityExists(\PDO $db, string $code): bool {
        $q = $db->prepare("SELECT 1 FROM kb_entities WHERE code=?");
        $q->execute([$code]);
        return (bool)$q->fetchColumn();
    }

    // ------------------------------------------------------------------
    // خواندن، جست‌وجو و ناوبری
    // ------------------------------------------------------------------

    /** جست‌وجوی ساده (LIKE روی عنوان/slug/abstract) */
    public static function search(\PDO $db, string $q, ?string $type = null, int $limit = 20): array {
        $q = trim($q);
        if ($q === '') return [];
        // فرار از کاراکترهای خاصِ LIKE (کلمه‌ی فارسی/انگلیسی به‌طور کامل سالم می‌ماند)
        $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
        $sql = "SELECT e.code,e.title_fa,e.title_en,e.entity_type,e.domain,e.parent_code,
                et.label_fa AS type_label,
                (CASE WHEN e.title_fa LIKE ? THEN 3 WHEN e.title_en LIKE ? THEN 2 WHEN e.abstract LIKE ? THEN 1 ELSE 0 END) AS score
            FROM kb_entities e JOIN kb_entity_types et ON et.code=e.entity_type
            WHERE e.status='active' AND (e.title_fa LIKE ? OR e.title_en LIKE ? OR e.slug LIKE ? OR e.abstract LIKE ?)";
        $params = [$like, $like, $like, $like, $like, $like, $like];
        if ($type) { $sql .= " AND e.entity_type=?"; $params[] = strtoupper($type); }
        $sql .= " ORDER BY score DESC, e.title_fa LIMIT " . max(1, min(200, $limit));
        $st = $db->prepare($sql);
        $st->execute($params);
        return $st->fetchAll();
    }

    /** پرونده‌ی کامل یک موجودیت: خود + روابط + فاکت‌ها + پیوندها */
    public static function get(\PDO $db, string $code): ?array {
        $st = $db->prepare("SELECT e.*, et.label_fa AS type_fa, et.label_en AS type_en
            FROM kb_entities e JOIN kb_entity_types et ON et.code=e.entity_type WHERE e.code=?");
        $st->execute([$code]);
        $e = $st->fetch();
        if (!$e) return null;
        $e['properties'] = $e['properties'] !== null ? json_decode((string)$e['properties'], true) : null;

        // والد
        if (!empty($e['parent_code'])) {
            $p = $db->prepare("SELECT code,title_fa,title_en,entity_type FROM kb_entities WHERE code=?");
            $p->execute([$e['parent_code']]);
            $e['parent'] = $p->fetch() ?: null;
        } else $e['parent'] = null;

        // فرزندان (درخت ناوبری)
        $c = $db->prepare("SELECT code,title_fa,title_en,entity_type FROM kb_entities WHERE parent_code=? ORDER BY id");
        $c->execute([$code]);
        $e['children'] = $c->fetchAll();

        // روابط (با نامِ دو سر)
        $r = $db->prepare("SELECT r.*, rt.label_fa AS rel_fa,
                s.title_fa AS source_title, s.entity_type AS source_type,
                t.title_fa AS target_title, t.entity_type AS target_type
            FROM kb_relations r
            JOIN kb_relation_types rt ON rt.code=r.relation_type
            JOIN kb_entities s ON s.code=r.source_code
            JOIN kb_entities t ON t.code=r.target_code
            WHERE (r.source_code=? OR r.target_code=?) AND r.status='active'
            ORDER BY r.relation_type, r.id");
        $r->execute([$code, $code]);
        $e['relations'] = $r->fetchAll();

        // فاکت‌ها
        $f = $db->prepare("SELECT f.*, fd.label_fa AS facet_fa FROM kb_entity_facets f
            JOIN kb_facet_dimensions fd ON fd.code=f.facet_code WHERE f.entity_code=? ORDER BY fd.sort_order, f.id");
        $f->execute([$code]);
        $e['facets'] = $f->fetchAll();

        // پیوندهای درون‌سایتی
        $l = $db->prepare("SELECT * FROM kb_entity_links WHERE entity_code=? ORDER BY link_type, id");
        $l->execute([$code]);
        $e['links'] = $l->fetchAll();

        return $e;
    }

    /** همسایه‌ها در شبکه دانش (نیمه‌رخگام، با فیلتر نوع رابطه و جهت) */
    public static function neighbors(\PDO $db, string $code, int $depth = 1, ?string $relType = null, string $direction = 'both'): array {
        $depth = max(1, min(3, $depth));
        $visited = [$code => 0];
        $out = [];
        for ($d = 1; $d <= $depth; $d++) {
            $codes = array_keys(array_filter($visited, fn($x) => $x === $d - 1));
            if (!$codes) break;
            // کدها slugified و امن هستند (فقط a-z0-9-_) — درون‌نویسی مستقیم جایگزین ? در CASE
            $in = implode(',', array_map(fn($c) => "'" . self::q($c) . "'", $codes));
            $where = [];
            $params = [];
            if ($direction === 'out' || $direction === 'both') $where[] = "r.source_code IN ($in)";
            if ($direction === 'in' || $direction === 'both') $where[] = "r.target_code IN ($in)";
            $sql = "SELECT r.source_code, r.target_code, r.relation_type, rt.label_fa,
                    (CASE WHEN r.source_code IN ($in) THEN r.target_code ELSE r.source_code END) AS other,
                    e.title_fa AS other_title, e.entity_type AS other_type, r.confidence
                FROM kb_relations r
                JOIN kb_relation_types rt ON rt.code=r.relation_type
                JOIN kb_entities e ON e.code=(CASE WHEN r.source_code IN ($in) THEN r.target_code ELSE r.source_code END)
                WHERE (" . implode(' OR ', $where) . ") AND r.status='active'";
            if ($relType) { $sql .= " AND r.relation_type=?"; $params[] = strtoupper($relType); }
            $st = $db->prepare($sql);
            $st->execute($params);
            foreach ($st->fetchAll() as $row) {
                if (isset($visited[$row['other']])) continue;
                $visited[$row['other']] = $d;
                $out[] = $row;
            }
        }
        return $out;
    }

    /** وضعیت کلی هسته دانش */
    public static function status(\PDO $db): array {
        $out = [];
        $out['entities'] = (int)$db->query("SELECT COUNT(*) FROM kb_entities WHERE status='active'")->fetchColumn();
        $out['entities_by_type'] = $db->query("SELECT e.entity_type, et.label_fa, COUNT(*) c
            FROM kb_entities e JOIN kb_entity_types et ON et.code=e.entity_type
            WHERE e.status='active' GROUP BY e.entity_type ORDER BY et.sort_order, et.code")->fetchAll();
        $out['entities_by_domain'] = $db->query("SELECT domain, COUNT(*) c FROM kb_entities WHERE status='active' GROUP BY domain")->fetchAll();
        $out['relations'] = (int)$db->query("SELECT COUNT(*) FROM kb_relations WHERE status='active'")->fetchColumn();
        $out['relations_by_confidence'] = $db->query("SELECT confidence, COUNT(*) c FROM kb_relations GROUP BY confidence")->fetchAll();
        $out['facets'] = (int)$db->query("SELECT COUNT(*) FROM kb_entity_facets")->fetchColumn();
        $out['links'] = (int)$db->query("SELECT COUNT(*) FROM kb_entity_links")->fetchColumn();
        $out['links_by_confidence'] = $db->query("SELECT confidence, COUNT(*) c FROM kb_entity_links GROUP BY confidence")->fetchAll();
        $out['pending_content'] = (int)$db->query("SELECT COUNT(*) FROM kb_entities WHERE status='active' AND (abstract IS NULL OR abstract='')")->fetchColumn();
        return $out;
    }

    // ------------------------------------------------------------------
    // اعتبارسنجی یکپارچگی
    // ------------------------------------------------------------------

    /** گزارش یکپارچگی: اخلال‌های ساختاری شبکه دانش */
    public static function validate(\PDO $db): array {
        $issues = [];
        $n = (int)$db->query("SELECT COUNT(*) FROM kb_relations r WHERE NOT EXISTS (SELECT 1 FROM kb_entities e WHERE e.code=r.source_code)")->fetchColumn();
        if ($n) $issues[] = "رابطه‌ای با source ناموجود: $n";
        $n = (int)$db->query("SELECT COUNT(*) FROM kb_relations r WHERE NOT EXISTS (SELECT 1 FROM kb_entities e WHERE e.code=r.target_code)")->fetchColumn();
        if ($n) $issues[] = "رابطه‌ای با target ناموجود: $n";
        $n = (int)$db->query("SELECT COUNT(*) FROM kb_entities e WHERE e.entity_type NOT IN (SELECT code FROM kb_entity_types)")->fetchColumn();
        if ($n) $issues[] = "موجودیت با نوع نامعتبر: $n";
        $n = (int)$db->query("SELECT COUNT(*) FROM kb_entities e WHERE e.parent_code IS NOT NULL AND NOT EXISTS (SELECT 1 FROM kb_entities p WHERE p.code=e.parent_code)")->fetchColumn();
        if ($n) $issues[] = "والدِ ناموجود: $n";
        $n = (int)$db->query("SELECT COUNT(*) FROM kb_entity_facets f WHERE f.facet_code NOT IN (SELECT code FROM kb_facet_dimensions)")->fetchColumn();
        if ($n) $issues[] = "فاکت با بعد نامعتبر: $n";
        $n = (int)$db->query("SELECT COUNT(*) FROM kb_entity_links l WHERE NOT EXISTS (SELECT 1 FROM kb_entities e WHERE e.code=l.entity_code)")->fetchColumn();
        if ($n) $issues[] = "پیوند با موجودیت ناموجود: $n";
        // تکرار عنوان فارسی (احتمال مفهوم تکراری)
        $dups = $db->query("SELECT title_fa, COUNT(*) c FROM kb_entities WHERE status='active' GROUP BY title_fa HAVING c>1")->fetchAll();
        foreach ($dups as $d) $issues[] = "عنوان تکراری «{$d['title_fa']}»: {$d['c']} رکورد (باید یک موجودیت + چند رابطه باشد)";
        return $issues;
    }

    // ------------------------------------------------------------------
    // تطبیق با دیتابیس اصلی (cards / theorists / library_resources)
    // ------------------------------------------------------------------

    /**
     * تطبیق خودکار موجودیت‌های هسته دانش با محتوای اصلی (فقط‌خواندنی).
     * معیار: تطبیق دقیقِ نرمال‌شده → 'verified'؛ زیرمجموعه/شباهت → 'suggested'.
     * برگشت: آرایه‌ی [entity_code, entity_title, link_type, link_id, link_title, confidence]
     */
    public static function matchMain(\PDO $main, \PDO $ed, bool $apply = false): array {
        // نقششه‌ی ستون‌های واقعی هر جدول اصلی (عناوین فارسی/انگلیسی در هر جدول نامی متفاوت دارند)
        $pool = [
            'card' => ['table' => 'cards', 'title' => 'title_fa', 'title_en' => 'title_en', 'where' => " WHERE status='published'"],
            'theorist' => ['table' => 'theorists', 'title' => 'name', 'title_en' => 'name_en', 'where' => ''],
            'library_resource' => ['table' => 'library_resources', 'title' => 'title', 'title_en' => 'title_en', 'where' => ''],
        ];
        $mainRows = [];
        foreach ($pool as $type => $cfg) {
            try {
                $st = $main->query("SELECT id, `{$cfg['title']}` AS title, slug, `{$cfg['title_en']}` AS title_en FROM {$cfg['table']}" . $cfg['where'] . " LIMIT 5000");
                foreach ($st->fetchAll() as $r) {
                    $mainRows[] = ['type' => $type, 'id' => (string)$r['id'], 'title' => (string)$r['title'], 'title_en' => (string)($r['title_en'] ?? ''), 'slug' => (string)($r['slug'] ?? '')];
                }
            } catch (\Throwable $e) {
                \Resaname\Support::log('kb_match_main_failed', ['table' => $cfg['table'], 'error' => $e->getMessage()]);
            }
        }

        $edSt = $ed->query("SELECT code, title_fa, title_en, entity_type FROM kb_entities WHERE status='active'");
        $kb = $edSt->fetchAll();

        $matches = [];
        foreach ($kb as $e) {
            $et = self::normTitle((string)$e['title_fa']);
            $en = mb_strtolower(trim((string)$e['title_en']));
            if ($et === '') continue;
            foreach ($mainRows as $m) {
                $mt = self::normTitle((string)$m['title']);
                $men = mb_strtolower(trim((string)$m['title_en']));
                $lenMin = min(mb_strlen($et), mb_strlen($mt));
                $lenMax = max(mb_strlen($et), mb_strlen($mt));
                $hit = null;
                if ($et !== '' && $mt === $et) $hit = 'verified';
                elseif ($en !== '' && $men !== '' && $en === $men) $hit = 'verified';
                // زیرمجموعه‌ی معنادار: کوتاه‌ترین عنوان دست‌کم ۸ کاراکتر و بیش از نیمی از بلندترین
                elseif ($et !== '' && $mt !== '' && $lenMin >= 8 && $lenMin / max(1, $lenMax) >= 0.5
                    && (str_contains($mt, $et) || str_contains($et, $mt))) $hit = 'suggested';
                elseif ($hit === null && $lenMin >= 6 && self::similarity($et, $mt) >= 0.82) $hit = 'suggested';
                if ($hit) {
                    $matches[] = ['entity_code' => $e['code'], 'entity_title' => $e['title_fa'], 'link_type' => $m['type'], 'link_id' => $m['id'], 'link_title' => $m['title'], 'confidence' => $hit];
                    if ($hit === 'verified') break; // یک تطبیق دقیق کافی است
                }
            }
        }
        if ($apply) {
            foreach ($matches as $m) {
                self::linkMain($ed, $m['entity_code'], $m['link_type'], $m['link_id'], $m['link_title'], $m['confidence'], 'auto_match');
            }
            self::log($ed, null, 'link_main_apply', ['count' => count($matches)], 'kb');
        }
        return $matches;
    }

    /** نرمال‌سازی عنوان برای تطبیق (حذف پیشوندهای نام‌نویس + نیم‌فاصله/تطویل) */
    public static function normTitle(string $t): string {
        $t = trim($t);
        $t = str_replace(['ـ', '‌'], '', $t); // تطویل و نیم‌فاصله
        $t = str_replace(['نظریه‌ی ', 'نظریه ', 'مدل ', 'مکتب ', 'سنت ', 'روش ', 'فن ', 'چارچوب ', 'پارادایم '], '', $t);
        $t = str_ireplace(['theory ', 'model of ', 'the '], '', mb_strtolower($t));
        $t = preg_replace('#[\s\-_/،:؛|()]+#u', ' ', $t);
        return trim(mb_strtolower($t));
    }

    /** شباهت ساده (ضریب دایس روی ۳-گرام‌های کاراکتری؛ برابر برای دو عنوان هم‌طول، پایین برای زیرمجموعه) */
    private static function similarity(string $a, string $b): float {
        if ($a === '' || $b === '') return 0.0;
        $ga = self::ngrams($a, 3);
        $gb = self::ngrams($b, 3);
        if (!$ga || !$gb) return 0.0;
        $common = count(array_intersect_key($ga, $gb));
        return (2 * $common) / (count($ga) + count($gb));
    }

    private static function ngrams(string $s, int $n): array {
        $s = mb_strtolower($s);
        $len = mb_strlen($s);
        if ($len < $n) return [$s => 1];
        $out = [];
        for ($i = 0; $i + $n <= $len; $i++) $out[mb_substr($s, $i, $n)] = 1;
        return $out;
    }

    // ------------------------------------------------------------------
    // منوی دانشنامه (بخش ششم طرح)
    // ------------------------------------------------------------------

    /** ساختار منوی ده‌گانه‌ی دانشنامه */
    public const MENU = [
        'مکاتب و سنت‌های فکری' => ['SCHOOL', 'TRADITION'],
        'نظریه‌ها و نظام‌های تبیینی' => ['THEORY', 'MODEL', 'FRAMEWORK', 'HYPOTHESIS', 'LAW', 'CONCEPT'],
        'پارادایم‌ها و رویکردهای پژوهش' => ['PARADIGM', 'APPROACH'],
        'روش‌شناسی و طرح‌های پژوهش' => ['METHODOLOGY', 'METHOD', 'DESIGN', 'STRATEGY'],
        'گردآوری داده و نمونه‌گیری' => ['TECHNIQUE', 'SAMPLING', 'INSTRUMENT'],
        'تحلیل داده' => ['ANALYSIS'],
        'کیفیت و اعتبار پژوهش' => ['QUALITY'],
        'اخلاق و استانداردهای پژوهش' => ['ETHICS', 'PROTOCOL'],
        'ابزارها و نرم‌افزارهای پژوهش' => ['SOFTWARE', 'DATASET'],
        'اندیشمندان و منابع علمی' => ['PERSON', 'WORK'],
    ];

    /** رندر منوی دانشنامه از داده‌ی زنده (درخت ناوبری بر اساس parent_code) */
    public static function menu(\PDO $db, ?string $section = null): string {
        $st = $db->query("SELECT code,title_fa,title_en,entity_type,parent_code FROM kb_entities WHERE status='active' ORDER BY id");
        $all = $st->fetchAll();
        $byType = [];
        foreach ($all as $e) $byType[$e['entity_type']][] = $e;

        $lines = [];
        foreach (self::MENU as $label => $types) {
            if ($section && stripos($label, $section) === false) continue;
            $lines[] = "■ $label";
            $top = [];
            foreach ($types as $t) foreach ($byType[$t] ?? [] as $e) if (!$e['parent_code']) $top[] = $e;
            usort($top, fn($a, $b) => strcmp($a['title_fa'], $b['title_fa']));
            if (!$top) { $lines[] = '   (خالی)'; continue; }
            foreach ($top as $e) {
                $children = self::childCount($all, $e['code']);
                $suffix = $children ? "  ($children زیرمجموعه)" : '';
                $lines[] = '   • ' . $e['title_fa'] . ($e['title_en'] ? " — {$e['title_en']}" : '') . $suffix;
            }
        }
        return implode("\n", $lines);
    }

    private static function childCount(array $all, string $code): int {
        $n = 0;
        foreach ($all as $e) if ($e['parent_code'] === $code) $n++;
        return $n;
    }

    // ------------------------------------------------------------------
    // خروجی JSON (برای RAG / آینده)
    // ------------------------------------------------------------------

    /** خروجی کامل هسته دانش به‌صورت JSON (هر موجودیت با روابط و فاکت‌ها) */
    public static function exportJson(\PDO $db): string {
        $st = $db->query("SELECT * FROM kb_entities WHERE status='active' ORDER BY id");
        $entities = [];
        foreach ($st->fetchAll() as $e) {
            $e['properties'] = $e['properties'] !== null ? json_decode((string)$e['properties'], true) : null;
            $entities[] = $e;
        }
        $rels = $db->query("SELECT source_code,target_code,relation_type,note,weight,confidence FROM kb_relations WHERE status='active'")->fetchAll();
        $fac = $db->query("SELECT entity_code,facet_code,value_fa,value_en FROM kb_entity_facets")->fetchAll();
        $facMap = [];
        foreach ($fac as $f) $facMap[$f['entity_code']][] = $f;
        $links = $db->query("SELECT entity_code,link_type,link_id,link_title,confidence FROM kb_entity_links")->fetchAll();
        $linkMap = [];
        foreach ($links as $l) $linkMap[$l['entity_code']][] = $l;
        foreach ($entities as &$e) {
            $e['facets'] = $facMap[$e['code']] ?? [];
            $e['links'] = $linkMap[$e['code']] ?? [];
        }
        unset($e);
        return json_encode(['exported_at' => date('c'), 'entities' => $entities, 'relations' => $rels], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    }

    // ------------------------------------------------------------------
    // کمکی‌ها
    // ------------------------------------------------------------------

    /** تبدیل نام به code یکنواخت (حروف کوچک، خط تیره) */
    public static function slugifyCode(string $s): string {
        $s = mb_strtolower(trim($s));
        $map = [];
        for ($i = 0; $i < mb_strlen($s); $i++) {
            $ch = mb_substr($s, $i, 1);
            $map[] = $ch;
        }
        $s = implode('', $map);
        $s = preg_replace('/[^a-z0-9\-]+/', '-', $s);
        $s = trim(preg_replace('/-+/', '-', $s), '-');
        return $s;
    }

    /** پاک‌سازی ورودی برای ورود مستقیم در SQL (فقط برای مقادیر code که از slugifyCode می‌آیند) */
    private static function q(string $s): string {
        return preg_replace('/[^a-zA-Z0-9\-_]/', '', $s);
    }

    private static function log(\PDO $db, ?string $code, string $action, array $detail, string $source): void {
        try {
            $db->prepare("INSERT INTO kb_audit_log (entity_code,action,detail,source) VALUES (?,?,?,?)")
                ->execute([$code, $action, json_encode($detail, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), $source]);
        } catch (\Throwable) {
            // جدول ممیزی هنوز ساخته نشده — نادیده بگیر
        }
    }
}
