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
    define('RUNTIME_ENV', $_SERVER['RUNTIME_ENV'] ?? '');

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
        $domainWhitelist = $di->get('config')->domainWhitelist->toArray();
        if (in_array('*', $domainWhitelist, true) || in_array($originDomain, $domainWhitelist, true)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
            header('Access-Control-Allow-Headers: accept, content-type, x-token, x-source');
            header('Access-Control-Allow-Methods: GET, HEAD, POST, PUT, DELETE, OPTIONS, PATCH');
            header('Access-Control-Max-Age: ' . 86400 * 30);
            if ('OPTIONS' === $_SERVER['REQUEST_METHOD']) {
                UtilService::successResponse(200)->send();
                exit;
            }
        }
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
     * Module/Version/Route
     * example:
     *      Module:
     *          蛇形式命名, 比如 /admin_order
     *      Version:
     *          Major[.Minor][.Revision], 比如 /v1, /v1.1, /v2, /v2.1 版本在Route方法中控制, 为必填.
     *      Route:
     *          Module名即为Route文件名, 默认v1, 其他版本加Major版本号. 比如 app/routes/admin_order.php, app/routes/admin_order_v2.php
     */
    preg_match('/\/v[\d.]+\//', $_SERVER['REQUEST_URI'], $version);
    if ($version) {
        $module = ltrim(strstr($_SERVER['REQUEST_URI'], $version[0], true), '/');
        $version = explode('.', trim($version[0], '/'));
        $routeVersion = 'v1' == $version[0] ? '' : "_{$version[0]}";
        $routeFile = APP_PATH . '/routes/' . $module . $routeVersion . '.php';
        if (is_file($routeFile)) {
            include $routeFile;
        }
    }

    /**
     * 请求来源校验
     */
    $moduleWhitelist = $di->get('config')->moduleWhitelist->toArray();
    if (empty($module) || !in_array($module, $moduleWhitelist)) {
        $source = $di->get('request')->getHeader('X-Source');
        $sourceWhitelist = $di->get('config')->sourceWhitelist->toArray();
        if (!$source || !isset($sourceWhitelist[$source])) {
            UtilService::errorResponse(400, 'InvalidSource', '无效请求来源');
        }
    }

    /**
     * Slow api
     */
    $apiStartTime = microtime(true);

    /**
     * Handle the request
     */
    $app->handle($_SERVER['REQUEST_URI']);

    $apiCost = microtime(true) - $apiStartTime;
    if ($apiCost > $di->get('config')->slowApiCost) {
        error_log("慢API警告! 执行耗时: {$apiCost}秒. API: {$_SERVER['REQUEST_METHOD']} {$_SERVER['REQUEST_URI']} \n");
    }

} catch (Throwable $e) {
    $message = $e->getMessage() . " \n" . $e->getTraceAsString() . " \n";
    error_log($message);

    $message = 'prod' === RUNTIME_ENV ? '服务异常, 请稍后重试' : $message;
    (new Response())->setStatusCode(500)->setJsonContent(
        [
            'status' => 'Exception',
            'message' => $message
        ]
    )->send();
}
