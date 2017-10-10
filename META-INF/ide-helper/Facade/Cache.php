<?php

namespace Zan\Framework\Store\Facade;

class Cache
{
    public static function initConfigMap($configMap)
    {
        \ZanPHP\NoSql\Facade\Cache::initConfigMap($configMap);
    }

    public static function appendConfigMapByConfigKey($configKey, array $config)
    {
        \ZanPHP\NoSql\Facade\Cache::appendConfigMapByConfigKey($configKey, $config);
    }

    public static function __callStatic($func, $args)
    {
        \ZanPHP\NoSql\Facade\Cache::__callStatic($func, $args);
    }

    public static function get($configKey, $keys)
    {
        \ZanPHP\NoSql\Facade\Cache::get($configKey, $keys);
    }

    public static function hGet($configKey, $keys, $field = '')
    {
        \ZanPHP\NoSql\Facade\Cache::hGet($configKey, $keys, $field);
    }

    public static function hMGet($configKey, $keys, $fields)
    {
        \ZanPHP\NoSql\Facade\Cache::hMGet($configKey, $keys, $fields);
    }

    public static function hSet($configKey, $keys, $field='', $value='')
    {
        \ZanPHP\NoSql\Facade\Cache::hSet($configKey, $keys, $field, $value);
    }

    public static function hMSet($configKey, $keys, array $kv)
    {
        \ZanPHP\NoSql\Facade\Cache::hMSet($configKey, $keys, $kv);
    }

    public static function hExists($configKey, $keys, $field = '')
    {
        \ZanPHP\NoSql\Facade\Cache::hExists($configKey, $keys, $field);
    }

    public static function hGetAll($configKey, $keys)
    {
        \ZanPHP\NoSql\Facade\Cache::hGetAll($configKey, $keys);
    }

    public static function hKeys($configKey, $keys)
    {
        \ZanPHP\NoSql\Facade\Cache::hKeys($configKey, $keys);
    }

    public static function hDel($configKey, $keys)
    {
        \ZanPHP\NoSql\Facade\Cache::hDel($configKey, $keys);
    }

    public static function set($configKey, $keys, $value)
    {
        \ZanPHP\NoSql\Facade\Cache::set($configKey, $keys, $value);
    }

    public static function incr($configKey, $keys)
    {
        \ZanPHP\NoSql\Facade\Cache::incr($configKey, $keys);
    }

    public static function mGet($configKey, array $keysArr)
    {
        \ZanPHP\NoSql\Facade\Cache::mGet($configKey, $keysArr);
    }

    public static function mSet($configKey, array $keysArr, array $values)
    {
        \ZanPHP\NoSql\Facade\Cache::mSet($configKey, $keysArr, $values);
    }

    public function getConnection($connection)
    {
        \ZanPHP\NoSql\Facade\Cache::getConnection($connection);
    }

    public static function terminate()
    {
        \ZanPHP\NoSql\Facade\Cache::terminate();
    }
}