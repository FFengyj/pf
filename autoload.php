<?php

$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    [
        'Pf\System'       => __DIR__ . '/src',
    ]
);

$loader->registerFiles(
    [
        __DIR__ . '/src/Core/Functions.php'
    ]
);

$loader->register();

//require ROOT_PATH . '/vendor/autoload.php';


