<?php
namespace Resaname\Structure;

/**
 * طبقه‌بندی رسمیِ نوعِ محتوا.
 * کارت‌ها لزوماً «نظریه» نیستند؛ ممکن است مدل، مفهوم، اثر، مکتب، پدیده،
 * روش پژوهش، استراتژی و ... باشند. هوش مصنوعی در هر بررسی باید نوع واقعیِ
 * محتوا را از فهرست رسمیِ زیر شناسایی کند و worker پس از اعتبارسنجی،
 * آن را در editorial_documents.content_category / content_type ذخیره می‌کند.
 */
final class ContentTypes {
    /** کلید دسته => [label: نام فارسی دسته, types: لیبل‌های رسمیِ انواع] */
    public const GROUPS = [
        'theoretical' => [
            'label' => 'ساختارهای نظری و تبیینی',
            'types' => ['نظریه (Theory)', 'مدل (Model)', 'چارچوب نظری (Theoretical Framework)', 'پارادایم (Paradigm)', 'فرضیه (Hypothesis)', 'قانون علمی (Law)'],
        ],
        'intellectual' => [
            'label' => 'جریان‌ها و سنت‌های فکری',
            'types' => ['مکتب (School)', 'جریان فکری (Intellectual Movement)', 'سنت پژوهشی (Research Tradition)', 'گفتمان (Discourse)', 'ایدئولوژی (Ideology)'],
        ],
        'knowledge' => [
            'label' => 'قلمروهای دانش',
            'types' => ['حوزه مطالعاتی (Field of Study)', 'رشته علمی (Discipline)', 'زیررشته (Subdiscipline)', 'حوزه میان‌رشته‌ای (Interdisciplinary Field)'],
        ],
        'concept' => [
            'label' => 'مفاهیم و پدیده‌ها',
            'types' => ['مفهوم (Concept)', 'سازه (Construct)', 'اثر (Effect)', 'پدیده (Phenomenon)', 'فرایند (Process)', 'اصل (Principle)'],
        ],
        'method' => [
            'label' => 'ابزارهای علمی و پژوهشی',
            'types' => ['روش پژوهش (Research Method)', 'روش‌شناسی (Methodology)', 'تکنیک (Technique)', 'ابزار (Tool)', 'شاخص (Indicator)', 'مقیاس (Scale)', 'سنجه (Measure)'],
        ],
        'applied' => [
            'label' => 'ساختارهای کاربردی',
            'types' => ['استراتژی (Strategy)', 'الگو (Pattern)', 'چارچوب اجرایی (Operational Framework)', 'استاندارد (Standard)', 'دستورالعمل (Guideline)'],
        ],
    ];

    private static ?array $byType = null;

    /** دسته‌ی متناظر با یک لیبلِ نوع (یا null اگر در فهرست رسمی نباشد) */
    public static function categoryOf(string $type): ?string {
        $type = trim($type);
        if (self::$byType === null) {
            self::$byType = [];
            foreach (self::GROUPS as $cat => $g) {
                foreach ($g['types'] as $t) self::$byType[$t] = $cat;
            }
        }
        return self::$byType[$type] ?? null;
    }

    /** لیبل فارسیِ دسته (برای نمایش) */
    public static function categoryLabel(?string $cat): string {
        return self::GROUPS[$cat]['label'] ?? '';
    }

    /**
     * اعتبارسنجیِ خروجیِ مدل.
     * برگشت: ['category'=>کلید,'type'=>لیبل رسمی] یا null اگر نوع در فهرست رسمی نباشد.
     * (اگر type درست ولی category اشتباه آمده باشد، category صحیح استنباط می‌شود.)
     */
    public static function validate($category, $type): ?array {
        $type = trim((string)$type);
        $real = self::categoryOf($type);
        if ($real === null) return null;
        return ['category' => $real, 'type' => $type];
    }

    /** فهرست گروه‌بندی‌شده برای پرامپتِ هوش مصنوعی */
    public static function promptList(): string {
        $letters = ['الف', 'ب', 'ج', 'د', 'هـ', 'و'];
        $lines = []; $i = 0;
        foreach (self::GROUPS as $cat => $g) {
            $lines[] = $letters[$i].' ('.$cat.') '.$g['label'].': '.implode('، ', $g['types']);
            $i++;
        }
        return implode("\n", $lines);
    }
}
