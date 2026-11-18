# هسته دانش علمی رسانامه — معماری و راهنمای کامل

فاز دوم کارخانه هوشمندسازی: گراف دانش ساختاریافته برای دانشنامه‌ی علمی resaname.ir؛
شامل مکاتب، نظریه‌ها، نظریه‌پردازان، روش‌ها، نمونه‌گیری، تحلیل، کیفیت و اخلاق پژوهش.

## ۱. اصول معماری

1. **دو نظام مستقل** — نظام فکری-نظری (`theoretical`) و نظام روش‌شناختی (`methodological`)؛
   هیچ سلسله‌مراتب واحدی بین آن‌ها وجود ندارد. نظام سوم `shared` برای اندیشمندان، آثار و
   مناقشه‌ها (میان‌دو‌نظام).
2. **یک موجودیت، یک مفهوم، یک کد یکتا** — `kb_entities.code` (کدینگ `kind-slug-en`).
3. **روابط رکوردهای مستقل‌اند** — هر رابطه سطر جداگانه در `kb_relations` با نوع مشخص و
   اطمینان صریح (`verified`/`suggested`).
4. **فاکت از درخت جداست** — بُعد‌های چندبُعدی در `kb_entity_facets`؛ درخت `parent_code`
   فقط برای ساختار سلسله‌مراتبی عمیق ≤ ۲ سطح.
5. **تک منبع حقیقت** — سه ثبت‌نام‌ساز PHP (`app/Knowledge/{EntityTypes,RelationTypes,FacetTypes}.php`)
   که جدول‌های seed، CLI، MCP و پرامپت‌های AI همگی از آن‌ها می‌گیرند.
6. **دو دیتابیس** — `resaname_editorial` (جداول `kb_*`؛ خواندن/نوشتن) و `resaname_h`
   (فقط‌خواندنی؛ کارت‌ها/نظریه‌پردازها/منابع برای پیوند).

## ۲. جداول (۸ جدول در `resaname_editorial`)

DDL: `database/kb_schema.sql`

| جدول | نقش |
| --- | --- |
| `kb_entity_types` | ثبت‌نام‌ساز ۲۸ نوع موجودیت |
| `kb_relation_types` | ثبت‌نام‌ساز ۲۰ نوع رابطه (با پرچم تقارن) |
| `kb_facet_dimensions` | ثبت‌نام‌ساز ۹ بُعد فاکت |
| `kb_entities` | موجودیت‌ها: `code`(PK), `type`, `title_fa/en`, `parent_code`, `abstract_fa`, `properties`(JSON), `domain`, `is_active` |
| `kb_relations` | `source_code`, `target_code`, `type`, `note`, `weight`(0-100), `confidence`, `is_active` |
| `kb_entity_facets` | `entity_code`, `facet`, `value_fa`, `value_en` |
| `kb_entity_links` | `entity_code`, `link_type`(card/theorist/library_resource/category), `link_id`(مثل `card-84`), `link_title`, `confidence` |
| `kb_audit_log` | ردپای عملیات نوشتن (`action`, `detail` JSON, `source`, `created_at`) |

## ۳. ثبت‌نام‌سازها (تک منبع حقیقت)

- **۲۸ نوع موجودیت** — ۱۱ نظری + ۱۴ روش‌شناختی + ۳ مشترک.
  نظری: SCHOOL, TRADITION, PARADIGM, THEORY, MODEL, FRAMEWORK, HYPOTHESIS, LAW, CONCEPT, DISCIPLINE, SUBFIELD
  روش‌شناختی: METHODOLOGY, METHOD, APPROACH, DESIGN, STRATEGY, TECHNIQUE, INSTRUMENT, SAMPLING, ANALYSIS, QUALITY, ETHICS, SOFTWARE, PROTOCOL, DATASET
  مشترک: PERSON, WORK, DISPUTE
- **۲۰ نوع رابطه** — IS_A, PART_OF, BELONGS_TO, INFLUENCED_BY, INFLUENCED, CRITICIZES, EXTENDS, CONTRADICTS, RELATED_TO, APPLIES_TO, USES, MEASURES, ANALYZES, COLLECTS, DEVELOPED_BY, PROPOSED_BY, OPERATIONALIZES, TESTS, DERIVED_FROM, COMPARED_WITH (سه‌تایشم تقارنی: CONTRADICTS, RELATED_TO, COMPARED_WITH)
- **۹ بُعد فاکت** — discipline, theoretical_tradition, approach, epistemology, data_type, analysis_scale, application_domain, time_frame, qualifies_for

فهرست کامل با تعاریف: `.opencode/skills/resaname-kb/references/taxonomy.md`

## ۴. داده‌های seed

فایل‌ها: `app/Knowledge/Seed/{Schools,Paradigms,Designs,Collection,Analysis,ToolsQualityEthics,Theories,Persons,Relations}.php`
به‌علاوه ۸ فایل فاکت، یکی به‌ازای هر فایل موجودیت: `Facets_{Theories,Persons,Schools,Paradigms,Designs,Collection,Analysis,ToolsQualityEthics}.php`

فرمت سطر موجودیت:
```php
[code, title_fa, title_en, TYPE, parent_code, abstract_fa?, properties?],
```
`properties` **فقط** اگر عنصر هفتم باشد ثبت می‌شود. فرمت رابطه:
```php
[source_code, target_code, RELATION_TYPE, note?, weight?, confidence?],
```

`php bin/kb.php seed` آیدامپوتنت است (upsert) و فایل‌های `Facets_*.php` را با glob بارگذاری و در `kb_entity_facets` درج می‌کند (درج additive؛ `ON DUPLICATE` فقط `value_en` را به‌روز می‌کند). `kb_entity_links` را لمس نمی‌کند.

## ۵. سرویس PHP مرجع — `app/Knowledge/KnowledgeBase.php`

مرجع عملیاتیِ همه‌ی رفتارها؛ CLI و MCP هر دو این منطق را آینه می‌کنند (برای همگام نگه‌داشتن،
هر تغییر در الگوریتم باید در `server.mjs` هم اعمال شود):

- `install()` — ساخت جداول + بارگذاری ثبت‌نام‌سازها
- `status()` / `validate()` — آمار و گزارش یکپارچگی
- `search($q, $type?, $limit?)` — جست‌وجوی امتیازبندی‌شده روی title_fa/title_en/abstract (LIKE با فرار صحیح `%`/`_`/`\`)
- `get($code)` / `neighbors($code, $relType?, $dir?)` — پرونده و شبکه (در `neighbors` مقدار CASE به‌صورت literal slug‌شده inline می‌شود — پارامتر `?` داخل CASE در PDO کار نمی‌کرد)
- `addEntity()` / `addRelation()` / `addFacet()` / `linkMain()` — نوشتن با `kb_audit_log`
- `matchMain($apply)` — تطبیق خودکار با دیتابیس اصلی (بند ۷)
- `menu($section?)` — منوی ده‌گانه‌ی ناوبری

## ۶. منوی ده‌گانه‌ی دانشنامه

`php bin/kb.php menu [بخش]` و `kb_menu` ساختار ناوبری سایت را از روی `parent_code` می‌سازند:
۱ مکاتب و سنت‌ها · ۲ پارادایم‌ها · ۳ نظریه‌ها و مدل‌ها · ۴ حوزه‌های تخصصی · ۵ روش‌شناسی و رویکردها ·
۶ طرح‌ها · ۷ فنون گردآوری و نمونه‌گیری · ۸ تحلیل داده · ۹ کیفیت و اخلاق · ۱۰ اندیشمندان و آثار.

## ۷. تطبیق با دیتابیس اصلی (`link-main` / `kb_match_main`)

ستون‌های واقعی `resaname_h`:
- `cards` → `title_fa`/`title_en` (فقط `status='published'`)
- `theorists` → `name`/`name_en`
- `library_resources` → `title`/`title_en`

سطوح اطمینان:
- **verified** — تطبیق دقیق عنوان بعد از نرمال‌سازی (فارسی یا انگلیسی)
- **suggested** — شامل‌بودن (کوتاه‌تر ≥ ۸ نویسه و ≥ ۵۰٪ بلندتر) **یا** شباهت Dice trigram ≥ ۰٫۸۲ (کوتاه‌تر ≥ ۶ نویسه)

نرمال‌سازی `normTitle`: حذف تطویل و ZWNJ و پیشوندهای نام‌گزار (`نظریه `، `مدل `، `روش `…).
پیشوند «مطالعات » عمداً در لیست نیست (عنوان ۵ نویسه‌ای عمومی می‌ساخت). شباهت `Dice = 2*shared/(|a|+|b|)` روی trigram — **نه** `common/min`.

## ۸. ابزارهای OpenCode (فاز دوم)

چهار ابزار OpenCode روی همین هسته سوار شده‌اند:

| ابزار | فایل | نقش |
| --- | --- | --- |
| **MCP server** | `mcp/resaname-kb/server.mjs` | ۱۳ ابزار `kb_*`؛ اتصال مستقیم mysql2 (بدون وابستگی به PHP) |
| **Skill** | `.opencode/skills/resaname-kb/SKILL.md` | دستورالعمل طبقه‌بندی + `references/{schema,taxonomy}.md` |
| **Agent (Role)** | `.opencode/agents/kb-curator.md` | زیرایجنت «سرپرست طبقه‌بندی» (mode: subagent) |
| **Plugin (Hook)** | `.opencode/plugins/resaname-kb/index.ts` | دستور `/kb` + هوک `prompt` برای یادآوری خودکار |
| **پیکربندی** | `opencode.json` | تعریف سرور MCP محلی `resaname-kb` |

### خط فرمان (۱۶ دستور)

`install, seed, status, validate, list, show, search, graph, add, relate, facet, link, link-main, menu, export, help`

### ابزارهای MCP (۱۳ عدد)

خواندن: `kb_status, kb_search, kb_get, kb_neighbors, kb_list, kb_menu, kb_registries, kb_validate`
نوشتن (با ممیزی): `kb_add_entity, kb_add_relation, kb_add_facet, kb_link_main`
تطبیق: `kb_match_main` (فقط‌خواندنی، `apply=true` ذخیره می‌کند)

## ۹. وضعیت فعلی

- ۵۸۱ موجودیت فعال / ۳۸۴ رابطه (۳۴۳ verified، ۴۱ suggested) / **۱۸۸۶ فاکت** (فاکت‌بندی ۹بُعدیِ همه‌ی ۵۸۱ موجودیت با workflow fan-out ۸ workerی؛ ۸ فایل `Facets_*.php`) / **۰ موجودیت بدون چکیده** (۱۴۳ چکیده با workflow fan-out ۶ workerی نوشته و verify شد)
- `validate` بدون خطا
- پیوندهای درون‌سایتی: ۷۱ (۴۹ verified / ۲۲ suggested) — از ۷۷ تطبیقِ `link-main`، ۶ تطبیق نادرست در بازبینی ویرایشی حذف شد؛ پیوند design-simulation→card-474 هم به concept-simulation جابه‌جا شد (لاگ `kb_audit_log`).

## ۱۰. نقشه‌ی راه (بند ۲۰ — قابلیت‌های هوشمند)

1. **چکیده‌ساز** — ✅ انجام شد: ۱۴۳ چکیده با workflow fan-out (۶ worker + ۲ بازبینی مستقل + gate قطعی)؛ جزئیات در `WORKFLOW-REPORT.md`.
2. **پیوند خودکار** — ✅ انجام شد: ۷۱ پیوند معتبر اعمال و ۶ تطبیق نادرست در بازبینی حذف شد.
3. **جست‌وجوی ترکیبی** — جست‌وجوی همزمان موجودیت + کارت سایت از یک جعبه.
4. **ساختار‌یابی پرسش** — پاسخ پرسش‌های پژوهش با کشیدن زیرگراف (graph) و تبدیل به متن.
5. **پیشنهاد رابطه** — برای موجودیت‌های تازه‌وارد، پیشنهاد خودکار رابطه بر اساس همسایه‌ها.
6. **فاکت‌بندی** — تکمیل فاکت‌های ۹بُعدی برای موجودیت‌های کلیدی (الان فقط ۱ فاکت ثبت شده).
