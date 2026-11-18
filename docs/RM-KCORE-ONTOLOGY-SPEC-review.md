# بازبینی مهندسی — سند RM-KCORE-ONTOLOGY-SPEC v1.0.0

**بازبین:** مهندس ارشد داده + متخصص روش‌شناسی/محتوای پژوهشی
**تاریخ:** ۱۵ نوامبر ۲۰۲۶
**مبنای بازبینی:** تطبیق مستقیم با کد و دیتابیس فعلی (نه نقد صرفاً نظری)

---

## ۰. حقیقت فعلی (آنچه واقعاً در پروژه هست)

برای اینکه نظرم مستند باشد، اول وضعیت فعلی را از خود کد ثبت می‌کنم:

| لایه | فایل/جدول | آنچه **امروز** هست |
|---|---|---|
| انواع موجودیت | `app/Knowledge/EntityTypes.php` (131 سطر) | 28 نوع؛ هرکدام فقط 4 فیلد: `[label_fa, label_en, domain, description]`. **هیچ schema فیلدی برای properties وجود ندارد.** |
| `properties` | ستون `kb_entities.properties` (JSON) | JSON آزاد؛ بدون نوع، بدون cardinality، بدون اعتبارسنجی. در عمل تقریباً همه null. |
| روابط | `app/Knowledge/RelationTypes.php` (74 سطر) | 20 رابطه؛ هرکدام `[label_fa, label_en, description, symmetric]`. **هیچ domain/range ندارد.** |
| فاکت‌ها | `app/Knowledge/FacetTypes.php` (65 سطر) | 9 بُعد؛ مقادیر **متن آزاد** (`value_fa VARCHAR(300)`). **واژگان کنترل‌شده نیست.** |
| موجودیت | `kb_entities` | `code, slug, title_fa, title_en, entity_type, domain, parent_code, abstract, description, properties(JSON), status(enum: draft/active/archived), created_at, updated_at`. **بدون version، created_by/updated_by، visibility، aliases، external_ids، provenance.** |
| روابط (جدول) | `kb_relations` | `source_code, target_code, relation_type, note, weight, confidence(enum: verified/suggested/ai_draft), status, created_by, ...`. **بدون valid_from/until، بدون evidence، بدون qualification، بدون امتیاز عددی.** |
| فاکت‌ها (جدول) | `kb_entity_facets` | `entity_code, facet_code, value_fa, value_en` — **متن آزاد.** |
| داده | آمار زنده | 581 موجودیت، 384 رابطه، 1886 فاکت، 71 لینک، 0 چکیده‌ی در انتظار. `kb_validate` → سالم. |

**نتیجه‌ی کلیدی:** سیستم فعلی یک «گراف دانش سبک» است — ثبت موجودیت + رابطه + فاکت، با اعتبارسنجیِ وجود (validate). سند پیشنهادی می‌خواهد این را به یک **هستی‌شناسی کاربردی کامل** با schema صریح، واژگان کنترل‌شده، شاهد/منبع/منشأ، و نسخه‌بندی تبدیل کند. **فاصله‌ی این دو، نه یک ویرایش فایل، بلکه بازآرایی چندجمله‌ای از لایه‌ی داده است.** این مهم‌ترین نکته‌ی کل بازبینی است.

---

## ۱. جمع‌بندی کلی (Verdict)

> **سند از نظر مفهومی و نظری در سطح بسیار بالاست و اصولاً درست نوشته شده. اما به‌شکل کنونش یک «برنامه‌ی 6 تا 12 ماهه» است، نه «specی که روی EntityTypes.php اعمال شود».** اگر بی‌واسطه اجرا شود، تقریباً کل لایه‌ی داده بازنویسی می‌شود و ریسکِ خراب‌شدن داده‌های فعلی (581 رکورد) بسیار بالاست.

توصیه‌ی من: **مفاد این سند را به‌عنوان North-Star (هدف نهایی) تصویب کنید، اما پیاده‌سازی را فازبندی‌شده و افزایشی (additive / backward-compatible) انجام دهید.** بخش 16 سند که عنوان «الزامات فنی برای پیاده‌سازی در EntityTypes.php» دارد، هزینه‌ی واقعی را به‌طرز محسوسی دست‌کم می‌زند: `EntityTypes.php` امروز یک آرایه‌ی مسطح 131 سطری است؛ آنچه بخش 16.2 می‌خواهد (family, subtypes, properties[], allowed_facets[], allowed_relations[], form_sections[], validation_rules[], search_config[], version) در واقع یک **registry کامل schema** است که باید فایل/ساختار جداگانه داشته باشد، نه همان فایل.

---

## ۲. نقاط قوت (آنچه واقعاً خوب است و باید حفظ شود)

این‌ها بینش‌های حرفه‌ای‌اند و دقیقاً همان چیزهایی‌اند که در پروژه‌های دانش واقعی معمولاً از دست می‌روند:

1. **تفکیک چهار مفهومِ وضعیت** (بخش 1.1 و 4.1): «درستی داده» ≠ «اعتبار علمی ادعا» ≠ «اطمینان استخراج» ≠ «وضعیت انتشار». این تمایز، به‌خصوص «منتشرشده ≠ تأییدشده‌ی علمی»، طلایی است.
2. **تفکیک Source / Citation / Evidence / Provenance** (بخش 9): درست. «اثر» و «منبع» لزوماً دو شیء مستقل نیستند — این دقیقاً همان مسئله‌ای است که در داده‌ی فعلی با `WORK` + `kb_entity_links` ساده مدیریت شده و سند آن را درست گسترش می‌دهد.
3. **تفکیک هسته‌ی دانش از محتوای تحریریه‌ای** (بخش 11): کاملاً درست و با معماری فعلی (دو دیتابیس جدا) هم‌راستاست.
4. **قواعد روابط «نادرست»** (بخش 8.3): مثلاً «`SOFTWARE → MEASURES → CONCEPT` نادرست است» و «`DISPUTE → PROPOSED_BY → PERSON` بهتر است `HAS_PARTICIPANT` باشد» — این‌ها دقیقاً همان اشتباهاتی‌اند که در یک گراف دانش واقعی رخ می‌دهد.
5. **مدل‌سازی qualified relation** (بخش 8.4): امکان داشتن `valid_from/until`, `evidence_ids`, `asserted_by`, `verified_by`, `confidence_score` روی هر رابطه.
6. **قاعده‌ی «نامشخص ≠ صفر ≠ خیر»** و **حذف داده‌ی متناقض ممنوع، ثبت اختلاف اجباری** — بسیار درست از نظر دانش‌محور.
7. **آزمون‌های پذیرش AT-01..AT-18** (بخش 17): به‌عنوان Definition of Done عالی‌اند.
8. **فرآیند حاکمیت هستی‌شناسی** (بخش 18) + ADR (بخش 20): حرفه‌ای.

---

## ۳. هشدارهای مهندسی (با استناد به فایل/جدول مشخص)

### 3.1. «UUID/ULID به‌عنوان کلید» — مخالفت ملایم، با جایگزین
بخش 4 می‌خواهد `id` = UUID/ULID باشد. **اما کلید فعلی، `code` است** (`theory-agenda-setting`) و 384 رابطه و 71 لینک، **با FK مستقیم به `code`** به آن ارجاع دارند (به‌راستی: `FOREIGN KEY (source_code) REFERENCES kb_entities(code)`).
- **هزینه‌ی سوییچ به UUID PK:** بازنویسی همه‌ی FKها + مهاجرت 384+71 ارجاع + شکستن slug.
- **جایگزین:** `code` فعلاً همان «شناسه‌ی پایدار» است. پیشنهاد: **`code` را به‌عنوان کلید داخلی حفظ کنید**؛ اگر برای استفاده‌ی خارجی UUID خواستید، یک ستون `uid` (ULID) اضافه کنید و برای display بگذارید. ارزش مهاجرت PK را ندارد.

### 3.2. `properties` schema — بزرگ‌ترین ارزش و بزرگ‌ترین هزینه
امروز `properties` JSON آزاد است. سند می‌خواهد schema صریح (type, cardinality, validation, required, searchable, ai_extractable, evidence_required).
- **درست است و ضروری‌ترین بخش برای «ساختار محتوایی سایت» شماست** (همان چیزی که درباره‌ی فرم نظریه/نظریه‌پرداز پرسیدید).
- **اما:** این یعنی یک فایل registry جدید (پیشنهاد: `app/Knowledge/EntitySchemas.php`) + یک اعتبارسنج (`EntitySchemaValidator.php`) + فرم‌سازی + و مهاجرت 581 رکورد فعلی (که properties‌شان null است).
- **توصیه:** **افزایشی** شروع کنید. اول برای ~7 نوعی که سایت به آن‌ها نیاز دارد (THEORY, PERSON, WORK, SCHOOL, MODEL, FRAMEWORK, METHOD). اعتبارسنج در فاز اول **lenient** (خطا = warning، نه block) باشد تا 581 رکورد فعلی نشکنند؛ بعد سفت شود.

### 3.3. فاکت‌ها: متن آزاد ← واژگان کنترل‌شده (ریسکِ واقعیِ داده)
امروز 1886 فاکت، **متن آزاد** هستند. سند می‌خواهد هر فاکت registry مقادیر معیار داشته باشد.
- **مشکل واقعی:** برای تبدیل 1886 مقدارِ آزاد به مقادیر کنترل‌شده باید **داده‌ی فعلی تمیز/همسان‌سازی شود** (مثلاً «جامعه‌شناسی» vs «جامعه‌شناسی‌ای» vs «Sociology»). این یک job داده‌تمیزی است، نه صرفاً schema.
- **فراتر از آن، سند فاکت‌ها را عوض می‌کند:**
  - 9 فاکت فعلی: `discipline, theoretical_tradition, approach, epistemology, data_type, analysis_scale, application_domain, time_frame, qualifies_for`
  - 9 فاکت سند: `discipline, subfield, time_frame, approach, epistemology, ontology, data_type, theoretical_tradition, research_context`
  - **نتیجه:** 3 تا حذف (`analysis_scale`, `application_domain`, `qualifies_for`)، 3 تا اضافه (`subfield`, `ontology`, `research_context`). یعنی **remap روی 1886 سطر** فاکت.
- **توصیه:** حذف فاکت‌های فعلی را **soft** کنید (غیرفعال/بایگانی، نه delete — با خود اصل سند که «حذف مقدار استفاده‌شده ممنوع» است هم‌راستا). برای `subfield`/`discipline`/`theoretical_tradition` به‌عنوان **ارجاع به موجودیت** فعلاً صبر کنید؛ در فاز اول به‌صورت **مقصدِ کنترل‌شده‌ی رشته‌ای (string)** بگذارید و بعد که آن انواع پر شدند، به FK تبدیل کنید.

### 3.4. روابط: بدون domain/range ← registry با domain/range + inverse
امروز هیچ محدودیتی روی مبدأ/مقصد رابطه نیست. سند می‌خواهد `allowed_source_types`/`allowed_target_types` + معکوس canonical.
- **درست و ضروری است** (برای جلوگیری از `SOFTWARE→MEASURES→CONCEPT` و...).
- **هزینه:** گسترش `kb_relation_types` (افزودن دو ستون JSON) + **ممیزی 384 رابطه‌ی موجود** برای انطباق با domain/range جدید.
- **ریسک:** بعضی از 384 رابطه‌ی فعلی احتمالاً با domain/rangeِ سختِ سند **ناسازگار** خواهند بود.
- **توصیه:** **هیچ رابطه‌ای را خودکار حذف نکنید.** انطباق‌نیافته‌ها را `status=needs_review` بگذارید و گزارش دهید. validator سمت سرور مرجع نهایی باشد ولی در فاز 1 «block» نیندازد.

### 3.5. `classification_ids` به‌عنوان آرایه — آنتی‌پترن
بخش 4/2.3 می‌خواهد `classification_ids` (آرایه‌ی ارجاع) + multi-parent. **نگذارید این به یک ستون JSON آرایه‌ای در `kb_entities` تبدیل شود** — برای query relational و join آنتی‌پترن است و با همان «ارجاع با شناسه و رابطه» که اصل سند می‌گوید، تضاد دارد.
- **جایگزین:** سلسله‌مراتب تک‌والد را با همان `parent_code` (روابط IS_A / PART_OF) نگه دارید؛ **چندوالدبود را با روابطِ تایپ‌شده مدل کنید**، نه با آرایه.
- **`subtypes`** هم بهتر است با یک جدول `kb_entity_subtypes` یا با همان مکانیزم رابطه باشد، نه آرایه‌ی JSON.

### 3.6. سه محورِ وضعیت (بخش 4.1) — درست، ولی با care
امروز `status` یک enum سه‌گانه است. سند می‌خواهد سه محور مستقل (workflow / scientific-validity / lifecycle). **درست است.**
- **هزینه:** 3 ستون جدید روی 581 رکورد + نگاشت مقدار فعلی. جدول کوچک است، قابل‌مدیریت — ولی **backward-compatible** باشد (ستون قدیمی را نگه دارید یا map کنید، نه drop).

### 3.7. نسخه‌بندی کامل (بخش 10) — بیش‌ازحد برای فاز 1
سند می‌خواهد هر ویرایش یک نسخه‌ی immutable بسازد (`previous_version_id`, `supersedes`, snapshot). برای 581 رکورد **فاز 1 بیش از حد** است.
- **داراییم:** `kb_audit_log` (append-only) + `updated_at`.
- **توصیه:** در فاز 1 همان **audit log + `version` int + `updated_at`/`updated_by`** کافی است. immutable-versioning کامل فقط در صورت نیاز واقعی (مثلاً نیاز به «بازبینی نسخه‌ی ۳ از نظریه‌ی X») اضافه شود.

### 3.8. 10 نوع جدید (بخش 3.2) — همه را یکجا نگیرید
- **ارزشمند و پیشنهادی برای فاز 1/2:** `FINDING`, `EVIDENCE`, `SOURCE` (به‌عنوان نقش روی WORK)، `RESEARCH_STUDY`.
- **تعویق (فاز 2 هستی‌شناسی):** `PROPOSITION`, `CONSTRUCT`, `VARIABLE`, `INDICATOR`, `RESEARCH_PROBLEM`, `RESEARCH_QUESTION` — این‌ها لایه‌ی «اجرای پژوهش»‌اند؛ ارزشمندند ولی هم‌زمان با 10 مورد دیگر، scope را منفجر می‌کند.
- **`SOURCE` به‌عنوان موجودیت مستقل یا نقش روی WORK:** سند خودش هم این ابهام را ذکر کرده (نکته‌ی بخش 3.2). **تصمیم من:** در فاز 1، **نقش روی WORK** (یک flag `has_bibliographic_role`) + جدول `kb_citations`/`kb_evidence`. جدول مستقل `kb_sources` فقط برای منابعِ غیرکتابی که WORK ندارند ساخته شود، وگرنه duplicate می‌شود.

---

## ۴. جدول شکاف (spec در برابر واقعیت + هزینه)

| ردیف | آنچه سند می‌خواهد | واقعیت فعلی | شکاف / هزینه مهاجرت |
|---|---|---|---|
| 1 | 28→38 نوع | 28 نوع | افزایشی؛ 10 نوع جدید (بعضی تعویق) — **کم** |
| 2 | `properties` با schema صریح | JSON آزاد، ~null | **بالا** — registry + validator + فرم + مهاجرت |
| 3 | فاکت کنترل‌شده + matrix | متن آزاد (1886 سطر) | **بالا** — registry + تمیزکاری داده + remap 9→9 |
| 4 | رابطه با domain/range + inverse | بدون محدودیت | **متوسط** — گسترش registry + ممیزی 384 رابطه |
| 5 | qualified relation (evidence, valid_from, ...) | فقط note+weight+confidence | **متوسط** — ستون‌های جدید + جدول evidence |
| 6 | 3 محور وضعیت مستقل | 1 enum | **کم** — 3 ستون + map |
| 7 | فیلدهای identity (aliases, visibility, created_by, version, external_ids) | حداقلی | **کم** — ستون‌های جدید روی جدول کوچک |
| 8 | Source/Citation/Evidence/Provenance | فقط `WORK` + `kb_entity_links` | **متوسط تا بالا** — جدول‌های جدید |
| 9 | نسخه‌بندی immutable | فقط audit_log | **بالا (توصیه: تعویق)** |
| 10 | UUID PK | `code` (FK در 384+71) | **بالا (توصیه: انجام نشود؛ `uid` جایگزین)** |
| 11 | multi-parent + subtypes + classification | تک‌والد `parent_code` | **متوسط (توصیه: با رابطه، نه آرایه)** |
| 12 | جداسازی editorial | دو دیتابیس جدا (موجود) | **کم** — عملاً همین الان جداست |

---

## ۵. مسیر پیشنهادی (فازبندی‌شده، افزایشی، backward-compatible)

> اصل: **هیچ داده‌ای drop یا overwrite نشود.** هر فاز additive است و `kb_validate` + آزمون‌های AT را پاس بماند.

### فاز 0 — حاکمیت و تصمیم‌گیری (کم‌هزینه، پُرپسند)
- موارد بازِ بخش 20 را به ADR تبدیل کنید (پاسخ من در بخش 6).
- این مرحله schema را قفل می‌کند؛ قبل از آن کد نزنید.

### فاز 1 — گسترش کم‌ریسکِ schema (1–2 اسپرینت)
- افزودن ستون‌های additive به `kb_entities`: `entity_family`, `visibility`, `version`, `created_by`, `updated_by`, `aliases`, `original_name`, `original_language`, `external_identifiers`.
- 3 محور وضعیت (ستون‌های جدا؛ `status` قدیمی map شود).
- **registryِ `EntitySchemas.php` + `EntitySchemaValidator.php`** — **فقط برای 7 نوع اصلی** (THEORY, PERSON, WORK, SCHOOL, MODEL, FRAMEWORK, METHOD). این مستقیماً «ساختار محتوایی سایت» شما را می‌سازد.
- lenient validation (warning-first).

### فاز 2 — فاکت‌های کنترل‌شده + تمیزکاری داده (1–2 اسپرینت؛ ریسکِ داده‌ی اصلی)
- `ControlledVocabularies.php` + جدول `kb_facet_values`.
- **همسان‌سازی 1886 مقدار فعلی** (انسانی + کمک AI + بازبینی).
- remapِ 9→9 (soft-disable سه‌تای حذف‌شده).

### فاز 3 — ارتقای روابط (1 اسپرینت)
- گسترش `kb_relation_types` (JSON: `allowed_source_types`, `allowed_target_types`, `inverse`).
- ستون‌های qualification روی `kb_relations` (assertion_status, evidence, asserted_by, verified_by, extraction_confidence) — **کنار** `confidence` فعلی، نه جایگزین آن.
- **ممیزی 384 رابطه**؛ انطباق‌نیافته‌ها → `needs_review` (بدون حذف).

### فاز 4 — 4 نوع بنیادی جدید (1–2 اسپرینت)
- `FINDING`, `EVIDENCE`, `RESEARCH_STUDY` + `SOURCE` به‌عنوان نقش.
- `kb_citations`, `kb_evidence` جدول‌ها.

### فاز 5 — بقیه‌ی 10 نوع + آزمون‌پذیری AI + امتیاز کیفیت + جست‌وجوی چندلایه (فاز بعدی)
- PROPOSITION, CONSTRUCT, VARIABLE, INDICATOR, RESEARCH_PROBLEM, RESEARCH_QUESTION.
- آزمون‌های AT-01..AT-18 را در CI بگذارید (نه فقط در جلسه‌ی تصویب).

---

## ۶. پاسخ به موارد بازِ بخش 20 (تصمیم پیشنهادی من)

| # | سؤال | پیشنهاد من | دلیل |
|---|---|---|---|
| 1 | SOURCE مستقل یا نقش WORK؟ | **نقش روی WORK** + `kb_citations`/`kb_evidence` | جلوگیری از duplicate؛ فقط منابع غیرکتابی بدون WORK، جدول مستقل |
| 2 | CONCEPTUAL_FRAMEWORK | **زیرنوع FRAMEWORK** با `framework_kind` | هنوز نیاز به صفحه/چرخه‌ی مستقل ندارد |
| 3 | STATISTICAL_TEST | **زیرطبقه‌ی ANALYSIS** (فاز 2) | هویت مستقل در فاز 1 اثبات‌نشده |
| 4 | INSTRUMENT_VERSION | **فیلد/رکورد نسخه روی INSTRUMENT** | نسخه ≠ موجودیت مستقل تا نیاز واقعی |
| 5 | INSTITUTION/JOURNAL/CONFERENCE | **JOURNAL + INSTITUTION فاز 2؛ CONFERENCE تعویق** | برای دانشنامه‌ی رسانه، مجله/نهاد محوری‌ترند |
| 6 | ARGUMENT | **تعویق**؛ PROPOSITION + EVIDENCE کافی‌ست | کاهش scope فاز 1 |
| 7 | دامنه: علوم انسانی یا همه‌ی علوم؟ | **فاز 1: علوم انسانی/اجتماعی (ارتباطات)** | واژگان/واحدها/فاکت‌ها را تنگ و معنادار نگه می‌دارد |
| 8 | تقویم/زبان | **از روز اول چندتقویمی/چندزبانه (ستون‌ها را بگذارید)** ولی FA+شمسی primary | افزودن ستون ارزان است؛ مهاجرت بعدی گران |

---

## ۷. چه چیزهایی را کات/تعویق می‌کنم (برای کاهش ریسک)

1. **immutable-versioning کامل** → با audit_log + `version` int بسازیم (فاز 1).
2. **UUID به‌عنوان PK** → انجام نمی‌شود؛ `code` حفظ می‌شود، `uid` اختیاری.
3. **multi-parent با آرایه‌ی JSON** → با روابط تایپ‌شده.
4. **فاکت به‌عنوان FK به موجودیت** → فاز 1 string کنترل‌شده، بعداً FK.
5. **10 نوع جدید به‌صورت هم‌زمان** → 4 نوع در فاز 4، بقیه فاز 5.
6. **جایگزینی `confidence` enum با امتیاز عددی** → **افزودن** `assertion_status` + `extraction_confidence` کنار آن.

---

## ۸. برآورد و ریسک

- **حجم واقعی:** یک برنامه‌ی چندماهه (فاز 1 تا 4 ≈ 5–7 اسپرینت فشرده)، نه یک «ویرایش فایل».
- **بزرگ‌ترین ریسک‌ها (به ترتیب):**
  1. **تمیزکاری 1886 فاکتِ آزاد → کنترل‌شده** (داده، نه کد).
  2. **ممیزی 384 رابطه با domain/range جدید** (ممکن است بخش قابل‌توجهی ناسازگار باشد).
  3. **مهاجرت 581 رکورد بدون drop** (schema migration روی جدول کوچک، قابل‌مدیریت).
- **بزرگ‌ترین ارزش (برای سایت مخاطب‌نهادی شما):** **registryِ `properties` + 3 محور وضعیت + qualified relation** — دقیقاً همین سه چیز «ساختار محتوایی متفاوت هر نوع» را که می‌خواهید، ممکن می‌سازند.

---

## ۹. قاعده‌ی نهایی (هم‌راستا با خود سند)

**هیچ تغییری در داده‌های فعلی صرفاً بر اساس این سند و بدون:** (الف) ADR برای موارد باز، (ب) migration غیرمخرب، (ج) اجرای آزمون‌های AT در CI — **انجام نشود.**

پیشنهاد عملی برای شروع: **فاز 0 (ADR) + فاز 1 (properties برای 7 نوع + 3 محور وضعیت + ستون‌های identity).** این کمترین هزینه را دارد و مستقیماً «ساختار محتوایی سایت» را می‌سازد.
