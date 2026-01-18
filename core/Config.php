<?php

namespace Core;

class Config
{
    private static array $config = [];

    public static function load(string $envFile = '.env'): void
    {
        if (!file_exists($envFile)) {
            throw new \RuntimeException("Environment file {$envFile} not found");
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }

            if (strpos($line, '=') === false) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value);

            // Remove quotes if present
            if (preg_match('/^"(.*)"$/', $value, $matches)) {
                $value = $matches[1];
            } elseif (preg_match("/^'(.*)'$/", $value, $matches)) {
                $value = $matches[1];
            }

            self::$config[$key] = $value;
        }

        // Set timezone
        if (isset(self::$config['TIMEZONE'])) {
            date_default_timezone_set(self::$config['TIMEZONE']);
        }
    }

    public static function get(string $key, $default = null)
    {
        return self::$config[$key] ?? $default;
    }

    public static function has(string $key): bool
    {
        return isset(self::$config[$key]);
    }
}
