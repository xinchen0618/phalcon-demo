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
     * Load controller
     */
    $loader->registerNamespaces(
        [
            'app\controllers' => BASE_PATH . '/app/controllers',
        ],
        true
    );
    $loader->register();

    /**
     * 跨域
     */
    if (!empty($_SERVER['HTTP_ORIGIN'])) {
        $originHost = parse_url($_SERVER['HTTP_ORIGIN'], PHP_URL_HOST);
        if (false === strpos($originHost, '.')) {       // localhost
            $originDomain = $originHost;
        } elseif (filter_var($originHost, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {      // IP
            $originDomain = $originHost;
        } else {    // 取根域名
            $originHost = explode('.', $originHost);
            $originLen = count($originHost);
            $originDomain = $originHost[$originLen - 2] . '.' . $originHost[$originLen - 1];
        }
        $domainWhitelist = $di->getConfig()->domainWhitelist->toArray();
        if (in_array('*', $domainWhitelist, true) || in_array($originDomain, $domainWhitelist, true)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Headers: accept, content-type, x-token');
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
     * 默认v1, 其他版本加版本号, 蛇形式命名
     * example:
     *      Module:
     *          /admin_order/v1, /admin_order/v2
     *      Route:
     *          app/routes/admin_order.php, app/routes/admin_order_v2.php
     */
    preg_match('/\/v\d+\//', $_SERVER['REQUEST_URI'], $version);
    if ($version) {
        $module = ltrim(strstr($_SERVER['REQUEST_URI'], $version[0], true), '/');   // e.g. admin_order
        $version = trim($version[0], '/');  // e.g. v1
        $routeFile = APP_PATH . '/routes/' . $module . ('v1' == $version ? '' : "_{$version}") . '.php';    // e.g. admin_order.php
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
