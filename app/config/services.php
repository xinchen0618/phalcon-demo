<?php

/**
 * Shared configuration service
 */
$di->setShared('config', function () {
    $config = include APP_PATH . '/config/config.php';

    return new Phalcon\Config($config);
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

/**
 * session
 */
$di->setShared('session', function () {
    $redisConfig = $this->getConfig()->redis;
    $options = [
        'host' => $redisConfig->host,
        'port' => $redisConfig->port,
        'auth' => $redisConfig->auth,
        'index' => $this->getConfig()->redisDbIndex->session,
        'lifetime' => 86400 * 30
    ];

    $session = new Phalcon\Session\Manager();
    $serializerFactory = new Phalcon\Storage\SerializerFactory();
    $factory = new Phalcon\Storage\AdapterFactory($serializerFactory);
    $redis = new Phalcon\Session\Adapter\Redis($factory, $options);

    $token = $this->getRequest()->getHeader('X-Token');
    if ($token && strlen($token) === 26) {
        $session->setId($token);
    }

    session_set_cookie_params(86400 * 30);

    $session->setAdapter($redis)->start();

    return $session;
});
