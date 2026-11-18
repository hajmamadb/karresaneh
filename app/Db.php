<?php
namespace Resaname;
use PDO;
final class Db {
    public static function main(): PDO { return self::connect('MAIN_DB'); }
    public static function editorial(): PDO { return self::connect('EDITORIAL_DB'); }
    private static function connect(string $prefix): PDO {
        $host=Config::get($prefix.'_HOST','127.0.0.1');
        $port=Config::get($prefix.'_PORT','3306');
        $name=Config::get($prefix.'_NAME');
        $user=Config::get($prefix.'_USER');
        $pass=Config::get($prefix.'_PASS','');
        if (!$name || !$user) throw new \RuntimeException("Database configuration incomplete: $prefix");
        $dsn="mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";
        return new PDO($dsn,$user,$pass,[
            PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES=>false,
            PDO::MYSQL_ATTR_INIT_COMMAND=>"SET NAMES utf8mb4"
        ]);
    }
}
