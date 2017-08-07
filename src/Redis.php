<?php

namespace ZanPHP\NoSql;

use ZanPHP\Contracts\ConnectionPool\Connection;
use ZanPHP\Contracts\Debugger\Tracer;
use ZanPHP\Contracts\Trace\Constant;
use ZanPHP\Contracts\Trace\Trace;
use ZanPHP\Coroutine\Context;
use ZanPHP\Coroutine\Contract\Async;
use ZanPHP\Coroutine\Task;
use ZanPHP\Timer\Timer;

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
    private $callback;
    /**
     * @var Connection
     */
    private $conn;
    /**
     * @var \swoole_redis
     */
    private $sock;
    private $cmd;
    private $args;

    /** @var  Trace */
    private $trace;
    private $traceHandle;

    /**
     * @var Tracer
     */
    private $debuggerTrace;
    private $debuggerTid;

    const DEFAULT_CALL_TIMEOUT = 2000;

    /**
     * Redis constructor.
     * @param Connection $conn
     */
    public function __construct($conn)
    {
        $this->conn = $conn;
        $this->sock = $conn->getSocket();
    }

    public function __call($name, $arguments)
    {
        $value = (yield getContext("service-chain-value"));
        if (is_array($value) && isset($value["zan_test"]) && $value["zan_test"] === true) {
            //压测流量key增加前缀zan_test_
            $arguments[0] = "zan_test_".$arguments[0];
        }
        $this->cmd = $name;
        $this->args = $arguments;
        $arguments[] = [$this, 'recv'];
        $this->sock->$name(...$arguments);
        $this->beginTimeoutTimer();
        yield $this;
    }

    public function recv(/** @noinspection PhpUnusedParameterInspection */
        $client, $ret)
    {
        if ($this->trace instanceof Trace) {
            $this->trace->commit($this->traceHandle, Constant::SUCCESS);
        }

        if ($this->debuggerTrace instanceof Tracer) {
            $this->debuggerTrace->commit($this->debuggerTid, "info", $ret);
        }

        $this->cancelTimeoutTimer();
        call_user_func($this->callback, $ret);
    }

    public function execute(callable $callback, $task)
    {
        $conf = $this->conn->getConfig();
        if (isset($conf["path"])) {
            $dsn = $conf["path"];
        } else if (isset($conf["host"]) && isset($conf["port"])) {
            $dsn = "{$conf["host"]}:{$conf["port"]}";
        } else {
            $dsn = "";
        }

        /** @var Task $task */
        /** @var Context $ctx */
        $ctx = $task->getContext();
        $trace = $ctx->get("trace", null);

        if ($trace instanceof Trace) {
            $info = json_encode([
                "args" => $this->args,
                "dsn" => $dsn,
            ]);
            $this->trace = $trace;
            $this->traceHandle = $trace->transactionBegin(Constant::REDIS, $this->cmd." ".$info);
        }
        $debuggerTrace = $ctx->get("debugger_trace", null);
        if ($debuggerTrace instanceof Tracer) {
            $this->debuggerTid = $debuggerTrace->beginTransaction(Constant::REDIS, $this->cmd, [
                "args" => $this->args,
                "dsn" => $dsn,
            ]);
            $this->debuggerTrace = $debuggerTrace;
        }

        $this->callback = $callback;
    }

    private function beginTimeoutTimer()
    {
        $config = $this->conn->getConfig();
        $timeout = isset($config['timeout']) ? $config['timeout'] : self::DEFAULT_CALL_TIMEOUT;
        Timer::after($timeout, $this->onTimeout(), spl_object_hash($this));
    }

    private function cancelTimeoutTimer()
    {
        Timer::clearAfterJob(spl_object_hash($this));
    }

    private function onTimeout()
    {
        $start = microtime(true);
        return function() use($start) {
            if ($this->callback) {
                $duration = microtime(true) - $start;
                $ctx = [
                    "cmd" => $this->cmd,
                    "args" => $this->args,
                    "duration" => $duration,
                ];

                if ($this->debuggerTrace instanceof Tracer) {
                    $this->debuggerTrace->commit($this->debuggerTid, "warn", $ctx);
                }

                $callback = $this->callback;
                $ex = new RedisCallTimeoutException("Redis call {$this->cmd} timeout", 0, null, $ctx);
                if ($this->trace instanceof Trace) {
                    $this->trace->commit($this->traceHandle, $ex);
                }
                $callback(null, $ex);
            }
        };
    }
}