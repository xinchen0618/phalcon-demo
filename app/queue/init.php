<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault\Cli as CliDi;

define('BASE_PATH', dirname(__DIR__, 2));
define('APP_PATH', BASE_PATH . '/app');

/**
 * The FactoryDefault Dependency Injector automatically registers the services that
 * provide a full stack framework. These default services can be overidden with custom ones.
 */
$di = new CliDi();

/**
 * Include Services
 */
include APP_PATH . '/config/services.php';

/**
 * Include Autoloader
 */
include APP_PATH . '/config/loader.php';

