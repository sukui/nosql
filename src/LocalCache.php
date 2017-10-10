<?php

namespace ZanPHP\NoSql;

use ZanPHP\Cache\APCuStore;

class LocalCache
{
    private $delegate;
    private $store;
    private $ttl;

    const DEFAULT_TTL = 3;

    public function __construct($delegate, $ttl)
    {
        $this->delegate = $delegate;
        $this->ttl = is_int($ttl)? $ttl: static::DEFAULT_TTL;
        $this->store = new APCuStore("local");
    }

    public function get($key)
    {
        $value = $this->store->get($key);
        if ($value !== null) {
            yield $value;
            return;
        }
        $value = (yield $this->delegate->get($key));
        $this->store->put($key, $value, $this->ttl);
        yield $value;
    }

    public function hGet($key, $field)
    {
        $value = $this->store->get($key);
        if ($value !== null) {
            $value = json_decode($value, true);
            yield isset($value[$field])? $value[$field]: null;
            return;
        }
        $value = (yield $this->delegate->hGet($key, $field));

        $str = json_encode($value);
        $this->store->put($key, $str, $this->ttl);
        yield $value;
    }

    public function mSet(...$args)
    {
        yield $this->delegate->mSet(...$args);
        foreach ($args as $arg) {
            $this->store->forget($arg);
        }
    }

    public function hMSet($key, ...$args)
    {
        yield $this->delegate->hMSet($key, ...$args);

        foreach ($args as $index => $arg) {
            if ($index % 2 == 0) {
                $this->store->forget($arg);
            }
        }
    }

    public function expire($key, $ttl)
    {
        yield $this->delegate->expire($key, $ttl);
    }

    public function __call($method, $arguments)
    {
        yield $this->delegate->$method(...$arguments);
        if (isset($arguments[0])) {
            $this->store->forget($arguments[0]);
        }
    }
}