<?php
namespace Resaname\Knowledge\Seed;

/**
 * بخش ۶: طرح‌ها و استراتژی‌های پژوهش (Research Designs & Strategies)
 *
 * این دسته از روش‌های گردآوری داده جداست: طرح، «نقشه‌ی اجرایی» پژوهش است
 * (چه کاری، در چه ترتیبی) و فن، «ابزار دریافت داده» است.
 */
return [
    // ---------------- آزمایشی ----------------
    ['design-experimental', 'پژوهش آزمایشی', 'Experimental Research', 'DESIGN', 'methodology-root',
        'طرحی که با کنترل متغیرهای مزاحم و مداخله‌ی فعال پژوهشگر، رابطه‌ی علّی را آزمودنی می‌کند.'],
    ['design-true-experiment', 'آزمایش واقعی', 'True Experiment', 'DESIGN', 'design-experimental',
        'آزمایش با تخصیص تصادفی، گروه کنترل و کنترل کامل متغیرها؛ قوی‌ترین طرح برای استنتاج علّی.'],
    ['design-quasi-experiment', 'نیمه‌آزمایشی', 'Quasi-Experiment', 'DESIGN', 'design-experimental',
        'آزمایش بدون تخصیص تصادفی (گروه‌های موجود)؛ رایج در پژوهش‌های رسانه‌ای و آموزشی.'],
    ['design-pre-experiment', 'پیش‌آزمایشی', 'Pre-Experiment', 'DESIGN', 'design-experimental',
        'طرح‌های ساده با یک گروه و بدون کنترل (پیش‌آزمون-پس‌آزمون بدون گروه شاهد).'],

    // ---------------- غیرآزمایشی ----------------
    ['design-nonexperimental', 'پژوهش غیرآزمایشی', 'Non-experimental Research', 'DESIGN', 'methodology-root',
        'طرح‌هایی که بدون مداخله‌ی فعال پژوهشگر، روابط بین متغیرها را در وضعیت طبیعی می‌سنجند.'],
    ['design-descriptive', 'توصیفی', 'Descriptive Design', 'DESIGN', 'design-nonexperimental',
        'تصویر وضعیت موجود یک پدیده (چند، کجا، چه هنگام) بدون آزمودن علّیت.'],
    ['design-correlational', 'طرح همبستگی', 'Correlational Design', 'DESIGN', 'design-nonexperimental',
        'سنجش اندازه و جهت رابطه‌ی دو یا چند متغیر بدون ادعای علّیت.'],
    ['design-causal-comparative', 'علّی-مقایسه‌ای', 'Causal-Comparative Design', 'DESIGN', 'design-nonexperimental',
        'مقایسه‌ی گروه‌هایی که پیش از پژوهش با یک عامل متفاوت‌اند (مطالعه‌ی علّی-مقایسه‌ای).'],

    // ---------------- پیمایشی ----------------
    ['design-survey', 'پژوهش پیمایشی', 'Survey Research', 'DESIGN', 'methodology-root',
        'دریافت داده از نمونه‌ای از جامعه با پرسشنامه یا مصاحبه برای توصیف و سنجش باورها، نگرش‌ها و رفتارها.'],
    ['design-cross-sectional', 'مقطعی', 'Cross-Sectional Design', 'DESIGN', 'design-survey',
        'اندازه‌گیری یک‌باره در یک نقطه‌ی زمانی.'],
    ['design-longitudinal', 'طولی', 'Longitudinal Design', 'DESIGN', 'design-survey',
        'اندازه‌گیری مکرر در طول زمان برای مشاهده‌ی تغییرات.'],
    ['design-panel', 'پانل', 'Panel Design', 'DESIGN', 'design-longitudinal',
        'پیگیری همان افراد در چند زمان.'],
    ['design-trend', 'روندی', 'Trend Design', 'DESIGN', 'design-longitudinal',
        'اندازه‌گیری مکرر یک شاخص در جمعیت‌های مشابه (نه لزوماً همان افراد).'],
    ['design-cohort', 'نسلی', 'Cohort Design', 'DESIGN', 'design-longitudinal',
        'پیگیری یک نسل یا گروه هم‌زمان در طول زمان.'],

    // ---------------- موردی ----------------
    ['design-case-study', 'پژوهش موردی', 'Case Study', 'DESIGN', 'methodology-root',
        'بررسی عمیق و چندمنبعه‌ی یک مورد (فرد، سازمان، رویداد) در بافت واقعی.'],
    ['design-single-case', 'مطالعه‌ی موردی منفرد', 'Single Case Study', 'DESIGN', 'design-case-study', 'بررسی عمیق و چندمنبعه‌ی یک مورد واحد؛ وقتی مورد منحصربه‌فرد، بحرانی یا آشکارساز باشد، این طرح گزینه‌ی مناسب است. در برابر مطالعه‌ی چندگانه، عمق و بافت را بر تعمیم ترجیح می‌دهد.'],
    ['design-multiple-case', 'مطالعه‌ی موردی چندگانه', 'Multiple Case Study', 'DESIGN', 'design-case-study', 'بررسی چند مورد برای سنجش الگوهای همسان یا متضاد میان آن‌ها. در برابر مطالعه‌ی منفرد، تکرار طرح در موارد تازه، تعمیم‌پذیری و روایی یافته را بیشتر می‌کند.'],
    ['design-intra-case', 'درون‌موردی', 'Intra-case Analysis', 'DESIGN', 'design-case-study',
        'تحلیل درون هر مورد مستقل از سایر موارد.'],
    ['design-cross-case', 'بین‌موردی', 'Cross-case Analysis', 'DESIGN', 'design-case-study',
        'مقایسه‌ی یافته‌ها میان موارد.'],

    // ---------------- تاریخی ----------------
    ['design-historical', 'طرح پژوهش تاریخی', 'Historical Research Design', 'DESIGN', 'methodology-root',
        'بازسازی و تفسیر نظام‌مند گذشته با مستندات.'],
    ['design-historiography', 'تاریخ‌نگاری', 'Historiography', 'DESIGN', 'design-historical', 'بررسی و نقد شیوه‌ی نگارش آثار تاریخی، از منابع تا چارچوب‌های تفسیری و گرایش‌های روایتگران. پیش‌نیاز تحلیل تاریخی است، زیرا اعتبار هر بازسازی گذشته به دقت در ارزیابی منابع بازمی‌گردد.'],
    ['design-historical-analysis', 'تحلیل تاریخی', 'Historical Analysis', 'DESIGN', 'design-historical', 'بازسازی نظام‌مند رویدادها و دوره‌های گذشته با اتکا بر منابع اصلی و ثانویه و نقد اعتبار آن‌ها. برخلاف تاریخ‌نگاری، خودِ فرایند نگارش تاریخ را هدف نمی‌گیرد؛ رویداد را هدف دارد.'],
    ['design-oral-history', 'تاریخ شفاهی', 'Oral History', 'DESIGN', 'design-historical',
        'گردآوری و تحلیل روایت‌های شفاهی شاهدان تاریخ.'],

    // ---------------- قوم‌نگارانه ----------------
    ['design-ethnography', 'پژوهش قوم‌نگارانه', 'Ethnography', 'DESIGN', 'methodology-root',
        'مطالعه‌ی غوطه‌ورانه‌ی فرهنگ و رفتار یک گروه در بافت طبیعی آن برای فهم الگوهای معنابخش.'],
    ['design-classic-ethnography', 'قوم‌نگاری کلاسیک', 'Classic Ethnography', 'DESIGN', 'design-ethnography', 'غوطه‌وری طولانی پژوهشگر در بافت طبیعی گروه هدف، با مشاهده‌ی مشارکتی و گفت‌وگو، برای فهم الگوهای معنابخش. در برابر قوم‌نگاری دیجیتال و سازمانی، حوزه‌ی مطالعه، زندگی روزمره‌ی خودِ گروه است.'],
    ['design-digital-ethnography', 'قوم‌نگاری دیجیتال', 'Digital Ethnography', 'DESIGN', 'design-ethnography',
        'قوم‌نگاری گروه‌ها در محیط‌های آنلاین و شبکه‌های اجتماعی.'],
    ['design-organizational-ethnography', 'قوم‌نگاری سازمانی', 'Organizational Ethnography', 'DESIGN', 'design-ethnography', 'کاربرد قوم‌نگاری درون سازمان برای فهم فرهنگ و الگوی معنابخشی کارکنان با مشاهده‌ی عملی، گفت‌وگو و مطالعه‌ی اسناد. در پژوهش ارتباطات برای کالبدشکافی رسانه‌ها و بنگاه‌های خبری رایج است.'],
    ['design-critical-ethnography', 'قوم‌نگاری انتقادی', 'Critical Ethnography', 'DESIGN', 'design-ethnography',
        'قوم‌نگاری با چارچوب انتقادی: هدف فهم روابط قدرت و امکان تغییر است.'],

    // ---------------- پدیدارشناختی ----------------
    ['design-phenomenology', 'پژوهش پدیدارشناختی', 'Phenomenological Research', 'DESIGN', 'methodology-root',
        'بررسی ساختار تجربه‌ی زیسته‌ی یک پدیده از منظر کسانی که آن را تجربه کرده‌اند.'],
    ['design-phenomenological-descriptive', 'پدیدارشناختی توصیفی', 'Descriptive Phenomenology', 'DESIGN', 'design-phenomenology', 'رویکردی که تجربه‌ی زیسته را بدون تفسیر پژوهشگر، با کنارگذاشتن پیش‌فرض‌ها، همان‌گونه که برای شرکت‌کننده محسوس است توصیف می‌کند. در برابر رویکرد تفسیری، پژوهشگر بازتابی خالص از تجربه است.'],
    ['design-phenomenological-interpretive', 'پدیدارشناختی تفسیری', 'Interpretive Phenomenology', 'DESIGN', 'design-phenomenology', 'رویکردی که معنای تجربه‌ی زیسته را در گفت‌وگوی مداوم میان پژوهشگر و شرکت‌کننده می‌جوید. در برابر رویکرد توصیفی، پژوهشگر تفسیرکننده‌ی تجربه است و نقش فعال تفسیر جایگزین توصیف خالص می‌شود.'],
    ['design-phenomenological-hermeneutic', 'پدیدارشناختی هرمنوتیکی', 'Hermeneutic Phenomenology', 'DESIGN', 'design-phenomenology', 'ترکیب پدیدارشناسی و هرمنوتیک؛ در این رویکرد فهم تجربه‌ی زیسته خود یک فرایند تفسیر است و پژوهشگر در چرخه‌ای میان جزئیات و کل حرکت می‌کند. در برابر دو رویکرد دیگر، تفسیر را ذات فهم می‌داند، نه مرحله‌ی پس از توصیف.'],

    // ---------------- داده‌بنیاد ----------------
    ['design-grounded', 'نظریه‌پردازی داده‌بنیاد', 'Grounded Theory', 'DESIGN', 'methodology-root',
        'طرحی برای تولید نظریه از داده‌ها با کدگذاری تدریجی و نظریه‌پردازی از دل مشاهدات.'],
    ['design-glaser-grounded', 'گلیزری', 'Glaserian Grounded Theory', 'DESIGN', 'design-grounded',
        'رویکرد گلیزر: کشف نظریه بدون پیش‌بازجویی مفهومی.'],
    ['design-strauss-grounded', 'اشتراوسی', 'Straussian Grounded Theory', 'DESIGN', 'design-grounded',
        'رویکرد اشتراوس و کوربین: کدگذاری باز، محوری و انتخابی با چارچوب‌های نظری.'],
    ['design-constructivist-grounded', 'سازه‌گرایانه', 'Constructivist Grounded Theory', 'DESIGN', 'design-grounded',
        'رویکرد چارمز: نظریه برساخته‌ی ذهنی پژوهشگر و شرکت‌کنندگان.'],

    // ---------------- روایی ----------------
    ['design-narrative', 'پژوهش روایی', 'Narrative Research', 'DESIGN', 'methodology-root',
        'تحلیل و تفسیر روایت‌ها و داستان‌هایی که افراد درباره‌ی تجربه‌های خود می‌سازند.'],
    ['design-biographical', 'زندگی‌نامه‌ای', 'Biographical Research', 'DESIGN', 'design-narrative', 'روایت زندگی یک فرد از زبان خودش؛ پژوهشگر معنایی را می‌جوید که خودِ فرد به رویدادهای زندگی‌اش می‌بخشد. در برابر تاریخ زندگی، محور اصلی روایت درون‌زبانی شخص است، نه بازسازی سندی از منابع متعدد.'],
    ['design-personal-narrative', 'روایت شخصی', 'Personal Narrative', 'DESIGN', 'design-narrative', 'تمرکز بر یک یا چند تجربه‌ی مشخص زندگی و ساختار روایی آن؛ شخصیت‌ها، بافت، رویدادها و نتیجه. واحد تحلیل خودِ داستان است. در برابر زندگینامه‌ای، کل زندگی هدف نیست، بلکه معنای یک تجربه‌ی خاص.'],
    ['design-life-history', 'تاریخ زندگی', 'Life History', 'DESIGN', 'design-narrative', 'بازسازی نظام‌مند و زمانی زندگی یک فرد از آغاز تا کنون با تکیه بر مصاحبه، اسناد و شواهد متعدد. در برابر زندگینامه‌ای، فقط روایت درون‌زبانی نیست؛ کل مسیر زندگی به‌صورت گسترده بازسازی می‌شود.'],

    // ---------------- اقدام‌پژوهی ----------------
    ['design-action-research', 'پژوهش اقدام‌پژوهانه', 'Action Research', 'DESIGN', 'methodology-root',
        'چرخه‌ی طرح، اجرا، مشاهده و بازاندیشی برای حل مسئله‌ی واقعی و بهبود عمل.'],
    ['design-classic-action', 'اقدام‌پژوهی', 'Action Research', 'DESIGN', 'design-action-research', 'شکل اصلی طرح؛ پژوهشگر هم‌زمان عملگر است و در چرخه‌ی طرح، اجرا، مشاهده و بازاندیشی، عمل خود را بهبود می‌بخشد و دانش می‌سازد. در برابر شکل مشارکتی، جوامع هدف در طرح‌ریزی مشارکت ندارند.'],
    ['design-participatory-action', 'اقدام‌پژوهی مشارکتی', 'Participatory Action Research', 'DESIGN', 'design-action-research', 'شکلی از طرح که در آن اعضای جامعه‌ی هدف در همه‌ی مراحل، از تعریف مسئله تا ارزیابی، مشارکت فعال دارند. هدف نهایی، آگاهی‌بخشی و تغییر اجتماعی در کنار حل مسئله است.'],

    // ---------------- تطبیقی ----------------
    ['design-comparative', 'طرح پژوهش تطبیقی', 'Comparative Design', 'DESIGN', 'methodology-root', 'طرحی که دو یا چند واحد — کشور، سیستم، دوره یا فرهنگ — را با هم می‌سنجد تا شباهت‌ها و تفاوت‌ها را توضیح دهد. در پژوهش ارتباطات برای مقایسه‌ی رسانه‌ها و نظام‌های رسانه‌ای رایج است.'],
    ['design-historical-comparative', 'تطبیقی تاریخی', 'Historical Comparative', 'DESIGN', 'design-comparative', 'مقایسه‌ی یک پدیده در دوره‌های زمانی مختلف برای سنجش تغییر و تحول. واحد تحلیل، دوره‌ی زمانی است. در پژوهش رسانه‌ها برای مقایسه‌ی دوره‌های گوناگون یک رسانه یا نظام رسانه‌ای به‌کار می‌رود.'],
    ['design-cross-cultural', 'تطبیقی میان‌فرهنگی', 'Cross-Cultural Comparative', 'DESIGN', 'design-comparative', 'مقایسه‌ی یک پدیده یا متغیر در دو یا چند بافت فرهنگی متفاوت برای کشف الگوهای همسان و متفاوت. در پژوهش رسانه‌ها برای سنجش اثر یک پیام یا رسانه در فرهنگ‌های گوناگون کاربرد دارد.'],
    ['design-institutional-comparative', 'تطبیقی نهادی', 'Institutional Comparative', 'DESIGN', 'design-comparative', 'مقایسه‌ی نهادهای رسانه‌ای از حیث ساختار، مالکیت، قانون‌گذاری و عملکرد. تفاوتش با تطبیق میان‌فرهنگی در مقیاس کوچک‌تر و سازمانی بودن آن است. برای فهم چگونگی شکل‌گیری عمل رسانه‌ای در بافت نهادی به‌کار می‌رود.'],

    // ---------------- ارزیابانه ----------------
    ['design-evaluative', 'پژوهش ارزیابانه', 'Evaluative Research', 'DESIGN', 'methodology-root',
        'سنجش نظام‌مند ارزش، اثر و کارایی مداخله‌ها، برنامه‌ها و رسانه‌ها.'],
    ['design-process-evaluation', 'ارزیابی فرایند', 'Process Evaluation', 'DESIGN', 'design-evaluative', 'پایش چگونگی اجرای برنامه؛ میزان پایبندی به طرح، پوشش و نحوه‌ی خدمت‌رسانی. برخلاف ارزیابی پیامد، به نتیجه نمی‌پردازد و نشان می‌دهد برنامه به چه نحوی اجرا شده تا تفسیر یافته‌ها ممکن شود.'],
    ['design-outcome-evaluation', 'ارزیابی پیامد', 'Outcome Evaluation', 'DESIGN', 'design-evaluative', 'سنجش نتایج مستقیم و میان‌مدت برنامه بر مخاطبان، مانند آگاهی، نگرش و رفتار. در برابر ارزیابی فرایند، به کیفیت اجرا نمی‌پردازد و در برابر ارزیابی اثر، به تغییرات بلندمدت و کلان نه.'],
    ['design-impact-evaluation', 'ارزیابی اثر و کارایی', 'Impact and Efficiency Evaluation', 'DESIGN', 'design-evaluative', 'سنجش پیامدهای بلندمدت و کلان مداخله بر افراد و جامعه، به‌همراه کارایی اقتصادی آن. در مقابل دو قلم دیگر، به تغییر پایدار و نسبت فایده به هزینه می‌نگرد.'],

    // ---------------- طراحی‌محور ----------------
    ['design-design-based', 'پژوهش طراحی‌محور', 'Design-Based Research', 'DESIGN', 'methodology-root',
        'تولید دانش از طریق طراحی و آزمایش مداخله در زمینه‌ی واقعی.'],
    ['design-design-science', 'پژوهش علم طراحی', 'Design Science Research', 'DESIGN', 'design-design-based', 'طرحی که هدفش ساختن و ارزیابی یک شیء نوآورانه، همچون ابزار، چارچوب یا نمونه‌ی اولیه، برای حل مسئله‌ی واقعی است. در برابر پژوهش طراحی‌محور، محور دانش‌سازی خودِ محصول طراحی‌شده است.'],

    // ---------------- محاسباتی ----------------
    ['design-computational', 'طرح پژوهش محاسباتی', 'Computational Research Design', 'DESIGN', 'methodology-root',
        'طرح‌های مبتنی بر داده‌های بزرگ، الگوریتم و شبیه‌سازی.'],
    ['design-big-data-analysis', 'تحلیل داده‌های بزرگ', 'Big Data Analysis', 'DESIGN', 'design-computational', 'تحلیل مجموعه‌های داده‌ای به‌اندازه‌ای بزرگ، سریع و گوناگون که روش‌های سنتی از عهده‌ی پردازششان برنمی‌آیند. در پژوهش رسانه‌ها برای داده‌های شبکه‌های اجتماعی و ردیابی رفتار کاربر رایج است.'],
    ['design-simulation', 'شبیه‌سازی', 'Simulation', 'DESIGN', 'design-computational', 'ساخت مدل عددی از یک فرایند واقعی و اجرای آن در شرایط کنترل‌شده برای آزمودن مکانیسم‌ها. وقتی آزمایش مستقیم دشوار است، مانند پخش یک شایعه در شبکه، این روش جایگزین آن می‌شود.'],
    ['design-computational-modeling', 'مدل‌سازی محاسباتی', 'Computational Modeling', 'DESIGN', 'design-computational', 'ترجمه‌ی یک نظریه به الگوریتم یا معادله و اجرای عددی آن برای آزمودن منطقی فرض‌ها و تولید پیش‌بینی. در برابر شبیه‌سازی که فرایند را تقلید می‌کند، این طرح از دل خودِ نظریه مدل می‌سازد.'],
];
