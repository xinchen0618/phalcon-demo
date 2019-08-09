<?php

/**
 * Registering an autoloader
 */
$loader = new \Phalcon\Loader();

$loader->registerDirs(
    [
        APP_PATH . '/controllers/'
    ]
)->register();
