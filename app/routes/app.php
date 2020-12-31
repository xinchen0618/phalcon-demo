<?php

/**
 * Add your routes here
 */
$app->get('/', function () use($app) {
    return $app->response->setStatusCode(200)->setJsonContent(
        [
            'status' => 'OK',
            'message' => '服务正常'
        ]
    );
});

/**
 * Not found handler
 */
$app->notFound(function () use($app) {
    return $app->response->setStatusCode(404)->setJsonContent(
        [
            'status' => 'ResourceNotFound',
            'message' => '您请求的资源不存在'
        ]
    );
});

$app->after(function () use ($app, $apiStartTime) {
    // 慢API警告
    $apiDuration = microtime(true) - $apiStartTime;
    if ($apiDuration > $app->config->slowApiDuration) {
        $api = $_SERVER['REQUEST_METHOD'] . ' ' . $_SERVER['REQUEST_URI'];
        error_log("慢API警告! 执行耗时: {$apiDuration}秒. API: {$api} \n");
    }
});
