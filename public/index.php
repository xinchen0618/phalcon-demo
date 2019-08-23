<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

// 错误提示转Exception
function exception_error_handler($severity, $message, $file, $line)
{
    if (!(error_reporting() & $severity)) {
        return;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
}

set_error_handler('exception_error_handler');

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

try {

    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overidden with custom ones.
     */
    $di = new FactoryDefault();

    /**
     * Include Services
     */
    include APP_PATH . '/config/services.php';

    /**
     * Get config service for use in inline setup below
     */
    $config = $di->getConfig();

    /**
     * Include Autoloader
     */
    include APP_PATH . '/config/loader.php';

    /**
     * Starting the application
     * Assign service locator to the application
     */
    $app = new Micro($di);

    /**
     * Include Application
     */
    include APP_PATH . '/app.php';
    // dynamic loading
    $routers = [
        '/account/v1' => 'account.php',
        '/order/v1' => 'order.php'
    ];
    foreach ($routers as $prefix => $router) {
        if (0 === strpos($_SERVER['REQUEST_URI'], $prefix)) {
            include APP_PATH . '/' . $router;
            break;
        }
    }

    /**
     * Handle the request
     */
    $app->handle();

} catch (\Throwable $e) {
    $app->response->setStatusCode(500)->setJsonContent([
        'status' => 'Exception',
        'message' => 'prod' == getenv('RUNTIME_ENVIRONMENT') ? '服务异常，请稍后重试' : $e->getMessage() . '. ' . $e->getTraceAsString()
    ])->send();
}
