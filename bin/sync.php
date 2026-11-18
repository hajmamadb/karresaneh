<?php
require dirname(__DIR__).'/bootstrap.php';
$all=in_array('--all',$argv,true);
$table=null;
foreach ($argv as $arg) if (in_array($arg,['cards','theorists','library_resources'],true)) $table=$arg;
if (!$all && !$table) exit("Usage: php bin/sync.php --all | cards | theorists | library_resources\n");
$svc=new \Resaname\SyncService(\Resaname\Db::main(),\Resaname\Db::editorial());
$tables=$all?['cards','theorists','library_resources']:[$table];
foreach ($tables as $t) {
  $n=$svc->sync($t);
  echo "$t: $n rows imported/checked\n";
}
