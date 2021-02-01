<?php

/**
 * Shared configuration service
 */

use Phalcon\Session\Bag as SessionBag;

$di->setShared('config', function () {
    $config = include APP_PATH . '/config/config.php';

    // 环境配置优先
    if (is_file(APP_PATH . '/config/config_' . RUNTIME_ENV. '.php')) {
        $envConfig = include APP_PATH . '/config/config_' . RUNTIME_ENV . '.php';
        $config = array_merge($config, $envConfig);
    }

    return new Phalcon\Config($config);
});

/**
 * Database connection is created based in the parameters defined in the configuration file
 */
$di->setShared('db', function () {
    $config = $this->get('config')->mysql->toArray();

    return new Phalcon\Db\Adapter\Pdo\Mysql($config);
});

/**
 * cache, redis长连接
 */
$di->setShared('cache', function () {
    $redisConfig = $this->get('config')->redis;
    $cache = new Redis();
    $cache->pconnect($redisConfig->host, $redisConfig->port);
    if ($redisConfig->auth) {
        $cache->auth($redisConfig->auth);
    }
    $cache->select($redisConfig->index->cache);

    return $cache;
});

/**
 * redis存储, redis长连接
 */
$di->setShared('redis', function () {
    $redisConfig = $this->get('config')->redis;
    $redis = new Redis();
    $redis->pconnect($redisConfig->host, $redisConfig->port);
    if ($redisConfig->auth) {
        $redis->auth($redisConfig->auth);
    }
    $redis->select($redisConfig->index->redis);

    return $redis;
});

/**
 * session, redis长连接
 */
$di->setShared('session', function () {
    $redisConfig = $this->get('config')->redis;
    $options = [
        'host' => $redisConfig->host,
        'port' => $redisConfig->port,
        'auth' => $redisConfig->auth,
        'index' => $redisConfig->index->session,
        'lifetime' => 86400 * 30,
        'persistent' => true
    ];

    $session = new Phalcon\Session\Manager();
    $serializerFactory = new Phalcon\Storage\SerializerFactory();
    $factory = new Phalcon\Storage\AdapterFactory($serializerFactory);
    $redis = new Phalcon\Session\Adapter\Redis($factory, $options);

    $token = $this->get('request')->getHeader('X-Token');
    if ($token && strlen($token) === 26) {
        $session->setId($token);
    }

    session_set_cookie_params(86400 * 30);

    $session->setAdapter($redis)->start();

    return $session;
});
$di->setShared('userSession', function () {
    $userSession = new SessionBag('user');
    $userSession->setDI($this);

    return $userSession;
});
$di->setShared('adminSession', function () {
    $adminSession = new SessionBag('admin');
    $adminSession->setDI($this);

    return $adminSession;
});

/**
 * 消息队列redis
 */
$di->setShared('queueRedis', function () {
    $redisConfig = $this->get('config')->redis;
    $redis = new Redis();
    $redis->connect($redisConfig->host, $redisConfig->port);
    if ($redisConfig->auth) {
        $redis->auth($redisConfig->auth);
    }
    $redis->select($redisConfig->index->queue);

    return $redis;
});
