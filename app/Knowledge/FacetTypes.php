<?php
namespace Resaname\Knowledge;

/**
 * ثبت‌نام‌ساز رسمیِ ابعاد طبقه‌بندی چندبُعدی (Facet Dimensions)
 *
 * فاکت‌ها ویژگی‌های مستقلی هستند که محدود به درخت سلسله‌مراتب نمی‌شوند؛
 * هر موجودیت می‌تواند چندین فاکت در ابعاد مختلف داشته باشد
 * (رویکرد کمی/کیفی، نوع داده، مقیاس تحلیل، سنت نظری و ...).
 *
 * فیلدها: [label_fa, label_en, description]
 * مقادیر فاکت‌ها آزاد (متن) هستند و در seed پیشنهادی ثبت می‌شوند.
 */
final class FacetTypes {

    public const DIMENSIONS = [
        'discipline'            => ['حوزه علمی', 'Discipline',
            'حوزه‌ی علمی اصلی که موجودیت در آن کاربرد دارد (علوم ارتباطات، جامعه‌شناسی، روان‌شناسی).'],
        'theoretical_tradition' => ['سنت نظری', 'Theoretical Tradition',
            'سنت فکری محمول بر موجودیت (انتقادی، کارکردگرا، تعامل‌گرا، ساختارگرا).'],
        'approach'              => ['رویکرد روش‌شناختی', 'Methodological Approach',
            'رویکرد اجرای موجودیت (کمی، کیفی، آمیخته، محاسباتی).'],
        'epistemology'          => ['مبنا معرفت‌شناختی', 'Epistemological Basis',
            'پارادایم معرفت‌شناختی پشت‌موجودیت (اثبات‌گرا، تفسیری، برساخت‌گرا، انتقادی، پراگماتیست).'],
        'data_type'             => ['نوع داده', 'Data Type',
            'نوع داده‌ای که روش/فن با آن کار می‌کند (متنی، عددی، تصویری، صوتی، رفتاری، شبکه‌ای، اسنادی).'],
        'analysis_scale'        => ['مقیاس تحلیل', 'Analysis Scale',
            'مقیاس تحلیل موجودیت (خرد/بین‌فردی، میانه/سازمانی، کلان/جامعه‌شناختی).'],
        'application_domain'    => ['حوزه کاربرد', 'Application Domain',
            'حوزه‌های کاربردی اصلی (رسانه، سیاست، سازمان، سلامت، آموزش، توسعه).'],
        'time_frame'            => ['دوره زمانی', 'Time Frame',
            'دوره‌ی تاریخی پیدایش یا کاربرد (پیش از رسانه‌های توده‌ای، عصر رسانه‌های توده‌ای، دیجیتال، پلتفرمی).'],
        'qualifies_for'         => ['مناسب برای', 'Qualifies For',
            'کاربرندهای اصلی که معیار/اصول برای آن‌ها معنادار است (کمی، کیفی، آمیخته، همه).'],
    ];

    public static function exists(string $code): bool {
        return isset(self::DIMENSIONS[$code]);
    }

    public static function labelFa(string $code): string {
        return self::DIMENSIONS[$code][0] ?? $code;
    }

    public static function labelEn(string $code): string {
        return self::DIMENSIONS[$code][1] ?? $code;
    }

    public static function descriptionOf(string $code): string {
        return self::DIMENSIONS[$code][2] ?? '';
    }

    public static function all(): array {
        return self::DIMENSIONS;
    }

    /** فهرست برای پرامپتِ هوش مصنوعی (استخراج خودکار فاکت‌ها از متون) */
    public static function promptList(): string {
        $lines = [];
        foreach (self::DIMENSIONS as $code => $d) {
            $lines[] = "$code — $d[0] ($d[1]): " . $d[2];
        }
        return implode("\n", $lines);
    }
}
