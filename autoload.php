<?php

$loader = new \Phalcon\Loader();
$loader->registerNamespaces(
    [
        'Pf\System'       => ROOT_PATH . '/src',
    ]
);

$loader->registerFiles(
    [
        ROOT_PATH . '/src/Core/Functions.php'
    ]
);

$loader->register();

//require ROOT_PATH . '/vendor/autoload.php';


