<?php

namespace ZanPHP\NoSql\Facade;

use InvalidArgumentException;
use ZanPHP\Contracts\ConnectionPool\ConnectionManager;
use ZanPHP\NoSql\Redis as KVRedis;
use ZanPHP\Contracts\ConnectionPool\Connection;
use ZanPHP\Exception\ZanException;
use ZanPHP\Support\LZ4;
use ZanPHP\Support\ObjectArray;

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

    private static $_instance = null;
    private static $_configMap = null;

    private $namespace;
    private $setName;

    private function __construct($namespace, $setName)
    {
        $this->namespace = $namespace;
        $this->setName = $setName;
    }

    /**
     * @param $config
     * @return static
     */
    private static function getIns($config)
    {
        $ns = $config['namespace'];
        $set = $config['set'];

        $key = "$ns:$set";

        if (!isset(self::$_instance[$key])) {
            self::$_instance[$key] = new static($ns, $set);
        }
        return self::$_instance[$key];
    }

    public static function initConfigMap($configMap)
    {
        self::$_configMap = $configMap;
    }

    public static function get($configKey, $fmtArgs)
    {
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->get($realKey));
        $result = self::unSerialize($result);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function set($configKey, $fmtArgs, $value)
    {
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $value = self::serialize($value);

        $result = (yield $redis->set($realKey, $value));

        $ttl = isset($conf['exp']) ? $conf['exp'] : 0;
        if($result && $ttl){
            yield self::expire($redis, $realKey, $ttl);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hGet($configKey, $fmtArgs, $bin)
    {
        if ($bin === self::INVALID_BIN_NAME) {
            throw new \InvalidArgumentException("bin name " . self::INVALID_BIN_NAME . " is reversed");
        }
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->hGet($realKey, $bin));
        $result = self::unSerialize($result);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hSet($configKey, $fmtArgs, $bin, $value)
    {
        if ($bin === self::INVALID_BIN_NAME) {
            throw new InvalidArgumentException("bin name " . self::INVALID_BIN_NAME . " is reversed");
        }

        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $value = self::serialize($value);

        $result = (yield $redis->hSet($realKey, $bin, $value));

        $ttl = isset($conf['exp']) ? $conf['exp'] : 0;
        if($result && $ttl){
            yield self::expire($redis, $realKey, $ttl, 'hash');
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result === 1;
    }

    public static function hDel($configKey, $fmtArgs, $bin)
    {
        if ($bin === self::INVALID_BIN_NAME) {
            throw new InvalidArgumentException("bin name " . self::INVALID_BIN_NAME . " is reversed");
        }
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->hDel($realKey, $bin));
        $result = self::unSerialize($result);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result === 1;
    }

    public static function mGet($configKey, array $fmtArgsArray)
    {
        // @var Connection $conn
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKeys = [];
        foreach ($fmtArgsArray as $fmtArgs) {
            $realKeys[] = $self->fmtKVKey($conf, $fmtArgs);
        }
        $resultList = (yield $redis->mGet(...$realKeys));
        if ($resultList) {
            foreach ($resultList as &$result) {
                if ($result !== null) {
                    $result = self::unSerialize($result);
                }
            }
            unset($result);
        }

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $resultList;
    }

    public static function del($configKey, $fmtArgs)
    {
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->del($realKey));

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result === 1;
    }

    public static function incr($configKey, $fmtArgs)
    {
        yield static::incrBy($configKey, $fmtArgs);
    }

    public static function incrBy($configKey, $fmtArgs, $value = 1)
    {
        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->incrBy($realKey, $value));

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    public static function hIncrBy($configKey, $fmtArgs, $bin, $value = 1)
    {
        if ($bin === self::INVALID_BIN_NAME) {
            throw new InvalidArgumentException("bin name " . self::INVALID_BIN_NAME . " is reversed");
        }

        /* @var Connection $conn */
        $conf = self::getItemConfig($configKey);
        $self = self::getIns($conf);

        $conn = (yield $self->getConnection($conf['connection']));
        $redis = new KVRedis($conn);

        $realKey = $self->fmtKVKey($conf, $fmtArgs);
        $result = (yield $redis->hIncrBy($realKey, $bin, $value));
        $result = self::unSerialize($result);

        /** @noinspection PhpVoidFunctionResultUsedInspection */
        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    // 注意: 后端存储引擎可能对redis 协议支持有限制
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

        $ttl = isset($conf['exp']) ? $conf['exp'] : 0;
        if($result && $ttl){
            $dataType = self::getDataTypeByFuncName($func);
            if ($dataType != '') {
                yield self::expire($redis, $realKey, $ttl, $dataType);
            }
        }

        yield self::deleteActiveConnectionFromContext($conn);
        $conn->release();

        yield $result;
    }

    private static function getDataTypeByFuncName($func)
    {
        $funcMap = [
            'lPush' => 'list',
            'rPush' => 'list',
            'sAdd' => 'set',
            'zAdd' => 'zset',
        ];
        return isset($funcMap[$func]) ? $funcMap[$func] : '';
    }

    private static function expire(KVRedis $redis, $key, $ttl = 0, $dataType = 'kv')
    {
        /* @var Connection $conn */
        if(!$ttl || !$key){
            yield false;
            return;
        }
        switch ($dataType) {
            case 'kv' :
                yield $redis->expire($key, $ttl);
                break;
            case 'hash' :
                yield $redis->hexpire($key, $ttl);
                break;
            case 'list' :
                yield $redis->lexpire($key, $ttl);
                break;
            case 'set' :
                yield $redis->sexpire($key, $ttl);
                break;
            case 'zset' :
                yield $redis->zexpire($key, $ttl);
                break;
            default:
                yield $redis->expire($key, $ttl);
                break;
        }
    }

    public function getConnection($connection)
    {
        $connectionManager = make(ConnectionManager::class);
        $conn = (yield $connectionManager->get($connection));
        if (!$conn instanceof Connection) {
            throw new ZanException('kv get connection error');
        }
        yield $this->insertActiveConnectionIntoContext($conn);
        yield $conn;
    }

    private function insertActiveConnectionIntoContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            $activeConnections = new ObjectArray();
        }
        $activeConnections->push($connection);
        yield setContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, $activeConnections);
    }

    private static function deleteActiveConnectionFromContext($connection)
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        $activeConnections->remove($connection);
    }

    private static function closeActiveConnectionFromContext()
    {
        $activeConnections = (yield getContext(self::ACTIVE_CONNECTION_CONTEXT_KEY, null));
        if (null === $activeConnections || !($activeConnections instanceof ObjectArray)) {
            return;
        }
        while (!$activeConnections->isEmpty()) {
            $connection = $activeConnections->pop();
            if ($connection instanceof Connection) {
                $connection->close();
            }
        }
    }

    public static function terminate()
    {
        yield self::closeActiveConnectionFromContext();
    }

    public static function serialize($payload)
    {
        if (is_scalar($payload)) {
            $payload = strval($payload);
        } else {
            $payload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            if ($payload === false) {
                $errno = json_last_error();
                throw new InvalidArgumentException("serialize kv payload fail, [errno=$errno]");
            }
        }

        if (strlen($payload) > self::COMPRESS_LEN) {
            $payload = LZ4::getInstance()->encode($payload);
        }
        return $payload;
    }

    public static function unSerialize($payload)
    {
        if ($payload === null) {
            return null;
        }

        $lz4 = LZ4::getInstance();
        if ($lz4->isLZ4($payload)) {
            $payload = $lz4->decode($payload);
        }
        return $payload;
    }

    private function fmtKVKey($config, $fmtArgs){
        $kvPrefix = "$this->namespace:$this->setName:";

        $format = isset($config['key']) ? $config['key'] : null ;

        if($fmtArgs === null){
            if ($format === null) {
                throw new InvalidArgumentException('expect keys is string or array, null given');
            }
            return $kvPrefix . $format;
        } else {
            if(!is_array($fmtArgs)){
                $fmtArgs = [$fmtArgs];
            }
            return $kvPrefix . sprintf($format, ...array_values($fmtArgs));
        }
    }

    /**
     * @param string $configKey
     * @return array
     * @throws InvalidArgumentException
     */
    private static function getItemConfig($configKey)
    {
        $result = self::$_configMap;
        $routes = explode('.', $configKey);
        if (empty($routes)) {
            throw new InvalidArgumentException("Empty KV configKey");
        }
        foreach ($routes as $route) {
            if (!isset($result[$route])) {
                throw new InvalidArgumentException("Invalid KV config [configKey=$configKey]");
            }
            $result = &$result[$route];
        }
        return self::validConfig($configKey, $result);
    }

    private static function validConfig($configKey, $config)
    {
        if (!$config
            || !isset($config['connection'])
            || !isset($config['key'])
            || !isset($config['namespace'])
            || !isset($config['set'])
        ) {
            throw new InvalidArgumentException("Invalid KV config [configKey=$configKey]");
        }
        return $config;
    }

}