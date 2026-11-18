# بازبینی (نسخه‌ی سبز/ساخت جدید) — RM-KCORE-ONTOLOGY-SPEC v1.0.0

**فرضِ جدید:** دیتابیس فعلی «سیستمی است که باید live مهاجرت شود» نیست؛ بلکه **دیتابیس جدیدِ نسخه‌ی کامل‌تر** را می‌سازیم و داده‌ی فعلی (581 موجودیت / 384 رابطه / 1886 فاکت / 71 لینک) را **به‌عنوان seed یک‌باره** واردش می‌کنیم.
**پیامد:** بزرگ‌ترین نگرانیِ بازبینیِ قبل (مهاجرتِ ریسک‌دار + backward-compatibility) **برطرف** شد. حالا می‌توانیم اسکیما را **از اول درست** طراحی کنیم.

---

## ۱. آنچه این بازبینی در مقایسه‌ی نسخه‌ی قبل تغییر می‌دهد

| موضوع | بازبینیِ قبل (مهاجرت) | بازبینیِ جدید (ساخت سبز) |
|---|---|---|
| کلید اصلی | `code` را نگه دار (FK شکنده نمی‌شود) | **می‌توانیم درست کار کنیم:** `id` (PK) + `code` (business key) |
| نسخه‌بندی | فقط audit_log (immutable گران است) | **می‌توانیم `kb_entity_versions` را از اول بگذاریم** |
| فاکت‌ها | remapِ 1886 سطرِ درجایشده | **واژگان کنترل‌شده را از اول تعریف می‌کنیم**؛ 1886 مقدار فقط seed است |
| روابط | ممیزی 384 رابطه‌ی زنده | **registry کامل domain/range از اول**؛ 384 رابطه فقط seed + validate |
| 3 محور وضعیت | ستون‌های جدید + map | **از اول پیاده** |
| Source/Evidence | احتیاط روی کارکرد فعلی | **جدول‌های جدا از اول** |
| 10 نوع جدید | فازبندی به‌دلیل مهاجرت | **registry را یکجا می‌نویسیم** (سخت نیست)؛ فقط «پر کردن داده + UI» را فازبندی می‌کنیم |

**نتیجه:** سند RM-KCORE-ONTOLOGY-SPEC حالا **بسیار مناسب‌تر به‌عنوان هدف طراحیِ دیتابیس جدید** است. عمده‌ی توصیه‌های قبلی من (که برای محافظت از داده‌ی زنده بودند) دیگر لازم نیست؛ اما چند **تصمیمِ طراحیِ خالص** باقی می‌ماند که باید برای اسکیماِ جدید گرفته شود.

---

## ۲. معماری هدفِ دیتابیس جدید (لایه‌بندی)

```
[لایه‌ی 1 — تک منبع حقیقت / registries]  ← کد، deterministic، قابل آزمون
  EntityTypes.php      (38 نوع + family)
  EntityFamilies.php   (جدید)
  EntitySchemas.php    (جدید) ← properties schema هر نوع (data_type, cardinality, validation)
  RelationTypes.php    (گسترش: allowed_source_types, allowed_target_types, inverse, requires_evidence)
  FacetTypes.php       (فهرست فاکت‌ها + value_kind)
  ControlledVocab.php  (جدید) ← مقادیر کنترل‌شده هر فاکت
  EntitySchemaValidator.php (جدید)

[لایه‌ی 2 — دیتابیس جدید]  ← kb_schema_v2.sql
  (جدول‌ها در بخش ۳)

[لایه‌ی 3 — seed از داده‌ی فعلی]  ← اسکریپت یک‌باره، آیدامپوتنت
  581 موجودیت / 384 رابطه / 1886 فاکت / 71 لینک → map به اسکیما جدید

[لایه‌ی 4 — سایت v2]
  فرمِ محتوایی هر نوع (از EntitySchemas) + صفحه‌ی هر موجودیت + جست‌وجوی فاکت‌دار
```

**اصل:** لایه‌ی 1 «تک منبع حقیقت» است. دیتابیس، seed و سایت **همه از همین registries** می‌سازند تا drift نشود (همین رویکرد فعلی `bin/kb.php` و MCP است، فقط غنی‌تر).

---

## ۳. اسکیما پیشنهادیِ دیتابیس جدید (`kb_schema_v2.sql`)

### جدول‌های هسته

**`kb_entity_families`** (جدید)
`code, label_fa, label_en, description, sort_order` — 8 خانواده (THEORETICAL_SYSTEM, KNOWLEDGE_STRUCTURE, SCIENTIFIC_DOMAIN, RESEARCH_METHODOLOGY, RESEARCH_ACTIVITY, RESEARCH_RESOURCE, INTELLECTUAL_AGENT, SCIENTIFIC_ARGUMENTATION).

**`kb_entity_types`** (گسترش)
`code, label_fa, label_en, family_code (FK), domain, description, sort_order` — 38 نوع.

**`kb_relation_types`** (گسترش)
`code, label_fa, label_en, description, symmetric, allowed_source_types JSON, allowed_target_types JSON, inverse_code, requires_evidence TINYINT, sort_order`.

**`kb_facet_dimensions`** (گسترش)
`code, label_fa, label_en, description, value_kind ENUM('STRING','ENTITY_REF','TIME_PERIOD'), sort_order`.

**`kb_facet_values`** (جدید — registry واژگان کنترل‌شده)
`id, facet_code (FK), code, label_fa, label_en, definition, entity_ref (nullable FK→kb_entities.code برای value_kind=ENTITY_REF), active, sort_order`.

**`kb_entities`** (بازطراحی — قرارداد کامل)
`id (BIGINT PK autoinc), code (VARCHAR unique — business key), entity_type (FK), family_code,
 canonical_name_fa, canonical_name_en, slug (unique), aliases JSON, original_name, original_language, language,
 properties JSON (تأییدشده با EntitySchemas),
 status_workflow ENUM(draft,in_review,approved,published,archived),
 status_scientific ENUM(unassessed,supported,partially_supported,contested,refuted,not_applicable),
 status_lifecycle ENUM(active,historical,superseDED,deprecated,unknown),
 visibility ENUM(public,restricted,private),
 version INT, external_identifiers JSON,
 created_at, created_by, updated_at, updated_by, verified_at, verified_by`

> **تصمیم کلید:** `id` (autoincrement) = کلید فنی/داخلی؛ `code` = شناسه‌ی پایدار و خوانا (business key) که در slug و ارجاع‌های بیرونی استفاده می‌شود. روابط **به `id`** ارجاع می‌دهند (FK تمیز)، نه به `code`. این همان طراحیِ درستی است که در مهاجرت ممکن نبود.

**`kb_entity_subtypes`** (جدید — برای multi-parent / زیرنوع)
`entity_code (FK), subtype_code, sort_order`. **چندوالدبود را با جدول/رابطه مدل می‌کنیم، نه با آرایه‌ی JSON.**

**`kb_relations`** (بازطراحی — qualified)
`id, source_id (FK→kb_entities.id), target_id (FK), relation_type (FK), valid_from, valid_until,
 assertion_status ENUM(unverified,under_review,verified,contested,rejected),
 asserted_by, verified_by, extraction_confidence DECIMAL, note, weight, status, created_at, updated_at,
 UNIQUE(source_id,target_id,relation_type)`

**`kb_entity_facets`** (بازطراحی — ارجاع به واژگان)
`id, entity_id (FK), facet_code, facet_value_id (FK→kb_facet_values.id), sort_order`.
> مقدار فاکت دیگر متن آزاد نیست؛ به `kb_facet_values` ارجاع می‌دهد. برای فاکت‌های ENTITY_REF، خودِ `kb_facet_values.entity_ref` به موجودیت اشاره می‌کند.

### جدول‌های منبع/شاهد/منشأ (جدید)

**`kb_sources`**
`id, kind ENUM, title_fa, title_en, year, publisher, doi, isbn, issn, url, language, access_type,
 bibliographic JSON, work_code (nullable FK→kb_entities.code — اگر منبع همان WORK است)`
> `work_code` دقیقاً مسئله‌ی «duplicate نشود» را حل می‌کند: اگر منبع یک اثر است، به همان WORK وصل می‌شود نه رکورد جدا.

**`kb_citations`**
`id, source_id (FK), locator_type ENUM(PAGE,CHAPTER,SECTION,PARAGRAPH,TABLE,FIGURE,TIMESTAMP,URL_FRAGMENT), locator_value, quoted_text, paraphrase, citation_style, language, accessed_at`.

**`kb_evidence`**
`id, source_id (FK), citation_id (nullable FK), about_code (nullable FK→kb_entities.code),
 evidence_type, evidence_summary, quoted_content, locator, page_start, page_end,
 direction ENUM(supports,challenges,neutral), extraction_method, verification_status, extraction_confidence`.

**`kb_entity_versions`** (جدید — نسخه‌بندی immutable؛ در ساخت سبز ارزان است)
`id, entity_id (FK), version, snapshot JSON, change_reason, changed_by, created_at`.

**`kb_audit_log`** (حفظ/گسترش) + **`kb_entity_links`** (حفظ — لینک به دیتابیس اصلی سایت).

---

## ۴. تصمیمات طراحیِ باقی‌مانده (الان باید برای اسکیما جدید گرفته شوند)

### 4.1. EVIDENCE و SOURCE: موجودیت یا جدول زیرساخت؟
سند آن‌ها را جزو 10 نوع جدید (یعنی سطرِ `kb_entities`) گذاشته. **توصیه‌ی من:**
- **`FINDING`, `RESEARCH_STUDY`, `PROPOSITION`, `CONSTRUCT`, `VARIABLE`, `INDICATOR`, `RESEARCH_PROBLEM`, `RESEARCH_QUESTION`** → **نوع موجودیت** در `kb_entities` (چون مفهوم دانشی با هویت/رابطه/صفحه دارند).
- **`EVIDENCE` و `SOURCE`** → **جدول‌های زیرساخت/ارجاع** (`kb_sources`, `kb_evidence`, `kb_citations`) **به‌جای** سطرِ `kb_entities`.
- **دلیل:** (الف) حجم بالا و ماهیت «ارجاع/نقل» دارند، نه «مفهوم دانشی»؛ (ب) در UX معمولاً *درون* موجودیتی که به آن‌ها ارجاع می‌دهد نمایش می‌شوند، نه صفحه‌ی مستقل؛ (ج) SOURCE اغلب همان WORK است (با `work_code` وصل می‌شود).
- **پیامد:** `kb_entities` = **36 نوع** (28 + 8 نوع دانشی) + 3 جدول ارجاعی. این را **ADR** کنید (سند هم روی SOURCE جای مانده گذاشته).

### 4.2. 10 نوع جدید: registry را یکجا، «پر کردن + UI» را فازبندی
- **registry** (کد/تعریف/family) را **برای هر 8 نوع دانشی از اول بنویسید** — این فقط کد است، هزینه‌ی کم.
- **پر کردن داده + فرم سایت** را به اولویت ببندید:
  - **اولویت A (بالاترین ارزش برای دانشنامه‌ی پژوهش):** `RESEARCH_STUDY`, `FINDING`, `RESEARCH_QUESTION`, `RESEARCH_PROBLEM`.
  - **اولویت B:** `PROPOSITION`, `CONSTRUCT`, `VARIABLE`, `INDICATOR`.

### 4.3. فاکت‌ها: 9 فاکتِ سند را از اول کنترل‌شده
- 9 فاکتِ سند (`discipline, subfield, time_frame, approach, epistemology, ontology, data_type, theoretical_tradition, research_context`) را در `kb_facet_values` با `value_kind` تعریف کنید:
  - `discipline` → ENTITY_REF (به DISCIPLINE)
  - `subfield` → ENTITY_REF (به SUBFIELD)
  - `theoretical_tradition` → ENTITY_REF (به TRADITION)
  - `time_frame` → TIME_PERIOD
  - بقیه → STRING (واژگان کنترل‌شده)
- ماتریس «نوع × فاکت» (بخش 13 سند) را مستقیم در `EntitySchemas.allowed_facets` کد کنید.

### 4.4. `properties`: JSON تایپ‌شده + registry
- برای هر نوع، schema در `EntitySchemas.php` (data_type, cardinality, required, validation, searchable, ai_extractable, evidence_required).
- ذخیره در `kb_entities.properties` به‌صورت JSON، ولی **اعتبارسنجی با EntitySchemaValidator** (در ساخت سبز، از اول **strict** باشد، نه lenient).

### 4.5. چندوالد / طبقه‌بندی
- **از آرایه‌ی JSON (`classification_ids`) پرهیز کنید.** سلسله‌مراتب با `parent_code` (IS_A/PART_OF) + چندوالد با **`kb_entity_subtypes` / روابط**.

### 4.6. سه محور وضعیت + نسخه‌بندی
- از اول: `status_workflow` / `status_scientific` / `status_lifecycle` + `kb_entity_versions` + `kb_audit_log`.

---

## ۵. استراتژی seed از داده‌ی فعلی (یک‌باره، آیدامپوتنت)

این **تنها کارِ «داده‌ی کثیف»** باقی‌مانده است (نه مهاجرتِ زنده، یک batch job):

1. **اسکریپت seed** (همان الگوی فعلی `php bin/kb.php seed`) که دیتابیس قدیمی را می‌خواند:
   - 581 موجودیت → `kb_entities` جدید (map `status` قدیمی → 3 محور جدید؛ `abstract`→`summary`).
   - 384 رابطه → `kb_relations` جدید؛ **هر رابطه با domain/rangeِ registry validate شود**؛ ناسازگارها را در خروجی **گزارش** بده (نه delete).
   - 1886 فاکت (متن آزاد) → `kb_facet_values`: **همسان‌سازی/نگاشت** مقادیر به واژگان کنترل‌شده (این پرحجم‌ترین بخش است؛ انسانی + کمک AI + بازبینی).
   - 71 لینک → `kb_entity_links` (بدون تغییر).
2. **خروجی:** یک فایل seed deterministic + **گزارش انطباق** (چه رابطه‌ای با domain/range جدید ناسازگار شد، چه فاکتی نگاشت نشد).
3. **آزمون:** بعد از seed، `kb_validate` + آزمون‌های AT-01..AT-18 در CI.

> چون دیتابیس جدید است، می‌توانیم seed را **چند بار از اول** اجرا کنیم تا خراب — بدون ترس از تخریب داده‌ی زنده.

---

## ۶. برنامه‌ی ساخت (نه مهاجرت)

| فاز | کار | خروجی |
|---|---|---|
| **A. registries** | 6 فایلِ لایه‌ی 1 (از جمله `EntitySchemas`, `EntityFamilies`, `ControlledVocab`, `EntitySchemaValidator`) | تک منبع حقیقتِ v2 |
| **B. schema** | `kb_schema_v2.sql` (جدول‌های بخش ۳) + migration‌های خالی | دیتابیس جدیدِ خالی |
| **C. seed** | اسکریپت seed + نگاشت/validate 581+384+1886+71 + گزارش | دیتابیس جدیدِ پر |
| **D. سایت v2** | فرم‌های نوع‌به‌نوع (از EntitySchemas) + صفحه‌ی موجودیت + جست‌وجوی فاکت‌دار + نمایش evidence/source | نسخه‌ی کامل‌تر سایت |
| **E. نوع‌های B** | پر کردن `PROPOSITION/CONSTRUCT/VARIABLE/INDICATOR` + evidence-rich pages | غنی‌تر |

---

## ۷. خلاصه‌ی تغییر نگرش (نسبت به بازبینیِ قبل)

1. ✅ **UUID/PK:** حالا می‌توانیم درست کار کنیم → `id` PK + `code` business key + روابط به `id`.
2. ✅ **immutable versioning:** حالا ارزان است → `kb_entity_versions` از اول.
3. ✅ **فاکت/رابطه:** از اول کنترل‌شده/registry کامل؛ داده‌ی قدیمی فقط seed است.
4. ⚠️ **EVIDENCE/SOURCE:** توصیه‌ی من **جدول ارجاعی** به‌جای موجودیت (ADR بزنید).
5. ⚠️ **multi-parent:** با جدول/رابطه، **نه** آرایه‌ی JSON.
6. ⚠️ **10 نوع جدید:** registry یکجا؛ پر کردن داده + UI فازبندی.
7. 🟢 **بزرگ‌ترین کار باقی‌مانده:** **نگاشت 1886 فاکتِ آزاد → واژگان کنترل‌شده** در seed (data cleaning، یک‌باره).

**قاعده‌ی نهایی:** چون دیتابیس جدید است، **سند RM-KCORE-ONTOLOGY-SPEC را می‌توانیم تقریباً کامل به‌عنوان هدف طراحی بگیریم**؛ فقط 4 تصمیمِ بخش 4 (EVIDENCE/SOURCE، 10 نوع، فاکت، کلید) را به‌صورت ADR قفل کنید و بعد فاز A تا D را اجرا کنید.
