<?php
/**
 * Redis内存锁适配器类
 */

namespace Pf\System\Lock;


class RedisAdapter implements LockInterface
{
    private $clockDriftFactor = 0.01;

    private $quorum;

    private $servers = [];
    private $instances = [];
    private $lock = [];

    private $lock_prefix = '';
    private $lock_timewait = 200;
    private $lock_timeout = 15000;
    private $lock_retry_times = 10;

    function __construct($cache,$options)
    {

        $this->servers = [$cache];

        $property = ['lock_prefix','lock_timewait','lock_timeout','lock_retry_times'];
        foreach($property as $attr) {
            if(!isset($options[$attr])) continue;
            $this->$attr = $options[$attr];
        }
        $this->quorum  = min(count($this->servers), (count($this->servers) / 2 + 1));
    }

    public function acquire($resource, $ttl = 0)
    {

        $resource = $this->lock_prefix . $resource;
        $ttl = $ttl ?$ttl*1000: $this->lock_timeout;

        $token = uniqid();
        $retry = $this->lock_retry_times;

        do {
            $n = 0;
            $startTime = microtime(true) * 1000;
            foreach ($this->servers as $instance) {
                if ($this->lockInstance($instance, $resource, $token, $ttl)) {
                    $n++;
                }
            }

            # Add 2 milliseconds to the drift to account for Redis expires
            # precision, which is 1 millisecond, plus 1 millisecond min drift
            # for small TTLs.
            $drift = ($ttl * $this->clockDriftFactor) + 2;
            $validityTime = $ttl - (microtime(true) * 1000 - $startTime) - $drift;

            if ($n >= $this->quorum && $validityTime > 0) {
                return [
                    'validity' => $validityTime,
                    'resource' => $resource,
                    'token'    => $token,
                ];
            } else {
                foreach ($this->servers as $instance) {
                    $this->unlockInstance($instance, $resource, $token);
                }
            }

            // Wait a random delay before to retry
            $delay = mt_rand(floor($this->lock_timewait / 2), $this->lock_timewait);
            usleep($delay * 1000);
            $retry--;

        } while ($retry > 0);

        return false;
    }

    public function release($lock)
    {
        if (!$lock) return false;

        $resource = $lock['resource'];
        $token    = $lock['token'];

        foreach ($this->servers as $instance) {
            $this->unlockInstance($instance, $resource, $token);
        }
    }

    private function initInstances()
    {
        if (empty($this->instances)) {
            foreach ($this->servers as $server) {
                $redis = new \Redis();
                $redis->connect($server['host'], $server['port'], $server['timeout']);

                $this->instances[] = $redis;
            }
        }
    }

    private function lockInstance($instance, $resource, $token, $ttl)
    {
        return $instance->set($resource, $token, ['NX', 'PX' => $ttl]);
    }

    private function unlockInstance($instance, $resource, $token)
    {
        $script = '
            if redis.call("GET", KEYS[1]) == ARGV[1] then
                return redis.call("DEL", KEYS[1])
            else
                return 0
            end
        ';
        return $instance->eval($script, [$resource, $token], 1);
    }
}
