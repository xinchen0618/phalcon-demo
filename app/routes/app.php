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
