<?php

/**
 * 计划任务配置 https://github.com/SidRoberts/phalcon-cron/wiki/Example
 */
$di->set(
    'cron',
    function () {
        $cron = new \Sid\Phalcon\Cron\Manager();



        return $cron;
    }
);
