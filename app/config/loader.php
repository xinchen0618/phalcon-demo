<?php
/**
 * Common load
 */

use Phalcon\Loader;

include BASE_PATH . '/vendor/autoload.php';
include APP_PATH . '/config/redis_constant.php';

$loader = new Loader();
$loader->registerNamespaces(
    [
        'app\services' => BASE_PATH . '/app/services',
    ]
);
$loader->register();
