<?php
/**
 * 公共load
 */

use Phalcon\Loader;

include BASE_PATH . '/vendor/autoload.php';

$loader = new Loader();
$loader->registerNamespaces(
    [
        'app\services' => BASE_PATH . '/app/services',
    ]
);
$loader->register();
