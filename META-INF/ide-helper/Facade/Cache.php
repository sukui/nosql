<?php

namespace Zan\Framework\Store\Facade;

/**
 * Class Cache
 * @package Zan\Framework\Store\Facade
 *
 * @method static bool del($configKey, $keys)
 */
class Cache
{

    const POOL_PREFIX = 'connection.';

    const ACTIVE_CONNECTION_CONTEXT_KEY= 'redis_active_connections';

    public static function initConfigMap($configMap)
    {
        self::$_configMap = $configMap;
    }

    /**
     * 给初始化后的Cache配置追加配置项
     *
     * @param string $configKey
     * @param array $config
     * @return null
     */
    public static function appendConfigMapByConfigKey($configKey, array $config)
    {

    }

    /**
     * @param $func
     * @param $args
     * @return \Generator|void
     */
    public static function __callStatic($func, $args)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     */
    public static function get($configKey, $keys)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @param string $field
     * @return \Generator|void
     */
    public static function hGet($configKey, $keys, $field = '')
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @param $fields
     * @return \Generator|void
     */
    public static function hMGet($configKey, $keys, $fields)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @param string $field
     * @param string $value
     * @return \Generator|void
     */
    public static function hSet($configKey, $keys, $field='', $value='')
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @param array $kv
     * @return \Generator|void
     */
    public static function hMSet($configKey, $keys, array $kv)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @param string $field
     * @return \Generator|void
     */
    public static function hExists($configKey, $keys, $field = '')
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     */
    public static function hGetAll($configKey, $keys)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     */
    public static function hKeys($configKey, $keys)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     */
    public static function hDel($configKey, $keys)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @param $value
     * @return \Generator|void
     */
    public static function set($configKey, $keys, $value)
    {

    }

    /**
     * @param $configKey
     * @param $keys
     * @return \Generator|void
     */
    public static function incr($configKey, $keys)
    {

    }

    /**
     * @param $configKey
     * @param array $keysArr
     * @return \Generator|void
     */
    public static function mGet($configKey, array $keysArr)
    {

    }

    /**
     * @param $configKey
     * @param array $keysArr
     * @param array $values
     * @return \Generator|void
     */
    public static function mSet($configKey, array $keysArr, array $values)
    {

    }

    /**
     * @param $connection
     * @return \Generator
     * @throws Exception
     * @throws \Zan\Framework\Foundation\Exception\System\InvalidArgumentException
     */
    public function getConnection($connection)
    {
        $conn = (yield ConnectionManager::getInstance()->get($connection));
        if (!$conn instanceof Connection) {
            throw new ZanException('Redis get connection error');
        }
        yield $this->insertActiveConnectionIntoContext($conn);
        yield $conn;
    }

    public static function terminate()
    {

    }
}