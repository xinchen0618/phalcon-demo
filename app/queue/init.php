<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault\Cli as CliDi;

define('BASE_PATH', dirname(__DIR__, 2));
define('APP_PATH', BASE_PATH . '/app');

/**
 * The FactoryDefault Dependency Injector automatically registers the services that
 * provide a full stack framework. These default services can be overridden with custom ones.
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

/**
 * REDIS_BACKEND can have simple 'host:port' format or use a DSN-style format like this:
 * - redis://user:pass@host:port
 *
 * Note: the 'user' part of the DSN URI is required but is not used.
 */
if (null === Resque::$redis) {
    $config = $di->get('config');
    Resque::setBackend("{$config->redis->host}:{$config->redis->port}", $config->redisDbIndex->queue, $config->redis->auth);
}
