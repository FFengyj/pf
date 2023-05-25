<?php

namespace Pf\System\Core\Provider;


use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Pheanstalk\Pheanstalk;

/**
 * Class ServiceBeanstalk
 * @package Pf\System\Core\Provider
 * @author fyj
 */
class ServiceBeanstalk implements ServiceProviderInterface
{
    private $service_name = 'beanstalk';

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
        if ($config = $di->get('config')->path('beanstalk',[])) {

            $di->setShared($this->service_name, function () use($config) {
                return new Pheanstalk(
                    $config->host,
                    $config->port,
                    $config->connect_timeout,
                    $config->connect_persistent
                );
            });
        }
    }

}