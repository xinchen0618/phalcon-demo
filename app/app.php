<?php
/**
 * Local variables
 * @var \Phalcon\Mvc\Micro $app
 */

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
            'message' => '您请求资源不存在'
        ]
    );
});


$app->get('/mysql', function () use ($app) {
//    $key = 'mysql:instance';
//    $db = $app->cache->get($key);
//    if (!$db) {
//        $mysqlConfig = $app->config->mysql->toArray();
//        $db = new \Phalcon\Db\Adapter\Pdo\Mysql($mysqlConfig);
//        $app->cache->save($key, $db, 3600);
//    }

    $sql = "SELECT * FROM users222";
    $users = $this->db->fetchAll($sql);

    return $app->response->setStatusCode(200)->setJsonContent($users);
});

$app->get('/redis', function () use ($app) {
//    $app->redisA->flushAll();

//    $app->redisA->set('aaa', 123);
//    $app->redisA->set('bbb', 234);
//
//    $app->redisB->set('AAA', 'abc');
//
//    var_export($app->redisA->dbSize());
//    echo "\n";
//    var_export($app->redisB->dbSize());

    var_export($app->redisA->info());
    echo "\n";
    var_export($app->redisA->config('GET', '*'));
});