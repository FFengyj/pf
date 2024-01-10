<?php

namespace Pf\System\Core\Provider;


use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Pheanstalk\Pheanstalk;

/**
 * Class ServiceLock
 * @package Pf\System\Core\Provider
 * @author fyj
 */
class ServiceLock implements ServiceProviderInterface
{

    private $service_name = 'lock';

    /**
     * Beanstalk constructor.
     * @param string $service_name
     */
    public function __construct($service_name = '')
    {
        if ($service_name) {
            $this->service_name = $service_name;
        }
    }

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di)
    {
        if ($config = $di->get('config')->path('lock',[])) {
            $di->setShared($this->service_name, function () use($di,$config) {
                $memory = $di->getShared($config['memory_svc']);
                return new $config['adapter']($memory,$config['options']);
            });
        }
    }

}
