<?php
namespace App\Utils;

use Predis\Client;
use App\Config\Config;

class RedisCache
{
    private static $client = null;

    public static function getClient(): Client
    {
        if (self::$client === null) {
            self::$client = new Client([
                'scheme' => 'tcp',
                'host' => Config::REDIS_HOST,
                'port' => Config::REDIS_PORT
            ]);
        }
        return self::$client;
    }

    public static function get(string $key)
    {
        return self::getClient()->get($key);
    }

    public static function set(string $key, $value, int $ttl = 300)
    {
        return self::getClient()->setex($key, $ttl, $value);
    }
}
