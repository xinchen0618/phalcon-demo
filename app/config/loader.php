<?php

require BASE_PATH . '/vendor/autoload.php';

/**
 * Registering an autoloader
 */
$loader = new \Phalcon\Loader();

$loader->registerDirs(
    [
        APP_PATH . '/controllers/',
        APP_PATH . '/services/',
    ]
)->register();
