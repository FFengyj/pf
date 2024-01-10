<?php

namespace Pf\System\Core\Provider;


use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;

/**
 * Class ServiceRedis
 * @package Pf\System\Core\Provider
 * @author fyj
 */
class ServiceRedis implements ServiceProviderInterface
{


    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di)
    {
        $config = $di->get('config')->path('redis',[]);

        foreach ($config as $group => $cfg) {

            $service_name = $group == 'default' ? 'redis' : $group;
            $di->setShared($service_name, function() use ($cfg) {

                $redis = new \Redis();
                $conf = $cfg->toArray();

                $conf['timeout']        =  $conf['timeout'] ?? 5;
                $conf['retry_interval'] = $conf['retry_interval'] ?? 0;
                $conf['read_timeout']   = $conf['read_timeout'] ?? 0;
                $conf['reserved']       = $conf['reserved'] ?? null;
                $conf['options']        = $conf['options'] ?? [];

                if (!empty($conf['persistent_id'])) {
                    $redis->pconnect($conf['host'], $conf['port'],$conf['timeout'],$conf['persistent_id'],$conf['retry_interval'],$conf['read_timeout']);
                } else {
                    $redis->connect($conf['host'], $conf['port'],$conf['timeout'],$conf['reserved'],$conf['retry_interval'],$conf['read_timeout']);
                }

                if (isset($conf['auth'])) {
                    $redis->auth($conf['auth']);
                }
                $redis->select($conf['db'] ?? 0);

                foreach ($conf['options'] as $key => $val) {
                    $redis->setOption($key,$val);
                }

                return $redis;
            });
        }

    }
}
