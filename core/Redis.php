<?php

namespace Core;

class Redis
{
    private static ?\Redis $instance = null;

    public static function getInstance(): \Redis
    {
        if (self::$instance === null) {
            $redis = new \Redis();
            $host = Config::get('REDIS_HOST', '127.0.0.1');
            $port = (int)Config::get('REDIS_PORT', 6379);
            $password = Config::get('REDIS_PASSWORD', '');
            $db = (int)Config::get('REDIS_DB', 0);

            if (!$redis->connect($host, $port)) {
                throw new \RuntimeException("Redis connection failed");
            }

            if ($password) {
                $redis->auth($password);
            }

            $redis->select($db);
            self::$instance = $redis;
        }

        return self::$instance;
    }

    public static function get(string $key)
    {
        $value = self::getInstance()->get($key);
        return $value !== false ? json_decode($value, true) : null;
    }

    public static function set(string $key, $value, int $ttl = 0): bool
    {
        $data = json_encode($value);
        if ($ttl > 0) {
            return self::getInstance()->setex($key, $ttl, $data);
        }
        return self::getInstance()->set($key, $data);
    }

    public static function delete(string $key): bool
    {
        return self::getInstance()->del($key) > 0;
    }

    public static function exists(string $key): bool
    {
        return self::getInstance()->exists($key) > 0;
    }

    public static function expire(string $key, int $ttl): bool
    {
        return self::getInstance()->expire($key, $ttl);
    }
}
