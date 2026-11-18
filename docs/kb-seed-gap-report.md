# گزارش شکاف محتوایی — seed کارت‌ها به هسته‌ی دانش
## دیتابیس: `resaname_kb_final_inspect` · تاریخ: ۱۵/۱۱/۲۰۲۶

> این گزارش **نتیجه‌ی واقعی** seed 771 سندِ `resaname_editorial` به اسکیمای فاینال است.
> هیچ داده‌ی ساختگی وارد نشده؛ همه‌ی مقادیر از دیتابیس مبدأ آمده‌اند. دیتابیس خام `resaname_kb_final` دست‌نخورده است.

## ۱. اجمالی

| جدول | رکورد |
|---|---:|
| `kb_entities` (موجودیت) | **771** (731 کارت + 39 نظریه‌پرداز + 1 کتاب) |
| `kb_entity_facets` | **714** (همه `analysis_scale`) |
| `kb_relations` | **23** (همه `RELATED_TO`، از ارجاع صریح MC در متن) |
| `kb_entity_links` | **771** (731 card + 39 theorist + 1 library_resource) |
| `kb_sources` | **1** (کتاب «نظریه‌های ارتباطات» — Sorens & Tancred) |

دیتابیس خام `resaname_kb_final`: entities=0, relations=0, facets=0 — **خالی، دست‌نخورده** ✅

- وضعیت ویرایشی: `draft`: 593، `approved`: 178
- وضعیت علمی: `unassessed`: 771 — **هیچ‌کدام ارزیابی علمی ندارند** (امتیاز ویرایشی ≠ اعتبار علمی)
- وضعیت چرخه‌حیات: `active`: 771
- کارت‌های **امتیاز 90+ (اولویت)**: **183** = 158 کارت + 25 نظریه‌پرداز

## ۲. توزیع موجودیت‌ها بر اساس نوع (با اولویت 90+)

| نوع | برچسب | تعداد | اولویت 90+ |
|---|---|---:|---:|
| `THEORY` | نظریه | 479 | 106 |
| `MODEL` | مدل | 124 | 31 |
| `FRAMEWORK` | چارچوب نظری | 45 | 9 |
| `PERSON` | اندیشمند | 39 | 25 |
| `WORK` | اثر علمی | 18 | 1 |
| `HYPOTHESIS` | فرضیه | 17 | 4 |
| `METHOD` | روش پژوهش | 12 | 3 |
| `PARADIGM` | پارادایم | 9 | 1 |
| `SCHOOL` | مکتب | 8 | 1 |
| `CONCEPT` | مفهوم | 8 | 1 |
| `LAW` | قانون علمی | 4 | 0 |
| `STRATEGY` | استراتژی | 3 | 1 |
| `METHODOLOGY` | روش‌شناسی | 3 | 0 |
| `SUBFIELD` | حوزه تخصصی | 1 | 0 |
| `INDICATOR` | شاخص | 1 | 0 |

**انواع خالی (21 از 36):** `CONSTRUCT`، `PROPOSITION`، `VARIABLE`، `FINDING`، `RESEARCH_PROBLEM`، `RESEARCH_QUESTION`، `RESEARCH_STUDY`، `ANALYSIS`، `APPROACH`، `DESIGN`، `ETHICS`، `INSTRUMENT`، `PROTOCOL`، `QUALITY`، `SAMPLING`، `TECHNIQUE`، `DATASET`، `SOFTWARE`، `DISCIPLINE`، `DISPUTE`، `TRADITION`

## ۳. فیلدهای خالی به تفکیک نوع — **اصلی‌ترین بخش گزارش**

هر ردیف = یک فیلدِ `properties`؛ «خالی» یعنی مقدار خالی/غیرموجود. فیلد `title_en` جداگانه در ستون آخر.

### `THEORY` (نظریه) — 479 رکورد، 90+: 106

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `studies` | 477 | 2 | 100٪ |
| `issues` | 476 | 3 | 99٪ |
| `score` | 476 | 3 | 99٪ |
| `research_guide` | 474 | 5 | 99٪ |
| `evolution` | 472 | 7 | 99٪ |
| `aliases` | 471 | 8 | 98٪ |
| `applications` | 471 | 8 | 98٪ |
| `assumptions` | 471 | 8 | 98٪ |
| `critiques` | 471 | 8 | 98٪ |
| `field` | 471 | 8 | 98٪ |
| `history` | 471 | 8 | 98٪ |
| `key_concepts` | 471 | 8 | 98٪ |
| `main_problem` | 471 | 8 | 98٪ |
| `mechanism` | 471 | 8 | 98٪ |
| `model` | 471 | 8 | 98٪ |
| `principles` | 471 | 8 | 98٪ |
| `related` | 471 | 8 | 98٪ |
| `sources` | 471 | 8 | 98٪ |
| `summary` | 471 | 8 | 98٪ |
| `theorists` | 471 | 8 | 98٪ |
| `year` | 13 | 466 | 3٪ |
| `theorists_text` | 12 | 467 | 3٪ |
| `approach` | 8 | 471 | 2٪ |
| `card_type` | 8 | 471 | 2٪ |
| `category_id` | 8 | 471 | 2٪ |
| `code` | 8 | 471 | 2٪ |
| `comments_count` | 8 | 471 | 2٪ |
| `constructs` | 8 | 471 | 2٪ |
| `core_logic` | 8 | 471 | 2٪ |
| `created_at` | 8 | 471 | 2٪ |
| `critique` | 8 | 471 | 2٪ |
| `detailed_analysis` | 8 | 471 | 2٪ |
| `example` | 8 | 471 | 2٪ |
| `id` | 8 | 471 | 2٪ |
| `image_url` | 8 | 471 | 2٪ |
| `level` | 8 | 471 | 2٪ |
| `macro_domain` | 8 | 471 | 2٪ |
| `paradigm` | 8 | 471 | 2٪ |
| `published_at` | 8 | 471 | 2٪ |
| `recommended_sources` | 8 | 471 | 2٪ |
| `related_approaches` | 8 | 471 | 2٪ |
| `status` | 8 | 471 | 2٪ |
| `tags` | 8 | 471 | 2٪ |
| `updated_at` | 8 | 471 | 2٪ |
| `views` | 8 | 471 | 2٪ |
| `slug` | 0 | 479 | 0٪ |
| `title_en` | 0 | 479 | 0٪ |
| `title_fa` | 0 | 479 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 479 | 0٪ |

### `MODEL` (مدل) — 124 رکورد، 90+: 31

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `studies` | 124 | 0 | 100٪ |
| `research_guide` | 123 | 1 | 99٪ |
| `issues` | 122 | 2 | 98٪ |
| `score` | 122 | 2 | 98٪ |
| `aliases` | 121 | 3 | 98٪ |
| `applications` | 121 | 3 | 98٪ |
| `assumptions` | 121 | 3 | 98٪ |
| `critiques` | 121 | 3 | 98٪ |
| `evolution` | 121 | 3 | 98٪ |
| `field` | 121 | 3 | 98٪ |
| `history` | 121 | 3 | 98٪ |
| `key_concepts` | 121 | 3 | 98٪ |
| `main_problem` | 121 | 3 | 98٪ |
| `mechanism` | 121 | 3 | 98٪ |
| `model` | 121 | 3 | 98٪ |
| `principles` | 121 | 3 | 98٪ |
| `related` | 121 | 3 | 98٪ |
| `sources` | 121 | 3 | 98٪ |
| `summary` | 121 | 3 | 98٪ |
| `theorists` | 121 | 3 | 98٪ |
| `year` | 11 | 113 | 9٪ |
| `theorists_text` | 9 | 115 | 7٪ |
| `approach` | 3 | 121 | 2٪ |
| `card_type` | 3 | 121 | 2٪ |
| `category_id` | 3 | 121 | 2٪ |
| `code` | 3 | 121 | 2٪ |
| `comments_count` | 3 | 121 | 2٪ |
| `constructs` | 3 | 121 | 2٪ |
| `core_logic` | 3 | 121 | 2٪ |
| `created_at` | 3 | 121 | 2٪ |
| `critique` | 3 | 121 | 2٪ |
| `detailed_analysis` | 3 | 121 | 2٪ |
| `example` | 3 | 121 | 2٪ |
| `id` | 3 | 121 | 2٪ |
| `image_url` | 3 | 121 | 2٪ |
| `level` | 3 | 121 | 2٪ |
| `macro_domain` | 3 | 121 | 2٪ |
| `paradigm` | 3 | 121 | 2٪ |
| `published_at` | 3 | 121 | 2٪ |
| `recommended_sources` | 3 | 121 | 2٪ |
| `related_approaches` | 3 | 121 | 2٪ |
| `status` | 3 | 121 | 2٪ |
| `tags` | 3 | 121 | 2٪ |
| `updated_at` | 3 | 121 | 2٪ |
| `views` | 3 | 121 | 2٪ |
| `slug` | 0 | 124 | 0٪ |
| `title_en` | 0 | 124 | 0٪ |
| `title_fa` | 0 | 124 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 124 | 0٪ |

### `FRAMEWORK` (چارچوب نظری) — 45 رکورد، 90+: 9

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `research_guide` | 45 | 0 | 100٪ |
| `studies` | 45 | 0 | 100٪ |
| `aliases` | 44 | 1 | 98٪ |
| `applications` | 44 | 1 | 98٪ |
| `assumptions` | 44 | 1 | 98٪ |
| `critiques` | 44 | 1 | 98٪ |
| `evolution` | 44 | 1 | 98٪ |
| `field` | 44 | 1 | 98٪ |
| `history` | 44 | 1 | 98٪ |
| `key_concepts` | 44 | 1 | 98٪ |
| `main_problem` | 44 | 1 | 98٪ |
| `mechanism` | 44 | 1 | 98٪ |
| `model` | 44 | 1 | 98٪ |
| `principles` | 44 | 1 | 98٪ |
| `related` | 44 | 1 | 98٪ |
| `sources` | 44 | 1 | 98٪ |
| `summary` | 44 | 1 | 98٪ |
| `theorists` | 44 | 1 | 98٪ |
| `year` | 6 | 39 | 13٪ |
| `theorists_text` | 4 | 41 | 9٪ |
| `approach` | 1 | 44 | 2٪ |
| `card_type` | 1 | 44 | 2٪ |
| `category_id` | 1 | 44 | 2٪ |
| `code` | 1 | 44 | 2٪ |
| `comments_count` | 1 | 44 | 2٪ |
| `constructs` | 1 | 44 | 2٪ |
| `core_logic` | 1 | 44 | 2٪ |
| `created_at` | 1 | 44 | 2٪ |
| `critique` | 1 | 44 | 2٪ |
| `detailed_analysis` | 1 | 44 | 2٪ |
| `example` | 1 | 44 | 2٪ |
| `id` | 1 | 44 | 2٪ |
| `image_url` | 1 | 44 | 2٪ |
| `level` | 1 | 44 | 2٪ |
| `macro_domain` | 1 | 44 | 2٪ |
| `paradigm` | 1 | 44 | 2٪ |
| `published_at` | 1 | 44 | 2٪ |
| `recommended_sources` | 1 | 44 | 2٪ |
| `related_approaches` | 1 | 44 | 2٪ |
| `status` | 1 | 44 | 2٪ |
| `tags` | 1 | 44 | 2٪ |
| `updated_at` | 1 | 44 | 2٪ |
| `views` | 1 | 44 | 2٪ |
| `slug` | 0 | 45 | 0٪ |
| `title_en` | 0 | 45 | 0٪ |
| `title_fa` | 0 | 45 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 45 | 0٪ |

### `PERSON` (اندیشمند) — 39 رکورد، 90+: 25

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `issues` | 35 | 4 | 90٪ |
| `score` | 35 | 4 | 90٪ |
| `summary` | 35 | 4 | 90٪ |
| `academic_career` | 0 | 39 | 0٪ |
| `avatar_url` | 0 | 39 | 0٪ |
| `bio` | 0 | 39 | 0٪ |
| `cards_count` | 0 | 39 | 0٪ |
| `field` | 0 | 39 | 0٪ |
| `id` | 0 | 39 | 0٪ |
| `importance` | 0 | 39 | 0٪ |
| `main_theory` | 0 | 39 | 0٪ |
| `name` | 0 | 39 | 0٪ |
| `name_en` | 0 | 39 | 0٪ |
| `nationality` | 0 | 39 | 0٪ |
| `quote` | 0 | 39 | 0٪ |
| `slug` | 0 | 39 | 0٪ |
| `year` | 0 | 39 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 39 | 0٪ |

### `WORK` (اثر علمی) — 18 رکورد، 90+: 1

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `doi` | 18 | 0 | 100٪ |
| `duration_seconds` | 18 | 0 | 100٪ |
| `embed_url` | 18 | 0 | 100٪ |
| `file_url` | 18 | 0 | 100٪ |
| `isbn` | 18 | 0 | 100٪ |
| `publication_date` | 18 | 0 | 100٪ |
| `source_url` | 18 | 0 | 100٪ |
| `video_url` | 18 | 0 | 100٪ |
| `body` | 17 | 1 | 94٪ |
| `creator` | 17 | 1 | 94٪ |
| `issues` | 17 | 1 | 94٪ |
| `language` | 17 | 1 | 94٪ |
| `meta_description` | 17 | 1 | 94٪ |
| `meta_title` | 17 | 1 | 94٪ |
| `publication_year` | 17 | 1 | 94٪ |
| `publisher` | 17 | 1 | 94٪ |
| `resource_type` | 17 | 1 | 94٪ |
| `score` | 17 | 1 | 94٪ |
| `studies` | 17 | 1 | 94٪ |
| `title` | 17 | 1 | 94٪ |
| `aliases` | 16 | 2 | 89٪ |
| `applications` | 16 | 2 | 89٪ |
| `assumptions` | 16 | 2 | 89٪ |
| `critiques` | 16 | 2 | 89٪ |
| `evolution` | 16 | 2 | 89٪ |
| `field` | 16 | 2 | 89٪ |
| `history` | 16 | 2 | 89٪ |
| `key_concepts` | 16 | 2 | 89٪ |
| `main_problem` | 16 | 2 | 89٪ |
| `mechanism` | 16 | 2 | 89٪ |
| `model` | 16 | 2 | 89٪ |
| `principles` | 16 | 2 | 89٪ |
| `related` | 16 | 2 | 89٪ |
| `research_guide` | 16 | 2 | 89٪ |
| `sources` | 16 | 2 | 89٪ |
| `theorists` | 16 | 2 | 89٪ |
| `summary` | 15 | 3 | 83٪ |
| `approach` | 3 | 15 | 17٪ |
| `card_type` | 3 | 15 | 17٪ |
| `category_id` | 3 | 15 | 17٪ |
| `code` | 3 | 15 | 17٪ |
| `comments_count` | 3 | 15 | 17٪ |
| `constructs` | 3 | 15 | 17٪ |
| `core_logic` | 3 | 15 | 17٪ |
| `critique` | 3 | 15 | 17٪ |
| `detailed_analysis` | 3 | 15 | 17٪ |
| `example` | 3 | 15 | 17٪ |
| `level` | 3 | 15 | 17٪ |
| `macro_domain` | 3 | 15 | 17٪ |
| `paradigm` | 3 | 15 | 17٪ |
| `recommended_sources` | 3 | 15 | 17٪ |
| `related_approaches` | 3 | 15 | 17٪ |
| `tags` | 3 | 15 | 17٪ |
| `theorists_text` | 3 | 15 | 17٪ |
| `views` | 3 | 15 | 17٪ |
| `created_at` | 2 | 16 | 11٪ |
| `id` | 2 | 16 | 11٪ |
| `image_url` | 2 | 16 | 11٪ |
| `published_at` | 2 | 16 | 11٪ |
| `status` | 2 | 16 | 11٪ |
| `updated_at` | 2 | 16 | 11٪ |
| `title_fa` | 1 | 17 | 6٪ |
| `year` | 1 | 17 | 6٪ |
| `slug` | 0 | 18 | 0٪ |
| `title_en` | 0 | 18 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 18 | 0٪ |

### `HYPOTHESIS` (فرضیه) — 17 رکورد، 90+: 4

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `year` | 3 | 14 | 18٪ |
| `theorists_text` | 2 | 15 | 12٪ |
| `approach` | 0 | 17 | 0٪ |
| `card_type` | 0 | 17 | 0٪ |
| `category_id` | 0 | 17 | 0٪ |
| `code` | 0 | 17 | 0٪ |
| `comments_count` | 0 | 17 | 0٪ |
| `constructs` | 0 | 17 | 0٪ |
| `core_logic` | 0 | 17 | 0٪ |
| `created_at` | 0 | 17 | 0٪ |
| `critique` | 0 | 17 | 0٪ |
| `detailed_analysis` | 0 | 17 | 0٪ |
| `example` | 0 | 17 | 0٪ |
| `id` | 0 | 17 | 0٪ |
| `image_url` | 0 | 17 | 0٪ |
| `level` | 0 | 17 | 0٪ |
| `macro_domain` | 0 | 17 | 0٪ |
| `paradigm` | 0 | 17 | 0٪ |
| `published_at` | 0 | 17 | 0٪ |
| `recommended_sources` | 0 | 17 | 0٪ |
| `related_approaches` | 0 | 17 | 0٪ |
| `slug` | 0 | 17 | 0٪ |
| `status` | 0 | 17 | 0٪ |
| `tags` | 0 | 17 | 0٪ |
| `title_en` | 0 | 17 | 0٪ |
| `title_fa` | 0 | 17 | 0٪ |
| `updated_at` | 0 | 17 | 0٪ |
| `views` | 0 | 17 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 17 | 0٪ |

### `METHOD` (روش پژوهش) — 12 رکورد، 90+: 3

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `studies` | 12 | 0 | 100٪ |
| `research_guide` | 11 | 1 | 92٪ |
| `aliases` | 10 | 2 | 83٪ |
| `applications` | 10 | 2 | 83٪ |
| `assumptions` | 10 | 2 | 83٪ |
| `critiques` | 10 | 2 | 83٪ |
| `evolution` | 10 | 2 | 83٪ |
| `field` | 10 | 2 | 83٪ |
| `history` | 10 | 2 | 83٪ |
| `issues` | 10 | 2 | 83٪ |
| `key_concepts` | 10 | 2 | 83٪ |
| `main_problem` | 10 | 2 | 83٪ |
| `mechanism` | 10 | 2 | 83٪ |
| `model` | 10 | 2 | 83٪ |
| `principles` | 10 | 2 | 83٪ |
| `related` | 10 | 2 | 83٪ |
| `score` | 10 | 2 | 83٪ |
| `sources` | 10 | 2 | 83٪ |
| `summary` | 10 | 2 | 83٪ |
| `theorists` | 10 | 2 | 83٪ |
| `approach` | 2 | 10 | 17٪ |
| `card_type` | 2 | 10 | 17٪ |
| `category_id` | 2 | 10 | 17٪ |
| `code` | 2 | 10 | 17٪ |
| `comments_count` | 2 | 10 | 17٪ |
| `constructs` | 2 | 10 | 17٪ |
| `core_logic` | 2 | 10 | 17٪ |
| `created_at` | 2 | 10 | 17٪ |
| `critique` | 2 | 10 | 17٪ |
| `detailed_analysis` | 2 | 10 | 17٪ |
| `example` | 2 | 10 | 17٪ |
| `id` | 2 | 10 | 17٪ |
| `image_url` | 2 | 10 | 17٪ |
| `level` | 2 | 10 | 17٪ |
| `macro_domain` | 2 | 10 | 17٪ |
| `paradigm` | 2 | 10 | 17٪ |
| `published_at` | 2 | 10 | 17٪ |
| `recommended_sources` | 2 | 10 | 17٪ |
| `related_approaches` | 2 | 10 | 17٪ |
| `status` | 2 | 10 | 17٪ |
| `tags` | 2 | 10 | 17٪ |
| `theorists_text` | 2 | 10 | 17٪ |
| `updated_at` | 2 | 10 | 17٪ |
| `views` | 2 | 10 | 17٪ |
| `year` | 1 | 11 | 8٪ |
| `slug` | 0 | 12 | 0٪ |
| `title_en` | 0 | 12 | 0٪ |
| `title_fa` | 0 | 12 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 12 | 0٪ |

### `PARADIGM` (پارادایم) — 9 رکورد، 90+: 1

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `year` | 2 | 7 | 22٪ |
| `approach` | 0 | 9 | 0٪ |
| `card_type` | 0 | 9 | 0٪ |
| `category_id` | 0 | 9 | 0٪ |
| `code` | 0 | 9 | 0٪ |
| `comments_count` | 0 | 9 | 0٪ |
| `constructs` | 0 | 9 | 0٪ |
| `core_logic` | 0 | 9 | 0٪ |
| `created_at` | 0 | 9 | 0٪ |
| `critique` | 0 | 9 | 0٪ |
| `detailed_analysis` | 0 | 9 | 0٪ |
| `example` | 0 | 9 | 0٪ |
| `id` | 0 | 9 | 0٪ |
| `image_url` | 0 | 9 | 0٪ |
| `level` | 0 | 9 | 0٪ |
| `macro_domain` | 0 | 9 | 0٪ |
| `paradigm` | 0 | 9 | 0٪ |
| `published_at` | 0 | 9 | 0٪ |
| `recommended_sources` | 0 | 9 | 0٪ |
| `related_approaches` | 0 | 9 | 0٪ |
| `slug` | 0 | 9 | 0٪ |
| `status` | 0 | 9 | 0٪ |
| `tags` | 0 | 9 | 0٪ |
| `theorists_text` | 0 | 9 | 0٪ |
| `title_en` | 0 | 9 | 0٪ |
| `title_fa` | 0 | 9 | 0٪ |
| `updated_at` | 0 | 9 | 0٪ |
| `views` | 0 | 9 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 9 | 0٪ |

### `SCHOOL` (مکتب) — 8 رکورد، 90+: 1

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `studies` | 8 | 0 | 100٪ |
| `aliases` | 7 | 1 | 88٪ |
| `applications` | 7 | 1 | 88٪ |
| `assumptions` | 7 | 1 | 88٪ |
| `critiques` | 7 | 1 | 88٪ |
| `evolution` | 7 | 1 | 88٪ |
| `field` | 7 | 1 | 88٪ |
| `history` | 7 | 1 | 88٪ |
| `issues` | 7 | 1 | 88٪ |
| `key_concepts` | 7 | 1 | 88٪ |
| `main_problem` | 7 | 1 | 88٪ |
| `mechanism` | 7 | 1 | 88٪ |
| `model` | 7 | 1 | 88٪ |
| `principles` | 7 | 1 | 88٪ |
| `related` | 7 | 1 | 88٪ |
| `research_guide` | 7 | 1 | 88٪ |
| `score` | 7 | 1 | 88٪ |
| `sources` | 7 | 1 | 88٪ |
| `summary` | 7 | 1 | 88٪ |
| `theorists` | 7 | 1 | 88٪ |
| `approach` | 1 | 7 | 12٪ |
| `card_type` | 1 | 7 | 12٪ |
| `category_id` | 1 | 7 | 12٪ |
| `code` | 1 | 7 | 12٪ |
| `comments_count` | 1 | 7 | 12٪ |
| `constructs` | 1 | 7 | 12٪ |
| `core_logic` | 1 | 7 | 12٪ |
| `created_at` | 1 | 7 | 12٪ |
| `critique` | 1 | 7 | 12٪ |
| `detailed_analysis` | 1 | 7 | 12٪ |
| `example` | 1 | 7 | 12٪ |
| `id` | 1 | 7 | 12٪ |
| `image_url` | 1 | 7 | 12٪ |
| `level` | 1 | 7 | 12٪ |
| `macro_domain` | 1 | 7 | 12٪ |
| `paradigm` | 1 | 7 | 12٪ |
| `published_at` | 1 | 7 | 12٪ |
| `recommended_sources` | 1 | 7 | 12٪ |
| `related_approaches` | 1 | 7 | 12٪ |
| `status` | 1 | 7 | 12٪ |
| `tags` | 1 | 7 | 12٪ |
| `theorists_text` | 1 | 7 | 12٪ |
| `updated_at` | 1 | 7 | 12٪ |
| `views` | 1 | 7 | 12٪ |
| `year` | 1 | 7 | 12٪ |
| `slug` | 0 | 8 | 0٪ |
| `title_en` | 0 | 8 | 0٪ |
| `title_fa` | 0 | 8 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 8 | 0٪ |

### `CONCEPT` (مفهوم) — 8 رکورد، 90+: 1

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `year` | 1 | 7 | 12٪ |
| `approach` | 0 | 8 | 0٪ |
| `card_type` | 0 | 8 | 0٪ |
| `category_id` | 0 | 8 | 0٪ |
| `code` | 0 | 8 | 0٪ |
| `comments_count` | 0 | 8 | 0٪ |
| `constructs` | 0 | 8 | 0٪ |
| `core_logic` | 0 | 8 | 0٪ |
| `created_at` | 0 | 8 | 0٪ |
| `critique` | 0 | 8 | 0٪ |
| `detailed_analysis` | 0 | 8 | 0٪ |
| `example` | 0 | 8 | 0٪ |
| `id` | 0 | 8 | 0٪ |
| `image_url` | 0 | 8 | 0٪ |
| `level` | 0 | 8 | 0٪ |
| `macro_domain` | 0 | 8 | 0٪ |
| `paradigm` | 0 | 8 | 0٪ |
| `published_at` | 0 | 8 | 0٪ |
| `recommended_sources` | 0 | 8 | 0٪ |
| `related_approaches` | 0 | 8 | 0٪ |
| `slug` | 0 | 8 | 0٪ |
| `status` | 0 | 8 | 0٪ |
| `tags` | 0 | 8 | 0٪ |
| `theorists_text` | 0 | 8 | 0٪ |
| `title_en` | 0 | 8 | 0٪ |
| `title_fa` | 0 | 8 | 0٪ |
| `updated_at` | 0 | 8 | 0٪ |
| `views` | 0 | 8 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 8 | 0٪ |

### `LAW` (قانون علمی) — 4 رکورد، 90+: 0

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `approach` | 0 | 4 | 0٪ |
| `card_type` | 0 | 4 | 0٪ |
| `category_id` | 0 | 4 | 0٪ |
| `code` | 0 | 4 | 0٪ |
| `comments_count` | 0 | 4 | 0٪ |
| `constructs` | 0 | 4 | 0٪ |
| `core_logic` | 0 | 4 | 0٪ |
| `created_at` | 0 | 4 | 0٪ |
| `critique` | 0 | 4 | 0٪ |
| `detailed_analysis` | 0 | 4 | 0٪ |
| `example` | 0 | 4 | 0٪ |
| `id` | 0 | 4 | 0٪ |
| `image_url` | 0 | 4 | 0٪ |
| `level` | 0 | 4 | 0٪ |
| `macro_domain` | 0 | 4 | 0٪ |
| `paradigm` | 0 | 4 | 0٪ |
| `published_at` | 0 | 4 | 0٪ |
| `recommended_sources` | 0 | 4 | 0٪ |
| `related_approaches` | 0 | 4 | 0٪ |
| `slug` | 0 | 4 | 0٪ |
| `status` | 0 | 4 | 0٪ |
| `tags` | 0 | 4 | 0٪ |
| `theorists_text` | 0 | 4 | 0٪ |
| `title_en` | 0 | 4 | 0٪ |
| `title_fa` | 0 | 4 | 0٪ |
| `updated_at` | 0 | 4 | 0٪ |
| `views` | 0 | 4 | 0٪ |
| `year` | 0 | 4 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 4 | 0٪ |

### `STRATEGY` (استراتژی) — 3 رکورد، 90+: 1

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `approach` | 0 | 3 | 0٪ |
| `card_type` | 0 | 3 | 0٪ |
| `category_id` | 0 | 3 | 0٪ |
| `code` | 0 | 3 | 0٪ |
| `comments_count` | 0 | 3 | 0٪ |
| `constructs` | 0 | 3 | 0٪ |
| `core_logic` | 0 | 3 | 0٪ |
| `created_at` | 0 | 3 | 0٪ |
| `critique` | 0 | 3 | 0٪ |
| `detailed_analysis` | 0 | 3 | 0٪ |
| `example` | 0 | 3 | 0٪ |
| `id` | 0 | 3 | 0٪ |
| `image_url` | 0 | 3 | 0٪ |
| `level` | 0 | 3 | 0٪ |
| `macro_domain` | 0 | 3 | 0٪ |
| `paradigm` | 0 | 3 | 0٪ |
| `published_at` | 0 | 3 | 0٪ |
| `recommended_sources` | 0 | 3 | 0٪ |
| `related_approaches` | 0 | 3 | 0٪ |
| `slug` | 0 | 3 | 0٪ |
| `status` | 0 | 3 | 0٪ |
| `tags` | 0 | 3 | 0٪ |
| `theorists_text` | 0 | 3 | 0٪ |
| `title_en` | 0 | 3 | 0٪ |
| `title_fa` | 0 | 3 | 0٪ |
| `updated_at` | 0 | 3 | 0٪ |
| `views` | 0 | 3 | 0٪ |
| `year` | 0 | 3 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 3 | 0٪ |

### `METHODOLOGY` (روش‌شناسی) — 3 رکورد، 90+: 0

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `year` | 1 | 2 | 33٪ |
| `approach` | 0 | 3 | 0٪ |
| `card_type` | 0 | 3 | 0٪ |
| `category_id` | 0 | 3 | 0٪ |
| `code` | 0 | 3 | 0٪ |
| `comments_count` | 0 | 3 | 0٪ |
| `constructs` | 0 | 3 | 0٪ |
| `core_logic` | 0 | 3 | 0٪ |
| `created_at` | 0 | 3 | 0٪ |
| `critique` | 0 | 3 | 0٪ |
| `detailed_analysis` | 0 | 3 | 0٪ |
| `example` | 0 | 3 | 0٪ |
| `id` | 0 | 3 | 0٪ |
| `image_url` | 0 | 3 | 0٪ |
| `level` | 0 | 3 | 0٪ |
| `macro_domain` | 0 | 3 | 0٪ |
| `paradigm` | 0 | 3 | 0٪ |
| `published_at` | 0 | 3 | 0٪ |
| `recommended_sources` | 0 | 3 | 0٪ |
| `related_approaches` | 0 | 3 | 0٪ |
| `slug` | 0 | 3 | 0٪ |
| `status` | 0 | 3 | 0٪ |
| `tags` | 0 | 3 | 0٪ |
| `theorists_text` | 0 | 3 | 0٪ |
| `title_en` | 0 | 3 | 0٪ |
| `title_fa` | 0 | 3 | 0٪ |
| `updated_at` | 0 | 3 | 0٪ |
| `views` | 0 | 3 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 3 | 0٪ |

### `SUBFIELD` (حوزه تخصصی) — 1 رکورد، 90+: 0

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `approach` | 0 | 1 | 0٪ |
| `card_type` | 0 | 1 | 0٪ |
| `category_id` | 0 | 1 | 0٪ |
| `code` | 0 | 1 | 0٪ |
| `comments_count` | 0 | 1 | 0٪ |
| `constructs` | 0 | 1 | 0٪ |
| `core_logic` | 0 | 1 | 0٪ |
| `created_at` | 0 | 1 | 0٪ |
| `critique` | 0 | 1 | 0٪ |
| `detailed_analysis` | 0 | 1 | 0٪ |
| `example` | 0 | 1 | 0٪ |
| `id` | 0 | 1 | 0٪ |
| `image_url` | 0 | 1 | 0٪ |
| `level` | 0 | 1 | 0٪ |
| `macro_domain` | 0 | 1 | 0٪ |
| `paradigm` | 0 | 1 | 0٪ |
| `published_at` | 0 | 1 | 0٪ |
| `recommended_sources` | 0 | 1 | 0٪ |
| `related_approaches` | 0 | 1 | 0٪ |
| `slug` | 0 | 1 | 0٪ |
| `status` | 0 | 1 | 0٪ |
| `tags` | 0 | 1 | 0٪ |
| `theorists_text` | 0 | 1 | 0٪ |
| `title_en` | 0 | 1 | 0٪ |
| `title_fa` | 0 | 1 | 0٪ |
| `updated_at` | 0 | 1 | 0٪ |
| `views` | 0 | 1 | 0٪ |
| `year` | 0 | 1 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 1 | 0٪ |

### `INDICATOR` (شاخص) — 1 رکورد، 90+: 0

| فیلد | خالی | پر | درصد خالی |
|---|---:|---:|---:|
| `approach` | 0 | 1 | 0٪ |
| `card_type` | 0 | 1 | 0٪ |
| `category_id` | 0 | 1 | 0٪ |
| `code` | 0 | 1 | 0٪ |
| `comments_count` | 0 | 1 | 0٪ |
| `constructs` | 0 | 1 | 0٪ |
| `core_logic` | 0 | 1 | 0٪ |
| `created_at` | 0 | 1 | 0٪ |
| `critique` | 0 | 1 | 0٪ |
| `detailed_analysis` | 0 | 1 | 0٪ |
| `example` | 0 | 1 | 0٪ |
| `id` | 0 | 1 | 0٪ |
| `image_url` | 0 | 1 | 0٪ |
| `level` | 0 | 1 | 0٪ |
| `macro_domain` | 0 | 1 | 0٪ |
| `paradigm` | 0 | 1 | 0٪ |
| `published_at` | 0 | 1 | 0٪ |
| `recommended_sources` | 0 | 1 | 0٪ |
| `related_approaches` | 0 | 1 | 0٪ |
| `slug` | 0 | 1 | 0٪ |
| `status` | 0 | 1 | 0٪ |
| `tags` | 0 | 1 | 0٪ |
| `theorists_text` | 0 | 1 | 0٪ |
| `title_en` | 0 | 1 | 0٪ |
| `title_fa` | 0 | 1 | 0٪ |
| `updated_at` | 0 | 1 | 0٪ |
| `views` | 0 | 1 | 0٪ |
| `year` | 0 | 1 | 0٪ |
| `canonical_name_en` (ستون جدا) | 0 | 1 | 0٪ |

## ۴. فاکت‌ها (13 بُعد)

| بُعد فاکت | برچسب | مقدار در این seed |
|---|---|---:|
| `discipline` | رشته علمی | — خالی — |
| `subfield` | حوزه تخصصی | — خالی — |
| `time_frame` | دوره زمانی | — خالی — |
| `geographic_region` | منطقه جغرافیایی | — خالی — |
| `approach` | رویکرد | — خالی — |
| `epistemology` | معرفت‌شناسی | — خالی — |
| `ontology` | هستی‌شناسی | — خالی — |
| `data_type` | نوع داده | — خالی — |
| `theoretical_tradition` | سنت نظری | — خالی — |
| `research_context` | زمینه پژوهش | — خالی — |
| `analysis_scale` | مقیاس تحلیل | 714 |
| `application_domain` | حوزه کاربرد | — خالی — |
| `qualifies_for` | مناسب برای | — خالی — |

> فقط `analysis_scale` از فیلد `level` کارت پر شده (714). **12 بُعد دیگر کامل خالی‌اند** — این‌ها کار محتوایی واقعی دارند.

## ۵. کارت‌های اولویت (امتیاز 90+)

مجموع: **183** — تفکیک نوع:

| نوع | 90+ | از کل |
|---|---:|---:|
| `THEORY` | 106 | 479 |
| `MODEL` | 31 | 124 |
| `PERSON` | 25 | 39 |
| `FRAMEWORK` | 9 | 45 |
| `HYPOTHESIS` | 4 | 17 |
| `METHOD` | 3 | 12 |
| `WORK` | 1 | 18 |
| `STRATEGY` | 1 | 3 |
| `SCHOOL` | 1 | 8 |
| `PARADIGM` | 1 | 9 |
| `CONCEPT` | 1 | 8 |

### نظریه‌پردازان 90+ (25)

| کد | نام | نمره | وضعیت ویرایشی |
|---|---|---:|---|
| th-1 | ماکسول مک‌کامبز (Maxwell McCombs) | 95 | approved |
| th-15 | والتر لیپمن (Walter Lippmann) | 95 | approved |
| th-19 | تئودور آدورنو (Theodor Adorno) | 95 | approved |
| th-22 | هنری جنکینز (Henry Jenkins) | 95 | approved |
| th-26 | دیوید آلتهاید (David Altheide) | 95 | approved |
| th-28 | ایلیا کتز (Elihu Katz) | 95 | approved |
| th-31 | نیل پستمن (Neil Postman) | 95 | approved |
| th-33 | یوهان گالتونگ (Johan Galtung) | 95 | approved |
| th-38 | دیوید هزمندهالگ (David Hesmondhalgh) | 95 | draft |
| th-5 | استوارت هال (Stuart Hall) | 95 | approved |
| th-7 | الیزابت نوئل-نویمان (Elisabeth Noelle-Neumann) | 95 | approved |
| th-16 | کرت لوین (Kurt Lewin) | 94 | draft |
| th-27 | رولان بارت (Roland Barthes) | 94 | draft |
| th-32 | دالاس اسمایث (Dallas Smythe) | 94 | draft |
| th-11 | مانوئل کاستلز (Manuel Castells) | 92 | approved |
| th-17 | پیر بوردیو (Pierre Bourdieu) | 92 | approved |
| th-20 | ملوین دفلور (Melvin DeFleur) | 92 | approved |
| th-24 | نیک کولدری (Nick Couldry) | 92 | approved |
| th-3 | مارشال مک‌لوهان (Marshall McLuhan) | 92 | approved |
| th-30 | دیوید منینگ وایت (David Manning White) | 92 | approved |
| th-34 | ریچارد اِی. پتی (Richard E. Petty) | 92 | approved |
| th-35 | کارل وایک (Karl Weick) | 92 | approved |
| th-37 | زیزی پاپاچاریسی (Zizi Papacharissi) | 92 | approved |
| th-39 | دیوید نیبورگ (David Nieborg) | 92 | approved |
| th-9 | پل لازارسفلد (Paul Lazarsfeld) | 92 | approved |

### کارت‌های 90+ بدون کد MC (1)

- `card-540` — مدل هلیکال (مارپیچ) (نمره 92)

## ۶. کارت‌های بدون کد MC (17) — کد = `source_id`

| کد فعلی | عنوان | نمره | 90+ |
|---|---|---:|:-:|
| `card-212` | الگوی بومرنگ و شبکه‌های حمایتی فراملی | 88 |  |
| `card-248` | نظریه‌ی نقض انتظار | 88 |  |
| `card-322` | نظریه توازن | 88 |  |
| `card-323` | نظریه واکنش روانی | 88 |  |
| `card-33` | اثرات شبکه‌ای | 88 |  |
| `card-388` | سرمایه انسانی | 88 |  |
| `card-391` | نظریه X و Y | 88 |  |
| `card-403` | نظم نوین جهانی اطلاعات و ارتباطات | 87 |  |
| `card-430` | جماعت‌های شبکه‌ای | 88 |  |
| `card-456` | نظریه بازی در فرهنگ و ارتباطات | 88 |  |
| `card-479` | فلسفه تکنولوژی | 88 |  |
| `card-540` | مدل هلیکال (مارپیچ) | 92 | ✅ |
| `card-581` | اثر بومرنگ | 88 |  |
| `card-59` | پاسخگویی الگوریتمی | 88 |  |
| `card-71` | نظریه داده‌بنیاد | 88 |  |
| `card-77` | روش Q | 88 |  |
| `card-90` | جبر فناورانه در برابر ساخت اجتماعی فناوری | 88 |  |

## ۷. موجودیت‌های نیازمند بازبینیِ طبقه‌بندی

| کد | عنوان | نوع فعلی | دلیل |
|---|---|---|---|
| MC-054 | اصل آسیب | `CONCEPT` | از «اصل (Principle)» |
| MC-057 | دعاوی راهبردی علیه مشارکت عمومی | `CONCEPT` | از «پدیده (Phenomenon)» |
| MC-080 | علوم اجتماعی محاسباتی | `SUBFIELD` | از «حوزه مطالعاتی (Field of Study)» |
| MC-168 | بوم مدل کسب‌وکار | `FRAMEWORK` | از «چارچوب اجرایی (Operational Framework)» |
| MC-276 | الگوهای فریبنده | `MODEL` | از «الگو (Pattern)» |
| MC-311 | سایبرنتیک | `THEORY` | از «بدون content_type» |
| MC-412 | مدل پذیرش فناوری | `CONCEPT` | از «پدیده (Phenomenon)» |
| MC-421 | روزنامه‌نگاری خودکار و الگوریتمی | `MODEL` | از «بدون content_type» |
| MC-557 | سندرم جهان خشن | `THEORY` | از «بدون content_type» |

## ۸. نظریه‌پردازان (39) — PERSON

داده‌ی واقعی از جدول `theorists` سایت. `main_theory` متن آزادی است (مثلاً «نظریه تعیین و برجسته‌سازی دستور کار (Agenda-Setting)»).

| کد | نام | name_en | نمره | وضعیت |
|---|---|---|---:|---|
| th-1 | ماکسول مک‌کامبز | Maxwell McCombs | 95 | approved |
| th-15 | والتر لیپمن | Walter Lippmann | 95 | approved |
| th-19 | تئودور آدورنو | Theodor Adorno | 95 | approved |
| th-22 | هنری جنکینز | Henry Jenkins | 95 | approved |
| th-26 | دیوید آلتهاید | David Altheide | 95 | approved |
| th-28 | ایلیا کتز | Elihu Katz | 95 | approved |
| th-31 | نیل پستمن | Neil Postman | 95 | approved |
| th-33 | یوهان گالتونگ | Johan Galtung | 95 | approved |
| th-38 | دیوید هزمندهالگ | David Hesmondhalgh | 95 | draft |
| th-5 | استوارت هال | Stuart Hall | 95 | approved |
| th-7 | الیزابت نوئل-نویمان | Elisabeth Noelle-Neumann | 95 | approved |
| th-16 | کرت لوین | Kurt Lewin | 94 | draft |
| th-27 | رولان بارت | Roland Barthes | 94 | draft |
| th-32 | دالاس اسمایث | Dallas Smythe | 94 | draft |
| th-11 | مانوئل کاستلز | Manuel Castells | 92 | approved |
| th-17 | پیر بوردیو | Pierre Bourdieu | 92 | approved |
| th-20 | ملوین دفلور | Melvin DeFleur | 92 | approved |
| th-24 | نیک کولدری | Nick Couldry | 92 | approved |
| th-3 | مارشال مک‌لوهان | Marshall McLuhan | 92 | approved |
| th-30 | دیوید منینگ وایت | David Manning White | 92 | approved |
| th-34 | ریچارد اِی. پتی | Richard E. Petty | 92 | approved |
| th-35 | کارل وایک | Karl Weick | 92 | approved |
| th-37 | زیزی پاپاچاریسی | Zizi Papacharissi | 92 | approved |
| th-39 | دیوید نیبورگ | David Nieborg | 92 | approved |
| th-9 | پل لازارسفلد | Paul Lazarsfeld | 92 | approved |
| th-8 | اروینگ گافمن | Erving Goffman | 88 | draft |
| th-13 | شانتو اینگار | Shanto Iyengar | 85 | draft |
| th-12 | رابرت انتمن | Robert Entman | 84 | draft |
| th-14 | دنیس مک‌کوایل | Denis McQuail | 84 | draft |
| th-10 | هارولد لاسول | Harold Lasswell | 82 | draft |
| th-21 | ساندرا بال‌روکیچ | Sandra Ball-Rokeach | 82 | draft |
| th-23 | استیگ هیارد | Stig Hjarvard | 82 | draft |
| th-4 | یورگن هابرماس | Jürgen Habermas | 82 | draft |
| th-6 | جورج گربنر | George Gerbner | 80 | draft |
| th-18 | ژان بودریار | Jean Baudrillard | 78 | draft |
| th-25 | دانیل کانمن | Daniel Kahneman | 78 | draft |
| th-29 | فیلیپ تیچنور | Philip Tichenor | 78 | draft |
| th-36 | لوسی کونگ | Lucy Kung | 76 | draft |
| th-2 | دونالد شاو | Donald Shaw | 68 | draft |

> **شکاف:** روابط ساختاریافته‌ی `DEVELOPED_BY`/`PROPOSED_BY` بین این 39 شخص و نظریه‌هایشان **ساخته نشده** — چون `main_theory` متن آزاد است و parse نام‌ها ریسک داده‌ی ساختگی دارد. کار محتوایی: تطبیق دستی/نیمه‌خودکار هر `main_theory` با موجودیت مربوطه.

## ۹. روابط (23 رابطه‌ی واقعی از متن)

| مبدأ | مقصد | نوع | وضعیت |
|---|---|---|---|
| `MC-241` | `MC-243` | `RELATED_TO` | unverified/needs_review |
| `MC-246` | `MC-245` | `RELATED_TO` | unverified/needs_review |
| `MC-250` | `MC-052` | `RELATED_TO` | unverified/needs_review |
| `MC-251` | `MC-115` | `RELATED_TO` | unverified/needs_review |
| `MC-251` | `MC-255` | `RELATED_TO` | unverified/needs_review |
| `MC-252` | `MC-251` | `RELATED_TO` | unverified/needs_review |
| `MC-254` | `MC-037` | `RELATED_TO` | unverified/needs_review |
| `MC-255` | `MC-086` | `RELATED_TO` | unverified/needs_review |
| `MC-255` | `MC-183` | `RELATED_TO` | unverified/needs_review |
| `MC-255` | `MC-238` | `RELATED_TO` | unverified/needs_review |
| `MC-255` | `MC-251` | `RELATED_TO` | unverified/needs_review |
| `MC-255` | `MC-257` | `RELATED_TO` | unverified/needs_review |
| `MC-256` | `MC-047` | `RELATED_TO` | unverified/needs_review |
| `MC-257` | `MC-012` | `RELATED_TO` | unverified/needs_review |
| `MC-257` | `MC-113` | `RELATED_TO` | unverified/needs_review |
| `MC-258` | `MC-116` | `RELATED_TO` | unverified/needs_review |
| `MC-259` | `MC-038` | `RELATED_TO` | unverified/needs_review |
| `MC-262` | `MC-034` | `RELATED_TO` | unverified/needs_review |
| `MC-277` | `MC-280` | `RELATED_TO` | unverified/needs_review |
| `MC-285` | `MC-118` | `RELATED_TO` | unverified/needs_review |
| `MC-286` | `MC-222` | `RELATED_TO` | unverified/needs_review |
| `MC-289` | `MC-229` | `RELATED_TO` | unverified/needs_review |
| `MC-299` | `MC-119` | `RELATED_TO` | unverified/needs_review |

> همه از **ارجاع صریح `MC-xxx` در متن** استخراج شده‌اند (نه حدس). `assertion_status=unverified` چون هنوز توسط دانشمند تأیید نشده.

- کارت‌های مبدأ این 23 رابطه: 17 کارت (`MC-241`, `MC-246`, `MC-250`, `MC-251`, `MC-252`, `MC-254`، …)

## ۱۰. خلاصه‌ی شکاف‌های محتوایی (چه کاری باید انجام شود)

| # | شکاف | ابعاد | اولویت |
|---|---|---|---|
| 1 | **فیلدهای «عمیق» کارت تقریباً خالی‌اند** (در JSON برخی کارت‌ها هست، ولی برای اکثر پر نشده) | نظریه: 20 فیلد (مثل studies, issues, score, research_guide, evolution…)؛ مدل: 19 فیلد (مثل research_guide, issues, score, aliases, applications…)؛ چارچوب نظری: 16 فیلد (مثل aliases, applications, assumptions, critiques, evolution…)؛ روش پژوهش: 1 فیلد (مثل research_guide)؛ اثر علمی: 28 فیلد (مثل body, creator, issues, language, meta_description…) | بالا (اول 90+) |
| 2 | 12 بُعد فاکتِ خالی (discipline, subfield, time_frame, geographic_region, approach, epistemology, ontology, data_type, theoretical_tradition, research_context, application_domain, qualifies_for) | همه‌ی 771 رکورد | بالا (از 90+ شروع) |
| 3 | روابط بین‌موجودیتی تقریباً خالی | فقط 23 RELATED_TO از 771 موجودیت | بالا |
| 4 | 21 نوع موجودیتِ خالی (از 36) — مثل `PROPOSITION`, `CONSTRUCT`, `VARIABLE`, `RESEARCH_STUDY`, `FINDING`, `DISCIPLINE`, `TRADITION` | فازبندی‌شده | متوسط |
| 5 | `main_theory`ِ 39 نظریه‌پرداز متن آزاد است → روابط `PROPOSED_BY`/`DEVELOPED_BY` ندارند | 39 PERSON | بالا |
| 6 | `recommended_sources` متن آزاد است (714 کارت) → `kb_sources`/`kb_citations` خالی | 714 | متوسط |
| 7 | `kb_evidence` کامل خالی (هیچ شاهد ساختاریافته‌ای) | همه | متوسط (فاز 2) |
| 8 | 6 کارتِ نیازمند بازبینیِ نوع + 3 کارتِ بدون content_type (مجموع 9، جدول §7) | 9 | بالا (سریع) |
| 9 | 17 کارت بدون کد MC (کد فعلی = source_id) | 17 | متوسط |
| 10 | وضعیت علمی همه `unassessed` — ارزیابی علمی ندارد | 771 | فاز 2 (انسانی/داوری) |

## ۱۱. نقشه‌ی seed (برگشت‌پذیر و شفاف)

| مبدأ | مقصد | قاعده |
|---|---|---|
| `editorial_documents.current_json` | `kb_entities.properties` | کل JSON بدون هیچ حذفی + `quality_score` + `source` (provenance) + `priority_90plus` |
| `content_type` | `entity_type` | map مستند (§2)؛ 8 مورد flagged `needs_review` |
| `title_fa` / `name` | `canonical_name_fa` | — |
| `title_en` / `name_en` | `canonical_name_en` | خالی بماند اگر در مبدأ خالی بوده |
| `slug` | `slug` | همه 771 یکتا بودند |
| `code` (MC-xxx) / `id` (th-x) / `source_id` | `code` | 17 کارت بدون کد → `source_id` |
| `workflow_status` | `status_workflow` | approved→approved, draft_ready→draft |
| (هیچ‌چیز) | `status_scientific` | همه `unassessed` (ساختگی نیست) |
| `level` | فاکت `analysis_scale` | خرد→MICRO, میانی→MESO, کلان→MACRO |
| ارجاع صریح `MC-xxx` در متن | `kb_relations.RELATED_TO` | `unverified`/`needs_review` |
| `source_table`+`source_id` | `kb_entity_links` | confidence=verified |
| کتاب `library_resources` | `kb_entities.WORK` + `kb_sources.BOOK` | `work_id` وصل است |

## ۱۲. دستورات بازبینی (SQL آماده)

```sql
USE resaname_kb_final_inspect;
-- اولویت 90+ به تفکیک نوع
SELECT entity_type, COUNT(*) FROM kb_entities
WHERE JSON_EXTRACT(properties,'$.priority_90plus')=true GROUP BY entity_type;
-- نمونه‌ی یک نوع با فیلدهای خالی
SELECT code, canonical_name_fa, canonical_name_en,
  JSON_EXTRACT(properties,'$.year') AS year,
  JSON_EXTRACT(properties,'$.paradigm') AS paradigm,
  JSON_EXTRACT(properties,'$.core_logic') IS NULL AS no_core_logic
FROM kb_entities WHERE entity_type='THEORY' AND JSON_EXTRACT(properties,'$.priority_90plus')=true LIMIT 20;
-- کارت‌های 90+ که فیلدِ centralِ نوعشان خالی است (مثال: THEORY بدون core_logic)
SELECT e.code, e.canonical_name_fa FROM kb_entities e
WHERE e.entity_type='THEORY' AND JSON_EXTRACT(e.properties,'$.priority_90plus')=true
  AND (JSON_EXTRACT(e.properties,'$.core_logic') IS NULL OR JSON_EXTRACT(e.properties,'$.core_logic')='');
```

---
تولیدشده به‌صورت خودکار از `resaname_kb_final_inspect` در 2026-11-15 20:19