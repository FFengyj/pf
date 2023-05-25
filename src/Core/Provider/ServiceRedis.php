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
                $redis        = new \Redis();
                $redis->connect($cfg->host, $cfg->port);
                if ($cfg->auth) {
                    $redis->auth($cfg->auth);
                }
                $redis->select($cfg->db);
                return $redis;
            });
        }

    }
}