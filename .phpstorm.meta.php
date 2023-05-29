<?php

namespace PHPSTORM_META {


    override(\Di(0), map([
        '' => "@",
        'redis' => \Redis::class,
        'flash' => \Pf\System\Core\Plugin\Flash::class,
        'config' => \Phalcon\Config::class,
        'db' => \Phalcon\Db\AdapterInterface::class,
        'view' => \Phalcon\Mvc\View::class,
        'router' => \Phalcon\Mvc\Router::class,
        'queue' => \Pheanstalk\Pheanstalk::class,
        'lock' => \Pf\System\Lock\LockInterface::class,
        'logger' => \Phalcon\Logger\AdapterInterface::class,
        'request' => \Pf\System\Core\Plugin\Request::class,
        'response' => \Pf\System\Core\Plugin\Response::class,
        'dispatcher' => \Phalcon\Dispatcher::class,
        'modelsManager' => \Phalcon\Mvc\Model\Manager::class,
        'collectionManager' => \Phalcon\Mvc\Collection\Manager::class,
    ]));

    override(\Phalcon\Mvc\ModelInterface::findFirst(), map(["" => \Pf\System\Core\Mvc\ModelBase::class]));
    override(\Phalcon\Mvc\ControllerInterface::flash, map(["" => \Pf\System\Core\Plugin\Flash::class]));


}