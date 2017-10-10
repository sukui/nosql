<?php

namespace Zan\Framework\Store\NoSQL\Redis;

use ZanPHP\Coroutine\Contract\Async;

class Redis implements Async
{
    private $Redis;

    public function __construct($conn)
    {
        $this->Redis = new \ZanPHP\NoSql\Redis($conn);
    }

    public function __call($name, $arguments)
    {
        $this->Redis->__call($name, $arguments);
    }

    public function recv($client, $ret)
    {
        $this->Redis->recv($client, $ret);
    }

    public function execute(callable $callback, $task)
    {
        $this->Redis->execute($callback, $task);
    }
}