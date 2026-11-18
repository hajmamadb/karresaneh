<?php
namespace Resaname;
final class Config {
    private static array $v = [];
    public static function load(string $file): void {
        if (!is_file($file)) throw new \RuntimeException('.env is missing');
        foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#') || !str_contains($line, '=')) continue;
            [$k,$v] = explode('=', $line, 2);
            self::$v[trim($k)] = trim(trim($v), "\"'");
        }
        date_default_timezone_set(self::get('TIMEZONE','Asia/Tehran'));
    }
    public static function get(string $key, ?string $default=null): ?string { return self::$v[$key] ?? $default; }
    public static function bool(string $key, bool $default=false): bool { return filter_var(self::get($key, $default?'true':'false'), FILTER_VALIDATE_BOOLEAN); }
}
