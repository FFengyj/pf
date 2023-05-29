<?php

use Pf\System\Bootstrap;
use Phalcon\DI\FactoryDefault\CLI as CliDI;
use Phalcon\CLI\Console as ConsoleApp;

define('ROOT_PATH', dirname(__DIR__, 1));

require ROOT_PATH . '/vendor/autoload.php';

$options = [
    'root_path'   => ROOT_PATH,
];

$bootstrap = new Bootstrap(new CliDI(),$options);
$bootstrap(ConsoleApp::class,function(ConsoleApp $app,CliDI $di){


    var_dump(Di('config')->components_config->toArray());
    var_dump($di->get('config')->routes->toArray());

    var_dump(Di('config')->path('loader.set_extensions')->toArray());
    var_dump(Di('config')->path('not_exists.key','default value'));


});







?>




