<?php
require __DIR__.'/vendor/autoload.php';
require __DIR__.'/app/Config.php';
require __DIR__.'/app/Db.php';
require __DIR__.'/app/Support.php';
require __DIR__.'/app/Auth.php';
require __DIR__.'/app/SyncService.php';
require __DIR__.'/app/Structure/Dossier.php';
require __DIR__.'/app/Structure/PersonDossier.php';
require __DIR__.'/app/AI/IsmcProvider.php';
\Resaname\Config::load(__DIR__.'/.env');
if (PHP_SAPI !== 'cli') {
    session_name(\Resaname\Config::get('SESSION_NAME','resaname_editorial'));
    session_set_cookie_params(['httponly'=>true,'secure'=>true,'samesite'=>'Strict','path'=>'/']);
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
}
