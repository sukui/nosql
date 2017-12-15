<?php
/**
 * Created by PhpStorm.
 * User: huye
 * Date: 2017/9/18
 * Time: 上午11:43
 */
namespace ZanPHP\NoSql\Tests;


use ZanPHP\NoSql\RedisCallTimeoutException;
use ZanPHP\NoSql\Redis;
use ZanPHP\ConnectionPool\ConnectionManager;


class RedisTimeoutTest extends TaskTestCase {

    public function taskCallTimeout()
    {
        try {
            $conn = (yield ConnectionManager::getInstance()->get("redis.default_timeout"));
            $redis = new Redis($conn);
            yield $redis->set("foo", "value");
        } catch (RedisCallTimeoutException $e) {
            $this->assertTrue(true);
            return;
        }
        $this->fail("Expected RedisCallTimeoutException has not been raised.");
    }
}