<?php
require dirname(__DIR__).'/bootstrap.php';
if (PHP_SAPI !== 'cli') exit("CLI only\n");
/**
 * تست سریعِ نامِ مدل‌ها در API: برای هر مدل، یک پرامپت تک‌خطی می‌فرستد.
 * usage: php bin/test-models.php "qwen38,qwen38-nothinking,qwen38-sglang"
 */
$models=isset($argv[1]) && $argv[1]!=='' ? $argv[1] : 'qwen38';
foreach (array_filter(array_map('trim', explode(',', $models))) as $m) {
    $t0=microtime(true);
    try {
        $p=new \Resaname\AI\IsmcProvider($m);
        $r=$p->complete('شما یک دستیار هستید. فقط و فقط واژه‌ی «OK» را پاسخ بدهید.','سلام');
        $txt='';
        if (isset($r['choices'][0]['message']['content'])) $txt=trim((string)$r['choices'][0]['message']['content']);
        echo $m.' => OK ('.round(microtime(true)-$t0,1).'s) | '.substr($txt,0,60).PHP_EOL;
    } catch (\Throwable $e) {
        echo $m.' => FAIL ('.round(microtime(true)-$t0,1).'s) | '.substr($e->getMessage(),0,200).PHP_EOL;
    }
}
