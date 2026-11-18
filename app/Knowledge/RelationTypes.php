<?php
namespace Resaname\Knowledge;

/**
 * ثبت‌نام‌ساز رسمیِ انواع روابط بین‌موجودیتی (Knowledge Relations)
 *
 * هر رابطه در kb_relations به‌صورت رکورد مستقل ذخیره می‌شود؛ این کلاس
 * «تک منبع حقیقت» برای اعتبارسنجی کد رابطه در bin/kb.php، سرویس MCP و
 * پرامپت‌های AI است.
 *
 * فیلدها: [label_fa, label_en, description, symmetric]
 *  - symmetric=۱ یعنی رابطه متقارن است (مخالف‌گرد آن به‌طور ضمنی وجود دارد)
 *  - جهت خواندن: source [relation] target
 */
final class RelationTypes {

    public const RELATIONS = [
        'IS_A'          => ['از نوعِ', 'is a kind of', 'رابطه‌ی جنس‌به‌فصله: موجودیتِ source از نظره‌ی target است (مصاحبه عمیق از نوع فن پژوهش است).', 0],
        'PART_OF'       => ['زیرمجموعه', 'part of', 'source بخش یا زیرمجموعه‌ی target است (تحلیل مضمون جزء تحلیل داده‌های کیفی است).', 0],
        'BELONGS_TO'    => ['متعلق به', 'belongs to', 'source در قلمرو یا نظام target قرار دارد (مطالعات ایدئولوژی متعلق به مکتب انتقادی است).', 0],
        'INFLUENCED_BY' => ['متأثر از', 'influenced by', 'source در شکل‌گیری از target متأثر بوده است (مکتب بیرمنگام متأثر از مارکسیسم است).', 0],
        'INFLUENCED'    => ['تأثیرگذار بر', 'influences', 'برعکسِ INFLUENCED_BY؛ منبع مستقیمِ تأثیر را به مقصد می‌رساند.', 0],
        'CRITICIZES'    => ['نقدکننده', 'criticizes', 'source نقادِ دیدگاه‌ها یا نظام target است (سنت انتقادی، نقدکننده‌ی کارکردگرایی است).', 0],
        'EXTENDS'       => ['توسعه‌دهنده', 'extends', 'source، target را گسترش می‌دهد (پاساساختارگرایی، توسعه‌دهنده‌ی ساختارگرایی است).', 0],
        'CONTRADICTS'   => ['متناقض با', 'contradicts', 'مغایرت و تضاد مفهومی مستند بین دو دیدگاه.', 1],
        'RELATED_TO'    => ['مرتبط با', 'related to', 'رابطه‌ی عمومی مرتبط؛ وقتی نوع دقیق‌تر رابطه مشخص نیست.', 1],
        'APPLIES_TO'    => ['کاربرد در', 'applies to', 'کاربرد source در حوزه/حالت target (پارادایم اثبات‌گرایی کاربرد در رویکرد کمی دارد).', 0],
        'USES'          => ['استفاده می‌کند از', 'uses', 'source در اجرا از target بهره می‌برد (طرح قوم‌نگاری از مشاهده مشارکتی استفاده می‌کند).', 0],
        'MEASURES'      => ['اندازه‌گیری می‌کند', 'measures', 'source سازه یا کمیتی را اندازه‌گیری می‌کند (آلفای کرونباخ، پایایی را اندازه‌گیری می‌کند).', 0],
        'ANALYZES'      => ['تحلیل می‌کند', 'analyzes', 'source نوع داده یا مصادقی را تحلیل می‌کند (تحلیل شبکه، ساختار تعاملات را تحلیل می‌کند).', 0],
        'COLLECTS'      => ['گردآوری می‌کند', 'collects', 'source داده‌ای از نوع target را گردآوری می‌کند.', 0],
        'DEVELOPED_BY'  => ['توسعه‌یافته توسط', 'developed by', 'موجودیتِ source توسط شخص/مجموعه‌ی target توسعه یافته (مکتب فرانکفورت توسعه‌یافته توسط آدورنو).', 0],
        'PROPOSED_BY'   => ['پیشنهادشده توسط', 'proposed by', 'نظریه/مدل/روشِ source توسط شخص target پیشنهاد شده (نظریه برجسته‌سازی پیشنهادشده توسط مک‌کامبز).', 0],
        'OPERATIONALIZES' => ['عملیاتی‌سازی می‌کند', 'operationalizes', 'روش/فنِ source، مفهوم یا نظریه‌ی target را به اندازه‌های قابل اجرا تبدیل می‌کند.', 0],
        'TESTS'         => ['آزمون می‌کند', 'tests', 'روش یا طرحِ source برای آزمون نظریه‌ی target به‌کار می‌رود (پیمایش، نظریه استفاده و رضامندی را آزمون می‌کند).', 0],
        'DERIVED_FROM'  => ['برگرفته از', 'derived from', 'source از target استنباط یا مشتق شده است (نظریه شکاف آگاهی برگرفته از جریان‌های پژوهشی کلمبیا است).', 0],
        'COMPARED_WITH' => ['قابل مقایسه با', 'compared with', 'دو موجودیت هم‌سطح برای مقایسه و تقابل علمی ثبت شده‌اند.', 1],
    ];

    /** آیا کد رابطه معتبر است؟ */
    public static function exists(string $code): bool {
        return isset(self::RELATIONS[$code]);
    }

    public static function labelFa(string $code): string {
        return self::RELATIONS[$code][0] ?? $code;
    }

    public static function labelEn(string $code): string {
        return self::RELATIONS[$code][1] ?? $code;
    }

    public static function descriptionOf(string $code): string {
        return self::RELATIONS[$code][2] ?? '';
    }

    public static function isSymmetric(string $code): bool {
        return (bool)(self::RELATIONS[$code][3] ?? 0);
    }

    public static function all(): array {
        return self::RELATIONS;
    }

    /** فهرست برای پرامپتِ هوش مصنوعی (تشخیص خودکار روابط) */
    public static function promptList(): string {
        $lines = [];
        foreach (self::RELATIONS as $code => $r) {
            $sym = $r[3] ? ' (متقارن)' : '';
            $lines[] = "$code — $r[0] ($r[1])$sym";
        }
        return implode("\n", $lines);
    }
}
