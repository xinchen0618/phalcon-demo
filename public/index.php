<?php
declare(strict_types=1);

use app\services\UtilService;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Mvc\Micro;

try {
    /**
     * 错误提示转Exception
     */
    set_error_handler(function ($severity, $message, $file, $line) {
        if (!(error_reporting() & $severity)) {
            return;
        }
        throw new ErrorException($message, 0, $severity, $file, $line);
    });

    error_reporting(E_ALL);

    define('BASE_PATH', dirname(__DIR__));
    define('APP_PATH', BASE_PATH . '/app');

    /**
     * The FactoryDefault Dependency Injector automatically registers the services that
     * provide a full stack framework. These default services can be overridden with custom ones.
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
     * 跨域
     */
    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $origin = explode('.', parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST));
        $originLen = count($origin);
        $originDomain = $originLen > 2 ? $origin[$originLen - 2] . '.' . $origin[$originLen - 1] : $origin[0];
        $domainWhitelist = $di->getConfig()->domainWhitelist->toArray();
        if (in_array('*', $domainWhitelist, true) || in_array($originDomain, $domainWhitelist, true)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Headers: accept, content-type');
            header('Access-Control-Allow-Methods: GET, HEAD, POST, PUT, DELETE, OPTIONS, PATCH');
            header('Access-Control-Max-Age: ' . 86400 * 30);
            if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
                UtilService::successResponse(200)->send();
                exit;
            }
        }
    }

    /**
     * POST防重
     */
    if ('POST' === $_SERVER['REQUEST_METHOD']) {
        UtilService::speedLimit();
    }

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
     *      Route: app/routes/account.php, app/routes/account_v2.php
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

        $routeFile = APP_PATH . '/routes/' . $module . ('v1' == $version ? '' : "_{$version}") . '.php';
        if (is_file($routeFile)) {
            include $routeFile;
        }
    }

    /**
     * Handle the request
     */
    $app->handle($_SERVER['REQUEST_URI']);
} catch (Throwable $e) {
    $message = $e->getMessage() . " \n" . $e->getTraceAsString() . " \n";
    error_log($message);
    if ('prod' === getenv('RUNTIME_ENV')) {
        $message = '服务异常, 请稍后重试';
    }
    (new Response())->setStatusCode(500)->setJsonContent(
        [
            'status' => 'Exception',
            'message' => $message
        ]
    )->send();
}
