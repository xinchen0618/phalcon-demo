<?php

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

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

    /**
     * dynamic loading
     * example:
     *      Module: /account/v1
     *      App: account.php
     *      Controller: controllers/AccountController.php
     */
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
    if ($di->getDb()->isUnderTransaction()) {
        $di->getDb()->rollback();
    }

    $message = 'prod' === getenv('RUNTIME_ENVIRONMENT') ? '服务异常，请稍后重试' : UtilService::getStringTrace($e);
    UtilService::errorResponse(500, 'Exception', $message);
}
