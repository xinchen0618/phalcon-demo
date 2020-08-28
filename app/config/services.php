<?php

use Phalcon\Config;

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    $config = include APP_PATH . '/config/config.php';

    return new Config($config);
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->getConfig()->mysql->toArray();

    return new Phalcon\Db\Adapter\Pdo\Mysql($config);
});

/**
 * redis存储
 */
$di->setShared('redis', function () {
    $redisConfig = $this->getConfig()->redis;
    $redis = new Redis();
    $redis->connect($redisConfig->host, $redisConfig->port);
    if ($redisConfig->auth) {
        $redis->auth($redisConfig->auth);
    }
    $redis->select($this->getConfig()->redisDbIndex->redis);

    return $redis;
});

/**
 * 队列Redis
 */
$di->setShared('queueRedis', function () {
    $redisConfig = $this->getConfig()->redis;
    $redis = new Redis();
    $redis->connect($redisConfig->host, $redisConfig->port);
    if ($redisConfig->auth) {
        $redis->auth($redisConfig->auth);
    }
    $redis->select($this->getConfig()->redisDbIndex->queue);

    return $redis;
});
