<?php

return [
    // MySQL
    'mysql' => [
        'host' => '127.0.0.1',
        'port' => 3306,
        'username' => 'root',
        'password' => 'cx654321',
        'dbname' => 'test',
        'charset' => 'utf8mb4',
        'options' => [PDO::ATTR_EMULATE_PREPARES => false]
    ],

    // Redis
    'redis' => [
        'host' => '127.0.0.1',
        'port' => 6379,
        'auth' => '',
    ],

    // Redis dbIndex 分配
    'redisDbIndex' => [
        'cache' => 0,       // 缓存
        'queue' => 1,       // 队列
        'redis' => 2,       // 存储
        'session' => 3,     // SESSION
    ],

    // 跨域白名单
    'domainWhitelist' => [
        '*'
    ],
];
