<?php

/**
 * 公共配置
 */
return [
    // 慢API耗时(秒), API执行超过此时间将报警
    'slowApiCost' => 3,

    // 慢队列任务耗时(秒), 队列任务执行超过此时间将报警
    'slowQueueCost' => 10,

    // 慢Task耗时(秒), Task执行超过此时间将报警
    'slowTaskCost' => 30,

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

    // 请求来源白名单
    'sourceWhitelist' => [
        'example-miniprogram' => '示例小程序',
    ],

    // Path白名单, 不验证请求来源的路径
    'pathWhitelist' => [
        '/order/v1/wxpay/wxa/notify',
    ],
];
