<?php

use Phalcon\Loader;

include BASE_PATH . '/vendor/autoload.php';

$loader = new Loader();
$loader->registerDirs([
    APP_PATH . '/services'
]);
$loader->register();
