<?php

namespace Zan\Framework\Store\Facade;

/**
 * Class KVRedis
 *
 * 具体命令支持 参考 doc
 * http://doc.xxx.xxx/pages/viewpage.action?pageId=4860611
 *
 * AS与REDIS协议映射关系参考 :
 *
 * AS                  | REDIS
 * --------------------|------------
 * namespace:set:{key} | hash key
 * bin                 | hash field
 * {value}             | hash value
 *
 * set ns:set:key def_bin value
 * get ns:set:key def_bin
 *
 * hset ns:set:key bin value
 * hget ns:set:key bin
 *
 * 注意:
 *
 * 1. 使用redis协议存储的值，使用zan框架的as接口无法取出
 * 2. zan框架存入的string类型的值，使用redis协议可以取出；其他类型的值需要做数据迁移，无法取出;
 * 3. 使用redis协议时, hset不可以使用保留key值”redisvalue”
 * 4. 使用redis时，hset之后的值，通过get和mget无法取出；
 *    但是通过KV Proxy，get和mget可以取出，取出的值为json格式(value类型为Base64编码)
 */
class Store
{

    const COMPRESS_LEN = 1024; /* lz4 压缩阈值(min:strlen) */
    const DEFAULT_BIN_NAME = '_z_dft';
    const INVALID_BIN_NAME = 'redisvalue';
    const ACTIVE_CONNECTION_CONTEXT_KEY= 'kv_store2_active_connections';

    private function __construct($namespace, $setName)
    {

    }

    public static function initConfigMap($configMap)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @return \Generator
     */
    public static function get($configKey, $fmtArgs)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @param $value
     * @return \Generator
     */
    public static function set($configKey, $fmtArgs, $value)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @param $bin
     * @return \Generator
     */
    public static function hGet($configKey, $fmtArgs, $bin)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @param $bin
     * @param $value
     * @return \Generator
     */
    public static function hSet($configKey, $fmtArgs, $bin, $value)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @param $bin
     * @return \Generator
     */
    public static function hDel($configKey, $fmtArgs, $bin)
    {

    }

    /**
     * @param $configKey
     * @param array $fmtArgsArray
     * @return \Generator
     */
    public static function mGet($configKey, array $fmtArgsArray)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @return \Generator
     */
    public static function del($configKey, $fmtArgs)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @return \Generator
     */
    public static function incr($configKey, $fmtArgs)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @param int $value
     * @return \Generator
     */
    public static function incrBy($configKey, $fmtArgs, $value = 1)
    {

    }

    /**
     * @param $configKey
     * @param $fmtArgs
     * @param $bin
     * @param int $value
     * @return \Generator
     */
    public static function hIncrBy($configKey, $fmtArgs, $bin, $value = 1)
    {

    }

    /*
    // redis 协议支持有限制
    public static function __callStatic($func, $args)
    {
        // @var Connection $conn
        $configKey = array_shift($args);
        $keys = array_shift($args);

        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $keys);
        $result = (yield $redis->$func($realKey, ...$args));

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }
    */

    /**
     * @param $connection
     * @return \Generator
     */
    public function getConnection($connection)
    {

    }

    public static function terminate()
    {

    }

    /**
     * @param $payload
     * @return string
     * @throws InvalidArgumentException
     */
    public static function serialize($payload)
    {

    }


    public static function unSerialize($payload)
    {

    }
}