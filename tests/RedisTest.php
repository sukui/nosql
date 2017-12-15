<?php
/**
 * Created by PhpStorm.
 * User: huye
 * Date: 2017/9/18
 * Time: 上午11:43
 */
namespace ZanPHP\NoSql\Tests;

use ZanPHP\NoSql\Facade\Cache;

class RedisTest extends TaskTestCase {

    public function taskSetGet()
    {
        try {
            $value = "redisTest";
            yield Cache::set("pf.test.test", ["zan", "test"], $value);
            $this->assertEquals($value, "redisTest");
            $result = (yield Cache::get("pf.test.test", ["zan", "test"]));
            $this->assertEquals($result, "redisTest");
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }

    }

    public function taskDel()
    {
        try {
            yield Cache::set("pf.test.test", ["zan", "test1"], "redisTest1");
            yield Cache::del("pf.test.test", ["zan", "test1"]);
            $result = (yield Cache::get("pf.test.test", ["zan", "test1"]));
            $this->assertEquals($result, null);
        } catch (\Exception $e) {
            var_dump($e->getMessage());
        }
    }
}