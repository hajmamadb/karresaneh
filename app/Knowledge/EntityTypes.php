<?php
namespace Resaname\Knowledge;

/**
 * ثبت‌نام‌ساز رسمیِ انواع موجودیت‌های دانش (Knowledge Entity Types)
 *
 * «تک منبع حقیقت» برای هسته دانش علمی رسانامه:
 *  - جدول kb_entity_types از همین آرایه می‌سازد (seed)
 *  - bin/kb.php و سرویس MCP نوع هر موجودیت را با این فهرست اعتبارسنجی می‌کنند
 *  - پرامپت‌های AI (طبقه‌بندی هوشمند) از promptList() استفاده می‌کنند
 *
 * دو نظام مستقل:
 *  - theoretical      مکاتب، سنت‌ها، نظریه‌ها، مدل‌ها و مفاهیم (نظام فکری و نظری)
 *  - methodological   روش‌شناسی، طرح، فن، نمونه‌گیری، تحلیل، کیفیت، اخلاق
 *  - shared           اندیشمندان، آثار و ساختارهای میان‌دو‌نظام
 *
 * فیلدها: [label_fa, label_en, domain, description]
 */
final class EntityTypes {

    public const TYPES = [
        // ---------------- نظام فکری، نظری و پارادایمی ----------------
        'SCHOOL'     => ['مکتب فکری', 'School of Thought', 'theoretical',
            'جریان یا سنت منسجمی از اندیشه که حول یک مرکز علمی شکل گرفته و نظریه‌ها و روش‌های مشخصی دارد (مکتب فرانکفورت، مکتب شیکاگو).'],
        'TRADITION'  => ['سنت فکری', 'Intellectual Tradition', 'theoretical',
            'سنت و جریان فکری (معمولاً میان‌رشته‌ای) که مبانی فلسفی، روش‌شناختی یا تحلیلی یک حوزه را تأمین می‌کند (مارکسیسم، کارکردگرایی).'],
        'PARADIGM'   => ['پارادایم', 'Research Paradigm', 'theoretical',
            'مبانی هستی‌شناختی و معرفت‌شناختی پژوهش که چارچوب کلی پرسش، روش و تفسیر یافته را تعیین می‌کند (اثبات‌گرایی، تفسیرگرایی).'],
        'THEORY'     => ['نظریه', 'Theory', 'theoretical',
            'مجموعه‌ای منسجم از مفروضات و گزاره‌ها که یک پدیده را تبیین و پیش‌بینی می‌کند (نظریه برجسته‌سازی).'],
        'MODEL'      => ['مدل', 'Model', 'theoretical',
            'ترسیم ساده‌شده و شماتیک از فرآیند یا نظامی واقعی؛ توصیفی است، نه صرفاً تبیینی (مدل ارتباطی لاسول).'],
        'FRAMEWORK'  => ['چارچوب نظری', 'Theoretical Framework', 'theoretical',
            'چارچوب تحلیلی ترکیبی که پژوهشگر برای مطالعه خود از چند نظریه/مفهوم می‌سازد.'],
        'HYPOTHESIS' => ['فرضیه', 'Hypothesis', 'theoretical',
            'گزاره‌ی آزمودنی درباره رابطه دو یا چند متغیر.'],
        'LAW'        => ['قانون علمی', 'Scientific Law', 'theoretical',
            'گزاره‌ی عمومی و پایدار درباره رابطه‌ی پدیده‌ها که در طول زمان تأیید شده است.'],
        'CONCEPT'    => ['مفهوم', 'Concept', 'theoretical',
            'واحد بنیادین تفکر علمی؛ اصطلاح یا سازه‌ای که پدیده‌ای را نام‌گذاری و مفهوم‌سازی می‌کند (هژمونی، درگیری مخاطب).'],
        'DISCIPLINE' => ['رشته علمی', 'Discipline', 'theoretical',
            'حوزه‌ای منسجم از دانش با روش‌ها و مرزهای مشخص (علوم ارتباطات).'],
        'SUBFIELD'   => ['حوزه تخصصی', 'Subfield', 'theoretical',
            'حوزه مطالعاتی تخصصی زیر یک رشته یا مکتب (مطالعات شبکه‌های اجتماعی، مطالعات رسانه‌های دیجیتال).'],

        // ---------------- نظام روش‌شناسی و پژوهش ----------------
        'METHODOLOGY' => ['روش‌شناسی', 'Methodology', 'methodological',
            'نظام منسجم از اصول، روش‌ها و معیارهایی که منطق کلی پژوهش را تعیین می‌کند (روش‌شناسی کمی، کیفی، آمیخته).'],
        'METHOD'     => ['روش تحقیق', 'Research Method', 'methodological',
            'راهبرد کلی و منسجمی برای پاسخ به پرسش پژوهش (پیمایش، مطالعه موردی، آزمایش).'],
        'APPROACH'   => ['رویکرد پژوهش', 'Research Approach', 'methodological',
            'نگاه و روی کار پژوهش‌گر در قبال پرسش و داده که از پارادایم می‌آید و روش را شکل می‌دهد (رویکرد کمّی، کیفی، آمیخته، انتقادی، مشارکتی).'],
        'DESIGN'     => ['طرح پژوهش', 'Research Design', 'methodological',
            'طرح و استراتژی اجرایی پژوهش؛ نقشه‌ی دقیق از «چه کاری، در چه ترتیبی، با چه داده‌ای» (آزمایش واقعی، قوم‌نگاری).'],
        'STRATEGY'   => ['استراتژی پژوهش', 'Research Strategy', 'methodological',
            'راهبرد میان‌رده بین روش و فن؛ شیوه‌ی سازمان‌داده‌شده‌ی گردآوری و تحلیل در خدمت یک هدف.'],
        'TECHNIQUE'  => ['فن پژوهش', 'Research Technique', 'methodological',
            'فن و ابزار عملی گردآوری داده (مصاحبه نیمه‌ساختاریافته، مشاهده مشارکتی، پرسشنامه).'],
        'INSTRUMENT' => ['ابزار پژوهش', 'Research Instrument', 'methodological',
            'ابزار یا دسته ابزارهایی که برای اجرا و پشتیبانی از روش‌ها به‌کار می‌روند (مقیاس‌های سنجش، ابزارهای پیمایش).'],
        'SAMPLING'   => ['روش نمونه‌گیری', 'Sampling Method', 'methodological',
            'روش انتخاب نمونه از جامعه (تصادفی ساده، هدفمند، گلوله‌برفی).'],
        'ANALYSIS'   => ['روش تحلیل داده', 'Data Analysis Method', 'methodological',
            'روش و فن پردازش و تفسیر داده (تحلیل مضمون، رگرسیون، تحلیل شبکه‌های اجتماعی).'],
        'QUALITY'    => ['معیار کیفیت پژوهش', 'Quality Criterion', 'methodological',
            'معیار سنجش اعتبار، پایایی و کیفیت پژوهش (روایی، پایایی، اعتبارپذیری، مثلث‌سازی).'],
        'ETHICS'     => ['اصل اخلاقی پژوهش', 'Research Ethics', 'methodological',
            'اصل و استاندارد اخلاقی در طراحی، اجرا و انتشار پژوهش (رضایت آگاهانه، محرمانگی).'],
        'SOFTWARE'   => ['نرم‌افزار پژوهشی', 'Research Software', 'methodological',
            'نرم‌افزار یا ابزار محاسباتی مورد استفاده در پژوهش (NVivo، SPSS، Gephi).'],
        'PROTOCOL'   => ['پروتکل پژوهشی', 'Research Protocol', 'methodological',
            'دستورالعمل و راهنمای اجرایی استاندارد برای یک فرآیند پژوهشی (پروتکل تحلیل محتوا، پروتکل اخلاقی).'],
        'DATASET'    => ['داده و منبع پژوهشی', 'Dataset / Data Source', 'methodological',
            'مجموعه‌داده، آرشیو یا مخزن داده مورد استفاده پژوهشگران.'],

        // ---------------- ساختارهای میان‌دو‌نظام ----------------
        'PERSON'     => ['اندیشمند و نظریه‌پرداز', 'Scholar / Theorist', 'shared',
            'نظریه‌پرداز، دانشمند یا مؤثر علمی؛ صاحب نظریه، بنیان‌گذار مکتب یا توسعه‌دهنده‌ی روش (با پیوند به صفحه‌ی theorists در سایت).'],
        'WORK'       => ['اثر علمی بنیادین', 'Foundational Work', 'shared',
            'کتاب، مقاله یا پژوهش تأثیرگذار که مرجع یک نظریه، مکتب یا روش است.'],
        'PUBLICATION'=> ['نشریه علمی', 'Academic Journal / Publication', 'shared',
            'نشریه‌ی علمی داورشده (ژورنال، فصلنامه، کتابخانه) که مقالات و پژوهش‌ها در آن منتشر می‌شوند. شامل اطلاعات ISSN، نمایه‌ها، ضریب تأثیر و حوزه تخصصی.'],
        'DISPUTE'    => ['مناقشه علمی', 'Academic Dispute', 'shared',
            'اختلاف‌نظر و مناقشه‌ی نظری یا روش‌شناختی مستند بین دیدگاه‌ها یا اندیشمندان.'],
    ];

    private static ?array $byCode = null;

    /** آیا کد نوع معتبر است؟ */
    public static function exists(string $code): bool {
        return isset(self::TYPES[$code]);
    }

    /** برچسب فارسیِ نوع (یا خودِ کد اگر ناشناخته باشد) */
    public static function labelFa(string $code): string {
        return self::TYPES[$code][0] ?? $code;
    }

    /** برچسب انگلیسیِ نوع */
    public static function labelEn(string $code): string {
        return self::TYPES[$code][1] ?? $code;
    }

    /** نظام متعلق (theoretical | methodological | shared) */
    public static function domainOf(string $code): string {
        return self::TYPES[$code][2] ?? 'shared';
    }

    /** شرح نوع (برای نمایش و پرامپت) */
    public static function descriptionOf(string $code): string {
        return self::TYPES[$code][3] ?? '';
    }

    /** فهرست کامل برای seed / نمایش */
    public static function all(): array {
        return self::TYPES;
    }

    /** فهرست گروه‌بندی‌شده برای پرامپتِ هوش مصنوعی */
    public static function promptList(): string {
        $groups = ['theoretical' => 'نظام فکری، نظری و پارادایمی', 'methodological' => 'نظام روش‌شناسی و پژوهش', 'shared' => 'ساختارهای میان‌دو‌نظام'];
        $lines = [];
        foreach ($groups as $dom => $label) {
            $items = [];
            foreach (self::TYPES as $code => $t) {
                if ($t[2] !== $dom) continue;
                $items[] = "$code ($t[0] / $t[1])";
            }
            if ($items) $lines[] = "$label: " . implode('، ', $items);
        }
        return implode("\n", $lines);
    }
}
