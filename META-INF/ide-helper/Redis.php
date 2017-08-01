<?php

namespace Zan\Framework\Store\NoSQL\Redis;

use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Contract\Async;

/**
 * Class Redis
 * @method string get(string $key);
 * @method bool set(string $key, string $value);
 * @method array mGet(...$keys);
 * @method int hSet(string $key, string $field, string $value);
 * @method string hGet(string $key, string $field);
 * @method string hDel(string $key, string $field);
 * @method bool expire(string $key, int $ttlSec);
 * @method int incr(string $key);
 * @method int incrBy(string $key, int $value);
 * @method int hIncrBy(string $key, string $field, int $value);
 * @method bool del(string $key);
 * @method bool hMGet(string $key, ...$params);
 * @method bool hMSet(string $key, ...$params);
 */
class Redis implements Async
{
    const DEFAULT_CALL_TIMEOUT = 2000;

    /**
     * Redis constructor.
     * @param Connection $conn
     */
    public function __construct($conn)
    {

    }

    public function __call($name, $arguments)
    {

    }

    public function recv(/** @noinspection PhpUnusedParameterInspection */
        $client, $ret)
    {

    }

    public function execute(callable $callback, $task)
    {

    }
}