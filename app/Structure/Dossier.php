<?php
namespace Resaname\Structure;

/**
 * داسییر ساختاریافته نظریه — «شناسنامه نظریه»
 *
 * ساختار رسمی و پایدار محتوای هر کارتِ نظریه: ۲۲ فیلد مستقل در ۴ گروه.
 * این کلاس «تک منبع حقیقت» (single source of truth) برای worker (پرامپت و
 * اعتبارسنجی)، صفحه‌ی بررسی (نمایش) و هر مصرف‌کننده‌ی بعدی (مثل موتور AI/RAG) است.
 *
 * سیاست پرکردن (نسخه ۱): فقط چیدمان + ویراستاری محتوای موجودِ کارت خام.
 * هیچ فکتی اختراع نمی‌شود؛ فیلدی که در منبع معادل ندارد، خالی می‌ماند و
 * در issues گزارش می‌شود. تکمیل فیلدهای خالی در «نوبت بعدی» (جستجوی عمیق) انجام می‌شود.
 */
final class Dossier {

    public const VERSION = 1;

    /** گروه‌های چهارگانه (به‌ترتیب نمایش) */
    public static function groups(): array {
        return [
            'identity'    => ['label' => 'شناسنامه و معرفی نظریه', 'order' => 1],
            'foundations' => ['label' => 'بنیان‌های نظری و مفهومی', 'order' => 2],
            'evaluation'  => ['label' => 'تبیین، توسعه و ارزیابی', 'order' => 3],
            'sources'     => ['label' => 'منابع و اطلاعات پژوهشی', 'order' => 4],
        ];
    }

    /**
     * ۲۲ فیلد رسمی. هر فیلد:
     *  key      کلید انگلیسی (نام ستون در JSON)
     *  label    عنوان فارسی برای نمایش
     *  group    گروه متعلق (کلید groups)
     *  type     text | array | json | number
     *  required آیا برای «محتوای کامل» ضروری است (خالی ماندن آن گزارش می‌شود)
     *  hint     راهنمای کوتاه برای پرکردن (به مدل و ویراستار)
     *  source   فیلدهای کارت خامی که محتوای این بخش از آن‌ها استخراج می‌شود (راهنمای نگاشت)
     */
    public static function fields(): array {
        return [
            // ---- گروه ۱: شناسنامه و معرفی نظریه ----
            ['key'=>'title_fa','label'=>'عنوان فارسی','group'=>'identity','type'=>'text','required'=>true,
             'hint'=>'نام رسمی و رایج نظریه به فارسی (بدون تکیه‌ی اضافی).','source'=>['title_fa']],
            ['key'=>'title_en','label'=>'عنوان انگلیسی','group'=>'identity','type'=>'text','required'=>true,
             'hint'=>'نام رسمی نظریه به انگلیسی/لاتین.','source'=>['title_en']],
            ['key'=>'aliases','label'=>'نام‌های جایگزین و اختصارات','group'=>'identity','type'=>'array','required'=>false,
             'hint'=>'آرایه‌ی رشته: نام‌های دیگر، ترجمه‌ها و اختصارات رایج.','source'=>['tags'],'subkeys'=>[]],
            ['key'=>'slug','label'=>'Slug','group'=>'identity','type'=>'text','required'=>true,
             'hint'=>'شناسه‌ی URL نظریه. هرگز دست‌نخورده باقی بماند.','source'=>['slug']],
            ['key'=>'summary','label'=>'معرفی کوتاه (خلاصه نظریه)','group'=>'identity','type'=>'text','required'=>true,
             'hint'=>'خلاصه‌ای ۱۰۰ تا ۱۵۰ کلمه که سریع مشخص کند نظریه چیست و چه مسئله‌ای را توضیح می‌دهد.','source'=>['core_logic']],
            ['key'=>'theorists','label'=>'نظریه‌پردازان اصلی','group'=>'identity','type'=>'array','required'=>true,
             'hint'=>'آرایه‌ی آبجکت: {name_fa: نام کامل فارسی, name_en: نام کامل انگلیسی, role: نقش و سهم هر نظریه‌پرداز}.','source'=>['theorists_text'],'subkeys'=>['name_fa'=>'نام فارسی','name_en'=>'نام انگلیسی','role'=>'نقش و سهم']],
            ['key'=>'year','label'=>'سال شکل‌گیری','group'=>'identity','type'=>'number','required'=>true,
             'hint'=>'سال یا بازه‌ی زمانی پیدایش نظریه (عدد یا رشته‌ای مانند 1951 یا 1947-1951).','source'=>['year']],
            ['key'=>'history','label'=>'تاریخچه شکل‌گیری','group'=>'identity','type'=>'text','required'=>true,
             'hint'=>'زمان، مکان، بستر تاریخی و شرایط علمی و اجتماعی پیدایش نظریه.','source'=>['detailed_analysis']],
            ['key'=>'field','label'=>'حوزه علمی و طبقه‌بندی','group'=>'identity','type'=>'json','required'=>true,
             'hint'=>'آبجکت: {parent: رشته مادر, subfield: زیرشاخه‌ی علمی, application: حوزه کاربرد, family: خانواده‌ی نظریه‌ای}.','source'=>['paradigm','macro_domain','approach','level','category_id'],'subkeys'=>['parent'=>'رشته مادر','subfield'=>'زیرشاخه‌ی علمی','application'=>'حوزه کاربرد','family'=>'خانواده‌ی نظریه‌ای']],

            // ---- گروه ۲: بنیان‌های نظری و مفهومی ----
            ['key'=>'main_problem','label'=>'مسئله و پرسش اصلی نظریه','group'=>'foundations','type'=>'text','required'=>true,
             'hint'=>'نظریه اساساً به دنبال پاسخ به چه مسئله‌ای است؟','source'=>['core_logic']],
            ['key'=>'assumptions','label'=>'مفروضات بنیادین','group'=>'foundations','type'=>'text','required'=>true,
             'hint'=>'پیش‌فرض‌های نظریه درباره انسان، جامعه، رسانه، ارتباطات یا واقعیت اجتماعی.','source'=>['detailed_analysis']],
            ['key'=>'key_concepts','label'=>'مفاهیم کلیدی','group'=>'foundations','type'=>'array','required'=>true,
             'hint'=>'آرایه‌ی آبجکت: {term_fa: اصطلاح فارسی, term_en: معادل انگلیسی, definition: تعریف دقیق}.','source'=>['constructs'],'subkeys'=>['term_fa'=>'اصطلاح','term_en'=>'معادل انگلیسی','definition'=>'تعریف']],
            ['key'=>'principles','label'=>'گزاره‌ها و اصول نظری','group'=>'foundations','type'=>'text','required'=>true,
             'hint'=>'اصول بنیادین و گزاره‌هایی که نظریه بر آن‌ها استوار است.','source'=>['detailed_analysis']],
            ['key'=>'model','label'=>'مدل و چارچوب مفهومی','group'=>'foundations','type'=>'json','required'=>false,
             'hint'=>'نمایش روابط میان متغیرها/عناصر. آبجکت: {description: توصیف روابط, elements: آرایه‌ی عناصر}.','source'=>['detailed_analysis'],'subkeys'=>['description'=>'توصیف روابط','elements'=>'عناصر']],
            ['key'=>'mechanism','label'=>'سازوکار عملکرد نظریه','group'=>'foundations','type'=>'text','required'=>true,
             'hint'=>'نظریه چگونه فرایند مورد نظر را تبیین می‌کند؛ چه اتفاقی می‌افتد و چه عواملی باعث آن می‌شوند.','source'=>['core_logic','detailed_analysis']],

            // ---- گروه ۳: تبیین، توسعه و ارزیابی ----
            ['key'=>'applications','label'=>'کاربردها و مصادیق','group'=>'evaluation','type'=>'text','required'=>true,
             'hint'=>'کاربرد نظریه در جهان واقعی، مطالعات رسانه‌ای، سیاست، فرهنگ و ارتباطات.','source'=>['example']],
            ['key'=>'studies','label'=>'مطالعات و پژوهش‌های شاخص','group'=>'evaluation','type'=>'array','required'=>false,
             'hint'=>'آرایه‌ی آبجکت: {citation: پژوهش/آزمون, findings: یافته‌ی کلیدی}.','source'=>['detailed_analysis','recommended_sources'],'subkeys'=>['citation'=>'پژوهش','findings'=>'یافته']],
            ['key'=>'evolution','label'=>'تحولات و توسعه نظریه','group'=>'evaluation','type'=>'text','required'=>false,
             'hint'=>'نسخه‌های جدید، بازنگری‌ها، شاخه‌های فرعی و نظریه‌های توسعه‌یافته از آن.','source'=>['related_approaches','detailed_analysis']],
            ['key'=>'critiques','label'=>'نقدها و محدودیت‌ها','group'=>'evaluation','type'=>'text','required'=>true,
             'hint'=>'نقدهای نظری، روش‌شناختی، تجربی، محدودیت‌های کاربردی و معرفی منتقدان.','source'=>['critique']],
            ['key'=>'related','label'=>'نظریه‌های مرتبط و رقیب','group'=>'evaluation','type'=>'array','required'=>true,
             'hint'=>'آرایه‌ی آبجکت: {name: نام نظریه, relation: هم‌خانواده/مکمل/متعارض/رقیب}.','source'=>['related_approaches'],'subkeys'=>['name'=>'نظریه','relation'=>'روابط (هم‌خانواده/مکمل/متعارض/رقیب)']],

            // ---- گروه ۴: منابع و اطلاعات پژوهشی ----
            ['key'=>'sources','label'=>'منابع و مآخذ','group'=>'sources','type'=>'array','required'=>true,
             'hint'=>'آرایه‌ی آبجکت: {citation: منبع, doi: شناسه DOI (در صورت وجود), url: لینک دسترسی (در صورت وجود), language: فارسی/انگلیسی}.','source'=>['recommended_sources'],'subkeys'=>['citation'=>'منبع','doi'=>'DOI','url'=>'لینک','language'=>'زبان']],
            ['key'=>'research_guide','label'=>'راهنمای استفاده پژوهشی','group'=>'sources','type'=>'text','required'=>false,
             'hint'=>'مناسب برای چه پدیده‌هایی، متغیرهای قابل استخراج، روش‌های پژوهشی مناسب و محدودیت‌های استفاده پژوهشی.','source'=>[]],
        ];
    }

    /** فهرست کلیدهای رسمی (۲۲ عدد) — ترتیب نمایش */
    public static function keys(): array {
        return array_map(fn($f)=>$f['key'], self::fields());
    }

    /** آبجکت با همه‌ی کلیدهای رسمی و مقدار null (پایه‌ی اعتبارسنجی و نرمال‌سازی) */
    public static function blank(): array {
        $o=[]; foreach (self::keys() as $k) $o[$k]=null; return $o;
    }

    /** فیلدِ مشخص بر اساس کلید */
    public static function byKey(string $key): ?array {
        foreach (self::fields() as $f) if ($f['key']===$key) return $f;
        return null;
    }

    /** برچسب فارسی یک کلید */
    public static function label(string $key): string {
        $f=self::byKey($key); return $f ? $f['label'] : $key;
    }

    /** آیا مقدار یک فیلد «خالی» است؟ */
    public static function isEmpty($value): bool {
        if ($value===null) return true;
        if (is_string($value)) return trim($value)==='';
        if (is_array($value)) {
            if ($value===[]) return true;
            foreach ($value as $v) if (!self::isEmpty($v)) return false;
            return true;
        }
        return false;
    }

    /** نرمال‌سازی: فقط کلیدهای رسمی، در ترتیب رسمی، مابقی حذف می‌شود */
    public static function normalize($raw): array {
        $base=self::blank();
        if (!is_array($raw)) return $base;
        foreach ($base as $k=>$_) if (array_key_exists($k,$raw)) $base[$k]=$raw[$k];
        return $base;
    }

    /** آیا کلیدهای raw دقیقاً همان ۲۲ کلید رسمی‌اند؟ (برای پذیرش پیش‌نویس AI) */
    public static function exactKeys($raw): bool {
        if (!is_array($raw)) return false;
        $a=array_keys(self::blank()); $b=array_keys($raw);
        sort($a); sort($b);
        return $a===$b;
    }

    /** فهرست فیلدهای خالیِ ضروری (برای گزارش در issues) */
    public static function emptyRequired(array $dossier): array {
        $out=[];
        foreach (self::fields() as $f) {
            if ($f['required'] && self::isEmpty($dossier[$f['key']] ?? null)) $out[]=$f;
        }
        return $out;
    }

    /** ساختار گروه‌بندی‌شده برای نمایش: [groupKey => ['label'=>..,'fields'=>[...]]] */
    public static function grouped(): array {
        $g=self::groups(); $out=[];
        foreach ($g as $k=>$meta) $out[$k]=['label'=>$meta['label'],'fields'=>[]];
        foreach (self::fields() as $f) $out[$f['group']]['fields'][]=$f;
        return $out;
    }

    /** متن مشخصه‌ی ساختار برای پرامپت مدل (فارسی، گروه‌بندی‌شده) */
    public static function promptSpec(): string {
        $lines=[]; $n=0;
        foreach (self::grouped() as $gk=>$g) {
            $lines[]='گروه — '.$g['label'].':';
            foreach ($g['fields'] as $f) {
                $n++;
                $req=$f['required'] ? 'ضروری' : 'اختیاری';
                $lines[]=sprintf(' %d. %s (%s) [%s، %s]: %s',$n,$f['key'],$f['label'],$f['type'],$req,$f['hint']);
            }
        }
        return implode("\n",$lines);
    }
}
