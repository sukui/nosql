<?php

namespace Zan\Framework\Store\Facade;

class Store
{
    public static function initConfigMap($configMap)
    {
        \ZanPHP\NoSql\Facade\Store::initConfigMap($configMap);
    }

    public static function get($configKey, $fmtArgs)
    {
        \ZanPHP\NoSql\Facade\Store::get($configKey, $fmtArgs);
    }

    public static function set($configKey, $fmtArgs, $value)
    {
        \ZanPHP\NoSql\Facade\Store::set($configKey, $fmtArgs, $value);
    }

    public static function hGet($configKey, $fmtArgs, $bin)
    {
        \ZanPHP\NoSql\Facade\Store::hGet($configKey, $fmtArgs, $bin);
    }

    public static function hSet($configKey, $fmtArgs, $bin, $value)
    {
        \ZanPHP\NoSql\Facade\Store::hSet($configKey, $fmtArgs, $bin, $value);
    }

    public static function hDel($configKey, $fmtArgs, $bin)
    {
        \ZanPHP\NoSql\Facade\Store::hDel($configKey, $fmtArgs, $bin);
    }

    public static function mGet($configKey, array $fmtArgsArray)
    {
        \ZanPHP\NoSql\Facade\Store::mGet($configKey, $fmtArgsArray);
    }

    public static function del($configKey, $fmtArgs)
    {
        \ZanPHP\NoSql\Facade\Store::del($configKey, $fmtArgs);
    }

    public static function incr($configKey, $fmtArgs)
    {
        \ZanPHP\NoSql\Facade\Store::incr($configKey, $fmtArgs);
    }

    public static function incrBy($configKey, $fmtArgs, $value = 1)
    {
        \ZanPHP\NoSql\Facade\Store::incrBy($configKey, $fmtArgs, $value);
    }

    public static function hIncrBy($configKey, $fmtArgs, $bin, $value = 1)
    {
        \ZanPHP\NoSql\Facade\Store::hIncrBy($configKey, $fmtArgs, $bin, $value);
    }

    public function getConnection($connection)
    {
        \ZanPHP\NoSql\Facade\Store::getConnection($connection);
    }

    public static function terminate()
    {
        \ZanPHP\NoSql\Facade\Store::terminate();
    }

    public static function serialize($payload)
    {
        \ZanPHP\NoSql\Facade\Store::serialize($payload);
    }

    public static function unSerialize($payload)
    {
        \ZanPHP\NoSql\Facade\Store::unSerialize($payload);
    }
}