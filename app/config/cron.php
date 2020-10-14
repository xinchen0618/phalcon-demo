<?php

$di->set(
    'cron',
    function () {
        $cron = new \Sid\Phalcon\Cron\Manager();

        // 失败异步任务重新入队
        $cron->add(
            new \Sid\Phalcon\Cron\Job\Phalcon(
                '* * * * *',
                [
                    'task' => '\app\tasks\Main',
                    'action' => 'reEnqueue'
                ]
            )
        );

        return $cron;
    }
);
