<?php
/**
 * به‌روزرسانی description و properties دو موجودیت DISCIPLINE:
 * - discipline-communication (علوم ارتباطات)
 * - discipline-media-studies (مطالعات رسانه)
 * اجرا: php bin/kb_update_disciplines_desc.php  (idempotent)
 */

$env = parse_ini_file(__DIR__ . '/../.env');
$host = $env['EDITORIAL_DB_HOST'] ?? '127.0.0.1';
$port = $env['EDITORIAL_DB_PORT'] ?? 3306;
$name = $env['EDITORIAL_DB_NAME'] ?? 'resaname_editorial';
$user = $env['EDITORIAL_DB_USER'] ?? 'root';
$pass = $env['EDITORIAL_DB_PASS'] ?? '';

$db = new PDO(
    "mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4",
    $user, $pass,
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

$desc1 = 'علوم ارتباطات رشته‌ی بین‌رشته‌ای‌ای است که فرآیندهای تولید، انتقال، دریافت و اثر پیام را در سطوح بین‌فردی، گروهی، سازمانی، جمعی و بین‌المللی مطالعه می‌کند. این رشته ریشه در جامعه‌شناسی، روان‌شناسی، علوم سیاسی و ادبیات دارد و از مدل‌های خطی کلاسیک (شِن‌ون و وِی‌ور) تا نظریه‌های انتقادی و پساساختارگرا را در بر می‌گیرد. حوزه‌های پژوهشی اصلی شامل نظریه‌های اثرات رسانه، ارتباطات سیاسی، ارتباطات سازمانی، ارتباطات بین‌فردی و بازنمایی‌های فرهنگی و رسانه‌ای است. از پژوهشگران شاخص، ولتر لیپمن، ویلبر شرام، مارشال مک‌لوهان، نیل پوسمن و ژورگ هابرماس شمرده می‌شوند؛ از نشریات مرجع، Human Communication Research و Journal of Communication نام برده می‌شود.';

$props1 = [
    'scope' => 'مطالعه‌ی فرآیندهای ارتباطی انسانی در سطوح بین‌فردی، گروهی، سازمانی، جمعی و بین‌المللی',
    'key_research_areas' => [
        'نظریه‌های اثرات رسانه',
        'ارتباطات سیاسی',
        'ارتباطات سازمانی',
        'ارتباطات بین‌فردی',
        'بازنمایی و معنا',
        'اقتصاد و مالکیت رسانه'
    ],
    'main_theories' => [
        'مدل ارتباطی شِن‌ون و وِی‌ور',
        'نظریه‌ی تعیین آجندا (Agenda-Setting)',
        'نظریه‌ی کاربرد و رضایت (Uses and Gratifications)',
        'نظریه‌ی رسانه‌های همگرا (Convergence)',
        'مکتب انتقادی فرانکفورت'
    ],
    'notable_researchers' => [
        'ولتر لیپمن',
        'ویلبر شرام',
        'مارشال مک‌لوهان',
        'نیل پوسمن',
        'ژورگ هابرماس'
    ],
    'key_publications' => [
        'Human Communication Research',
        'Journal of Communication',
        'Communication Studies'
    ],
    'related_disciplines' => [
        'discipline-sociology',
        'discipline-political-science',
        'discipline-psychology',
        'discipline-culture-studies'
    ]
];

$desc2 = 'مطالعات رسانه شاخه‌ی تخصصی علوم ارتباطات است که رسانه‌های جمعی (رادیو، تلویزیون، سینما، روزنامه، نشریات دیجیتال و پلتفرم‌های آنلاین) را از منظر ساختار، محتوا، اقتصاد، بازنمایی و سیاست‌گذاری بررسی می‌کند. این رشته بر تعامل رسانه با فرهنگ، ایدئولوژی و قدرت تمرکز دارد و از رویکردهای عمل‌گرایانه (مثل مطالعه‌ی مخاطب و اثرات رسانه) تا نظریه‌های انتقادی (مثل مکتب فرانکفورت و تحلیل فرهنگی) را در بر می‌گیرد. حوزه‌های کلیدی پژوهش شامل صنعت رسانه، مالکیت و تمرکز رسانه‌ای، اقتصاد دیجیتال، بازنمایی و هویت، و حکمرانی رسانه در فضای سایبری است. از پژوهشگران شاخص، استیون نوسنت، دونا هربلر و دانا هارپر شمرده می‌شوند؛ از نشریات مرجع، Media, Culture & Society و New Media & Society نام برده می‌شود.';

$props2 = [
    'scope' => 'مطالعه‌ی رسانه‌های جمعی و دیجیتال از منظر ساختار، محتوا، اقتصاد، بازنمایی و سیاست‌گذاری',
    'key_research_areas' => [
        'صنعت و اقتصاد رسانه',
        'مالکیت و تمرکز رسانه‌ای',
        'بازنمایی و ایدئولوژی',
        'مخاطب و مصرف رسانه',
        'حکمرانی رسانه و فضای سایبری'
    ],
    'main_theories' => [
        'نظریه‌ی رسانه‌های همگرا (Henry Jenkins)',
        'نظریه‌های بازنمایی انتقادی (Critical Representation)',
        'نظریه‌ی فرهنگ‌های پوپ (Popular Culture)',
        'مدل‌های اقتصاد رسانه دیجیتال'
    ],
    'notable_researchers' => [
        'استیون نوسنت (Steven Neustadt)',
        'دونا هربلر (Dona Harber)',
        'دانا هارپر (Dana Harper)',
        'هنری جنکینز (Henry Jenkins)'
    ],
    'key_publications' => [
        'Media, Culture & Society',
        'New Media & Society',
        'Journal of Communication'
    ],
    'related_disciplines' => [
        'discipline-communication',
        'discipline-sociology',
        'discipline-culture-studies',
        'discipline-political-science'
    ]
];

$upd = $db->prepare("UPDATE kb_entities SET description = ?, properties = ?, updated_at = NOW() WHERE code = ?");
$upd->execute([$desc1, json_encode($props1, JSON_UNESCAPED_UNICODE), 'discipline-communication']);
echo "updated: discipline-communication (desc=" . mb_strlen($desc1) . " chars, props=" . strlen(json_encode($props1)) . " bytes)\n";

$upd->execute([$desc2, json_encode($props2, JSON_UNESCAPED_UNICODE), 'discipline-media-studies']);
echo "updated: discipline-media-studies (desc=" . mb_strlen($desc2) . " chars, props=" . strlen(json_encode($props2)) . " bytes)\n";

echo "done\n";
