<?php
declare(strict_types=1);

use Phalcon\Di\FactoryDefault;
use Phalcon\Mvc\Micro;

error_reporting(E_ALL);

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
    include APP_PATH . '/routes/app.php';

    /**
     * Dynamic loading
     * 默认v1, 其他版本加版本号
     * example:
     *      Module: /account/v1, /account/v2
     *      App: account.php, account_v2.php
     *      Controller: app/controllers/AccountController.php, app/controllers/AccountV2Controller.php
     */
    preg_match('/\/v\d+\//', $_SERVER['REQUEST_URI'], $version);
    if ($version) {
        $module = ltrim(strstr($_SERVER['REQUEST_URI'], $version[0], true), '/');
        $version = trim($version[0], '/');

        $controllerFile = APP_PATH . '/controllers/' . str_replace('_', '', ucwords($module, '_')) . ('v1' == $version ? '' : ucfirst($version)) . 'Controller.php';
        if (is_file($controllerFile)) {
            include $controllerFile;
        }

        $appFile = APP_PATH . '/routes/' . $module . ('v1' == $version ? '' : "_{$version}") . '.php';
        if (is_file($appFile)) {
            include $appFile;
        }
    }

    /**
     * Handle the request
     */
    $app->handle($_SERVER['REQUEST_URI']);
} catch (\Exception $e) {
    $message = UtilService::getStringTrace($e);
    error_log($message);
    if ('prod' === getenv('RUNTIME_ENVIRONMENT')) {
        $message = '服务异常, 请稍后重试';
    }
    UtilService::errorResponse(500, 'Exception', $message)->send();
}
