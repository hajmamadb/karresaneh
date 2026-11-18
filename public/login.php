<?php
require dirname(__DIR__).'/bootstrap.php';
if (\Resaname\Auth::user()) { header('Location: /'); exit; }
$error='';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  \Resaname\Support::verifyCsrf();
  if (\Resaname\Auth::login(\Resaname\Db::editorial(),trim($_POST['username']??''),$_POST['password']??'')) { header('Location: /'); exit; }
  $error='نام کاربری یا گذرواژه صحیح نیست.';
}
?><!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>ورود سردبیر</title><link rel="stylesheet" href="/style.css"></head><body><main class="login"><h1>ورود به میز سردبیری</h1><?php if($error):?><p class="error"><?= \Resaname\Support::e($error) ?></p><?php endif;?><form method="post"><input type="hidden" name="_csrf" value="<?=\Resaname\Support::csrf()?>"><label>نام کاربری</label><input name="username" required autocomplete="username"><label>گذرواژه</label><input type="password" name="password" required autocomplete="current-password"><button>ورود</button></form></main></body></html>
