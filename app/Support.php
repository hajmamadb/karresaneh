<?php
namespace Resaname;
final class Support {
    public static function e(?string $s): string { return htmlspecialchars($s ?? '', ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }
    public static function log(string $message, array $context=[]): void {
        $line='['.date('c').'] '.$message.' '.json_encode($context,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES).PHP_EOL;
        $dir=dirname(__DIR__).'/storage/logs';
        if (!is_dir($dir)) mkdir($dir,0700,true);
        file_put_contents($dir.'/app.log',$line,FILE_APPEND|LOCK_EX);
    }
    /**
     * اطلاعات سال شمسی (الگوریتم رسمی EMP / jdf):
     * روز ماهِ مارس که ۱ فروردینِ سال $jy به آن می‌رسد.
     */
    private static function jalaliYearInfo(int $jy): array {
        $breaks=[-61,9,38,199,426,686,756,818,1111,1181,1210,1635,2060,2097,2192,2262,2324,2394,2456,3178];
        $gy=$jy+621;
        $leapJ=-14; $jp=$breaks[0]; $jump=0;
        for ($i=1,$bl=count($breaks); $i<$bl; $i++) {
            $jm2=$breaks[$i];
            $jump=$jm2-$jp;
            if ($jy<$jm2) break;
            $leapJ=$leapJ+intdiv($jump,33)*8+intdiv($jump%33,4);
            $jp=$jm2;
        }
        $n=$jy-$jp;
        $leapJ=$leapJ+intdiv($n,33)*8+intdiv(($n%33)+3,4);
        if (($jump%33)===4 && $jump-$n===4) $leapJ++;
        $leapG=intdiv($gy,4)-intdiv((intdiv($gy,100)+1)*3,4)-150;
        return ['march'=>20+$leapJ-$leapG];
    }

    /** تعداد روزهایی از ۱۹۷۰-۰۱-۰۱ تا Y-M-D میلادی (الگوریتم days_from_civil؛ بدون وابستگی به timezone و DST) */
    public static function daysFromCivil(int $y, int $m, int $d): int {
        $y -= $m <= 2 ? 1 : 0;
        $era = intdiv($y >= 0 ? $y : $y - 399, 400);
        $yoe = $y - $era * 400;                                            // [0, 399]
        $doy = intdiv(153 * ($m + ($m > 2 ? -3 : 9)) + 2, 5) + $d - 1;     // [0, 365]
        $doe = $yoe * 365 + intdiv($yoe, 4) - intdiv($yoe, 100) + $doy;    // [0, 146096]
        return $era * 146097 + $doe - 719468;
    }

    /** تبدیل روزِ مطلق (از ۱۹۷۰-۰۱-۰۱) به Y-M-D میلادی (algoritm civil_from_days) */
    public static function civilFromDays(int $z): array {
        $z += 719468;
        $era = intdiv($z >= 0 ? $z : $z - 146096, 146097);
        $doe = $z - $era * 146097;                                                  // [0, 146096]
        $yoe = intdiv($doe - intdiv($doe, 1460) + intdiv($doe, 36524) - intdiv($doe, 146096), 365); // [0, 399]
        $y = $yoe + $era * 400;
        $doy = $doe - (365 * $yoe + intdiv($yoe, 4) - intdiv($yoe, 100));
        $mp  = intdiv(5 * $doy + 2, 153);
        $d   = $doy - intdiv(153 * $mp + 2, 5) + 1;
        $m   = $mp + ($mp < 10 ? 3 : -9);
        return [$y + ($m <= 2 ? 1 : 0), $m, $d];
    }

    /** تبدیل تاریخ میلادی (Y-m-d H:i:s) به [سال،ماه،روز] شمسی */
    public static function gregorianToJalali(string $dt): array {
        $d=strtotime($dt);
        if ($d===false) return [0,0,0];
        $gy=(int)date('Y',$d); $gm=(int)date('n',$d); $gd=(int)date('j',$d);
        $jy=$gy-621;
        $fvDays=self::daysFromCivil($gy,3,self::jalaliYearInfo($jy)['march']);
        $days=self::daysFromCivil($gy,$gm,$gd);
        if ($days<$fvDays) {
            // تاریخ قبل از ۱ فروردینِ سال تخمینی است → سال شمسی قبل
            $jy--;
            $fvDays=self::daysFromCivil($gy-1,3,self::jalaliYearInfo($jy)['march']);
        }
        $k=$days-$fvDays; // روزِ سال (۰-based)
        if ($k<=185) {
            $jm=1+intdiv($k,31);
            $jd=($k%31)+1;
        } else {
            $k-=186;
            $jm=7+intdiv($k,30);
            $jd=($k%30)+1;
        }
        return [$jy,$jm,$jd];
    }

    /** ارقام لاتین → فارسی */
    public static function faDigits(string $s): string {
        return str_replace(['0','1','2','3','4','5','6','7','8','9'],['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'],$s);
    }

    /** نمایش تاریخ شمسی؛ مثال: «۱۸ آبان ۱۴۰۵، ۱۴:۲۳» */
    public static function faDate(?string $dt, bool $withTime=true): string {
        if ($dt===null || trim($dt)==='') return '';
        [$jy,$jm,$jd]=self::gregorianToJalali($dt);
        if (!$jy || !$jm || !$jd) return '';
        $months=['فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
        $out=self::faDigits((string)$jd).' '.$months[$jm-1].' '.self::faDigits((string)$jy);
        if ($withTime) $out.='، '.self::faDigits(date('G:i',strtotime($dt)));
        return $out;
    }

    public static function csrf(): string {
        if (empty($_SESSION['_csrf'])) $_SESSION['_csrf']=bin2hex(random_bytes(32));
        return $_SESSION['_csrf'];
    }
    public static function verifyCsrf(): void {
        $sent=$_POST['_csrf'] ?? '';
        if (!is_string($sent) || !hash_equals(self::csrf(),$sent)) {
            http_response_code(419); exit('درخواست نامعتبر است.');
        }
    }
    /** راه‌اندازی worker هوش مصنوعی (اگر قبلا در حال اجرا نباشد) */
    public static function startWorker(string $root): array {
        $lockFile=$root.'/storage/worker.lock';
        $lockData=json_decode((string)@file_get_contents($lockFile),true);
        $active=is_array($lockData)&&isset($lockData['started_at'])&&(time()-(int)$lockData['started_at'])<300;
        if ($active) return ['started'=>false,'message'=>'یک worker در حال اجراست و کارهای صف را خودکار برمی‌دارد.'];
        $logFile=$root.'/storage/logs/worker.out';
        if (!is_dir(dirname($logFile))) mkdir(dirname($logFile),0700,true);
        $descriptors=[0=>['file','NUL','r'],1=>['file',$logFile,'a'],2=>['file',$logFile,'a']];
        $proc=proc_open([PHP_BINARY,$root.'/bin/worker.php'],$descriptors,$pipes,$root);
        if (!is_resource($proc)) return ['started'=>false,'message'=>'فرآیند worker اجرا نشد.'];
        $st=proc_get_status($proc);
        self::log('worker_started_by_ui',['pid'=>$st['pid'] ?? null]);
        return ['started'=>true,'message'=>'worker هوش مصنوعی راه‌اندازی شد (PID '.($st['pid'] ?? '?') .')؛ پیشرفت را در وضعیت صف دنبال کنید.'];
    }
}
