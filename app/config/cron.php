<?php

/**
 * 计划任务配置 https://github.com/SidRoberts/phalcon-cron/wiki/Example
 */
$di->set(
    'cron',
    function () {
        $cron = new \Sid\Phalcon\Cron\Manager();

        /* example
        $cron->add(
            new \Sid\Phalcon\Cron\Job\Phalcon(
                '* * * * *',
                [
                    'task' => '\app\tasks\Main',
                    'action' => 'reEnqueue'
                ]
            )
        );
        */

        return $cron;
    }
);
