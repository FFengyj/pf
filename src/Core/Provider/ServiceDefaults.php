<?php

namespace Pf\System\Core\Provider;


use Phalcon\Config;
use Phalcon\Db\Adapter\Pdo\Factory;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\DiInterface;
use Phalcon\Mvc\Router;
use Phalcon\Mvc\View;
use Phalcon\Mvc\ViewInterface;

/**
 * Class ServiceDefaults
 * @package Pf\System\Core\Provider
 * @author fyj
 */
class ServiceDefaults implements ServiceProviderInterface
{

    protected $options;

    /**
     * DefaultDiProvider constructor.
     * @param $option
     */
    public function __construct($option)
    {
        $this->options = $option;
    }

    /**
     * @param DiInterface $di
     */
    public function register(DiInterface $di)
    {
        if (php_sapi_name() !== 'cli') {
            $this->registerRouter($di);
            $this->registerView($di);
        }
        $this->registerDatabases($di);
        $this->registerLogger($di);
    }

    /**
     * 注册 路由服务
     * @param DiInterface $di
     */
    protected function registerRouter(DiInterface $di)
    {
        $options = $this->options;

        $di->setShared('router', function () use($di,$options) {

            $config = $di->get('config')->routes->toArray();

            if (isset($config[$options['module_name']])) {
                $router =  require $config[$options['module_name']];
            } elseif (isset($config['default'])) {
                $router =  require $config['default'];
            }else {
                $router = new Router(true);
                $router->removeExtraSlashes(true);
                $router->setDefaultNamespace('Controllers');
            }

            if ($em = attach_events($di->get('config')->path('event.router',[]))) {
                $router->setEventsManager($em);
            }
            return $router;
        });
    }

    /**
     * 注册模板服务
     * @param DiInterface $di
     */
    protected function registerView(DiInterface $di)
    {
        $di->setShared('view', function (){

            /* @var DiInterface $this */
            $config = $this->get('config')->view->toArray();

            /* @var ViewInterface|View $view */
            $view = new $config['adapter'];

            if (!empty($config['engines'])) {
                $view->registerEngines($config['engines']);
            }

            if (!empty($config['base_dir'])) {
                $view->setViewsDir($config['base_dir']);
            }

            if ($em = attach_events($this->get('config')->path('event.view',[]))) {
                $view->setEventsManager($em);
            }

            return $view;
        });
    }

    /**
     * 注册数据库服务
     * @param DiInterface $di
     */
    protected function registerDatabases(DiInterface $di)
    {
        $db_config= $di->get('config')->db;
        foreach ($db_config as $group => $db) {

            $service_name = $group == 'default' ? 'db' : $group;

            $di->setShared($service_name, function () use($db,$group) {

                $connection =  Factory::load($db);

                $events = $this->get('config')->path("event.db",new Config())->toArray();
                $events = array_merge($events,$this->get('config')->path("event.db:{$group}",new Config())->toArray());

                if ($em = attach_events($events)) {
                    $connection->setEventsManager($em);
                }
                return $connection;
            });
        }
    }

    /**
     * @param DiInterface $di
     */
    protected function registerLogger(DiInterface $di)
    {
        $loggers = $di->get('config')->path('logger',[]);

        foreach ($loggers as $group => $log) {
            $service_name = $group == 'default' ? 'logger' : $group;
            $di->setShared($service_name, function () use($group,$log) {
                if (empty($log['params']['filename'])) {
                    $log['params']['filename'] = sprintf("%s.log",$group);
                }
                return new $log['class']($log['params']);
            });
        }
    }
}
