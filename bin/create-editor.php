<?php
require dirname(__DIR__).'/bootstrap.php';
if (PHP_SAPI!=='cli') exit("CLI only\n");
$username=trim(readline('Username: '));
$name=trim(readline('Display name: '));
echo 'Password: '; system('stty -echo'); $password=trim(fgets(STDIN)); system('stty echo'); echo PHP_EOL;
if ($username==='' || strlen($password)<14) exit("Username required; password must be at least 14 characters.\n");
$db=\Resaname\Db::editorial();
$q=$db->prepare("INSERT INTO editorial_users(username,password_hash,display_name,role) VALUES(?,?,?,'admin')");
$q->execute([$username,password_hash($password,PASSWORD_DEFAULT),$name ?: $username]);
echo "Editor account created.\n";
