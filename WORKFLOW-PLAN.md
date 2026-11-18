# WORKFLOW-PLAN — پر کردن ۱۴۳ چکیده‌ی خالی هسته دانش

## وضعیت اجرا (زنده)

| worker | فایل | تعداد | وضعیت | sessionID |
|---|---|---|---|---|
| w-paradigms | Paradigms.php | 15 | ✅ پایان (پایلوت) + بازبینی مستقل + ۸ اصلاح سشن اصلی | ses_e38df6d58ffeaBnoLRiJ4fQIwN |
| بازبین پایلوت | — | ۵ نمونه | ✅ پایان؛ ساختار قابل تعمیم، ۲ الگوی سیستماتیک اصلاح شد در پرامپت‌ها | ses_e38d3f7b9ffeQtKps9QVelJS4A |
| w-schools | Schools.php | 28 | ✅ پایان + verify مستقل (28/28، maxLen 178) | ses_e38bfd8faffebDjv2VRMyvTYLp |
| w-collection | Collection.php | 28 | ✅ پایان + verify مستقل (28/28، maxLen 263) | ses_e38ba5440ffej88y6Kqzz4J962 |
| w-designs | Designs.php | 25 | ✅ پایان + verify مستقل (25/25، maxLen 211) | ses_e38b36527ffes1K7wgA88jjk4Y |
| w-analysis | Analysis.php | 19 | ✅ پایان + verify مستقل (19/19، maxLen 295) | ses_e3899d07effeQiIPX2tlAQW606 |

**gate قطعی (سشن اصلی):** ✅ سبز — `seed` (581 موجودیت / 375 رابطه) + `validate` سالم + `status`: بدون چکیده: 0
**بازبین نهایی:** ▶️ ۱۵ نمونه قطعی (seed=20261110) — sessionID: ses_e38853a2effeSv2uxhZHOBRK6g
| w-tools | ToolsQualityEthics.php | 28 | ✅ پایان + verify مستقل (28/28، maxLen 228) | ses_e3899d07effdzeqqC1ADdG1vqI |

نکته‌ی جانبی (بازبینی پیوند): لینک design-simulation → card-474 «وانمایی/وانمودگی» (که به‌دست worker Designs شناسایی شد) بررسی شد: کارت متعلق به مفهوم نظری `concept-simulation` (شبیه‌سازی و وانموده — بودریار) است، نه طرح پژوهشی شبیه‌سازی. لینک با لاگ ممیزی (`link_move`) جابه‌جا شد.

اصلاحات اعمال‌شده روی Paradigms.php پس از بازبینی (۸ مورد): ۳ بازنویسی کامل (names/اصطلاح/طول) + ۴ اصلاح زیررشته‌ای (بوریس گران→روی بشاکر، تکرار «جنسیت»، بازبازگردانی→بازگردانی، ارتباط سلامت→ارتباطات سلامت) + تغییر عنوان دانش استعمارزدایانه→دانش دیکولونیال.
قوانین جدید تزریق‌شده در پرامپت‌های workerها: (1) سقف سخت ۳۰۰ نویسه، (2) نام خاص فقط در صورت آشنایی کامل، (3) بدون هیچ کاراکتر ASCII درون متن فارسی.


## هدف
برای هر ۱۴۳ موجودیتِ `status='active'` بدون چکیده، چکیده‌ی ۲ تا ۳ خطی فارسی، دانشنامه‌ای
(تعریف + تمایز از مفاهیم نزدیک + کاربرد/اهمیت) در سطر seed مربوطه نوشته و به دیتابیس اعمال شود.
پایان: `status` → «موجودیت‌ها بدون چکیده: 0» و `validate` سالم.

## واحد کار (unit)
یک فایل seed = یک worker. مالکیت فایل‌ها بین workerها **بی‌اشتراک**:

| worker | فایل | تعداد |
|---|---|---|
| w-paradigms | `app/Knowledge/Seed/Paradigms.php` | 15 |
| w-analysis | `app/Knowledge/Seed/Analysis.php` | 19 |
| w-designs | `app/Knowledge/Seed/Designs.php` | 25 |
| w-schools | `app/Knowledge/Seed/Schools.php` | 28 |
| w-collection | `app/Knowledge/Seed/Collection.php` | 28 |
| w-tools | `app/Knowledge/Seed/ToolsQualityEthics.php` | 28 |

(مجموع: 143 — Persons.php و Theories.php خالی هستند و Relations.php رکورد موجودیت ندارد.)

## توپولوژی (A — fan-out / fan-in + gate قطعی)
1. **پایلوت (دور ۰):** ابتدا فقط `w-paradigms` (کوچک‌ترین، ۱۵ مورد) اجرا می‌شود.
   یک reviewer مستقل ۵ نمونه‌ی تصادفی را می‌خواند («چه چیزی این را خراب می‌کند؟»).
   اگر الگوی مشکل‌ساز باشد، پرامپت workerها اصلاح می‌شود و بعد fan-out کامل می‌شود.
2. **fan-out (دور ۱):** پنج worker باقی‌مانده. حداکثر ۳–۴ هم‌زمان؛ امواج ۳+۲.
3. **gate (سشن اصلی — نه ساب‌ایجنت):**
   - `php bin/kb.php seed` (آیدامپوتنت؛ همه‌ی چکیده‌ها را به DB می‌رساند)
   - `php bin/kb.php validate` → باید «یکپارچگی سالم» باشد
   - `php bin/kb.php status` → «بدون چکیده: 0»
   - روی قرمز بودن: stderr/خروجی به‌عنوان فیدبک به worker متعلق (round cap: ۲ دور follow-up با sessionID)
4. **fresh eyes نهایی:** یک ساب‌ایجنت مستقل ۱۵ نمونه‌ی تصادفی از ۱۴۳ چکیده‌ی نهایی (از DB) را
   می‌خواند: صحت فکت، لحن دانشنامه‌ای، نیم‌فاصله/تطویل، تکرار عنوان در چکیده.
5. **گزارش:** `WORKFLOW-REPORT.md` (verified / notCovered / تفکیک unit) + خط پیشرفت برای هر worker.

## معیار پذیرش هر worker (per code)
- [ ] سطر seed به‌درستی تغییر کرده: چکیده عنصر ششم آرایه است (اگر properties موجود است، **پیش** از آن)
- [ ] چکیده ۲ تا ۳ خط، فارسی، بدون تکرار عنوان خود موجودیت، بدون اختراع فکت
- [ ] `php -l <فایل>` سبز
- [ ] اسکن CJK (`[\u4e00-\u9fff\u3040-\u30ff]`) روی فایل: خالی
- [ ] نیم‌فاصله‌ها درست (نوشته‌شده با ZWNJ واقعی)

## ممنوعیت‌های worker
- فقط فایل seed خود را تغییر دهد؛ به هیچ فایل دیگر، دیتابیس، یا `bin/kb.php seed` دست نزند
  (seed را سشن اصلی یک‌بار در آخر می‌زند تا نوشتن‌های موازی روی DB نباشد).
- context خواندن مجاز است: `php bin/kb.php show <code>`، `php bin/kb.php graph <code>`،
  `php bin/kb.php list <TYPE>` (فقط‌خواندنی).

## ایمنی
- قبل از شروع: snapshot از ۹ فایل seed به `%TEMP%\kb-seed-backup-<ts>\` (پایگاه بازگشت؛ بدون git — پروژه عمدی بدون git است).
- round cap: ۲ دور follow-up برای هر worker.
- اگر پایلوت الگوی سیستماتیک نشان داد: fan-out کامل نمی‌شود، پرامپت اصلاح می‌شود (دور ۰ تکرار می‌شود).

## شکل گزارش
`verified:` دستورات دقیق (seed/validate/status + خروجی‌ها) · `notCovered:` موارد دیده‌نشده + دلیل ·
تفکیک: 143/143 چکیده، نمونه‌های reviewer، خطاها.
