<?php

return [
    \ZanPHP\NoSql\Facade\Cache::class => "Zan\\Framework\\Store\\Facade\\Cache",
    \ZanPHP\NoSql\Facade\Store::class => "Zan\\Framework\\Store\\Facade\\Store",
    \ZanPHP\NoSql\Redis::class => "Zan\\Framework\\Store\\NoSQL\\Redis\\Redis",
    \ZanPHP\NoSql\RedisCallTimeoutException::class => "Zan\\Framework\\Store\\NoSQL\\Exception\\RedisCallTimeoutException",
];