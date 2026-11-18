<?php
namespace Resaname\Knowledge\Seed;

/**
 * بخش ۱۰: ابزارهای گردآوری و تحلیل داده (Research Instruments & Tools)
 * بخش ۱۱: اعتبارسنجی، پایایی و کیفیت پژوهش (Research Quality)
 * بخش ۱۲: اخلاق پژوهش و حکمرانی داده
 *
 * نکته: ابزارهایی مثل NVivo روش تحقیق نیستند؛ نرم‌افزارهایی برای پشتیبانی
 * از اجرای برخی روش‌ها. مرز METHOD / TECHNIQUE / SOFTWARE / INSTRUMENT
 * در همین بخش رعایت شده است.
 */
return [
    // ---------------- ۱۰. ابزارها و نرم‌افزارها ----------------
    ['instrument-survey-tools', 'ابزار پیمایش', 'Survey Tools', 'INSTRUMENT', 'methodology-root',
        'ابزارهای ساخت، توزیع و جمع‌آوری پرسشنامه و نظرسنجی.'],
    ['software-google-forms', 'Google Forms', 'Google Forms', 'SOFTWARE', 'instrument-survey-tools', 'پلتفرم رایگان و مرورگری برای ساخت پرسشنامه و نظرسنجی آنلاین. پاسخ‌ها به‌صورت خودکار در جدول ذخیره می‌شوند و برای گردآوری سریع داده‌های کمّی از نمونه‌های کوچک تا متوسط کاربرد دارد.'],
    ['software-qualtrics', 'Qualtrics', 'Qualtrics', 'SOFTWARE', 'instrument-survey-tools', 'پلتفرم سازمانی و جامع برای طراحی، اجرا و تحلیل پرسشنامه و نظرسنجی آنلاین. در برابر ابزارهای رایگان، امکانات پیشرفته‌ی نمونه‌گیری، طرح آزمایشی و تحلیل پاسخ‌ها را در یک بستر واحد فراهم می‌کند.'],
    ['software-surveymonkey', 'SurveyMonkey', 'SurveyMonkey', 'SOFTWARE', 'instrument-survey-tools', 'پلتفرم ابری و تخصصی ساخت پرسشنامه و نظرسنجی آنلاین با قالب‌های آماده و گزارش‌های تحلیلی خودکار. برای گردآوری سریع داده‌های کمّی و مرور اولیه‌ی نتایج کاربرد دارد.'],

    ['instrument-interview-tools', 'ابزار مصاحبه', 'Interview Tools', 'INSTRUMENT', 'methodology-root',
        'ابزارهای ثبت و اجرای مصاحبه.'],
    ['instrument-audio-recorder', 'ضبط‌کننده صوتی', 'Audio Recorder', 'INSTRUMENT', 'instrument-interview-tools', 'دستگاه یا اپلیکیشن ثبت صدا برای ضبط کامل مصاحبه‌ها و گفتار شرکت‌کنندگان؛ برخلاف نرم‌افزار پیاده‌سازی مصاحبه که اجرای گفت‌وگو را مدیریت می‌کند، وظیفه‌اش ثبت است. ثبت دقیق صدا پیش‌شرط کدگذاری داده‌های مصاحبه در پژوهش‌های کیفی است.'],
    ['instrument-interview-software', 'نرم‌افزار پیاده‌سازی مصاحبه', 'Interview Administration Software', 'SOFTWARE', 'instrument-interview-tools',
        'نرم‌افزارهای اجرای مصاحبه آنلاین و نیمه‌ساختاریافته.'],

    ['software-qual-analysis', 'نرم‌افزارهای تحلیل کیفی', 'Qualitative Analysis Software', 'SOFTWARE', 'methodology-root',
        'نرم‌افزارهای مدیریت و کدگذاری داده‌های کیفی (CAQDAS).'],
    ['software-nvivo', 'NVivo', 'NVivo', 'SOFTWARE', 'software-qual-analysis', 'نرم‌افزاری برای مدیریت و کدگذاری داده‌های کیفی که با متن، صوت، ویدئو و محتوای وب کار می‌کند. تمایز آن از ابزارهای برادر، پشتیبانی گسترده از منابع چندگانه‌ی داده و رابط منسجم برای کدگذاری مضمونی است.'],
    ['software-atlas-ti', 'ATLAS.ti', 'ATLAS.ti', 'SOFTWARE', 'software-qual-analysis', 'نرم‌افزاری برای مدیریت و کدگذاری داده‌های کیفی که با ترسیم شبکه‌ی روابط و یادداشت‌های تحلیلی، ساختار کشف مفاهیم را شفاف می‌کند. امکان کدگذاری گروهی و مشارکتی از تمایزهای آن با ابزارهای برادر است.'],
    ['software-maxqda', 'MAXQDA', 'MAXQDA', 'SOFTWARE', 'software-qual-analysis', 'نرم‌افزاری برای مدیریت و کدگذاری داده‌های کیفی و کمّی در یک بستر واحد. تمایز شاخص آن از ابزارهای برادر، امکان تحلیل آمیخته است. می‌توان متغیرهای کمّی را به کدهای کیفی متصل کرد و نتایج هر دو شاخه را در کنار هم گزارش کرد.'],

    ['software-quant-analysis', 'نرم‌افزارهای تحلیل کمّی', 'Quantitative Analysis Software', 'SOFTWARE', 'methodology-root',
        'نرم‌افزارهای آماری برای تحلیل داده‌های عددی.'],
    ['software-spss', 'SPSS', 'SPSS', 'SOFTWARE', 'software-quant-analysis', 'نرم‌افزار آماری با رابط گرافیکی برای تحلیل داده‌های کمّی، از آمار توصیفی تا رگرسیون و سنجش روایی و پایایی پرسشنامه. رابط بصری آن، انجام تحلیل‌ها را برای پژوهشگرانی که با برنامه‌نویسی آشنا نیستند ساده‌تر می‌کند.'],
    ['software-stata', 'Stata', 'Stata', 'SOFTWARE', 'software-quant-analysis', 'نرم‌افزار آماری مبتنی بر دستورات و رابط گرافیکی برای تحلیل داده‌های کمّی با تمرکز بر اقتصادسنجی و تحلیل‌های طولی. در برابر ابزارهای هم‌رده، پوشش قوی داده‌های تکراری و نمونه‌گیری پیچیده از تمایزهای آن است.'],
    ['software-sas', 'SAS', 'SAS', 'SOFTWARE', 'software-quant-analysis', 'نرم‌افزار سازمانی و پیشرفته برای تحلیل داده‌های کمّی با تأکید بر پایداری و تکرارپذیری خروجی. در پژوهش‌های بالینی و دارویی، به‌دلیل قابلیت ممیزی دقیق و کنترل کامل نسخه‌ها، ابزار مرجع بسیاری از نهادهای نظارتی است.'],

    ['software-programming', 'نرم‌افزارهای آماری و برنامه‌نویسی', 'Statistical Programming', 'SOFTWARE', 'methodology-root',
        'زبان‌ها و محیط‌های برنامه‌نویسی آماری.'],
    ['software-r', 'R', 'R', 'SOFTWARE', 'software-programming', 'زبان و محیط آماری متن‌باز و کدبنیان برای تحلیل داده‌های کمّی و رسم نمودار؛ برخلاف ابزارهای گرافیکی، با کد کار می‌کند و برخلاف زبان‌های عمومی، تخصصی آماری است. از مدل‌های خطی تا تحلیل بیزی و یادگیری ماشین را پوشش می‌دهد و شفافیت کد، بازتولیدپذیری را تقویت می‌کند.'],
    ['software-python', 'Python', 'Python', 'SOFTWARE', 'software-programming', 'زبان برنامه‌نویسی عمومی با اکوسیستم غنی برای تحلیل داده، پردازش زبان طبیعی و یادگیری ماشین. در پژوهش‌های کمّی و داده‌محور، امکان تحلیل‌های بزرگ‌مقیاس و خودکار را فراهم می‌کند.'],

    ['software-sem', 'نرم‌افزارهای مدل‌سازی معادلات ساختاری', 'SEM Software', 'SOFTWARE', 'methodology-root', 'دسته‌ای از ابزارهایی برای برآورد و آزمون مدل‌های معادلات ساختاری و روابط علّی میان سازه‌ها. از اعضای شاخص این دسته، AMOS و SmartPLS است.'],
    ['software-amos', 'AMOS', 'AMOS', 'SOFTWARE', 'software-sem', 'نرم‌افزار آزمون و برآورد مدل‌های معادلات ساختاری با رویکرد مبتنی بر کوواریانس و رابط گرافیکی برای ترسیم مسیرها. در برابر ابزارهای مبتنی بر روش حداقل مربعات جزئی، بر پایه‌ی فرض نرمال بودن داده و آزمون برازش کل مدل استوار است.'],
    ['software-smartpls', 'SmartPLS', 'SmartPLS', 'SOFTWARE', 'software-sem', 'نرم‌افزار آزمون و برآورد مدل‌های معادلات ساختاری با رویکرد حداقل مربعات جزئی و رابط گرافیکی. در برابر ابزارهای مبتنی بر کوواریانس، برای داده‌های غیرنرمال و نمونه‌های کوچک‌تر مقاوم‌تر است و تمرکز آن بر پیش‌بینی واریانس سازه‌هاست.'],
    ['software-lavaan', 'lavaan', 'lavaan', 'SOFTWARE', 'software-sem', 'کتابخانه‌ی متن‌باز برای مدل‌سازی معادلات ساختاری که با نوشتن کد، از تحلیل عاملی تا مدل‌های ساختاری و علّی را پوشش می‌دهد. در برابر ابزارهای گرافیکی، انعطاف کامل در طراحی مدل و شفافیت کامل کد را فراهم می‌کند.'],

    ['software-network-analysis', 'نرم‌افزارهای تحلیل شبکه', 'Network Analysis Software', 'SOFTWARE', 'methodology-root', 'دسته‌ای از ابزارهایی برای تجزیه‌وتحلیل شبکه‌های اجتماعی، از سنجش مرکزیت و خوشه‌یابی تا بصری‌سازی ساختار روابط. از اعضای شاخص این دسته، Gephi و UCINET است.'],
    ['software-gephi', 'Gephi', 'Gephi', 'SOFTWARE', 'software-network-analysis', 'نرم‌افزار متن‌باز بصری‌سازی و تحلیل شبکه‌های اجتماعی با تأکید بر نمایش تعاملی شبکه‌های بزرگ. در برابر ابزارهای تحلیلی هم‌رده، قدرت اصلی آن در طراحی نقشه‌های خوانای شبکه و شناسایی الگوهای ساختاری از روی شکل است.'],
    ['software-ucinet', 'UCINET', 'UCINET', 'SOFTWARE', 'software-network-analysis', 'نرم‌افزار تحلیلی جامع شبکه‌های اجتماعی برای محاسبه‌ی شاخص‌های مرکزیت، تراکم و زیرگروه‌ها. در برابر ابزارهای بصری‌سازی، تمرکز آن بر تحلیل کمّی دقیق شبکه با جداول ورودی و خروجی است.'],
    ['software-pajek', 'Pajek', 'Pajek', 'SOFTWARE', 'software-network-analysis', 'نرم‌افزار تحلیل گراف و شبکه برای محاسبه‌ی شاخص‌های ساختاری شبکه‌های بزرگ. در پژوهش‌های شبکه، برای پردازش سریع شبکه‌های حجیمی که از توان ابزارهای هم‌رده فراتر می‌روند کاربرد دارد.'],

    ['software-text-analysis', 'نرم‌افزارهای تحلیل متن', 'Text Analysis Software', 'SOFTWARE', 'methodology-root', 'دسته‌ای از ابزارهایی برای تحلیل کمّی زبان در پیکره‌های متنی، از شمارش و فراوانی واژگان تا بررسی روابط معنایی. از اعضای شاخص این دسته، AntConc و Voyant Tools است.'],
    ['software-antconc', 'AntConc', 'AntConc', 'SOFTWARE', 'software-text-analysis', 'نرم‌افزار رایگان تحلیل پیکره‌ی متنی برای شمارش، فراوانی، هم‌پوشانی و هم‌هنگامی واژگان در متن. در پژوهش‌های زبانی و تحلیل کمّی محتوای متنی، سرعت و سادگی آن برای پردازش پیکره‌های بزرگ کاربرد دارد.'],
    ['software-voyant', 'Voyant Tools', 'Voyant Tools', 'SOFTWARE', 'software-text-analysis', 'مجموعه‌ی ابزارهای مرورگری و رایگان تحلیل پیکره‌ی متنی بدون نیاز به نصب. در یک صفحه، فرکانس واژگان، نمودارهای زمانی و تحلیل‌های اولیه‌ی متنی را به‌صورت تعاملی ارائه می‌کند و برای مرور سریع پیکره کاربرد دارد.'],

    ['software-digital-data', 'نرم‌افزارهای تحلیل داده دیجیتال', 'Digital Data Tools', 'SOFTWARE', 'methodology-root', 'دسته‌ای از ابزارهایی برای گردآوری، پردازش و تحلیل داده‌های دیجیتال مانند داده‌های وب و سرویس‌های برنامه‌نویسی. اعضای شاخص این دسته، ابزارهای استخراج خودکار داده از وب و ابزارهای دسترسی به داده‌های آنلاین است.'],
    ['instrument-web-extraction', 'ابزارهای استخراج وب', 'Web Extraction Tools', 'SOFTWARE', 'software-digital-data',
        'ابزارهای استخراج خودکار داده از وب.'],
    ['instrument-api-tools', 'ابزارهای API', 'API Tools', 'SOFTWARE', 'software-digital-data',
        'ابزارهای دسترسی و پردازش داده از سرویس‌های برنامه‌نویسی.'],

    ['software-discourse-analysis', 'نرم‌افزارهای تحلیل گفتمان', 'Discourse Analysis Software', 'SOFTWARE', 'methodology-root',
        'نرم‌افزارهای کدگذاری و تحلیل متن گفتمانی.'],

    ['software-reference-management', 'نرم‌افزارهای مدیریت منابع', 'Reference Management', 'SOFTWARE', 'methodology-root', 'دسته‌ای از ابزارهایی برای گردآوری، سازمان‌دهی و ارجاع خودکار منابع پژوهشی در نوشتار علمی. از اعضای شاخص این دسته، Zotero و EndNote است.'],
    ['software-zotero', 'Zotero', 'Zotero', 'SOFTWARE', 'software-reference-management', 'ابزار متن‌باز مدیریت منابع پژوهشی با افزونه‌ی مرورگر برای ثبت خودکار اطلاعات منابع. رایگان بودن، اتصال آسان به ویرایشگرهای متنی و اشتراک‌گذاری کتابخانه در پژوهش‌های گروهی از تمایزهای آن است.'],
    ['software-endnote', 'EndNote', 'EndNote', 'SOFTWARE', 'software-reference-management', 'نرم‌افزار مدیریت منابع پژوهشی و ارجاع‌دهی خودکار در نوشتار علمی. در برابر ابزارهای رایگان، یکپارچگی عمیق با ویرایشگرهای متنی و کاربرد سازمانی در پژوهش‌های انسانی و پزشکی از تمایزهای آن است.'],
    ['software-mendeley', 'Mendeley', 'Mendeley', 'SOFTWARE', 'software-reference-management', 'نرم‌افزار مدیریت منابع پژوهشی که کتابخانه‌ی ارجاعات، ذخیره‌سازی فایل مقاله و فضای کاری مشترک را در یک سرویس رایگان ترکیب می‌کند. یکپارچگی مدیریت منابع و هم‌کاری گروهی از تمایزهای آن با ابزارهای برادر است.'],

    // ---------------- ۱۱. کیفیت پژوهش ----------------
    ['quality-root', 'کیفیت و اعتبار پژوهش', 'Research Quality', 'QUALITY', 'methodology-root',
        'نظام معیارهای سنجش صحت، قابلیت اتکا و اعتبار یافته‌های پژوهش در هر روش‌شناسی.'],

    // ۱۱.۱ کمّی
    ['quality-quant', 'کیفیت پژوهش کمّی', 'Quantitative Research Quality', 'QUALITY', 'quality-root',
        'معیارهای روایی و پایایی در پژوهش‌های کمّی.',
        ['qualifies_for' => ['کمّی']]],
    ['quality-content-validity', 'روایی محتوا', 'Content Validity', 'QUALITY', 'quality-quant',
        'نحوه‌ی پوشش مناسب ابزار از کل سازه‌ی مورد سنجش.'],
    ['quality-construct-validity', 'روایی سازه', 'Construct Validity', 'QUALITY', 'quality-quant',
        'درجه‌ای که ابزار سازه‌ی نظری مورد نظر را واقعاً می‌سنجد.'],
    ['quality-criterion-validity', 'روایی ملاکی', 'Criterion Validity', 'QUALITY', 'quality-quant',
        'همبستگی نمره‌ی ابزار با یک ملاک بیرونی معتبر.'],
    ['quality-convergent-validity', 'روایی همگرا', 'Convergent Validity', 'QUALITY', 'quality-quant',
        'همبستگی قوی با ابزارهایی که همان سازه را می‌سنجند.'],
    ['quality-discriminant-validity', 'روایی واگرا', 'Discriminant Validity', 'QUALITY', 'quality-quant',
        'عدم همبستگی قوی با ابزارهایی که سازه‌های متفاوت می‌سنجند.'],
    ['quality-retest-reliability', 'پایایی بازآزمایی', 'Test-Retest Reliability', 'QUALITY', 'quality-quant',
        'ثبات نمره‌ها در دو زمان از یک ابزار.'],
    ['quality-cronbach-alpha', 'آلفای کرونباخ', 'Cronbach Alpha', 'QUALITY', 'quality-quant',
        'شاخص پایایی درونی ابزار بر پایه‌ی همبستگی آیتم‌ها.'],
    ['quality-mcdonald-omega', 'امگای مک‌دونالد', "McDonald Omega", 'QUALITY', 'quality-quant',
        'جایگزین تک‌عاملی و دقیق‌تر آلفای کرونباخ برای پایایی درونی.'],
    ['quality-inter-rater', 'پایایی بین ارزیابان', 'Inter-rater Reliability', 'QUALITY', 'quality-quant',
        'هم‌نوازی قضاوت چند ارزیاب مستقل.'],
    ['quality-internal-external', 'اعتبار درونی و بیرونی', 'Internal and External Validity', 'QUALITY', 'quality-quant',
        'اعتبار درونی: صحت استنتاج علّی؛ اعتبار بیرونی: تعمیم‌پذیری یافته.'],

    // ۱۱.۲ کیفی
    ['quality-qual', 'کیفیت پژوهش کیفی', 'Qualitative Research Quality', 'QUALITY', 'quality-root',
        'معیارهای اعتبار پژوهش کیفی (چارچوب لینکلن و گوبا).',
        ['qualifies_for' => ['کیفی']]],
    ['quality-credibility', 'اعتبارپذیری', 'Credibility', 'QUALITY', 'quality-qual',
        'معادل روایی در کیفی: آیا یافته‌ها از منظر شرکت‌کنندگان قابل‌قبول است؟'],
    ['quality-transferability', 'انتقال‌پذیری', 'Transferability', 'QUALITY', 'quality-qual',
        'معادل تعمیم‌پذیری: امکان انتقال یافته به بافت‌های دیگر (با توصیف غنی).'],
    ['quality-dependability', 'اتکاپذیری', 'Dependability', 'QUALITY', 'quality-qual',
        'معادل پایایی: قابلیت اتکای فرایند پژوهش در صورت تکرار در همان بافت.'],
    ['quality-confirmability', 'تأییدپذیری', 'Confirmability', 'QUALITY', 'quality-qual',
        'معادل بی‌طرفی: یافته صرفا‌ً از داده برمی‌خیزد، نه از پیش‌فرض پژوهشگر.'],
    ['quality-reflexivity', 'بازاندیشی پژوهشگر', 'Researcher Reflexivity', 'QUALITY', 'quality-qual',
        'آگاهی و گزارش‌دهی پژوهشگر از جایگاه و اثر خود بر داده.'],
    ['quality-triangulation', 'مثلث‌سازی', 'Triangulation', 'QUALITY', 'quality-qual',
        'استفاده از چند منبع، روش، پژوهشگر یا نظریه برای تقویت اعتبار.'],
    ['quality-member-checking', 'بررسی مشارکت‌کنندگان', 'Member Checking', 'QUALITY', 'quality-qual',
        'بازگرداندن تفسیرها به شرکت‌کنندگان برای تأیید و اصلاح.'],
    ['quality-audit-trail', 'ممیزی پژوهش', 'Audit Trail', 'QUALITY', 'quality-qual',
        'ردپای شفاف و قابل‌بازبینی تصمیمات پژوهشی.'],
    ['quality-thick-description', 'توصیف غنی', 'Thick Description', 'QUALITY', 'quality-qual',
        'تصویرسازی پُربافت از بافت و معنا برای امکان انتقال‌پذیری.'],

    // ۱۱.۳ آمیخته
    ['quality-mixed', 'کیفیت پژوهش آمیخته', 'Mixed Methods Quality', 'QUALITY', 'quality-root',
        'معیارهای کیفیت ویژه‌ی پژوهش‌های آمیخته.',
        ['qualifies_for' => ['آمیخته']]],
    ['quality-mixed-validity', 'اعتبار طراحی آمیخته', 'Mixed Methods Design Validity', 'QUALITY', 'quality-mixed',
        'سازگاری انتخاب طرح آمیخته با هدف و پرسش پژوهش.'],
    ['quality-integration-quality', 'کیفیت یکپارچه‌سازی داده‌ها', 'Data Integration Quality', 'QUALITY', 'quality-mixed',
        'صحت و شفافیت ترکیب داده‌های کمّی و کیفی در تحلیل.'],
    ['quality-integrated-inference', 'استنتاج‌های یکپارچه', 'Integrated Inference', 'QUALITY', 'quality-mixed',
        'برآورد ترکیبی از یافته‌های هر دو شاخه‌ی داده.'],
    ['quality-quant-qual-consistency', 'سازگاری یافته‌های کمّی و کیفی', 'Quant-Qual Consistency', 'QUALITY', 'quality-mixed',
        'هماهنگی (یا تبیین تناقض) نتایج دو شاخه‌ی داده.'],

    // ---------------- ۱۲. اخلاق پژوهش و حکمرانی داده ----------------
    ['ethics-root', 'اخلاق پژوهش و حکمرانی داده', 'Research Ethics and Data Governance', 'ETHICS', 'methodology-root',
        'نظام اصول و استانداردهای اخلاقی در طراحی، اجرا، تحلیل و انتشار پژوهش.'],
    ['ethics-informed-consent', 'رضایت آگاهانه', 'Informed Consent', 'ETHICS', 'ethics-root',
        'دریافت آگاهی کامل و موافقت داوطلبانه‌ی شرکت‌کننده قبل از شرکت در پژوهش.'],
    ['ethics-confidentiality', 'محرمانگی', 'Confidentiality', 'ETHICS', 'ethics-root',
        'نگه‌داشتن اطلاعات شرکت‌کنندگان پنهان و قابل‌دسترسی نبودن برای غیرمصرح.'],
    ['ethics-anonymization', 'ناشناس‌سازی', 'Anonymization', 'ETHICS', 'ethics-root',
        'حذف یا تبدیل شناسه‌های داده برای جلوگیری از شناسایی افراد.'],
    ['ethics-data-protection', 'حفاظت از داده‌های شخصی', 'Personal Data Protection', 'ETHICS', 'ethics-root',
        'رعایت مقررات حفاظت داده (مانند GDPR) در گردآوری و نگهداری.'],
    ['ethics-digital-research', 'اخلاق پژوهش دیجیتال', 'Digital Research Ethics', 'ETHICS', 'ethics-root',
        'اصول اخلاقی پژوهش در محیط‌های آنلاین: مرز عمومی/خصوصی، ربات‌ها، داده‌های باز.'],
    ['ethics-social-media', 'اخلاق پژوهش در شبکه‌های اجتماعی', 'Social Media Research Ethics', 'ETHICS', 'ethics-root',
        'چارچوب‌های اخلاقی جمع‌آوری و تحلیل داده کاربران شبکه‌های اجتماعی.'],
    ['ethics-vulnerable-groups', 'اخلاق پژوهش با گروه‌های آسیب‌پذیر', 'Ethics of Research with Vulnerable Groups', 'ETHICS', 'ethics-root',
        'ملاحظات ویژه‌ی پژوهش با کودکان، بیماران و جوامع آسیب‌پذیر.'],
    ['ethics-conflict-interest', 'تعارض منافع', 'Conflict of Interest', 'ETHICS', 'ethics-root',
        'شفاف‌سازی هرگونه منافع مالی یا غیرمالی که می‌تواند پژوهش را منحرف کند.'],
    ['ethics-transparency', 'شفافیت پژوهش', 'Research Transparency', 'ETHICS', 'ethics-root',
        'گزارش شفاف طراحی، داده، روش و محدودیت‌های پژوهش.'],
    ['ethics-preregistration', 'پیش‌ثبت پژوهش', 'Preregistration', 'ETHICS', 'ethics-root',
        'ثبت پیش‌دست‌ه‌موردی طرح و تحلیل قبل از گردآوری داده برای جلوگیری از P-Hacking.'],
    ['ethics-open-data', 'دسترسی آزاد به داده', 'Open Data Access', 'ETHICS', 'ethics-root',
        'قانون‌گذاری داده‌های پژوهش (با رعایت حریم خصوصی).'],
    ['ethics-reproducibility', 'بازتولیدپذیری', 'Reproducibility', 'ETHICS', 'ethics-root',
        'امکان تکرار تحلیل با داده و کد مشابه و رسیدن به نتیجه مشابه.'],
    ['ethics-repeatability', 'تکرارپذیری', 'Repeatability', 'ETHICS', 'ethics-root',
        'امکان تکرار کل پژوهش در شرایط مشابه و رسیدن به نتیجه مشابه.'],
    ['ethics-data-management', 'مدیریت و نگهداری داده‌های پژوهش', 'Research Data Management', 'ETHICS', 'ethics-root',
        'برنامه‌ی مدون برای نگهداری، ذخیره‌سازی و حذف داده‌های پژوهش.'],
    ['ethics-beneficence', 'فایده‌مندی (بهره‌وری)', 'Beneficence', 'ETHICS', 'ethics-root',
        'تعهد پژوهشگر به ارتقای رفاه شرکت‌کنندگان و جامعه، حداکثر کردن فایده و حداقل کردن آسیب در طراحی و اجرای پژوهش.',
        [
            'definition' => 'تعهد اخلاقی پژوهشگر به ارتقای فعالانه‌ی رفاه شرکت‌کنندگان و جامعه، و به حداقل رساندن هرگونه آسیب، رنج یا ضرر احتمالی در طراحی، اجرا و انتشار پژوهش.',
            'why_important' => 'پیشگیری از تکرار فاجعه‌های تاریخی (نازی‌ها، ترانسونیا)؛ حفظ اعتماد عمومی به پژوهش؛ الزام اخلاقی و حقوقی کمیته‌های اخلاق؛ تضمین اینکه پژوهش صرفاً در خدمت دانش نیست، بلکه در خدمت انسان هم هست.',
            'how_to_ensure' => [
                'انجام ارزیابی هزینه/فایده (risk-benefit assessment) قبل از شروع پژوهش',
                'حداقل‌سازی زمان، شدت و تعداد سؤالات/آزمون‌ها برای کاهش خستگی و رنج',
                'ارائه‌ی منابع حمایتی (ارائه مشاوره، ارجاع به متخصص) پس از پژوهش‌های حساس',
                'طراحی پروتکل‌های قطع پژوهش در صورت مشاهده‌ی آسیب',
                'اجتناب از سؤالات/مداخلاتی که رنج روانی غیرضروری ایجاد می‌کنند',
                'تصویب طرح توسط کمیته‌ی اخلاق پیش از اجرای هرگونه پژوهش',
            ],
            'violations' => [
                'ادامه‌ی پژوهش در صورتی‌که خطرات به‌مراتب بیش از منافع باشد',
                'مشارکت اجباری گروه‌های آسیب‌پذیر در پژوهش‌های بی‌فایده',
                'حذف پروتکل‌های حمایتی پس از پژوهش‌های حساس',
                'طراحی پرسشنامه/مصاحبه با سؤالات تهاجمی یا توهین‌آمیز بدون ضرورت علمی',
            ],
            'examples' => 'در پژوهش با بازماندگان تروما، پژوهشگر باید منابع مشاوره رایگان را برای شرکت‌کنندگانی که در طول مصاحبه دچار ترومای مجدد می‌شوند، در دسترس بگذارد و در صورت مشاهده‌ی نشانه‌ی آسیب، مصاحبه را متوقف کند.',
            'guidelines' => ['Belmont Report (1979): Beneficence', 'APA Ethics Code Principle A (Beneficence and Nonmaleficence)', 'دستورالعمل‌های کمیته‌ی اخلاق پژوهش: ارزیابی ریسک-فایده'],
            'sources' => ['Belmont Report (1979)', 'APA Ethical Principles of Psychologists and Code of Conduct'],
        ]],
];
