# مرجع فنی هسته دانش — جداول، خط فرمان، ابزارهای MCP

## جداول دیتابیس (در `resaname_editorial`)

DDL کامل: `database/kb_schema.sql` — ۸ جدول:

| جدول | نقش | کلیدها / ستون‌های اصلی |
| --- | --- | --- |
| `kb_entity_types` | ثبت‌نام‌ساز ۲۸ نوع موجودیت | `code`, `label_fa`, `label_en`, `domain`, `description`, `sort_order` |
| `kb_relation_types` | ثبت‌نام‌ساز ۲۰ نوع رابطه | `code`, `label_fa`, `label_en`, `description`, `symmetric` |
| `kb_facet_dimensions` | ثبت‌نام‌ساز ۹ بُعد فاکت | `code`, `label_fa`, `label_en`, `description` |
| `kb_entities` | خودِ موجودیت‌ها | `code` (یکتا، PK), `type`, `title_fa`, `title_en`, `parent_code`, `abstract_fa`, `properties` (JSON), `domain`, `is_active` |
| `kb_relations` | روابط به‌عنوان رکوردهای مستقل | `source_code`, `target_code`, `type`, `note`, `weight` (0-100), `confidence` (`verified`\|`suggested`), `is_active` |
| `kb_entity_facets` | فاکت‌های چندبُعدی (جدا از درخت) | `entity_code`, `facet` (code بُعد), `value_fa`, `value_en` |
| `kb_entity_links` | پیوند به دیتابیس اصلی | `entity_code`, `link_type` (`card`\|`theorist`\|`library_resource`\|`category`), `link_id` (مثل `card-84`), `link_title`, `confidence` |
| `kb_audit_log` | ردپای عملیات نوشتن | `entity_code`, `action`, `detail` (JSON), `source`, `created_at` |

دو دیتابیس:
- `resaname_editorial` — جداول `kb_*` (خوندن/نوشتن)
- `resaname_h` — فقط‌خواندنی؛ `cards (id,title_fa,title_en,slug,status)`, `theorists (id,name,name_en,slug)`, `library_resources (id,title,title_en,slug)`

## فرمت سطر seed (فایل‌های `app/Knowledge/Seed/*.php`)

```php
[code, title_fa, title_en, TYPE, parent_code, abstract_fa?, properties?],
```
توجه: `properties` **فقط** اگر عنصر هفتم آرایه باشد ثبت می‌شود؛ اگر جابه‌جا شود سنجاق‌شده نمی‌ماند.

## خط فرمان — `php bin/kb.php` (۱۶ دستور)

| دستور | توضیح |
| --- | --- |
| `install` | ساخت جداول `kb_*` + بارگذاری ثبت‌نام‌سازها |
| `seed` | بارگذاری کامل طبقه‌بندی (آیدامپوتنت؛ `kb_entity_facets`/`kb_entity_links` را پاک نمی‌کند) |
| `status` | آمار هسته دانش (تفکیک نوع/نظام، اطمینان روابط، بدون‌چکیده‌ها) |
| `validate` | گزارش یکپارچگی (دو سر ناموجود، نوع نامعتبر، والد ناموجود، عنوان تکراری) |
| `list [TYPE] [--limit=N]` | فهرست موجودیت‌ها |
| `show <code>` | پرونده‌ی کامل یک موجودیت |
| `search <query> [--type=TYPE] [--limit=N]` | جست‌وجوی امتیازبندی‌شده |
| `graph <code> [--depth=1] [--rel=TYPE] [--dir=both\|out\|in]` | شبکه‌ی اطراف موجودیت |
| `add --code= --fa= --en= --type= [--parent=] [--abstract=] [--json='{}']` | ساخت/به‌روزرسانی موجودیت |
| `relate --source= --target= --type= [--note=] [--weight=50] [--confidence=suggested]` | ثبت رابطه |
| `facet --code= --facet= --value= [--value-en=]` | افزودن فاکت |
| `link --code= --type=card\|theorist\|library_resource\|category --id= [--title=]` | پیوند دستی با دیتابیس اصلی |
| `link-main [--apply]` | تطبیق خودکار با دیتابیس اصلی (بدون `--apply` فقط‌خواندنی) |
| `menu [بخش]` | منوی ده‌گانه‌ی دانشنامه |
| `export [--file=kb.json]` | خروجی JSON کامل |
| `help` | راهنما |

نکته: در ویندوز PHP را به PATH اضافه کن:
`$env:PATH = "C:\Users\MamaDDelta\AppData\Local\Microsoft\WinGet\Packages\PHP.PHP.8.4_Microsoft.Winget.Source_8wekyb3d8bbwe;" + $env:PATH`

## ابزارهای MCP — سرور `resaname-kb` (۱۳ ابزار)

در OpenCode از طریق Code Mode: `tools["resaname-kb"].kb_*`

| ابزار | ورودی | خروجی |
| --- | --- | --- |
| `kb_status` | — | آمار کامل (تفکیک نوع/نظام، اطمینان، فاکت، پیوند، بدون‌چکیده) |
| `kb_search` | `query`, `type?`, `limit?` | نتایج امتیازبندی‌شده |
| `kb_get` | `code` | پرونده‌ی کامل: خود + والد + فرزندان + روابط + فاکت‌ها + پیوندها |
| `kb_neighbors` | `code`, `relType?`, `dir?` (out/in/both) | همسایه‌ها در شبکه |
| `kb_list` | `type?`, `limit?` | فهرست موجودیت‌ها |
| `kb_menu` | `section?` | منوی ده‌گانه‌ی ناوبری |
| `kb_registries` | — | سه ثبت‌نام‌ساز رسمی (تک منبع حقیقت) |
| `kb_validate` | — | گزارش یکپارچگی |
| `kb_add_entity` | `code`, `type`, `titleFa`, `titleEn`, `parentCode?`, `abstractFa?`, `properties?` | ساخت/به‌روزرسانی (با ممیزی) |
| `kb_add_relation` | `source`, `target`, `type`, `note?`, `weight?`, `confidence?` | ثبت رابطه (هر دو سر باید موجود باشد) |
| `kb_add_facet` | `code`, `facet`, `valueFa`, `valueEn?` | افزودن فاکت |
| `kb_link_main` | `code`, `mainType`, `mainId`, `title?`, `confidence?` | پیوند دستی |
| `kb_match_main` | `apply?` | تطبیق خودکار؛ `apply=true` ذخیره می‌کند |

## منطق تطبیق با دیتابیس اصلی (`link-main` / `kb_match_main`)

ستون‌های واقعی دیتابیس اصلی:
- `cards` → `title_fa` / `title_en` (فقط `status='published'`)
- `theorists` → `name` / `name_en`
- `library_resources` → `title` / `title_en`

سطوح اطمینان:
- **verified** — تطبیق دقیق عنوان (بعد از نرمال‌سازی) فارسی یا انگلیسی
- **suggested** — شامل‌بودن (کوتاه‌تر ≥ ۸ نویسه و ≥ ۵۰٪ بلندتر) یا شباهت Dice trigram ≥ ۰٫۸۲ (کوتاه‌تر ≥ ۶ نویسه)

نرمال‌سازی (`normTitle`): حذف تطویل (ـ) و نیم‌فاصله (ZWNJ) و پیشوندهای نام‌گزار (`نظریه `، `مدل `، `روش `…).
پیشوند «مطالعات » **در لیست نیست** (عنوان عمومی ۵ نویسه‌ای می‌ساخت که ۳۰+ کارت می‌خورد).

شباهت: `Dice = 2*shared / (|a| + |b|)` روی trigram — **نه** `common/min` (زیرمجموعه ۱٫۰ می‌خورد).

## سیاست عنوان تکراری

نام طبیعی را نگه دار؛ عنوان تکراری را با نوع qualify کن:
- پارادایم‌ها: «پژوهش انتقادی **(پارادایم)**»، «پراگماتیسم پژوهشی»
- طرح‌ها: «طرح همبستگی»، «طرح پژوهش تاریخی»
- آثار: زیرعنوان واقعی کتاب («تحلیل داده‌های کیفی: منبع گسترش‌یافته»)
