<?php

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    return include APP_PATH . '/config/config.php';
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $mysqlConfig = $this->getConfig()->mysql->toArray();

    return new \Phalcon\Db\Adapter\Pdo\Mysql($mysqlConfig);
});

/**
 * 缓存, redis dbindex 0
 */
$di->setShared('cache', function () {
    $frontCache = new Phalcon\Cache\Frontend\Data(
        [
            'lifetime' => 86400 * 30,
        ]
    );

    $redis = $this->getConfig()->redis;

    $cache = new Phalcon\Cache\Backend\Redis(
        $frontCache,
        [
            'host' => $redis->host,
            'port' => $redis->port,
            'persistent' => false,
            'index' => 0,
        ]
    );

    return $cache;
});

$di->setShared('redisA', function () {
    $redisConfig = $this->getConfig()->redis;
    $redisA = new Redis();
    $redisA->pconnect($redisConfig->host, $redisConfig->port, 0, 'persistent_0');
    $redisA->select(1);

    return $redisA;
});

$di->setShared('redisB', function () {
    $redisConfig = $this->getConfig()->redis;
    $redisB = new Redis();
    $redisB->pconnect($redisConfig->host, $redisConfig->port, 0, 'persistent_1');
    $redisB->select(2);

    return $redisB;
});
