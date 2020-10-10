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
        'index' => [
            'cache' => 0,       // 缓存
            'redis' => 1,       // 存储
            'session' => 2,     // SESSION
            'queue' => 3,       // 队列
        ],
    ],

    // 跨域白名单
    'domainWhitelist' => [
        '*'
    ],
];
