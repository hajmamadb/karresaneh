<?php
require dirname(__DIR__).'/bootstrap.php';
$sql=file_get_contents(dirname(__DIR__).'/database/schema.sql');
$db=\Resaname\Db::editorial();
foreach (array_filter(array_map('trim',preg_split('/;\s*(?:\r?\n|$)/',$sql))) as $statement) $db->exec($statement);

// ارتقای جداول موجود: افزودن ستون‌های جدید در صورت نبودن (idempotent)
$upgrades=[
    'editorial_documents'=>[
        'dossier_json'=>'LONGTEXT NULL',
        'dossier_hash'=>'CHAR(64) NULL',
        'structure_version'=>'TINYINT UNSIGNED NOT NULL DEFAULT 1',
        'content_category'=>'VARCHAR(40) NULL',
        'content_type'=>'VARCHAR(80) NULL',
    ],
    'editorial_jobs'=>[
        'feedback'=>'TEXT NULL',
        'auto_retry'=>'TINYINT UNSIGNED NOT NULL DEFAULT 0',
    ],
    'editorial_revisions'=>[
        'quality_score'=>'TINYINT UNSIGNED NULL',
    ],
];
$chk=$db->prepare("SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME=? AND COLUMN_NAME=?");
foreach ($upgrades as $table=>$cols) {
    foreach ($cols as $col=>$def) {
        $chk->execute([$table,$col]);
        if ((int)$chk->fetchColumn()===0) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$col` $def");
            echo "Added $table.$col\n";
        }
    }
}
echo "Editorial schema installed.\n";
