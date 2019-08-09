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
    $config = $this->getConfig()->mysql->toArray();

    return new Phalcon\Db\Adapter\Pdo\Mysql($config);
});

