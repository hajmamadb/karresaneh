# WORKFLOW-PLAN — فاکت‌بندی ۹بُعدی کل گراف (۵۸۱ موجودیت)

## ✅ پایان‌بندی workflow (۲۰۲۶-۱۱-۱۱): ۸/۸ فایل تأیید، ۱۸۸۶ فاکت در DB، بازبین نهایی ۱۵/۱۵

## وضعیت اجرا (پایانی)

| worker | فایل جدید | موجودیت‌ها | وضعیت | sessionID |
|---|---|---|---|---|
| f-theories (پایلوت) | Facets_Theories.php | 30 | ✅ تأیید — سبز ساختاری + بازبین تازه‌نگر ۱۰/۱۰ + ۳ اصلاح اعمال‌شده (DB: 129 فاکت) | done |
| f-persons | Facets_Persons.php | 107 | ✅ تأیید ساختاری مستقل (299 فاکت، 36 WARN مکتب‌شناختی مجاز) — بازبین نهایی ✔ | done |
| f-schools | Facets_Schools.php | 118 | ✅ تأیید ساختاری مستقل (429 فاکت) — بازبین نهایی ✔ | done |
| f-paradigms | Facets_Paradigms.php | 49 | ✅ تأیید ساختاری مستقل (118 فاکت) — بازبین نهایی ✔ | done |
| f-designs | Facets_Designs.php | 57 | ✅ تأیید ساختاری مستقل (292 فاکت) + ۱ تایپو سشن‌اصلی (تپبیقی→تطبیقی ×۴) | done |
| f-collection | Facets_Collection.php | 72 | ✅ تأیید ساختاری مستقل (223 فاکت) — بازبین نهایی ✔ | done |
| f-analysis | Facets_Analysis.php | 69 | ✅ تأیید ساختاری (202 فاکت) — بازبین نهایی ✔ | done |
| f-tools | Facets_ToolsQualityEthics.php | 79 | ✅ تأیید ساختاری مستقل (195 فاکت) — بازبین نهایی ✔ | done |

> پیشرفت‌های سشن اصلی (موازی): ۶ PERSON یتیم → ۹ رابطه‌ی curation در Relations.php + seed + ممایش (orphan_person_linkage)؛
> یتیمان کل گراف ۲۲۴ → ۲۱۹؛ PERSON بی‌رابط باقی‌مانده: فقط person-silverman (بدون هدف امن فکتی — مستند).
> بازبین تازه‌نگر پایلوت: تأیید ۱۰/۱۰؛ ۳ اصلاح (surveillance-capitalism→عصر پلتفرمی، crisis-communication→مدیریت+علوم ارتباطات، uses-gratifications→کیفی|آمیخته) اعمال + ممایش (facet_review_fixes).
> دیتابیس (اکنون): ۵۸۱ موجودیت / ۳۸۴ رابطه / ۱۲۹ فاکت / validate سالم.
> **موج ۱ کامل شد (۴/۴ GREEN):** analysis 202 + persons 299 + tools 195 + schools 429 = 1125 سطر فاکتِ آماده‌ی seed.
> سیاست نهایی اعتبارسنج (سشن اصلی): (الف) parent با‌نقل در seedها (regex پارسر رفع‌شده؛ مرجع اصلی = srcmap پارس‌شده با PHP).
> (ب) هاب‌های TECHNIQUE/SAMPLING/SOFTWARE/INSTRUMENT/QUALITY/ETHICS/PERSON/WORK + هر -root: ۰–۲ فاکت؛
> (ج) بقیه‌ی انواع (شامل هاب‌های ANALYSIS/DESIGN/TRADITION/...): والد‌ها ۲–۴ فاکت (ابعاد مجازشان توصیف‌کننده‌ی خود گره‌اند).
> (د) ETHICS: data_type/time_frame/approach مجاز؛ CONCEPT مجموعه‌ی دوکاره (نظری + روش‌شناختی)؛ DESIGN: approach/data_type/analysis_scale/application_domain/epistemology.
> **موج ۲ کامل شد (۳/۳ GREEN):** collection 223 + paradigms 118 + designs 292. گزارش‌شفاهی workerهای designs با فایل واقعی هم‌خوان نبود (هالوسینیشن گزارش) — فایل دیسک تأیید شد؛ درس: هیچ‌وقت گزارش worker به‌جای verify مستقل.
> **Gate نهایی:** seed → 581/384/**1886 فاکت**؛ validate سالم ✔ (1253 + 633 = 1886 دقیقاً).
> **بازبین تازه‌نگر نهایی (سشن اصلی، ۱۵ نمونه‌ی قطعی ۲×۸ فایل): ۱۵/۱۵ تأیید.**
> دو یافته‌ی مستند (اصلاح‌نیاز نبود): (۱) theory-media-extension: tradition=ساختارگرا تقریب واژگانی برای مکتب تورنتو؛
> (۲) خانواده‌ی case-study (۵ ردیف) + ethnography سازمانی: data_type چهارمقدار (شواهد چندمنبعه‌ی طرح موردی — عمدی).

(مجموع: 581 ✓ — شمارش واقعی کدهای هر فایل)
تغییر زیرساخت: `bin/kb.php seed` حالا `Facets_*.php` را با glob بارگذاری می‌کند (smoke-test شده: seed بدون فایل فاکت همان رفتار قبلی).

## هدف
برای هر موجودیت فعال، ۲ تا ۴ فاکتِ مرتبط (از ۹ بُعد `FacetTypes`) در فایل seed فاکتِ
متناظر نوشته و با `seed` به دیتابیس برسد. پایان: هر موجودیت ۲–۴ فاکت (گره‌های ساختاری خالی)
و `validate` سالم.

## ۹ بُعد
discipline (حوزه علمی) · theoretical_tradition (سنت نظری) · approach (رویکرد روش‌شناختی) ·
epistemology (مبنا معرفت‌شناختی) · data_type (نوع داده) · analysis_scale (مقیاس تحلیل) ·
application_domain (حوزه کاربرد) · time_frame (دوره زمانی) · qualifies_for (مناسب برای)

## واحد کار (فایل‌های seed فاکت — جدید، مالکیت بی‌اشتراک)
| worker | فایل جدید | موجودیت‌های پوشش‌داده‌شده |
|---|---|---|
| f-theories (پایلوت) | `Facets_Theories.php` | Theories.php (~57) |
| f-persons | `Facets_Persons.php` | Persons.php (95) |
| f-schools | `Facets_Schools.php` | Schools.php (~104) |
| f-paradigms | `Facets_Paradigms.php` | Paradigms.php (~42) |
| f-designs | `Facets_Designs.php` | Designs.php (63) |
| f-collection | `Facets_Collection.php` | Collection.php (~68) |
| f-analysis | `Facets_Analysis.php` | Analysis.php (76) |
| f-tools | `Facets_ToolsQualityEthics.php` | ToolsQualityEthics.php (~76) |

فرمت هر فایل: `return [ [code, ['dim' => 'مقدار', ...]], ... ];`
(برای مقادیر چندگانه یک بُعد: `'dim' => ['مقدار۱', 'مقدار۲']`)

## ابعادِ منطقی برای هر نوع (راهنمای worker)
- THEORY/MODEL: theoretical_tradition, analysis_scale, time_frame, application_domain, discipline
- TRADITION/SCHOOL: theoretical_tradition, time_frame, discipline
- PARADIGM/APPROACH: epistemology, approach, application_domain
- SUBFIELD: discipline, analysis_scale, application_domain, time_frame
- DESIGN/TECHNIQUE/ANALYSIS/SAMPLING: approach, data_type, analysis_scale, application_domain
- SOFTWARE: approach, data_type, qualifies_for
- QUALITY: qualifies_for, approach
- ETHICS: qualifies_for, application_domain
- PERSON: discipline, time_frame, theoretical_tradition
- WORK: time_frame, discipline, theoretical_tradition
- CONCEPT: ۲–۴ (نظری: سنت/مقیاس/دوره/حوزه/رشته؛ روش‌شناختی: approach/data_type/qualifies_for)
- DISPUTE: time_frame، discipline · METHODOLOGY (methodology-root): ۰–۲ فاکت
- **سیاست هاب (نهایی، موج ۱):** TECHNIQUE/SAMPLING/SOFTWARE/INSTRUMENT/QUALITY/ETHICS/PERSON/WORK/DISPUTE + هر کدِ `-root` = ۰–۲ فاکت؛
  والد بقیه‌ی انواع (ANALYSIS/DESIGN/PARADIGM/TRADITION/...) = ۲–۴ (ابعاد مجازشان توصیف‌کننده‌ی خود گره‌اند)

## واژگان کنترل‌شده (اولویت روی این‌ها؛ خارج از فهرست فقط اگر واقعا‌ً لازم باشد)
- approach: کمّی، کیفی، آمیخته، محاسباتی، تاریخی، تطبیقی
- epistemology: اثبات‌گرا، پس‌اثبات‌گرا، تفسیری، برساخت‌گرا، انتقادی، پراگماتیست، واقع‌گرا
- data_type: متنی، عددی، تصویری، صوتی، رفتاری، شبکه‌ای، اسنادی، دیجیتال
- analysis_scale: خرد/بین‌فردی، میانه/سازمانی، کلان/جامعه‌شناختی
- time_frame: پیش از رسانه‌های توده‌ای، عصر رسانه‌های توده‌ای، عصر دیجیتال، عصر پلتفرمی
- qualifies_for: کمّی، کیفی، آمیخته، همه
- discipline: علوم ارتباطات، جامعه‌شناسی، روان‌شناسی، فلسفه، علوم کامپیوتر، علوم سیاسی، مدیریت، علوم تربیتی
- application_domain: رسانه، سیاست، سازمان، سلامت، آموزش، توسعه، بازاریابی، بحران
- theoretical_tradition: انتقادی، کارکردگرا، تعامل‌گرا، ساختارگرا، پساساختارگرا، فمینیست، شبکه‌ای، هریگانه (مختصر)

## توپولوژی
1. **پایلوت:** f-theories (~57) → بازبین مستقل ۸ نمونه
2. **fan-out:** ۷ worker باقی‌مانده، امواج ۴+۳
3. **gate (سشن اصلی):** `php bin/kb.php seed` → `validate` → `status` (عدد فاکت‌ها)
4. **بازبین نهایی:** نمونه‌گیری تصادفی ۱۵ موجودیت از DB (فاکت‌ها + کانتکست)
5. **گزارش:** به‌روزرسانی WORKFLOW-REPORT.md + مستندات

## ممنوعیت‌های worker
- فقط فایل `Facets_<نام>.php` خود را بسازد/تغییر دهد
- `seed` نزند، به DB ننویسد، فایل‌های entity دست نزند
- فکت اختراع نکن؛ مقدار فاکت باید از کانتکست موجودیت (`show`/`graph`) و دانش استاندارد قابل استناد باشد
- ZWNJ واقعی، بدون تطویل، بدون CJK، بدون ASCII نامطلوب در مقادیر فارسی

## ایمنی
- هیچ فایل موجودی تغییر نمی‌کند (فقط ۸ فایل جدید + تغییر کوچک `bin/kb.php` توسط سشن اصلی)
- درج فاکت additive است (ON DUPLICATE فقط value_en را به‌روز می‌کند) — اصلاح خطا = حذف سطر از Facets + `kb_entity_facets` را سشن اصلی مدیریت می‌کند
