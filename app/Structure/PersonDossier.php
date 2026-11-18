<?php
namespace Resaname\Structure;

/**
 * داسییر ساختاریافته شخص — «پروفایل نظریه‌پرداز / دانشمند / مؤثر»
 *
 * ساختار رسمی و پایدار محتوای هر صفحه‌ی نظریه‌پرداز، دانشمند و مؤثر:
 * ۲۱ فیلد مستقل در ۵ گروه. این کلاس «تک منبع حقیقت» (single source of truth)
 * برای worker (پرامپت و اعتبارسنجی)، صفحه‌ی بررسی (نمایش) و هر مصرف‌کننده‌ی
 * بعدی (مثل موتور AI/RAG) است.
 *
 * سیاست پرکردن (نسخه ۱):
 * - ساختار ۲۱ فیلدی ملزم و ثابت است: در خروجی AI هر ۲۱ کلید باید حضور داشته
 *   باشد؛ حذف، اضافه یا تغییر نام هیچ کلیدی مجاز نیست.
 * - هیچ فکتی اختراع نمی‌شود: فیلدی که داده‌ی معتبر و مطمئن ندارد، خالی
 *   می‌ماند و در issues به‌عنوان «نیازمند پژوهش عمیق» گزارش می‌شود.
 * - لینک‌های درون‌سایتی فقط با قالب URL رسمی و فقط از slugهای فهرست
 *   «محتوای سایت» که در پرامپت داده می‌شود ساخته می‌شوند.
 */
final class PersonDossier {

    public const VERSION = 1;

    /** انواع شخص (برای آینده: دانشمندان و مؤثرین در کنار نظریه‌پردازان) */
    public const TYPES = ['theorist' => 'نظریه‌پرداز', 'scientist' => 'دانشمند', 'influencer' => 'مؤثر'];

    /** گروه‌های پنج‌گانه (به‌ترتیب نمایش) */
    public static function groups(): array {
        return [
            'identity'     => ['label' => 'شناسنامه و اطلاعات هویتی', 'order' => 1],
            'biography'    => ['label' => 'زندگی‌نامه و مسیر علمی', 'order' => 2],
            'achievements' => ['label' => 'اندیشه‌ها و دستاوردهای علمی (هسته اصلی صفحه)', 'order' => 3],
            'evaluation'   => ['label' => 'نقد و ارزیابی علمی', 'order' => 4],
            'network'      => ['label' => 'ارتباطات و منابع علمی', 'order' => 5],
        ];
    }

    /**
     * ۲۱ فیلد رسمی. هر فیلد:
     *  key      کلید انگلیسی (نام کلید در JSON)
     *  label    عنوان فارسی برای نمایش
     *  group    گروه متعلق (کلید groups)
     *  type     text | array | json | number
     *  required آیا برای «محتوای کامل» ضروری است (خالی ماندن آن گزارش می‌شود)
     *  links    آیا این فیلد باید لینک محتوای مرتبطِ درون‌سایتی داشته باشد
     *  hint     راهنمای دقیق پرکردن (به مدل و ویراستار)
     *  source   فیلدهای رکورد خامی که می‌توانند راهنمای نگاشت باشند
     *  subkeys  برچسب فارسی زیرفیلدها (برای نمایش در میز سردبیری)
     */
    public static function fields(): array {
        return [
            // ---- گروه ۱: شناسنامه و اطلاعات هویتی ----
            ['key'=>'name','label'=>'نام و نام خانوادگی','group'=>'identity','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {name_fa: نام کامل فارسی, name_native: نام کامل اصلی به زبان مبدأ, aliases: آرایه‌ی نام‌های جایگزین, spellings: آرایه‌ی شیوه‌های مختلف نگارش نام}. نام اصلی دقیقااً از داده‌ی خام شخص گرفته شود.','source'=>['name','name_en'],
             'subkeys'=>['name_fa'=>'نام کامل فارسی','name_native'=>'نام اصلی (زبان مبدأ)','aliases'=>'نام‌های جایگزین','spellings'=>'شیوه‌های نگارش']],
            ['key'=>'basic_info','label'=>'تصویر و اطلاعات پایه','group'=>'identity','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {image_url: تصویر معتبر شخص (از داده‌ی خام)، birth_date: تاریخ تولد, death_date: تاریخ وفات (اگر زنده است null), nationality: ملیت, country_of_activity: کشور محل فعالیت}.','source'=>['avatar_url','year','nationality'],
             'subkeys'=>['image_url'=>'تصویر','birth_date'=>'تاریخ تولد','death_date'=>'تاریخ وفات','nationality'=>'ملیت','country_of_activity'=>'کشور محل فعالیت']],
            ['key'=>'introduction','label'=>'معرفی کوتاه','group'=>'identity','type'=>'text','required'=>true,'links'=>false,
             'hint'=>'معرفی علمی در حدود ۲۰۰ تا ۲۵۰ کلمه: شخص کیست، چه جایگاهی در رشته دارد و مهم‌ترین دستاوردش چیست. علمی و بدون لحن تبلیغاتی.','source'=>['bio','main_theory','field']],
            ['key'=>'specializations','label'=>'حوزه‌های تخصصی','group'=>'identity','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {main_field: رشته اصلی, secondary_fields: آرایه‌ی رشته‌های فرعی, research_areas: آرایه‌ی زمینه‌های پژوهشی, expertise: آرایه‌ی تخصص‌های علمی}.','source'=>['field','main_theory'],
             'subkeys'=>['main_field'=>'رشته اصلی','secondary_fields'=>'رشته‌های فرعی','research_areas'=>'زمینه‌های پژوهشی','expertise'=>'تخصص‌های علمی']],
            ['key'=>'affiliations','label'=>'وابستگی‌های علمی و سازمانی','group'=>'identity','type'=>'array','required'=>true,'links'=>false,
             'hint'=>'آرایه‌ی آبجکت: {name: نام دانشگاه/مرکز/مؤسسه/انجمن, kind: دانشگاه|مرکز پژوهشی|مؤسسه|انجمن علمی, role: نقش شخص در آن نهاد, location: شهر/کشور (در صورت دانستن)}.','source'=>['academic_career','bio'],
             'subkeys'=>['name'=>'نام نهاد','kind'=>'نوع (دانشگاه/مرکز/مؤسسه/انجمن)','role'=>'نقش','location'=>'مکان']],

            // ---- گروه ۲: زندگی‌نامه و مسیر علمی ----
            ['key'=>'education','label'=>'زندگی و تحصیلات','group'=>'biography','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {education: آرایه‌ی تحصیلات دانشگاهی, degrees: آرایه‌ی مدارک علمی (کارشناسی/ارشد/دکتری با رشته), mentors: آرایه‌ی استادان و افراد تأثیرگذار, family_social_background: زمینه خانوادگی و اجتماعی فقط در صورت ارتباط علمی، وگرنه null}.','source'=>['bio','academic_career'],
             'subkeys'=>['education'=>'تحصیلات دانشگاهی','degrees'=>'مدارک علمی','mentors'=>'استادان و افراد تأثیرگذار','family_social_background'=>'زمینه خانوادگی و اجتماعی']],
            ['key'=>'career','label'=>'مسیر حرفه‌ای و دانشگاهی','group'=>'biography','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {teaching: آرایه‌ی سوابق تدریس, positions: آرایه‌ی موقعیت‌های دانشگاهی (با سال در صورت دانستن), responsibilities: آرایه‌ی مسئولیت‌های علمی, research_activities: آرایه‌ی فعالیت‌های پژوهشی, institutions: آرایه‌ی مؤسسات و مراکز علمی حاضر در آن‌ها}.','source'=>['academic_career','bio'],
             'subkeys'=>['teaching'=>'سوابق تدریس','positions'=>'موقعیت‌های دانشگاهی','responsibilities'=>'مسئولیت‌های علمی','research_activities'=>'فعالیت‌های پژوهشی','institutions'=>'مراکز علمی']],
            ['key'=>'historical_context','label'=>'زمینه تاریخی و فکری','group'=>'biography','type'=>'text','required'=>true,'links'=>false,
             'hint'=>'شرایط اجتماعی، سیاسی، فرهنگی و علمی دوران فعالیت شخص و تأثیر آن بر اندیشه‌های او.','source'=>['bio','field']],
            ['key'=>'intellectual_evolution','label'=>'سیر تحول فکری','group'=>'biography','type'=>'text','required'=>true,'links'=>false,
             'hint'=>'تغییرات مهم در دیدگاه‌های شخص، مراحل مختلف فعالیت علمی و تحول مواضع نظری او در طول زمان.','source'=>['bio','main_theory']],
            ['key'=>'students_and_influences','label'=>'شاگردان و افراد تأثیرگذار','group'=>'biography','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {mentors: آرایه‌ی {name, relation} استادان, colleagues: آرایه‌ی همکاران, students: آرایه‌ی {name, notable_work} شاگردان, others: آرایه‌ی دیگر کسانی که در شکل‌گیری یا گسترش اندیشه‌های او نقش داشته‌اند}.','source'=>['bio','academic_career'],
             'subkeys'=>['mentors'=>'استادان','colleagues'=>'همکاران','students'=>'شاگردان','others'=>'افراد دیگر']],

            // ---- گروه ۳: اندیشه‌ها و دستاوردهای علمی (هسته اصلی صفحه) ----
            ['key'=>'thought_system','label'=>'منظومه فکری و اندیشه‌های اصلی','group'=>'achievements','type'=>'text','required'=>true,'links'=>false,
             'hint'=>'تبیین جهان‌بینی علمی، رویکرد نظری و محورهای اصلی تفکر شخص.','source'=>['main_theory','bio']],
            ['key'=>'theories','label'=>'نظریه‌های ارائه‌شده','group'=>'achievements','type'=>'array','required'=>true,'links'=>true,
             'hint'=>'آرایه‌ی آبجکت: {name_fa, name_en, role: بنیان‌گذار|توسعه‌دهنده|چهره اصلی, year: سال شکل‌گیری (در صورت دانستن), url: لینک نظریه در همین سایت (فقط اگر در فهرست محتوای سایت موجود باشد، وگرنه null)}.','source'=>['main_theory'],
             'subkeys'=>['name_fa'=>'نام فارسی','name_en'=>'نام انگلیسی','role'=>'نقش (بنیان‌گذار/توسعه‌دهنده/چهره اصلی)','year'=>'سال','url'=>'لینک درون‌سایتی']],
            ['key'=>'key_concepts','label'=>'مفاهیم و اصطلاحات کلیدی','group'=>'achievements','type'=>'array','required'=>true,'links'=>false,
             'hint'=>'آرایه‌ی آبجکت: {term_fa: اصطلاح فارسی, term_en: معادل انگلیسی, definition: تعریف دقیق, coined: bool — آیا این مفهوم توسط او ابداع، صورت‌بندی یا توسعه داده شده است؟}.','source'=>['main_theory','bio'],
             'subkeys'=>['term_fa'=>'اصطلاح','term_en'=>'معادل انگلیسی','definition'=>'تعریف','coined'=>'ابداع/صورت‌بندی توسط شخص']],
            ['key'=>'works','label'=>'آثار و تألیفات','group'=>'achievements','type'=>'json','required'=>true,'links'=>false,
             'hint'=>'آبجکت: {books: آرایه‌ی {title, year, publisher} کتاب‌ها, articles: آرایه‌ی مقالات علمی شاخص, notable_works: آرایه‌ی {title, note} آثار شاخص با یک جمله توضیح, talks_interviews: آرایه‌ی سخنرانی‌ها و مصاحبه‌های علمی مهم, translated_works: آرایه‌ی آثار ترجمه‌شده به فارسی/انگلیسی}.','source'=>['bio','academic_career'],
             'subkeys'=>['books'=>'کتاب‌ها','articles'=>'مقالات علمی','notable_works'=>'آثار شاخص','talks_interviews'=>'سخنرانی‌ها و مصاحبه‌ها','translated_works'=>'آثار ترجمه‌شده']],
            ['key'=>'method','label'=>'روش و رویکرد علمی','group'=>'achievements','type'=>'json','required'=>true,'links'=>true,
             'hint'=>'آبجکت: {text: روش‌های پژوهش، رویکرد معرفت‌شناختی، روش تحلیل و شیوه استدلال علمی, links: آرایه‌ی {label, url} لینک محتوای مورد اشاره‌ی مرتبط در همین سایت}.','source'=>['main_theory','bio'],
             'subkeys'=>['text'=>'روش و رویکرد','links'=>'لینک‌های مرتبط']],
            ['key'=>'legacy','label'=>'تأثیرگذاری و میراث علمی','group'=>'achievements','type'=>'json','required'=>true,'links'=>true,
             'hint'=>'آبجکت: {text: تأثیر بر رشته‌های علمی، مکاتب فکری، نظریه‌پردازان بعدی و تحولات دانش, links: آرایه‌ی {label, url} لینک محتوای مورد اشاره‌ی مرتبط در همین سایت}.','source'=>['main_theory','bio'],
             'subkeys'=>['text'=>'تأثیرگذاری','links'=>'لینک‌های مرتبط']],

            // ---- گروه ۴: نقد و ارزیابی علمی ----
            ['key'=>'critiques','label'=>'نقدهای واردشده بر اندیشه‌ها','group'=>'evaluation','type'=>'json','required'=>true,'links'=>true,
             'hint'=>'آبجکت: {text: دیدگاه‌های انتقادی موافقان و مخالفان، نقدهای نظری و روش‌شناختی, links: آرایه‌ی {label, url} لینک محتوای مورد اشاره‌ی مرتبط در همین سایت (مثلااً صفحه‌ی شخصیت‌های موافق، مخالف و منتقدان)}.','source'=>['main_theory'],
             'subkeys'=>['text'=>'نقدها','links'=>'لینک‌های مرتبط (موافقان/مخالفان/منتقدان)']],
            ['key'=>'responses','label'=>'پاسخ‌ها و دفاعیات','group'=>'evaluation','type'=>'text','required'=>true,'links'=>false,
             'hint'=>'پاسخ‌های خود نظریه‌پرداز به نقدها، دفاعیات مدافعان او و بازنگری‌های صورت‌گرفته در اندیشه‌هایش.','source'=>['bio']],
            ['key'=>'standing','label'=>'جایگاه علمی و ارزیابی معاصر','group'=>'evaluation','type'=>'text','required'=>true,'links'=>false,
             'hint'=>'جایگاه او در ادبیات علمی امروز، میزان تأثیرگذاری، استمرار کاربرد اندیشه‌ها و مناقشات علمی پیرامون آثارش.','source'=>['bio','main_theory']],

            // ---- گروه ۵: ارتباطات و منابع علمی ----
            ['key'=>'network','label'=>'شبکه ارتباطات علمی','group'=>'network','type'=>'json','required'=>true,'links'=>true,
             'hint'=>'آبجکت: {peers: آرایه‌ی {name, url?} نظریه‌پردازان هم‌عصر, teachers_students: آرایه‌ی {name, role: استاد|شاگرد, url?}, allies_critics: آرایه‌ی {name, stance: هم‌فکر|مخالف|منتقد, url?}, schools: آرایه‌ی {name, url?} مکاتب مرتبط, related_theories: آرایه‌ی {name, url?} نظریه‌های مرتبط, centers: آرایه‌ی {name, url?} مراکز علمی و دانشگاهی مرتبط}. هر url فقط لینک درون‌سایتی معتبر از فهرست محتوای سایت.','source'=>['main_theory','bio'],
             'subkeys'=>['peers'=>'نظریه‌پردازان هم‌عصر','teachers_students'=>'استادان و شاگردان','allies_critics'=>'هم‌فکران و منتقدان','schools'=>'مکاتب مرتبط','related_theories'=>'نظریه‌های مرتبط','centers'=>'مراکز علمی و دانشگاهی']],
            ['key'=>'references','label'=>'منابع و مآخذ','group'=>'network','type'=>'array','required'=>true,'links'=>false,
             'hint'=>'آرایه‌ی آبجکت: {type: اثر اصلی|زندگی‌نامه|مقاله علمی|مصاحبه یا سند تاریخی, citation: اطلاعات کامل ارجاع, language: fa|en, doi: شناسه DOI (فقط اگر با قطعیت می‌دانید), url: لینک ناشر (در صورت وجود), publisher: ناشر}. حداقل ۵ منبع معتبر شامل آثار اصلی، زندگی‌نامه‌های معتبر، مقالات علمی درباره‌ی شخص، مصاحبه‌ها و اسناد تاریخی، به فارسی و انگلیسی.','source'=>['recommended_sources'],
             'subkeys'=>['type'=>'نوع منبع','citation'=>'ارجاع','language'=>'زبان','doi'=>'DOI','url'=>'لینک ناشر','publisher'=>'ناشر']],
        ];
    }

    /** فهرست کلیدهای رسمی (۲۱ عدد) — ترتیب نمایش */
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

    /** آیا کلیدهای raw دقیقااً همان ۲۱ کلید رسمی‌اند؟ (برای پذیرش پیش‌نویس AI) */
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

    /** درصد تکمیل داسییر: تعداد فیلدهای غیرخالی بر ۲۱ */
    public static function completeness(array $dossier): int {
        $all=self::fields(); $filled=0;
        foreach ($all as $f) if (!self::isEmpty($dossier[$f['key']] ?? null)) $filled++;
        return (int)round($filled / count($all) * 100);
    }

    /** ساختار گروه‌بندی‌شده برای نمایش: [groupKey => ['label'=>..,'fields'=>[...]]] */
    public static function grouped(): array {
        $g=self::groups(); $out=[];
        foreach ($g as $k=>$meta) $out[$k]=['label'=>$meta['label'],'fields'=>[]];
        foreach (self::fields() as $f) $out[$f['group']]['fields'][]=$f;
        return $out;
    }

    /** متن مشخصه‌ی ساختار برای پرامپت مدل (فارسی، گروه‌بندی‌شده، با نشان لینک‌دار) */
    public static function promptSpec(): string {
        $lines=[]; $n=0;
        foreach (self::grouped() as $gk=>$g) {
            $lines[]='گروه — '.$g['label'].':';
            foreach ($g['fields'] as $f) {
                $n++;
                $req=$f['required'] ? 'ضروری' : 'اختیاری';
                $link=(!empty($f['links'])) ? '، لینک‌دار (باید لینک درون‌سایتی مرتبط داشته باشد)' : '';
                $lines[]=sprintf(' %d. %s (%s) [%s، %s%s]: %s',$n,$f['key'],$f['label'],$f['type'],$req,$link,$f['hint']);
            }
        }
        return implode("\n",$lines);
    }
}
